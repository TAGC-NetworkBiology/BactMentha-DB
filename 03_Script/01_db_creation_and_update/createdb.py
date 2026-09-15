# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Script to create the database tables with references and constraints.
Also creates the database views to display on the website.
"""

import psycopg2 # to connect to the database and execute queries
from psycopg2 import Error
import argparse
from bactmentha_db_tools.db_tasks import Db_tasks


class Createdb():
    """
    Class to create the database tables and views

    Creation of the queries for the creation of the tables
    Execution of the tables creation queries with function 'tables_creation'
    Creation of the queries for the creation of the view
    Execution of the tables creation views with function 'views_creation'

    Also contains two function using the librairy psycopg2 :
    create_server_connection : connect to a postgres database using connection parameters
    execute_query : execute a query using database connection
    Both functions will be used in the other scripts : insertdata.py and updatedb.py
    """

    def __init__(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str) -> None:
        """
        Database connection using given parameters and creation of all the tables and view of the database

        Args: connection parameters
            - db_name: name of the database to connect to (defined in the docker compose file)
            - db_host_name: name of the database host (In the docker compose file it is the service that contain the database)
            - db_user_name: user if often 'postgres' (defined in the docker compose file)
            - db_pw: the database password (defined in the docker compose file)
            - db_port: port to listen from the docker container (defined in the docker compose file)

        Return: None
        """
        print("\nRunning script createdb.py\n")
        self.bactmentha_db = Db_tasks(db_name, db_host_name, db_user_name, db_pw, db_port)
        self.tables_queries = self.get_tables_queries()
        self.views_queries = self.get_views_queries()
        self.main()
        print("\nEnd of script createdb.py.\n")


    def main(self) -> None:
        self.bactmentha_db.execute_many_queries(self.tables_queries)
        self.bactmentha_db.execute_many_queries(self.views_queries)


    def get_tables_queries(self) -> list:
        """Get the list of POSTGRESQL queries to create the database tables"""
        # QUERIES FOR THE CREATION OF EMPTY TABLES FOR EACH FILE
        create_uniprot_taxonomy_query = """CREATE TABLE IF NOT EXISTS uniprot_taxonomy (
                                        taxon_id text PRIMARY KEY,
                                        taxon_name text NOT NULL,
                                        taxon_family text NOT NULL,
                                        who_priority text,
                                        hazard_group text
                                        );"""
        create_uniprot_protein_query = """CREATE TABLE IF NOT EXISTS uniprot_protein (
                                        uniprot_ac text PRIMARY KEY,
                                        uniprot_id text,
                                        uniprot_gene text,
                                        uniprot_name text,
                                        uniprot_taxon_id text NOT NULL,
                                        uniprot_seqlen integer NOT NULL,
                                        FOREIGN KEY (uniprot_taxon_id) REFERENCES uniprot_taxonomy (taxon_id)
                                        );"""   
        create_uniprot_keyword_query = """CREATE TABLE IF NOT EXISTS uniprot_keyword (
                                        uniprot_ac text NOT NULL,
                                        uniprot_keyword text NOT NULL,
                                        uniprot_taxon_id text NOT NULL,
                                        FOREIGN KEY (uniprot_ac) REFERENCES uniprot_protein (uniprot_ac),
                                        FOREIGN KEY (uniprot_taxon_id) REFERENCES uniprot_taxonomy (taxon_id),
                                        CONSTRAINT unique_lines_uniprot_keywords UNIQUE (uniprot_ac, uniprot_keyword, uniprot_taxon_id)
                                        );"""
        create_annotation_query = """CREATE TABLE IF NOT EXISTS annotation (
                                        annotation_id text PRIMARY KEY,
                                        annotation_description text NOT NULL
                                        );"""
        create_protein_annotation_query = """CREATE TABLE IF NOT EXISTS protein_annotation (
                                        uniprot_ac text NOT NULL,
                                        annotated_sequence text NOT NULL,
                                        annotation_source text NOT NULL,
                                        annotation_id text NOT NULL,
                                        inference_method text NOT NULL,
                                        sequence_identity float,
                                        bit_score float,
                                        alignment_coverage float,
                                        FOREIGN KEY (uniprot_ac) REFERENCES uniprot_protein (uniprot_ac),
                                        FOREIGN KEY (annotation_id) REFERENCES annotation (annotation_id),
                                        CONSTRAINT unique_lines_protein_annotation UNIQUE (uniprot_ac, annotated_sequence, annotation_source, annotation_id, inference_method, sequence_identity, bit_score, alignment_coverage)
                                        );"""
        create_uniprot_xref_query = """CREATE TABLE IF NOT EXISTS uniprot_xref (
                                        uniprot_ac text NOT NULL,
                                        interactor_id text PRIMARY KEY,
                                        FOREIGN KEY (uniprot_ac) REFERENCES uniprot_protein (uniprot_ac)
                                        );"""
        create_interaction_full_query = """CREATE TABLE IF NOT EXISTS interaction_full (
                                        interactor_idA text NOT NULL,
                                        interactor_idB text NOT NULL,
                                        taxon_interactor_idA text NOT NULL,
                                        taxon_interactor_idB text NOT NULL,
                                        publication_id text NOT NULL,
                                        interaction_type text NOT NULL,
                                        detection_method text NOT NULL,
                                        MI_score float,
                                        mnt_interaction_id text PRIMARY KEY,
                                        annotations text NOT NULL,
                                        FOREIGN KEY (interactor_idA) REFERENCES uniprot_xref (interactor_id),
                                        FOREIGN KEY (interactor_idB) REFERENCES uniprot_xref (interactor_id)
                                        );"""
        create_interaction_feature_query = """CREATE TABLE IF NOT EXISTS interaction_feature (
                                        mnt_interaction_id text NOT NULL,
                                        interactor_idA text NOT NULL,
                                        feature_interactor_idA text,
                                        feature_start_interactor_idA text,
                                        feature_end_interactor_idA text,
                                        interactor_idB text NOT NULL,
                                        feature_interactor_idB text,
                                        feature_start_interactor_idB text,
                                        feature_end_interactor_idB text,
                                        FOREIGN KEY (mnt_interaction_id) REFERENCES interaction_full (mnt_interaction_id),
                                        CONSTRAINT unique_lines_interaction_feature UNIQUE (mnt_interaction_id, interactor_idA, feature_interactor_idA, feature_start_interactor_idA, feature_end_interactor_idA, interactor_idB, feature_interactor_idB, feature_start_interactor_idB, feature_end_interactor_idB)
                                        );"""
        create_mimicint_interface_query = """CREATE TABLE IF NOT EXISTS mimicint_interface (
                                        mimicint_interaction_id text PRIMARY KEY,
                                        interface_interactor_idA text,
                                        interface_type_idA text,
                                        interface_start_interactor_idA text,
                                        interface_end_interactor_idA text,
                                        interface_interactor_idB text,
                                        interface_type_idB text,
                                        interface_start_interactor_idB text,
                                        interface_end_interactor_idB text
                                        );"""
        create_imex_xref_query = """CREATE TABLE IF NOT EXISTS imex_xref (
                                        mnt_interaction_id text NOT NULL,
                                        interaction_id text NOT NULL,
                                        FOREIGN KEY (mnt_interaction_id) REFERENCES interaction_full (mnt_interaction_id),
                                        CONSTRAINT unique_lines_imex_xref UNIQUE (mnt_interaction_id, interaction_id)
                                        )"""
        create_mimicint_xref_query = """CREATE TABLE IF NOT EXISTS mimicint_xref (
                                        mimicint_interaction_id text NOT NULL,
                                        mnt_interaction_id text NOT NULL,
                                        FOREIGN KEY (mimicint_interaction_id) REFERENCES mimicint_interface (mimicint_interaction_id),
                                        FOREIGN KEY (mnt_interaction_id) REFERENCES interaction_full (mnt_interaction_id),
                                        CONSTRAINT unique_lines_mimicint_xref UNIQUE (mimicint_interaction_id, mnt_interaction_id)
                                        )"""
        create_cellular_component_query = """CREATE TABLE IF NOT EXISTS cellular_components (
                                        uniprot_ac text NOT NULL,
                                        CC_value text NOT NULL,
                                        CC_id text NOT NULL,
                                        topology_value text,
                                        topology_id text,
                                        annotation_source text NOT NULL,
                                        FOREIGN KEY (uniprot_ac) REFERENCES uniprot_xref (interactor_id)
                                        )"""
        create_af3_predictions_query = """CREATE TABLE IF NOT EXISTS af3_predictions (
                                        mnt_interaction_id text NOT NULL,
                                        interactor_idA text NOT NULL,
                                        avg_plddt_idA float,
                                        interactor_idB text NOT NULL,
                                        avg_plddt_idB float,
                                        model text NOT NULL,
                                        iptm float,
                                        ptm float,
                                        pdockq2_score float,
                                        pdockq2_range text,
                                        potential_clashes integer NOT NULL,
                                        residue_contacts integer,
                                        contacts_on_A integer,
                                        contacts_on_B integer,
                                        FOREIGN KEY (mnt_interaction_id) REFERENCES interaction_full (mnt_interaction_id)
                                        )"""
        create_metadata_query = """CREATE TABLE IF NOT EXISTS metadata (
                                        metadata text PRIMARY KEY,
                                        version_or_update text NOT NULL
                                        )"""
        # QUERIES LIST
        query_list = [create_uniprot_taxonomy_query,
                      create_uniprot_protein_query,
                      create_uniprot_keyword_query,
                      create_annotation_query,
                      create_protein_annotation_query,
                      create_uniprot_xref_query,
                      create_interaction_full_query,
                      create_interaction_feature_query,
                      create_mimicint_interface_query,
                      create_imex_xref_query,
                      create_mimicint_xref_query,
                      create_cellular_component_query,
                      create_af3_predictions_query,
                      create_metadata_query]
        return query_list


    def get_views_queries(self) -> list:
        """Get the list of POSTGRESQL queries to create the database views"""
        # QUERIES FOR THE CREATION OF THE VIEWS
        # A view of the interactions in interaction_full table for the 3 host organisms (human, mice and rat)
        view_interaction_full_homo_sapiens = """CREATE OR REPLACE VIEW view_interaction_full_homo_sapiens AS
                                        SELECT * FROM interaction_full
                                        WHERE taxon_interactor_idb = '9606'"""
        view_interaction_full_mus_musculus = """CREATE OR REPLACE VIEW view_interaction_full_mus_musculus AS
                                        SELECT * FROM interaction_full
                                        WHERE taxon_interactor_idb = '10090'"""
        view_interaction_full_rattus_norvegicus = """CREATE OR REPLACE VIEW view_interaction_full_rattus_norvegicus AS
                                        SELECT * FROM interaction_full
                                        WHERE taxon_interactor_idb = '10116'"""
        # For each bacteria taxon (except hosts), the number of distinct interactions in which they are involved
        view_bact_interaction_stats_global = '''CREATE OR REPLACE VIEW view_bact_interaction_stats_global AS 
                                            SELECT
                                                UT.taxon_name AS taxon_name,
                                                UT.taxon_id AS taxon_id,
                                                UT.taxon_family AS taxon_family,
                                                COUNT(DISTINCT PA.uniprot_ac) as annotated_proteins,
                                                COUNT(DISTINCT CASE WHEN PA.annotation_source = 'VFDB' THEN PA.uniprot_ac END) AS source_VFDB,
                                                COUNT(DISTINCT CASE WHEN PA.annotation_source = 'BastionHub' THEN PA.uniprot_ac END) AS source_BastionHub,
                                                COUNT(DISTINCT IF.mnt_interaction_id) as annotated_interactions
                                                FROM uniprot_taxonomy as UT
                                                INNER JOIN uniprot_protein AS UP ON UP.uniprot_taxon_id = UT.taxon_id
                                                INNER JOIN protein_annotation AS PA ON PA.uniprot_ac = UP.uniprot_ac
                                                INNER JOIN interaction_full as IF ON
                                                    (UT.taxon_id = IF.taxon_interactor_ida AND IF.interactor_ida IN 
                                                        (SELECT interactor_id FROM uniprot_xref WHERE uniprot_ac IN 
                                                            (SELECT uniprot_ac FROM protein_annotation)))
                                                GROUP BY taxon_name, taxon_id, taxon_family
                                                ORDER BY annotated_interactions DESC;'''
        # Same thing by hosts organisms
        view_bact_interaction_stats_homo_sapiens = '''CREATE OR REPLACE VIEW view_bact_interaction_stats_homo_sapiens AS
                                            SELECT
                                                UT.taxon_name AS taxon_name,
                                                UT.taxon_id AS taxon_id,
                                                UT.taxon_family AS taxon_family,
                                                COUNT(DISTINCT PA.uniprot_ac) as annotated_proteins,
                                                COUNT(DISTINCT CASE WHEN PA.annotation_source = 'VFDB' THEN PA.uniprot_ac END) AS source_VFDB,
                                                COUNT(DISTINCT CASE WHEN PA.annotation_source = 'BastionHub' THEN PA.uniprot_ac END) AS source_BastionHub,
                                                COUNT(DISTINCT IF.mnt_interaction_id) as annotated_interactions
                                                FROM uniprot_taxonomy as UT
                                                INNER JOIN uniprot_protein AS UP ON UP.uniprot_taxon_id = UT.taxon_id
                                                INNER JOIN protein_annotation AS PA ON PA.uniprot_ac = UP.uniprot_ac
                                                INNER JOIN interaction_full as IF ON
                                                    (UT.taxon_id = IF.taxon_interactor_ida AND IF.taxon_interactor_idb = '9606' AND IF.interactor_ida IN 
                                                        (SELECT interactor_id FROM uniprot_xref WHERE uniprot_ac IN 
                                                            (SELECT uniprot_ac FROM protein_annotation)))
                                                GROUP BY taxon_name, taxon_id, taxon_family
                                                ORDER BY annotated_interactions DESC;'''
        view_bact_interaction_stats_mus_musculus = '''CREATE OR REPLACE VIEW view_bact_interaction_stats_mus_musculus AS
                                        SELECT
                                            UT.taxon_name AS taxon_name,
                                            UT.taxon_id AS taxon_id,
                                            UT.taxon_family AS taxon_family,
                                            COUNT(DISTINCT PA.uniprot_ac) as annotated_proteins,
                                            COUNT(DISTINCT CASE WHEN PA.annotation_source = 'VFDB' THEN PA.uniprot_ac END) AS source_VFDB,
                                            COUNT(DISTINCT CASE WHEN PA.annotation_source = 'BastionHub' THEN PA.uniprot_ac END) AS source_BastionHub,
                                            COUNT(DISTINCT IF.mnt_interaction_id) as annotated_interactions
                                            FROM uniprot_taxonomy as UT
                                            INNER JOIN uniprot_protein AS UP ON UP.uniprot_taxon_id = UT.taxon_id
                                            INNER JOIN protein_annotation AS PA ON PA.uniprot_ac = UP.uniprot_ac
                                            INNER JOIN interaction_full as IF ON
                                                (UT.taxon_id = IF.taxon_interactor_ida AND IF.taxon_interactor_idb = '10090' AND IF.interactor_ida IN 
                                                    (SELECT interactor_id FROM uniprot_xref WHERE uniprot_ac IN 
                                                        (SELECT uniprot_ac FROM protein_annotation)))
                                            GROUP BY taxon_name, taxon_id, taxon_family
                                            ORDER BY annotated_interactions DESC;'''
        view_bact_interaction_stats_rattus_norvegicus = '''CREATE OR REPLACE VIEW view_bact_interaction_stats_rattus_norvegicus AS
                                        SELECT
                                            UT.taxon_name AS taxon_name,
                                            UT.taxon_id AS taxon_id,
                                            UT.taxon_family AS taxon_family,
                                            COUNT(DISTINCT PA.uniprot_ac) as annotated_proteins,
                                            COUNT(DISTINCT CASE WHEN PA.annotation_source = 'VFDB' THEN PA.uniprot_ac END) AS source_VFDB,
                                            COUNT(DISTINCT CASE WHEN PA.annotation_source = 'BastionHub' THEN PA.uniprot_ac END) AS source_BastionHub,
                                            COUNT(DISTINCT IF.mnt_interaction_id) as annotated_interactions
                                            FROM uniprot_taxonomy as UT
                                            INNER JOIN uniprot_protein AS UP ON UP.uniprot_taxon_id = UT.taxon_id
                                            INNER JOIN protein_annotation AS PA ON PA.uniprot_ac = UP.uniprot_ac
                                            INNER JOIN interaction_full as IF ON
                                                (UT.taxon_id = IF.taxon_interactor_ida AND IF.taxon_interactor_idb = '10116' AND IF.interactor_ida IN
                                                    (SELECT interactor_id FROM uniprot_xref WHERE uniprot_ac IN
                                                        (SELECT uniprot_ac FROM protein_annotation WHERE uniprot_ac = PA.uniprot_ac)))
                                            GROUP BY taxon_name, taxon_id, taxon_family
                                            ORDER BY annotated_interactions DESC;'''
        # Number of interactions by bacteria family taxons
        view_bact_family_interaction_stats_global = """CREATE OR REPLACE VIEW view_bact_family_interaction_stats_global AS
                                        SELECT
                                            taxon_family,
                                                SUM(annotated_proteins) AS total_annotated_proteins,
                                                SUM(source_VFDB) AS total_source_VFDB,
                                                SUM(source_BastionHub) AS total_source_BastionHub,
                                                SUM(annotated_interactions) AS total_annotated_interactions
                                            FROM view_bact_interaction_stats_global
                                            GROUP BY taxon_family
                                            ORDER BY total_annotated_interactions DESC;"""
        # Same thing by hosts organisms
        view_bact_family_interaction_stats_homo_sapiens = """CREATE OR REPLACE VIEW view_bact_family_interaction_stats_homo_sapiens AS
                                            SELECT
                                            taxon_family,
                                                SUM(annotated_proteins) AS total_annotated_proteins,
                                                SUM(source_VFDB) AS total_source_VFDB,
                                                SUM(source_BastionHub) AS total_source_BastionHub,
                                                SUM(annotated_interactions) AS total_annotated_interactions
                                            FROM view_bact_interaction_stats_homo_sapiens
                                            GROUP BY taxon_family
                                            ORDER BY total_annotated_interactions DESC;"""
        view_bact_family_interaction_stats_mus_musculus = """CREATE OR REPLACE VIEW view_bact_family_interaction_stats_mus_musculus AS
                                            SELECT
                                            taxon_family,
                                                SUM(annotated_proteins) AS total_annotated_proteins,
                                                SUM(source_VFDB) AS total_source_VFDB,
                                                SUM(source_BastionHub) AS total_source_BastionHub,
                                                SUM(annotated_interactions) AS total_annotated_interactions
                                            FROM view_bact_interaction_stats_mus_musculus
                                            GROUP BY taxon_family
                                            ORDER BY total_annotated_interactions DESC;"""
        view_bact_family_interaction_stats_rattus_norvegicus = """CREATE OR REPLACE VIEW view_bact_family_interaction_stats_rattus_norvegicus AS
                                            SELECT
                                            taxon_family,
                                                SUM(annotated_proteins) AS total_annotated_proteins,
                                                SUM(source_VFDB) AS total_source_VFDB,
                                                SUM(source_BastionHub) AS total_source_BastionHub,
                                                SUM(annotated_interactions) AS total_annotated_interactions
                                            FROM view_bact_interaction_stats_rattus_norvegicus
                                            GROUP BY taxon_family
                                            ORDER BY total_annotated_interactions DESC;"""
        # Stats of the annotations of bacterias involved in interactions with any of the hosts
        view_annotation_stats_global = """CREATE OR REPLACE VIEW view_annotation_stats_global AS
                                        SELECT
                                            A.annotation_description,
                                            COUNT(DISTINCT PA.uniprot_ac) AS annotated_proteins,
                                            CASE
                                                WHEN COUNT(DISTINCT PA.annotation_source) = 2 THEN 'VFDB/BastionHub'
                                                WHEN COUNT(DISTINCT PA.annotation_source) = 1 AND MAX(PA.annotation_source) = 'VFDB' THEN 'VFDB'
                                                ELSE 'BastionHub'
                                            END AS annotation_source,
                                            COUNT(DISTINCT IF.mnt_interaction_id) AS interactions
                                        FROM protein_annotation AS PA
                                            INNER JOIN annotation AS A 
                                                ON A.annotation_id = PA.annotation_id
                                            INNER JOIN uniprot_protein AS UP 
                                                ON UP.uniprot_ac = PA.uniprot_ac
                                            INNER JOIN uniprot_xref AS UX 
                                                ON UX.uniprot_ac = UP.uniprot_ac
                                            INNER JOIN interaction_full AS IF 
                                                ON IF.interactor_ida = UX.interactor_id
                                        GROUP BY A.annotation_description
                                        ORDER BY interactions DESC;"""
        # Stats of the annotations of bacterias involved in interactions with a specific host (human, mice or rat)
        view_annotation_stats_homo_sapiens = """CREATE OR REPLACE VIEW view_annotation_stats_homo_sapiens AS
                                            SELECT
                                                A.annotation_description,
                                                COUNT(DISTINCT PA.uniprot_ac) AS annotated_proteins,
                                                CASE
                                                    WHEN COUNT(DISTINCT PA.annotation_source) = 2 THEN 'VFDB/BastionHub'
                                                    WHEN COUNT(DISTINCT PA.annotation_source) = 1 AND MAX(PA.annotation_source) = 'VFDB' THEN 'VFDB'
                                                    ELSE 'BastionHub'
                                                END AS annotation_source,
                                                COUNT(DISTINCT IF.mnt_interaction_id) AS interactions
                                            FROM annotation AS A
                                                INNER JOIN protein_annotation AS PA 
                                                    ON A.annotation_id = PA.annotation_id
                                                INNER JOIN uniprot_protein AS UP 
                                                    ON PA.uniprot_ac = UP.uniprot_ac
                                                INNER JOIN uniprot_xref AS UX 
                                                    ON UP.uniprot_ac = UX.uniprot_ac
                                                INNER JOIN interaction_full AS IF 
                                                    ON UX.interactor_id = IF.interactor_ida
                                            WHERE IF.taxon_interactor_idb = '9606'
                                            GROUP BY A.annotation_description
                                            ORDER BY interactions DESC;""" 
        view_annotation_stats_mus_musculus = """CREATE OR REPLACE VIEW view_annotation_stats_mus_musculus AS
                                            SELECT
                                                A.annotation_description,
                                                COUNT(DISTINCT PA.uniprot_ac) AS annotated_proteins,
                                                CASE
                                                    WHEN COUNT(DISTINCT PA.annotation_source) = 2 THEN 'VFDB/BastionHub'
                                                    WHEN COUNT(DISTINCT PA.annotation_source) = 1 AND MAX(PA.annotation_source) = 'VFDB' THEN 'VFDB'
                                                    ELSE 'BastionHub'
                                                END AS annotation_source,
                                                COUNT(DISTINCT IF.mnt_interaction_id) AS interactions
                                            FROM annotation AS A
                                                INNER JOIN protein_annotation AS PA 
                                                    ON A.annotation_id = PA.annotation_id
                                                INNER JOIN uniprot_protein AS UP 
                                                    ON PA.uniprot_ac = UP.uniprot_ac
                                                INNER JOIN uniprot_xref AS UX 
                                                    ON UP.uniprot_ac = UX.uniprot_ac
                                                INNER JOIN interaction_full AS IF 
                                                    ON UX.interactor_id = IF.interactor_ida
                                            WHERE IF.taxon_interactor_idb = '10090'
                                            GROUP BY A.annotation_description
                                            ORDER BY interactions DESC;""" 
        view_annotation_stats_rattus_norvegicus = """CREATE OR REPLACE VIEW view_annotation_stats_rattus_norvegicus AS
                                            SELECT
                                                A.annotation_description,
                                                COUNT(DISTINCT PA.uniprot_ac) AS annotated_proteins,
                                                CASE
                                                    WHEN COUNT(DISTINCT PA.annotation_source) = 2 THEN 'VFDB/BastionHub'
                                                    WHEN COUNT(DISTINCT PA.annotation_source) = 1 AND MAX(PA.annotation_source) = 'VFDB' THEN 'VFDB'
                                                    ELSE 'BastionHub'
                                                END AS annotation_source,
                                                COUNT(DISTINCT IF.mnt_interaction_id) AS interactions
                                            FROM annotation AS A
                                                INNER JOIN protein_annotation AS PA 
                                                    ON A.annotation_id = PA.annotation_id
                                                INNER JOIN uniprot_protein AS UP 
                                                    ON PA.uniprot_ac = UP.uniprot_ac
                                                INNER JOIN uniprot_xref AS UX 
                                                    ON UP.uniprot_ac = UX.uniprot_ac
                                                INNER JOIN interaction_full AS IF 
                                                    ON UX.interactor_id = IF.interactor_ida
                                            WHERE IF.taxon_interactor_idb = '10116'
                                            GROUP BY A.annotation_description
                                            ORDER BY interactions DESC;""" 
        view_graphical_stats = """CREATE OR REPLACE VIEW view_graphical_stats AS
                                SELECT
                                'global' AS Taxon,
                                COUNT(DISTINCT IF.mnt_interaction_id) AS NI,
                                COUNT(DISTINCT CASE WHEN annotations='tff' THEN IF.mnt_interaction_id END) AS M,
                                COUNT(DISTINCT CASE WHEN annotations='ftf' THEN IF.mnt_interaction_id END) AS F,
                                COUNT(DISTINCT CASE WHEN annotations='fft' THEN IF.mnt_interaction_id END) AS P,
                                COUNT(DISTINCT CASE WHEN annotations='ttf' THEN IF.mnt_interaction_id END) AS MF,
                                COUNT(DISTINCT CASE WHEN annotations='tft' THEN IF.mnt_interaction_id END) AS MP,
                                COUNT(DISTINCT CASE WHEN annotations='ftt' THEN IF.mnt_interaction_id END) AS FP,
                                COUNT(DISTINCT CASE WHEN annotations='ttt' THEN IF.mnt_interaction_id END) AS MFP,
                                COUNT(DISTINCT CASE WHEN annotations='fff' THEN IF.mnt_interaction_id END) AS noMFP,
                                COUNT(DISTINCT CASE WHEN (annotations!='fff' AND annotations!='ttt') THEN IF.mnt_interaction_id END) AS notfull
                                FROM interaction_full AS IF
                                UNION ALL
                                SELECT
                                'homo_sapiens' AS Taxon,
                                COUNT(DISTINCT IFH.mnt_interaction_id) AS NI,
                                COUNT(DISTINCT CASE WHEN annotations='tff' THEN IFH.mnt_interaction_id END) AS M,
                                COUNT(DISTINCT CASE WHEN annotations='ftf' THEN IFH.mnt_interaction_id END) AS F,
                                COUNT(DISTINCT CASE WHEN annotations='fft' THEN IFH.mnt_interaction_id END) AS P,
                                COUNT(DISTINCT CASE WHEN annotations='ttf' THEN IFH.mnt_interaction_id END) AS MF,
                                COUNT(DISTINCT CASE WHEN annotations='tft' THEN IFH.mnt_interaction_id END) AS MP,
                                COUNT(DISTINCT CASE WHEN annotations='ftt' THEN IFH.mnt_interaction_id END) AS FP,
                                COUNT(DISTINCT CASE WHEN annotations='ttt' THEN IFH.mnt_interaction_id END) AS MFP,
                                COUNT(DISTINCT CASE WHEN annotations='fff' THEN IFH.mnt_interaction_id END) AS noMFP,
                                COUNT(DISTINCT CASE WHEN (annotations!='fff' AND annotations!='ttt') THEN IFH.mnt_interaction_id END) AS notfull
                                FROM view_interaction_full_homo_sapiens AS IFH
                                UNION ALL
                                SELECT
                                'mus_musculus' AS Taxon,
                                COUNT(DISTINCT IFM.mnt_interaction_id) AS NI,
                                COUNT(DISTINCT CASE WHEN annotations='tff' THEN IFM.mnt_interaction_id END) AS M,
                                COUNT(DISTINCT CASE WHEN annotations='ftf' THEN IFM.mnt_interaction_id END) AS F,
                                COUNT(DISTINCT CASE WHEN annotations='fft' THEN IFM.mnt_interaction_id END) AS P,
                                COUNT(DISTINCT CASE WHEN annotations='ttf' THEN IFM.mnt_interaction_id END) AS MF,
                                COUNT(DISTINCT CASE WHEN annotations='tft' THEN IFM.mnt_interaction_id END) AS MP,
                                COUNT(DISTINCT CASE WHEN annotations='ftt' THEN IFM.mnt_interaction_id END) AS FP,
                                COUNT(DISTINCT CASE WHEN annotations='ttt' THEN IFM.mnt_interaction_id END) AS MFP,
                                COUNT(DISTINCT CASE WHEN annotations='fff' THEN IFM.mnt_interaction_id END) AS noMFP,
                                COUNT(DISTINCT CASE WHEN (annotations!='fff' AND annotations!='ttt') THEN IFM.mnt_interaction_id END) AS notfull
                                FROM view_interaction_full_mus_musculus AS IFM
                                UNION ALL
                                SELECT
                                'rattus_norvegicus' AS Taxon,
                                COUNT(DISTINCT IFR.mnt_interaction_id) AS NI,
                                COUNT(DISTINCT CASE WHEN annotations='tff' THEN IFR.mnt_interaction_id END) AS M,
                                COUNT(DISTINCT CASE WHEN annotations='ftf' THEN IFR.mnt_interaction_id END) AS F,
                                COUNT(DISTINCT CASE WHEN annotations='fft' THEN IFR.mnt_interaction_id END) AS P,
                                COUNT(DISTINCT CASE WHEN annotations='ttf' THEN IFR.mnt_interaction_id END) AS MF,
                                COUNT(DISTINCT CASE WHEN annotations='tft' THEN IFR.mnt_interaction_id END) AS MP,
                                COUNT(DISTINCT CASE WHEN annotations='ftt' THEN IFR.mnt_interaction_id END) AS FP,
                                COUNT(DISTINCT CASE WHEN annotations='ttt' THEN IFR.mnt_interaction_id END) AS MFP,
                                COUNT(DISTINCT CASE WHEN annotations='fff' THEN IFR.mnt_interaction_id END) AS noMFP,
                                COUNT(DISTINCT CASE WHEN (annotations!='fff' AND annotations!='ttt') THEN IFR.mnt_interaction_id END) AS notfull
                                FROM view_interaction_full_rattus_norvegicus AS IFR"""
        # Bact annotation proportion in full interactions tables
        view_bact_annotation_count_global = """CREATE OR REPLACE VIEW view_bact_annotation_count_global AS
                                        SELECT 
                                            UT.taxon_id,
                                            UT.taxon_name,
                                            COUNT(IF.mnt_interaction_id) AS number_of_interactions,
                                            SUM(CASE WHEN SUBSTRING(IF.annotations, 1, 1) = '1' THEN 1 ELSE 0 END) AS number_of_annotated_interactions
                                        FROM 
                                            interaction_full AS IF
                                        JOIN 
                                            uniprot_taxonomy AS UT ON IF.taxon_interactor_ida = UT.taxon_id
                                        GROUP BY 
                                            UT.taxon_id, UT.taxon_name
                                        ORDER BY 
                                            number_of_interactions DESC;"""
        view_bact_annotation_count_homo_sapiens = """CREATE OR REPLACE VIEW view_bact_annotation_count_homo_sapiens AS
                                        SELECT 
                                            UT.taxon_id,
                                            UT.taxon_name,
                                            COUNT(IF.mnt_interaction_id) AS number_of_interactions,
                                            SUM(CASE WHEN SUBSTRING(IF.annotations, 1, 1) = '1' THEN 1 ELSE 0 END) AS number_of_annotated_interactions
                                        FROM 
                                            view_interaction_full_homo_sapiens AS IF
                                        JOIN 
                                            uniprot_taxonomy AS UT ON IF.taxon_interactor_ida = UT.taxon_id
                                        GROUP BY 
                                            UT.taxon_id, UT.taxon_name
                                        ORDER BY 
                                            number_of_interactions DESC;"""
        view_bact_annotation_count_mus_musculus = """CREATE OR REPLACE VIEW view_bact_annotation_count_mus_musculus AS
                                        SELECT 
                                            UT.taxon_id,
                                            UT.taxon_name,
                                            COUNT(IF.mnt_interaction_id) AS number_of_interactions,
                                            SUM(CASE WHEN SUBSTRING(IF.annotations, 1, 1) = '1' THEN 1 ELSE 0 END) AS number_of_annotated_interactions
                                        FROM 
                                            view_interaction_full_mus_musculus AS IF
                                        JOIN 
                                            uniprot_taxonomy AS UT ON IF.taxon_interactor_ida = UT.taxon_id
                                        GROUP BY 
                                            UT.taxon_id, UT.taxon_name
                                        ORDER BY 
                                            number_of_interactions DESC;"""
        view_bact_annotation_count_rattus_norvegicus = """CREATE OR REPLACE VIEW view_bact_annotation_count_rattus_norvegicus AS
                                        SELECT 
                                            UT.taxon_id,
                                            UT.taxon_name,
                                            COUNT(IF.mnt_interaction_id) AS number_of_interactions,
                                            SUM(CASE WHEN SUBSTRING(IF.annotations, 1, 1) = '1' THEN 1 ELSE 0 END) AS number_of_annotated_interactions
                                        FROM 
                                            view_interaction_full_rattus_norvegicus AS IF
                                        JOIN 
                                            uniprot_taxonomy AS UT ON IF.taxon_interactor_ida = UT.taxon_id
                                        GROUP BY 
                                            UT.taxon_id, UT.taxon_name
                                        ORDER BY 
                                            number_of_interactions DESC;"""
        # who priority and hazard group views
        view_who_priority_hazard_group = """CREATE OR REPLACE VIEW view_who_priority_hazard_group AS 
                                            SELECT
                                                UT.taxon_id,
                                                UT.taxon_name,
                                                UT.taxon_family,
                                                UT.hazard_group,
                                                UT.who_priority,
                                                COUNT(IF.taxon_interactor_ida) AS interactions 
                                            FROM
                                                uniprot_taxonomy AS UT
                                            LEFT JOIN
                                                interaction_full AS IF
                                            ON UT.taxon_id = IF.taxon_interactor_ida
                                            WHERE who_priority != 'NULL' 
                                            OR hazard_group != 'NULL'
                                            GROUP BY
                                                UT.taxon_id,
                                                UT.taxon_name,
                                                UT.taxon_family,
                                                UT.hazard_group,
                                                UT.who_priority;"""
        view_who_priority = """CREATE OR REPLACE VIEW view_who_priority AS
                            SELECT
                                UT.taxon_id,
                                UT.taxon_name,
                                UT.taxon_family,
                                UT.hazard_group,
                                UT.who_priority,
                                COUNT(IF.taxon_interactor_ida) AS interactions
                            FROM
                                uniprot_taxonomy AS UT
                            LEFT JOIN
                                interaction_full AS IF
                            ON UT.taxon_id = IF.taxon_interactor_ida
                            WHERE who_priority != 'NULL'
                            GROUP BY
                                UT.taxon_id,
                                UT.taxon_name,
                                UT.taxon_family,
                                UT.hazard_group,
                                UT.who_priority;"""
        view_hazard_group2 = """CREATE OR REPLACE VIEW view_hazard_group2 AS
                            SELECT
                                UT.taxon_id,
                                UT.taxon_name,
                                UT.taxon_family,
                                UT.hazard_group,
                                UT.who_priority,
                                COUNT(IF.taxon_interactor_ida) AS interactions 
                            FROM
                                uniprot_taxonomy AS UT
                            LEFT JOIN
                                interaction_full AS IF
                            ON UT.taxon_id = IF.taxon_interactor_ida
                            WHERE hazard_group = '2'
                            GROUP BY
                                UT.taxon_id,
                                UT.taxon_name,
                                UT.taxon_family,
                                UT.hazard_group,
                                UT.who_priority;"""
        view_hazard_group3 = """CREATE OR REPLACE VIEW view_hazard_group3 AS
                            SELECT
                                UT.taxon_id,
                                UT.taxon_name,
                                UT.taxon_family,
                                UT.hazard_group,
                                UT.who_priority,
                                COUNT(IF.taxon_interactor_ida) AS interactions 
                            FROM
                                uniprot_taxonomy AS UT
                            LEFT JOIN
                                interaction_full AS IF
                            ON UT.taxon_id = IF.taxon_interactor_ida
                            WHERE hazard_group = '3'
                            GROUP BY
                                UT.taxon_id,
                                UT.taxon_name,
                                UT.taxon_family,
                                UT.hazard_group,
                                UT.who_priority;"""
        view_who_priority_stats = """CREATE OR REPLACE VIEW view_who_priority_stats AS
                            SELECT 
                                'interactions' AS category, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority != 'NULL' OR UT.hazard_group != 'NULL' THEN IF.mnt_interaction_id END) AS who_or_hazard, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority != 'NULL' AND UT.hazard_group != 'NULL' THEN IF.mnt_interaction_id END) AS who_and_hazard,
                                COUNT(DISTINCT CASE WHEN UT.who_priority != 'NULL' THEN IF.mnt_interaction_id END) AS who_priority, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority = 'family' THEN IF.mnt_interaction_id END) AS who_priority_family, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority = 'genus' THEN IF.mnt_interaction_id END) AS who_priority_genus, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority = 'species' THEN IF.mnt_interaction_id END) AS who_priority_species, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority = 'NULL' THEN IF.mnt_interaction_id END) AS who_priority_null, 
                                COUNT(DISTINCT CASE WHEN UT.hazard_group != 'NULL' THEN IF.mnt_interaction_id END) AS hazard_group, 
                                COUNT(DISTINCT CASE WHEN UT.hazard_group = '2' THEN IF.mnt_interaction_id END) AS hazard_group2, 
                                COUNT(DISTINCT CASE WHEN UT.hazard_group = '3' THEN IF.mnt_interaction_id END) AS hazard_group3, 
                                COUNT(DISTINCT CASE WHEN UT.hazard_group = 'NULL' THEN IF.mnt_interaction_id END) AS hazard_group_null, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority != 'NULL' AND UT.hazard_group = 'NULL' THEN IF.mnt_interaction_id END) AS who_not_hazard, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority = 'NULL' AND UT.hazard_group != 'NULL' THEN IF.mnt_interaction_id END) AS hazard_not_who,
                                COUNT(DISTINCT IF.mnt_interaction_id) AS total_nb 
                                FROM interaction_full AS IF 
                                INNER JOIN uniprot_xref AS UX ON UX.interactor_id = IF.interactor_ida 
                                INNER JOIN uniprot_protein AS UP ON UP.uniprot_ac = UX.uniprot_ac 
                                INNER JOIN uniprot_taxonomy AS UT ON UT.taxon_id = UP.uniprot_taxon_id
                            UNION ALL 
                            SELECT 
                                'bact_taxa' AS category, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority != 'NULL' OR UT.hazard_group != 'NULL' THEN UT.taxon_id END) AS who_or_hazard, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority != 'NULL' AND UT.hazard_group != 'NULL' THEN UT.taxon_id END) AS who_and_hazard,
                                COUNT(DISTINCT CASE WHEN UT.who_priority != 'NULL' THEN UT.taxon_id END) AS who_priority, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority = 'family' THEN UT.taxon_id END) AS who_priority_family, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority = 'genus' THEN UT.taxon_id END) AS who_priority_genus, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority = 'species' THEN UT.taxon_id END) AS who_priority_species, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority = 'NULL' THEN UT.taxon_id END) AS who_priority_null, 
                                COUNT(DISTINCT CASE WHEN UT.hazard_group != 'NULL' THEN UT.taxon_id END) AS hazard_group, 
                                COUNT(DISTINCT CASE WHEN UT.hazard_group = '2' THEN UT.taxon_id END) AS hazard_group2, 
                                COUNT(DISTINCT CASE WHEN UT.hazard_group = '3' THEN UT.taxon_id END) AS hazard_group3, 
                                COUNT(DISTINCT CASE WHEN UT.hazard_group = 'NULL' THEN UT.taxon_id END) AS hazard_group_null, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority != 'NULL' AND UT.hazard_group = 'NULL' THEN UT.taxon_id END) AS who_not_hazard, 
                                COUNT(DISTINCT CASE WHEN UT.who_priority = 'NULL' AND UT.hazard_group != 'NULL' THEN UT.taxon_id END) AS hazard_not_who,
                                COUNT(DISTINCT CASE WHEN UT.taxon_id NOT IN (SELECT DISTINCT taxon_interactor_idb FROM interaction_full) THEN UT.taxon_id END) AS total_nb  
                                FROM uniprot_taxonomy AS UT ;"""
        # view for download (all interaction data and child row PA)
        view_full_interaction_data = """CREATE OR REPLACE VIEW view_full_interaction_data AS
            SELECT IF.mnt_interaction_id, 
                (SELECT UT_A.who_priority FROM uniprot_taxonomy UT_A WHERE UT_A.taxon_id = IF.taxon_interactor_idA) AS who_priority, 
                (SELECT UT_A.hazard_group FROM uniprot_taxonomy UT_A WHERE UT_A.taxon_id = IF.taxon_interactor_idA) AS hazard_group,
                IF.interactor_ida, 
                (SELECT UP_A.uniprot_gene FROM uniprot_xref UX_A 
                    INNER JOIN uniprot_protein UP_A ON UP_A.uniprot_ac = UX_A.uniprot_ac 
                    WHERE UX_A.interactor_id = IF.interactor_idA) AS gene_A,
                (SELECT UT_A.taxon_name FROM uniprot_xref UX_A 
                    INNER JOIN uniprot_protein UP_A ON UP_A.uniprot_ac = UX_A.uniprot_ac 
                    INNER JOIN uniprot_taxonomy UT_A ON UT_A.taxon_id = UP_A.uniprot_taxon_id 
                    WHERE UX_A.interactor_id = IF.interactor_idA) AS taxon_name_A,
                IF.interactor_idB, 
                (SELECT UP_B.uniprot_gene FROM uniprot_xref UX_B 
                    INNER JOIN uniprot_protein UP_B ON UP_B.uniprot_ac = UX_B.uniprot_ac 
                    WHERE UX_B.interactor_id = IF.interactor_idB) AS gene_B,
                (SELECT UT_B.taxon_name FROM uniprot_xref UX_B 
                    INNER JOIN uniprot_protein UP_B ON UP_B.uniprot_ac = UX_B.uniprot_ac 
                    INNER JOIN uniprot_taxonomy UT_B ON UT_B.taxon_id = UP_B.uniprot_taxon_id 
                    WHERE UX_B.interactor_id = IF.interactor_idB) AS taxon_name_B,
                IF.detection_method,
                IF.interaction_type, 
                IF.publication_id, 
                IF.MI_score, 
                IF.annotations
            FROM interaction_full AS IF 
            """

        view_bact_prot_annotation = """CREATE OR REPLACE VIEW view_bact_prot_annotation AS
                                    SELECT PA.uniprot_ac AS uniprot_ac,
                                    PA.annotated_sequence AS annotated_sequence,
                                    PA.annotation_source AS annotation_source,
                                    PA.annotation_id AS annotation_id,
                                    A.annotation_description AS annotation_description,
                                    PA.inference_method AS inference_method,
                                    PA.sequence_identity AS sequence_identity,
                                    PA.bit_score AS bit_score,
                                    PA.alignment_coverage AS alignment_coverage
                                    FROM protein_annotation AS PA
                                    INNER JOIN annotation as A ON PA.annotation_id = A.annotation_id;"""
        # list of views
        view_list = [view_interaction_full_homo_sapiens, view_interaction_full_mus_musculus, view_interaction_full_rattus_norvegicus,
                     view_bact_interaction_stats_global, view_bact_interaction_stats_homo_sapiens, view_bact_interaction_stats_mus_musculus,
                     view_bact_interaction_stats_rattus_norvegicus, view_bact_family_interaction_stats_global, view_bact_family_interaction_stats_homo_sapiens,
                     view_bact_family_interaction_stats_mus_musculus, view_bact_family_interaction_stats_rattus_norvegicus, view_annotation_stats_global,
                     view_annotation_stats_homo_sapiens, view_annotation_stats_mus_musculus, view_annotation_stats_rattus_norvegicus, view_graphical_stats,
                     view_who_priority_hazard_group, view_who_priority, view_hazard_group2, view_hazard_group3, view_who_priority_stats,
                     view_full_interaction_data, view_bact_prot_annotation, view_bact_annotation_count_global, view_bact_annotation_count_homo_sapiens,
                     view_bact_annotation_count_mus_musculus, view_bact_annotation_count_rattus_norvegicus]
        return view_list


if __name__ == "__main__":

    # Instantiate the parser
    parser = argparse.ArgumentParser(prog='BactMentha Database Creation',
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

    # Run the database creation script
    Createdb(args.db_name, args.db_host_name, args.db_user_name, args.db_password, args.db_port)
