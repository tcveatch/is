<?php
//
// isjs.php
// 

/* This program uses an IS data structure (in $NAME.php) to
   autogenerate a JS-includeable library/function file that provides
   an authenticated client-side API for IS CRUD operations. 

   To use it:
   % php isjs.php $NAME > is$NAME.js
   or from a browser:
   http:path.to.here/isjs.php$NAME=yourtablename > is$NAME.js
 */

$debug=0;
if (defined('STDIN')) { $NAME=$argv[1];      }
else                  { $NAME=$_GET['NAME']; }
include "$NAME/$NAME.php";

echo "/* start JS library file based on $NAME.php */\n";
?>
  //
  // This file is autogenerated by php isjs.php <?php echo $NAME;?> or
  // isjs.php?NAME=<?php echo $NAME;?> and when included by client-side
  // JS code provides an IS API through HTTP to the server's REST-ish API
  // (in PHP) and ultimately the server's DB for table <?php echo $NAME;?>
  //

<?php
  echo "//JS<BR>functions S,C,R,U,D ($is[TableName],col1, col2..) { .. } ;\n\n";
  echo "debug=$debug;";
 ?>

//
// search:
//
// provide criteria to server REST PHP API, retrieve array of records
//
function is_<?php echo $is['TableName']; ?>_search() {
  alert("called is_<?php echo $is['TableName']; ?>_search()");
}

//
// create:
//
// create a record with default values
//
function is_<?php echo $is['TableName'];?>_create() {
  // instead of replacing the current page with a referenced URL
  // we will collect the elements from the form, submit them to the
  // server in a POST message within a JSON structure, then get back
  // whatever it likes to send us, and display that in place of the form.
  const e = new FormData(document.getElementById("createForm"));
  let k = {};
  for (const [key,value] of e.entries()) {
    if (key!='id') // ignore id since a new row gets its own id. 
      k[key]=value;
  }
  pkg = JSON.stringify(k);
  // From here, call the server API with the JSON string.
  url = "<?php echo "$is[URL]/$is[Path]/$is[Name]/is$is[TableName].php"; ?>";
  const xhr = new XMLHttpRequest();
  xhr.onload = function () { alert(`loaded: ${xhr.status} ${xhr.response}`); };
  xhr.open("POST",url,true); // POST adds a new row to the table
  msg = "in is_<?php echo $is['TableName']; ?>_create(), form data|JSON=" + pkg + ", url=" + url;
  console.log(msg);
  xhr.setRequestHeader("Content-type","application/json; charset=utf-8");
  // xhr.setRequestHeader("Access-Control-Allow-Origin",url);
  // xhr.setRequestHeader("Access-Control-Allow-Methods","PUT");
  xhr.send(pkg);
  alert("xhr.send(" + pkg + ") sent.");
}

//
// read:
//
// pick one using its ID, retrieve one record
//

// Globally, make an associative array with key=val = $colName $InputType for the is.php $Columns.
// then InputTypeLookup[fea] has the InputType string needed to build the inputs resulting from a .._read()
var InputTypeLookup = new Object;
<?php // assign inputtypes as values for features with colName for each Column.
    foreach( $is['Columns'] as list( $id, $colName, $type, $N, $def, $InputType, $SQL_Type )) {
      echo "  InputTypeLookup.$colName = \"$InputType\";\n";
    }
 ?>

function gotPut(responseText,parentName,elementSet) {
    got = JSON.parse(responseText);
    alert("gotPut(" + responseText + "," + parentName + "," + elementSet + ")");
    // now for each element in got => fea val make an input with name=fea, id=fea, type = $InputType value=val
    parent = document.getElementById(parentName);
/*
    for (let key in got) {
      let inp = document.createElement("input");
      inp.setAttribute("id",key);
      inp.setAttribute("name",key);
      inp.setAttribute("type",InputTypeLookup[key]);
      parent.appendChild(inp);
    }
 */
    for (let key in got) {
      document.getElementById("is" + key + "Input").setAttribute("value",got[key]);
    }
   
}

function is_<?php echo $is['TableName'];?>_read(
	      the_id = -1,
	      elementSet = "readElementSet",
	      divName = "isReadDiv"
	    ) {
  div = document.getElementById(divName);	    
  if (debug) alert("20 in is_<?php echo $is['TableName']; ?>_read(the_id=" + the_id + ", onload=" + onload +")");
  if (the_id == -1) {	    
    const e = new FormData(document.getElementById("readForm"));
    entries = "..."
    for (const [key,value] of e.entries()) {
      entries += key + ":" + value + ",";
      if (key=='id')
        requestID = value;
    }
    if (debug) alert("1 in is_<?php echo $is['TableName']; ?>_read() with e.entries" + entries + ", requested ID=" + requestID);
  } else { // if the default been overridden, then use the overriding value.
    requestID = the_id;
    alert("2 in is_<?php echo $is['TableName']; ?>_read(id=" + requestID + ")");
  }

  // GET the id row and alert its contents.
  // From here, call the server API with the JSON string.
  url = "<?php echo "$is[URL]/$is[Path]/$is[Name]/is$is[TableName].php?id="; ?>"+ requestID;
  const xhr = new XMLHttpRequest();
  xhr.overrideMimeType("application/json");
  xhr.onload = function () {
	          alert("onload, load widgets into " + elementSet);
		  gotPut(xhr.responseText,divName,elementSet);
  };
  xhr.open("GET",url,true); // 
  msg = "in is_<?php echo $is['TableName']; ?>_read() with id="+requestID;
  console.log(msg);
  xhr.setRequestHeader("Content-type","application/json; charset=utf-8"); // on send or load?
  // xhr.setRequestHeader("Access-Control-Allow-Origin",url);
  // xhr.setRequestHeader("Access-Control-Allow-Methods","PUT");
  xhr.send("");
  alert("xhr.send([Empty body]) sent to " + url);
}

//
// update
//
// send a record with modified values (keep track of what changed)
//
//   addUpdate() initializes state
// .._updateUI() handles the updateState state machine, and
//   .._update() makes the actual update call: collect/send data, receive/display data

function is_<?php echo $is['TableName'];?>_update(jsonUpdateStr) {
  // is..update() collects the elements from the form, POSTs them as JSON to the
  // server, then gets back whatever it likes to send us, and displays that in the form

  alert("3 in is_<?php echo $is['TableName']; ?>_update(), state="+uState);

  const e = new FormData(document.getElementById("isUpdateForm"));
  let k = {};
  for (const [key,value] of e.entries()) { k[key] = value } // uses name attribute which is the bare $colName
  pkg = JSON.stringify(k);
  // From here, call the server API with the JSON string.
  url = "<?php echo "$is[URL]/$is[Path]/$is[Name]/is$is[TableName].php"; ?>";
  const xhr = new XMLHttpRequest();
  xhr.onload = function () { alert(`loaded: ${xhr.status} ${xhr.response}`); };
  xhr.open("PUT",url,true); // POST adds a new row to the table
  msg = "in is_<?php echo $is['TableName']; ?>_update(), form data|JSON=" + pkg + ", url=" + url;
  console.log(msg);
  xhr.setRequestHeader("Content-type","application/json; charset=utf-8");
  // xhr.setRequestHeader("Access-Control-Allow-Origin",url);
  // xhr.setRequestHeader("Access-Control-Allow-Methods","PUT");
  xhr.send(pkg);
  alert("xhr.send(" + pkg + ") sent.");
}

// Update related globals.
const updateState = { // Meaning, if in this state:
  UNITIALIZED: 0,     //   loading code during startup.
  INIT: 1,            //   set by addUpdate() during first page load
  START: 2,           //   set by .._updateUI() after page is loaded
  CHOOSEROW: 3,
  SAVEDROW: 4,
  UNSAVEDROW: 5
}
let uState = updateState.UNITIALIZED; // global
let uTop = "IS Update Widget <FORM id='isUpdateForm' name='isUpdateForm' onsubmit=\"is_<?php echo "$is[TableName]";?>_updateUI()\">";
let uBot = "</FORM></P>";

function activeUpdateSubmit(yorn) {
  let s = document.getElementById("isUpdateSubmit");
  s.disabled = !yorn;
  s.value = (yorn?"SAVE[active]":"SAVE[inactive]")
}

function is_<?php echo $is['TableName'];?>_updateUI() {
  div = document.getElementById("isUpdateDiv");
  if        (    uState == updateState.UNITIALIZED) { // on page load (should never occur)
    alert("Wierd: in is_<?php echo $is['TableName']; ?>_updateUI(), with state=UNINITIALIZED");
    exit();
  }

  if        (    uState == updateState.INIT) {
    	    	 // alert("4 in is_<?php echo $is['TableName']; ?>_updateUI(), state=INIT");
		 if (debug) {
     		   var x    = document.createElement("INPUT");
     		       x.setAttribute("type","submit");
     		       x.setAttribute("value","Modify Something");
     		   var ut = document.createTextNode("Debugging: To pick a row to modify click Modify Something");
     		   var form = document.createElement("FORM"); 
     		       form.onsubmit = function() {is_<?php echo "$is[TableName]";?>_updateUI();};
     		       form.name = "isUpdateForm";
     		       form.id   = "isUpdateForm";
     		       form.appendChild(ut);
     		       form.appendChild(x);
     		   div.appendChild(form);
                 } else {
     		   div.innerHTML = uTop
	 	    +  "To pick a row to modify click Modify Something"
		    +  "<input type=\"submit\" value=\"Modify Something\">"
		    +  uBot;
	         }
    // We do get a Console warning, Form submission canceled because form is not
    // connected.  Ignore this. It's connected enough to call the onsubmit
    // function, which is all we need.  Maybe it lacks an action target
    // URL, that's fine that's what we want.  To pursue, look up shadow trees.
	 	 uState  = updateState.START;   // And be ready to handle it.
  } else if (    uState == updateState.START) { // user clicked Modify Something
    	    	 // alert("5 in is_<?php echo $is['TableName']; ?>_updateUI(), state=START");  
  	    	 // the only thing to do here is change state to CHOOSEROW and change the UI to fit.
		 // show ID widget and label button as Access Item To Edit.
		 // START       an "Edit By Row ID" button, with action is..update(CHOOSEROW)
     		 div.innerHTML = uTop
		  +  "<B>Enter a row ID from DB <?php echo $is['DBName'];?>, Table <?php echo $is['TableName']; ?>:</B><BR>"
		  +  "<BR>ID: <input type=number id=updateID name=updateID>"
		  +  "<input type=\"submit\" value=\"Access item to edit\">"
		  +  uBot;
	 	 uState  = updateState.CHOOSEROW;
  } else if (    uState == updateState.CHOOSEROW) {  // row has been chosen: proceed
    	    	 alert("6 in is_<?php echo $is['TableName']; ?>_updateUI(), state=CHOOSEROW");
		 chosenID = document.getElementById("updateID").value;
    	    	 // alert("7 in is_<?php echo $is['TableName']; ?>_updateUI(), state=CHOOSEROW, chosenID=" + chosenID);
		 div.innerHTML = uTop
		  +  "<B>Modify row " + chosenID + " from table <?php echo $is['TableName']; ?>:</B><BR>"
		  +  "<input type=hidden name=\"" + chosenID + "\">\n"
		  +  "<div id=updateElementSet>updateElementSet div here" // might be optional
		  // build structure: a set of inputs for each column.
		  // XXX future: move each input into the HTML where targets for each reside.
		  // then you can have any surrounding context, picture, table structure, explanatory
		  // text, graphics etc. that you like but the inputs would then go into the right place.
		  // meanwhile ugly: unlabelled widgets in a row within the form inside updateElementSet.
		  <?php foreach ( $is['Columns'] as list( $id, $colName, $type, $N, $def, $InputType, $SQL_Type )) {
		          if ($id != 'id') {
			    echo " + '<INPUT name=\"{$colName}\" id=\"is{$colName}Input\" "
			         . "type=\"$InputType\" value=\"{$def}\" oninput=\"activeUpdateSubmit(true)\">'\n\t";
		          }
    	                } 
	           ?>
		  +  "<input id=\"isUpdateSubmit\" type=\"submit\" value=\"SAVE[inactive]\">"
		  +  "</div>\n"; // end of updateElementset div
		  +  uBot;
		 // x remove the ID widget,
		 // x add widgets to show all the row items, 
		 // x label button as grayed out SAVE
    	    	 // x do a GET with the chosen id
    	    	 is_<?php echo $is['TableName']; ?>_read(the_id = chosenID,
	 	       	elementSet = "updateElementSet",divName="isUpdateForm");
	         uState  = updateState.SAVEDROW; 
  } else if (    uState == updateState.SAVEDROW) { // got set to SAVED after .._read() or _update()
    	    	 alert("8 in is_<?php echo $is['TableName']; ?>_updateUI(), state=SAVEDROW");
		 // SAVEDROW continue to show the row editably with grayed Save button
		 alert("notice any change to contents, whereupon:...");
	 	 // make the SAVE button active
		 activeUpdateSubmit(true);
		 uState = updateState.UNSAVEDROW; // be ready to save it.

// XXX there's some kind of a bug here where it calls an ACTION with the whole ?a=b&... suffix.
// XXX and ends up back at ground zero state START.
// XXX it would be nice to know the METHOD used for each of these calls in their alert()'s.

  } else if (    uState == updateState.UNSAVEDROW) { // got set to UNSAVED elsewhere upon edit.
    	    	 alert("9 in is_<?php echo $is['TableName']; ?>_updateUI(), state=UNSAVEDROW");  
    	    	 // UNSAVEDROW  show row w/changes, live SAVE button, with
		 // is_update() { actually update the row to the server, with onload() { s=SAVEDROW; go; } }
    	    	 // Okay?  So: do a PUT/UPDATE with the id and modified row contents
		 is_<?php echo $is['TableName']; ?>_update( // call the actual update function.
		   onload=function() { XXX this SHOULD be like the read() onload to update the form but...???
   		     alert("compare with the modified row contents and alert if error then return");
		     activeUpdateSubmit(false);
		     uState  = updateState.SAVEDROW;
		   }
		 );

  } else    {
    alert("10 in _updateUI, updateState has an unknown value: " + uState);
  }
}

//
// delete:
//
// just delete one, using its ID.
//
function is_<?php echo $is['TableName'];?>_delete() {
  const e = new FormData(document.getElementById("deleteForm"));
  entries = "..."; pkg = ""; requestID = -1;
  for (const [key,value] of e.entries()) {
    entries += key + ":" + value + ",";
    if (key=='id') {
      requestID = value;
      pkg = "{\"id\":\"" + requestID + "\"}";
    }
  }
  alert("called is_<?php echo $is['TableName']; ?>_delete() with FormData.entries=" + entries + ", requesting ID=" + requestID);

  // From here, call the server API with the JSON string.
  url = "<?php echo "$is[URL]/$is[Path]/$is[Name]/is$is[TableName].php"; ?>";
  const xhr = new XMLHttpRequest();
  xhr.onload = function () { alert(`loaded: ${xhr.status} ${xhr.response}`); };
  xhr.open("DELETE",url,true); // POST adds to table; PUT replaces known id in table (=update)
  msg = "in is_<?php echo $is['TableName']; ?>_delete(), id=" + requestID + ", url=" + url;
  console.log(msg);
  xhr.setRequestHeader("Content-type","application/json; charset=utf-8");
  xhr.send(pkg);
  alert("xhr.send(" + pkg + ") sent.");
}

<?php
//
// Below used to be in isui.php, but got moved into isjs.php
//
  // Here we define portable UI code units, e.g., divs, for: 
  // create; search|select vars col*; form edit; 
  // button delete; calling is$NAME.js functions and providing the needed parameters as in
  // is$NAME.scrud($NAME,search,col1, col2..);

  /* This program uses an IS data structure (in $NAME.php) to
     autogenerate (via PHP self-rewriting code) some JS functions that
     attach code to standard UI DIVs in the caller's HTML .
     so as to interface with the IS API and do SCRUD operations.

     if (defined('STDIN')) { $NAME=$argv[1];      }
     else                  { $NAME=$_GET['NAME']; }
     include "$NAME/$NAME.php";
   */

  /* To use it:
     % php isui.php $NAME > ../$NAME/is$NAME.ui.js

     then include it by reference in your JS code in your client
     program, and call its functions to attach this standard code to
     your own divs of the standard names.

   */

  /* implement here basic UI divs to let user:
     initiate: New, Search & Read, Edit, Delete, and
     and propagate edits as changes up through the JS API.

     Call them in the JS elsewhere, somehow to do the right thing.
   */
 ?>

function addSearch() {
  div = document.getElementById("isSearchDiv");
  div.innerHTML 
    += " UI before operation is initiated: criteria-specifying widgetry. "
    +  "Values are gleaned here when activated by software due to who knows "
    +  "what, so it knows how to search.  Action search(criteria) brings an "
    +  "array of matching records from the server to here show them "
    +  "displayed UI after operation completes: put them in a datatable, "
    +  "also showing the count of matches, or show error if ID doesn't "
    +  "exist or something failed in transit. "
    +  "<FORM id='searchForm' name='searchForm' onsubmit=\"is_<?php echo "$is[TableName]";?>_search()\">"
    +  "<input type=\"submit\" value=\"Search\">"
    +  "</FORM>";
  // div.onclick=is_<?php echo $is['TableName']; ?>_search();
}

function addCreate() {
  divpp = document.getElementById("isCreateDiv");
  div.innerHTML 
    += " UI before operation is initiated: A form with \"Create New\" submit element and "
    +  "onsubmit=is_<?php echo $is['TableName']; ?>_create(this). So click Create New and get a new record created on " 
    +  "the server and here show the new record, displayed UI after " 
    +  "operation completes: nothing but a result variable\'s value " 
    +  "indicates success or not."
    +  "<FORM id='createForm' name='createForm' onsubmit=\"is_<?php echo "$is[TableName]";?>_create()\">"
    +  "<B><?php echo $is['TableName']; ?>:</B><BR>"
    +  "<?php 
	 foreach( $is['Columns'] as list( $id, $colName, $type, $N, $def, $InputType, $SQL_Type )) {
	   if ($id != 'id') {
             echo "<BR>{$colName}: <input type=$InputType name={$colName} value={$def}>";
           }
         } 
       ?>"
    +  "<input type=\"submit\" value=\"CreateNew\">"
    +  "</FORM></P>";
  // div.onclick=is_<?php echo $is['TableName']; ?>_create();
  // A form has action, onsubmit, and inputs with type, value, and onclick.
  //    action is supposed to be a URL to go to with the form wrapped up in method=(GET|POST)
  // 	onsubmit can return true or false to prevent action if data is invalidated.
  // 	onclick can help fill in the values in the input widget
  // 	type and value are the field data type and value.
  // So in the IS model this is all BS because we mostly don't want
  //    a page reload or a different URL to go to, instead we want the CRUD op to
  //    operate and then redisplay just the CRUD part of the page with the result
  //    C: with the new record and its values ready to be edited or deleted
  //    R: with the chosen record and " " " 
  //    U: with the updated record and " " "
  //    D: with some indicator that we are done with that one now, and you
  //       can create another one or pick another one or search for a set of others
  //       or alternatively it can display the remainder of the displayed
  //       list of them after the deleted one is removed.
  // So should those be page reloads?  No, it should be a JS call within the page webapp,
  //    made to the PHP server code to do the CRUD op and get the natural result,
  //    and let the page writer decide what they want next to get or display or navigate to.
  //    
}

function addRead() {
  div = document.getElementById("isReadDiv");
  div.innerHTML
    += " UI before operation is initiated: nothing.  It\'s activated by "
    +  "software due to who knows what, but it must know the record ID. "
    +  "Action read(id) brings that record from the server to here show the "
    +  "record displayed UI after operation completes: show the record "
    +  "displayed or error if ID doesn\'t exist or something failed in "
    +  "transit."

    +  "<FORM id='readForm' name='readForm' onsubmit=\"is_<?php echo "$is[TableName]";?>_read()\">"
    +  "<B>Enter a row ID from table <?php echo $is['TableName']; ?>:</B><BR>"
    +  "<?php echo "<BR>ID: <input type=number name=id>"; ?>"
    +  "<input type=\"submit\" value=\"GET/Read/Display Specified Row\">"
    +  "</FORM></P>";
  // div.onclick=is_<?php echo $is['TableName']; ?>_read();
}

function addUpdate() { // Init updateState state machine and call .._updateUI()
  if (uState==updateState.UNITIALIZED) {
    uState = updateState.INIT; // global.
    is_<?php echo $is['TableName']; ?>_updateUI();
  }
}

function addDelete() {
  div = document.getElementById("isDeleteDiv");
  div.innerHTML
    += " UI before operation is initiated: A button associated with that ID "
    +  "saying \"Delete\" with onclick=isdelete() Action click Delete and get "
    +  "that record deleted on the server and here show result.  UI after "
    +  "operation completes: nothing but a result variable\'s value "
    +  "indicates success or not. "
    +  "<FORM id='deleteForm' name='deleteForm' onsubmit=\"is_<?php echo $is['TableName'];?>_delete()\" >"
    +  "<input type=\"id\" name='id' value=\"50\">"
    +  "<input type=\"submit\" value=\"Delete\">"
    +  "</FORM>";
  // div.onclick=is_<?php echo $is['TableName']; ?>_delete();
}
