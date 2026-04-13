# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Scirpt to update the database and archive the new version.
"""

# Standard libraries imports
#import psycopg2
import zipfile
import warnings
from sys import path
from distutils.dir_util import copy_tree
import shutil
import os
import pandas as pd
import argparse
from createStatGraphs import createGraphs
from computeMutationsStats import computeMutationsStats

class Update():
    """
    Updating and archiving the database tables.
    The script createdb.py has to run at least once before to run this script.
    Truncate the tables to erase all the data it contains.
    Calls insertdata.py script module to fill the tables with the new data.
    Creates the archive file for the new version of the database.
    """

    def __init__(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str, reference_path:str, archive_path:str, 
                 path_to_createdb:str, path_to_insertdata:str, who_annotations_path:str) -> None:
        """
        Args:
            - db_name: name of the database to connect to (defined in the docker compose file / .env file)
            - db_host_name: name of the database host (In the docker compose file it is the service that contain the database)
            - db_user_name: user if often 'postgres' (defined in the docker compose file / .env file)
            - db_pw: the database password (defined in the docker compose file / .env file)
            - db_port: port to listen from the docker container (defined in the docker compose file / .env file)
            - reference_path: path to the raw data folder that contains a subfolder for each taxon (defined in the docker compose file / .env file)
            - archive_path: path to the archive folder in which to save the compress folder with the new data after update (defined in the docker compose file / .env file)
            - path_to_createdb: path to the tables and views cretaion script (defined in the docker compose file / .env file)
            - path_to_insertdata: path to the data_insertion script (defined in the docker compose file / .env file)

        Return: None (Calls the main function to do the update and archiving)
        """
        # Other scripts imports
        path.append(path_to_createdb)
        path.append(path_to_insertdata)
        from createdb import Createdb as cdb
        from insertdata import Insertion

        # ATTRIBUTES
        self.connection = cdb.create_server_connection(db_name, db_host_name, db_user_name, db_pw, db_port)
        self.reference_path = reference_path
        self.input_archive_path = archive_path
        self.list_taxon_dir = [tax for tax in os.listdir(self.reference_path)]
        
        self.list_of_file_names = ["uniprot_taxonomy.txt", "uniprot_protein.txt", "uniprot_keyword.txt",
                                   "annotation_description_table.txt", "bacterial_annotation_table.txt",
                                   "uniprot_xref.txt", "interaction_full.txt", "interaction_feature.txt",
                                   "mimicint_interface.txt", "imex_xref.txt", "mimicint_xref.txt"]
        self.list_of_table_names = ["uniprot_taxonomy", "uniprot_protein", "uniprot_keyword", "annotation",
                                    "protein_annotation", "uniprot_xref", "interaction_full", "interaction_feature",
                                    "mimicint_interface", "imex_xref", "mimicint_xref", "metadata"]
        self.graph_creator = None
        self.who_annotations_folder_path = "/".join(who_annotations_path.split("/")[0:-1])

        # FUNCTION TO RUN
        self.update_and_archive_data(db_name, db_host_name, db_user_name, db_pw, db_port, reference_path, path_to_createdb, cdb, Insertion, who_annotations_path)
        self.connection.close()


    def update_and_archive_data(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str,
                                reference_path:str, path_to_createdb:str, cdb:__module__, Insertion:__module__, who_annotations_path:str) -> None:
        """
        Main function for the update and the archiving. Saves a copy of the used reference files in a raw data subfolder.
        Creation of an archive table file for each database table in a dedicated subfolder. Creation of an archive dump for the database.

        Args:
            - db_name: name of the database to connect to (defined in the docker compose file / .env file)
            - db_host_name: name of the database host (In the docker compose file it is the service that contain the database)
            - db_user_name: user if often 'postgres' (defined in the docker compose file / .env file)
            - db_pw: the database password (defined in the docker compose file / .env file)
            - db_port: port to listen from the docker container (defined in the docker compose file / .env file)
            - reference_path: path to the raw data folder that contains a subfolder for each taxon (defined in the docker compose file / .env file)
            - path_to_createdb: path to the tables and views cretaion script (defined in the docker compose file / .env file)
            - cdb: module that contains some connexion and query execution functions (from createdb.py script)
            - Insertion: module that is imported to insert the new files data for each update (from insertdata.py script)

        Return: None
        """
        # Starting the connexion
        print("\nRunning script updatedb.py\n")
        self.connection = cdb.create_server_connection(db_name, db_host_name, db_user_name, db_pw, db_port)
        # Deleting old data
        print("\n##### DELETION OF TABLES DATA #####\n")
        self.drop_tables(cdb)
        self.drop_database_cache()
        # Adding new data
        print("\n##### NEW DATA INSERTION #####\n")
        self.new_data_insertion(db_name, db_host_name, db_user_name, db_pw, db_port, reference_path, path_to_createdb, Insertion, who_annotations_path)
        # Archiving the new data
        print("\n##### ARCHIVE CREATION #####\n")
        self.create_archive_version_paths()
        self.archive_folders_creation()
        self.fill_taxaRawData_archive_dir()
        self.fill_completeRawData_archive_dir()
        self.fill_completeDatabaseTables_archive_dir()
        self.fill_taxaDatabaseTables_archive_dir()
        self.fill_dump_archive_dir(db_name, db_host_name, db_user_name, db_pw, db_port)
        self.fill_complete_archive_dir()
        self.compressing_archive_files()
        print("\n##### CREATION OF WEBSITE GRAPHS #####\n")
        createGraphs(path_to_createdb, db_name, db_host_name, db_user_name, db_pw, db_port)
        print("\nEnd of script updatedb.py. Closing connexion to PosgreSQL database.\n")        

    def drop_tables(self, cdb:__module__) -> None:
        """
        Deletion of all data that contain the database tables.

        Args:
            - cdb: module that contains some connexion and query execution functions (from createdb.py script)

        Return: None
        """
        # Deletion of data in tables creating from files
        for table_name in self.list_of_table_names:
            drop_table_query = "TRUNCATE " + table_name + " CASCADE;"
            cdb.execute_query(self.connection, drop_table_query)
            print("Table", table_name, "is now empty.")


    def drop_database_cache(self) -> None:
        """
        Deletion of the database cache.
        DISCARD ALL cannot run in a transaction block (what is done by the execute_query() function).
        To avoid errors due do that, creates a cursor object and add an autocommit to run the command.

        Args: None

        Return: None
        """
        print("Deleting database cache")
        drop_cache_cursor = self.connection.cursor()
        self.connection.autocommit = True
        drop_cache_cursor.execute("DISCARD ALL;")
        drop_cache_cursor.close()
        print("Successfully droped the database cache")

    @staticmethod
    def new_data_insertion(db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str,
                           reference_path:str, path_to_createdb:str, Insertion:__module__, who_annotations_path:str) -> None:
        """
        Insertion of the new data in the database tables using new files from mimicint workflow.
        Running the Insertion class from insertdata.py.

        Args:
            - db_name: name of the database to connect to (defined in the docker compose file / .env)
            - db_host_name: name of the database host (In the docker compose file it is the service that contain the database)
            - db_user_name: user if often 'postgres' (defined in the docker compose file / .env)
            - db_pw: the database password (defined in the docker compose file / .env)
            - db_port: port to listen from the docker container (defined in the docker compose file / .env)
            - reference_path: path to the raw data folder that contains a subfolder for each taxon (defined in the docker compose file / .env)
            - path_to_createdb: path to the tables and views cretaion script (defined in the docker compose file / .env)
            - Insertion: module that is imported to insert the new files data for each update (from insertdata.py script)

        Return: None
        """
        Insertion(db_name, db_host_name, db_user_name, db_pw, db_port, reference_path, path_to_createdb, who_annotations_path)
        print("End of data insertion")


    def get_archive_version(self) -> str:
        """
        Creates a new archive for the new version of the database just after data insertion.
        
        Args: None

        Return: None
        """
        # Setting the date of database creation (and not update)
        archive_cursor = self.connection.cursor()
        # Taking the last insertion date from metadata table
        archive_cursor.execute("SELECT version_or_update FROM metadata WHERE metadata = 'last_update'")
        archive_version = archive_cursor.fetchone()[0]
        archive_cursor.close()
        # The version will be in the name of the archive directory
        return archive_version


    def create_archive_version_paths(self) -> None:
        """
        Set the paths of the archive folders and files to create for the new update of the database.
        
        Args: None.
        
        Return: None.
        """
        self.archive_version = self.get_archive_version()
        self.main_archive_dir = self.input_archive_path + "/bm_archive_" + self.archive_version
        self.complete_archive_dir = self.main_archive_dir + "/bm_archive_" + self.archive_version + "_complete"
        self.completeRawData_archive_dir = self.main_archive_dir + "/bm_archive_" + self.archive_version + "_rawdata"
        self.taxaRawData_archive_dirs = [self.completeRawData_archive_dir + "_" + tax for tax in self.list_taxon_dir]
        self.completeDatabaseTables_archive_dir = self.main_archive_dir + "/bm_archive_" + self.archive_version + "_databasetables"
        self.taxaDatabaseTables_archive_dirs = [self.completeDatabaseTables_archive_dir + "_" + tax for tax in self.list_taxon_dir]
        self.dump_archive_dir = self.main_archive_dir + "/bm_archive_" + self.archive_version + "_dump"
        self.complete_archive_dir_rawData = self.complete_archive_dir + "/raw_data"
        self.complete_archive_dir_databaseTables = self.complete_archive_dir + "/database_tables"
        self.complete_archive_dir_dump = self.complete_archive_dir + "/dump"
        self.listOfPathToArchiveDirs = [
            self.main_archive_dir,
            self.complete_archive_dir,
            self.completeRawData_archive_dir,
            *self.taxaRawData_archive_dirs,
            self.completeDatabaseTables_archive_dir,
            *self.taxaDatabaseTables_archive_dirs,
            self.dump_archive_dir,
            self.complete_archive_dir_rawData,
            self.complete_archive_dir_databaseTables,
            self.complete_archive_dir_dump
        ]
        self.foldersToCompress = self.listOfPathToArchiveDirs[1:-3]


    def checkIfExistPaths(self) -> list:
        """
        For all the paths in self.listOfPathToArchiveDirs, check is the path has already been created or not.

        Args: None.

        Returns:
            - listExistPaths: the list of booleans (True if path exist, else false)
        """
        listExistPaths = []
        for DirPath in self.listOfPathToArchiveDirs:
            listExistPaths.append(os.path.exists(DirPath))
        return listExistPaths


    def get_taxon_tableQuery(self, tableName:str, taxonID:str) -> str:
        """
        Retrieve the query to get all the data from one specified taxon in a specific table.

        Args:
            - tableName: name of the table from which the data will be taken.
            - taxonID: identifier of the host taxon for which we want to get the data.

        Returns:
            - the query (str) for the table and the host taxon.
        """
        queryDict = {
            "uniprot_taxonomy": f"""SELECT DISTINCT UT.* FROM uniprot_taxonomy AS UT 
                                INNER JOIN uniprot_protein AS UP ON UP.uniprot_taxon_id = UT.taxon_id 
                                INNER JOIN uniprot_xref AS UX ON UX.uniprot_ac = UP.uniprot_ac 
                                INNER JOIN interaction_full AS IF ON IF.interactor_ida = UX.interactor_id OR IF.interactor_idb = UX.interactor_id 
                                WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "uniprot_protein": f"""SELECT DISTINCT UP.* FROM uniprot_protein AS UP 
                                INNER JOIN uniprot_xref AS UX ON UX.uniprot_ac = UP. uniprot_ac 
                                INNER JOIN interaction_full AS IF ON IF.interactor_ida = UX.interactor_id OR IF.interactor_idb = UX.interactor_id 
                                WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "uniprot_keyword": f"""SELECT DISTINCT UK.* FROM uniprot_keyword AS UK 
                                INNER JOIN uniprot_protein AS UP ON UP.uniprot_ac = UK.uniprot_ac 
                                INNER JOIN uniprot_xref AS UX ON UX.uniprot_ac = UP.uniprot_ac 
                                INNER JOIN interaction_full AS IF ON IF.interactor_ida = UX.interactor_id OR IF.interactor_idb = UX.interactor_id 
                                WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "annotation": f"""SELECT DISTINCT A.* FROM annotation AS A 
                                INNER JOIN protein_annotation AS PA ON PA.annotation_id = A.annotation_id 
                                INNER JOIN uniprot_protein AS UP ON UP.uniprot_ac = PA.uniprot_ac 
                                INNER JOIN uniprot_xref AS UX ON UX.uniprot_ac = UP.uniprot_ac 
                                INNER JOIN interaction_full AS IF ON IF.interactor_ida = UX.interactor_id OR IF.interactor_idb = UX.interactor_id 
                                WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "protein_annotation": f"""SELECT DISTINCT PA.* FROM protein_annotation AS PA 
                                INNER JOIN uniprot_protein AS UP ON UP.uniprot_ac = PA.uniprot_ac 
                                INNER JOIN uniprot_xref AS UX ON UX.uniprot_ac = UP.uniprot_ac 
                                INNER JOIN interaction_full AS IF ON IF.interactor_ida = UX.interactor_id OR IF.interactor_idb = UX.interactor_id 
                                WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "uniprot_xref": f"""SELECT DISTINCT UX.* FROM uniprot_xref AS UX 
                            INNER JOIN interaction_full AS IF ON IF.interactor_ida = UX.interactor_id OR IF.interactor_idb = UX.interactor_id 
                            WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "interaction_full": f"""SELECT DISTINCT * FROM interaction_full WHERE taxon_interactor_idb = '{taxonID}' ;""",
            "interaction_feature": f"""SELECT DISTINCT FEAT.* FROM interaction_feature AS FEAT 
                                    INNER JOIN interaction_full AS IF ON FEAT.mnt_interaction_id = IF.mnt_interaction_id 
                                    WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "mimicint_interface": f"""SELECT DISTINCT * FROM mimicint_interface AS MI 
                                    INNER JOIN mimicint_xref AS MX ON MI.mimicint_interaction_id = MX.mimicint_interaction_id 
                                    INNER JOIN interaction_full AS IF ON MX.mnt_interaction_id = IF.mnt_interaction_id 
                                    WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "imex_xref": f"""SELECT DISTINCT IX.* FROM imex_xref AS IX 
                            INNER JOIN interaction_full AS IF ON IF.mnt_interaction_id = IX.mnt_interaction_id 
                            WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "mimicint_xref": f"""SELECT DISTINCT MX.* FROM mimicint_xref AS MX 
                            INNER JOIN interaction_full AS IF ON MX.mnt_interaction_id = IF.mnt_interaction_id 
                            WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "metadata": "SELECT * FROM metadata;",
        }
        return queryDict[tableName]


    def save_csv_table(self, taxonID:str, table_name:str, query:str, folder_path:str) -> None:
        """
        Runs a query to take into a pandas dataframe the data from a database table for a specified taxon (or not).
        And save the corresponding table into a csv file in the specified folder.

        Args:
            - taxonID: identifier of the taxon (if not empty) for which the data is retrieved to be added to the csv file name.
            - table_name: name of the table that has been queried to be added to the csv file name.
            - query: the sql query to run for the specified taxon and table name.
            - folder_path: the path of the folder in which the csv file will be saved.

        Returns: None.
        """
        with warnings.catch_warnings():
            warnings.simplefilter("ignore", category=UserWarning)
            content = pd.read_sql_query(query, self.connection)
        if taxonID != "":
            absolute_path = folder_path + "/" + table_name + "_" + taxonID + ".csv"
            content.to_csv(path_or_buf = absolute_path,
                            sep = "\t",
                            header = True,
                            index = False,
                            index_label = None)
            print("Successfully saved", table_name, "into a csv file for taxon ", taxonID)
        else:
            absolute_path = folder_path + "/" + table_name + ".csv"
            content.to_csv(path_or_buf = absolute_path,
                            sep = "\t",
                            header = True,
                            index = False,
                            index_label = None)
            print("Successfully saved", table_name, "into a csv file for all taxa")


    def archive_folders_creation(self) -> None:
        """
        Creating the archive folders for the new database version.
        Folder structure :
        + main_archive_dir
            + complete_archive_dir
                + complete_archive_dir_rawData
                + complete_archive_dir_databaseTables
                + complete_archive_dir_dump
            + completeRawData_archive_dir
                + All the reference folder for each taxon
            + taxaRawData_archive_dirs (one subfolder per taxon)
                + Reference files per taxon
            + completeDatabaseTables_archive_dir
                + tables in csv format (for all taxa)
            + taxaDatabaseTables_archive_dirs
                + tables for each taxon_id in csv format
            + dump_archive_dir
                + dump file

        Args: None

        Return: none
        """
        existPath = self.checkIfExistPaths()
        # creates the folders if not exist
        for i in range(len(existPath)):
            if not existPath[i]:
                os.mkdir(self.listOfPathToArchiveDirs[i])
        print("\nSuccessfully created archive directories")


    def fill_taxaRawData_archive_dir(self) -> None:
        """
        Retrieve and copy the reference files per host taxon in corresponding directories.

        Args: None

        Return: None
        """
        for taxonArchiveDir in self.taxaRawData_archive_dirs:
            path_to_copy = self.reference_path + taxonArchiveDir.split("_")[-1]
            copy_tree(path_to_copy,taxonArchiveDir)
        print("Successfully filled the raw data archive folders per host taxon")


    def fill_completeRawData_archive_dir(self) -> None:
        """
        Retrieve and copy the reference files for all taxa in corresponding directory.

        Args: None

        Return: None
        """
        copy_tree(self.reference_path, self.completeRawData_archive_dir)
        copy_tree(self.who_annotations_folder_path, self.completeRawData_archive_dir)
        print("Successfully filled the complete raw data archive folder")


    def fill_completeDatabaseTables_archive_dir(self) -> None:
        """
        Converting the database tables to pandas dataframes.
        Saving the pandas dataframes as csv files in the corresponding directory.

        Args: None

        Return: None
        """
        # For all the tables in the list
        for table_name in self.list_of_table_names:
            query = "SELECT * FROM " + table_name + ";"
            self.save_csv_table("", table_name, query, self.completeDatabaseTables_archive_dir)


    def fill_taxaDatabaseTables_archive_dir(self) -> None:
        """
        Retrieve the database table rows that concern each host taxon.
        Saving the pandas dataframes as csv files in the corresponding directory for each taxon.

        Args: None

        Return: None
        """
        for dir_path in self.taxaDatabaseTables_archive_dirs:
            taxon = dir_path.split("_")[-1]
            for table_name in self.list_of_table_names:
                query = self.get_taxon_tableQuery(table_name, taxon)
                self.save_csv_table(taxon, table_name, query, dir_path)


    def fill_dump_archive_dir(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str) -> None:
            """
            Creating the dump file to save the whole database into.
            Provides a way to restore the database version easily.

            Args: connection parameters
                - db_name: name of the database to connect to (defined in the docker compose file)
                - db_host_name: name of the database host (In the docker compose file it is the service that contain the database)
                - db_user_name: user if often 'postgres' (defined in the docker compose file)
                - db_pw: the database password (defined in the docker compose file)
                - db_port: port to listen from the docker container (defined in the docker compose file)

            Return: None
            """
            # Setting the database parameters for the dump
            dump_file_path = self.dump_archive_dir + "/bm_" + self.archive_version + "_backup.dmp"
            #dump command explained : pg_dump postgres://username:password@postgres_server:5432/databasename
            db_connection_string = 'postgresql://'+db_user_name+':'+db_pw+'@' + db_host_name + ':' + db_port + '/' + db_name
            try:
                # Database connection parameters + default custom compression paramater for output format
                dump_backup_command = 'pg_dump ' + db_connection_string + ' --format=plain --no-owner > ' + dump_file_path
                os.system(dump_backup_command)
                print("Successfully created the dump file.")
            except:
                with open(dump_file_path, 'w') as dump_file:
                    dump_file.write("ERREUR")
                print("Error when writing the dump file")


    def fill_complete_archive_dir(self) -> None:
        """
        Copy the content of the complete raw data, complete database tables and dump archive folder in the complete archive folder.

        Args: None.

        Return: None.
        """
        copy_tree(self.completeRawData_archive_dir, self.complete_archive_dir_rawData)
        copy_tree(self.completeDatabaseTables_archive_dir, self.complete_archive_dir_databaseTables)
        copy_tree(self.dump_archive_dir, self.complete_archive_dir_dump)
        for taxon_dir in self.taxaDatabaseTables_archive_dirs:
            copy_tree(taxon_dir, self.complete_archive_dir_databaseTables)


    def folder_compression(self, new_zip_name:str, folder_to_zip:str) -> None:
        """
        Compression of the archive directory into a zip file that will contain the same
        structure than the archive directory. Called by the compressing_archive_files function that compress
        the subfolder of the main archive folder.

        Args:
            - new_zip_name: name of the compressed archive file
            - folder_to_zip: archive file to compress

        Return: None
        """
        # Creating the zip folder for the archive version
        with zipfile.ZipFile(new_zip_name, "w", zipfile.ZIP_DEFLATED) as archive_file:
            # Go through the directory and copy all files and subdirectories
            for root, dirs, files in os.walk(folder_to_zip):
                for file in files:
                    file_name = os.path.join(root, file)
                    archive_file.write(file_name, os.path.relpath(file_name, folder_to_zip))    


    def compressing_archive_files(self) -> None:
        """
        For each folder to compress, compress it into a .zip file and erase the previous unzipped folder.

        Args: None

        Return : None
        """
        for folder in self.foldersToCompress:
            self.folder_compression(folder+".zip", folder)
            shutil.rmtree(folder, ignore_errors=True)
        print("Successfully compressed the archive files")



if __name__ == "__main__":

    # Instantiate the parser
    parser = argparse.ArgumentParser(prog='BactMentha Database Update and Archiving',
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
    parser.add_argument('--path_to_insertdata', '--idbpath', type=str, required=True, nargs=1,
                        help='Path to the data insertion script, that will be called for each update. \
                            Ex: "/BactMentha/Workspace/tagc-bactmentha-db/03_Script/02_InsertData"')
    parser.add_argument('--archive_path', '--arcpath', type=str, required=True, nargs=1,
                        help='Path to the archive folder. \
                        It is recommanded to put the archive folder into the Output folder. \
                        Ex: "/BactMentha/Data/05_Output/02_Archive"')
    parser.add_argument('--who_annotations_path', '--whopath', type=str, required=True, nargs=1,
                        help='Path to the WHO priority annotations file (.txt). \
                        Ex: "/BactMentha/01_Reference/02_who_annotations/bactmentha_taxa_who-hazard_annotations.txt"')
    
    args = parser.parse_args()

    update = Update(args.db_name[0], args.db_host_name[0], args.db_user_name[0], args.db_password[0], args.db_port[0],
                    args.reference_path[0], args.archive_path[0], args.path_to_createdb[0], args.path_to_insertdata[0],
                    args.who_annotations_path[0])
