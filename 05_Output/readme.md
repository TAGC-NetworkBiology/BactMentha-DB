# 05_Output

This folder will contain the database files and the database archives.

Create the 01_Database and 02_Archive subfolder in the 05_Output repository.
You have now 
```bash
BactMentha-DB
├── 01_Reference
│    └── ...
├── 02_Container
│    └── ...
├── 03_Script
│    └── ...
└── 05_Output
     ├── 01_Database
     └── 02_Archive
```

## 02_Archive

Get the archive folder containing the last versions of the database (DATABASE.2026-04-13.tar.gz) from the following Zenodo link:
https://doi.org/10.5281/zenodo.19498850


Extract the Archive from BactMentha main folder (the one that contains 02_Container and 03_Script). You should now have the following folder structure:
```bash
.
├── bm_archive_2025_07_25
│   ├── bm_archive_2025_07_25_complete.zip
│   ├── bm_archive_2025_07_25_databasetables_10090.zip
│   ├── bm_archive_2025_07_25_databasetables_10116.zip
│   ├── bm_archive_2025_07_25_databasetables_9606.zip
│   ├── bm_archive_2025_07_25_databasetables.zip
│   ├── bm_archive_2025_07_25_dump.zip
│   ├── bm_archive_2025_07_25_rawdata_10090.zip
│   ├── bm_archive_2025_07_25_rawdata_10116.zip
│   ├── bm_archive_2025_07_25_rawdata_9606.zip
│   └── bm_archive_2025_07_25_rawdata.zip
├── bm_archive_2025_08_27
│   ├── bm_archive_2025_08_27_complete.zip
│   ├── bm_archive_2025_08_27_databasetables_10090.zip
│   ├── bm_archive_2025_08_27_databasetables_10116.zip
│   ├── bm_archive_2025_08_27_databasetables_9606.zip
│   ├── bm_archive_2025_08_27_databasetables.zip
│   ├── bm_archive_2025_08_27_dump.zip
│   ├── bm_archive_2025_08_27_rawdata_10090.zip
│   ├── bm_archive_2025_08_27_rawdata_10116.zip
│   ├── bm_archive_2025_08_27_rawdata_9606.zip
│   └── bm_archive_2025_08_27_rawdata.zip
└── DATABASE.2026-04-13.tar.gz
```

Copy ```bm_archive_2025_08_27_dump.zip``` in 05_Output/01_Database subfolder and extract it here:
```bash
05_Output
  └── 01_Database
      ├── bm_2025_08_27_backup.dmp
      └── bm_archive_2025_08_27_dump.zip
```

Then open a terminal located in the 01_Database folder and run the following commmand to recreate the database files with the database dump file:
```bash
sudo apt install postgresql
sudo apt install postgresql-client-common


'host=db port=5432 dbname=bactmentha_db user=postgres password=postgres'
```


