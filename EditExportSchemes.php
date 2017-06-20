<?php

  @ini_set('upload_max_size', '10000G');
  @ini_set('post_max_size', '10500G');
  @ini_set('memory_limit', '120000G');
  @ini_set('max_execution_time', '10000000');
  set_time_limit(10000000);
  include("config.php");
  include("functions.php");
  $act                          = $_REQUEST['act'];
  $allowedExtensions            = array(
      "csv"
);

$conversionMethodTypes        = array();
$conversionMethodTypes[]      = 'If equal to A, replace with B';
$conversionMethodTypes[]      = 'If contains A, replace A with B';
$conversionMethodTypes[]      = 'If contains A, replace all with B';
$conversionMethodTypes[]      = 'If contains A, add B on end';
$conversionMethodTypes[]      = 'If not equal to A, Replace with B';
$conversionMethodTypes[]      = 'If not containing A, Replace with B';
$conversionMethodTypes[]      = 'If not containing A, add B on end';
$conversionMethodTypes[]      = 'If A is numeric, use currency format';
$conversionMethodTypes[]      = 'Replace with A';
$conversionMethodParameters   = array();
$conversionMethodParameters[] = 'A';
$conversionMethodParameters[] = 'B';
/*
$conversionMethodParameters[] = 'C';
$conversionMethodParameters[] = 'D';
$conversionMethodParameters[] = 'E';
$conversionMethodParameters[] = 'F';*/
$newValueDelimiter            = '{|ND|}';
$keyValueDelimiter            = '{|KD|}';

?>
<html>
<head>
<title>Amazon API System Tools</title>
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
</head>
<body>
<?php
include("header.php");
?>
<p align="center">
  <a href="importExportTool.php?act=masterTableEditor">Master Table Fields</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="ExportSchemes.php?act=exportSchemeEditor">Export Schemes</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="importExportTool.php?act=importSchemeEditor">Import Schemes</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="importExportTool.php?act=conversionMethods">Conversion Schemes</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="importExportTool.php?act=mergeSchemes">Merge Schemes</a>
  <br>
  <br>
  <a href="importExportTool.php?act=importHistory">Import  History</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="importExportTool.php?act=newConversion">Run Conversion</a>
</p>

<?php
//EDIT EXPORT SCHEME FORM
if ($act == 'editExportScheme')
  {
    $exportScheme = getArray("SELECT * FROM exportSchemes WHERE id = " . quote_smart($_REQUEST['exportSchemeID']) . "");
  }
?>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
<tr>
<td><strong>New Export Scheme</strong></td>
</tr>
<tr><td>
<form method="post" action="importExportTool.php" enctype="multipart/form-data">
<input type="hidden" name="act" value="submitEditExportScheme">
<input type="hidden" name="exportSchemeID" value="<?php
    echo $_REQUEST['exportSchemeID'];
?>">
Choose a Comma Delimited CSV with 1 row and each field name on a column to mass export fields<br>
<input type="file" name="fieldNameFile"> 
<?php
    $MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
    $MTcount      = mysql_num_rows($MTsql);
    $masterFields = '';
    for ($MTi = 0; $MTi < $MTcount; $MTi++)
      {
        mysql_data_seek($MTsql, $MTi);
        $MTarr = mysql_fetch_array($MTsql);
        $masterFields .= '<option value="' . $MTarr['id'] . '">' . $MTarr['fieldName'] . '</option>';
      }
    $CMsql = mysql_query("SELECT * FROM conversionSchemes ORDER BY conversionName ASC") or die(mysql_error());
    $CMcount           = mysql_num_rows($CMsql);
    $conversionSchemes = '';
    for ($CMi = 0; $CMi < $CMcount; $CMi++)
      {
        mysql_data_seek($CMsql, $CMi);
        $CMarr = mysql_fetch_array($CMsql);
        $conversionSchemes .= '<option value="' . $CMarr['id'] . '">' . $CMarr['conversionName'] . '</option>';
      }
    if ($_FILES['fieldNameFile']['error'] == UPLOAD_ERR_OK)
      {
        if (isAllowedExtension($_FILES['fieldNameFile']['name']))
          {
            $filename  = file_get_contents($_FILES['fieldNameFile']['tmp_name']);
            $fieldRows = csv_headers_to_array($filename);
?>
<table width="100%">
<tr><td>
New Field to Export</td>
<td>Master Field Association</td>
<td>Conversion Scheme</td>
<td>Merge  Scheme</td>
<td>Description of Field</td>
</tr>
<?php
            $usedFields = array();
            foreach ($fieldRows as $k => $v)
              {
                if ($v != '' && !in_array(strtolower($v), $usedFields) && getValue("SELECT COUNT(id) FROM exportFields WHERE exportSchemeID = " . quote_smart($_REQUEST['exportSchemeID']) . " AND LOWER(fieldName) = " . quote_smart(strtolower($v)) . "") == 0)
                  {
                    $usedFields[] = strtolower($v);
?>
<tr><td>
<input type="hidden" name="fieldID[]" value="">
<input name="fieldName[]" value="<?php
                    echo htmlspecialchars($v);
?>">  </td>
<td><select name="masterFieldID[]">
<option value="">Ignore Field</option>
<?php
                    $masterFields = '';
                    $MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
                    $MTcount = mysql_num_rows($MTsql);
                    for ($MTi = 0; $MTi < $MTcount; $MTi++)
                      {
                        mysql_data_seek($MTsql, $MTi);
                        $MTarr = mysql_fetch_array($MTsql);
                        $masterFields .= '<option value="' . $MTarr['id'] . '"';
                        if (strtolower($MTarr['fieldName']) == strtolower($v))
                            $masterFields .= ' selected="selected"';
                        $masterFields .= '>' . $MTarr['fieldName'] . '</option>';
                      }
                    echo $masterFields;
?>
</select></td>
<td><select name="conversionMethod[]">
<option value="">No Conversion Scheme</option>
<?php
                    echo $conversionSchemes;
?>
</select></td>
<td><select name="mergeScheme[]">
<option value="">No Merge Scheme</option>
<?php
                    $CMsql = mysql_query("SELECT * FROM mergeSchemes ORDER BY schemeName ASC") or die(mysql_error());
                    $CMcount      = mysql_num_rows($CMsql);
                    $mergeSchemes = '';
                    for ($CMi = 0; $CMi < $CMcount; $CMi++)
                      {
                        mysql_data_seek($CMsql, $CMi);
                        $CMarr = mysql_fetch_array($CMsql);
                        $mergeSchemes .= '<option value="' . $CMarr['id'] . '"';
                        if ($CMarr['id'] == $arr['mergeSchemeID'])
                            $mergeSchemes .= ' selected="selected"';
                        $mergeSchemes .= '>' . $CMarr['schemeName'] . '</option>';
                      }
                    echo $mergeSchemes;
?>
</select></td>
<td><input name="fieldDescription[]" value="<?php
                    echo htmlspecialchars($v);
?>"></td>
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
Export Scheme Name: <input type="text" name="schemeName" value="<?php
    echo $exportScheme['schemeName'];
?>"><br>
Customer: <input type="text" name="customer" value="<?php
    echo $exportScheme['customer'];
?>"><br>
<table width="100%">
<tr><td>
Field to Export</td>
<td>Master Field Association</td>
<td>Conversion Scheme</td>
<td>Merge Scheme</td>
<td>Description of Field</td>
</tr>
<?php
    $sql = mysql_query("SELECT * FROM exportFields WHERE exportSchemeID = " . quote_smart($_REQUEST['exportSchemeID']) . "") or die(mysql_error());
    $count = mysql_num_rows($sql);
    for ($i = 0; $i < $count; $i++)
      {
        mysql_data_seek($sql, $i);
        $arr = mysql_fetch_array($sql);
?>
<tr><td>
<input type="hidden" name="fieldID[]" value="<?php
        echo $arr['id'];
?>">
<input name="fieldName[]" value="<?php
        echo $arr['fieldName'];
?>">  </td>
<td><select name="masterFieldID[]">
<option value="">Ignore Field</option>
<?php
        $MTsql = mysql_query("SELECT * FROM masterTableFields ORDER BY fieldName ASC") or die(mysql_error());
        $MTcount      = mysql_num_rows($MTsql);
        $masterFields = '';
        for ($MTi = 0; $MTi < $MTcount; $MTi++)
          {
            mysql_data_seek($MTsql, $MTi);
            $MTarr = mysql_fetch_array($MTsql);
            $masterFields .= '<option value="' . $MTarr['id'] . '"';
            if ($MTarr['id'] == $arr['masterFieldID'])
                $masterFields .= ' selected="selected"';
            $masterFields .= '>' . $MTarr['fieldName'] . '</option>';
          }
        echo $masterFields;
?>
</select></td>
<td><select name="conversionMethod[]">
<option value="">No Conversion Scheme</option>
<?php
        $CMsql = mysql_query("SELECT * FROM conversionSchemes ORDER BY conversionName ASC") or die(mysql_error());
        $CMcount           = mysql_num_rows($CMsql);
        $conversionSchemes = '';
        for ($CMi = 0; $CMi < $CMcount; $CMi++)
          {
            mysql_data_seek($CMsql, $CMi);
            $CMarr = mysql_fetch_array($CMsql);
            $conversionSchemes .= '<option value="' . $CMarr['id'] . '"';
            if ($CMarr['id'] == $arr['conversionSchemeID'])
                $conversionSchemes .= ' selected="selected"';
            $conversionSchemes .= '>' . $CMarr['conversionName'] . '</option>';
          }
        echo $conversionSchemes;
?>
</select></td>
<td><select name="mergeScheme[]">
<option value="">No Merge Scheme</option>
<?php
        $CMsql = mysql_query("SELECT * FROM mergeSchemes ORDER BY schemeName ASC") or die(mysql_error());
        $CMcount      = mysql_num_rows($CMsql);
        $mergeSchemes = '';
        for ($CMi = 0; $CMi < $CMcount; $CMi++)
          {
            mysql_data_seek($CMsql, $CMi);
            $CMarr = mysql_fetch_array($CMsql);
            $mergeSchemes .= '<option value="' . $CMarr['id'] . '"';
            if ($CMarr['id'] == $arr['mergeSchemeID'])
                $mergeSchemes .= ' selected="selected"';
            $mergeSchemes .= '>' . $CMarr['schemeName'] . '</option>';
          }
        echo $mergeSchemes;
?>
</select></td>
<td><input name="fieldDescription[]" value="<?php
        echo $arr['fieldDescription'];
?>"></td>
</tr>
<?php
      }
?></table><input type="submit" name="Submit" value="Update Scheme">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="Submit" value="Save as New">
</form>
</td></tr>
</table>