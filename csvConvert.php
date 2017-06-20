<?php
include("config.php");
include("functions.php");
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
pre.code { font-weight: bold; width: 800px; height: 300px; overflow: scroll; background-color: #EFEFEF; }
-->
</style>
<script type="text/javascript" language="javascript" src="jquery-1.4.2.min.js"></script>
<SCRIPT LANGUAGE="javascript" type="text/javascript">
<!--
jQuery( document ).ready(function() {
jQuery( 'pre.code' ).click( function() {
var refNode = $( this )[0];
if ( $.browser.msie ) {
var range = document.body.createTextRange();
range.moveToElementText( refNode );
range.select();
} else if ( $.browser.mozilla || $.browser.opera ) {
var selection = window.getSelection();
var range = document.createRange();
range.selectNodeContents( refNode );
selection.removeAllRanges();
selection.addRange( range );
} else if ( $.browser.safari ) {
var selection = window.getSelection();
selection.setBaseAndExtent( refNode, 0, refNode, 1 );
}
} );
} );
//-->
</SCRIPT>
</head>
<body>
<?php
include("header.php");
?>
<?php
$allowedExtensions = array("csv");
if($_REQUEST['action'] == 'submitForm') {
if($_FILES['csvFile']['error'] == UPLOAD_ERR_OK) {
if(isAllowedExtension($_FILES['csvFile']['name'])) {
$filename = file_get_contents($_FILES['csvFile']['tmp_name']);
$filename = split("\n", $filename);
$fieldNames = split(",",$filename[0]);
$sq .= "CREATE TABLE `".quote_smart($_REQUEST['tableName'])."` (
`id` INT( 25 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
";
$i = 0;
$total = count($fieldNames);
$placed = array();
foreach($fieldNames as $k => $v) {
$v = str_replace("\r", "", $v);
$v = str_replace("\n", "", $v);
$v = str_replace(" ", "_", $v);
$v = remSymbols($v);
if(trim(ltrim(rtrim($v))) != '' && !in_array(strtolower($v), $placed)) {
$sq .= "`".$v."` TEXT NOT NULL,\n";
$placed[] = strtolower($v);
}
$i++;
}
$sq = substr($sq, 0, strlen($sq)-strlen(",\n"));
$sq .= "
) ENGINE = MYISAM ;";
echo 'YOUR GENERATED QUERY IS BELOW: <BR><BR><pre class="code">'.$sq.'</pre><BR>';
}
}
}
?>
<form method="post" enctype="multipart/form-data" action="csvConvert.php">
CSV File with table field names: <input type="file" name="csvFile" /><br />
Table Name to Create: <input type="text" name="tableName" /><br />
<input type="submit" name="Submit" value="Go" />
<input type="hidden" name="action" value="submitForm" />
</form>
</body>
</html>