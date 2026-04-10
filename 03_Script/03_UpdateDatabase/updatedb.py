# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Scirpt to update the database and archive the new version.
"""

# Standard libraries imports
#import psycopg2
import zipfile
from sys import path
from distutils.dir_util import copy_tree
import shutil
import os
import pandas as pd
import argparse
from createStatGraphs import createGraphs

class Update():
    """
    Updating and archiving the database tables.
    The script createdb.py has to run at least once before to run this script.
    Truncate the tables to erase all the data it contains.
    Calls insertdata.py script module to fill the tables with the new data.
    Creates the archive file for the new version of the database.
    """

    def __init__(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str, reference_path:str, archive_path:str, 
                 path_to_createdb:str, path_to_insertdata:str) -> None:
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
        self.connection = None
        self.archive_version = None
        self.raw_data_dir = reference_path
        self.archive_dir = archive_path
        self.archive_version_path = None
        self.compressed_archive_path = None
        self.list_taxon_dir = [tax for tax in os.listdir(self.raw_data_dir)]
        self.list_of_file_names = ["uniprot_taxonomy.txt", "uniprot_protein.txt", "uniprot_keyword.txt",
                                   "annotation_description_table.txt", "bacterial_annotation_table.txt",
                                   "uniprot_xref.txt", "interaction_full.txt", "interaction_feature.txt",
                                   "mimicint_interface.txt", "imex_xref.txt", "mimicint_xref.txt"]
        self.list_of_table_names = ["uniprot_taxonomy", "uniprot_protein", "uniprot_keyword", "annotation",
                                    "protein_annotation", "uniprot_xref", "interaction_full", "interaction_feature",
                                    "mimicint_interface", "imex_xref", "mimicint_xref", "metadata"]
        self.graph_creator = None

        # FUNCTION TO RUN
        self.update_and_archive_data(db_name, db_host_name, db_user_name, db_pw, db_port, reference_path, path_to_createdb, cdb, Insertion)


    def update_and_archive_data(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str,
                                reference_path:str, path_to_createdb:str, cdb:__module__, Insertion:__module__) -> None:
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
        self.new_data_insertion(db_name, db_host_name, db_user_name, db_pw, db_port, reference_path, path_to_createdb, Insertion)
        # Archiving the new data
        print("\n##### ARCHIVE CREATION #####\n")
        self.set_archive_version_and_path()
        self.archive_folder_creation()
        self.archiving_raw_data_files()
        self.archiving_database_tables()
        self.archiving_database_dump(db_name, db_host_name, db_user_name, db_pw, db_port)
        self.compressing_archive_files()
        print("\n##### CREATION OF WEBSITE GRAPHS #####\n")
        createGraphs(path_to_createdb, db_name, db_host_name, db_user_name, db_pw, db_port)
        print("\nEnd of script updatedb.py. Closing connexion to PosgreSQL database.\n")
        self.connection.close()


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
                           reference_path:str, path_to_createdb:str, Insertion:__module__) -> None:
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
        Insertion(db_name, db_host_name, db_user_name, db_pw, db_port, reference_path, path_to_createdb)
        print("End of data insertion")


    def set_archive_version_and_path(self) -> None:
        """
        Creates a new archive for the new version of the database just after data insertion.
        
        Args: None

        Return: None
        """
        # Setting the date of database creation (and not update)
        archive_cursor = self.connection.cursor()
        # Taking the last insertion date from metadata table
        archive_cursor.execute("SELECT version_or_update FROM metadata WHERE metadata = 'last_update'")
        self.archive_version = archive_cursor.fetchone()[0]
        archive_cursor.close()
        # The version will be in the name of the archive directory
        self.archive_version_path = self.archive_dir + "/bactmentha_version_" + self.archive_version


    def archive_folder_creation(self) -> None:
        """
        Creating the archive folder for the new database version.
        Folder structure :
        + Archive_version_number
            + Archive_Database_tables
                + Tables.csv
            + Archive_raw_Data
                + One subfolder per taxon_id
                    + Tables for each taxon_id in text files
            + Dump_file

        Args: None

        Return: none
        """
        # Setting the path of the directories to create inside the archive folder
        archive_raw_data = self.archive_version_path + "/raw_data"
        archive_database_tables = self.archive_version_path + "/database_tables"
        # creating archive directories if not exist
        exist_condition = []
        path_to_create = []
        isExist_archive = os.path.exists(self.archive_version_path)
        isExist_subdir_rawdata = os.path.exists(archive_raw_data)
        isExist_archive_database_tables = os.path.exists(archive_database_tables)
        exist_condition = [isExist_archive,
                           isExist_subdir_rawdata, isExist_archive_database_tables]
        path_to_create = [self.archive_version_path,
                          archive_raw_data, archive_database_tables]
        # If the directory does not exist, it is created
        for i in range(len(exist_condition)):
            if not exist_condition[i]:
                os.mkdir(path_to_create[i])
        # Creating sub-folders for each taxon in raw_data archive
        for taxon_dir in self.list_taxon_dir:
            isExist_taxon_dir = os.path.exists(archive_raw_data + "/" + taxon_dir)
            if not isExist_taxon_dir:
                os.mkdir(archive_raw_data + "/" + taxon_dir)
        print("\nSuccessfully created archive directories")


    def archiving_raw_data_files(self) -> None:
        """
        Saving the raw data that was used to create the database tables.
        Search the files in the reference files and save the data for each taxon in a different subfolder.

        Args: None

        Return: none
        """
        # Setting the path to the raw_data subfolder from archive
        archive_subdir_raw_data = self.archive_version_path + "/raw_data"
        # Creating the subfolders for each taxon if they exist
        for taxon_dir in self.list_taxon_dir:
            path_to_copy = self.raw_data_dir+taxon_dir
            isExist_taxon_dir = os.path.exists(archive_subdir_raw_data + "/" + taxon_dir)
            if not isExist_taxon_dir:
                os.mkdir(archive_subdir_raw_data + "/" + taxon_dir)
                print("Successfully created archive subdirectory for taxon ", taxon_dir)
            path_to_archive = archive_subdir_raw_data + "/" + taxon_dir
            copy_tree(path_to_copy,path_to_archive)
        print("Successfully added the raw data")


    def archiving_database_tables(self) -> None:
        """
        Converting the database tables to pandas dataframes.
        Saving the pandas dataframes as files.

        Args: None

        Return: None
        """
        # For all the tables in the list
        for table_name in self.list_of_table_names:
            query = "SELECT * FROM " + table_name + ";"
            # Create a pandas dataframe
            content = pd.read_sql_query(query, self.connection)
            absolute_archive_file_path = self.archive_version_path + "/database_tables/bm_" + self.archive_version + "_" + table_name + ".csv"
            # Put the content into a csv file in the archive folder
            content.to_csv(path_or_buf = absolute_archive_file_path,
                           sep = "\t",
                           header = True,
                           index = False,
                           index_label = None)
            print("Successfully saved", table_name, "into a csv file.")


    def archiving_database_dump(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str) -> None:
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
        dump_file_path = self.archive_version_path + "/bm_" + self.archive_version + "_backup.dmp"
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
        Set the parameters for the archive subfolder compression.

        Args: None

        Return : None
        """
        # full compressed folder:
        self.folder_compression(self.archive_version_path+".zip", self.archive_version_path)
        # Deleting the uncompress containinf archive file
        shutil.rmtree(self.archive_version_path, ignore_errors=True)
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
    
    args = parser.parse_args()

    update = Update(args.db_name[0], args.db_host_name[0], args.db_user_name[0], args.db_password[0], args.db_port[0],
                    args.reference_path[0], args.archive_path[0], args.path_to_createdb[0], args.path_to_insertdata[0])
