<?php
set_time_limit(0);
session_start();
$thisFolder = str_replace("config.php", "", __FILE__);
include($thisFolder.'dbcon.php');
date_default_timezone_set('America/New_York');
$GLOBALS['echoDebug'] = TRUE;
?>