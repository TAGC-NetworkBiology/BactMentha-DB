# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Scirpt to fill the database tables using the files derived from mimicint workflow.
It will also indirectly fill the view (that are automatically updated)
"""

import psycopg2  # for database connection and queries execution
import platform
import warnings
from datetime import datetime
import time
from os import path, listdir, stat
import pandas as pd
import sys
import argparse
import subprocess

from bactmentha_db_tools.db_tasks import Db_tasks
from bactmentha_db_tools.get_cellular_components import GetCellularComponents
from bactmentha_db_tools.af3_processing import AF3_Processing

from constants import REFERENCE, DB_TABLES, WHO_ANNOT, AF_PRED, CELL_COMP, TABLES_PATHS, PRIMARY_KEYS

class Insertion():
    """
    Class for the data insertion in the database tables after their creation.

    Reads the references files into dataframes
    Do some changes on some of the dataframes
    Add a dataframe for the metadata table
    Add the data from each dataframe into a different database table
    
    It uses two function from createdb.py script for database connection and query execution.
    It is called by the updatedb.py script.
    """

    def __init__(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str) -> None:
        """
        Insertion of the data in the database tables.

        Args: connection parameters
            - db_name: name of the database to connect to (defined in the docker compose file / .env file)
            - db_host_name: name of the database host (In the docker compose file it is the service that contain the database)
            - db_user_name: user if often 'postgres' (defined in the docker compose file / .env file)
            - db_pw: the database password (defined in the docker compose file / .env file)
            - db_port: port to listen from the docker container (defined in the docker compose file / .env file)

        Return: None (Calls the main function to do the data insertion)
        """
        af3_path = path.join(AF_PRED, "clean_tables", "mentha_af3_complexes.txt")
        if not path.isfile(af3_path):
            AF3_Processing()
        cc_path = path.join(CELL_COMP, "uniprot_localizations.txt")
        if not path.isfile(cc_path):
            GetCellularComponents()
        self.bactmentha_db = Db_tasks(db_name, db_host_name, db_user_name, db_pw, db_port)
        self.host_taxa = [h for h in listdir(path.join(REFERENCE, "01_DatabaseTables"))]
        self.df_names = TABLES_PATHS.keys()
        self.main()


    def main(self) -> None:
        """
        Run the Data Insertion script to fill the bactmentha database.
        """
        print("\nRunning script insertdata.py\n")
        print("Reading Reference files:")
        self.dfs = self.get_dfs_from_files()
        print("Adding WHO_priority and Hazard groups to uniprot_taxonomy")
        self.add_who_pritority_in_uniprot_taxo()
        print("Adding PA-BR-MI annotations column in interaction_full")
        self.add_annotations_in_intfull()
        print("Filling database tables:")
        self.fill_database_tables()
        print("Creating metadata table")
        self.create_and_fill_metadata()
        print("\nEnd of script insertdata.py. Closing connexion to PosgreSQL database.\n")


    @staticmethod
    def change_column_names_in_annotations_df(table_name:str, df:pd.DataFrame) -> pd.DataFrame:
        """
        Changing the column names for tables 'annotation' and 'protein_annotation' to fit the 
        database tables.

        Args:
            - table_name: table to modify ('annotation' or 'protein_annotation').
            - df: corresponding pandas dataframe created with the reference files.

        Return:
            - df: modified dataframe with the right column names and order.
        """
        if table_name == 'annotation':
            df.rename(columns={"id":"annotation_id", 
                               "Description":"annotation_description"}, inplace=True)
        elif table_name == 'protein_annotation':
            df.rename(columns={"query_bactmentha":"uniprot_ac",
                               "target_ac":"annotated_sequence",
                               "source":"annotation_source",
                               "annotation":"annotation_id",
                               "inference":"inference_method",
                               "seq_identity":"sequence_identity"}, inplace=True)
        elif table_name == "cellular_components":
            df.rename(columns={"protein": "uniprot_ac",
                               "cellular_component_value": "CC_value",
                               "cellular_component_id": "CC_id",}, inplace=True)
            # also remove the columns that won't be in the database
            df = df[["uniprot_ac", "CC_value", "CC_id", "topology_value", "topology_id", "annotation_source"]]
        elif table_name == "af3_predictions":
            df.rename(columns={"protein_idA": "interactor_idA",
                               "avg_pLDDT_A": "avg_plddt_idA",
                               "protein_idB": "interactor_idB",
                               "avg_pLDDT_B": "avg_plddt_idB",
                               "pDockQ2_score": "pdockq2_score",
                               "pDockQ2_range": "pdockq2_range",}, inplace=True)
            # also remove the columns that won't be in the database
            df = df[["mnt_interaction_id", "interactor_idA", "avg_plddt_idA", "interactor_idB",
                     "avg_plddt_idB", "model", "ptm", "iptm", "pdockq2_score", "pdockq2_range",
                     "potential_clashes", "residue_contacts", "contacts_on_A", "contacts_on_B"]]
        return df

    @staticmethod
    def modif_table_content_keywords_and_uniprot_protein(table_name:str, table_content:pd.DataFrame) -> pd.DataFrame:
        """
        Modification of a column in two 'uniprot_protein' and 'uniprot_keyword'
        to fit the database syntax because there might be some single quotes in some enzymes names.

        Args:
            - table_name: name of the table to modify ('uniprot_protein' or 'uniprot_keyword').
            - table_content: corresponding pandas dataframe created with the reference files.

        Return:
            - table_content: modified pandas dataframe corresponding to the table.

        """
        if table_name == 'uniprot_protein':
            # For enzymes names that contain a ' : to fit postgresql syntax.
            table_content["uniprot_name"] = table_content["uniprot_name"].str.replace("'", "''")
        elif table_name == 'uniprot_keyword':
            # Because this column is readen as float from the file.
            table_content["uniprot_taxon_id"] = table_content["uniprot_taxon_id"].astype('Int64') # rounded
            table_content["uniprot_taxon_id"] = table_content["uniprot_taxon_id"].astype(str) # converted to match uniprot_taxonomy format
        return table_content


    def exchange_a_and_b_columns(self, df_name:str, df:pd.DataFrame) -> pd.DataFrame:
        """
        Exchange specified columns between interactor A and B based on the given table name.
        Get pathogen proteins in A and host proteins in B.

        Parameters:
        - df_name (str): The name of the table.
        - df (pd.DataFrame): The DataFrame representing the table content.

        Returns:
        - pd.DataFrame: The modified DataFrame after column exchange.
        """
        if df_name == "interaction_full":
            condition = df['taxon_interactor_idA'].isin(self.host_taxa)
            cols_order_a = ['interactor_idB', 'interactor_idA', 'taxon_interactor_idB', 'taxon_interactor_idA']
            cols_order_b = ['interactor_idA', 'interactor_idB', 'taxon_interactor_idA', 'taxon_interactor_idB']
        elif df_name == "interaction_feature":
            intfull = self.df_dict["interaction_full"] # already sorted
            intersectionA = df['interactor_idA'].isin(intfull['interactor_idB'].unique())
            intersectionB = df['interactor_idB'].isin(intfull['interactor_idA'].unique())
            condition = intersectionA | intersectionB
            cols_order_a = ['interactor_idB', 'feature_interactor_idB', 'feature_start_interactor_idB', 'feature_end_interactor_idB',
                            'interactor_idA', 'feature_interactor_idA', 'feature_start_interactor_idA', 'feature_end_interactor_idA']
            cols_order_b = ['interactor_idA', 'feature_interactor_idA', 'feature_start_interactor_idA', 'feature_end_interactor_idA',
                            'interactor_idB', 'feature_interactor_idB', 'feature_start_interactor_idB', 'feature_end_interactor_idB']
        elif df_name == "mimicint_interface":
            intfull = self.df_dict["interaction_full"] # already sorted
            intersectionA = df['interface_interactor_idA'].isin(intfull['interactor_idB'].unique())
            intersectionB = df['interface_interactor_idB'].isin(intfull['interactor_idA'].unique())
            condition = intersectionA | intersectionB
            cols_order_a = ['interface_interactor_idB', 'interface_type_idB', 'interface_start_interactor_idB', 'interface_end_interactor_idB',
                            'interface_interactor_idA', 'interface_type_idA', 'interface_start_interactor_idA', 'interface_end_interactor_idA']
            cols_order_b = ['interface_interactor_idA', 'interface_type_idA', 'interface_start_interactor_idA', 'interface_end_interactor_idA',
                            'interface_interactor_idB', 'interface_type_idB', 'interface_start_interactor_idB', 'interface_end_interactor_idB']
        # Change columns:
        df.loc[condition, cols_order_a] = df.loc[condition, cols_order_b].values
        return df


    def get_table_df_path(self, df_name:str, host:str) -> str:
        """From table names, get the table to the corresponding text file.

        Args:
            df_name (str): table name in TABLES_PATHS dict keys.
            host (str): host name to get subtable.

        Returns:
            str: full path to the table file.
        """
        if df_name == "who_annotations":
            return path.join(WHO_ANNOT, TABLES_PATHS[df_name])
        elif df_name == "af3_predictions":
            return path.join(AF_PRED, TABLES_PATHS[df_name])
        elif df_name == "cellular_components":
            return path.join(CELL_COMP, TABLES_PATHS[df_name])
        return path.join(DB_TABLES, host, TABLES_PATHS[df_name])


    def get_formatted_df(self, df_name:str, df:pd.DataFrame) -> pd.DataFrame:
        """Format the table if needed to mach the expected database format (column renaming,
        A/B columns exchange...)

        Args:
            df_name (str): table name in TABLES_PATHS dict keys. 
            df (pd.DataFrame): corresponding table read from file.

        Returns:
            pd.DataFrame: modified df or same df if no change needed.
        """
        if df_name in ['annotation','protein_annotation','cellular_components','af3_predictions']:
            return self.change_column_names_in_annotations_df(df_name, df)
        # Changing the syntax in some columns if necessary
        elif df_name in ['uniprot_protein', 'uniprot_keyword']:
            return self.modif_table_content_keywords_and_uniprot_protein(df_name, df)
        # Change A and B interactors so that host protein is in A and bacterial is in B
        elif df_name in ['interaction_full', 'interaction_feature', 'mimicint_interface']:
            return self.exchange_a_and_b_columns(df_name, df)
        return df


    def get_dfs_from_files(self) -> dict[str, pd.DataFrame]:
        """Get fo each table the corresponding pandas dataframe from file.

        Returns:
            dict: tables names as keys and pd.DataFrame as values
        """
        self.df_dict = {}
        for df_name in self.df_names:
            print(f"    - {df_name}")
            df_list = []
            for host in self.host_taxa:
                if "mimicint" in df_name and host != "9606":
                    continue
                table_path = self.get_table_df_path(df_name, host)
                tmp_df = pd.read_csv(table_path, sep='\t', header=0, index_col=None, dtype=str)
                df_list.append(tmp_df)
            tmp_df = pd.concat(df_list, axis=0)
            df = self.get_formatted_df(df_name, tmp_df)
            if df_name == "interaction_full":
                df = df.rename(columns={"confidence_score": "MI_Score"})
                print(df.columns)
            self.df_dict[df_name] = df.drop_duplicates(ignore_index=True)
        return self.df_dict


    def add_who_pritority_in_uniprot_taxo(self) -> None:
        """
        Add the who_priority and hazard_group columns to the uniprot_taxonomy_table when informations are present.
        """
        unip_taxo = self.dfs["uniprot_taxonomy"]
        who_annot = self.dfs["who_annotations"]
        new_df = pd.merge(unip_taxo, who_annot[['taxon_id', 'who_priority', 'hazard_group']],
                          on='taxon_id', how='left')
        self.dfs["uniprot_taxonomy"] = new_df


    def check_annotation_PA(self, unip_ac:str) -> int:
        """Check if the given UniProt AC has at least one value in bacterial protein annotation df.
        Return: 1 if True, else False.
        """
        return int(unip_ac in self.dfs["protein_annotation"]["uniprot_ac"].unique())


    def check_annotation_CC(self, prot_a:str, prot_b) -> int:
        """Check if at least one of the given proteins id has at least one value in the cellular
        components table.
        Return: 1 if True, else False.
        """
        return int(prot_a in self.dfs["cellular_components"]["uniprot_ac"].unique() \
                   or prot_b in self.dfs["cellular_components"]["uniprot_ac"].unique())


    def check_annotation_BR(self, mnt_id:str) -> int:
        """Check if the given mnt_interaction_id is found at least once in the interaction_feature
        table.
        Return: 1 if True, else False.
        """
        return int(mnt_id in self.dfs["interaction_feature"]["mnt_interaction_id"].unique())
    

    def check_annotation_MI(self, mnt_id:str) -> int:
        """Check if the given mnt_interaction_id is found at least once in the mimicint_xref
        table.
        Return: 1 if True, else False.
        """
        return int(mnt_id in self.dfs["mimicint_xref"]["mnt_interaction_id"].unique())
    

    def check_annotation_AF(self, mnt_id:str) -> int:
        """Check if the given mnt_interaction_id is found at least once in the af3_predictions
        table.
        Return: 1 if True, else False.
        """
        return int(mnt_id in self.dfs["af3_predictions"]["mnt_interaction_id"].unique())


    def add_annotations_in_intfull(self) -> None:
        """Modification of the interaction_full table to add the Annotations column.
        This column values will depend on the available additionnal information per interaction:
        - protein_annotation for the bacterial protein (interactor_idA) ?
        - cellular_components (for at least one of the interactors) ?
        - interaction_feature ?
        - mimicint_interface ?
        - af3_predictions ?

        The data in the annotation column will be "XXXXX" where X can be either '0' or '1'
        depending if some informations were found or not.
        """
        # get tables of interest:
        uniprot_xref = self.dfs["uniprot_xref"]
        # empty annotations column
        annotations = []
        # Fill annoations column         
        for _, row in self.dfs["interaction_full"].iterrows():
            mnt_id = row["mnt_interaction_id"]
            prot_a = row["interactor_idA"]
            prot_b = row["interactor_idB"]
            unip_ac_a = uniprot_xref.loc[uniprot_xref['interactor_id'] == prot_a]["uniprot_ac"].iloc[0]
            # Get values for each annotation type:
            has_PA = self.check_annotation_PA(unip_ac_a)
            has_CC = self.check_annotation_CC(prot_a, prot_b)
            has_BR = self.check_annotation_BR(mnt_id)
            has_MI = self.check_annotation_MI(mnt_id)
            has_AF = self.check_annotation_AF(mnt_id)
            annotations.append(f"{has_PA}{has_CC}{has_BR}{has_MI}{has_AF}")
        self.dfs["interaction_full"] = self.dfs["interaction_full"].assign(annotations=annotations)


    def fill_database_tables(self) -> None:
        """Fill all the database tables with the content from the self.dfs dict.
        """
        for df_name in self.df_names:
            if df_name != "who_annotations":
                print(f"    - {df_name}")
                self.fill_db_with_table(df_name, self.dfs[df_name], PRIMARY_KEYS[df_name])


    def fill_db_with_table(self, df_name:str, df:pd.DataFrame, p_key:str) -> None:
        """
        Reading the pandas tables and adding the containing data to the database tables using postgresql queries.
        Called after dataframe creation with the references files.

        Args:
            - df_name: name of the table to fill in the database
            - df: pandas dataframe created with the reference files (exception for metadata)
            - p_key: primary key in the database table
        """
        queries = []
        # takes the columns names from the table
        col_list = df.columns.values.tolist()
        # col_list converted to tuple to fit postgres syntax (removing the quotes)
        col_tuple = str(col_list).replace("'",'').replace('[','(').replace(']',')')
        for line_tuple in df.itertuples(index=False, name=None):
            # Convert NaN values to NULL for PostgreSQL
            line_tuple = tuple('NULL' if pd.isna(value) else value for value in line_tuple)
            # common query start for all cases
            query = ("INSERT INTO " + df_name + " " + col_tuple + " VALUES " + str(line_tuple).replace('"', "'"))
            # First case : the table as a primary_key
            if p_key is not None:
                query += f" ON CONFLICT ({p_key}) DO NOTHING;"
            # Second case : no primary_key -> unique lines
            else:
                query += " ON CONFLICT DO NOTHING;"
            queries.append(query)
        self.bactmentha_db.execute_many_queries(queries)


    def get_file_creation_date(self, filepath: str) -> datetime:
        birth_time = int(subprocess.check_output(["stat", "-c", "%W", filepath], text=True))
        return datetime.fromtimestamp(birth_time)


    def create_and_fill_metadata(self) -> None:
        """Creates the metadata table to store the database last update (date of downloading of the
        imex interactions files, check on human host only).
        """
        # Setting date of data downloading from IMEx.
        filepath = path.join(DB_TABLES, "9606", "01_Mentha_host_interaction", "mentha_9606_interactions.txt")
        creation_time = self.get_file_creation_date(filepath)
        formatted_date = creation_time.strftime("%B %-dth %Y")
        version = creation_time.strftime("%Y_%m_%d")
        # Taking the version of postgres
        query = "SELECT setting FROM pg_settings WHERE name = 'server_version'"
        postgres_version = self.bactmentha_db.cursor_query_and_get_first_result(query)
        # Taking the versions of python and main libraries
        python_version = platform.python_version()
        psycopg2_module_version = psycopg2.__libpq_version__
        pandas_module_version = pd.__version__ 
        # Adding all to the future columns of metadata table
        metadata_list = ["last_update", "version", "postgresql", "python", "psycopg2_PyLib", "Pandas_PyLib"]
        update_version_list = [formatted_date, version, postgres_version, python_version, 
                               psycopg2_module_version, pandas_module_version]
        # Setting the metadata.content
        df = pd.DataFrame({"metadata" : metadata_list, "version_or_update" : update_version_list})
        # Filling the database table with this one
        self.fill_db_with_table('metadata', df, 'metadata')



if __name__ == "__main__":

    # Instantiate the parser
    parser = argparse.ArgumentParser(prog='BactMentha Data Insertion',
                                    description='This program creates the database tables using the given reference data. \
                                                It also creates the views for the web visualisation.')
   # Add the parser arguments
    parser.add_argument('--db_name', '--dbn', type=str, required=True,
                        help='the name of the database. Ex: "bactmentha_db"')
    parser.add_argument('--db_host_name', '--dbh', type=str, required=True, 
                        help='Host name, name of the service that contains \
                        the database if you are using a docker .yml file. Ex:"db"')
    parser.add_argument('--db_user_name', '--dbu', type=str, required=True,
                        help='the name of the database user. Ex: "postgres"')
    parser.add_argument('--db_password', '--dbpw', type=str, required=True,
                        help='Password for the database. Ex: "postgres"')
    parser.add_argument('--db_port', '--dbpt', type=str, required=True,
                        help='Port to connect to the database, defined in the .yml file. Ex: "5432"')

    args = parser.parse_args()

    insertion = Insertion(args.db_name, args.db_host_name, args.db_user_name, args.db_password, args.db_port)
