# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Constants for BactMentha DB creation, data insertion and update.
All paths are set from within the docker container.
"""

from os import path

PROJECT_FOLDER="/BactMentha"
# PROJECT_FOLDER="/home/bergogne/Documents/BactMentha_DB"

# REFERENCES folder and subfolders

REFERENCE = path.join(PROJECT_FOLDER, "01_Reference")
DB_TABLES = path.join(REFERENCE, "01_DatabaseTables")
WHO_ANNOT = path.join(REFERENCE, "02_WhoAnnotations")
AF_PRED = path.join(REFERENCE, "03_AF3_predictions")
CELL_COMP = path.join(REFERENCE, "04_Cellular_component")

# OUTPUT folder and subfolders

OUTPUT = path.join(PROJECT_FOLDER, "05_Output")
ARCHIVES = path.join(OUTPUT, "02_Archive")

# SCRIPT folder and subfolders

SCRIPT = path.join(PROJECT_FOLDER, "03_Script")
DB_CREATION_AND_UPDATE = path.join(SCRIPT, "01_db_creation_and_update")


# Database tables names and corresponding files /!\ after DB_TABLES + taxon_id

TABLES_PATHS = {
        "uniprot_taxonomy": path.join("04_Formatting", "uniprot_taxonomy.txt"),                                                 # DB_TABLES
        "uniprot_protein": path.join("04_Formatting", "uniprot_protein.txt"),                                                   # DB_TABLES
        "uniprot_keyword": path.join("04_Formatting", "uniprot_keyword.txt"),                                                   # DB_TABLES
        "annotation": path.join("05_Bacterial_annotation", "03_Annotation_formatting", "annotation_description.txt"),           # DB_TABLES
        "protein_annotation": path.join("05_Bacterial_annotation", "03_Annotation_formatting", "bacterial_annotation.txt"),     # DB_TABLES
        "uniprot_xref": path.join("04_Formatting", "uniprot_xref.txt"),                                                         # DB_TABLES
        "interaction_full": path.join("04_Formatting", "interaction_full.txt"),                                                 # DB_TABLES
        "interaction_feature": path.join("06_Feature_formatting", "interaction_feature.txt"),                                   # DB_TABLES
        "mimicint_interface": path.join("07_mimicINT_formatting", "mimicint_interface.txt"),                                    # DB_TABLES
        "imex_xref": path.join("04_Formatting", "imex_xref.txt"),                                                               # DB_TABLES
        "mimicint_xref": path.join("07_mimicINT_formatting", "mimicint_xref.txt"),                                              # DB_TABLES
        "who_annotations": path.join("bactmentha_taxa_who-hazard_annotations.txt"),                                             # WHO_ANNOT
        "af3_predictions": path.join("clean_tables", "mentha_af3_complexes.txt"),                                                  # AF_PRED
        "cellular_components": path.join("uniprot_localizations.txt"),                                                          # CELL_COMP
}

PRIMARY_KEYS = {
        "uniprot_taxonomy": "taxon_id",
        "uniprot_protein": "uniprot_ac",
        "uniprot_keyword": None,
        "annotation": "annotation_id",
        "protein_annotation": None,
        "uniprot_xref": "interactor_id",
        "interaction_full": "mnt_interaction_id",
        "interaction_feature": None,
        "mimicint_interface": "mimicint_interaction_id",
        "imex_xref": None,
        "mimicint_xref": None,
        "af3_predictions": None,
        "cellular_components": None,
}

