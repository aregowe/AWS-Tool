<?php

//CRON JOB MANAGEMENT FUNCTIONS

function logCron($msg) {
	global $thisCommRun,$rollingCronLogUpdates,$cronJobID,$echoDebug, $ignoreCronLog, $logCommLogs;	
}

function logCronORI($msg) {
	global $thisCommRun,$rollingCronLogUpdates,$cronJobID,$echoDebug, $ignoreCronLog, $logCommLogs;	
	$msg = "\n\n".time().' ::  '.$msg.'';	
	//if($logCommLogs && $thisCommRun != '') insertCommLog($msg, $thisCommRun);	
	$query = "UPDATE cronJobs SET jobLog = CONCAT(jobLog, ".quote_smart($msg)."), memoryUsage = ".quote_smart(getMemoryUsage())." WHERE id = ".quote_smart($cronJobID)."";
	if($rollingCronLogUpdates === TRUE && $cronJobID != '') mysql_query($query) or die(mysql_error());	
	if($ignoreCronLog !== TRUE) $GLOBALS['cronLog'] .= $msg;	
	if($echoDebug) echo str_replace("\n","<BR>\n",$msg).'<BR>'."";
}

function initCronJob($jobName) {
	$thisCronJob = $jobName;
	$thisJobNumber = rand(0,getrandmax());
	$GLOBALS['thisJobNumber'] = $thisJobNumber;
	$GLOBALS['thisCronJob'] = $thisCronJob;
	$GLOBALS['jobName'] = $thisCronJob;
	$GLOBALS['scriptStart'] = time();
	$GLOBALS['startTimestamp'] = $GLOBALS['scriptStart'];
	$GLOBALS['cronLog'] = '';
	$GLOBALS['thisRun'] = 0;	
	mysql_query("INSERT INTO cronJobs (jobName, jobNumber, jobStarted, jobLog, jobActive) VALUES (".quote_smart($thisCronJob).", ".quote_smart($thisJobNumber).", NOW(), ".quote_smart("Job Started").", 'yes')");
	$cronJobID = mysql_insert_id();
	$GLOBALS['cronJobID'] = $cronJobID;
	return $cronJobID;
}

function setCronJobLoopNumber($loopNumber) {
	global $jobName;
	$query = mysql_query("UPDATE cronJobs SET jobLoopNumber = ".quote_smart($loopNumber)." WHERE jobName = ".quote_smart($jobName)." AND jobNumber = ".quote_smart($GLOBALS['cronJobID'])."");	
}

function isCronJobActive($reportInactive = TRUE) {
	global $thisCronJob, $cronJobID;	
	$activeJobs = getValue("SELECT COUNT(id) FROM cronJobs WHERE jobName = ".quote_smart($thisCronJob)." AND jobActive = 'yes'");
	$GLOBALS['activeJobs'] = $activeJobs;
	if($activeJobs > 1) { //GREATER THAN 1 BECAUSE WE ALREADY HAVE THIS ONE ACTIVE THAT WE ARE LOOKING AT
		return TRUE;
	} else {
		return FALSE;
	}
}

function endCronJob($message = '') {
	global $thisCronJob, $cronJobID, $activeJobs, $cronLog, $scriptStart, $queriesRun;
	$scriptEnd = time();	
	if($message != '') logCron($message);	
	logCron('<h1>Script Took '.($scriptEnd-$scriptStart).' second(s)</h1>');	
	logCron("Cron (".$thisCronJob.") Finished at ".date("M d Y g:i:as")."\nRan ".$queriesRun." queries.\n\n");	
	mysql_query("UPDATE cronJobs SET jobActive = 'no' WHERE id = ".quote_smart($cronJobID)."");
	exit();
}
?>