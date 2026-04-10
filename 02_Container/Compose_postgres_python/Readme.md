# `DOCKER-COMPOSE FILE FOR BACTMENTHA_DB USING DOCKER`

## `SERVICES THAT WILL BE CREATED`

Five containers will be created by running the `compose_postgres_python.yml` file.  
This file is using the environment variables from the `.env` file that might be hidden. Use `Ctrl+H` in the compose_postgres_python.yml file directory
to display it. Don't forget to replace the path by your computer structure (If you didn't change the structure of the BactMentha-DB project folder, you would only have to change the begginning of each path when necessary).

### `1- BACTMENTHA_POSTGRES`

Postgresql is the SGBD used to manage the database.  
This service is built with an **`official postgres image`** from Dockerhub : `postgres:13`.  
This service will share a volume with the computer to retrieve the data from the database.  

### `2- BACTMENTHA_PYTHON3`

Python is the language of the script for database creation, data insertion and update / archiving.  
This service is built from the **`bactmentha_python3 image created from the dockerfile`** in the relative path `/01_python3`.  
If this image has not already been built, you can find the instruction to build it in the `Readme.md` file from the same directory.  
When the compose_postgres_python.yml file is running, some **`command can be executed`** from this python container. You can see more informations about which command to run in the `Readme.md` from `/01_python3` folder.  
This service shares some volumes with the computer to access the scripts and all the project.  

### `3- BACTMENTHA_ADMINER`

Adminer Database Manager as been added to the project to see the results of the python script in a web navigator.  
This service is built from an **`official adminer image`** from Dockerhub : `adminer` (latest).  

### `4- BACTMENTHA_NGINX`

Nginx is the webserver that will permit to display the BactMentha_DB website in a web navigator.  
This service is build from an **`official nginx image`** from DockerHub : `nginx` (latest).  
This service shares some volumes with the computer to access the modified configuration file and some log files in the `03_nginx` folder, and to access the website scripts.  
This service needs PHP to be able to display the website correctly.  

### `5- BACTMENTHA_PHP`

PHP is used to display the database website.  
This service is built from the **`bactmentha_php image created from the dockerfile`** in the relative path `02_php`.  
If this image has not already been built, you can find the instruction to build it in the `Readme.md` file from the same directory.  
This service shares some volumes with the computer to access the database scripts and the database tables.  


## `HOW TO RUN THE DOCKER-COMPOSE FILE`

You might now be able to run the docker-compose file (don't forget if you have change your images names to change them in the docker-compose.yml file before this step).  
To creates those container, the only thing to do now is to run the following command (you might already be in the folder containing the docker-compose file) :
```shell
docker-compose -f compose_postgres_python.yml up
```  
Now your containers are created (you can see it running `docker ps -a` in your terminal) but not functionnal. To start your containers and be able to display the data contained in them using the defined ports, use the following command in your terminal :  
```shell
docker start bactmentha_postgres bactmentha_adminer bactmentha_php bactmentha_nginx
```  
Those are the names set for the containers in the docker-compose file.  

If you want to run the compose file another time (to update the database after tables creation and data insertion for exemple, or for a new update) you have to stop and remove the running containers using:  
```shell
docker-compose -f compose_postgres_python.yml down
```  

Then you can set the command that you want to run at the bactmentha_python3 container creation removing the comment '#' of one of them and start again the containers with the `docker-compose -f compose_postgres_python.yml up` command.  
`/!\ you might uncomment only one command at a time and be careful of the indentation (you may have to add/remove spaces)`   


## `LINK TO DOCKERHUB OFFICAL IMAGES`

- official postgres image dockerhub link : https://hub.docker.com/_/postgres  
- official adminer image dockerhub link : https://hub.docker.com/_/adminer  
- oficial nginx image dockerhub link : https://hub.docker.com/_/nginx  