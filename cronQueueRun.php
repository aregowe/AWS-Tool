<?php

//if($_REQUEST['p'] != 'inc') exit();

$itemsPerRun = 5000;

include("config.php");

include("functions.php");

include("functions.cronJobs.php");

mysql_query("UPDATE cronJobs SET jobActive = 'no' WHERE jobActive = 'yes' AND jobStarted < DATE_ADD(NOW(), INTERVAL -1 HOUR)");

set_time_limit(0);

$rollingCronLogUpdates = TRUE;

$echoDebug = TRUE;

if($echoDebug) $debugOutput = TRUE;

$query = "UPDATE itemQueue SET cronJob = '', `status` = 'pending' WHERE cronJobStarted < DATE_ADD(NOW(), INTERVAL -30 MINUTE) AND `status` = 'running'";

mysql_query($query);

$query = "UPDATE cronJobs SET jobActive = 'no' WHERE jobActive = 'yes' AND cronJobStarted < DATE_ADD(NOW(), INTERVAL -30 MINUTE)";

mysql_query($query);

/* JOB SETUP - NO NEED TO EDIT BEYOND THIS LINE */

$cronJobID = initCronJob('cronQueueRun');

logCron("Cron job start after init.");

/* IF JOB IS ACTIVE, EXIT THIS JOB */

if(isCronJobActive()) endCronJob();

$thisCronJob = rand(1,getrandmax());

logCron("Ran SQL update query \"".$query."\". affected ".mysql_affected_rows()." rows.");

$queueCount = getValue("SELECT COUNT(id) FROM itemQueue WHERE status = 'pending' AND cronJob = ''");

if($queueCount > 0) {

	$sql = mysql_query("SELECT * FROM itemQueue WHERE status = 'pending' AND cronJob = '' LIMIT 0,".$itemsPerRun."");

	logCron("Ran SQL update query \""."SELECT * FROM itemQueue WHERE status = 'pending' AND cronJob = '' LIMIT 0,".$itemsPerRun.""."\". found ".mysql_num_rows($sql)." rows.");

	$count = mysql_num_rows($sql);

	$jobSessionIDs = array();

	$lastSessionID = '';

	for($i = 0;$i < $count;$i++) {

		mysql_data_seek($sql, $i);

		$arr = mysql_fetch_array($sql);

		logCron('SKU PULLED: "'.($arr['skuID']).'"');

		$arr['skuID'] = utf8_decode($arr['skuID']);

		$skuID = str_replace('&nbsp;', '', $arr['skuID']);

		//logCron('('.__LINE__.') SKU NOW: "'.($skuID).'"');

		$skuID = removeWordCharacters($skuID);

		//logCron('('.__LINE__.') SKU NOW: "'.($skuID).'"');

		$skuID = str_replace('?', '', $skuID);

		//logCron('('.__LINE__.') SKU NOW: "'.($skuID).'"');

		$skuID = str_replace('Â', '', $skuID);

		//logCron('('.__LINE__.') SKU NOW: "'.($skuID).'"');

		$skuID = str_replace(' ', '', $skuID);

		//logCron('('.__LINE__.') SKU NOW: "'.($skuID).'"');

		$skuID = str_replace("\t", '', $skuID);

		//logCron('('.__LINE__.') SKU NOW: "'.($skuID).'"');

		$skuID = rtrim($skuID);

		//logCron('('.__LINE__.') SKU NOW: "'.($skuID).'"');

		$skuID = ltrim($skuID);

		//logCron('('.__LINE__.') SKU NOW: "'.($skuID).'"');

		$skuID = trim($skuID);

		//logCron('('.__LINE__.') SKU NOW: "'.($skuID).'"');

		//logCron('SKU RE JIGGED TO : "'.$skuID.'"');

		logCron("skuID now = \"".($skuID)."\"");

		mysql_query("UPDATE itemQueue SET status = 'running', cronJob = ".quote_smart($cronJobID)." WHERE id = ".quote_smart($arr['id'])."");

		logCron("UPDATE itemQueue SET status = 'running', cronJob = ".quote_smart($cronJobID)." WHERE id = ".quote_smart($arr['id'])."");

		setCronJobLoopNumber($i);

		$versionNumber = '2';

		if($lastSessionID != $arr['sessionID']) {

			$jobSessionIDs[] = $arr['sessionID'];

			$lastSessionID = $arr['sessionID'];
		}

		if($arr['skuType'] == 'ASIN') {

			if($versionNumber == '2') {

				itemLog("lookupByASIN_V2(".$skuID.",".$arr['sessionID'].",".$arr['admin'].");", $arr['sessionID'], $skuID);

				$result = lookupByASIN_V2($skuID,$arr['sessionID'],$arr['admin']);

				logCron("lookupByASIN_V2(".$skuID.",".$arr['sessionID'].",".$arr['admin'].") = ".print_r($result,true)."");

			} else {

				itemLog("lookupByASIN(".$skuID.",".$arr['sessionID'].",".$arr['admin'].");", $arr['sessionID'], $skuID);

				$result = lookupByASIN($skuID,$arr['sessionID'],$arr['admin']);

				logCron("lookupByASIN(".$skuID.",".$arr['sessionID'].",".$arr['admin'].") = ".print_r($result,true)."");

			}
		} else if($arr['skuType'] == 'EAN') {

			$result = lookupByEAN($skuID,$arr['sessionID'],$arr['admin']);

		} else if($arr['skuType'] == 'UPC') {

				//lookupByUPC($skuID,$arr['sessionID'],$arr['admin']);

			if($versionNumber == '2') {

				itemLog("lookupByUPC_V2(".$skuID.",".$arr['sessionID'].",".$arr['admin'].");", $arr['sessionID'], $skuID);

				logCron("About to run : lookupByUPC_V2(".$skuID.",".$arr['sessionID'].",".$arr['admin'].")");

				$result = lookupByUPC_V2($skuID,$arr['sessionID'],$arr['admin']);

				logCron("lookupByUPC_V2(".$skuID.",".$arr['sessionID'].",".$arr['admin'].") = ".print_r($result,true)."");

			} else {

				$result = lookupByUPC($skuID,$arr['sessionID'],$arr['admin']);
				
			}
		} else if($arr['skuType'] == 'SKU') {

			itemLog("lookupBySku(".$skuID.",".$arr['sessionID'].",".$arr['admin'].");", $arr['sessionID'], $skuID);

			$result = lookupBySku($skuID,$arr['merchantID'],$arr['sessionID'],$arr['admin']);

			logCron("lookupBySku(".$skuID.",".$arr['sessionID'].",".$arr['admin'].") = ".print_r($result,true)."");

		}
		if($result === FALSE) {

			logCron("UPDATE itemQueue SET status = 'failed',cronJob = '' WHERE id = ".quote_smart($arr['id'])."");

			mysql_query("UPDATE itemQueue SET status = 'failed',cronJob = '' WHERE id = ".quote_smart($arr['id'])."");

			logCron("Updated... Sleeping for 1 second.");

		} else {

			logCron("UPDATE itemQueue SET status = 'completed',cronJob = '' WHERE id = ".quote_smart($arr['id'])."");

			mysql_query("UPDATE itemQueue SET status = 'completed' WHERE id = ".quote_smart($arr['id'])."");

			logCron("Updated... Sleeping for 1 second.");
		}
		sleep(1);
	}

	if(count($jobSessionIDs) > 0) {

		foreach($jobSessionIDs as $k => $v) {
			if(getValue("SELECT COUNT(id) FROM itemQueue WHERE sessionID = ".quote_smart($v)." AND (status = 'pending' OR status='running')") == 0) {

				$message = 'A new job has just completed it\'s run in queue.';

				$message .= "\n\nThe url to access this is : \n\n";

				$admin = getArray("SELECT * FROM admins WHERE username IN (SELECT admin FROM itemQueue WHERE sessionID = ".quote_smart($v)." LIMIT 1) LIMIT 1");

				$message .= "http://thedatamaxx.com/showResults.php?sessionID=".urlencode($v)."&username=".$admin['username']."&password=".$admin['password'];

				$to = $admin['emailAddress'];

				$subject = 'Amazon Queue Run Finished a Job';

				mail($to, $subject, $message);
			}
		}
	}
}
endCronJob();
?>