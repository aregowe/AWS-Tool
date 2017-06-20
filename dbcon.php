<?php
$dbconIncluded = 'yes';
$dbConn = mysql_connect('localhost', '', '');
if($dbConn === FALSE) exit("We are currently working to improve server performence and are under server maintanence. Please check back in 15 minutes.");
$dbSel = mysql_select_db('thedatam_extractor');
if($dbSel === FALSE) exit("We are currently working to improve server performence and are under server maintanence. Please check back in 15 minutes.");
?>