# `CREATION OF THE PYTHON IMAGE FOR BACTMENTHA_DB`

This dockerfile permits to build an image starting from **`python 3.9`**.  
Thanks to this image, python will be able to `access the BactMentha Database`**` in the postgresql container.  
It will be able to create and modify the database.  

## `IMAGE CONTENT :`

This image contains :  
- python3 version 3.9
- pip3 (latest)
- psycopg2 (the package used for the connection with the PostreSQL database) version 130009
- datetime (latest)
- pandas version 2.0.1
- numpy (latest)
- plotly (latest)
- kaleido (latest)
- postgresql-contrib (for the connexion with the database) (latest)
- biopython (latest)
- git+https://github.com/evo-design/proto-tools.git (latest)

*NB : `latest` versions, downloaded in date `August 2026`.*

## `IMAGE BUILDING :`

The command to run a dockerfile in order to create an image is :  
```shell
docker build -t {image_name} {dockerfile_directory}
```  

If you want to run the python dockerfile from the 01_python3 directory, then run this command:  
```shell
docker build -t bactmentha_python3 .
```  

If you want to run the python dockerfile from the Compose_postgres_python directory, then run the command:  
```shell
docker build -t bactmentha_python3 01_python3
```  

If you change the name of your python image, you will have to make sure to change it also in the **`compose_postgres_python.yml`** file.










