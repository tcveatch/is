<?php include "/var/www/shared/local.php"; ?>
<?php
//
// is.php
//

/* This is a base example Information Structure file to derive your own from.
 * Copy this to .../ops/is/$NAME/$NAME.php, modify it with your own data structure(s).
 * It should be enough to specify an SQL table structure given a few good defaults,
 * and as populated through the octopus of a web app to be a data
   structure with named/typed elements.
 */
  $is =  [
          "Name" => "Test",
          "URL" =>  "https://<?php echo "$tomveatch";?>.com",
          "User" => "public_user", // crud ops only on this table within the 'tv' database.
          "Pwd" =>  "Mousbusication", 
          "Path" => "ops/is",
	  "BackupDir"=>"~/.mysql_backup/", // backup directory
	  "BackupDate" =>"20200203",      // specify B/backup.(BDate).sql to restore from
	  "DBName" => "tv",
	  "TableName" =>  "Test",
	  "Columns" =>  [ // [ col#, colName,    type,  N, def, InputType, SQL Type   ]
	     	             [    1,    "id","Number",  1,  -1,   "hidden", "INT AUTO_INCREMENT PRIMARY KEY"  ], 
			     [    2, "TestN","Number",  1,  -1,   "number", "SMALLINT DEFAULT -1" ],
		             [    3, "TestS","String", 20,  "",     "TEXT", "VARCHAR(20) CHARACTER SET utf8" ],
		             [    4, "TestB","Boolean", 1,   1, "checkbox", "BIT"     ], // checkbox|radio
		             [    5, "TestE","Enum",    1,   1,   "select", "ENUM('val1','val2','val3')" ] 
		             [    6, "TxTime",  "Date", 1,   0,   "hidden", "TIMESTAMP DEFAULT CURRENT_TIMESTAMP" ],
		     ],
	  "Adds"     => [    [    7, "TestD","Date",    1,   0,     "date", "DATETIME DEFAULT CURRENT_TIMESTAMP" ]
	  	     // If you are going to make a change in a table adding a column,
		     // then use this in $is$NAME.db modify_table first, then move
		     // Adds into Columns to reflect new reality.
		     ],
          "Criteria" =>  "An SQL substring to use in a SELECT WHERE command"
  ];  // PHP syntax. convert with json_encode($is) if needed.
  
/* Column type is a vague idea at present, but columm SQL Type is any
  (My)SQL variable type declaration.  From the MySQL pages, for example:
     Number types are the MySQL numeric types:
       BIT, (TINYINT(1)|BOOLEAN), (INT|INTEGER), SMALLINT, DECIMAL|DEC|FIXED), NUMERIC;
       FLOAT, (REAL|DOUBLE|DOUBLE PRECISION), 
     String types are CHAR, VARCHAR(len), BINARY, VARBINARY, BLOB, TEXT, ENUM, and SET.
     Temporal types include: DATE, TIME, DATETIME, TIMESTAMP, and YEAR.
  Furthermore: you can append stuff like:
     DEFAULT 'x' to set a default value for that colummn, too.
     NOT NULL to require it have some value (implied by PRIMARY KEY)
     AUTO_INCREMENT to count up the next value
     UNIQUE to make sure values in the column are unique (implied by PRIMARY KEY)
 */
?>
