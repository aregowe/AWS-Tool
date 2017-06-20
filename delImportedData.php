<?php
include("dbcon.php");
$sql = mysql_query("SELECT * FROM importedData ORDER BY id ASC LIMIT 10");
$count = mysql_num_rows($sql);
for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql,$i);
$arr = mysql_fetch_array($sql);
echo "<pre>".print_r($arr,1)."</pre>";
}
exit();
mysql_query("DELETE FROM importedData WHERE 1");
echo 'Done.';
?>