<?php
include("config.php");
include("functions.php");

if($_REQUEST['username'] != '' && $_REQUEST['password'] != '') {
	if(getValue("SELECT COUNT(id) FROM admins WHERE username = ".quote_smart($_REQUEST['username'])." AND `password` = ".quote_smart($_REQUEST['password'])."") > 0) {
		$_SESSION['login'] = $_REQUEST['username'];
	} else {
		$invalidLogin = TRUE;
	}
}
if($_SESSION['login'] != '') {
	$divideArray = array();
	$divideArray[] = 'ItemAttributes_PackageDimensions_Height';
	$divideArray[] = 'ItemAttributes_PackageDimensions_Length';
	$divideArray[] = 'ItemAttributes_PackageDimensions_Weight';
	$divideArray[] = 'ItemAttributes_PackageDimensions_Width';
	
	if($_REQUEST['action'] == 'downloadErrorReport') {
		if($_REQUEST['sessionID'] == '') {
			exit("INVALID RESULT ID SET");
		} else {
			$sql = mysql_query("SELECT * FROM itemErrors WHERE sessionID = ".quote_smart($_REQUEST['sessionID'])."");
			$count = mysql_num_rows($sql);
			if($count == 0) {
				echo "Count = 0. No results pulled a valid response to record.";
			} else {
				$headers = array();
				$headerRow = 0;
				$header = '';
				$data = '';
				
				$headers['sessionID'] = 'sessionID';
				$headers['itemID'] = 'itemID';
				$headers['itemLog'] = 'errorLog';
				$headers['lastUpdated'] = 'processDate';
				
				
				foreach($headers as $key => $valueKey) $data .=  '"' . str_replace('"', '""',$valueKey) . '"' . ","; 
				
				$data .= "\n";
				
				for($i = 0;$i < $count;$i++) {
					mysql_data_seek($sql, $i);
					$arr = mysql_fetch_array($sql);
					
					foreach($headers as $key => $valueKey) $data .=  '"' . str_replace('"', '""',$arr[$key]) . '"' . ","; 
					
					$data .= "\n";
				}
				$data = str_replace("\r","",$data);
				$date = date("m-d-y");
				//exit('<pre>'.$data.'</pre>');
				header("Content-type: application/x-msdownload"); 
				header("Content-Disposition: attachment; filename=Amazon_Item_Errors_Report_".$date."_".time().".csv"); 
				header("Pragma: no-cache"); 
				header("Expires: 0"); 
				print "$data";
				exit;
			}
		}
	} else if($_REQUEST['action'] == 'download' && $_REQUEST['Submit']=='Download XLS') {
		if($_REQUEST['sessionID'] == '') {
			exit("INVALID RESULT ID SET");
		} else {
			$sql = mysql_query("SELECT * FROM itemLookup WHERE sessionID = ".quote_smart($_REQUEST['sessionID'])."");
			$count = mysql_num_rows($sql);
			if($count == 0) {
				echo "Count = 0. No results pulled a valid response to record.";
			} else {
				
				$headers = array();
				$headerRow = 0;
				$header = '';
				$data = '';
				for($i = 0;$i < $count;$i++) {
					mysql_data_seek($sql, $i);
					$arr = mysql_fetch_array($sql);
					$item = '';
					//echo "<pre>".print_r($arr,1)."</pre><BR>";
					$item = string2array($arr['itemDetails']);
					//echo 'New Item<BR>';
					foreach($item as $k=>$v) {
						//echo 'K['.$k.'] = '.$v.'<BR>';
						if(!in_array($k, $headers) && in_array($k,$_REQUEST['headers'])) {
							$headers[$k] = $k;
							$headerRow++;
							$header .= $k . "\t";
						}
					}
				}
				for($i = 0;$i < $count;$i++) {
					mysql_data_seek($sql, $i);
					$arr = mysql_fetch_array($sql);
					$item = string2array($arr['itemDetails']);
					$line = ''; 
					foreach($headers as $k=>$value) {
						if ((!isset($item[$k])) || ($item[$k] == "")) { 
							$item[$k] = "\t"; 
						} else {
							if(in_array($k, $divideArray)) $item[$k] = number_format(($item[$k]/100),2,".","");
							$item[$k] = str_replace('"', '""', $item[$k]); 
							$item[$k] = '"' . $item[$k] . '"' . "\t"; 
						} 
						$line .= $item[$k];
					} 
					$data .= trim($line)."\n";
				}
				$data = str_replace("\r","",$data);
				$date = date("m-d-y");
				header("Content-type: application/x-msdownload"); 
				header("Content-Disposition: attachment; filename=Amazon_API_Report_".$date."_".time().".xls"); 
				header("Pragma: no-cache"); 
				header("Expires: 0"); 
				print "$header\n$data";
				exit;

			}
		}
	} else if($_REQUEST['action'] == 'download' && $_REQUEST['Submit']=='Download CSV') {
		if($_REQUEST['sessionID'] == '') {
			exit("INVALID RESULT ID SET");
		} else {
			$sql = mysql_query("SELECT * FROM itemLookup WHERE sessionID = ".quote_smart($_REQUEST['sessionID'])."");
			$count = mysql_num_rows($sql);
			if($count == 0) {
				echo "Count = 0. No results pulled a valid response to record.";
			} else {
				$headers = array();
				$headerRow = 0;
				$header = '';
				$data = '';
				for($i = 0;$i < $count;$i++) {
					mysql_data_seek($sql, $i);
					$arr = mysql_fetch_array($sql);
					$item = '';
					//echo "<pre>".print_r($arr,1)."</pre><BR>";
					$item = string2array($arr['itemDetails']);
					//echo 'New Item<BR>';
					foreach($item as $k=>$v) {
						if(!in_array($k, $headers) && in_array($k,$_REQUEST['headers'])) {
							$headers[$k] = $k;
							$headerRow++;
							$header .= $k . ",";
							//echo 'Added header '.$k.'<BR>';
						}
					}
				}
				for($i = 0;$i < $count;$i++) {
					mysql_data_seek($sql, $i);
					$arr = mysql_fetch_array($sql);
					$item = string2array($arr['itemDetails']);
					$line = ''; 
					//echo 'Pulled item with '.count($item).' name key pairs.<BR>';
					foreach($headers as $k=>$value) {
						//echo 'At header['.$k.']';
						//echo 'Checking '.$item[$k].'<BR>';
						if ((!isset($item[$k])) || ($item[$k] == "")) { 
							$item[$k] = '" ",'; 
						} else { 
							if(in_array($k, $divideArray)) $item[$k] = number_format(($item[$k]/100),2,".","");
							$item[$k] = str_replace('"', '""', $item[$k]); 
							$item[$k] = '"' . $item[$k] . '"' . ","; 
						} 
						//echo 'Line .= '.$item[$k].'<BR>';
						$line .= $item[$k];
					} 
					$data .= trim($line)."\n";
				}
				$data = str_replace("\r","",$data);
				$date = date("m-d-y");
				header("Content-type: application/x-msdownload"); 
				header("Content-Disposition: attachment; filename=Amazon_API_Report_".$date."_".time().".csv"); 
				header("Pragma: no-cache"); 
				header("Expires: 0"); 
				print "$header\n$data";
				exit;

			}
		}
	} else if($_REQUEST['action'] == 'viewResults') {
		include("header.php");
		if($_REQUEST['sessionID'] == '') {
			exit("INVALID RESULT ID SET");
		} else {
			$sql = mysql_query("SELECT * FROM itemLookup WHERE sessionID = ".quote_smart($_REQUEST['sessionID'])."");
			$count = mysql_num_rows($sql);
			if($count == 0) {
				echo "Count = 0. No results pulled a valid response to record.";
			} else {
				
			}
		}
	} else {
		include("header.php");
		
		$queueCount = getValue("SELECT COUNT(id) FROM itemQueue WHERE sessionID = ".quote_smart($_REQUEST['sessionID'])." AND (status = 'pending' OR status = 'running')");
		$queueTotal = getValue("SELECT COUNT(id) FROM itemQueue WHERE (status = 'pending' OR status = 'running')");
		if($queueCount > 0) {
			?><script type="text/JavaScript" language="javascript">
				<!--
				setTimeout("location.reload(true);",5000);
				//   -->
				</script>
			<?php
			echo 'There are '.$queueTotal.' total items in queue\'s in general.<BR>';
			echo 'There are '.$queueCount.' items left in this queue.<BR><img src="/loading3.gif" border="0" align="absmiddle" /> Results are in queue and processing.<BR /><BR />An email will be sent when the job is complete, otherwise you can wait here to see the results.<BR><a href="index.php">Back Home</a><BR><br />
<br />
NOTE: Cron jobs run once every 5 minutes and process approximately 30 ASINs every minute. Please ensure to upload as many ASINs as possible, in a single file, to ensure maximum efficiency.';

			echo '<BR><BR>';
			$itemErrors = getValue("SELECT COUNT(id) FROM itemErrors WHERE sessionID = ".quote_smart($_REQUEST['sessionID'])."");
			if($itemErrors > 0) {
				echo '<font color="red">'.$itemErrors.' error(s) occurred on items submitted in this report.</font> &nbsp;&nbsp; - &nbsp;&nbsp; <a href="http://thedatamaxx.com/showResults.php?sessionID='.urlencode($_REQUEST['sessionID']).'&action=downloadErrorReport" target="_blank">Download Error Report</a>';
			} else {
				echo '<font color="green">No item error(s) occurred.</font>';
			}
			echo '<BR><BR>';
			
		} else {
			//if($_SESSION['errorLog'] != '') {
				//echo '<FONT COLOR=RED><STRONG>One or more errors have occurred.</STRONG></FONT> - The details are listed at the bottom of this page if any were returned.';
			//}
			?><a href="index.php">Back Home</a><br>
			<SCRIPT LANGUAGE="JavaScript">
				<!--
				function checkAll(field) {
					for (i = 0; i < field.length; i++) field[i].checked = true ;
				}
				
				function uncheckAll(field) {
					for (i = 0; i < field.length; i++) field[i].checked = false ;
				}
				-->
				</script>
			<BR><BR>Quick Link: http://thedatamaxx.com/showResults.php?sessionID=<?php echo urlencode($_REQUEST['sessionID']); ?>&amp;username=<?php echo $_SESSION['login']; ?>&amp;password=<?php echo getValue("SELECT `password` FROM admins WHERE username = ".quote_smart($_SESSION['login']).""); ?><BR>

			<p><strong>NOTE:</strong> Cron jobs run <i>once</i> every 5 minutes and process <i>approximately</i> 30 ASINs every minute. Please upload as many ASINs as possible, in a single file, to ensure maximum efficiency.</p>
            
            <?php
			
			$itemErrors = getValue("SELECT COUNT(id) FROM itemErrors WHERE sessionID = ".quote_smart($_REQUEST['sessionID'])."");
			if($itemErrors > 0) {
				echo '<font color="red">'.$itemErrors.' error(s) occurred on items submitted in this report.</font> &nbsp;&nbsp; - &nbsp;&nbsp; <a href="http://thedatamaxx.com/showResults.php?sessionID='.urlencode($_REQUEST['sessionID']).'&action=downloadErrorReport" target="_blank">Download Error Report</a>';
			} else {
				echo '<font color="green">No item error(s) occurred.</font>';
			}
			
			?>
            
            <BR /><BR />
            <form name="download" id="download" action="showResults.php" method="post">
            <br />
            <input type="button" name="CheckAll" value="Check All" onClick="checkAll(document.download['headers[]'])">&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" name="UnCheckAll" value="Uncheck All" onClick="uncheckAll(document.download['headers[]'])">&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="submit" name="Submit" value="Download CSV">&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="submit" name="Submit" value="Download XLS"><br />
			<hr />
			<?php
			$sql = mysql_query("SELECT * FROM itemLookup WHERE sessionID = ".quote_smart($_REQUEST['sessionID'])."") or die(mysql_error().' on line '.__LINE__);
			$count = mysql_num_rows($sql);
			if($count == 0) {
				echo "Count = 0. No results pulled a valid response to record.";
			} else {
				$headers = array();
				$headerRow = 0;
				$header = '';
				$data = '';
				for($i = 0;$i < $count;$i++) {
					mysql_data_seek($sql, $i);
					$arr = mysql_fetch_array($sql);
					$item = '';
					//echo "<pre>".print_r($arr,1)."</pre><BR>";
					$item = string2array($arr['itemDetails']);
					//echo 'New Item<BR>';
					$checked = array();
					$checked[] = 'ASIN';
					$checked[] = 'ParentASIN';
					$checked[] = 'ItemAttributes_Title';
					$checked[] = 'ItemAttributes_UPC';
					
					foreach($item as $k=>$v) {
						if(!in_array($k, $headers)) {
							$headers[$k] = $k;
							echo '<input type="checkbox" name="headers[]" id="headers['.$k.']" value="'.$k.'"';
							if(in_array($k, $checked)) echo ' checked="checked"';
							echo ' > <label for="headers['.$k.']">'.$k.'</label><BR>';
							$headerRow++;
							//$header .= $k . ",";
							//echo 'Added header '.$k.'<BR>';
						}
					}
				}
			}
			?><input type="hidden" name="action" value="download">
			
			<hr>
			<input type="hidden" name="sessionID" value="<?php echo $_REQUEST['sessionID'];?>">
			<input type="submit" name="Submit" value="Download CSV">&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="submit" name="Submit" value="Download XLS">
			</form>
			<hr><BR>
			<BR><BR>Quick Link: http://thedatamaxx.com/showResults.php?sessionID=<?php echo urlencode($_REQUEST['sessionID']); ?>&amp;username=<?php echo $_SESSION['login']; ?>&amp;password=<?php echo getValue("SELECT `password` FROM admins WHERE username = ".quote_smart($_SESSION['login']).""); ?><BR><BR><a href="index.php">Back Home</a><BR>
			<?php
			//if($_SESSION['errorLog'] != '') {
				//echo '<FONT COLOR=RED><STRONG>One or more errors have occurred. The details are listed below if any were returned.</STRONG></FONT><BR>'.$_SESSION['errorLog'];
			//}
		}
	}
} else exit("Invalid login ....");
?>
            <p><span class="description">Server Memory Usage:</span> <span class="result"><?php echo get_server_memory_usage() ?>%</span><BR /><span class="description">Server CPU Usage: </span> <span class="result"><?php echo get_server_cpu_usage() ?>%</span></p><?php
include("footer.php");
?>