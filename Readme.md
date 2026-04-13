# `BactMentha_DataBase`
  
This project aims to create a database for bacteria-host protein-protein interactions and the associated website, using the data derived from the BactMentha workflow.  
  
# `I. Project content`  
  
Contains four subfolders : **`01_Reference`**, **`02_Container`**, **`03_Script`**, **`05_Output`**.  
If you want to deploy the database localy on your computer, follow the deployment steps bellow, starting with the .git clone command:
```bash
git clone git@github.com:TAGC-NetworkBiology/BactMentha-DB.git
```
  
## `01_Reference folder`  
  
The folder **`01_Reference`**, contains a subfolder **`01_DatabaseTables`** derived from the BactMentha Workflow, containing a subfolder for each taxon that is studied for now in the BactMentha Project. For now, the only three present taxons are : **9606** (*Homo sapiens*), **10090** (*Mus_musculus*), **10116** (*Rattus norvegicus*). Each taxon subfolder contains the same names files derived from the BactMentha Workflow (at the exception of the *07_mimicINT_formatting* folder that is only present for taxon *9606*).    
The folder **`01_Reference`** also contain a subfolder **`02_WhoAnnotations`** itself containing a text file with manual currations of WHO priority and hazard group for the studied pathogens.

When the .git is pulled for the first time, this folder is empty. Get here the references files (01_DatabaseTables.2026-04-10.tar.gz and 02_WhoAnnotations.2026-04-10.tar.gz) from the following Zenodo link: https://doi.org/10.5281/zenodo.19498850. After extraction, the folder structure should be the following:
```bash
.
├── 01_DatabaseTables
│   ├── 10090
│   ├── 10116
│   └── 9606
└── 02_WhoAnnotations
    ├── bactmentha_taxa_who-hazard_annotations.csv
    └── bactmentha_taxa_who-hazard_annotations.txt
```
This step is not necessary if you don't want to perform the database update but just deploy the current databse content on the website.

## `02_Container folder`  
  
The folder **`02_Container`** contains the dockerfiles and docker-compose files needed for the creation of the database.  
  
Contains the folder **`Compose_postgres_python`** that itself contains the file `compose_postgres_python.yml`.
The file `compose_postgres_python.yml` is used to run the scripts for BactMentha database creation, data insertion and update / archiving. The command to run in the compose file will differ. For more information, see the documentation for the `compose_postgres_python.yml` in the ame folder : **`Compose_postgres_python`**
  
This folder also contains three subfolders for the containers construction when using the `compose_postgres_python.yml` :  
- **`01_python3:`** contains a dockerfile and its Readme.md file giving further informations for the creation of the Python3 image to use in the docker-compose file.
- **`02_php:`** contains a dockerfile and its Readme.md file giving further informations for the creation of the Php image to use in the docker-compose file.
- **`03_nginx:`** contains a subfolder: *conf*, that contains a file called `nginx.conf` that will be used at the container creation to modify the configuration of Nginx Webserver in the image.

This folder also contain an .env file that is hidden by default. You can make this file appear using the following command `Ctrl + H`.  
This environment file is used to set some variables names and paths that will be used at the execution of the .yml file.

The content of this folder is pulled from the .git. 
  
## `03_Script folder`  
  
The folder **`03_Script`** contains a subfolder for each python script used in the database creation, data insertion and update. And it also contains a folder for the website creation scripts.  
- **`01_CreateDatabase`:** contains the script for the database tables and views creation.
- **`02_InsertData`:** contains the script for the data insertion in the database using reference files.
- **`03_UpdateDatabase`:** contains the script for the database update and archiving and for the website graphs pre-creation.
- **`04_Website`:** contains two subfolders : `static` and `templates`. In the `static` folder, you will find `'css'`, `'img'`, `'js'` and `'php'` folder containing respectively the css file, the images and the custom JavaScript and Php libraries for BactMentha website. The `templates` folder contains the scripts for each BactMentha Websit page.

The content of this folder is pulled from the .git.

## `05_Output folder`  
  
This folder will contain the database files and the database archives.

Get the archive folder containing the last versions of the database (DATABASE.2026-04-13.tar.gz) from the following Zenodo link:
https://doi.org/10.5281/zenodo.19498850

Extract the archive in 05_Output to get the folowing folder structure:
```bash
05_Output
  ├── 01_Database
  └── 02_Archive
```
  
### `01_Database`  

This folder contains the current files of the POSTGRESQL database. 
  
This folder will be modified by `postgres` if you update the database, so that you may not have the rights on it anymore. If you want to access the containing files, you may run the following command to `recover the rights on the folder and its containing elements`:  
```bash
sudo setfacl -R -m u:username:rwx /Your/Path/To/.../BactMentha-DB/05_Output/01_Database
```  
Don't forget to replace *username* by your personal one and to change the *path* to BactMentha-DB to fit your computer arborescence !  

At first when extracting the Zenodo, the database is complete and you have access on it.
  
### `02_Archive`  
  
This folder contains the archive versions of the database with a main archive folder for each version, containing different types of archives that enable the user to download all or only some information. The archive folders have the following structure (where *2025_08_27* is the date of the last update of the database) :  
   
```bash 
.
├── bm_archive_2024_02_01_complete.zip
├── bm_archive_2024_02_01_DatabaseTables_10090.zip
├── bm_archive_2024_02_01_DatabaseTables_10116.zip
├── bm_archive_2024_02_01_DatabaseTables_9606.zip
├── bm_archive_2024_02_01_DatabaseTables.zip
├── bm_archive_2024_02_01_dump.zip
├── bm_archive_2024_02_01_RawData_10090.zip
├── bm_archive_2024_02_01_RawData_10116.zip
├── bm_archive_2024_02_01_RawData_9606.zip
└── bm_archive_2024_02_01_RawData.zip
```  

Here is the content of the archive .zip files:  
- 1) *bm_archive_2024_02_01_complete.zip* : contains three subfolders for :  
    - raw_data (equivalent of the file *bm_archive_2024_02_01_RawData.zip*), the complete database tables and the dump file archive.
    - database_tables : all the database tables for all taxa and per host taxon (equivalent of the files *bm_archive_2024_02_01_DatabaseTables.zip* and *bm_archive_2024_02_01_DatabaseTables_XXXXX.zip*).  
    - dump : database dump file (also found in *bm_archive_2024_02_01_dump.zip*)
- 2) *bm_archive_2024_02_01_DatabaseTables_XXXXX.zip* : contains the part of the database tables that only concern the host taxon XXXXX for the specified database version (there will be as files of this type that host taxa in the database).
- 3) *bm_archive_2024_02_01_DatabaseTables.zip* : contains the complete database tables (all host taxa) for the specified database version. 
- 4) *bm_archive_2024_02_01_dump.zip* : contains the dump file provided to restore the full database with the tables constraints and relations.
- 5) *bm_archive_2024_02_01_RawData_XXXXX.zip* : contains the part of raw data used to create the database tables that only concern the host taxon XXXXX for the specified database version (there will be as much files of this type that host taxa in the database). 
- 6) *bm_archive_2024_02_01_RawData.zip* : contains all the raw_data files used to fill the database tables (including the WHO pathogenes classification file concerning the pathogenes found in the database).
 

- **`The database tables:`** all the database tables are exported into csv files.
- **`The row data:`** a copy a the used reference files for the last update for each taxon.  
    
# `II. How to execute the scripts`  
  
## `Preparation`  
  
Here is the default structure of the folder `BactMentha-DB`:  
  
```bash
.
├── 01_Reference
│   ├── 01_DatabaseTables
│   │   ├── 10090
│   │   ├── 10116
│   │   └── 9606
|   └── 02_WhoAnnotations
│   │   └── bactmentha_taxa_who-hazard_annotations.txt
├── 02_Container
│   └── Compose_postgres_python
|       ├── .env (hidden -> display with `Ctrl + H`)
│       ├── 01_python3
│       │   ├── Dockerfile
│       │   └── Readme.md
│       ├── 02_php
│       │   ├── Dockerfile
│       │   └── Readme.md
│       ├── 03_nginx
│       │   ├── conf
│       │   └── log
│       ├── compose_postgres_python.yml
│       └── Readme.md
├── 03_Script
│   ├── 01_CreateDatabase
│   │   └── createdb.py
│   ├── 02_InsertData
│   │   └── insertdata.py
│   ├── 03_UpdateDatabase
│   │   ├── createStatGraphs.py
│   │   └── updatedb.py
│   └── 04_Website
│       ├── static
│       │   ├── css
│       │   ├── img
│       │   ├── js
│       │   └── php
│       └── templates
│           ├── bactmentha_about.php
│           ├── bactmentha_archive.php
│           ├── bactmentha_contact.php
│           ├── bactmentha_data.php
│           ├── bactmentha_doc.php
│           ├── bactmentha_faq.php
│           ├── bactmentha_home.php
│           └── bactmentha_stats.php
├── 05_Output
│   ├── 01_Database
│   └── 02_Archive
│       └── bactmentha_version_2023_07_28.zip
└── Readme.md
```  

## `Modification of the .env file`  

You can find the .env file at : `Your/Path/To/.../BactMentha-DB/02_Container/Compose_postgres_python/.env`.  
This file must be modified as follows:  
- The `sections 2, 5, and 6 have to be modified` by replacing `'Your/Path/To/.../BactMentha-DB'` by your actual path to the project folder.  
- You `don't have to modify sections 3 and 4` unless you have changed the name of the mounted volume for python inside the compose_postgres_python.yml file.  
- `Section 1` is setting the database connection parameters and `should not be modified`.  
- Finally, `Section 7` enables to chose the python script to run when the python container is created by modifying the value of COMMAND to 0 (no script to run), 1 (running the tables creation if not exist), 2 (running the data insertion in the tables if they are empty) or 3 (update and archiving of the database as well as the pre-creation of the website graphs).  


## `Docker images creation`
  
This project is using **`docker`**. To run one of the wanted script, you first have to create the images that will be used in the docker-compose file.  

Go to the following path : `/Your/Path/To/.../BactMentha-DB/02_Container/Compose_postgres_python`.

In this folder, you will have to **`run the two images`** for *`Python`* and *`Php`* (the order of creation here doesn't import).  

Creation of the Python3 image using the given dockerfile that is in the folder *01_python3*:  
```bash
docker build -t bactmentha_python3 01_python3
```  

You can change "bactmentha_python3" by any name you want to give to the image (not recommanded), but you will also have to change it in the docker-compose file (we'll talk about it later). You can use "." instead of "01_python3" if you open your terminal in the 01_python3 folder.  

Creation of the php image using the given dockerfile in the folder 02_php:  
```bash
docker build -t bactmentha_php 02_php
```  
  
Same as previously, you can use "." for the path to the dockerfile if you are in the 02_php directory.  
 
  
## `Set the command to run for the python service in the .env file`   

If the database doesn't already exists (see in 'Your/Path/To/.../BactMentha-DB/02_Output/01_Database'), you have to create it with the first script:  
- Open the .env file, set the COMMAND variable to `1`, then save the file.  
- Build the compose_postgres_python.yml file up just once (see the next section for the command line).  
- Remove the container by using the same command as for the build and replace 'up' by 'down' at the end.  
- Follow the data insertion, and update and archiving step to insert the data and create the first archive of BactMentha database.  

If the database tables have been created with the first command but don't contain any data (especially for the the metadata table that is used in the update script), you have to run the data insertion script once before to do the udpates and archiving steps.
- Be sure that your containers have be stoped and removed (build down) before to do the data insertion (see the next section for the command line). 
- Open the .env file, set the COMMAND variable to `2`, then save the file.  
- Build the compose_postgres_python.yml file up just once (see the next section for the command line).  
- Remove the container by using the same command as for the build and replace 'up' by 'down' at the end.  

If the database has already been created and filled at least once, you can update the database and create an archive for the new version:  
- Be sure that your containers have be stoped and removed (build down) before to do the update (see the next section for the command line).  
- Open the .env file, set the COMMAND variable to `3`, then save the file.  
- Run the compose_postgres_python.yml (see the next section for the command line)  
- You can now start and use your containers (see command in the next section to start the containers).  

*If you want to stop and remove your containers for any reason and don't want to run any script when building them up, then just set the COMMAND variable of the .env file to 0 before to build your containers up again.*   
  
  
## `To run the docker compose file and make the container functionnal`
  
You might now be able to run the docker-compose file (don't forget if you have change your images names to change them in the docker-compose.yml file before this step).  
To creates those container, the only thing to do now is to run the following command (you might already be in the folder containing the docker-compose file) :  
```bash
docker-compose -f compose_postgres_python.yml up
```  
Now your containers are created (you can see it running ```docker ps -a``` in your terminal) but not functionnal. To start your containers and be able to display the data containing in them using the defined ports, use the following command in your terminal :  
```bash
docker start bactmentha_postgres bactmentha_adminer bactmentha_php bactmentha_nginx
```  
Those are the names set for the containers in the docker-compose file.  

If you want to run the compose file another time (to update the database after tables creation and data insertion for exemple, or for a new update) you have to stop and remove the running containers using:  
```bash
docker-compose -f compose_postgres_python.yml down
```  
  
Then you can set the command that you want to run in the .env file and use again the ```docker-compose -f compose_postgres_python.yml up``` command, and start again the containers.  

*NB: removing your containers won't erase your data unless it is saved localy thanks to shared volumes.*  
  
# `III. Requirements`
  
## `Using docker (recommanded)`
  
- Tested using `Docker version 24.0.5, build 24.0.5-0ubuntu1~22.04.1` and `Docker version 27.3.1, build ce12230`
- Tested using `docker-compose version 1.29.2`, build unknown and `Docker Compose version v2.29.7`.

  
## `Not using docker (not recommanded)`
  
*NB : This project has not been tested without docker.*  
*NB : for the `latest` versions, downloaded in date `April 2023`.*  
  
Python and its dependencies:  
- python3 version 3.9
- pip3 (latest)
- psycopg2 (the package used for the connection with the PostreSQL database) version 130009
- datetime (latest)
- pandas version 2.0.1
- numpy (latest)
- plotly (latest)
- kaleido (latest)
- postgresql-contrib (for the connexion with the database) (latest)  

Php and its dependencies:  
- php : 7.4 (-fpm-alpine3.13)
- pgsql extension for php (latest)
- pdo_pgsql etension for php (latest)
- pdo_pgsql extension (latest)  

Adminer:
- Adminer (latest version)  

Postgresql:
- Postgresql version 13.10 (Debian 13.10-1.pgdg110+1)
