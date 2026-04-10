# `CREATION OF THE PHP IMAGE FOR BACTMENTHA_DB`

This dockerfile permits to build an image starting from **`php : 7.4-fpm-alpine3.13`**.  
Thanks to this image, the webserver nginx (if used, else the computer) will be able to `access the BactMentha Database`.  
It will be able to make some requests on the database to display the data on the webpage.  

## `IMAGE CONTENT :`

This image contains :  
- php : 7.4-fpm-alpine3.13
- pgsql extension for php (latest)
- pdo_pgsql etension for php (latest)
- pdo_pgsql extension (latest)

*NB : for the `latest` versions, downloaded in date `April 2023`.*

A copy of the *`php.ini`* file is also added to the image so ensure this file is located in the same folder as the dockerfile before to build the image. Here is the php.ini content:
post_max_size = 20M
memory_limit = 256M
max_input_vars = 15000
max_execution_time = 3600


## `IMAGE BUILDING :`

The command to run a dockerfile in order to create an image is :  
```shell
docker build -t {image_name} {dockerfile_directory}
```  

If you want to run the python dockerfile from the 02_php directory, then run this command:  
```shell
docker build -t bactmentha_php .
```  

If you want to run the python dockerfile from the Compose_postgres_python directory, then run the command:  
```shell
docker build -t bactmentha_php 02_php
```  

If you change the name of your python image, you will have to make sure to change it also in the **`compose_postgres_python.yml`** file.

