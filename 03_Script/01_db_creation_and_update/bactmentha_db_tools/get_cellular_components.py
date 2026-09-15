# -*- coding: utf-8 -*-
#========================================
# Author: Lou BERGOGNE
#========================================

# Standard libraries imports
import time
from concurrent.futures import ThreadPoolExecutor, as_completed
import threading
import requests
from os import path, makedirs, listdir
import sys
import pandas as pd

current_dir = path.dirname(__file__)
sys.path.insert(0, path.abspath(path.join(current_dir, ".."))) # import constants.py

from constants import DB_TABLES, CELL_COMP

class GetCellularComponents:

    def __init__(self) -> None:
        self.thread_local = threading.local()
        self.hosts = [f for f in listdir(path.join(DB_TABLES))]
        self.prots = self.get_all_proteins()
        self.thread_local = threading.local()
        makedirs(CELL_COMP, exist_ok=True)
        makedirs(path.join(CELL_COMP, "raw"), exist_ok=True)
        self.main()


    def get_all_proteins(self) -> set:
        """Get the merged uniprot_protein.txt tables for all hosts. And return all unique proteins ids."""
        dfs = []
        for h in self.hosts:
            df = pd.read_csv(path.join(DB_TABLES, h, "04_Formatting", "interaction_light.txt"),
                             sep='\t', header=0)
            dfs.append(df)
        full = pd.concat(dfs, axis=0, ignore_index=True)
        set_a = set(full["interactor_idA"].unique())
        set_b = set(full["interactor_idB"].unique())
        return set_a.union(set_b)


    def main(self) -> None:
        """Read the tables for each host and sort them to get the list of pathogen and host proteins.
        """
        prots_cc_found = self.get_prots_cellular_components_from_uniprot()
        print(f"Among the {len(self.prots)} proteins, {len(prots_cc_found)} have CC.")
        self.prots_cc_df = self.get_prots_cellular_components_table()


    def get_prots_cellular_components_from_uniprot(self) -> set:
        """Get the cellular localisation from Uniprot for each protein."""
        not_found_file = path.join(CELL_COMP, "prots_localization_not_found.txt")
        prots_localization_not_found = self.get_previous_localization_status(not_found_file)
        prots_cc_found, prots_to_process = self.get_prots_to_process(prots_localization_not_found)
        print(f"{len(prots_cc_found)} proteins already processed, {len(prots_to_process)} proteins to process.")
        self.process_proteins(prots_to_process, prots_cc_found, prots_localization_not_found, not_found_file)
        return prots_cc_found


    def get_previous_localization_status(self, not_found_file: str) -> dict:
        """Read the localisation status from the previous run."""
        if not path.exists(not_found_file):
            with open(not_found_file, "w") as f:
                f.write("protein\treason\n")
            return {}
        df_not_found = pd.read_csv(not_found_file, sep="\t", header=0)
        return dict(zip(df_not_found["protein"], df_not_found["reason"]))


    def get_prots_to_process(self, prots_localization_not_found: dict) -> tuple:
        """Identify proteins already processed and proteins that need to be processed."""
        prots_cc_found = set()
        prots_to_process = set()
        for protein in self.prots:
            output_file = path.join(CELL_COMP, "raw", f"{protein}.txt")
            if path.exists(output_file):
                prots_cc_found.add(protein)
            elif prots_localization_not_found.get(protein) == "Protein failed due to max retry attempts":
                prots_to_process.add(protein)
            elif protein not in prots_localization_not_found:
                prots_to_process.add(protein)
        return prots_cc_found, prots_to_process


    def process_proteins(self, prots_to_process: set, prots_cc_found: set, prots_localization_not_found: dict, not_found_file: str) -> None:
        """Process proteins with no localisation file using parallel workers."""
        max_workers = 10
        with ThreadPoolExecutor(max_workers=max_workers) as executor:
            futures = [executor.submit(self.get_protein_cellular_components, protein)
                    for protein in prots_to_process]
            for counter, future in enumerate(as_completed(futures), 1):
                protein, reason = future.result()
                if reason == "found":
                    prots_cc_found.add(protein)
                    prots_localization_not_found.pop(protein, None)
                else:
                    prots_localization_not_found[protein] = reason
                self.write_localization_status(prots_localization_not_found, not_found_file)
                print(f"protein {counter}/{len(prots_to_process)}: {protein}")


    def write_localization_status(self, prots_localization_not_found: dict, not_found_file: str) -> None:
        """Write the current localisation status to the checkpoint file."""
        df_not_found = pd.DataFrame(prots_localization_not_found.items(), columns=["protein", "reason"])
        df_not_found.to_csv(not_found_file, sep="\t", header=True, index=False)


    def get_protein_cellular_components(self, protein: str) -> tuple:
        """Extract the cellular component from uniprot for a single protein"""
        canonical_protein = protein.split("-")[0]
        # First try the exact protein accession, else with canonical protein if no subcell loc
        data, status = self.get_uniprot_json(protein)
        source = "exact"
        if status == "failed":
            return protein, "Protein failed due to max retry attempts"
        if status == "not_found" or not self.has_subcellular_location(data):
            if canonical_protein != protein:
                data, status = self.get_uniprot_json(canonical_protein)
                source = "canonical_fallback"
                if status == "failed":
                    return protein, "Protein failed due to max retry attempts"
                if status == "not_found":
                    return protein, "Protein and/or canonical form not existing on Uniprot"
            else:
                if status == "not_found":
                    return protein, "Protein and/or canonical form not existing on Uniprot"
        # No subcellular localisation, even after canonical fallback.
        if not data or not self.has_subcellular_location(data):
            return protein, "Protein found without SubcellularLocation"
        # Else write location into the output file.
        locations = self.extract_location_entries_from_data(data, source)
        self.write_protein_localization_file(protein, data, source, locations)
        return protein, "found"


    def get_session(self) -> requests.Session:
        """Get one requests Session per worker thread."""
        if not hasattr(self.thread_local, "session"):
            self.thread_local.session = requests.Session()
        return self.thread_local.session


    def get_uniprot_json(self, accession: str) -> dict:
        """Retrieve the UniProt JSON entry for an accession."""
        url = f"https://rest.uniprot.org/uniprotkb/{accession}.json"
        session = self.get_session()
        max_attempts = 3
        for attempt in range(max_attempts):
            try:
                response = session.get(url, timeout=(5, 15))
                # Entry does not exist / accession cannot be retrieved.
                # Return {} so the caller can try the canonical accession if not already it.
                if response.status_code in {400, 404}:
                    return {}, "not_found"
                # Temporary problems: retry.
                if response.status_code in {429, 500, 502, 503, 504}:
                    if attempt < max_attempts - 1:
                        wait_time = 2 ** attempt
                        print(
                            f"UniProt {response.status_code} for {accession}, "
                            f"retrying in {wait_time}s..."
                        )
                        time.sleep(wait_time)
                        continue
                    return {}, "failed"
                response.raise_for_status()
                return response.json(), "found"
            except requests.RequestException as e:
                if attempt < max_attempts - 1:
                    wait_time = 2 ** attempt
                    print(
                        f"ERROR retrieving {accession}: {e}. "
                        f"Retrying in {wait_time}s..."
                    )
                    time.sleep(wait_time)
                else:
                    print(
                        f"ERROR retrieving {accession} after "
                        f"{max_attempts} attempts: {e}"
                    )
        return {}, "failed"


    def has_subcellular_location(self, data: dict) -> bool:
        """Check whether a UniProt entry contains subcellular location data."""
        return any(
            comment.get("commentType") == "SUBCELLULAR LOCATION"
            for comment in data.get("comments", [])
        )


    def extract_location_entries_from_data(self, data: dict, source: str) -> dict:
        """Extract UniProt subcellular localisation annotations."""
        locations = []
        for comment in data.get("comments", []):
            if comment.get("commentType") != "SUBCELLULAR LOCATION":
                continue
            locations.extend(self.extract_location_entries(comment, source))
        return locations


    def extract_location_entries(self, comment: dict, source: str) -> list:
        """Extract location, topology and evidence information."""
        locations = []
        for entry in comment.get("subcellularLocations", []):
            location = entry.get("location", {})
            topology = entry.get("topology", {})
            evidence = entry.get("evidences", [])
            evidence_source = None
            evidence_id = None
            if evidence:
                evidence_source = evidence[0].get("source")
                evidence_id = evidence[0].get("id")
            location_data = {
                "value": location.get("value"),
                "id": location.get("id"),
                "topology_value": topology.get("value"),
                "topology_id": topology.get("id"),
                "annotation_source": source,
                "evidence_source": evidence_source,
                "evidence_id": evidence_id
            }
            locations.append(location_data)
        return locations


    def write_protein_localization_file(self, protein: str, data: dict, source: str, locations: list) -> None:
        """Write UniProt protein information and cellular localisation."""
        taxon_id = data.get("organism", {}).get("taxonId")
        output_file = path.join(CELL_COMP, "raw", f"{protein}.txt")
        with open(output_file, "w") as f:
            f.write(f"uniprot_ac: {protein}\n")
            f.write(f"taxon_id: {taxon_id}\n")
            f.write("has_subcellular_location: True\n")
            f.write(f"subcellular_location_source: {source}\n")
            f.write("locations:\n")
            f.write("value\tid\ttopology_value\ttopology_id\tannotation_source\tevidence_source\tevidence_id\n")
            for location in locations:
                f.write(f"{location.get('value') or '-'}\t{location.get('id') or '-'}\t"
                    f"{location.get('topology_value') or '-'}\t{location.get('topology_id') or '-'}\t"
                    f"{location.get('annotation_source') or '-'}\t{location.get('evidence_source') or '-'}\t"
                    f"{location.get('evidence_id') or '-'}\n")


    def get_prots_cellular_components_table(self) -> pd.DataFrame:
        """Read cellular localisation files and convert them into a long-format DataFrame.
        """
        rows = []
        for filename in listdir(path.join(CELL_COMP, "raw")):
            filepath = path.join(CELL_COMP, "raw", filename)
            with open(filepath, "r") as f:
                lines = [line.rstrip("\n") for line in f]
            protein = lines[0].split(":", 1)[1].strip()
            if not protein in self.prots:
                continue
            locations_index = lines.index("locations:")
            header = lines[locations_index + 1].split("\t")
            for line in lines[locations_index + 2:]:
                if not line:
                    continue
                values = line.split("\t")
                location = dict(zip(header, values))
                rows.append({
                    "protein": protein,
                    "cellular_component_value": location["value"],
                    "cellular_component_id": location["id"],
                    "topology_value": location["topology_value"],
                    "topology_id": location["topology_id"],
                    "annotation_source": location["annotation_source"],
                    "evidence_source": location["evidence_source"],
                    "evidence_id": location["evidence_id"]
                })
        df = pd.DataFrame(rows).drop_duplicates(ignore_index=True)
        df = df[df["protein"].isin(self.prots)] # remove canonical not used rows
        cc_outpath = path.join(CELL_COMP, "uniprot_localizations.txt")
        df.to_csv(cc_outpath, sep='\t', header=True, index=False)
        print(f"Saving at {cc_outpath}")
        return df



if __name__ == "__main__":
    GetCellularComponents()