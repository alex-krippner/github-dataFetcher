Create/Connect to the database using a method from the database class

    Check if a database file already exists
       If there is no database file create a new connection
            Define the DSN
                ??? Do I need a Database source name (DSN)
                ??? What options do we need?
            Initiate a new php database object
                ??? What parameters need to be passed to to the PDO?
                        "sqlite:"  and the path to the sqlite file

Create table using a method from the database class
    Use the up to date sql schema files in the data directory
    ??? Do I need to prepare the statements before creating tables?

Insert repos.json into database using a method from the database class
    ??? What controls are there to check if repos.json already been added to database?
    duplicate data p
??? Render admin view

Allow user to interact with data