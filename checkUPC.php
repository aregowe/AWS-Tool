<?php
set_time_limit(0);
include("config.php");
include("functions.php");
$itemID=713947710405;
$searchIndex = 'All';
$accessKey = 'AKIAJS5PTXWNC755UBDQ';
$responseGroups = 'Images,Small,Medium,Large,Offers,OfferFull,OfferSummary,OfferListings,Variations,VariationImages,VariationSummary,ItemAttributes,ItemIds';
$extra = '&Operation=ItemLookup';
$itemIdType = 'UPC';
$extra .= '&IdType='.urlencode($itemIdType);
$extra .= '&ItemId='.$itemID;
$extra .= '&Id='.$itemID;
$extra .= '&SearchIndex='.$searchIndex;
$url = "http://ecs.amazonaws.com/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=".urlencode($accessKey).$extra."&ResponseGroup=".$responseGroups;
$secret = 'z2pvnX3q7OerpeyG2aDjEBtgFZos9m7jZOqquUML';
$host = parse_url($url,PHP_URL_HOST);
$timestamp = gmstrftime("%Y-%m-%dT%H:%M:%S.000Z");
$url=$url. "&Timestamp=" . $timestamp;
$paramstart = strpos($url,"?");
$workurl = substr($url,$paramstart+1);
$workurl = str_replace(",","%2C",$workurl);
$workurl = str_replace(":","%3A",$workurl);
$params = explode("&",$workurl);
sort($params);
$signstr = "GET\n" . $host . "\n/onca/xml\n" . implode("&",$params);
$signstr = base64_encode(hash_hmac('sha256', $signstr, $secret, true));
$signstr = urlencode($signstr);
$signedurl = $url . "&Signature=" . $signstr;
$request = $signedurl;
$xml_doc = simplexml_load_file($request);
echo '<pre>';
print_r(object2array($xml_doc),1);
echo '</pre>';
if($xml_doc->SellerListings->Request->Errors->Error->Message != '') {
echo '(UPC,'.$itemID.') - '.$xml_doc->SellerListings->Request->Errors->Error->Message.'<BR>';
} else {
if(is_object($xml_doc->Items)) {
echo '<pre>'.print_r(object2array($xml_doc),1).'</pre>';
/*$baseArray = object2array($xml_doc->Items);
foreach($baseArray['Item'] as $k => $v) {
$finalBase = array2string(flatten_array($v));
$sql = "INSERT INTO itemLookup (itemID, itemType, itemDetails, sessionID,admin) VALUES (
".quote_smart($itemID).", 'UPC', ".quote_smart($finalBase).", ".quote_smart($sessionID).",".quote_smart($admin)."
)";
echo $sql.'<hr>';
//mysql_query($sql) or die(mysql_error());
}
*/
/*if($baseArray != '') {
mysql_query("INSERT INTO itemLookup (itemID, itemType, itemDetails, sessionID,admin) VALUES (
".quote_smart($itemID).", 'UPC', ".quote_smart($baseArray).", ".quote_smart($sessionID).",".quote_smart($admin)."
)") or die(mysql_error());
} else {
//echo "<pre>".print_r($xml_doc,1)."</pre>";
}
unset($parent);
unset($baseArray); //FREE MEMORY
*/
} else {
echo "<pre>".print_r($xml_doc,1)."</pre>";
}
}
/* PART 2 */
/*$sql = mysql_query("SELECT * FROM itemLookup WHERE sessionID = ".quote_smart('1047768192')."");
$count = mysql_num_rows($sql);
echo '<h1>Found '.$count.' records.</h1>';

for($i = 0;$i < $count;$i++) {
mysql_data_seek($sql, $i);
$arr = mysql_fetch_array($sql);
$item = string2array($arr['itemDetails']);
echo '<hr><pre>'.print_r($item,1).'</pre><hr>';
}
*/
?>