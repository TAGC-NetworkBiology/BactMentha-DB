# -*- coding: utf-8 -*-
"""
Author : Lou BERGOGNE

Predefined functions to handle BactMentha database connection, queries etc...
"""

import psycopg2  # for database connection and queries execution
import pandas as pd

class Db_tasks:

    def __init__(self, db_name:str, db_host_name:str, db_user_name:str, db_pw:str, db_port:str):
        """Args:
            - db_name: name of the database to connect to (defined in the docker compose file / .env file)
            - db_host_name: name of the database host (In the docker compose file it is the service that contain the database)
            - db_user_name: user if often 'postgres' (defined in the docker compose file / .env file)
            - db_pw: the database password (defined in the docker compose file / .env file)
            - db_port: port to listen from the docker container (defined in the docker compose file / .env file)
        """
        self.db_name = db_name
        self.db_host_name = db_host_name
        self.db_user_name = db_user_name
        self.db_pw = db_pw
        self.db_port = db_port


    def create_server_connection(self) -> object:
        """
        To create the connection to the postgresql Database.
        The connection will be used in other functions to execute the queries.

        Return:
        - connection: a connection object
        """
        try:
            connection = psycopg2.connect(database = self.db_name, 
                                        host = self.db_host_name,
                                        user = self.db_user_name,
                                        password = self.db_pw,
                                        port = self.db_port)
            print("PostgreSQL Database connection successful")
        except:
            connection = None
            print("/!\ Error when trying Database connection /!\\\\")
        return connection


    def execute_single_query(self, query: str) -> None:
        """
        Execute a PostgreSQL query using a connection object.
        Logs only the first error and suppresses subsequent transaction-related errors.

        Args:
            - connection: a connection object to connect to the database
            - query: a PostgreSQL query to execute
            - state: a dictionary to track whether the first error has been logged

        Return: None
        """
        cdb = self.create_server_connection()
        try:
            cursor = cdb.cursor()
            cursor.execute(query)
            cdb.commit()
        except psycopg2.Error as e:
            cdb.rollback()
            with open("/BactMentha/03_Script/psycopg2_log_error.txt", "a") as log:
                log.write(
                    f"Error while executing query:\n"
                    f"{query}\n"
                    f"Error details: {e}\n\n"
                )
            print("Error logged in /BactMentha/03_Script/psycopg2_log_error.txt")
        finally:
            cursor.close()
            cdb.close()
        
    
    def execute_many_queries(self, queries: list) -> None:
        """
        Execute PostgreSQL queries using a connection object.
        Logs only the first error and suppresses subsequent transaction-related errors.

        Args:
            - queries: a list of PostgreSQL queries to execute

        Return: None
        """
        cdb = self.create_server_connection()
        try: 
            cursor = cdb.cursor()
            for query in queries:
                cursor.execute(query)
            cdb.commit()
        except psycopg2.Error as e:
            cdb.rollback()
            with open("/BactMentha/03_Script/psycopg2_log_error.txt", "a") as log:
                log.write(
                    f"Error while executing query:\n"
                    f"{query}\n"
                    f"Error details: {e}\n\n"
                )
            print("Error logged in /BactMentha/03_Script/psycopg2_log_error.txt")
        finally:
            cursor.close()
            cdb.close()


    def cursor_query_and_get_first_result(self, query:str) -> str:
        """
        Creates a cursor object and executes a query. Returns the first result of the query.

        Args:
            - query: psql query to execute using psycopg2 connection to the database.

        Return:
            - result: the first result of the query.
        """
        cdb = self.create_server_connection()
        try:
            cursor = cdb.cursor()
            cursor.execute(query)
            result = cursor.fetchone()[0] # to avoid the comma at the end
        except psycopg2.Error as e:
            cdb.rollback()
            with open("/BactMentha/03_Script/psycopg2_log_error.txt", "a") as log:
                log.write(
                    f"Error while executing query:\n"
                    f"{query}\n"
                    f"Error details: {e}\n\n"
                )
            print("Error logged in /BactMentha/03_Script/psycopg2_log_error.txt")
            result = None
        finally:
            cursor.close()
            cdb.close()
        return result


    def cursor_query_and_fetchall_in_df(self, query:str) -> pd.DataFrame:
        """Run the given query and get the resulting table in a pandas dataframe.

        Args:
            query (str): PostgreSQL query to execute.

        Returns:
            pd.DataFrame: Result of the query.
        """
        cdb = self.create_server_connection()
        try:
            cursor = cdb.cursor()
            cursor.execute(query)
            column_names = [desc[0] for desc in cursor.description]
            records = cursor.fetchall()
            cursor.close()
            result = pd.DataFrame(records, columns=column_names, index=None)
        except psycopg2.Error as e:
            cdb.rollback()
            with open("/BactMentha/03_Script/psycopg2_log_error.txt", "a") as log:
                log.write(
                    f"Error while executing query:\n"
                    f"{query}\n"
                    f"Error details: {e}\n\n"
                )
            print("Error logged in /BactMentha/03_Script/psycopg2_log_error.txt")
            result = None
        finally:
            cursor.close()
            cdb.close()
        return result
