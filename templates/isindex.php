<?php include "/var/www/shared/local.php"; ?>
<?php
  //
  // isindex.php
  //

  /* Here's a template index.php file for an IS SCRUD page 

     This program uses an IS data structure (in $NAME.php) to
     autogenerate a sample index.php file with an IS SCRUD UI.
     To use it:
     % php isindex.php $NAME > $NAME/index.php
   */
  $debug=1;
  if (defined('STDIN')) { $NAME=$argv[1];      }
  else                  { $NAME=$_GET['NAME']; }
  include "$NAME/$NAME.php";
?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
</head>
<body>

<!-- <div w3-include-html="is<?php echo $is['TableName']; ?>.ui.html"></div> -->

<P><div id="isCreateDiv"><B>Create</B> </div></P><HR COLOR="DARKBLUE">
<P><div id="isReadDiv"  ><B>Read</B>   </div></P><HR COLOR="DARKBLUE">
<P><div id="isUpdateDiv"><B>Update</B> </div></P><HR COLOR="DARKBLUE">
<P><div id="isDeleteDiv"><B>Delete</B> </div></P><HR COLOR="DARKBLUE">
<P><div id="isSearchDiv"><B>Search</B> </div></P><HR COLOR="DARKBLUE">

<script src="./isapi.js"></script>
<script src="./is<?php echo "$NAME";?>.js"></script>
<script>
	// Hang the isui divs inside the given divs.
	addSearch();
	addCreate();
	addRead();
	addUpdate();
	addDelete();
</script>
</body>
</html>


