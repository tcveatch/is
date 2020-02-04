<?php
//
// isdb.php:
//

  /* This program uses an IS data structure (in $NAME.php) to
     autogenerate (via PHP self-rewriting code) a set of IS MySQL commands
     to set up, grow, shrink, maintain, backup,
     and to use (via a SCRUD API called from server side PHP) a database.
   */
  $debug=0;
  
  if (defined('STDIN')) { $NAME=$argv[1];      }
  else                  { $NAME=$_GET['NAME']; }
  include "$NAME/$NAME.php";
  if ($debug) echo "is[User]=" . $is['User'] . "\n";
  
  $pfx = "mysql -u" . $is['User'] . " -p" . $is['Pwd'] . " ";
  $rpfx = "cpanel->phpMyAdmin->SQL->";

  if (isset($is['BackupDate'])) {
    $d = $is['BackupDate'];
  } else {
    $d = date("Ymd");
  }

  // might need a JSON record to be inserted.  { fea = val; ... }
  $insertData = '{"fea1":"val1","fea2":"val2","fea3":"val3"}';
  if ($debug)
    echo "insertData=$insertData\n";
  $rec = json_decode($insertData,true);
  if ($debug)
    echo "rec=";
  if ($debug)
    var_dump($rec);
  $features = join(",",array_keys($rec));
  $values = join(",",array_values($rec));
  // $whereclause = "($features) VALUES ($values)";

?>
#/bin/bash

if [ "$#" -ne 1 ]; then
  echo Usage: $0 CMD
  echo "where CMD is one of: "
  echo "  create_user"
  echo "  create_db"
  echo "  backup_db"
  echo "  restore_db"
  echo "  create_table"
  echo "  modify_table"
  echo "  create"
  echo "  read"
  echo "  update"
  echo "  delete"
  exit 2
fi

if   [ $1 == "create_user" ]; then
  echo "First create a DB, then create a user with privileges on that DB.";
  echo "You cannot use cpanel->phpMyAdmin, instead use ";
  echo "cpanel->MySQL DB Wizard->Create DB Test";
  echo "After that I created user tv with all privileges, then user public_user with CRUD privileges";
  echo "cpanel->MySQL DB Wizard->Create User public_user, ";
  echo "  password ...->privileges DELETE INSERT SELECT UPDATE";
  echo "Also see is.php";
elif [ $1 == "create_db" ]; then
  # Context of use: One time only, by the administrator, perhaps also inside the restore operation from a backup.
  echo "First create a DB, then users, then give users privileges on it.";
  echo "cpanel->MySQL DB Wizard->Create DB Test";
elif [ $1 == "backup_db" ]; then
  # Context of use: periodically by a cron job
  # Back up just the db in $NAME.php
  echo "Do this: <?php echo "% mysqldump -u=" . $is['User'] . " -p=" . $is['Pwd'] . " --databases " . $is['DBName'] . " > " . $is['BackupDir'] . "backup.$d.sql"; ?>";
elif [ $1 == "restore_db" ]; then
  # Context of use: after a disaster or infrastructure change by the admin.
  # restore the backup specified by BDate in $NAME.php  
  echo "Do this: <?php echo "% mysql < $is[BackupDir]backup.$is[BackupDate].sql"; ?>";
elif [ $1 == "create_table" ]; then
  # You have to use cpanel->MySQL DB Wizard to do administrative ops like creating &
  # dropping DBs.  Hopefully as mere user tv you can create/drop tables.
  # whereas as user public_user you can only do CRUD ops on these tables.

  # Context of use: Once only, by the app developer, perhaps again to rebuild.
  echo "<?php
         $table_create_command = "CREATE TABLE IF NOT EXISTS " . $is['TableName'] . " ( \n\t";
	 foreach( $is['Columns'] as list( $id, $colName, $type, $N, $def, $InputType, $SQL_Type )) {
	   if ( $id != "1" ) {
	     $table_create_command .= ", \n\t";
	   }
	   $table_create_command .= $colName . " "; // column name
	   $table_create_command .= $SQL_Type; // column MySQL data type
         }
	 $table_create_command .= "\n) ENGINE=INNODB;\n";
	 echo "$rpfx $table_create_command"; 
    ?>";
elif [ $1 == "modify_table" ]; then
  # Context of use: by developer when the app changes and needs another column.
  echo "<?php
          $table_modify_command = "";
          foreach ($is['Adds'] as list( $id, $colName, $type, $N, $def, $InputType, $SQL_Type )) {
            $table_modify_command .= "ALTER TABLE $is[DBName].$is[TableName] ADD "
			  . $colName . " "    // column name
			  . $SQL_Type . ";\n"; // column MySQL data type
          }
          echo "$rpfx $table_modify_command"; 
    ?>";
elif [ $1 == "create" ]; then
  # Context of use: by the app itself when used by a client
  echo "<?php echo $pfx; ?> INSERT INTO TABLE $is[DBName].$is[TableName]$rec[Insert];";
elif [ $1 == "read" ]; then
  # Context of use: by the app itself when used by a client
  # expecting features to be "*" or a joined subset of column names.
  # expecting SelectString to look like "WHERE id=23" or some other search criteria
  echo "<?php echo "$pfx SELECT " . ($features?$features:"*") . " FROM $is[DBName].$is[TableName] SelectString;"; ?>";
elif [ $1 == "update" ]; then
  echo Context of use: by the app itself when used by a client
  echo expecting UpdateString looks like "SET col1 = val1, col2 = val2, ... WHERE [condition]"
  echo "<?php echo "$pfx UPDATE TABLE $is[DBName].$is[TableName] UpdateString;"; ?>";
elif [ $1 == "delete" ]; then
  # Context of use: by the app itself when used by a client
  # expecting DeleteString looks like "WHERE `id` = 32"
  echo "<?php echo "$pfx DELETE FROM TABLE $is[DBName].$is[TableName] DeleteString;"; ?>";
else
  echo "Argument $1 not recognized. Please try again."
fi
exit 
