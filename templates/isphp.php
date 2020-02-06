<?php
  //
  // isphp.php (PHP generating PHP)
  //

  /* This program uses an IS data structure (in $NAME.php) to
     autogenerate a PHP-includeable library/function file that provides
     an authenticated server API to those SQL CRUD operations on
     server's DB returning CRUD results to client.

     To use it:
     % php isphp.php $NAME
     or from a browser:
     http:path.to.here/isphp.php$NAME=yourtablename
   */
  /* Bug trail:

   x learn to use error_log()
   x condition $_POST, $input on having it set
   x Handle nonexistent REQUEST_METHOD (when called on command-line)
   x add $link arg to mysqli_error().
   x Table tv.Test doesn't exist. got isTest.db create_table to work.
   x added data to xhr.send().
   x with Content-type: application/json headers reading the php://input produces a string.
   x Again php Test/isTest.php says Table tv.Test doesn't exist: Test/isTest.db create_table yet again!
   x input an Object unless json_decode(..,TRUE).  Fixed.
   x empty query, sql undefined: edge case found and replaced with a Delete op
   x figured out how to get data into the server code: json_decode(file_get_contents(php://input))
   x syntax errors for make tc
   x  mysqli_query returns "1"  (and row is created) but mysqli_info() returns NULL.
   x syntax errors for make tr
   x syntax errors for make tu
   x syntax errors for make td
   x curl works but a form from a browser doesn't (had to clean up the output and make good json on return)
   x how? after isTest.php we get Navigated to /?<form-data>
   x	since trying to send JSON, set header for application/json
   x	also since JSON, upon failure to get approval, it backs up,
   x	     re-encodes JSON as overwritten URL ?a=b&c=d..\
   x	     and tries again but breaking the URL.
   x CRUD ops don't have data from curl.
   x make tr -> GET&SELECT WHERE id=-1, but pulls out something that is id=-1!
   x figured out the URL the form hits via Makefile curl
   x figured out where it hits it, isphp.php|isTest.php
   x hoping it gets results back from echo on the server php.
   x test.data.update had an id specified. But can you set an id for a specified row which already has an id? Yes.
   x fixed. make tu: isTest.php recieves a POST and the ill-formed record, so json_decode result is empty &c.
   x don't know how to get data back from the server code to the client
   x   Wondering how to get the results into the client: in the form in isjs.php is_$app_$op() or something.
   x    solution: clean output written via echo in the server php code.
   x how? after isTest.php we get Navigated to tomveatch.co
   x   first it's xhr.send(l) to isTest.php; then into lala land.
   x   sql undefined isTest.php line 87.
   x UI for Create should let you put in info to go into the fields.
   UI for Read/Search should let you pick from existing row ids, or check boxes on a subset of rows in a table
      or specify values or ranges of values or regexes to match values for different rows
      and to combine the choices with ANDs and ORs.  Hmm.
      So while creating the UI for a subset-selection, we already need to select * and make a table with the result
      and provide checkboxes or subset-selection criteria before even doing the Read/Search.
   x   Let's do Search later and just do Read for now, and let's assume Read requires a row id only.
   x   So the initial UI is a numeric field to put in your own row id,
   x   and the result is displayed in some default display format for a record based on is.php:Columns.
   x UI for Update should, as for Create, let you put in info to go into the fields.
   x   Update should first be select row by ID to edit, then a Read it by ID, then a
   x   display of everything editably, then an Update button then a display with a Saved dressing on the button,
   x    then if more editing occurs, rename the button Update/Save again and let it continue.
   x UI for Delete should either show all the existing rows to pick one or more to delete,
   x    or it should let you put in a number.
   x Return results for delete should include SUCCESS, FAILURE, and Row Didn't Exist In The First Place.
   x accessing isCorona.php with json data correct fails and somehow pulls index.php?bla=bla?encoded=as+url
   x  which then fails with headers not set properly, apparently.
   x  becausee isCorona.php is an empty file!
   x  // the onsubmit function should end with "return false;" to prevent additional submit handling.
   Open Bugs & Goals:
   Transition from Create to Update (Save[Inactive]) displaying the created record.
   Transition from Read to Update, displaying the read record.
   Record display occurs
   	  after Read
	  during Update process after read and during Save[active] and Save[inactive]
	  during Create
	  before Delete
   Widgetry should be independent of row data copying.
     Provide a hook in the widgetry to hang the 
     Let onload call a copy function with a widget selector parameter.
       then for each column put the received data into the selected widget by colomn
   */
  $debug=1;
  if (defined('STDIN')) { $NAME=$argv[1];      /* Run this script on the command line and give the app name as an argument. */ }
  else                  { $NAME=$_GET['NAME']; }
  include "$NAME/$NAME.php";

  echo "<?" . "php \n";
?>
  //
  // This PHP program was generated by php isphp.php <?php echo $is['Name'];?> or isphp.php?NAME=<?php echo $is['Name'];?> 
  // and provides an IS API to the DB <?php echo $is['DBName'];?> for table <?php echo $is['TableName']; ?> 
  //

  // The URL of this program provides a REST-ish API on HTTP requests with JSON data in the HTTP message body.
  // The HTTP method GET|PUT|POST|DELETE determines the REST function READ/SEARCH|UPDATE|CREATE|DELETE.
  // (previous draft had the table and operation in the URL as &is=$tablename&$op=S|C|R|U|D.
  
/* Second Draft: use php_crud_api approach: use HTTP METHOD to select
 * CRUD op, and direct mysql calls in here.
 */

/*
 * Phase 1: get ready.
 */
$debug    = 1;
$method   = (isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "NONE");
error_log ( "method=$method\n",3,"/tmp/isphp.err");
$h = getallheaders();
$s = "";
foreach ($h as $hdr => $hval) {
   $s .= "$hdr: $hval\n";
}
if ($debug) error_log ( "XXX headers: $s XXX\n",3,"/tmp/isphp.err");

if ($method=="OPTIONS") {
  http_response_code(200); // 200 OK, or 204 No Content.
  header("Accept: GET,PUT,POST,DELETE,OPTIONS");
  header("Access-Control-Allow-Origin: http://www.tomveatch.com");
  header("Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS");
  header("Access-Control-Allow-Headers: access-control-allow-methods, access-control-allow-origin, Content-Type, Accept, Origin,");
  exit(0);
}

$request  = (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO']!=""
          ? explode('/', trim($_SERVER['PATH_INFO'],'/')) 
          : "" );
	  
// echo "_REQUEST: "; var_dump($_REQUEST);
// echo "_GET: "    ; var_dump($_GET);
// echo "_POST: "   ; var_dump($_POST);

function var_error_log($prefix,$object=null,$suffix) {
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    error_log("$prefix $contents $suffix",3,"/tmp/isphp.err");        // log contents of the result of var_dump( $object )
}

$phpinput = file_get_contents('php://input',true);
error_log ( "php://input=" . ($phpinput==""?"empty":$phpinput) . "\n",3,"/tmp/isphp.err");
$input    = ($phpinput!="" ? json_decode($phpinput,1) : NULL);

if (!$input) {
  if ($phpinput=="") {
    error_log("No input available from php://input\n",3,"/tmp/isphp.err");
  } else {
    error_log("Input not decodeable as valid JSON: '$phpinput'\n",3,"/tmp/isphp.err");
  }
} else {
  if ($method == 'POST' && isset($input['id'])) {
    $es = "Error: POST (create) request has 'id' $input[id]. A new DB entry has no id beforehand.\n";
    $res= "{'rows_affected':'0','result':'FAILED','msg':'$es'}\n";
    error_log($res,3,"/tmp/isphp.err");
    echo "$res";
    die($res);
  }
}

// To do a single-row select, delete, or update, ensure the input json contains a row id element.
if ($method=="DELETE" || $method=="GET" || $method=="PUT") { // these need to know the row to work on.
  // var_error_log("json_decode result is: ",$input, "\n",3,"/tmp/isphp.err");
  if     ($input != NULL)
    $rowid  = $input['id'];
  elseif (is_array($_GET) && isset($_GET['id']))
    $rowid  = $_GET['id'];  // Somehow via phpinput no request data comes in message body, but it works in the URL
  else 
    $rowid  = -1;

  error_log("rowid: $rowid\n",3,"/tmp/isphp.err");
}

// connect to the mysql database
$link     = mysqli_connect('localhost', 
     		           '<?php echo $is['User'];?>', 
      	                   '<?php echo $is['Pwd'];?>', 
      		           '<?php echo $is['DBName'];?>');
mysqli_set_charset($link,'utf8');
 
// retrieve the table and key from the path
$table    = "<?php echo $is['TableName']; ?>"; // was preg_replace('/[^a-z0-9_]+/i','',array_shift($request));

if ($method=="POST") {
  if (isset($_POST)) { // only for POST method
    error_log("_POST: [" . join(":",array_keys($_POST)) . "]=>[" . join(":",array_values($_POST)) . "]\n",  3,"/tmp/isphp.err");
  } else {
    error_log("_POST not set.\n",3,"/tmp/isphp.err");
  }
}

// escape the columns and values from the input object
$columns = ((isset($input) && $input != NULL)
	 ? preg_replace('/[^a-z0-9_]+/i','',array_keys($input))
         : [] );
$values  = ((isset($input) && $input != "")
	 ? array_map(function ($value) use ($link) {
	     if ($value===null) return null;
	     return mysqli_real_escape_string($link,(string)$value);
	   },array_values($input))
	 : [] );
/* was: 
   $columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
   $values = array_map(function ($value) use ($link) {
     if ($value===null) return null;
     return mysqli_real_escape_string($link,(string)$value);
   },array_values($input));
*/ 

// build the SET part of the SQL command for a POST/UPDATE 
$set = '';
$j=0;
for ($i=0;$i<count($columns);$i++) {
  if ($columns[$i] != 'id') { // Exclude the id column
    $set.=($j>0?',':'').'`'.$columns[$i].'`=';
    $set.=($values[$i]===null?'NULL':'"'.$values[$i].'"');
    $j++;
  }
}
 
// create SQL based on HTTP method
switch ($method) {
  case 'NONE':   // Get last inserted id. 
    $method="GET";
    $rowid=mysqli_insert_id($link); // if none found, mysqli_insert_id returns 0 which drops through and selects all rows.
    error_log("method='NONE': changing to a GET for row id $rowid\n",3,"/tmp/isphp.err");
  case 'GET':    // Read or Search
    $sql = "SELECT * FROM `$table`".($rowid?" WHERE id=$rowid":($search?" WHERE $search":'')).";";
    break;
  case 'PUT':    // Update (PUT=store something at a given place (since id must therefore be known, this is an UPDATE)
       		 // respond with 200 OK.  Parse Content-* headers or reply with 501 Not Implemented.
    $sql = "UPDATE `$table` SET $set WHERE id=$rowid;";
    break;
  case 'POST':   // Create (POST=put something subordinate to, into the DB, thus a new id, this is a CREATE)
       		 // reply with 201 Created + entity with id + Location: header.
    $sql = "INSERT INTO `$table` (`" . implode("`,`",$columns) ."`) VALUES ('" . implode("','",$values) . "');";
    break;
  case 'DELETE': // Delete
  default:
    $sql = "DELETE FROM `$table` WHERE id=$rowid;";
    break;
}

/*
 * Phase 2: Do The Query
 */
// execute SQL statement
$result = mysqli_query($link,$sql); // if SELECT then $result is a mysqli_result object, else TRUE or FALSE only.

/*
 * Phase 3: Process the result and return or die as appropriate.
 */

if (!$result) { // false could be failed delete, failed insert, or failed read.
  $es = " mysqli_query(sql)=FALSE: " . mysqli_error($link) . ", w/sql: $sql\n";
  // console.log($es); this code is run on the server, output is not seen on client browser.
  error_log($es,3,"/tmp/isphp.err");

  // die if SQL statement failed
  http_response_code(404);
  error_log("No result, die with mysqli_error = " . mysqli_error($link),3,"/tmp/isphp.err");
  die(mysqli_error($link)); // mysqli_close($link): can't call after die cuz we're dead.  Can't call before die() because mysqli_error won't work.
}

$rs = print_r($result,TRUE);
error_log("mysqli_query() returned \"{$rs}\" on sql: $sql\n",3,"/tmp/isphp.err");

// print some results like the id of the inserted/deleted row, the updated row id, the row itself
if ($method == 'GET') {  // aka SELECT aka READ or SEARCH
  $res = "";
  if (!$rowid) $res .= '['; // if no rowid is set, this may be for multiple rows, so start an array of JSON objects
  $N = mysqli_num_rows($result);
  for ($i=0;$i<$N;$i++) {
    $res .= ($i>0?',':'').json_encode(mysqli_fetch_object($result));
  }
  if (!$rowid) $res .= ']';
  error_log("GET result: $res\n",3,"/tmp/isphp.err");
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=utf-8");
  http_response_code(200); // OK
}

elseif ($method=="POST") { // aka INSERT aka CREATE
  $res = "{";
  // handle reported stats for a POST/INSERT
  // for multi-line POST/INSERTs we can find out a bit about how it went with mysqli_info(). 
  $str = mysqli_info($link);
  // Unless multiple inserts at a time, $str will report No results. 
  error_log("Called mysqli_info() recieved: $str.\n",3,"/tmp/isphp.err");
  preg_match_all ('/(\S[^:]+): (\d+)/', $str, $matches);
  $info = array_combine($matches[1], $matches[2]);
  if ($info==NULL) {
    if ($debug) error_log("No results from mysqli_info() (expected given this is a one-row UPDATE)\n",3,"/tmp/isphp.err");
    error_log("Expecting 1 and got: " .     mysqli_affected_rows($link) . " row(s) affected\n",3,"/tmp/isphp.err");
  } else {
    if ($debug) var_error_log("info is not null: ",$info,"\n");
    if (isset($info['Records'])) {
        error_log("HTTP POST, mysql INSERT modified " . $info['Records'] . " rows.\n",3,"/tmp/isphp.err");
	$res .= "\"Records\":\"" . $info['Records'] . "\",";
    }
    if (isset($info ['Duplicates']) && $info['Duplicates'] != 0) {
        error_log("HTTP POST, mysql INSERT found " . $info['Duplicates'] . " duplicate rows.\n",3,"/tmp/isphp.err");
	$res .= "\"Duplicates\":\"" . $info['Duplicates'] . "\",";
    }
    if (isset($info['Warnings']) && $info ['Warnings'] != 0)
        error_log("HTTP POST, mysql INSERT generated " . $info ['Warnings'] . " warnings.\n",3,"/tmp/isphp.err");
	$res .= "\"Warnings\":\"" . $info['Warnings'] . "\",";
  }

  // reply with 201 Created + the entity id + maybe a Location: header if RESTish.
  http_response_code(201);
  $mar = mysqli_affected_rows($link);
  error_log("Expecting 1 and got: $mar row(s) affected\n",3,"/tmp/isphp.err");
  $miid = mysqli_insert_id($link);
  // id is the auto-incremented, new, row id for the inserted row.
  // rows_affected should be 1 for 1 new row.
  // result should be TRUE for success, otherwise we died before we got here. Just being helpful.
  error_log("id of " . ($method=="POST"?"inser":"upda") . "ted row is: $miid\n",3,"/tmp/isphp.err");
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Methods: *");
  header("Content-Type: application/json; charset=utf-8");
  $res .= "\"id\":\"$miid\",\"rows_affected\":\"$mar\",\"result\":\"$result\"}\n"; // get ready to report home
}

elseif ($method == 'PUT') { // aka UPDATE
  $res = "{";
  // handle reported stats for a PUT/UPDATE
  // for multi-line PUT/UPDATEs we can find out a bit about how it went with mysqli_info().
  $str = mysqli_info($link);
  /* Unless multiple updates at a time, $str will report No results. */ 
  error_log("Called mysqli_info() recieved: $str.\n",3,"/tmp/isphp.err");
  preg_match_all ('/(\S[^:]+): (\d+)/', $str, $matches);
  $info = array_combine($matches[1], $matches[2]);
  if ($info==NULL) {
    error_log("No results from mysqli_info() (expected given this is a one-row UPDATE)\n",3,"/tmp/isphp.err");
    error_log("Expecting 1 and got: " .     mysqli_affected_rows($link) . " row(s) affected\n",3,"/tmp/isphp.err");
  } else {
    if ($debug) var_error_log("info is not null: ",$info,"\n");

    if (isset($info['Rows matched'])) {
        error_log("HTTP PUT, mysql UPDATE matched " . $info['Rows matched'] . " rows.\n",3,"/tmp/isphp.err");
	$res .= "'Rows matched':" . $info['Rows matched'] . ",";
    }
    if (isset($info['Changed'])) {
        error_log("HTTP PUT, mysql UPDATE changed " . $info['Changed'] . " rows.\n",3,"/tmp/isphp.err");
	$res .= "'Changed':" . $info['Changed'] . ",";
    }
    if (isset($info['Warnings'])) {
        error_log("HTTP PUT, mysql UPDATE generated " . $info ['Warnings'] . " warnings.\n",3,"/tmp/isphp.err");
	$res .= "'Warnings':" . $info['Warnings'] . ",";
    }
  }
  $res .= "}";
  // respond with 200 OK. XXX Parse Content-* headers or reply with 501 Not Implemented.  
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=utf-8");
  http_response_code(200);
}

elseif ($method == 'DELETE') { 
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=utf-8");
  http_response_code(200);
  $res= "{\"id\":\"$rowid\",\"result\":\"$result\"}\n"; // get ready to report home
       // result should always be TRUE since we checked $res and died earlier on failure.
       // probably if the row was already deleted then it will seem to be TRUE
       //    even though it wasn't quite SUCCESS in deleting a row.
}

else {
  error_log("Unrecognized method: $method. Not going to respond\n",3,"/tmp/isphp.err");
  $res = "";
}

error_log("$method response is: $res",3,"/tmp/isphp.err");
echo "$res"; // report to client (actual output here)

// close mysql connection
mysqli_close($link);

<?php
  echo "?" . ">";
 ?>