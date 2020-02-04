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

<div w3-include-html="is<?php echo $is['TableName']; ?>.ui.html"></div>

<P><div id="isSearchDiv">S</div></P>
<P><div id="isCreateDiv">C</div></P>
<P><div id="isReadDiv"  >R</div></P>
<P><div id="isUpdateDiv">U</div></P>
<P><div id="isDeleteDiv">D</div></P>
<HR>

<script src="./is<?php echo "$is[Name]";?>.js"></script>
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


