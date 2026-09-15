# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Predefined functions to handle the Alphafold predicted complex files: convert from cif to pdf,
get for each pairs of interactors the corresponding models, iptm or path to an archive tarball
with all models and plots.
"""

from os import path, listdir, makedirs, walk
import sys
import zipfile
import subprocess
import json
import pandas as pd
import numpy as np
from Bio.PDB import PDBParser, MMCIFParser, PDBIO
import pickle
from proto_tools.entities.structures import ChainSelection, SingleChainSelection, Structure
from proto_tools.entities.structures.structure import BFactorType
from proto_tools.tools.structure_scoring.pdockq2.pdockq2 import PDockQ2Config, PDockQ2Input, run_pdockq2

current_dir = path.dirname(__file__)
sys.path.insert(0, path.abspath(path.join(current_dir, "..")))

from constants import DB_TABLES, AF_PRED, DB_CREATION_AND_UPDATE

class AF3_Processing:


    def __init__(self):
        self.hosts = [host for host in  listdir(DB_TABLES)]
        self.folders = [f for f in listdir(path.join(AF_PRED, "af3_predictions"))]
        makedirs(path.join(AF_PRED, "clean_tables"), exist_ok=True)
        self.main()


    def main(self) -> None:
        summary_table_path = path.join(AF_PRED, "clean_tables", "complexes_summary.txt")
        if not path.isfile(summary_table_path):
            self.convert_cif_to_pdb()
            self.get_residues_contacts()
            self.final_df = self.get_final_table()
            self.final_df.to_csv(path.join(AF_PRED, "clean_tables", "complexes_summary.txt"), sep='\t', header=True, index=False)
        else:
            self.final_df = pd.read_csv(summary_table_path, sep='\t', header=0)
        self.save_all_archives()


    def convert_cif_to_pdb(self) -> None:
        """Convert all CIF models into PDB models while preserving pLDDT as B-factor."""
        for folder in self.folders:
            folder_path = path.join(AF_PRED, "af3_predictions", folder, "bactmentha_af3")
            cif_files = [
                f for f in listdir(folder_path)
                if f.endswith(".cif")
            ]
            for cif_file in cif_files:
                cif_path = path.join(folder_path, cif_file)
                pdb_file = path.splitext(cif_file)[0] + ".pdb"
                pdb_path = path.join(folder_path, pdb_file)
                if path.exists(pdb_path):
                    continue
                print(f"Converting {cif_path} to {pdb_path}...")
                parser = MMCIFParser(QUIET=True)
                structure = parser.get_structure("AF3", cif_path)
                io = PDBIO()
                io.set_structure(structure)
                io.save(pdb_path)


    def get_avg_plddt_chain(self, pdb_path: str, chain_id: str) -> float:
        """Calculate the average per-residue pLDDT for a PDB chain.
        AlphaFold stores pLDDT values in the B-factor column of the PDB file.
        """
        parser = PDBParser(QUIET=True)
        structure = parser.get_structure("AF3", pdb_path)
        chain = structure[0][chain_id]
        plddt_values = [
            atom.get_bfactor()
            for atom in chain.get_atoms()
        ]
        return float(np.mean(plddt_values))


    def get_residues_contacts(self) -> None:
        """Run the residue_contacts_Interactome3D processing script to create the contact residues
        table.
        """
        script_path = path.join(DB_CREATION_AND_UPDATE, "bactmentha_db_tools", "af3_interactome3D_processing.sh")
        for folder in self.folders:
            folder_path = path.join(AF_PRED, "af3_predictions", folder, "bactmentha_af3")
            pdb_files = [
                f for f in listdir(folder_path) 
                if f.endswith(".pdb")
            ]
            for pdb_file in pdb_files:
                pdb_path = path.join(folder_path, pdb_file)
                out_file = path.splitext(pdb_file)[0] + "_contact_residues.txt"
                out_path = path.join(folder_path, out_file)
                if path.exists(out_path):
                    continue
                print(f"Computing residue contacts {pdb_file} to {out_file}...")
                subprocess.run(
                    ["bash", script_path, pdb_path, out_path],
                    check=True
                )


    def get_final_table(self) -> pd.DataFrame:
        """Creates a summary pandas dataframe with columns:
        0 - complex_id      6 - ptm                 12 - contacts_on_A
        1 - protein_idA     7 - iptm                13 - contacts_on_B
        2 - avg_pLDDT_A     8 - pDockQ2_score       14 - A_positions
        3 - protein_idB     9 - pDockQ2_range       15 - B_positions
        4 - avg_pLDDT_B     10 - potential_clashes
        5 - model           11 - residue_contacts
        """
        rows = []
        for folder in self.folders:
            print(f"Getting rows for {folder}")
            folder_path = path.join(AF_PRED, "af3_predictions", folder, "bactmentha_af3")
            prot_a, prot_b = folder.split("_in_complex_with_")
            for pdb_file in [f for f in listdir(folder_path) if f.endswith(".pdb")]:
                row = self.get_model_row(folder_path, pdb_file, prot_a, prot_b)
                rows.append(row)
        df = pd.DataFrame(rows)
        col_order = ["complex_id", "protein_idA", "avg_pLDDT_A", "protein_idB", "avg_pLDDT_B",
                     "model", "ptm", "iptm", "pDockQ2_score", "pDockQ2_range", "potential_clashes",
                     "residue_contacts", "contacts_on_A", "contacts_on_B", "A_positions","B_positions"]
        return df[col_order].sort_values(by="complex_id", ascending=True)


    def get_model_row(self, folder_path, pdb_file, prot_a, prot_b) -> dict:
        pdb_path = path.join(folder_path, pdb_file)
        model = path.splitext(pdb_file)[0]
        row = self.get_basic_model_data(model, prot_a, prot_b)
        row.update(self.get_plddt_data(pdb_path))
        row.update(self.get_contact_data(folder_path, model))
        row.update(self.get_confidence_data(folder_path, model))
        row.update(self.get_pdockq2_score_and_range(folder_path, model))
        return row


    def get_basic_model_data(self, model, prot_a, prot_b) -> dict:
        return {
            "complex_id": f"{prot_a}:{prot_b}",
            "protein_idA": prot_a,
            "protein_idB": prot_b,
            "model": model
        }


    def get_plddt_data(self, pdb_path: str) -> dict:
        return {
            "avg_pLDDT_A": self.get_avg_plddt_chain(pdb_path, "A"),
            "avg_pLDDT_B": self.get_avg_plddt_chain(pdb_path, "B")
        }


    def get_contact_data(self, folder_path: str, model: str) -> dict:
        contact_path = path.join(folder_path, model + "_contact_residues.txt")
        return self.parse_contact_file(contact_path)

    @staticmethod
    def format_residues_positions(residues:set) -> str:
        """Format all the positions by merging continuous residues chains:
        12, 13, 14, 15, 42, 43, 44, 45, 46 -> 12-15_42-46
        47, 49, 50, 67, 69 -> 47_49-50_67_69

        Args:
            residues (set): _description_

        Returns:
            str: _description_
        """
        residues = sorted(residues)
        if not residues:
            return None
        regions = []
        start = residues[0]
        previous = residues[0]
        for position in residues[1:]:
            if position == previous + 1:
                previous = position
            else:
                if start == previous:
                    regions.append(str(start))
                else:
                    regions.append(f"{start}-{previous}")
                start = position
                previous = position
        # Add final region
        if start == previous:
            regions.append(str(start))
        else:
            regions.append(f"{start}-{previous}")
        return "_".join(regions)


    def parse_contact_file(self, contact_path: str) -> dict:
        data = {
            "potential_clashes": None,
            "residue_contacts": None,
            "contacts_on_A": None,
            "contacts_on_B": None,
            "A_positions": None,
            "B_positions": None
        }
        A_positions = set()
        B_positions = set()
        with open(contact_path) as f:
            for line in f:
                if line.startswith("Number of potential clashes:"):
                    data["potential_clashes"] = int(line.split(":")[1])
                elif line.startswith("Number of interface residues:"):
                    data["residue_contacts"] = int(line.split(":")[1])
                elif line.startswith("Number of contact residues chain A:"):
                    data["contacts_on_A"] = int(line.split(":")[1])
                elif line.startswith("Number of contact residues chain B:"):
                    data["contacts_on_B"] = int(line.split(":")[1])
                elif line.strip().startswith(
                    ("ARG", "ALA", "ASN", "ASP", "CYS",
                     "GLN", "GLU", "GLY", "HIS", "ILE",
                     "LEU", "LYS", "MET", "PHE", "PRO",
                     "SER", "THR", "TRP", "TYR", "VAL")):
                    fields = line.split()
                    # Example:
                    # ARG 47 NH1 GLU 14 OE2 hb 3.264
                    # fields[1] = residue position on chain A
                    # fields[4] = residue position on chain B
                    A_positions.add(int(fields[1]))
                    B_positions.add(int(fields[4]))
        data["A_positions"] = self.format_residues_positions(A_positions)
        data["B_positions"] = self.format_residues_positions(B_positions)
        return data


    def get_confidence_data(self, folder_path: str, model: str) -> dict:
        ptm_path = path.join(folder_path, "ranking_ptm.json")
        iptm_path = path.join(folder_path, "ranking_iptm.json")
        ptm = None
        iptm = None
        # Remove the ranked_X_ prefix
        original_model = model.split("_", 2)[2]
        # for ptm
        if path.exists(ptm_path):
            with open(ptm_path) as f:
                ptm_data = json.load(f)
            ptm = ptm_data.get("ptm", {}).get(original_model)
        if path.exists(iptm_path):
            with open(iptm_path) as f:
                iptm_data = json.load(f)
            iptm = iptm_data.get("iptm", {}).get(original_model)
        return {
            "ptm": ptm,
            "iptm": iptm
        }

    @staticmethod
    def get_pDockQ2_range(pdockq2_score:float) -> str:
        """Get a range value from the given score according to pDockQ2 documentation."""
        if pdockq2_score > 0.80:
            return "High"
        if pdockq2_score > 0.49:
            return "Medium"
        if pdockq2_score > 0.23:
            return "Acceptable"
        return "Low"


    def get_pae(self, pkl_file: str):
        with open(pkl_file, "rb") as f:
            data = pickle.load(f)
        return data["pae"]


    def calculate_pdockq2(self, struct_file:str, pae:list) -> float:
        structure = Structure.from_file(
            struct_file,
            b_factor_type=BFactorType.PLDDT,
            metrics={"pae": pae})
        inputs = PDockQ2Input(
            structure=structure,
            binder_chain=SingleChainSelection(chain="A"), # pathogen protein
            target_chains=ChainSelection(chains=["B"])    # host protein
        )
        config = PDockQ2Config(distance_cutoff=8.0) # see pDockQ2 documentation for cutoff of 0.8
        result = run_pdockq2(inputs, config)
        return result.metrics.pdockq2
        

    def get_pdockq2_score_and_range(self, folder_path: str, model: str) -> dict:
        struct_file = path.join(folder_path, model + ".pdb")
        pkl_model = model.split("_", 2)[2]
        pkl_file = path.join(folder_path, f"result_{pkl_model}.pkl")
        pae = self.get_pae(pkl_file)
        pdockq2_score = self.calculate_pdockq2(struct_file, pae)
        pdockq2_range = self.get_pDockQ2_range(pdockq2_score)
        return {"pDockQ2_score": pdockq2_score,
                "pDockQ2_range": pdockq2_range}


    def save_all_archives(self) -> None:
        """Create individual ZIP archives and one archive containing all complexes."""
        print("Saving all archives")
        output_dir = path.join(AF_PRED, "clean_archives")
        makedirs(output_dir, exist_ok=True)
        for folder in self.folders:
            zip_path = path.join(output_dir, f"{folder}.zip")
            if path.exists(zip_path):
                continue
            print(f"Saving archive for {folder}")
            self.save_zip_model(folder, zip_path)
        self.get_formatted_bactmentha_df()
        self.get_main_zip()


    def save_zip_model(self, folder:str, zip_path:str) -> None:
        """Compress one AF3 complex folder into a ZIP archive."""
        folder_path = path.join(AF_PRED, "af3_predictions", folder)
        with zipfile.ZipFile(zip_path, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=6) as archive:
            for root, _, files in walk(folder_path):
                for file in files:
                    file_path = path.join(root, file)
                    archive_name = path.relpath(file_path, path.dirname(folder_path))
                    archive.write(file_path, archive_name)


    def get_main_zip(self) -> None:
        """Create one ZIP archive containing all individual complex ZIP archives."""
        print("Saving final archive")
        zip_dir = path.join(AF_PRED, "clean_archives")
        summary_table = path.join(AF_PRED, "clean_tables", "complexes_summary.txt")
        mentha_af3 = path.join(AF_PRED, "clean_tables", "mentha_af3_complexes.txt")
        output_path = path.join(AF_PRED, "bactmentha_af3_complexes.zip")
        with zipfile.ZipFile(output_path, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=6 ) as archive:
            # Add all individual complex ZIP archives
            for filename in listdir(zip_dir):
                if filename.endswith(".zip"):
                    file_path = path.join(zip_dir, filename)
                    archive.write(file_path, arcname=filename)
            archive.write(summary_table, arcname="complexes_summary.txt")
            archive.write(mentha_af3, arcname="mentha_af3_complexes.txt")


    def get_complete_intfull_df(self) -> pd.DataFrame:
        """Read and sort all the interaction full tables in one df."""
        dfs = []
        for host in self.hosts:
            df = pd.read_csv(path.join(DB_TABLES, host, "04_Formatting", "interaction_full.txt"),
                             sep='\t', header=0)
            dfs.append(df)
        return pd.concat(dfs, axis=0, ignore_index=True)


    def sort_ab_columns_intfull(self, df:pd.DataFrame) -> pd.DataFrame:
        """Exchange A and B columns to keep pathogens in A and ost in B.
        """
        # condition for column exchange : host taxon is in b and need to be in a
        condition = df['taxon_interactor_idA'].astype(str).isin(self.hosts)
        df.loc[condition, ['interactor_idB', 'interactor_idA', 'taxon_interactor_idB', 'taxon_interactor_idA']] = \
            df.loc[condition, ['interactor_idA', 'interactor_idB', 'taxon_interactor_idA', 'taxon_interactor_idB']].values
        return df


    def get_formatted_bactmentha_df(self) -> None:
        """Format the final output table into the expected bactmentha table with the mnt_interaction_id
        column for all interactions involving the pairs.
        """
        sub_dfs = []
        intfull = self.get_complete_intfull_df()
        intfull_sorted = self.sort_ab_columns_intfull(intfull)
        for _, row in intfull_sorted.iterrows():
            sub_df = self.final_df[self.final_df["complex_id"] == f"{row['interactor_idA']}:{row['interactor_idB']}"]
            sub_df["mnt_interaction_id"] = row["mnt_interaction_id"]
            sub_dfs.append(sub_df)
        df = pd.concat(sub_dfs, axis=0)
        df_path = path.join(AF_PRED, "clean_tables", "mentha_af3_complexes.txt")
        df.to_csv(df_path, sep='\t', header=True, index=False)


if __name__ == "__main__":
    AF3_Processing()
