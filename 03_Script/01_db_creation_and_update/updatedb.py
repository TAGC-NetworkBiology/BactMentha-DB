# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Scirpt to update the database and archive the new version.
"""

# Standard libraries imports
#import psycopg2
from zipfile import ZipFile, ZIP_DEFLATED
from io import StringIO
from sys import path
from distutils.dir_util import copy_tree
from shutil import copy2
from os import path, listdir, makedirs, walk, system
import pandas as pd
import argparse

from bactmentha_db_tools.db_tasks import Db_tasks
from createStatGraphs import createGraphs
#from bactmentha_db_tools.computeMutationsStats import computeMutationsStats
from insertdata import Insertion

from constants import SCRIPT, REFERENCE, ARCHIVES, DB_TABLES, TABLES_PATHS, AF_PRED, WHO_ANNOT, CELL_COMP

class Update():
    """
    Updating and archiving the database tables.
    The script createdb.py has to run at least once before to run this script.
    Truncate the tables to erase all the data it contains.
    Calls insertdata.py script module to fill the tables with the new data.
    Creates the archive file for the new version of the database.
    """

    def __init__(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str) -> None:
        """
        Args:
            - db_name: name of the database to connect to (defined in the docker compose file / .env file)
            - db_host_name: name of the database host (In the docker compose file it is the service that contain the database)
            - db_user_name: user if often 'postgres' (defined in the docker compose file / .env file)
            - db_pw: the database password (defined in the docker compose file / .env file)
            - db_port: port to listen from the docker container (defined in the docker compose file / .env file)
        """
        self.bactmentha_db = Db_tasks(db_name, db_host_name, db_user_name, db_pw, db_port)
        self.host_taxa = [h for h in listdir(path.join(REFERENCE, "01_DatabaseTables"))]
        self.tables_names = self.get_tables()
        self.main(db_name, db_host_name, db_user_name, db_pw, db_port)


    def main(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str) -> None:
        """
        Run the Data Insertion script to fill the bactmentha database.
        """
        print("\nRunning script updatedb.py\n")
        print("\n##### DELETION OF TABLES DATA #####\n")
        self.drop_tables()
        self.drop_database_cache()
        print("\n##### NEW DATA INSERTION #####\n")
        Insertion(db_name, db_host_name, db_user_name, db_pw, db_port)
        print("\n##### ARCHIVE CREATION #####\n")
        self.version = self.get_archive_version()
        self.create_archive_folders(db_name, db_host_name, db_user_name, db_pw, db_port)
        print("\n##### CREATION OF WEBSITE GRAPHS #####\n")
        self.copy_complexes_subfolders_in_website()
        print("\n##### CREATION OF WEBSITE GRAPHS #####\n")
        createGraphs(db_name, db_host_name, db_user_name, db_pw, db_port)
        # print("\n##### MUTATIONS STATS #####\n")
        # computeMutationsStats(db_name, db_host_name, db_user_name, db_pw, db_port)
        print("\nEnd of script updatedb.py. Closing connexion to PosgreSQL database.\n")


    def get_tables(self) -> list:
        """Get the database tables names. 
        """
        tables = [table for table in TABLES_PATHS if table != "who_annotations"]
        tables.append("metadata")
        return tables


    def drop_tables(self) -> None:
        """Deletion of all data that contain the database tables.
        """
        queries = []
        # Deletion of data in tables creating from files
        for table_name in self.tables_names :
            query = "TRUNCATE " + table_name + " CASCADE;"
            queries.append(query)
        self.bactmentha_db.execute_many_queries(queries)


    def drop_database_cache(self) -> None:
        """ Deletion of the database cache. DISCARD ALL cannot run in a transaction block (what is 
        done by the execute_query() function) To avoid errors due do that, creates a cursor object 
        and add an autocommit to run the command.
        """
        print("Deleting database cache")
        conn = self.bactmentha_db.create_server_connection()
        cursor = conn.cursor()
        conn.autocommit = True
        cursor.execute("DISCARD ALL;")
        cursor.close()
        conn.close()
        print("Successfully droped the database cache")


    def get_archive_version(self) -> str:
        """
        Get the date of last update after data insertion.
        """
        query = "SELECT version_or_update FROM metadata WHERE metadata = 'version'"
        version = self.bactmentha_db.cursor_query_and_get_first_result(query)
        return version


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
            "af3_predictions": f"""SELECT DISTINCT AF.* FROM af3_predictions AS AF 
                            INNER JOIN interaction_full AS IF ON AF.mnt_interaction_id = IF.mnt_interaction_id 
                            WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "cellular_components": f"""SELECT DISTINCT CC.* FROM cellular_components AS CC 
                                INNER JOIN interaction_full AS IF ON IF.interactor_ida = CC.uniprot_ac OR IF.interactor_idb = CC.uniprot_ac 
                                WHERE IF.taxon_interactor_idb = '{taxonID}' ;""",
            "metadata": "SELECT * FROM metadata;",
        }
        return queryDict[tableName]


    def create_archive_folders(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str) -> None:
        """Get the archives for each reference folder, raw_data, database tables, per host taxon
        archive, af3_predictions arichve and cellular_components archives.
        """
        makedirs(path.join(ARCHIVES), exist_ok=True)
        makedirs(path.join(ARCHIVES, f"bm_archive_{self.version}"), exist_ok=True)
        self.get_references_tables_archive()
        self.get_references_tables_per_host_archive()
        self.get_af3_predictions_archive()
        self.get_CC_and_who_annots_archive()
        self.get_complete_references_archive()
        self.get_database_tables_archives()
        self.get_database_tables_per_host_archives()
        self.get_database_dump_archive(db_name, db_host_name, db_user_name, db_pw, db_port)
        self.get_complete_archive()


    def get_references_tables_archive(self) -> None:
        """Get in this archive the content of the REFERENCE 01_DatabaseTables folder.
        """
        out_path = path.join(ARCHIVES, f"bm_archive_{self.version}", f"bm_archive_{self.version}_ref_tables.zip")
        input_path = DB_TABLES
        # Using relpath to avoid copying the input Folder itself
        with ZipFile(out_path, "w", ZIP_DEFLATED) as archive:
            for root, _, files in walk(input_path):
                for file in files:
                    filepath = path.join(root, file)
                    archive.write(filepath, path.relpath(filepath, input_path)) 


    def get_references_tables_per_host_archive(self) -> None:
        """Get in this archive the content of the REFERENCE 01_DatabaseTables HOST folder for each
        host taxon.
        """
        for host in self.host_taxa:
            out_path = path.join(ARCHIVES, f"bm_archive_{self.version}",
                                 f"bm_archive_{self.version}_ref_tables_{host}.zip")
            input_path = path.join(DB_TABLES, host)
            # Using relpath to avoid copying the input Folder itself
            with ZipFile(out_path, "w", ZIP_DEFLATED) as archive:
                for root, _, files in walk(input_path):
                    for file in files:
                        filepath = path.join(root, file)
                        archive.write(filepath, path.relpath(filepath, input_path)) 


    def get_af3_predictions_archive(self) -> None:
        """Get in this archive the content of the REFERENCE 03_AF3_predictions folder.
        """
        out_path = path.join(ARCHIVES, f"bm_archive_{self.version}", f"bm_archive_{self.version}_ref_af3_predictions.zip")
        input_path = path.join(AF_PRED, "bactmentha_af3_complexes.zip")
        copy2(input_path, out_path)


    def get_CC_and_who_annots_archive(self) -> None:
        """Get the CC final tables and who priority table into an archive folder.
        (REFERENCE 02_WhoAnnotations and REFERENCE 04_Cellular_component)
        """
        out_path = path.join(ARCHIVES, f"bm_archive_{self.version}", f"bm_archive_{self.version}_ref_WHO_and_CC_annot.zip")
        input_a = path.join(WHO_ANNOT, "bactmentha_taxa_who-hazard_annotations.txt")
        input_b = path.join(CELL_COMP, "uniprot_localizations.txt")
        with ZipFile(out_path, "w", ZIP_DEFLATED) as archive:
            archive.write(input_a, path.join("WHO", path.basename(input_a)))
            archive.write(input_b, path.join("CC", path.basename(input_b)))


    def get_complete_references_archive(self) -> None:
        """Get in this archive the content of the complete REFERENCE folder.
        """
        out_path = path.join(ARCHIVES, f"bm_archive_{self.version}",
                             f"bm_archive_{self.version}_ref_complete.zip")
        input_path = REFERENCE
        # Using relpath to avoid copying the input Folder itself
        with ZipFile(out_path, "w", ZIP_DEFLATED) as archive:
            for root, _, files in walk(input_path):
                for file in files:
                    filepath = path.join(root, file)
                    archive.write(filepath, path.relpath(filepath, input_path)) 


    def get_database_tables_archives(self) -> None:
        """Get the complete Database tables archive."""
        connection = self.bactmentha_db.create_server_connection()
        out_path = path.join(ARCHIVES, f"bm_archive_{self.version}",
                             f"bm_archive_{self.version}_db_tables_complete.zip")
        with ZipFile(out_path, "w", ZIP_DEFLATED) as archive:
            for table_name in self.tables_names:
                query = "SELECT * FROM " + table_name + ";"
                content = pd.read_sql_query(query, connection)
                csv_content = StringIO()
                content.to_csv(csv_content, sep="\t", header=True, index=False)
                archive.writestr(f"{table_name}.csv", csv_content.getvalue())
        connection.close()


    def get_database_tables_per_host_archives(self) -> None:
        """Get the Database tables archive per host taxon."""
        connection = self.bactmentha_db.create_server_connection()
        for host in self.host_taxa:
            out_path = path.join(ARCHIVES, f"bm_archive_{self.version}",
                                 f"bm_archive_{self.version}_db_tables_{host}.zip")
            with ZipFile(out_path, "w", ZIP_DEFLATED) as archive:
                for table_name in self.tables_names:
                    query = self.get_taxon_tableQuery(table_name, host)
                    content = pd.read_sql_query(query, connection)
                    csv_content = StringIO()
                    content.to_csv(csv_content, sep="\t", header=True, index=False)
                    archive.writestr(f"{table_name}.csv", csv_content.getvalue())
        connection.close()


    def get_database_dump_archive(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str) -> None:
        """Creating the dump file to save the whole database into. Provides a way to restore the 
        database version easily.

        Args: connection parameters
            - db_name: name of the database to connect to (defined in the docker compose file)
            - db_host_name: name of the database host (In the docker compose file it is the service that contain the database)
            - db_user_name: user if often 'postgres' (defined in the docker compose file)
            - db_pw: the database password (defined in the docker compose file)
            - db_port: port to listen from the docker container (defined in the docker compose file)
        """
        # Setting the database parameters for the dump
        dump_file_path = path.join(ARCHIVES, f"bm_archive_{self.version}", f"bm_{self.version}_backup.dmp")
        #dump command explained : pg_dump postgres://username:password@postgres_server:5432/databasename
        db_connection_string = 'postgresql://'+db_user_name+':'+db_pw+'@' + db_host_name + ':' + db_port + '/' + db_name
        try:
            # Database connection parameters + default custom compression paramater for output format
            dump_backup_command = 'pg_dump ' + db_connection_string + ' --format=plain --no-owner > ' + dump_file_path
            system(dump_backup_command)
            print("Successfully created the dump file.")
        except:
            with open(dump_file_path, 'w') as dump_file:
                dump_file.write("ERROR")
            print("Error when writing the dump file")


    def get_complete_archive(self) -> None:
        """Get the complete archive with everything inside: the complete reference archive + the
        complete database tables archive and the dump file.
        """
        input_ref = path.join(ARCHIVES, f"bm_archive_{self.version}",
                              f"bm_archive_{self.version}_ref_complete.zip")
        input_db_dfs = path.join(ARCHIVES, f"bm_archive_{self.version}",
                             f"bm_archive_{self.version}_db_tables_complete.zip")
        input_dmp = path.join(ARCHIVES, f"bm_archive_{self.version}",
                              f"bm_{self.version}_backup.dmp")
        out_path = path.join(ARCHIVES, f"bm_archive_{self.version}",
                             f"bactmentha_{self.version}_complete_archive.zip")
        with ZipFile(out_path, "w", ZIP_DEFLATED) as archive:
            archive.write(input_ref, path.basename(input_ref))
            archive.write(input_db_dfs, path.basename(input_db_dfs))
            archive.write(input_dmp, path.basename(input_dmp))

    def copy_complexes_subfolders_in_website(self) -> None:
        """Get all the complexes foder in clean_archive and copy them to the website folder so that
        it can be downloaded from it.
        """
        input_folder = path.join(AF_PRED, "clean_archives")
        out_folder = path.join(SCRIPT, "02_Website", "static", "complexes")
        makedirs(out_folder, exist_ok=True)
        for file in listdir(input_folder):
            if file.endswith(".zip"):
                copy2(path.join(input_folder, file), path.join(out_folder))



if __name__ == "__main__":
    # Instantiate the parser
    parser = argparse.ArgumentParser(prog='BactMentha Database Update and Archiving',
                                     description='This program creates the database tables using \
                                        the given reference data. It also creates the views for \
                                            the web visualisation.')
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

    update = Update(args.db_name, args.db_host_name, args.db_user_name, args.db_password, args.db_port)
