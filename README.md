# BuildClassesForSchema
Build PHP class files from an existing MySQL schema
Since this the first run at this, not all data types are working properly.

Assumptions that the database user will have read access to the information_schema.

Any class files generated also assume that they inherit from a class called "MAIN" and this class should include an autoload method to dynamically load the generated class files as they are needed. 

There is also an assumption that there is a class "DBCONN" which will return a valid database connection when called statically. Or you could create the datbase connection on the constructor of the MAIN class can call parent::__construct() in each generated class, so that a database connection is available. 

Assuming that php will be able to write to the directory you set for the build directory.

The $buildDirectory and $targetSchema are required values. 

If $targetTable is a string and the name of an existing table in the target schema, a class file for that table will be created.

If $targetTable is an array of existing table names in the target schema, it will create a class file for each table.

If $targetTable is null, it will pull all the table names for the schema and create a class file for each one.

The class files will be basic CRUD method and will most likely need to be modified some, but it gets a lot of the work done.
