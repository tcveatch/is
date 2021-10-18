<?php include "/var/www/shared/local.php"; ?>
<?php
//
// generates a Vue based UI for one or more basic SCRUD apps.
//

/*
For example, observation note-taking
a DB with ByWhom WhenSeen WhatWasSeen

Mainly the point of this will be to have a minimum vue app ish thing
that moves data from the UI over to calling isapi.js calls.
*/

$debug=0;
if (defined('STDIN')) { $NAME=$argv[1];      }
else                  { $NAME=$_GET['NAME']; }
include "$NAME/$NAME.php";

?>
<!DOCTYPE html><html><head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $NAME; ?> is a Vue App </title>
</head>
<body>
<div id="#app">
   <H1>{{ product }}</H1>
</div>
<script src="https://cdn.jsdelivr.net/npm/vue"></script>
<script src="isapi.js"></script>
<script> 
var app = new Vue({
 el: '#app',
 data: { // Modify this for one or more records to display edited, updated, read data.
         // Further, provide reactivity to populate it after isapi SCRUD calls
         // Further, provide examples of isapi SCRUD calls
         // Further, provide enough UI to make those isapi SCRUD calls with reasonable context.
   product: 'Socks'
 }
})
</script>
</body>
</html>
