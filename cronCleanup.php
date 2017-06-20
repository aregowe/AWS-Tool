<?php
$itemsPerRun = 5000;
include("config.php");
include("functions.php");
include("functions.cronJobs.php");
mysql_query("UPDATE cronJobs SET jobActive = 'no' WHERE jobActive = 'yes' AND jobStarted < DATE_ADD(NOW(), INTERVAL -1 DAY)"); 
set_time_limit(0);
$rollingCronLogUpdates = TRUE;
$echoDebug = TRUE;
if($echoDebug) $debugOutput = TRUE;
/* JOB SETUP - NO NEED TO EDIT BEYOND THIS LINE */
$cronJobID = initCronJob('cronCleanup');
$query = "UPDATE itemQueue SET cronJob = '', `status` = 'pending' WHERE cronJobStarted < DATE_ADD(NOW(), INTERVAL -30 MINUTE) AND `status` = 'running'";
mysql_query($query);
logCron("Ran SQL update query \"".$query."\". affected ".mysql_affected_rows()." rows.");
$query = "UPDATE cronJobs SET jobActive = 'no' WHERE jobActive = 'yes' AND cronJobStarted < DATE_ADD(NOW(), INTERVAL -30 MINUTE)";
mysql_query($query);
logCron("Ran SQL update query \"".$query."\". affected ".mysql_affected_rows()." rows.");
$query = "OPTIMIZE TABLE `itemLookup`";
mysql_query($query);
logCron("Ran SQL update query \"".$query."\". affected ".mysql_affected_rows()." rows.");
$query = "DELETE FROM `itemQueue` WHERE itemAdded < DATE_ADD(NOW(), INTERVAL -1 WEEK);";
mysql_query($query);
logCron("Ran SQL update query \"".$query."\". affected ".mysql_affected_rows()." rows.");
$query = "DELETE FROM itemLookup WHERE sessionID NOT IN (SELECT DISTINCT sessionID FROM `itemQueue`);";
mysql_query($query);
logCron("Ran SQL update query \"".$query."\". affected ".mysql_affected_rows()." rows.");
endCronJob();
?>