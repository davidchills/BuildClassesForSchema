<?php

	class BUILD_CLASS {
		
		/**
		 * Usually I wouldn't put my database credentials here, but this isn't really code to be used in any projects.
		 * It's just code to get you started on project where the database is already modeled.
		 */
		private $dbConn = null;
		private $dbHost = "";
		private $dbPort = "";
		private $dbSchema = "information_schema";
		private $dbUser = "";
		private $dbPass = "";
		private $buildDirectory = null;
		private $targetSchemaName = null;
		
		public function __construct(String $buildDirectory, String $targetSchemaName, $targetTable = null) {
			// Verify the build path is a directory. Could also check for write permissions.
			if (!is_dir($buildDirectory) || !is_writable($buildDirectory)) {
				error_log("Build path is not valid!");
				return;
			}
			else { $this->buildDirectory = $buildDirectory; }
			// Wont know if the schema name is valid, but it at least needs to be a valid string.
			if (is_null($targetSchemaName) || !is_string($targetSchemaName)) {
				error_log("Target Schema is not a valid string!");
				return;
			}
			else { $this->targetSchemaName = $targetSchemaName; }
			
			$this->createDatabaseConnection();
			if (is_null($targetTable)) { $this->fetchTableNames(); }
			elseif (is_array($targetTable)) { $this->fetchColumnData($targetTable); }
			elseif (is_string($targetTable)) { $this->fetchColumnData(array($targetTable)); }
			
		}
		
		private function createDatabaseConnection() : void {
			try {
				$this->dbConn = new pdo("mysql:host=".$this->dbHost.";port=".$this->dbPort.";dbname=".$this->dbSchema, $this->dbUser, $this->dbPass);
				$this->dbConn->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
				$this->dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->dbConn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
			catch(PDOException $e) { print "DB Error: ".$e; }
		}
		
		private function fetchTableNames() : void {
			$tableCollection = array();
			$statement = $this->dbConn->prepare("select table_name from `information_schema`.`TABLES` where TABLE_SCHEMA = :targetSchemaName");
			$statement->bindValue(':targetSchemaName', $this->targetSchemaName, PDO::PARAM_STR);
			try { $statement->execute(); }
			catch (PDOException $e) {
				$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
				$errorString .= $e->getMessage();
				error_log($errorString);
			}			
			if (is_object($statement) && get_class($statement) == 'PDOStatement') {
				while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
					$tableCollection[] = $row->table_name;
				}
			}
			$statement = null;
			$this->fetchColumnData($tableCollection);
		}
		
		private function fetchColumnData(Array $tableCollection) : void {
			$columnCollection = array();
			$statement = $this->dbConn->prepare("select 
			table_name,
			column_name,
			column_default,
			is_nullable,
			data_type,
			character_maximum_length,
			numeric_precision,
			column_type
			from `information_schema`.`COLUMNS` where TABLE_SCHEMA = :targetSchemaName and TABLE_NAME = :tableName");
			$statement->bindValue(':targetSchemaName', $this->targetSchemaName, PDO::PARAM_STR);
			foreach ($tableCollection as $tableName) {
				$statement->bindValue(':tableName', $tableName, PDO::PARAM_STR);
				try { $statement->execute(); }
				catch (PDOException $e) {
					$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
					$errorString .= $e->getMessage();
					error_log($errorString);
				}
				if (is_object($statement) && get_class($statement) == 'PDOStatement') {
					while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
						$columnCollection[$row->table_name][] = $row;
					}
				}				
			}
			$statement = null;
			$this->buildClassFile($columnCollection);
		}
		
		private function buildClassFile(Array $columnCollection) : void {
			foreach($columnCollection as $columnData) {
				$newClassName = strtoupper($columnData[0]->table_name);
				$newClassFileName = $this->buildDirectory.'/'.$newClassName.'.inc';
				if (!file_exists($newClassFileName)) {
					$classContent = "<?php\n\n";
					$classContent .= "class ".$newClassName." extends MAIN {\n\n";
					// Declare all the columns as varaibles.
					$classContent .= $this->addVarEntries($columnData);
					// Add a construct method.
					$classContent .= $this->addConstructorMethod($columnData);
					// Add a create method.
					$classContent .= $this->addCreateMethod($columnData);
					// Add an update method.
					$classContent .= $this->addUpdateMethod($columnData);
					// Add a delete method.
					$classContent .= $this->addDeleteMethod($columnData);	
					// Add Magic Set method.
					$classContent .= $this->addMagicSetMethod($columnData);
					// Add Magic Get method.
					$classContent .= $this->addMagicGetMethod($columnData);
					// Add Magic Isset method.
					$classContent .= $this->addMagicIssetMethod($columnData);
					// Add Magic Unset method.
					$classContent .= $this->addMagicUnsetMethod($columnData);

					$classContent .= "}\n?>";
					$fileHandle = fopen($newClassFileName, "xb");
					fwrite($fileHandle, $classContent);
					
				}
			}
		}
		
		private function addVarEntries(Array $columnData) : String {
			$returnString = "private \$dbConnection = null;\n";
			foreach ($columnData as $column) {
				$returnString .= "\tprotected $".$column->column_name." = ";
				if (in_array($column->data_type, ['tinyint','smallint','mediumint','int','bigint','double','float','integer','numeric','decimal','double'])) {
					if ($column->column_default) { $returnString .= $column->column_default.";\n"; }
					elseif ($column->is_nullable === 'YES') { $returnString .= "null;\n"; }
					else { $returnString .= "0;\n"; }
				}
				elseif (in_array($column->data_type, ['char','varchar','binary','varbinary','blob','text'])) {
					if ($column->column_default) { $returnString .= "\"".$column->column_default."\";\n"; }
					elseif ($column->is_nullable === 'YES') { $returnString .= "null;\n"; }
					else { $returnString .= '""'.";\n"; }
				}
				elseif (in_array($column->data_type, ['set','enum'])) {
					if ($column->column_default) { $returnString .= "\"".$column->column_default."\";\n"; }
					elseif ($column->is_nullable === 'YES') { $returnString .= "null;\n"; }
					else { $returnString .= '""'.";\n"; }					
				}
			}
			return $returnString;
		}

		private function addConstructorMethod(Array $columnData) : String {
			$returnString = "\n\n\tpublic function __construct(\$id = null) {\n";
			$returnString .= "\t\t// Maybe you do a query here to populate your object from the database.\n";
			$returnString .= "\t\t\$this->dbConnection = DBCONN::getInstance();\n";
			$returnString .= "\t\tif (is_null(\$id)) { return; }\n";
			$returnString .= "\t\t\$statement = \$this->dbConnection->prepare(\"select * from ".$columnData[0]->table_name." where id = :id\");\n";
			$returnString .= "\t\t\$statement->bindParam(':Id', \$id, PDO::PARAM_INT);\n";
			$returnString .= "\t\ttry { \$statement->execute(); }\n";
			$returnString .= "\t\tcatch (PDOException \$e) {\n";
			$returnString .= "\t\t\t\$errorString = \"Error in Class::Method - \".__METHOD__.\" near line: \".__LINE__.\":\\n\";\n";
			$returnString .= "\t\t\t\$errorString .= \$e->getMessage();\n";
			$returnString .= "\t\t\terror_log(\$errorString);\n";
			$returnString .= "\t\t}\n";
			
			$returnString .= "\t\tif (is_object(\$statement) && get_class(\$statement) == 'PDOStatement') {\n";
			$returnString .= "\t\t\twhile (\$row = \$statement->fetch(PDO::FETCH_OBJ)) {\n";
			$returnString .= "\t\t\t\t// Set your instance variables from the database values.\n";
			// Set the instance variables from the query results.
			foreach ($columnData as $column) {
				$returnString .= "\t\t\t\t\$this->".$column->column_name." = \$row->".$column->column_name.";\n";
			}
			
			$returnString .= "\t\t\t}\n";
			$returnString .= "\t\t}\n";
			
			$returnString .= "\t}\n\n";
			return $returnString;
		}
		
		private function addCreateMethod(Array $columnData) : String {
			$columnNames = array();
			foreach ($columnData as $column) { $columnNames[] = $column->column_name; }
		
			$returnString = "\n\n\tpublic function create() {\n";
			$returnString .= "\t\t// Do a database insert to create your object.\n";
			$returnString .= "\t\t\$statement = \$this->dbConnection->prepare(\"insert into ".$columnData[0]->table_name."(\n";
			$returnString .= "\t\t".implode(",\n\t\t",$columnNames);
			$returnString .= "\n\t\t) values (\n";
			$returnString .= "\t\t:".implode(",\n\t\t:",$columnNames);
			$returnString .= ")\");\n";
			
			foreach ($columnData as $column) {
				if (in_array($column->data_type, ['tinyint','smallint','mediumint','int','bigint','integer'])) {
					$returnString .= "\t\t\$statement->bindValue(':".$column->column_name."', \$this->".$column->column_name.", PDO::PARAM_INT);\n";
				}
				elseif (in_array($column->data_type, ['char','varchar'])) {
					$returnString .= "\t\t\$statement->bindValue(':".$column->column_name."', \$this->".$column->column_name.", PDO::PARAM_STR, strlen(\$this->".$column->column_name."));\n";
				}
				elseif (in_array($column->data_type, ['binary','varbinary','blob','text'])) {
					$returnString .= "\t\t\$statement->bindValue(':".$column->column_name."', \$this->".$column->column_name.", PDO::PARAM_LOB, strlen(\$this->".$column->column_name."));\n";
				}
				else { $returnString .= "\t\t\$statement->bindValue(':".$column->column_name."', \$this->".$column->column_name.");\n"; }
			}
			$returnString .= "\t\ttry { \$statement->execute(); }\n";
			$returnString .= "\t\tcatch (PDOException \$e) {\n";
			$returnString .= "\t\t\t\$errorString = \"Error in Class::Method - \".__METHOD__.\" near line \".__LINE__.\":\";\n";
			$returnString .= "\t\t\t\$errorString .= \$e->getMessage();\n";
			$returnString .= "\t\t\terror_log(\$errorString);\n\t\t}\n";
			$returnString .= "\t\tif (\$statement->rowCount() == 1) {\n";
			$returnString .= "\t\t\t\$id = \$this->dbConnection->lastInsertId();\n\t\t}\n";
			$returnString .= "\t\t\$statement = null;\n";
			$returnString .= "\t\treturn \$id;\n";
			$returnString .= "\t}\n\n";
			return $returnString;
		}		
		
		private function addUpdateMethod(Array $columnData) : String {			
			$returnString = "\n\n\tpublic function update(\$id = null) : void {\n";
			$returnString .= "\t\t// Do a query here to save your object back to the database.\n";
			$returnString .= "\t\t\$statement = \$this->dbConnection->prepare(\"update ".$columnData[0]->table_name." set";
			
			$subString = "";
			foreach ($columnData as $column) {
				$subString .= "\n\t\t".$column->column_name." = :".$column->column_name.",";
			}
			$returnString .= rtrim($subString, ",");
			
			$returnString .= "\n\t\twhere id = :id\");\n";
			
			foreach ($columnData as $column) {
				if (in_array($column->data_type, ['tinyint','smallint','mediumint','int','bigint','integer'])) {
					$returnString .= "\t\t\$statement->bindValue(':".$column->column_name."', \$this->".$column->column_name.", PDO::PARAM_INT);\n";
				}
				elseif (in_array($column->data_type, ['char','varchar'])) {
					$returnString .= "\t\t\$statement->bindValue(':".$column->column_name."', \$this->".$column->column_name.", PDO::PARAM_STR, strlen(\$this->".$column->column_name."));\n";
				}
				elseif (in_array($column->data_type, ['binary','varbinary','blob','text'])) {
					$returnString .= "\t\t\$statement->bindValue(':".$column->column_name."', \$this->".$column->column_name.", PDO::PARAM_LOB, strlen(\$this->".$column->column_name."));\n";
				}
				else { $returnString .= "\t\t\$statement->bindValue(':".$column->column_name."', \$this->".$column->column_name.");\n"; }			
			}
			$returnString .= "\t\ttry { \$statement->execute(); }\n";
			$returnString .= "\t\tcatch (PDOException \$e) {\n";
			$returnString .= "\t\t\t\$errorString = \"Error in Class::Method - \".__METHOD__.\" near line \".__LINE__.\":\";\n";
			$returnString .= "\t\t\t\$errorString .= \$e->getMessage();\n";
			$returnString .= "\t\t\terror_log(\$errorString);\n\t\t}\n";			
			$returnString .= "\t\t\$statement = null;\n";
			$returnString .= "\t}\n\n";
			return $returnString;
		}
		
		private function addDeleteMethod(Array $columnData) : String {
			$returnString = "\n\n\tpublic function delete(\$id = null) : void {\n";
			$returnString .= "\t\t// Delete your object from the database.\n";
			$returnString .= "\t\t\$statement = \$this->dbConnection->prepare(\"delete from ".$columnData[0]->table_name." where id = :id\");\n";
			$returnString .= "\t\t\$statement->bindValue(':id', \$id);\n";
			$returnString .= "\t\ttry { \$statement->execute(); }\n";
			$returnString .= "\t\tcatch (PDOException \$e) {\n";
			$returnString .= "\t\t\t\$errorString = \"Error in Class::Method - \".__METHOD__.\" near line \".__LINE__.\":\";\n";
			$returnString .= "\t\t\t\$errorString .= \$e->getMessage();\n";
			$returnString .= "\t\t\terror_log(\$errorString);\n\t\t}\n";
			$returnString .= "\t\t\$statement = null;\n";
			$returnString .= "\t}\n\n";
			return $returnString;
		}	
		
		private function addMagicSetMethod(Array $columnData) : String {
			$returnString = "\n\n\tpublic function __set(\$inName, \$inValue) : void {\n";
			foreach ($columnData as $column) {
				$returnString .= "\t\tif (\$inName == '".$column->column_name."') {\n";
				if (in_array($column->data_type, ['tinyint','smallint','mediumint','int','bigint','double','float','integer','numeric','decimal','double'])) {
					$returnString .= "\t\t\tif (is_numeric(\$inValue)) { \$this->".$column->column_name." = \$inValue; }\n";
					if ($column->column_default) { $returnString .= "\t\t\telse { \$this->".$column->column_name." = ".$column->column_default."; }\n"; }
					elseif ($column->is_nullable === 'YES') { $returnString .= "\t\t\telse { \$this->".$column->column_name." = null; }\n"; }
					else { $returnString .= "\t\t\telse { \$this->".$column->column_name." = 0; }\n"; }
				}
				elseif (in_array($column->data_type, ['char','varchar','binary','varbinary','blob','text'])) {
					if ($column->character_maximum_length) { $returnString .= "\t\t\tif (\$inValue) { \$this->".$column->column_name." = substr(\$inValue, 0, ".$column->character_maximum_length."); }\n"; }
					if ($column->is_nullable === 'YES') { $returnString .= "\t\t\telseif (is_null(\$inValue)) { \$this->".$column->column_name." = null; }\n"; }
					if ($column->column_default) { $returnString .= "\t\t\telse { \$this->".$column->column_name." = '".$column->column_default."'; }\n"; }
				}
				elseif (in_array($column->data_type, ['set','enum'])) {
					$returnString .= "\t\t\tif (in_array(\$inValue,['Y','N'])) { \$this->".$column->column_name." = \$inValue; }\n";
					$returnString .= "\t\t\telse { \$this->".$column->column_name." = '".$column->column_default."'; }\n";
				}
				$returnString .= "\t\t}\n";
			}
			$returnString .= "\t}\n";
			return $returnString;
		}
		
		private function addMagicGetMethod(Array $columnData) : String {
			$returnString = "\n\n\tpublic function __get(\$inName) {\n";
			$returnString .= "\t\t\$outVal = null;\n";
			foreach ($columnData as $column) {
				$returnString .= "\t\tif (\$inName == '".$column->column_name."') {\n";
				if (in_array($column->data_type, ['tinyint','smallint','mediumint','int','bigint','integer'])) {
					$returnString .= "\t\t\tif (is_numeric(\$this->".$column->column_name.")) { \$outVal = intval(\$this->".$column->column_name."); }\n";
					if ($column->is_nullable === 'YES') { $returnString .= "\t\t\telse { \$outVal = null; }\n"; }
					else { $returnString .= "\t\t\telse { \$outVal = 0; }\n"; }
				}
				elseif (in_array($column->data_type, ['double','float','integer','numeric','decimal','double'])) {
					$returnString .= "\t\t\tif (is_numeric(\$this->".$column->column_name.")) { \$outVal = floatval(\$this->".$column->column_name."); }\n";
				}
				elseif (in_array($column->data_type, ['char','varchar','binary','varbinary','blob','text'])) {
					if ($column->is_nullable === 'YES') { 
						$returnString .= "\t\t\tif (is_null(\$this->".$column->column_name.")) { \$outVal = null; }\n";
						$returnString .= "\t\t\telse { \$outVal = \$this->".$column->column_name."; }\n";
					}
					else { $returnString .= "\t\t\t\$outVal = \$this->".$column->column_name.";\n"; }
				}
				elseif (in_array($column->data_type, ['set','enum'])) {
					$returnString .= "\t\t\tif (in_array(\$this->".$column->column_name.",['Y','N'])) { \$outVal = \$this->".$column->column_name."; }\n";
					$returnString .= "\t\t\telse { \$outVal = ".$column->column_default."; }\n";
				}
				$returnString .= "\t\t}\n";
			}
			$returnString .= "\t}\n";
			return $returnString;
		}	
		
		private function addMagicIssetMethod(Array $columnData) : String {
			$returnString = "\n\n\tpublic function __isset(\$inName) {\n";
			$returnString .= "\t\t\$outVal = false;\n";
			foreach ($columnData as $column) {
				$returnString .= "\t\tif (\$inName == '".$column->column_name."') { \$outVal = (\$this->".$column->column_name.") ? true : false; }\n";
			}
			$returnString .= "\t\treturn \$outVal;\n";
			$returnString .= "\t}\n";
			return $returnString;			
		}
		
		private function addMagicUnsetMethod(Array $columnData) : String {
			$returnString = "\n\n\tpublic function __unset(\$inName) {\n";
			foreach ($columnData as $column) {
				$returnString .= "\t\tif (\$inName == '".$column->column_name."') { ";
				if ($column->column_default) { $returnString .= "\$this->".$column->column_name." = ".$column->column_default."; }\n"; }
				elseif ($column->is_nullable === 'YES') { $returnString .= "\$this->".$column->column_name." = null; }\n"; }
				elseif (in_array($column->data_type, ['tinyint','smallint','mediumint','int','bigint','integer'])) { $returnString .= "\$this->".$column->column_name." = 0; }\n"; }
				elseif (in_array($column->data_type, ['char','varchar','binary','varbinary','blob','text'])) { $returnString .= "\$this->".$column->column_name." = ''; }\n"; }
			}			
			$returnString .= "\t}\n";
			return $returnString;			
		}		
		
		
	}
	$buildDirectory = "/Users/daveh/TestBuildLocation";
	$targetSchemaName = "plex_meta";
	$targetTable = "movie_studio";
	//$targetTable = array("movie_studio","Users");
	$testObj = new BUILD_CLASS($buildDirectory, $targetSchemaName, $targetTable);
?>