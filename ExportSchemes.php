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

<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td><strong>New Export Scheme</strong></td>
  </tr>
  <tr>
    <td>
    <form method="post" action="importExportTool.php" enctype="multipart/form-data">
      <input type="hidden" name="act" value="submitNewExportScheme">
        New Export Scheme Name (For Display) : <input type="text" name="schemeName" value="">
        <br>
        <br>
        New Export Scheme Customer (For Tracking) : 
      <select name="customer">
        <option value="">Enter New Customer Name</option>
        <?php
            $sql = mysql_query("SELECT DISTINCT customer FROM exportSchemes ORDER BY customer ASC") or die(mysql_error());
            $count = mysql_num_rows($sql);
            if ($count > 0)
              {
                for ($i = 0; $i < $count; $i++)
                  {
                    mysql_data_seek($sql, $i);
                    $arr = mysql_fetch_array($sql);
                      ?><option value="<?php
                    echo $arr['customer'];
                      ?>"><?php
                    echo $arr['customer'];
                      ?></option><?php
                  }
              }
        ?>
      </select>
      <input type="text" name="customerName">
      <br>
      <br>
      <input type="submit" name="submit" value="Continue">
      </form>
    </td>
  </tr>
  <tr>
      <td>
          <p><strong>Current Export Schemes</strong>
              <br>
              <table width="100%" border="0" cellspacing="0" cellpadding="5">
                  <tr>
                      <td><strong>Export Scheme</strong>
                      </td>
                      <td><strong>Customer</strong>
                      </td>
                      <td>Options</td>
                  </tr>
                    <?php
                        $sql = mysql_query("SELECT * FROM exportSchemes ORDER BY schemeName ASC") or die(mysql_error());
                        $count = mysql_num_rows($sql);
                        for ($i = 0; $i < $count; $i++)
                          {
                            mysql_data_seek($sql, $i);
                            $arr = mysql_fetch_array($sql);
                            echo '<tr><td>' . $arr['schemeName'] . '</td><td>' . $arr['customer'] . '</td><td>';
                            echo '<a href="importExportTool.php?act=editExportScheme&exportSchemeID=' . $arr['id'] . '">Edit Scheme</a></td></tr>';
                          }
                    ?>
              </table>
          </p>
      </td>
  </tr>
</table>
<!-- END EXPORT SCHEME EDITOR -->