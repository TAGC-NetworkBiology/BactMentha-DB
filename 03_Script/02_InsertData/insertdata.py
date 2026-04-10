# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Scirpt to fill the database tables using the files derived from mimicint workflow.
It will also indirectly fill the view (that are automatically updated)
"""

import psycopg2  # for database connection and queries execution
import platform
import warnings
import datetime
import time
import os
import pandas as pd
import sys
import argparse


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

    def __init__(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str, reference_path:str, path_to_createdb:str, who_annotations_path:str) -> None:
        """
        Insertion of the data in the database tables.

        Args: connection parameters
            - db_name: name of the database to connect to (defined in the docker compose file / .env file)
            - db_host_name: name of the database host (In the docker compose file it is the service that contain the database)
            - db_user_name: user if often 'postgres' (defined in the docker compose file / .env file)
            - db_pw: the database password (defined in the docker compose file / .env file)
            - db_port: port to listen from the docker container (defined in the docker compose file / .env file)
            - reference_path: path to the raw data folder that contains a subfolder for each taxon (defined in the docker compose file / .env file)
            - path_to_createdb: path to the tables and views cretaion script (defined in the docker compose file / .env file)

        Return: None (Calls the main function to do the data insertion)
        """
        # To import the database connexion and query execution functions
        sys.path.append(path_to_createdb)
        from createdb import Createdb as cdb

        self.connection = None
        self.list_of_file_names = ['uniprot_taxonomy.txt', 'uniprot_protein.txt', 'uniprot_keyword.txt',
                                   'annotation_description.txt', 'bacterial_annotation.txt',
                                   'uniprot_xref.txt', 'interaction_full.txt', 'interaction_feature.txt',
                                   'mimicint_interface.txt', 'imex_xref.txt', 'mimicint_xref.txt']
        self.list_of_table_names = ['uniprot_taxonomy', 'uniprot_protein', 'uniprot_keyword', 'annotation',
                                    'protein_annotation', 'uniprot_xref', 'interaction_full', 'interaction_feature',
                                    'mimicint_interface', 'imex_xref', 'mimicint_xref']
        self.list_of_primary_keys = ['taxon_id', 'uniprot_ac', None, 'annotation_id', None, 'interactor_id',
                                     'mnt_interaction_id', None, 'mimicint_interaction_id', None, None]
        self.list_of_df = []
        self.ref_directory = reference_path
        self.list_of_taxa = [tax for tax in os.listdir(self.ref_directory)]
        dtypes = {'hazard_group': str}
        self.who_annotations = pd.read_table(who_annotations_path, header=0, dtype=dtypes)

        # Calling the main function
        self.data_insertion(db_name, db_host_name, db_user_name, db_pw, db_port, cdb)


    def data_insertion(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str, cdb:__module__) -> None:
        """
        Main function that calls the other ones to insert the data from the reference files into the database tables.
        The reference folder must contain separed taxon subfolder in which the used files must have the same names.
        Some informations are added into the metadata table during data insertion (versions, dates...)

        Args: connection parameters
            - db_name: name of the database to connect to (defined in the docker compose file / .env file)
            - db_host_name: name of the database host (In the docker compose file it is the service that contain the database)
            - db_user_name: user if often 'postgres' (defined in the docker compose file / .env file)
            - db_pw: the database password (defined in the docker compose file / .env file)
            - db_port: port to listen from the docker container (defined in the docker compose file / .env file)
            - cdb: module that contains some connexion and query execution functions (from createdb.py script)

        Return: None
        """
        # Starting connexion with postgresql
        print("\nRunning script insertdata.py\n")
        self.connection = cdb.create_server_connection(db_name, db_host_name, db_user_name, db_pw, db_port)
        print("Starting tables creation")
        print("Creating the database tables with files")
        # Creating and filling DB tables one by one
        for file_name in self.list_of_file_names:
            try:
                self.list_of_df.append(self.table_creation_with_file(file_name))
                print("Successfully converted the files into pandas dataframes")
            except:
                print("An error occured when trying to read the file ", file_name)
        try:
            self.add_annotations_in_intfull()
            print("Successfully added the annotations column in interaction full")
        except Exception as e:
            print("An error occured trying to add the annotations column in interaction full")
            import traceback
            traceback.print_exc()
        try:
            self.add_who_pritority_in_uniprot_taxo()
            print("Successfully added the who_priority and hazard_group columns in uniprot_taxonomy table")
        except:
            print("An error occured trying to add the who_priority and hazard_group columns in uniprot_taxonomy table")
        for file_name in self.list_of_file_names:
            index_in_lists = self.list_of_file_names.index(file_name)
            table_name = self.list_of_table_names[index_in_lists]
            table_content = self.list_of_df[index_in_lists]
            pkey = self.list_of_primary_keys[index_in_lists]
            try:
                self.fill_db_with_table(table_name, table_content, pkey, cdb)
                print("Successfully created and filled table", table_name, "using files", file_name)
            except:
                print("An error occured when trying to fill table ", table_name, " with file ", file_name)
        try:
            self.metadata_table_creation(cdb)
            print("Successfully created and filled table metadata")
        except:
            print(f"An error occurred while creating metadata table")
        print("\nEnd of script insertdata.py. Closing connexion to PosgreSQL database.\n")
        self.connection.close()


    def table_creation_with_file(self, file_name_in_list:str) -> pd.DataFrame:
        """
        Takes the data in the reference file (exception for metadata table) and add it into a pandas
        dataframe so that it will be added to the database in another function.
        All the data that come from same names files is added to the same dataframe.
        Most of the files are similar to the corresponding table in the database :
        - file name is the same that the database table name.
        - columns names are the same that in the database table.
        - columns order is the same that in the database table.

        But there are some exceptions that are taken in acount in this function:
        - files names don't correspond to table name for 'annotation' and 'protein_annotation' tables.
        - columns names are changing for 'annotation' and 'protein_annotation' tables.
        - there is some syntax modification for 'uniprot_protein' and 'uniprot_keyword'.
        - interactors A and B columns can be changed in some cases so that host protein is in A and bacterial protein in B.
        to fit postgres syntax in the queries.

        Args:
            - file_name_in_list: one file name in the list of files names to search in the reference folder

        Return:
            - table_content: the content of the dataframe to pass in the database table
        """
        # FINDING ALL THE FILES WITH RIGHT NAME IN TABLES FILES DIRECTORY
        list_of_path = []
        # walking through directories to find same names files
        for (path, dirs, files) in os.walk(self.ref_directory):
            for file_name in files:
                if file_name_in_list in file_name:
                    new_path = path + "/" + file_name
                    list_of_path.append(new_path)
        # Adding the first file content to the table
        table_content = pd.read_table(list_of_path[0], header=0)
        # Adding the other species tables one by one
        for i in range(1, len(list_of_path)):
            next_content_part = pd.read_table(list_of_path[i])
            table_content = pd.concat([table_content, next_content_part], axis=0)

        # Search the table_name and p_key that correspond to the file_name (index of the three lists)
        index_in_lists = self.list_of_file_names.index(file_name_in_list)
        table_name = self.list_of_table_names[index_in_lists]
        
        # Changing column names if necessary
        if table_name in ['annotation', 'protein_annotation']:
            return self.change_column_names(table_name, table_content)
        # Changing the syntax in some columns if necessary
        elif table_name in ['uniprot_protein', 'uniprot_keyword']:
            return self.modif_table_content(table_name, table_content)
        # Change A and B interactors so that host protein is in A and bacterial is in B
        elif table_name in ['interaction_full', 'interaction_feature', 'mimicint_interface']:
            return self.exchange_a_and_b_columns(table_name, table_content)
        table_content = table_content.drop_duplicates()
        return table_content


    def add_who_pritority_in_uniprot_taxo(self) -> None:
        """
        Add the who_priority and hazard_group columns to the uniprot_taxonomy_table when informations are present.

        Args: None.

        Return: None.
        """
        merged_df = pd.merge(self.list_of_df[0], self.who_annotations[['taxon_id', 'who_priority', 'hazard_group']], on='taxon_id', how='left')
        self.list_of_df[0] = merged_df
    

    def exchange_a_and_b_columns(self, table_name:str, table_content:pd.DataFrame) -> pd.DataFrame:
        """
        Exchange specified columns between interactor A and B based on the given table name.

        Parameters:
        - table_name (str): The name of the table.
        - table_content (pd.DataFrame): The DataFrame representing the table content.

        Returns:
        - pd.DataFrame: The modified DataFrame after column exchange.
        """
        if table_name == "interaction_full":
            # condition for column exchange : host taxon is in b and need to be in a
            condition = table_content['taxon_interactor_idA'].astype(str).isin(self.list_of_taxa)
            table_content.loc[condition, ['interactor_idB', 'interactor_idA', 'taxon_interactor_idB', 'taxon_interactor_idA']] = \
                table_content.loc[condition, ['interactor_idA', 'interactor_idB', 'taxon_interactor_idA', 'taxon_interactor_idB']].values
            return table_content
        elif table_name == "interaction_feature":
            intersectionA = table_content['interactor_idA'].astype(str).isin(self.list_of_df[6]['interactor_idB'].tolist())
            intersectionB = table_content['interactor_idB'].astype(str).isin(self.list_of_df[6]['interactor_idA'].tolist())
            condition = intersectionA | intersectionB
            table_content.loc[condition, ['interactor_idB', 'feature_interactor_idB', 'feature_start_interactor_idB', 'feature_end_interactor_idB',
                                          'interactor_idA', 'feature_interactor_idA', 'feature_start_interactor_idA', 'feature_end_interactor_idA']] = \
                table_content.loc[condition, ['interactor_idA', 'feature_interactor_idA', 'feature_start_interactor_idA', 'feature_end_interactor_idA',
                                              'interactor_idB', 'feature_interactor_idB', 'feature_start_interactor_idB', 'feature_end_interactor_idB']].values
            return table_content
        elif table_name == "mimicint_interface":
            intersectionA = table_content['interface_interactor_idA'].astype(str).isin(self.list_of_df[6]['interactor_idB'].tolist())
            intersectionB = table_content['interface_interactor_idB'].astype(str).isin(self.list_of_df[6]['interactor_idA'].tolist())
            condition = intersectionA | intersectionB
            table_content.loc[condition, ['interface_interactor_idB', 'interface_type_idB', 'interface_start_interactor_idB', 'interface_end_interactor_idB',
                                          'interface_interactor_idA', 'interface_type_idA', 'interface_start_interactor_idA', 'interface_end_interactor_idA']] = \
                table_content.loc[condition, ['interface_interactor_idA', 'interface_type_idA', 'interface_start_interactor_idA', 'interface_end_interactor_idA',
                                              'interface_interactor_idB', 'interface_type_idB', 'interface_start_interactor_idB', 'interface_end_interactor_idB']].values
            return table_content


    # def fill_db_with_table(self, table_name: str, table_content: pd.DataFrame, p_key: str, cdb: __module__) -> None:
    #     """
    #     Reading the pandas tables and adding the containing data to the database tables using PostgreSQL queries.

    #     Args:
    #         - table_name: name of the table to fill in the database
    #         - table_content: pandas DataFrame created with the reference files (exception for metadata)
    #         - p_key: primary key in the database table
    #         - cdb: module that contains some connection and query execution functions

    #     Return: None
    #     """
    #     # Get column names as a tuple for the SQL query
    #     col_list = table_content.columns.values.tolist()
    #     col_tuple = str(col_list).replace("'",'').replace('[','(').replace(']',')').replace('"', '')
    #     for line_tuple in table_content.itertuples(index=False, name=None):
    #         # Convert NaN values to NULL for PostgreSQL
    #         line_tuple = tuple('NULL' if pd.isna(value) else str(value.replace("'", '')) for value in line_tuple)
    #         # Create the SQL query
    #         query = (
    #             "INSERT INTO " + table_name + " " + col_tuple +
    #             " VALUES " + str(line_tuple).replace('"', "'").replace('\\', '').replace('[', '(').replace(']', ')')
    #         )
    #         # Add ON CONFLICT clause
    #         if p_key is not None:
    #             query += f" ON CONFLICT ({p_key}) DO NOTHING;"
    #         else:
    #             query += " ON CONFLICT DO NOTHING;"
    #         # Execute the query
    #         cdb.execute_query(self.connection, query)

    def fill_db_with_table(self, table_name:str, table_content:pd.DataFrame, p_key:str, cdb:__module__) -> None:
        """
        Reading the pandas tables and adding the containing data to the database tables using postgresql queries.
        Called after dataframe creation with the references files.

        Args:
            - table_name: name of the table to fill in the database
            - table_content: pandas dataframe created with the reference files (exception for metadata)
            - p_key: primary key in the database table
            - cdb: module that contains some connexion and query execution functions

        Return: None
        """
        # takes the columns names from the table
        col_list = table_content.columns.values.tolist()
        # col_list converted to tuple to fit postgres syntax (removing the quotes)
        col_tuple = str(col_list).replace("'",'').replace('[','(').replace(']',')')
        for line_tuple in table_content.itertuples(index=False, name=None):
            # Convert NaN values to NULL for PostgreSQL
            line_tuple = tuple('NULL' if pd.isna(value) else value for value in line_tuple)
            # common query start for all cases
            query = ("INSERT INTO " + table_name + " " + col_tuple + " VALUES " + str(line_tuple).replace('"', "'"))
            # First case : the table as a primary_key
            if p_key is not None:
                query += f" ON CONFLICT ({p_key}) DO NOTHING;"
            # Second case : no primary_key -> unique lines
            else:
                query += " ON CONFLICT DO NOTHING;"
            cdb.execute_query(self.connection, query)
        
        # for line_tuple in table_content.itertuples(index=False, name=None) :
        #     # common query start for all cases
        #     query = ("INSERT INTO " + table_name + " " + col_tuple + " VALUES " + str(line_tuple).replace('"', "'"))
        #     # First case : the table as a primary_key
        #     if p_key != None:
        #         query += (" ON CONFLICT (" + p_key + ") DO NOTHING;")
        #     # Second case : no primary_key -> unique lines
        #     else:
        #         query += (" ON CONFLICT DO NOTHING;")
        #     cdb.execute_query(self.connection, query)


    def metadata_table_creation(self, cdb:__module__) -> None:
        """
        Creation of the metadata table, which is not taken from a file.
        Takes the last data insertion date, python version (and libraries version),and last modification date
        of the reference files for each taxon.
        Directly add the content of the metadata dataframe created here into the database.

        Args:
            - cdb: module that contains some connexion and query execution functions (from createddb.py script)

        Return: None
        """
        table_name = "metadata"
        p_key = 'metadata'

        # Setting date of data insertion
        insert_time = time.strftime("%Y_%m_%d")

        # Taking the version of postgres
        postgres_cursor = self.connection.cursor()
        postgres_cursor.execute("SELECT setting FROM pg_settings WHERE name = 'server_version'")
        postgres_version = postgres_cursor.fetchone()[0]
        postgres_cursor.close()

        # Taking the versions of python and main libraries
        python_version = platform.python_version()
        psycopg2_module_version = psycopg2.__libpq_version__
        pandas_module_version = pd.__version__ 

        # Adding all to the future columns of metadata table
        metadata_list = ["last_update", "postgresql", "python", "psycopg2_PyLib", "Pandas_PyLib"]
        update_version_list = [insert_time, postgres_version, python_version, psycopg2_module_version, pandas_module_version]
    
        # Adding the taxon id of each specie and its last update to the metadata columns
        for specie in self.list_of_taxa:
            file_path = self.ref_directory + specie + "/01_Mentha_host_interaction/mentha_" + specie + "_interactions.txt"
            metadata_list.append("taxon_id_" + specie)
            update_version_list.append(datetime.date.fromtimestamp(os.path.getmtime(file_path)).strftime("%Y_%m_%d"))

        # Setting the metadata.content
        table_content = pd.DataFrame({"metadata" : metadata_list,
                                    "version_or_update" : update_version_list})
        print(table_content)
        # Filling the database table with this one
        self.fill_db_with_table(table_name, table_content, p_key, cdb)

    @staticmethod
    def change_column_names(table_name:str, table_content:pd.DataFrame) -> pd.DataFrame:
        """
        Changing the column names for tables 'annotation' and
        'protein_annotation' to fit the database tables.

        Args:
            - table_name: table to modify ('annotation' or 'protein_annotation').
            - table_content: corresponding pandas dataframe created with the reference files.

        Return:
            - table_content: modified dataframe with the right column names and order.
        """
        if table_name == 'annotation':
            table_content.rename(columns={"id":"annotation_id",
                                          "Description":"annotation_description"}, inplace=True)
        elif table_name == 'protein_annotation':
            table_content.rename(columns={"query_bactmentha":"uniprot_ac",
                                          "target_ac":"annotated_sequence",
                                          "source":"annotation_source",
                                          "annotation":"annotation_id",
                                          "inference":"inference_method",
                                          "seq_identity":"sequence_identity"}, inplace=True)
        return table_content

    @staticmethod
    def modif_table_content(table_name:str, table_content:pd.DataFrame) -> pd.DataFrame:
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
    

    def add_annotations_in_intfull(self) -> None:
        """
        Modification of the interaction_full table to add the Annotations column.
        In this column will be the results of three psql queries to find if the 
        interaction of the line is related to some data in other tables :
        - mimicint_interface
        - interaction_feature
        - protein_annotation (for at least one of the interactors)

        The data in the annotation column will be "ddd" where d can be either 't' or 'f'
        depending if some informations were found or not.

        Args: None

        Return: None
        """
        # Creates empty 'annotations' column
        annotations = []
        int_full = self.list_of_df[6]                                   # interaction_full content
        mimi_xref = self.list_of_df[10]["mnt_interaction_id"]            # bm_id in mimicint_xref
        int_feat = self.list_of_df[7]["mnt_interaction_id"]              # bm_id in interaction_feature
        prot_annot_ac = self.list_of_df[4]["uniprot_ac"]                # uniprot_ac in protein_annotation
        uniprot_xref = self.list_of_df[5]                               # uniprot_ac and interactor_id in uniprot_xref
        # iterates through all the rows of the table
        for index, row in int_full.iterrows():
            # extract the bm_id column from the dataframe
            bm_id = row["mnt_interaction_id"]
            interactorA = row["interactor_idA"]
            # Search in mimicint xref   
            if bm_id in mimi_xref.values:
                mimi_result = 't'
            else:
                mimi_result = 'f'
            # Search in interaction_feature
            if bm_id in int_feat.values:
                int_feat_result = 't'
            else:
                int_feat_result = 'f'
            # Search in protein_annotation
            unip_ac_a = uniprot_xref.loc[uniprot_xref['interactor_id'] == interactorA]["uniprot_ac"].iloc[0]
            if unip_ac_a in prot_annot_ac.values:
                prot_annot_result = 't'
            else:
                prot_annot_result = 'f'
            result = mimi_result+int_feat_result+prot_annot_result
            # creates the new value of the column
            annotations.append(result)
        # add the new column to the dataframe
        self.list_of_df[6] = int_full.assign(annotations=annotations)




    def cursor_query_execution(self, query:str) -> str:
        """
        Creates a cursor object and executes a query. Returns the first result of the query.

        Args:
            - query: psql query to execute using psycopg2 connection to the database.

        Return:
            - result: the first result of the query.
        """
        postgres_cursor = self.connection.cursor()
        postgres_cursor.execute(query)
        result = postgres_cursor.fetchone()[0] # to avoid the comma at the end
        postgres_cursor.close()
        return result



if __name__ == "__main__":

    # Instantiate the parser
    parser = argparse.ArgumentParser(prog='BactMentha Data Insertion',
                                    description='This program creates the database tables using the given reference data. \
                                                It also creates the views for the web visualisation.')
   # Add the parser arguments
    parser.add_argument('--db_name', '--dbn', type=str, required=True, nargs=1,
                        help='the name of the database. Ex: "bactmentha_db"')
    parser.add_argument('--db_host_name', '--dbh', type=str, required=True, nargs=1, 
                        help='Host name, name of the service that contains \
                        the database if you are using a docker .yml file. Ex:"db"')
    parser.add_argument('--db_user_name', '--dbu', type=str, required=True, nargs=1,
                        help='the name of the database user. Ex: "postgres"')
    parser.add_argument('--db_password', '--dbpw', type=str, required=True, nargs=1,
                        help='Password for the database. Ex: "postgres"')
    parser.add_argument('--db_port', '--dbpt', type=str, required=True, nargs=1,
                        help='Port to connect to the database, defined in the .yml file. Ex: "5432"')
    parser.add_argument('--reference_path', '--refpath', type=str, required=True, nargs=1,
                        help='Path to the reference file that contains a subfolder for each taxon files. \
                            This is the output folder from the BactMentha workflow. \
                            Ex: "/BactMentha/Data/01_Reference/01_DatabaseTables/"')
    parser.add_argument('--path_to_createdb', '--cdbpath', type=str, required=True, nargs=1,
                        help='Path to the script for database creation, to use some of the \
                            creation script functions for database connection. \
                            Ex: "/BactMentha/Workspace/tagc-bactmentha-db/03_Script/01_CreateDatabase"')
    parser.add_argument('--who_annotations_path', '--whopath', type=str, required=True, nargs=1,
                        help='Path to the WHO priority annotations file (.txt). \
                        Ex: "/BactMentha/01_Reference/02_who_annotations/bactmentha_taxa_who-hazard_annotations.txt"')

    args = parser.parse_args()

    insertion = Insertion(args.db_name[0], args.db_host_name[0], args.db_user_name[0], args.db_password[0], args.db_port[0],
                    args.reference_path[0], args.path_to_createdb[0], args.who_annotations_path[0])
