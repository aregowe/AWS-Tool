<?php
include("config.php");
include("functions.php");

//CHECK AND VALIDATE LOGIN
if($_REQUEST['toDoAction'] == 'login') {
	
	if(getValue("SELECT COUNT(id) FROM admins WHERE username = ".quote_smart($_REQUEST['username'])." AND `password` = ".quote_smart($_REQUEST['password'])."") > 0) {
		$_SESSION['login'] = $_REQUEST['username'];
	} else {
		$invalidLogin = TRUE;
	}
}
if($_REQUEST['action'] == 'logout') {
	$_SESSION['login'] = '';
	$loggedOut = TRUE;
}
$allowedExtensions = array("csv");

if($_SESSION['login'] != '') {
	
	if($_REQUEST['action']=='submitNow') {
	$_SESSION['errorLog'] = '';
	$thisSessionID = rand(1,getrandmax());
	
	for($i = 0;$i < count($_REQUEST['itemType']);$i++) {
		if($_FILES['item']['error'][$i] == UPLOAD_ERR_OK) {
		  if(isAllowedExtension($_FILES['item']['name'][$i])) {
			# Do uploading here
			$filename = file_get_contents($_FILES['item']['tmp_name'][$i]);
			$filename = split("\n", $filename);
			if($_REQUEST['itemType'][$i] == 'SKU') {
				$filenameCount = count($filename);
				for($k = 0;$k < $filenameCount;$k++) $filename[$k] = split(",",$filename[$k]);
			}
			foreach($filename as $k => $v) {
				$data = $v;
				if($_REQUEST['itemType'][$i] == 'ASIN') {
					addToQueueV2($data,'ASIN',$thisSessionID);
				} else if($_REQUEST['itemType'][$i] == 'UPC') {
					addToQueueV2($data,'UPC',$thisSessionID);
				}
			}
				$message = 'A new job has just been added to queue.';
				$message .= "\n\nThe url to access this is : \n\n";
				$admin = getArray("SELECT * FROM admins WHERE username = ".quote_smart($_SESSION['login'])."");
				
				$message .= "http://thedatamaxx.com/showResults.php?sessionID=".urlencode($thisSessionID)."&username=".$admin['username']."&password=".$admin['password'];
				$to = $admin['emailAddress'];
				
				$subject = 'Amazon Queue Run Started for '.$thisSessionID.'';
				mail($to, $subject, $message);
				//mail('janderson@ecatalogservices.com', $subject, $message);
			
		  } else {
		  	
		  }
		} else die("<h1>Cannot upload</h1>");
	}
	//header("Location: /showResults.php?sessionID=".$thisSessionID);
	}//END CHECK
}//END SESSION LOGIN CHECK
?><html>
<head>
<title>Amazon API</title>
<style type="text/css">
<!-- 
body { font-family: arial,helvetica,sans-serif; font-size: 11px; }
p { font-family: arial,helvetica,sans-serif; font-size: 11px; }
table tr td { font-family: arial,helvetica,sans-serif; font-size: 11px; }
td { font-family: arial,helvetica,sans-serif; font-size: 11px; }
span { font-family: arial,helvetica,sans-serif; font-size: 11px; }
div { font-family: arial,helvetica,sans-serif; font-size: 11px; }
a { font-family: arial,helvetica,sans-serif; font-size: 11px; }
-->
</head>
</style>
<?php
if($_SESSION['login'] != '') {
?>
<script type="text/javascript" language="javascript" src="jquery-1.4.2.min.js"></script>
<SCRIPT LANGUAGE="javascript" type="text/javascript">
<!--

function checkOperation(){
   var operation = '';
   operation = $('#operation').val();
   if(operation == 'SellerListingLookup') {
   	$('#itemIdTypeSellerListing').show();
   	$('#itemIdTypeLookupItem').hide();
   	$('#itemIdTypeSimilarityLookup').hide();
	$('#similarityNote').hide();
	$('#divItemLookupResponseGroups').hide();
	$('#divSellerListingLookupResponseGroups').show();
	$('#divSimilarityLookupResponseGroups').hide();
	$('#similarityTypeDiv').hide();
   } else if(operation == 'ItemLookup') {
   	$('#itemIdTypeSellerListing').hide();
   	$('#itemIdTypeLookupItem').show();
   	$('#itemIdTypeSimilarityLookup').hide();
	$('#similarityNote').hide();
	$('#divItemLookupResponseGroups').show();
	$('#divSellerListingLookupResponseGroups').hide();
	$('#divSimilarityLookupResponseGroups').hide();
	$('#similarityTypeDiv').hide();
   } else if(operation == 'SimilarityLookup') {
   	$('#itemIdTypeSellerListing').hide();
   	$('#itemIdTypeLookupItem').hide();
   	$('#itemIdTypeSimilarityLookup').show();
	$('#similarityNote').show();
	$('#divItemLookupResponseGroups').hide();
	$('#divSellerListingLookupResponseGroups').hide();
	$('#divSimilarityLookupResponseGroups').show();
	$('#similarityTypeDiv').show();
   } else {
   	$('#itemIdTypeSellerListing').hide();
   	$('#itemIdTypeLookupItem').hide();
   	$('#itemIdTypeSimilarityLookup').hide();
	$('#similarityNote').hide();
	$('#divItemLookupResponseGroups').hide();
	$('#divSellerListingLookupResponseGroups').hide();
	$('#divSimilarityLookupResponseGroups').hide();
	$('#similarityTypeDiv').hide();
   }
	checkIdType();
}
function checkIdType(){
   var operation = '';
   operation = $('#operation').val();
   if(operation == 'SellerListingLookup') {	
    var itemIdType = '';
    itemIdType = $('#SellerListingItemIdType').val();
	if(itemIdType == 'ASIN') {
		$('#searchIndexDiv').hide();
	} else {
		$('#searchIndexDiv').show();
	}
   } else if(operation == 'ItemLookup') {
   
    var itemIdType = '';
    itemIdType = $('#ItemLookupItemIdType').val();
	if(itemIdType == 'ASIN') {
		$('#searchIndexDiv').hide();
	} else {
		$('#searchIndexDiv').show();
	}
   } else {
   }
}

$(document).ready(function() {
	checkOperation();
	checkIdType();
});
//-->
</SCRIPT><?php
} //END IF $_SESSION['login'] != ''
?>
</head>

<body>

<?php

include("header.php");

if($_SESSION['login'] != '') {
?>
<script type="text/javascript" language="javascript">
<!--
	function addItemToList(){
		//var htmlStr = $('#itemContainer').html();
		
		$('#itemContainer').append('<p><select name="itemType[]"  id="itemType"><option value="ASIN">ASIN (asin)</option></select> &nbsp;<input type="file" name="item[]"></p>');
	}
-->
</script>
<h2>Extractor Version 2</h2>
<table width="500" border="0" align="center" cellpadding="10" cellspacing="1" bgcolor="#CCCCCC">
  <tr>
    <td bgcolor="#F3F3F3"><form method="post" action="index.php" enctype="multipart/form-data">
  
  <p>Upload only comma delimited CSV files, if it states (asin) then upload 1 asin per line, no comma's, if it says sku,merchantid, then have a 2 colum csv, first colum with the sku, second with the merchant id, one record per row/line.</p>
  <p>
    <select name="itemType[]"  id="itemType">
      <option value="ASIN">ASIN (asin)</option>
      <option value="UPC">UPC (upc)</option>
          </select> &nbsp;<input type="file" name="item[]">
  (<a href="#" onClick="addItemToList();return false;">add 1 more</a>)</p>
  <div id="itemContainer">
  
  </div>
  
<input type="submit" value="Go"> <input type="hidden" name="action" value="submitNow">
</form>
<p>NOTE: Cron jobs run once every 5 minutes and process approximately 30 ASINs every minute. Please upload as many ASINs as possible, in a single file, to ensure maximum efficiency.</p></td>
  </tr>
</table>
</p> 
<p align="center"><a href="index2.php" target="_blank">Original Extractor (deprecated)</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="test2.php" target="_blank">Test Tool 1</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="test3.php" target="_blank">Test Tool 2</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="test4.php">Test Tool 3</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="csvConvert.php">CSV Table Tool</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="importExportTool.php">Import Export Tool</a></p>
<p align="center"><a href="index.php?action=logout">Log Out</a></p>
<?php
} else { //IF SESSION LOGIN DOESN'T EXIST
	include("login.php");
}


include("footer.php");
?>
</body>
</html>