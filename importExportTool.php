<?php
$debugMode = FALSE; //ENABLE DEBUGGING OUTPUT
if($_SESSION['login'] == 'janderson') $debugMode = FALSE; //ENABLE DEBUGGING FOR ME
@ini_set( 'upload_max_size' , '10000G' );
@ini_set( 'post_max_size', '10500G');
@ini_set( 'memory_limit', '120000G');
@ini_set( 'max_execution_time', '10000000' );
set_time_limit(10000000);
include("config.php");
include("functions.php");
$act = $_REQUEST['act'];
$allowedExtensions = array("csv");
$conversionMethodTypes = array();
$conversionMethodTypes[] = 'If equal to A, replace with B';
$conversionMethodTypes[] = 'If contains A, replace A with B';
$conversionMethodTypes[] = 'If contains A, replace all with B';
$conversionMethodTypes[] = 'If contains A, add B on end';
$conversionMethodTypes[] = 'If not equal to A, Replace with B';
$conversionMethodTypes[] = 'If not containing A, Replace with B';
$conversionMethodTypes[] = 'If not containing A, add B on end';
$conversionMethodTypes[] = 'If A is numeric, use currency format';
$conversionMethodTypes[] = 'Replace with A';
$conversionMethodParameters = array();
$conversionMethodParameters[] = 'A';
$conversionMethodParameters[] = 'B';/*
$conversionMethodParameters[] = 'C';
$conversionMethodParameters[] = 'D';
$conversionMethodParameters[] = 'E';
$conversionMethodParameters[] = 'F';*/
$newValueDelimiter = '{|ND|}';
$keyValueDelimiter = '{|KD|}';
if($debugMode) echo '<h1>Debug mode enabled.</h1>';
if($act == 'downloadImportedDataNow') {
$importID = $_REQUEST['importID'];
$exportScheme = $_REQUEST['exportScheme'];
if($debugMode) echo 'Import ID: '.$_REQUEST['importID'].'<BR>Export Scheme: '.$_REQUEST['exportScheme'].'<BR>';
$sql = mysql_query("SELECT * FROM importedData WHERE id = ".quote_smart($importID)."");
$count = mysql_num_rows($sql);
if($debugMode) echo "Memory Usage: ".echo_memory_usage()."<br>";
if($count == 0) {
if($debugMode) echo "We're sorry but the imported data you are looking for could not be found.<BR>";
$act = 'importHistory';
} else {
if($debugMode) echo "Memory Usage: ".echo_memory_usage()."<br>";
for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
if($debugMode) echo "(".$i.", ".$count.") Pulled importData.<BR><pre>".$arr['importData']."</pre><BR>";
if($debugMode) echo "Memory Usage: ".echo_memory_usage()."<br>";
if($arr['importData'] != '') {
$importedData = string2array($arr['importData']);
if($debugMode) echo "Array converted from import data containing ".count($importData)." entries.<BR>";
}
if($debugMode) echo "Memory Usage: ".echo_memory_usage()."<br>";
}
if($debugMode) echo 'String array : '.substr($importedData['importData'],0,50000).'<BR><BR><BR><BR><HR><BR><BR><BR><BR>';
$dataArray = $importedData;
if($debugMode) echo "Memory Usage: ".echo_memory_usage()."<br>";
if($debugMode) echo 'String Array<BR><pre>'.print_r($dataArray,1).'</pre><BR>';
if($debugMode) echo 'data print test.<pre>'.print_r($dataArray,1)."</pre><BR>";
if($debugMode) echo 'Export Scheme ID = '.$exportScheme.'<BR>';
$headerSQL = mysql_query("SELECT * FROM exportFields WHERE exportSchemeID = ".quote_smart($exportScheme)."");
$headerCount = mysql_num_rows($headerSQL);
$headerRow = '';
for($i = 0;$i < $headerCount;$i++) {
mysql_data_seek($headerSQL, $i);
$arr = mysql_fetch_array($headerSQL);
$arr['fieldName'] = str_replace('"', '""',$arr['fieldName']);
$headerRow .= '"'.$arr['fieldName'].'",';
}
$headerRow = substr($headerRow, 0, strlen($headerRow)-1); //REMOVE LAST COMMA
if($debugMode) echo 'Header row print test.<pre>'.print_r($headerRow,1)."</pre><BR>";
if($debugMode) echo "Memory Usage: ".echo_memory_usage()."<br>";
$rows = '';
$rowCount = count($dataArray);
$rowI = 0;
foreach($dataArray as $k => $v) {
$rowI++;
if($debugMode) echo '('.$rowI.', '.$rowCount.') Row print test.<pre>'.print_r($v,1)."</pre><BR>";
for($i = 0;$i < $headerCount;$i++) {
mysql_data_seek($headerSQL, $i);
$arr = mysql_fetch_array($headerSQL);
$masterFieldName = getValue("SELECT fieldName FROM masterTableFields WHERE id = ".quote_smart($arr['masterFieldID'])."");
if($arr['mergeSchemeID'] == '0' || $arr['mergeSchemeID'] == '') $field = $v[$masterFieldName];
else {
$field = mergeFields($v,$arr['mergeSchemeID'],$arr['masterFieldID']);
}
$value = str_replace('"', '""',runConversionScheme($field,$arr['conversionSchemeID']));
$rows .= '"'.$value.'",';
}
$rows = substr($rows, 0, strlen($rows)-1); //REMOVE LAST COMMA
$rows .= "\n";
if($debugMode) echo 'ROW ('.$rowI.', '.$rowCount.')<BR>';
if($debugMode) echo "Memory Usage: ".echo_memory_usage()."<br>";
}
if($debugMode) echo "Memory Usage: ".echo_memory_usage()."<br>";
if($debugMode) echo "headerRow is as follows...<BR><pre>".print_r($headerRow,1)."</pre><BR><BR>";
if($debugMode) echo "rows is as follows...<BR><pre>".print_r($rows,1)."</pre><BR><BR>";
$rows = str_replace("\r","",$rows);
if($debugMode) echo "Memory Usage: ".echo_memory_usage()."<br>";
$date = date("m-d-y");
header("Content-type: application/x-msdownload"); 
header("Content-Disposition: attachment; filename=Export_Report_".$date."_".time().".csv"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 
print "$headerRow\n$rows";
exit();
//$act = 'importHistory';
}
}
?><html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Amazon API System Tools</title>
<link rel="stylesheet" href="style.css">
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container">
<?php
include("header.php");
?>
<p align="center"><a href="importExportTool.php?act=masterTableEditor">Master Table Fields</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<a href="importExportTool.php?act=exportSchemeEditor">Export Schemes</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<a href="importExportTool.php?act=importSchemeEditor">Import Schemes</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
<a href="importExportTool.php?act=conversionMethods">Conversion Schemes</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
<a href="importExportTool.php?act=mergeSchemes">Merge Schemes</a><br>
<br>
<a href="importExportTool.php?act=importHistory">Import  History</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<a href="importExportTool.php?act=newConversion">Run Conversion</a></p>
<!-- START CONVERSION METHODS EDITOR -->
<?php
if($act == 'importData') {
if($_FILES['importFile']['error'] == UPLOAD_ERR_OK) {
if(isAllowedExtension($_FILES['importFile']['name'])) {
$filename = file_get_contents($_FILES['importFile']['tmp_name']);
$hdrs = csv_headers_to_array($filename);
if(is_array($hdrs[0])) $importedHeaders = $hdrs[0];
else $importedHeaders = $hdrs;
$i = 0;
$headers = array();
$field = array();
$conversionID = array();
$excluded = array();
foreach($importedHeaders as $k => $importedHeader) {
$importedHeader = (ltrim(rtrim(trim($importedHeader))));
$lookupQuery = "SELECT * FROM importFields WHERE importSchemeID = ".quote_smart($_REQUEST['importScheme'])." AND fieldName = ".quote_smart($importedHeader);
$importField = getArray($lookupQuery);
$id = $importField['id'];
$conversionID[$i] = $importField['conversionSchemeID'];
$field[$i] = $id;
if($importField['masterFieldID'] > 0) {
$headers[$i] = getValue("SELECT fieldName FROM masterTableFields WHERE id = ".quote_smart($importField['masterFieldID'])."");
$excluded[$i] = false;
if($debugMode) echo '$headers['.$i.'] = "";<BR>SELECT fieldName FROM masterTableFields WHERE id = '.quote_smart($importField['masterFieldID']).' ('.getValue("SELECT fieldName FROM masterTableFields WHERE id = ".quote_smart($importField['masterFieldID'])."").')<BR>$excluded['.$i.'] = false;<BR>';
} else {
$excluded[$i] = true;
}
$i++;
}
unset($importedHeaders, $hdrs, $importedHeader);
if($debugMode) flush();
$fileRows = csv_to_array($filename,',','"','\\',"\n",FALSE);
$rowI = 0;
$newRow = array();
$baseArray = array();
if($debugMode) flush();
$testRow = $fileRows[0];
foreach($testRow as $k => $v) {
}
foreach($fileRows as $k => $v) {
$fieldRows = $v;//['Array'];
$i = 0;
if(is_array($fieldRows)) {
foreach($fieldRows as $fieldKey => $fieldValue) {
if($excluded[$i] !== TRUE) {
$conversionScheme = $conversionID[$i];
$fieldValue = runConversionScheme((ltrim(rtrim(trim($fieldValue)))), $conversionScheme);
$masterTableFieldName = $headers[$i];
$newRow[$masterTableFieldName] = $fieldValue;
if($debugMode) echo '$newRow['.$masterTableFieldName.'] = "'.$fieldValue.'"<BR>';
} else {
}
$i++;
}
$baseArray[$rowI] = $newRow;
$newRow = array();
}
//}
$rowI++;
if($debugMode) flush();
}
if($debugMode) flush();
unset($rowI, $newRow, $headers, $excluded, $fieldRows, $fileRows, $field, $hdrs, $importField);
if($debugMode) flush();
if (!mysql_ping($dbConn)) {
if($debugMode) echo '1 Lost connection, exiting after query #1<BR>';
mysql_close($dbConn);
$dbConn = mysql_connect('localhost', '', '');
if($dbConn === FALSE) exit("We are currently working to improve server performence and are under server maintanence. Please check back in 15 minutes.");
$dbSel = mysql_select_db('');
if($dbSel === FALSE) exit("We are currently working to improve server performence and are under server maintanence. Please check back in 15 minutes..");
}
if($debugMode) echo "Memory Usage: ".echo_memory_usage()."<br>";
if($debugMode) echo 'Final data value count ('.count($baseArray).')<BR>';
$finalData = array2string($baseArray);
if($debugMode) echo 'Final data length : '.strlen($finalData).'<BR>';
$thisImportID = rand(9999,getrandmax());
if($debugMode) flush();
mysql_query("INSERT INTO importedData (admin, importData, customer, importDate, importSchemeID, importID) VALUES (".quote_smart($_SESSION['login']).", ".quote_smart($finalData).", ".quote_smart($_REQUEST['customer']).", NOW(), ".quote_smart($_REQUEST['importScheme']).",".quote_smart($thisImportID).")") or die(mysql_error());

$importID = mysql_insert_id();
echo "Successfully imported new data. Data Import ID: ".$importID."<BR>";
if($debugMode) echo "Memory Usage: ".echo_memory_usage()."<br>";
flush();
$_REQUEST['importID'] = $importID;
$act = 'downloadImportedData';
}
}
}
//RUN CONVERSION
if($act == 'newConversion') {
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Conversion</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="importData">
Choose a Comma Delimited CSV<br>
<input type="file" name="importFile"> <br>
Choose an Import Scheme: 
<select name="importScheme">
<?php 
$sql = mysql_query("SELECT * FROM importSchemes ORDER BY schemeName ASC");
$count = mysql_num_rows($sql);
for($i =0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
echo '<option value="'.$arr['id'].'">'.$arr['schemeName'].' ('.$arr['customer'].')</option>';
}
?>
</select><br>
Optional Customer Name: <input type="text" name="customer">
(for tracking purposes only)<br>
<br>
<input type="submit" name="submit" value="Continue">
</form>
</td></tr>
</table>
<?php
}
//DOWNLOAD IMPORTED FILE
if($act == 'downloadImportedData') {
$importID = $_REQUEST['importID'];
?>
<form method="post" action="importExportTool.php" enctype="multipart/form-data" target="_blank">
<input type="hidden" name="act" value="downloadImportedDataNow">
<input type="hidden" name="importID" value="<?php echo $importID; ?>">
Select Export Scheme:
<select name="exportScheme">
<?php 
$sql = mysql_query("SELECT * FROM exportSchemes ORDER BY schemeName ASC");
$count = mysql_num_rows($sql);
for($i =0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
echo '<option value="'.$arr['id'].'">'.$arr['schemeName'].' ('.$arr['customer'].')</option>';
}
?>
</select><br>
Select Download Format: <select name="exportFormat"><option value="csv">Comma Delimited CSV</option></select><br>
<br>
<input type="submit" name="submit" value="Continue to Download">
</form>
<br>
<br>
<a href="importExportTool.php?act=importHistory">Back to import history</a>
<?php
}
//VIEW IMPORT HISTORY
if($act == 'importHistory') {
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>Imported Data History</strong></td>
</tr>
<tr><td>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr><td>Import ID</td><td>Customer</td><td>Import Scheme</td><td>       Date Imported       </td><td>    Options      </td></tr>
<?php 
$sql = mysql_query("SELECT DISTINCT importID, id, importDate	FROM importedData WHERE admin = ".quote_smart($_SESSION['login'])." ORDER BY id DESC");
$count = mysql_num_rows($sql);
for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
?>
<tr><td><?php echo $arr['id']; ?></td><td>       <?php echo $arr['customer']; ?>      </td><td>       <?php echo getValue("SELECT CONCAT(schemeName, ' (', customer, ')') FROM importSchemes WHERE id = ".quote_smart($arr['importSchemeID']).""); ?>      </td><td>       <?php echo $arr['importDate']; ?>      </td><td>    <a href="importExportTool.php?act=downloadImportedData&importID=<?php echo $arr['id']; ?>">Download</a>      </td></tr>
<?php
}
?></table>
</td></tr>
</table>
<?php
}
//CREATE A NEW CONVERSION METHOD
if($act == 'createNewConversionMethod') {
if($_REQUEST['conversionMethodName'] == '') {
echo 'You must enter a conversion scheme name to continue.';
$act = 'newConversionMethod';
} else {
$sql = "INSERT INTO conversionMethods 
(conversionSchemeID, conversionName, `conversionType`";
//GENERATE PARAMS SYSTEM IS USING
foreach($conversionMethodParameters as $k => $v) $sql .= ',`conversionParam'.$v.'`';
$sql .=") VALUES (".quote_smart($_REQUEST['conversionSchemeID']).", ".quote_smart($_REQUEST['conversionMethodName']).", ".quote_smart($_REQUEST['conversionType'])."";
foreach($conversionMethodParameters as $k => $v) $sql .= ','.quote_smart($_REQUEST['conversionParam'.$v]);
//SETUP METHOD
$sql .= ")";
mysql_query($sql) or die(mysql_error());
$_REQUEST['conversionMethodID'] = mysql_insert_id();
echo "Added your conversion method successfully.";
$act = 'editConversionScheme';
}
}
//NEW CONVERSION METHOD FORM
if($act == 'newConversionMethod') {
$conversionScheme = getArray("SELECT * FROM conversionSchemes WHERE id = ".quote_smart($_REQUEST['conversionSchemeID'])."");
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Conversion Method</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="createNewConversionMethod">
<input type="hidden" name="conversionSchemeID" value="<?php echo $_REQUEST['conversionSchemeID']; ?>">
Scheme: <?php echo $conversionScheme['conversionName']; ?><br>
Name This Conversion Method: <input type="text" name="conversionMethodName" value="<?php echo $_REQUEST['conversionMethodName']; ?>">
<br>
Conversion Type: <select name="conversionType">
<?php
foreach($conversionMethodTypes as $k => $v) { 
?><option value="<?php echo htmlspecialchars($v); ?>" <?php if($conversionMethod['conversionType'] == $v) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($v); ?></option><?php
}
?>
</select>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">

<?php foreach($conversionMethodParameters as $k => $v) { ?>
<tr><td>       Parameter <?php echo htmlspecialchars($v); ?>       </td><td>       <input type="text" name="conversionParam<?php echo htmlspecialchars($v); ?>" value="<?php echo $conversionMethod['conversionParam'.$v]; ?>">       </td></tr>
<?php
}
?></table>
<input type="submit" name="submit" value="Continue">
</form>
</td></tr>
</table>
<?php
}
//UPDATE CONVERSION METHOD
if($act == 'submitConversionMethodEdit') {
$sql = "UPDATE conversionMethods SET 
conversionName = ".quote_smart($_REQUEST['conversionName']).",
conversionType = ".quote_smart($_REQUEST['conversionType'])."";
//GENERATE PARAMS SYSTEM IS USING
foreach($conversionMethodParameters as $k => $v) $sql .= ',conversionParam'.$v.' = '.quote_smart($_REQUEST['conversionParam'.$v]).'';
//SETUP METHOD
$sql .= "
WHERE id = ".quote_smart($_REQUEST['conversionMethodID'])."";
mysql_query($sql) or die(mysql_error());
echo "Updated your conversion method successfully.";
$act = 'editConversionScheme';
}
//CREATE NEW CONVERSION SCHEME
if($act == 'newConversionScheme') {
if(getValue("SELECT COUNT(id) FROM conversionSchemes WHERE conversionName = ".quote_smart($_REQUEST['conversionSchemeName'])."") > 0) {
echo 'This conversion scheme name already exists. Please edit the current scheme with this name, or choose a different name.';
$act = 'conversionMethods';
} else {
$schemeID = createConversionScheme($_REQUEST['conversionSchemeName']);
$_REQUEST['conversionSchemeID'] = $schemeID;
$act = 'editConversionScheme';
}
}
if($act == 'submitConversionMethodBulkAdd') {
if($_FILES['bulkSchemeFile']['error'] == UPLOAD_ERR_OK) {
if(isAllowedExtension($_FILES['bulkSchemeFile']['name'])) {
$filename = file_get_contents($_FILES['bulkSchemeFile']['tmp_name']);
$fileRows = split("\n", $filename);
foreach($fileRows as $k => $v) {
$row = split(",", $v);
$name = $row[0];
$paramA = $row[1];
$paramB = $row[2];
if(getValue("SELECT COUNT(id) FROM conversionMethods WHERE conversionSchemeID = ".quote_smart($_REQUEST['conversionSchemeID'])." AND conversionParamA = ".quote_smart($paramA)." AND conversionParamB = ".quote_smart($paramB)."") == 0) {
if($name == '') {
echo 'You must enter a conversion scheme name to continue.';
$act = 'editConversionMethod';
} else {
$sql = "INSERT INTO conversionMethods 
(conversionSchemeID, conversionName, `conversionType`, conversionParamA, conversionParamB) VALUES (".quote_smart($_REQUEST['conversionSchemeID']).", ".quote_smart($name).", ".quote_smart($_REQUEST['conversionType']).",".quote_smart($paramA).", ".quote_smart($paramB).")";
mysql_query($sql) or die(mysql_error());
}
}
}
}
}
$act = 'editConversionScheme';
}
//EDIT CONVERSION METHOD FORM
if($act == 'editConversionMethod') {
$conversionScheme = getArray("SELECT * FROM conversionSchemes WHERE id = ".quote_smart($_REQUEST['conversionSchemeID'])."");
$conversionMethod = getArray("SELECT * FROM conversionMethods WHERE id = ".quote_smart($_REQUEST['conversionMethodID'])."");
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>Edit Conversion Method</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="submitConversionMethodEdit">
<input type="hidden" name="conversionSchemeID" value="<?php echo $_REQUEST['conversionSchemeID']; ?>">
<input type="hidden" name="conversionMethodID" value="<?php echo $_REQUEST['conversionMethodID']; ?>">
Name This Conversion Method: <input type="text" name="conversionName" value="<?php echo $conversionMethod['conversionName']; ?>"><br><br>
Conversion Type: <select name="conversionType">
<?php
foreach($conversionMethodTypes as $k => $v) { 
?><option value="<?php echo htmlspecialchars($v); ?>" <?php if($conversionMethod['conversionType'] == $v) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($v); ?></option><?php
}
?>
</select>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<?php foreach($conversionMethodParameters as $k => $v) { ?>
<tr><td>       Parameter <?php echo htmlspecialchars($v); ?>       </td><td>       <input type="text" name="conversionParam<?php echo htmlspecialchars($v); ?>" value="<?php echo $conversionMethod['conversionParam'.$v]; ?>">       </td></tr>
<?php
}
?></table>
<input type="submit" name="submit" value="Save">
<br>
<br>
</form>
</td>
</tr>
</table>
<?php
}
//DELETE CONVERSION METHOD FROM SCHEME
if($act == 'deleteConversionMethod') {
$conversionMethod = getArray("SELECT * FROM conversionMethods WHERE id = ".quote_smart($_REQUEST['conversionMethodID'])." LIMIT 1");
mysql_query("DELETE FROM conversionMethods WHERE id = ".quote_smart($_REQUEST['conversionMethodID'])." LIMIT 1") or die(mysql_error());
echo "Deleted conversion method.<BR>Name: ".$conversionMethod['conversionName']."<BR>conversionType: ".$conversionMethod['conversionType']."<BR>";
if($conversionMethod['conversionParamA'] != '') echo 'Parameter A: '.$conversionMethod['conversionParamA'].'<BR>';
if($conversionMethod['conversionParamB'] != '') echo 'Parameter B: '.$conversionMethod['conversionParamB'].'<BR>';
if($conversionMethod['conversionParamC'] != '') echo 'Parameter C: '.$conversionMethod['conversionParamC'].'<BR>';
if($conversionMethod['conversionParamD'] != '') echo 'Parameter D: '.$conversionMethod['conversionParamD'].'<BR>';
if($conversionMethod['conversionParamE'] != '') echo 'Parameter E: '.$conversionMethod['conversionParamE'].'<BR>';
if($conversionMethod['conversionParamF'] != '') echo 'Parameter F: '.$conversionMethod['conversionParamF'].'<BR>';
$act = 'editConversionScheme';
}
// UPDATE CONVERSION SCHEME NAME FROM EDIT EXISTING CONVERSION SCHEME
if($act == 'submitConversionSchemeEdit') {
mysql_query("UPDATE conversionSchemes SET conversionName = ".quote_smart($_REQUEST['conversionSchemeName'])." WHERE id = ".quote_smart($_REQUEST['conversionSchemeID'])."") or die(mysql_error());
echo 'Your scheme has been updated successfully.<BR>';
$act = 'editConversionScheme';
}
//EDIT AN EXISTING CONVERSION SCHEME
if($act == 'editConversionScheme') {
$conversionScheme = getArray("SELECT * FROM conversionSchemes WHERE id = ".quote_smart($_REQUEST['conversionSchemeID'])."");
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>Edit Conversion Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="submitConversionMethodBulkAdd">
<input type="hidden" name="conversionSchemeID" value="<?php echo $_REQUEST['conversionSchemeID']; ?>">
<input type="hidden" name="conversionMethodID" value="<?php echo $_REQUEST['conversionMethodID']; ?>">
<input type="file" name="bulkSchemeFile"> <select name="conversionType">
<?php
foreach($conversionMethodTypes as $k => $v) { 
?><option value="<?php echo htmlspecialchars($v); ?>" <?php if($conversionMethod['conversionType'] == $v) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($v); ?></option><?php
}
?>
</select><input type="submit" name="Bulk Upload"> (CSV File with Method Name, Param A, Param B)
</form>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="submitConversionSchemeEdit">
<input type="hidden" name="conversionSchemeID" value="<?php echo $_REQUEST['conversionSchemeID']; ?>">
Conversion Scheme: <input type="text" name="conversionSchemeName" value="<?php echo $conversionScheme['conversionName']; ?>">
<input type="submit" name="submit" value="Save">
<br>
<br>
<strong>  Current Conversion Method Actions</strong>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>Method Name</strong></td>
<td><strong>Method Type</strong></td>
<td><strong>Method Parameters</strong></td>
<td><strong>Options</strong></td>
</tr>
<?php
$sql = mysql_query("SELECT * FROM conversionMethods WHERE conversionSchemeID = ".quote_smart($conversionScheme['id'])." ORDER BY conversionName ASC") or die(mysql_error());
$count = mysql_num_rows($sql);
for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
?>
<tr>
<td valign="top">
<a href="importExportTool.php?act=editConversionMethod&conversionMethodID=<?php echo $arr['id']; ?>&conversionSchemeID=<?php echo $_REQUEST['conversionSchemeID']; ?>"><?php echo $arr['conversionName']; ?></a></td>
<td valign="top">
<?php echo $arr['conversionType']; ?>
</td><td valign="top">
<?php 
foreach($conversionMethodParameters as $k => $v) if($arr['conversionParam'.$v] != '') echo 'Parameter '.$v.': '.$arr['conversionParam'.$v].'<BR>';
?>
</td><td valign="top">
<a href="importExportTool.php?act=deleteConversionMethod&conversionMethodID=<?php echo $arr['id']; ?>&conversionSchemeID=<?php echo $_REQUEST['conversionSchemeID']; ?>">DELETE</a> | 
<a href="importExportTool.php?act=editConversionMethod&conversionMethodID=<?php echo $arr['id']; ?>&conversionSchemeID=<?php echo $_REQUEST['conversionSchemeID']; ?>">EDIT</a>
</td></tr>
<?php
}
?>
<tr><td colspan="3"><div align="right"><a href="importExportTool.php?act=newConversionMethod&conversionSchemeID=<?php echo $_REQUEST['conversionSchemeID']; ?>">Add New Conversion Method</a></div></td></tr>
</table>
</form>
</td></tr>
</table>
<?php
}
//DEFAULT PAGE FOR CONVERSIONS, SHOW CONVERSION METHODS AND MAKE NEW METHOD
if($act == 'conversionMethods') {
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Conversion Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="newConversionScheme">
Name This Conversion Scheme: <input type="text" name="conversionSchemeName" value="<?php echo $_REQUEST['conversionSchemeName']; ?>">
<input type="submit" name="submit" value="Continue">
<br>
<br>
</form>
</td></tr>
<tr><td>
<strong>  Current Conversion Schemes</strong>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>Conversion Scheme</strong></td>
<td><strong>Methods</strong></td>
<td><strong>Options</strong></td>
</tr>
<?php
$sql = mysql_query("SELECT * FROM conversionSchemes ORDER BY conversionName ASC") or die(mysql_error());
$count = mysql_num_rows($sql);
for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
?>
<tr>
<td>
<a href="importExportTool.php?act=editConversionScheme&conversionSchemeID=<?php echo $arr['id']; ?>"><?php echo $arr['conversionName']; ?></a></td>
<td>
<?php echo getValue("SELECT COUNT(id) FROM conversionMethods WHERE conversionSchemeID = ".quote_smart($arr['id']).""); ?> Params.
</td><td>
<a href="importExportTool.php?act=editConversionScheme&conversionSchemeID=<?php echo $arr['id']; ?>">EDIT</a>
</td></tr>
<?php
}
?>
</table>
</td></tr>
</table>
<?php
}
?>
<!-- END CONVERSION METHODS EDITOR -->
<!-- START MERGE SCHEMES -->
<?php
if($act == 'saveMergeScheme') {
mysql_query("UPDATE mergeSchemes SET schemeName = ".quote_smart($_REQUEST['schemeName']).", concatValue = ".quote_smart($_REQUEST['concatValue'])." WHERE id = ".quote_smart($_REQUEST['mergeSchemeID'])."");
mysql_query("DELETE FROM mergeFields WHERE mergeSchemeID = ".quote_smart($_REQUEST['mergeSchemeID'])."");
foreach($_REQUEST['mergeField'] as $k => $v) {
mysql_query("INSERT INTO mergeFields (mergeSchemeID, masterFieldID) VALUES (".quote_smart($_REQUEST['mergeSchemeID']).", ".quote_smart($v).")");
}
echo 'Your merge scheme has been successfully updated.<BR>';
$act = 'mergeSchemes';
}
if($act == 'editMergeScheme') {
?>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
Scheme Name: <input type="text" name="schemeName" value="<?php echo htmlspecialchars($_REQUEST['schemeName']); ?>"><BR>
Create a new merge scheme with a concatinating character of <input type="text" name="concatValue" value="<?php echo htmlspecialchars($_REQUEST['concatValue']); ?>"><br>
Please check each field that will be merged from the master field set.<br>
<input type="hidden" name="act" value="saveMergeScheme">
<input type="hidden" name="mergeSchemeID" value="<?php echo urlencode($_REQUEST['mergeSchemeID']); ?>">
<?php 
$MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$MTcount=mysql_num_rows($MTsql);
for($MTi = 0;$MTi < $MTcount;$MTi++) {
mysql_data_seek($MTsql, $MTi);
$MTarr = mysql_fetch_array($MTsql);
?><input type="checkbox" name="mergeField[]" value="<?php echo htmlspecialchars($MTarr['id']);?>"<?php
if(getValue("SELECT COUNT(id) FROM mergeFields WHERE mergeSchemeID = ".quote_smart($_REQUEST['mergeSchemeID'])." AND masterFieldID = ".quote_smart($MTarr['id'])."") > 0) echo ' checked="checked"';
?>> &nbsp; : &nbsp; <?php echo $MTarr['fieldName']; ?><br>
<?php
}
?>
<br>
<input type="submit" name="submit" value="Save Merge Scheme">
<br>
<br>
</form><?php
}
if($act == 'saveNewMergeScheme') {
if(getValue("SELECT COUNT(id) FROM mergeSchemes WHERE schemeName = ".quote_smart($_REQUEST['schemeName'])."") > 0) {
echo 'A Scheme with this name already exists.';
$act = 'mergeSchemes';
} else {
mysql_query("INSERT INTO mergeSchemes (schemeName, concatValue) VALUES (".quote_smart($_REQUEST['schemeName']).", ".quote_smart($_REQUEST['concatValue']).")");
$mergeSchemeID = mysql_insert_id();
foreach($_REQUEST['mergeField'] as $k => $v) {
mysql_query("INSERT INTO mergeFields (mergeSchemeID, masterFieldID) VALUES (".quote_smart($mergeSchemeID).", ".quote_smart($v).")");
}
echo 'Successfully created your import scheme.<BR>';
}
$act = 'mergeSchemes';
}
if($act == 'newMergeScheme') {
if(getValue("SELECT COUNT(id) FROM mergeSchemes WHERE schemeName = ".quote_smart($_REQUEST['schemeName'])."") > 0) {
echo 'A Scheme with this name already exists.';
$act = 'mergeSchemes';
} else {
?>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
Scheme Name: <input type="text" name="schemeName" value="<?php echo htmlspecialchars($_REQUEST['schemeName']); ?>"><BR>
Create a new merge scheme with a concatinating character of <input type="text" name="concatValue" value="<?php echo htmlspecialchars($_REQUEST['concatValue']); ?>"><br>
Please check each field that will be merged from the master field set.<br>
<input type="hidden" name="act" value="saveNewMergeScheme">
<?php 
$MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$MTcount=mysql_num_rows($MTsql);
for($MTi = 0;$MTi < $MTcount;$MTi++) {
mysql_data_seek($MTsql, $MTi);
$MTarr = mysql_fetch_array($MTsql);
?><input type="checkbox" name="mergeField[]" value="<?php echo htmlspecialchars($MTarr['id']);?>"> &nbsp; : &nbsp; <?php echo $MTarr['fieldName']; ?><br>
<?php
}
?>
<br>
<input type="submit" name="submit" value="Save Merge Scheme">
<br>
<br>
</form>
<?php
} //END IF COUNT SCHEME NAME == 0
}
//DEFAULT PAGE FOR CONVERSIONS, SHOW CONVERSION METHODS AND MAKE NEW METHOD
if($act == 'mergeSchemes') {
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Merge Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="newMergeScheme">
Name This Merge Scheme: <input type="text" name="schemeName" value="<?php echo $_REQUEST['schemeName']; ?>"><br>
Concatinating Character: <input type="text" name="concatValue" value="<?php if($_REQUEST['concatValue'] == '') echo ','; else echo $_REQUEST['concatValue']; ?>">
<input type="submit" name="submit" value="Continue">
<br>
<br>
</form>
</td></tr>
<tr><td>
<strong>  Current Merge Schemes</strong>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>Merge Scheme</strong></td>
<td><strong>Merge Fields</strong></td>
<td><strong>Options</strong></td>
</tr>
<?php
$sql = mysql_query("SELECT * FROM mergeSchemes ORDER BY schemeName ASC") or die(mysql_error());
$count = mysql_num_rows($sql);
for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
?>
<tr>
<td>
<a href="importExportTool.php?act=editMergeScheme&mergeSchemeID=<?php echo $arr['id']; ?>"><?php echo $arr['schemeName']; ?></a></td>
<td>
<?php echo getValue("SELECT COUNT(id) FROM mergeFields WHERE mergeSchemeID = ".quote_smart($arr['id']).""); ?>
</td><td>
<a href="importExportTool.php?act=editMergeScheme&mergeSchemeID=<?php echo $arr['id']; ?>">EDIT</a>
</td></tr>
<?php
}
?>
</table>
</td></tr>
</table>
<?php
}
?>
<!-- END MERGE SCHEMES -->
<!-- START IMPORT SCHEME EDITOR -->
<?php
//NEW IMPORT SCHEME FORM
//CREATE NEW IMPORT SCHEME
if($act == 'SaveNewImportScheme') {
$fieldCount = count($_REQUEST['fieldName']);
$schemeName = $_REQUEST['schemeName'];
$customer = $_REQUEST['customer'];
$schemeID = createImportScheme($schemeName, $customer);
for($i = 0;$i < $fieldCount;$i++) {
if($_REQUEST['fieldName'][$i] != '') {
addFieldToImportScheme($schemeID, $_REQUEST['fieldName'][$i], $_REQUEST['conversionMethod'][$i], $_REQUEST['masterFieldID'][$i], $_REQUEST['fieldDescription'][$i]);
}
}
echo 'Your new scheme has been successfully created.';
$act = 'importSchemeEditor';
}
//NEW IMPORT SCHEME FORM
//ADD FIELDS TO NEW IMPORT SCHEME FORM
if($act == 'submitNewImportSchemeAddFields') {
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Import Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="SaveNewImportScheme">
<?php
if($_FILES['fieldNameFile']['error'] == UPLOAD_ERR_OK) {
if(isAllowedExtension($_FILES['fieldNameFile']['name'])) {
$filename = file_get_contents($_FILES['fieldNameFile']['tmp_name']);
$fieldRows = csv_headers_to_array($filename);
$CMsql = mysql_query("SELECT * FROM conversionSchemes ORDER BY conversionName ASC") or die(mysql_error());
$CMcount=mysql_num_rows($CMsql);
$conversionSchemes = '';
for($CMi = 0;$CMi < $CMcount;$CMi++) {
mysql_data_seek($CMsql, $CMi);
$CMarr = mysql_fetch_array($CMsql);
$conversionSchemes .= '<option value="'.$CMarr['id'].'">'.$CMarr['conversionName'].'</option>';
}
?>
<table class="table table-hover table-bordered table-striped"  width="100%">
<tr><td>
Field to Import</td>
<td>Master Field Association</td>
<td>Conversion Scheme</td>
<td>Description of Field</td>
</tr>
<?php
$usedFields = array();
foreach($fieldRows as $k => $v) {
if($v != '' && !in_array(strtolower($v), $usedFields) && getValue("SELECT COUNT(id) FROM importFields WHERE importSchemeID = ".quote_smart($_REQUEST['importSchemeID'])."") == 0) {
$usedFields[] = strtolower($v);
?>
<tr><td>
<input name="fieldName[]" value="<?php echo htmlspecialchars($v); ?>">  </td>
<td><select name="masterFieldID[]">
<option value="">Ignore Field</option>
<?php
$masterFields = '';
$MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$MTcount=mysql_num_rows($MTsql);
for($MTi = 0;$MTi < $MTcount;$MTi++) {
mysql_data_seek($MTsql, $MTi);
$MTarr = mysql_fetch_array($MTsql);
$masterFields .= '<option value="'.$MTarr['id'].'"';
if(strtolower($MTarr['fieldName']) == strtolower($v)) $masterFields .= ' selected="selected"';
$masterFields .= '>'.$MTarr['fieldName'].'</option>';
}
echo $masterFields; ?>
</select></td>
<td><select name="conversionMethod[]">
<option value="">Ignore Field</option>
<?php echo $conversionSchemes; ?>
</select></td>
<td><input name="fieldDescription[]" value="<?php echo htmlspecialchars($v); ?>"></td>
</tr>
<?php
}
}
?></table><?php
}
}
?>
<input type="hidden" name="schemeName" value="<?php echo $_REQUEST['schemeName']; ?>">
<input type="hidden" name="customer" value="<?php if($_REQUEST['customer'] == '') echo $_REQUEST['customerName']; else echo $_REQUEST['customer']; ?>">
<br>
<br>
<input type="submit" name="submit" value="Save Import Scheme">
<br>
<br>
</form>
</td></tr>
</table>
<?php
}
//NEW IMPORT SCHEME FORM (name)
if($act == 'submitNewImportScheme') {
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Import Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="submitNewImportSchemeAddFields">
Choose a Comma Delimited CSV with 1 row and each field name on a column to mass import fields<br>
<input type="file" name="fieldNameFile"> 
<input type="hidden" name="schemeName" value="<?php echo $_REQUEST['schemeName']; ?>">
<input type="hidden" name="customer" value="<?php if($_REQUEST['customer'] == '') echo $_REQUEST['customerName']; else echo $_REQUEST['customer']; ?>">
<input type="submit" name="submit" value="Continue">
</form>
</td></tr>
</table>
<?php
}
//UPDATE IMPORT SCHEME
if($act == 'submitEditImportScheme') {
if($_REQUEST['Submit'] == 'Save as New') {
$fieldCount = count($_REQUEST['fieldName']);
if(getValue("SELECT COUNT(id) FROM importSchemes WHERE schemeName = ".quote_smart($_REQUEST['schemeName'])."") > 0) {
echo 'A Scheme with this name already exists.<BR>';
$act = 'editImportScheme';
} else {
$schemeName = $_REQUEST['schemeName'];
$customer = $_REQUEST['customer'];
$schemeID = createImportScheme($schemeName, $customer);
for($i = 0;$i < $fieldCount;$i++) {
if($_REQUEST['fieldName'][$i] != '') {
addFieldToImportScheme($schemeID, $_REQUEST['fieldName'][$i], $_REQUEST['conversionMethod'][$i], $_REQUEST['masterFieldID'][$i], $_REQUEST['fieldDescription'][$i]);
}
}
echo 'Your new scheme has been successfully created.';
$act = 'importSchemeEditor';
}
} else {
$schemeID = $_REQUEST['importSchemeID'];
$schemeName = $_REQUEST['schemeName'];
$count = count($_REQUEST['fieldName']);
$i = 0;
updateImportScheme($schemeID, $_REQUEST['schemeName'], $_REQUEST['customer']);
for($i = 0;$i < $count;$i++) {
if($_REQUEST['fieldID'][$i] != '') {
$sq = "UPDATE importFields SET fieldName = ".quote_smart($_REQUEST['fieldName'][$i]).", conversionSchemeID = ".quote_smart($_REQUEST['conversionMethod'][$i]).", masterFieldID = ".quote_smart($_REQUEST['masterFieldID'][$i]).", importSchemeID = ".quote_smart($schemeID).", fieldDescription = ".quote_smart($_REQUEST['fieldDescription'][$i])." WHERE id = ".quote_smart($_REQUEST['fieldID'][$i])." LIMIT 1";
mysql_query($sq) or die(mysql_error());
} else {
addFieldToImportScheme($schemeID, $_REQUEST['fieldName'][$i], $_REQUEST['conversionMethod'][$i], $_REQUEST['masterFieldID'][$i], $_REQUEST['fieldDescription'][$i]);
}
}
echo 'Your new scheme has been successfully updated.';
$act = 'editImportScheme';
}
}
//EDIT IMPORT SCHEME FORM
if($act == 'editImportScheme') {
$importScheme = getArray("SELECT * FROM importSchemes WHERE id = ".quote_smart($_REQUEST['importSchemeID'])."");
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Import Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="submitEditImportScheme">
<input type="hidden" name="importSchemeID" value="<?php echo $_REQUEST['importSchemeID']; ?>">
Choose a Comma Delimited CSV with 1 row and each field name on a column to mass import fields<br>
<input type="file" name="fieldNameFile"> 
<?php
$MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$MTcount=mysql_num_rows($MTsql);
$masterFields = '';
for($MTi = 0;$MTi < $MTcount;$MTi++) {
mysql_data_seek($MTsql, $MTi);
$MTarr = mysql_fetch_array($MTsql);
$masterFields .= '<option value="'.$MTarr['id'].'">'.$MTarr['fieldName'].'</option>';
}
$CMsql = mysql_query("SELECT * FROM conversionSchemes ORDER BY conversionName ASC") or die(mysql_error());
$CMcount=mysql_num_rows($CMsql);
$conversionSchemes = '';
for($CMi = 0;$CMi < $CMcount;$CMi++) {
mysql_data_seek($CMsql, $CMi);
$CMarr = mysql_fetch_array($CMsql);
$conversionSchemes .= '<option value="'.$CMarr['id'].'">'.$CMarr['conversionName'].'</option>';
}
if($_FILES['fieldNameFile']['error'] == UPLOAD_ERR_OK) {
if(isAllowedExtension($_FILES['fieldNameFile']['name'])) {
$filename = file_get_contents($_FILES['fieldNameFile']['tmp_name']);
$fieldRows = csv_headers_to_array($filename);
?>
<table class="table table-hover table-bordered table-striped"  width="100%">
<tr><td>New Field to Import</td>
<td>Master Field Association</td>
<td>Conversion Scheme</td>
<td>Description of Field</td>
</tr>
<?php
$usedFields = array();
foreach($fieldRows as $k => $v) {
if($v != '' && !in_array(strtolower($v), $usedFields) && getValue("SELECT COUNT(id) FROM importFields WHERE exportSchemeID = ".quote_smart($_REQUEST['exportSchemeID'])."") == 0) {
$usedFields[] = strtolower($v);
?>
<tr><td>
<input type="hidden" name="fieldID[]" value="">
<input name="fieldName[]" value="<?php echo htmlspecialchars($v); ?>">  </td>
<td><select name="masterFieldID[]">
<option value="">Ignore Field</option>
<?php
$masterFields = '';
$MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$MTcount=mysql_num_rows($MTsql);
for($MTi = 0;$MTi < $MTcount;$MTi++) {
mysql_data_seek($MTsql, $MTi);
$MTarr = mysql_fetch_array($MTsql);
$masterFields .= '<option value="'.$MTarr['id'].'"';
if(strtolower($MTarr['fieldName']) == strtolower($v)) $masterFields .= ' selected="selected"';
$masterFields .= '>'.$MTarr['fieldName'].'</option>';
}
echo $masterFields; ?>
</select></td>
<td><select name="conversionMethod[]">
<option value="">No Conversion Scheme</option>
<?php echo $conversionSchemes; ?>
</select></td>
<td><input name="fieldDescription[]" value="<?php echo htmlspecialchars($v); ?>"></td>
</tr>
<?php
}
}
?></table><?php
}
}
?>
<br>
Import Scheme Name: <input type="text" name="schemeName" value="<?php echo $importScheme['schemeName']; ?>"><br>
Customer: <input type="text" name="customer" value="<?php echo $importScheme['customer']; ?>"><br>
<table class="table table-hover table-bordered table-striped"  width="100%">
<tr><td>
Field to Import</td>
<td>Master Field Association</td>
<td>Conversion Scheme</td>
<td>Description of Field</td>
</tr>
<?php
$sql = mysql_query("SELECT * FROM importFields WHERE importSchemeID = ".quote_smart($_REQUEST['importSchemeID'])."") or die(mysql_error());
$count = mysql_num_rows($sql);
for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
?>
<tr><td>
<input type="hidden" name="fieldID[]" value="<?php echo $arr['id']; ?>">
<input name="fieldName[]" value="<?php echo $arr['fieldName']; ?>">  </td>
<td><select name="masterFieldID[]">
<option value="">Ignore Field</option>
<?php
$masterFields = '';
$MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$MTcount=mysql_num_rows($MTsql);
$masterFields = '';
for($MTi = 0;$MTi < $MTcount;$MTi++) {
mysql_data_seek($MTsql, $MTi);
$MTarr = mysql_fetch_array($MTsql);
$masterFields .= '<option value="'.$MTarr['id'].'"';
if($MTarr['id'] == $arr['masterFieldID']) $masterFields .= ' selected="selected"';
$masterFields .= '>'.$MTarr['fieldName'].'</option>';
}
echo $masterFields;
?>
</select></td>
<td><select name="conversionMethod[]">
<option value="">No Conversion Scheme</option>
<?php
$CMsql = mysql_query("SELECT * FROM conversionSchemes ORDER BY conversionName ASC") or die(mysql_error());
$CMcount=mysql_num_rows($CMsql);
$conversionSchemes = '';
for($CMi = 0;$CMi < $CMcount;$CMi++) {
mysql_data_seek($CMsql, $CMi);
$CMarr = mysql_fetch_array($CMsql);
$conversionSchemes .= '<option value="'.$CMarr['id'].'"';
if($CMarr['id'] == $arr['conversionSchemeID']) $conversionSchemes .= ' selected="selected"';
$conversionSchemes .= '>'.$CMarr['conversionName'].'</option>';
}
echo $conversionSchemes; ?>
</select></td>
<td><input name="fieldDescription[]" value="<?php echo $arr['fieldDescription']; ?>"></td>
</tr>
<?php
}
?></table>
<input type="submit" name="Submit" value="Update Scheme">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="Submit" value="Save as New">
</form>
</td></tr>
</table>
<?php
}
//DEFAULT IMPORT SCHEME PAGE
if($act == 'importSchemeEditor') {
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Import Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="submitNewImportScheme">
New Import Scheme Name (For Display) : <input type="text" name="schemeName">
<br>
<br>
New Import Scheme Customer (For Tracking) : <select name="customer">
<option value="">Enter New Customer Name</option>
<?php 
$sql = mysql_query("SELECT DISTINCT customer FROM importSchemes ORDER BY customer ASC") or die(mysql_error());
$count = mysql_num_rows($sql);
if($count > 0) {
for($i=0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
?><option value="<?php echo $arr['customer']; ?>"><?php echo $arr['customer']; ?></option><?php
}
}
?>
</select> 
<input type="text" name="customerName">
<br>
<br>
<input type="submit" name="submit" value="Continue">
</form>
</td></tr>
<tr><td>
<p><strong>Current Import Schemes</strong><br>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr><td><strong>Import Scheme</strong></td><td><strong>Customer</strong></td><td>Options</td></tr>
<?php
$sql = mysql_query("SELECT * FROM importSchemes ORDER BY schemeName ASC") or die(mysql_error());
$count=mysql_num_rows($sql);
for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
echo '<tr><td>'.$arr['schemeName'].'</td><td>'.$arr['customer'].'</td><td>';
echo '<a href="importExportTool.php?act=editImportScheme&importSchemeID='.$arr['id'].'">Edit Scheme</a></td></tr>';
}
?>
</table>
</p></td></tr>
</table>
<?php
}
?>
<!-- END IMPORT SCHEME EDITOR -->
<!-- START EXPORT SCHEME EDITOR -->
<?php
//NEW EXPORT SCHEME FORM
//CREATE NEW EXPORT SCHEME
if($act == 'SaveNewExportScheme') {
$fieldCount = count($_REQUEST['fieldName']);
$schemeName = $_REQUEST['schemeName'];
$customer = $_REQUEST['customer'];

$schemeID = createExportScheme($schemeName, $customer);

for($i = 0;$i < $fieldCount;$i++) {
if($_REQUEST['fieldName'][$i] != '') {
addFieldToExportScheme($schemeID, $_REQUEST['fieldName'][$i], $_REQUEST['conversionMethod'][$i], $_REQUEST['masterFieldID'][$i], $_REQUEST['fieldDescription'][$i],$_REQUEST['mergeScheme'][$i]);
}
}
echo 'Your new scheme has been successfully created.';
$act = 'exportSchemeEditor';
}
//NEW EXPORT SCHEME FORM
//ADD FIELDS TO NEW EXPORT SCHEME FORM
if($act == 'submitNewExportSchemeAddFields') {
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Export Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="SaveNewExportScheme">
<?php
if($_FILES['fieldNameFile']['error'] == UPLOAD_ERR_OK) {
if(isAllowedExtension($_FILES['fieldNameFile']['name'])) {
$filename = file_get_contents($_FILES['fieldNameFile']['tmp_name']);
$fieldRows = csv_headers_to_array($filename);
$MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$MTcount=mysql_num_rows($MTsql);
$masterFields = '';
for($MTi = 0;$MTi < $MTcount;$MTi++) {
mysql_data_seek($MTsql, $MTi);
$MTarr = mysql_fetch_array($MTsql);
$masterFields .= '<option value="'.$MTarr['id'].'">'.$MTarr['fieldName'].'</option>';
}
$CMsql = mysql_query("SELECT * FROM conversionSchemes ORDER BY conversionName ASC") or die(mysql_error());
$CMcount=mysql_num_rows($CMsql);
$conversionSchemes = '';
for($CMi = 0;$CMi < $CMcount;$CMi++) {
mysql_data_seek($CMsql, $CMi);
$CMarr = mysql_fetch_array($CMsql);
$conversionSchemes .= '<option value="'.$CMarr['id'].'">'.$CMarr['conversionName'].'</option>';
}
?>
<table class="table table-hover table-bordered table-striped"  width="100%">
<tr><td>
Field to Export</td>
<td>Master Field Association</td>
<td>Conversion Scheme</td>
<td>Merge Scheme</td>
<td>Description of Field</td>
</tr>
<?php
$usedFields = array();
foreach($fieldRows as $k => $v) {
if($v != '' && !in_array(strtolower($v), $usedFields) && getValue("SELECT COUNT(id) FROM exportFields WHERE exportSchemeID = ".quote_smart($_REQUEST['exportSchemeID'])." AND LOWER(fieldName) = ".quote_smart(strtolower($v))."") == 0) {
$usedFields[] = strtolower($v);
?>
<tr><td>
<input name="fieldName[]" value="<?php echo htmlspecialchars($v); ?>">  </td>
<td><select name="masterFieldID[]">
<option value="">Ignore Field</option>
<?php
$masterFields = '';
$MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$MTcount=mysql_num_rows($MTsql);
for($MTi = 0;$MTi < $MTcount;$MTi++) {
mysql_data_seek($MTsql, $MTi);
$MTarr = mysql_fetch_array($MTsql);
$masterFields .= '<option value="'.$MTarr['id'].'"';
if(strtolower($MTarr['fieldName']) == strtolower($v)) $masterFields .= ' selected="selected"';
$masterFields .= '>'.$MTarr['fieldName'].'</option>';
}
echo $masterFields; ?>
</select></td>
<td><select name="conversionMethod[]">
<option value="">Ignore Field</option>
<?php echo $conversionSchemes; ?>
</select></td>
<td><select name="mergeScheme[]">
<option value="">No Merge Scheme</option>
<?php
$CMsql = mysql_query("SELECT * FROM mergeSchemes ORDER BY schemeName ASC") or die(mysql_error());
$CMcount=mysql_num_rows($CMsql);
$mergeSchemes = '';
for($CMi = 0;$CMi < $CMcount;$CMi++) {
mysql_data_seek($CMsql, $CMi);
$CMarr = mysql_fetch_array($CMsql);
$mergeSchemes .= '<option value="'.$CMarr['id'].'"';
if($CMarr['id'] == $arr['mergeSchemeID']) $mergeSchemes .= ' selected="selected"';
$mergeSchemes .= '>'.$CMarr['schemeName'].'</option>';
}
echo $mergeSchemes; ?>
</select></td>
<td><input name="fieldDescription[]" value="<?php echo htmlspecialchars($v); ?>"></td>
</tr>
<?php
}
}
?></table><?php
}
}
?>
<input type="hidden" name="schemeName" value="<?php echo $_REQUEST['schemeName']; ?>">
<input type="hidden" name="customer" value="<?php if($_REQUEST['customer'] == '') echo $_REQUEST['customerName']; else echo $_REQUEST['customer']; ?>">
<br>
<br>
<input type="submit" name="submit" value="Save Export Scheme">
<br>
<br>
</form>
</td></tr>
</table>
<?php
}
//NEW EXPORT SCHEME FORM (name)
if($act == 'submitNewExportScheme') {
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Export Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="submitNewExportSchemeAddFields">
Choose a Comma Delimited CSV with 1 row and each field name on a column to mass export fields<br>
<input type="file" name="fieldNameFile"> 
<input type="hidden" name="schemeName" value="<?php echo $_REQUEST['schemeName']; ?>">
<input type="hidden" name="customer" value="<?php if($_REQUEST['customer'] == '') echo $_REQUEST['customerName']; else echo $_REQUEST['customer']; ?>">
<input type="submit" name="submit" value="Continue">
</form>
</td></tr>
</table>
<?php
}
//UPDATE EXPORT SCHEME
if($act == 'submitEditExportScheme') {
if($_REQUEST['Submit'] == 'Save as New') {
$fieldCount = count($_REQUEST['fieldName']);
if(getValue("SELECT COUNT(id) FROM exportSchemes WHERE schemeName = ".quote_smart($_REQUEST['schemeName'])."") > 0) {
echo 'A Scheme with this name already exists.<BR>';
$act = 'editExportScheme';
} else {
$schemeName = $_REQUEST['schemeName'];
$customer = $_REQUEST['customer'];
$schemeID = createExportScheme($schemeName, $customer);
for($i = 0;$i < $fieldCount;$i++) {
if($_REQUEST['fieldName'][$i] != '') {
addFieldToExportScheme($schemeID, $_REQUEST['fieldName'][$i], $_REQUEST['conversionMethod'][$i], $_REQUEST['masterFieldID'][$i], $_REQUEST['fieldDescription'][$i]);
}
}
echo 'Your new scheme has been successfully created.';
$act = 'exportSchemeEditor';
}
} else {
$schemeID = $_REQUEST['exportSchemeID'];
updateExportScheme($schemeID, $_REQUEST['schemeName'], $_REQUEST['customer']);
$count = count($_REQUEST['fieldName']);
$i = 0;
for($i = 0;$i < $count;$i++) {
if($_REQUEST['fieldID'][$i] != '') {
$sq = "UPDATE exportFields SET fieldName = ".quote_smart($_REQUEST['fieldName'][$i]).", conversionSchemeID = ".quote_smart($_REQUEST['conversionMethod'][$i]).", masterFieldID = ".quote_smart($_REQUEST['masterFieldID'][$i]).",mergeSchemeID = ".quote_smart($_REQUEST['mergeScheme'][$i]).", exportSchemeID = ".quote_smart($schemeID).", fieldDescription = ".quote_smart($_REQUEST['fieldDescription'][$i])." WHERE id = ".quote_smart($_REQUEST['fieldID'][$i])." LIMIT 1";
mysql_query($sq) or die(mysql_error());
} else {
addFieldToExportScheme($schemeID, $_REQUEST['fieldName'][$i], $_REQUEST['conversionMethod'][$i], $_REQUEST['masterFieldID'][$i], $_REQUEST['fieldDescription'][$i],$_REQUEST['mergeScheme'][$i]);
}
}
echo 'Your new scheme has been successfully updated.';
$act = 'editExportScheme';
}
}
//EDIT EXPORT SCHEME FORM
if($act == 'editExportScheme') {
$exportScheme = getArray("SELECT * FROM exportSchemes WHERE id = ".quote_smart($_REQUEST['exportSchemeID'])."");
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Export Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="submitEditExportScheme">
<input type="hidden" name="exportSchemeID" value="<?php echo $_REQUEST['exportSchemeID']; ?>">
Choose a Comma Delimited CSV with 1 row and each field name on a column to mass export fields<br>
<input type="file" name="fieldNameFile"> 
<?php
$MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$MTcount=mysql_num_rows($MTsql);
$masterFields = '';
for($MTi = 0;$MTi < $MTcount;$MTi++) {
mysql_data_seek($MTsql, $MTi);
$MTarr = mysql_fetch_array($MTsql);
$masterFields .= '<option value="'.$MTarr['id'].'">'.$MTarr['fieldName'].'</option>';
}
$CMsql = mysql_query("SELECT * FROM conversionSchemes ORDER BY conversionName ASC") or die(mysql_error());
$CMcount=mysql_num_rows($CMsql);
$conversionSchemes = '';
for($CMi = 0;$CMi < $CMcount;$CMi++) {
mysql_data_seek($CMsql, $CMi);
$CMarr = mysql_fetch_array($CMsql);
$conversionSchemes .= '<option value="'.$CMarr['id'].'">'.$CMarr['conversionName'].'</option>';
}
if($_FILES['fieldNameFile']['error'] == UPLOAD_ERR_OK) {
if(isAllowedExtension($_FILES['fieldNameFile']['name'])) {
$filename = file_get_contents($_FILES['fieldNameFile']['tmp_name']);
$fieldRows = csv_headers_to_array($filename);
?>
<table class="table table-hover table-bordered table-striped"  width="100%">
<tr><td>
New Field to Export</td>
<td>Master Field Association</td>
<td>Conversion Scheme</td>
<td>Merge  Scheme</td>
<td>Description of Field</td>
</tr>
<?php
$usedFields = array();
foreach($fieldRows as $k => $v) {
if($v != '' && !in_array(strtolower($v), $usedFields) && getValue("SELECT COUNT(id) FROM exportFields WHERE exportSchemeID = ".quote_smart($_REQUEST['exportSchemeID'])." AND LOWER(fieldName) = ".quote_smart(strtolower($v))."") == 0) {
$usedFields[] = strtolower($v);
?>
<tr><td>
<input type="hidden" name="fieldID[]" value="">
<input name="fieldName[]" value="<?php echo htmlspecialchars($v); ?>">  </td>
<td><select name="masterFieldID[]">
<option value="">Ignore Field</option>
<?php
$masterFields = '';
$MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$MTcount=mysql_num_rows($MTsql);
for($MTi = 0;$MTi < $MTcount;$MTi++) {
mysql_data_seek($MTsql, $MTi);
$MTarr = mysql_fetch_array($MTsql);
$masterFields .= '<option value="'.$MTarr['id'].'"';
if(strtolower($MTarr['fieldName']) == strtolower($v)) $masterFields .= ' selected="selected"';
$masterFields .= '>'.$MTarr['fieldName'].'</option>';
}
echo $masterFields; ?>
</select></td>
<td><select name="conversionMethod[]">
<option value="">No Conversion Scheme</option>
<?php echo $conversionSchemes; ?>
</select></td>
<td><select name="mergeScheme[]">
<option value="">No Merge Scheme</option>
<?php
$CMsql = mysql_query("SELECT * FROM mergeSchemes ORDER BY schemeName ASC") or die(mysql_error());
$CMcount=mysql_num_rows($CMsql);
$mergeSchemes = '';
for($CMi = 0;$CMi < $CMcount;$CMi++) {
mysql_data_seek($CMsql, $CMi);
$CMarr = mysql_fetch_array($CMsql);
$mergeSchemes .= '<option value="'.$CMarr['id'].'"';
if($CMarr['id'] == $arr['mergeSchemeID']) $mergeSchemes .= ' selected="selected"';
$mergeSchemes .= '>'.$CMarr['schemeName'].'</option>';
}
echo $mergeSchemes; ?>
</select></td>
<td><input name="fieldDescription[]" value="<?php echo htmlspecialchars($v); ?>"></td>
</tr>
<?php
}
}
?></table>
<?php
}
}
?>
<br>
Export Scheme Name: <input type="text" name="schemeName" value="<?php echo $exportScheme['schemeName']; ?>"><br>
Customer: <input type="text" name="customer" value="<?php echo $exportScheme['customer']; ?>"><br>
<table class="table table-hover table-bordered table-striped"  width="100%">
<tr><td>
Field to Export</td>
<td>Master Field Association</td>
<td>Conversion Scheme</td>
<td>Merge Scheme</td>
<td>Description of Field</td>
</tr>
<?php
$sql = mysql_query("SELECT * FROM exportFields WHERE exportSchemeID = ".quote_smart($_REQUEST['exportSchemeID'])."") or die(mysql_error());
$count = mysql_num_rows($sql);
for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
?>
<tr><td>
<input type="hidden" name="fieldID[]" value="<?php echo $arr['id']; ?>">
<input name="fieldName[]" value="<?php echo $arr['fieldName']; ?>">  </td>
<td><select name="masterFieldID[]">
<option value="">Ignore Field</option>
<?php
$MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$MTcount=mysql_num_rows($MTsql);
$masterFields = '';
for($MTi = 0;$MTi < $MTcount;$MTi++) {
mysql_data_seek($MTsql, $MTi);
$MTarr = mysql_fetch_array($MTsql);
$masterFields .= '<option value="'.$MTarr['id'].'"';
if($MTarr['id'] == $arr['masterFieldID']) $masterFields .= ' selected="selected"';
$masterFields .= '>'.$MTarr['fieldName'].'</option>';
}
echo $masterFields;
?>
</select></td>
<td><select name="conversionMethod[]">
<option value="">No Conversion Scheme</option>
<?php
$CMsql = mysql_query("SELECT * FROM conversionSchemes ORDER BY conversionName ASC") or die(mysql_error());
$CMcount=mysql_num_rows($CMsql);
$conversionSchemes = '';
for($CMi = 0;$CMi < $CMcount;$CMi++) {
mysql_data_seek($CMsql, $CMi);
$CMarr = mysql_fetch_array($CMsql);
$conversionSchemes .= '<option value="'.$CMarr['id'].'"';
if($CMarr['id'] == $arr['conversionSchemeID']) $conversionSchemes .= ' selected="selected"';
$conversionSchemes .= '>'.$CMarr['conversionName'].'</option>';
}
echo $conversionSchemes; ?>
</select></td>
<td><select name="mergeScheme[]">
<option value="">No Merge Scheme</option>
<?php
$CMsql = mysql_query("SELECT * FROM mergeSchemes ORDER BY schemeName ASC") or die(mysql_error());
$CMcount=mysql_num_rows($CMsql);
$mergeSchemes = '';
for($CMi = 0;$CMi < $CMcount;$CMi++) {
mysql_data_seek($CMsql, $CMi);
$CMarr = mysql_fetch_array($CMsql);
$mergeSchemes .= '<option value="'.$CMarr['id'].'"';
if($CMarr['id'] == $arr['mergeSchemeID']) $mergeSchemes .= ' selected="selected"';
$mergeSchemes .= '>'.$CMarr['schemeName'].'</option>';
}
echo $mergeSchemes; ?>
</select></td>
<td><input name="fieldDescription[]" value="<?php echo $arr['fieldDescription']; ?>"></td>
</tr>
<?php
}
?></table><input type="submit" name="Submit" value="Update Scheme">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="Submit" value="Save as New">
</form>
</td></tr>
</table>
<?php
}
//DEFAULT EXPORT SCHEME PAGE
if($act == 'exportSchemeEditor') {
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Export Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="submitNewExportScheme">
New Export Scheme Name (For Display) : <input type="text" name="schemeName" value="">
<br>
<br>
New Export Scheme Customer (For Tracking) : <select name="customer">
<option value="">Enter New Customer Name</option>
<?php 
$sql = mysql_query("SELECT DISTINCT customer FROM exportSchemes ORDER BY customer ASC") or die(mysql_error());
$count = mysql_num_rows($sql);
if($count > 0) {
for($i=0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
?><option value="<?php echo $arr['customer']; ?>"><?php echo $arr['customer']; ?></option><?php
}
}
?>
</select> 
<input type="text" name="customerName">
<br>
<br>
<input type="submit" name="submit" value="Continue">
</form>
</td></tr>
<tr><td>
<p><strong>Current Export Schemes</strong><br>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr><td><strong>Export Scheme</strong></td><td><strong>Customer</strong></td><td>Options</td></tr>
<?php
$sql = mysql_query("SELECT * FROM exportSchemes ORDER BY schemeName ASC") or die(mysql_error());
$count=mysql_num_rows($sql);
for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
echo '<tr><td>'.$arr['schemeName'].'</td><td>'.$arr['customer'].'</td><td>';
echo '<a href="importExportTool.php?act=editExportScheme&exportSchemeID='.$arr['id'].'">Edit Scheme</a></td></tr>';
}
?>
</table>
</p></td></tr>
</table>
<?php
}
?>
<!-- END EXPORT SCHEME EDITOR -->
<!-- START MASTER TABLE EDITOR -->
<?php
if($act == 'submitAddMasterFields') {
foreach($_REQUEST['fieldName'] as $k => $v) {
insertMasterField($v);
}
$act = 'masterTableEditor';
}
if($act == 'submitUploadMasterFields') {
if($_FILES['fieldNameFile']['error'] == UPLOAD_ERR_OK) {
if(isAllowedExtension($_FILES['fieldNameFile']['name'])) {
$filename = file_get_contents($_FILES['fieldNameFile']['tmp_name']);
if($_REQUEST['importType'] == 'byFirstColumn') {
$fieldRows = csv_headers_to_array($filename);
foreach($fieldRows as $k => $v) {
insertMasterField($v);
}
$fieldRows = csv_to_array($filename);
foreach($fieldRows as $k => $v) {
insertMasterField($v['Array'][0]);
}
} else if($_REQUEST['importType'] == 'byFirstRow') {
$fieldRows = csv_headers_to_array($filename);
foreach($fieldRows as $k => $v) {
insertMasterField($v);
}
}
}
}
$act = 'masterTableEditor';
}
if($act == 'masterTableEditor') {
?>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>Define Master Table Fields</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="submitUploadMasterFields">
Or choose a Comma Delimited CSV, for "By First Row", then you need to import a single row of header names, if by first column, then you need 1 header name on each new row's first column<br>
<input type="file" name="fieldNameFile"> <select name="importType"><option value="byFirstRow">By First Row, all columns</option><option value="byFirstColumn">By First Column, all rows</option> <input type="submit" name="submit" value="Import">
</form>
</td></tr>
<tr><td>
<p><strong>Current Fields in Master Table</strong><br>
<table class="table table-hover table-bordered table-striped"  width="100%" border="0" cellspacing="0" cellpadding="5">
<tr><td><strong>Field Name</strong></td></tr>
<?php
$sql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
$count=mysql_num_rows($sql);
for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
echo '<tr><td>'.$arr['fieldName'].'</td></tr>';
}
?>
</table>
</p></td></tr>
</table>
<?php
}
?>
<!-- END MASTER TABLE EDITOR -->
<p align="center">&nbsp;</p>
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</div>
</body>
</html>