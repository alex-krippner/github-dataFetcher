# Oversight

    The aim of Oversight is to provide overview of dokuwiki plugins
    using data from github's REST API.

    The application is based on a MVC structure, with currently the 
    index.php and admin.php files executing controller commands.

    Requesting <ip-address>/index.php through the localhost will 
    render a table of plugins.

    Requesting <ip-address>/admin.php will initialise a database with 
    the up to date schema and insert plugin data into local SQLite database,
    with the help of a local JSON file (containing a list of dokuwiki plugins)
    and githubs REST API.

    Requesting <ip-address>/admin.php?get=contributors will additionally fetch 
    contributor data from the github REST API.