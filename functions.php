<?php

function itemError($message, $sessionID, $itemID) {
	
	$message = "\n\n".time()." (".date("M d Y g:i:sa").") \n".$message;
	if(getValue("SELECT COUNT(id) FROM itemErrors WHERE itemID = ".quote_smart($itemID)." AND sessionID = ".quote_smart($sessionID)."") > 0) {
		//UPDATE
		mysql_query("UPDATE itemErrors SET itemLog = CONCAT(itemLog, ".quote_smart($message)."), lastUpdated = NOW() WHERE itemID = ".quote_smart($itemID)." AND sessionID = ".quote_smart($sessionID)."");
	} else {
		//INSERT
		mysql_query("INSERT INTO itemErrors (itemID, sessionID, itemLog, lastUpdated) VALUES (".quote_smart($itemID).", ".quote_smart($sessionID).", ".quote_smart($message).", NOW())"); 
	}
}

function itemLog($message, $sessionID, $itemID) {

}

function itemLogORI($message, $sessionID, $itemID) {
	
	$message = "\n\n".time()." (".date("M d Y g:i:sa").") \n".$message;
	if(getValue("SELECT COUNT(id) FROM itemLog WHERE itemID = ".quote_smart($itemID)." AND sessionID = ".quote_smart($sessionID)."") > 0) {
		//UPDATE
		mysql_query("UPDATE itemLog SET itemLog = CONCAT(itemLog, ".quote_smart($message)."), lastUpdated = NOW() WHERE itemID = ".quote_smart($itemID)." AND sessionID = ".quote_smart($sessionID)."");
	} else {
		//INSERT
		mysql_query("INSERT INTO itemLog (itemID, sessionID, itemLog, lastUpdated) VALUES (".quote_smart($itemID).", ".quote_smart($sessionID).", ".quote_smart($message).", NOW())"); 
	}
}

function getMemoryUsage() {
	$size = memory_get_usage();
	$unit=array('b','kb','mb','gb','tb','pb');
	return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

function echo_memory_usage() {
	$mem_usage = memory_get_usage(true);
   
	if ($mem_usage < 1024)
		echo $mem_usage." bytes";
	elseif ($mem_usage < 1048576)
		echo round($mem_usage/1024,2)." kilobytes";
	else
		echo round($mem_usage/1048576,2)." megabytes";
	   
	echo "<br/>";
}


function get_server_cpu_usage(){
	$load = sys_getloadavg();
	return number_format($load[0],2,".",",");
}

function get_server_memory_usage(){
 
	$free = shell_exec('free');
	$free = (string)trim($free);
	$free_arr = explode("\n", $free);
	$mem = explode(" ", $free_arr[1]);
	$mem = array_filter($mem);
	$mem = array_merge($mem);
	$memory_usage = $mem[2]/$mem[1]*100;
 
	return number_format($memory_usage,2,".",",");
}

function getXMLByASIN_V2($asin, $sessionID = '') {
	global $debugOutput;
	$itemID=$asin;
	$itemID=str_replace(' ', '',str_replace("&nbsp;","",$itemID));
	itemLog("getXMLByASIN_V2 :: init(".$itemID.", ".$sessionID.");", $sessionID, $itemID);
	
	$searchIndex = 'All';
	$accessKey = '';
	$responseGroups = 'SellerListing';
	
	$responseGroups = 'Medium,Offers';
	$extra = '&AssociateTag='.urlencode('ecatalogdevel-20');
	$extra .= '&Operation=ItemLookup';
$rgroups = "Help
ListMinimum
VariationSummary
VariationMatrix
TransactionDetails
VariationImages
PartBrandBinsSummary
CustomerFull
CartNewReleases
ItemIds
SalesRank
Fitments
Medium
PartBrowseNodeBinsSummary
TopSellers
Request
HasPartCompatibility
ListFull
Small
Seller
OfferFull
Accessories
VehicleMakes
TaggedItems
VehicleParts
BrowseNodeInfo
ItemAttributes
PromotionalTag
VehicleOptions
ListItems
Offers
TaggedGuides
NewReleases
VehiclePartFit
OfferSummary
VariationOffers
CartSimilarities
Reviews
ShippingCharges
ShippingOptions
EditorialReview
CustomerInfo
PromotionSummary
BrowseNodes
PartnerTransactionDetails
VehicleYears
SearchBins
VehicleTrims
Similarities
AlternateVersions
SearchInside
CustomerReviews
SellerListing
OfferListings
Cart
TaggedListmaniaLists
VehicleModels
ListInfo
Large
CustomerLists
Tracks
CartTopSellers
Images
Variations
RelatedItems
Collections";

	$rgroups = explode("\n", $rgroups);
	$responseGroups = '';
	foreach($rgroups as $k => $v) {
		$responseGroups .= trim(ltrim(rtrim($v))).',';
	}
	$responseGroups = substr($responseGroups, 0, strlen($responseGroups) - 1);
	$groups = 'Request,ItemIds,Small,Medium,Large,Offers,OfferFull,OfferSummary,OfferListings,PromotionSummary,PromotionDetails,Variations,VariationImages,VariationMinimum,VariationSummary,TagsSummary,Tags,VariationMatrix,VariationOffers,ItemAttributes,MerchantItemAttributes,Tracks,Accessories,EditorialReview,SalesRank,BrowseNodes,Images,Similarities,Subjects,Reviews,ListmaniaLists,SearchInside,PromotionalTag,AlternateVersions,Collections,ShippingCharges,RelatedItems,ShippingOptions';
	$responseGroups = 'Images,Medium,Large,ListItems,ItemAttributes,ListFull,VariationImages,ItemIds';
	
	
	
	$responseGroups = 'Images,Medium,Large,Small,ItemAttributes,VariationImages,ItemIds,VariationSummary,SalesRank,Reviews,BrowseNodes';
	
	$responseGroups = 'Images,Medium,Large,Small,ItemAttributes,ItemIds,SalesRank,Reviews,BrowseNodes';
	$responseGroups = 'Images,Medium,Large,Small,ItemAttributes,ItemIds,SalesRank,Reviews,BrowseNodes';
	
	$itemIdType = 'ASIN';
	
	$extra .= '&IdType='.urlencode($itemIdType);
	$extra .= '&ItemId='.str_replace(' ', '',str_replace("&nbsp;", "",$itemID));
	$url = "http://webservices.amazon.com/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=".urlencode($accessKey).$extra."&ResponseGroup=".urlencode($responseGroups);
	
	//itemLog("getXMLByASIN_V2 :: built url: ".$url."", $sessionID, $itemID);
	
	$secret = '';
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

	if($debugOutput == TRUE) echo time().' :: getXMLByASIN_V2 :: built signed url: '.$signedurl.'<BR>';
	itemLog("getXMLByASIN_V2 :: built signed url: ".$signedurl."", $sessionID, $itemID);
	
	$xml_doc = simplexml_load_file($request);
	if($debugOutput == TRUE) echo time().' :: getXMLByASIN_V2 :: XML_DOC returned and loaded.</pre><BR>';
	return $xml_doc;
	
}

function getXMLByUPC_V2($upc,$sessionID='') {
	global $debugOutput;
	$itemID=$upc;
	
	$searchIndex = 'All';
	$accessKey = '';
	$responseGroups = 'SellerListing';
	
	$responseGroups = 'Medium,Offers';
	$extra = '&AssociateTag='.urlencode('ecatalogdevel-20');
	$extra .= '&Operation=ItemLookup';
	$responseGroups = 'Images,Medium,Large,Small,ItemAttributes,VariationImages,ItemIds,VariationSummary,SalesRank,Reviews,BrowseNodes';
	$itemIdType = 'UPC';
	
	$extra .= '&IdType='.urlencode($itemIdType);
	$extra .= '&ItemId='.str_replace(" ", "",$itemID);
	$url = "http://webservices.amazon.com/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=".urlencode($accessKey).$extra."&ResponseGroup=".urlencode($responseGroups)."&SearchIndex=".urlencode($searchIndex);

	$secret = '';
	
	if($debugOutput == TRUE) echo time().' :: getXMLByUPC_V2 :: URL To Parse: '.$url.'<BR>';
	
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
	
	if($debugOutput == TRUE) echo time().' :: getXMLByUPC_V2 :: Return Data <BR>';
	
	itemLog("getXMLByUPC_V2 :: built signed url: ".$signedurl."", $sessionID, $itemID);
	
	$xml_doc = simplexml_load_file($request);
	if($debugOutput == TRUE) echo time().' :: getXMLByUPC_V2 :: XML_DOC returned<BR>';
	
	return $xml_doc;
	
}
function lookupByASIN_V2($asin, $sessionID,$admin='') {
	global $debugOutput;
	
	$itemID=$asin;
	
	itemLog("lookupByASIN_V2 :: calling getXMLByASIN_V2(".$itemID.");", $sessionID, $itemID);
	
	if($debugOutput == TRUE) echo time().' :: lookupByASIN_V2 :: calling getXMLByASIN_V2('.$itemID.');<BR>';
	
	$xml_doc = getXMLByASIN_V2($itemID, $sessionID);//simplexml_load_file($request);

	if($debugOutput == TRUE) echo time().' :: lookupByASIN_V2 :: Array Returned <BR>';
	
	itemLog("lookupByASIN_V2 :: Object Returned: <pre>".htmlspecialchars(print_r($xml_doc,1))."</pre>", $sessionID, $itemID);
	
	itemLog("lookupByASIN_V2 :: Array Returned: <pre>".htmlspecialchars(print_r(flatten_array(object2array($xml_doc)),1))."</pre>", $sessionID, $itemID);
	
	if($xml_doc->Items->Request->Errors->Error->Message != '') {
		$_SESSION['errorLog'] .= '(ASIN,'.$itemID.') - '.$xml_doc->Items->Request->Errors->Error->Message.'<BR>';
		itemLog('lookupByASIN_V2 :: (ASIN,'.$itemID.') - '.$xml_doc->Items->Request->Errors->Error->Message.'', $sessionID, $itemID);
		itemError($xml_doc->Items->Request->Errors->Error->Message, $sessionID, $itemID);
		return TRUE;
	} else if($xml_doc->Error->Message != '') {
		$_SESSION['errorLog'] .= '(ASIN,'.$itemID.') - '.$xml_doc->Error->Message.'<BR>';
		itemLog('lookupByASIN_V2 :: (ASIN,'.$itemID.') - '.$xml_doc->Error->Message.'', $sessionID, $itemID);
		$sql = "UPDATE itemQueue SET status = 'pending', cronJob = '' WHERE skuID = ".quote_smart($itemID)." AND sessionID = ".quote_smart($sessionID)."";
		mysql_query($sql);
		itemLog('lookupByASIN_V2 :: (ASIN,'.$itemID.') - ran reset query "'.$sql.'"', $sessionID, $itemID);
		itemLog('lookupByASIN_V2 :: (ASIN,'.$itemID.') - reset status to pending', $sessionID, $itemID);
		return FALSE;
	} else {
		if(is_object($xml_doc->Items)) {
			//exit("<pre>".print_r($xml_doc,1)."</pre>"."<HR><pre>".print_r($baseArray,1)."</pre>");
			
			$baseArray=flatten_array(object2array($xml_doc->Items->Item));
		
			itemLog('lookupByASIN_V2 :: $baseArray returned count ('.count($baseArray).')', $sessionID, $itemID);
			
			if(count($baseArray) > 0) {
				
				if($debugOutput == TRUE) echo time().' :: lookupByASIN_V2 :: Fetching detail page content<BR>';
				
				$data = get_url_contents($xml_doc->Items->Item->DetailPageURL);
				
				if($debugOutput == TRUE) echo time().' :: lookupByASIN_V2 :: Getting asin parent<BR>';
				$parent = getAsinParent($data);
				
				if($debugOutput == TRUE) echo time().' :: lookupByASIN_V2 :: Getting hi res image from url<BR>';
				$highResImage = getHiResImageURL($data);
				
				if($highResImage != '') {
					$baseArray = array('HiResImageURL' => $highResImage)+$baseArray;
				}
				if($parent != $itemID) { 
				
					if($debugOutput == TRUE) echo time().' :: lookupByASIN_V2 :: Looking up parent asin ('.$parent.')<BR>';
					//echo $parent.' != '.$data.', this is a child,get parent info for '.$parent.'<BR>';
					$baseArray = array('ParentASIN' => $parent)+$baseArray;
					if($parent != '') lookupByASIN_V2(trim(ltrim(rtrim($parent))),$sessionID);
				}
				
				
				if($debugOutput == TRUE) echo time().' :: lookupByASIN_V2 :: converting baseArray<BR>';
				$baseArray = array2string($baseArray);
				
				
				if($debugOutput == TRUE) echo time().' :: lookupByASIN_V2 :: Storing lookup data<BR>';
				mysql_query("INSERT INTO itemLookup (itemID, itemType, itemDetails, sessionID,admin) VALUES (
				".quote_smart($itemID).", 'ASIN', ".quote_smart($baseArray).", ".quote_smart($sessionID).",".quote_smart($admin)."
				)") or die(mysql_error());
				
				
				if($debugOutput == TRUE) echo time().' :: lookupByASIN_V2 :: Returning TRUE<BR>';
				unset($baseArr,$baseArray,$parent);
				return TRUE;
				
			} else {
				//echo "<pre>".print_r($xml_doc,1)."</pre>";
				itemLog('lookupByASIN_V2 :: $baseArray returned 0 results in the array.', $sessionID, $itemID);
				return FALSE;
			}
			unset($baseArray); //FREE MEMORY
		} else {
			//echo "<pre>".print_r($xml_doc,1)."</pre>";
			itemLog('lookupByASIN_V2 :: $xml_doc is not an object!', $sessionID, $itemID);
			return FALSE;
		}
	}
}

function lookupByUPC_V2($upc, $sessionID,$admin='') {
	$itemID=$upc;
	
	itemLog("lookupByUPC_V2 :: calling getXMLByASIN_V2(".$itemID.");", $sessionID, $itemID);
	
	if($debugOutput == TRUE) echo time().' :: Pulling XML array<BR>';
	
	$xml_doc = getXMLByUPC_V2($itemID,$sessionID);//simplexml_load_file($request);
	
	if($debugOutput == TRUE) echo time().' :: lookupByUPC_V2 :: Array pulled. Logging details.<BR>';
	
	itemLog("lookupByUPC_V2 :: Array Returned: <pre>".htmlspecialchars(print_r(flatten_array(object2array($xml_doc)),1))."</pre>", $sessionID, $itemID);
	
	if($debugOutput == TRUE) echo time().' :: lookupByUPC_V2 :: Details Logged</pre><BR>';
	
	if($debugOutput == TRUE) echo time().' :: lookupByUPC_V2 :: Array Returned <BR>';
	
	
	if($xml_doc->Items->Request->Errors->Error->Message != '') {
		$_SESSION['errorLog'] .= '(UPC,'.$itemID.') - '.$xml_doc->Items->Request->Errors->Error->Message.'<BR>';
		itemLog('lookupByUPC_V2 :: (UPC,'.$itemID.') - '.$xml_doc->Items->Request->Errors->Error->Message.'', $sessionID, $itemID);
		itemError($xml_doc->Items->Request->Errors->Error->Message, $sessionID, $itemID);
		return TRUE;
	} else if($xml_doc->Error->Message != '') {
		$_SESSION['errorLog'] .= '(UPC,'.$itemID.') - '.$xml_doc->Error->Message.'<BR>';
		itemLog('lookupByUPC_V2 :: (UPC,'.$itemID.') - '.$xml_doc->Error->Message.'', $sessionID, $itemID);
		$sql = "UPDATE itemQueue SET status = 'pending', cronJob = '' WHERE skuID = ".quote_smart($itemID)." AND sessionID = ".quote_smart($sessionID)."";
		mysql_query($sql);
		itemLog('lookupByUPC_V2 :: (UPC,'.$itemID.') - ran reset query "'.$sql.'"', $sessionID, $itemID);
		itemLog('lookupByUPC_V2 :: (UPC,'.$itemID.') - reset status to pending', $sessionID, $itemID);
		return FALSE;
	} else {
		if(is_object($xml_doc->Items)) {
			//exit("<pre>".print_r($xml_doc,1)."</pre>"."<HR><pre>".print_r($baseArray,1)."</pre>");
			
			if($debugOutput == TRUE) echo time().' :: lookupByUPC_V2 :: Flattening array.<BR>';
			$baseArray=flatten_array(object2array($xml_doc->Items->Item));
		
			itemLog('lookupByUPC_V2 :: $baseArray returned count ('.count($baseArray).')', $sessionID, $itemID);
			
			if(count($baseArray) > 0) {
				
				if($debugOutput == TRUE) echo time().' :: lookupByUPC_V2 :: Getting detail page url contents.<BR>';
				$data = get_url_contents($xml_doc->Items->Item->DetailPageURL);
				
				if($debugOutput == TRUE) echo time().' :: lookupByUPC_V2 :: Getting ASIN Parent<BR>';
				$parent = getAsinParent($data);
				if($debugOutput == TRUE) echo time().' :: lookupByUPC_V2 :: Getting high res image url<BR>';
				$highResImage = getHiResImageURL($data);
				if($highResImage != '') {
					$baseArray = array('HiResImageURL' => $highResImage)+$baseArray;
				}
				if($parent != $itemID) { 
					if($debugOutput == TRUE) echo time().' :: lookupByUPC_V2 :: Looking up parent ASIN data<BR>';
					//echo $parent.' != '.$data.', this is a child,get parent info for '.$parent.'<BR>';
					$baseArray = array('ParentASIN' => $parent)+$baseArray;
					if($parent != '') lookupByASIN_V2(trim(ltrim(rtrim($parent))),$sessionID);
				}
				
				if($debugOutput == TRUE) echo time().' :: lookupByUPC_V2 :: Converting array to string<BR>';
				$baseArray = array2string($baseArray);
				
				if($debugOutput == TRUE) echo time().' :: lookupByUPC_V2 :: Storing data<BR>';
				mysql_query("INSERT INTO itemLookup (itemID, itemType, itemDetails, sessionID,admin) VALUES (
				".quote_smart($itemID).", 'UPC', ".quote_smart($baseArray).", ".quote_smart($sessionID).",".quote_smart($admin)."
				)") or die(mysql_error());
				
				unset($baseArr,$baseArray,$parent);
				return TRUE;
				
			} else {
				//echo "<pre>".print_r($xml_doc,1)."</pre>";
				itemLog('lookupByUPC_V2 :: $baseArray returned 0 results in the array.', $sessionID, $itemID);
				return FALSE;
			}
			unset($baseArray); //FREE MEMORY
		} else {
			//echo "<pre>".print_r($xml_doc,1)."</pre>";
			itemLog('lookupByUPC_V2 :: $xml_doc is not an object!', $sessionID, $itemID);
			return FALSE;
		}
	}
}

function mergeFields($data,$mergeSchemeID,$masterFieldID) {

	$concatField = getValue("SELECT concatValue FROM mergeSchemes WHERE id = ".quote_smart($mergeSchemeID));
	
	$sql = mysql_query("SELECT * FROM mergeFields WHERE mergeSchemeID = ".quote_smart($mergeSchemeID)." ORDER BY id ASC");
	$count = mysql_num_rows($sql);
	if($count > 0) {
		$endResult = '';
		for($i = 0;$i < $count;$i++) {
			mysql_data_seek($sql, $i);
			$arr = mysql_fetch_array($sql);
			$field = getValue("SELECT fieldName FROM masterTableFields WHERE id = ".quote_smart($arr['masterFieldID'])."");
			if($data[$field] != '') {
			$endResult .= $data[$field].$concatField;
			}
		}
		$endResult = substr($endResult, 0, strlen($endResult)-strlen($concatField));
		return $endResult;
	} else {
		$fieldName = getValue("SELECT fieldName FROM masterTableFields WHERE id = ".quote_smart($masterFieldID)."");
		return $data[$fieldName];
	}
}
function runConversionScheme($value, $conversionSchemeID) {
	$conversionMethodTypes[] = 'If equal to A, replace with B';
	$conversionMethodTypes[] = 'If contains A, replace A with B';
	$conversionMethodTypes[] = 'If contains A, replace all with B';
	$conversionMethodTypes[] = 'If contains A, add B on end';
	$conversionMethodTypes[] = 'If not equal to A, Replace with B';
	$conversionMethodTypes[] = 'If not containing A, Replace with B';
	$conversionMethodTypes[] = 'If not containing A, add B on end';
	$conversionMethodTypes[] = 'If A is numeric, use currency format';
	$conversionMethodTypes[] = 'Replace with A';
	
	$sql = mysql_query("SELECT * FROM conversionMethods WHERE conversionSchemeID = ".quote_smart($conversionSchemeID)."");
	$count = mysql_num_rows($sql);
	for($i = 0;$i < $count;$i++) {
		mysql_data_seek($sql, $i);
		$arr = mysql_fetch_array($sql);
		
		$a = rtrim(ltrim(trim($arr['conversionParamA'])));
		$b = rtrim(ltrim(trim($arr['conversionParamB'])));
		
		if($arr['conversionType'] == 'Replace with A') {
			$value = $a;
		} else if($arr['conversionType'] == 'If equal to A, replace with B') {
			if($value == $a) $value = $b;
		} else if($arr['conversionType'] == 'If contains A, replace A with B') {
			if(substr_count($value,$a) > 0) $value = str_replace($a,$b,$value);
		} else if($arr['conversionType'] == 'If contains A, replace all with B') {
			if(substr_count($value,$a) > 0) $value = $b;
		} else if($arr['conversionType'] == 'If contains A, add B on end') {
			if(substr_count($value,$a) > 0) $value = $value.$b;
		} else if($arr['conversionType'] == 'If not equal to A, Replace with B') {
			if($value != $a) $value = $b;
		} else if($arr['conversionType'] == 'If not containing A, add B on end') {
			if(substr_count($value,$a) == 0) $value = $value.$b;
		} else if($arr['conversionType'] == 'If not containing A, Replace with B') {
			if(substr_count($value,$a) == 0) $value = $b;
		}  else if($arr['conversionType'] == 'If A is numeric, use currency format') {
			if(is_numeric($value)) $value = number_format($value, 2, ".", "");
		}
		
		
	}

	return $value;
}
if (!function_exists('str_getcsv')) {
if($debugMode) echo 'STR_GETCSV DOESNT EXIST, CREATING NOW<BR>';
function str_getcsv($input, $delimiter=',', $enclosure='"', $escape=null, $eol=null) {
	global $debugMode;
  $temp=fopen("php://memory", "rw");
  fwrite($temp, $input);
  fseek($temp, 0);
  $r = array();
  $row = array();
  $rows = 0;
  while (($data = fgetcsv($temp, 4096, $delimiter, $enclosure)) !== false) {
	$rows++;
	//$row[] = $data;
	$r[] = $data;
  }
  fclose($temp);
  return $r;
}
 
} else {
if($debugMode) echo 'STR_GETCSV EXISTS, BYPASSING CREATION<BR>';
}

function csv_headers_to_array($csv, $delimiter = ',', $enclosure = '"', $escape = '\\', $terminator = "\n") {
	$r = array();
    $rows = explode($terminator,trim($csv));
    $names = array_shift($rows);
    return str_getcsv($names,$delimiter,$enclosure,$escape);
}
function array_extend($a, $b) {
    foreach($b as $k=>$v) {
        if( is_array($v) ) {
            if( !isset($a[$k]) ) {
                $a[$k] = $v;
            } else {
                $a[$k] = array_extend($a[$k], $v);
            }
        } else {
            $a[$k] = $v;
        }
    }
    return $a;
}
function merge_assoc_array($array1, $array2) {
   
	if(sizeof($array1)>sizeof($array2))
	{
		echo $size = sizeof($array1);
	}else{
		$a = $array1;
		$array1 = $array2;
		$array2 = $a;
	   
		echo $size = sizeof($array1);
	}
   
	$keys2 = array_keys((array)$array2);
   
	for($i = 0;$i<$size;$i++)
	{
		$array1[$keys2[$i]] = $array1[$keys2[$i]] + $array2[$keys2[$i]];
	}
   
	$array1 = array_filter($array1);
	return $array1;
}
function merge_array_values($array, $addArray) {
	if(is_array($addArray)) {
		foreach($addArray as $k => $v) {
			$array[] = $v;
		}
		return $array;
	} else {
		return $array;
	}
}
function csv_to_array($csv, $delimiter = ',', $enclosure = '"', $escape = '\\', $terminator = "\n",$returnAssocArray=FALSE) {
	global $debugMode;
	
    $r = array();
	
    $rows = explode($terminator,trim($csv));
	
    $names = array_shift($rows);
	
    $names = str_getcsv($names,$delimiter,$enclosure,$escape);
	
    $nc = count($names);
	
	if($debugMode) echo 'Names array <pre>'.print_r($names,1).'</pre><BR>';
	if($debugMode) echo 'rows array <pre>'.print_r($rows,1).'</pre><BR>';
	//$fullRow = FALSE;
	if($debugMode) echo 'Name Count = '.$nc.'<BR>';
	$prevValues = array();
	$rowCount = 0;
    //foreach ($rows as $row) {
	for($i = 0;$i <= count($rows);$i++) {
		$row = $rows[$i];
		if($debugMode) echo "(".$i.",".count($rows).") ".print_r($row,true)."<BR>";
        if (trim($row)) {
		
		$values = str_getcsv($row,$delimiter,$enclosure,$escape);
        if($debugMode) echo 'Converted using str_getcsv : '.print_r($values,true).'<BR>';
		if($debugMode) echo '$values count: '.count($values).'<BR>';
		if (!$values) $values = array_fill(0,$nc,null);
		
		 //  if(count($values) == $nc) {
		if($debugMode) echo 'Count($values) = '.count($values).' - NC = '.$nc.'<BR>';
		$hasData = FALSE;
		//if($debugMode && $rowCount > 500) exit("End debug testing.<BR>");
		if(is_array($values) && count($values) > 0) {
			foreach($values as $k => $v) { 
				if(is_array($v) && count($v) > 0) {
					foreach($v as $key => $value) {
						if($value != '') $hasData = TRUE;
					}
				} else if(is_string($v) && $v != '') $hasData = TRUE;
			}
		}
		
				if($hasData) {
					if($debugMode) echo 'Count($values) = '.count($values).' - NC = '.$nc.'<BR>';
					if($debugMode) echo '<p>'.print_r($values,1).'</p><BR>';
					$rowCount++;
					if($returnAssocArray) $r[] = array_combine($names,$values);
					else $r[] = $values;
				} else {
					if($debugMode) echo 'NO DATA, Excluding <BR>';
				}
        }
    }
    return $r;
} 

function insertMasterField($fieldName,$echo = TRUE) {
	$fieldName = trim(ltrim(rtrim($fieldName)));
	if($fieldName != '' && getValue("SELECT COUNT(id) FROM masterTableFields WHERE LOWER(fieldName) = ".quote_smart(strtolower($fieldName))."") == 0) {
		mysql_query("INSERT INTO masterTableFields (fieldName) VALUES (".quote_smart($fieldName).")") or die(mysql_error());
		if($echo===TRUE) echo 'Inserted '.$fieldName.' into the master table field sets.<BR>';
	}
}

function createImportScheme($schemeName, $customer) {
	mysql_query("INSERT INTO importSchemes (admin, schemeName, customer) VALUES (".quote_smart($_SESSION['login']).", ".quote_smart($schemeName).", ".quote_smart($customer).")") or die(mysql_error());
	return mysql_insert_id();
}
function createExportScheme($schemeName, $customer) {
	mysql_query("INSERT INTO exportSchemes (admin, schemeName, customer) VALUES (".quote_smart($_SESSION['login']).", ".quote_smart($schemeName).", ".quote_smart($customer).")") or die(mysql_error());
	return mysql_insert_id();
}
function updateImportScheme($schemeID, $schemeName, $customer) {
	mysql_query("UPDATE importSchemes SET schemeName = ".quote_smart($schemeName).", customer = ".quote_smart($customer)." WHERE id = ".quote_smart($schemeID)."") or die(mysql_error());
	return TRUE;
}
function updateExportScheme($schemeID, $schemeName, $customer) {
	mysql_query("UPDATE exportSchemes SET schemeName = ".quote_smart($schemeName).", customer = ".quote_smart($customer)." WHERE id = ".quote_smart($schemeID)."") or die(mysql_error());
	return TRUE;
}
function createConversionScheme($schemeName) {
	mysql_query("INSERT INTO conversionSchemes (conversionName) VALUES (".quote_smart($schemeName).")") or die(mysql_error());
	return mysql_insert_id();
}
function addConversionMethod($schemeID, $methodName, $conversionType, $conversionParamA, $conversionParamB='',  $conversionParamC='',  $conversionParamD='',  $conversionParamE='',  $conversionParamF='') {
	
}
function addFieldToImportScheme($schemeID, $fieldName, $conversionMethod, $masterField, $fieldDescription) {
	//$conversionMethods = buildConversionMethods($conversionMethod);
	if(getValue("SELECT COUNT(id) FROM importFields WHERE fieldName = ".quote_smart($fieldName)." AND importSchemeID = ".quote_smart($schemeID)."") == 0) {
		mysql_query("INSERT INTO importFields (importSchemeID, fieldName, masterFieldID,conversionSchemeID,fieldDescription ) VALUES (
		".quote_smart($schemeID).",
		".quote_smart($fieldName).",
		".quote_smart($masterField).",
		".quote_smart($conversionMethod).",
		".quote_smart($fieldDescription)."
		)") or die(mysql_error());
	}
	return TRUE;
}
function addFieldToExportScheme($schemeID, $fieldName, $conversionMethod, $masterField, $fieldDescription,$mergeSchemeID=0) {
	//$conversionMethods = buildConversionMethods($conversionMethod);
	if(getValue("SELECT COUNT(id) FROM exportFields WHERE fieldName = ".quote_smart($fieldName)." AND exportSchemeID = ".quote_smart($schemeID)."") == 0) {
		mysql_query("INSERT INTO exportFields (exportSchemeID, fieldName, masterFieldID,conversionSchemeID,fieldDescription,mergeSchemeID ) VALUES (
		".quote_smart($schemeID).",
		".quote_smart($fieldName).",
		".quote_smart($masterField).",
		".quote_smart($conversionMethod).",
		".quote_smart($fieldDescription).",
		".quote_smart($mergeSchemeID)."
		)") or die(mysql_error());
	}
	return TRUE;
}
function buildConversionMethods($conversionMethods) {
	$sql = mysql_query("SELECT id FROM conversionMethods WHERE conversionSchemeID = ".quote_smart($conversionMethods)."") or die(mysql_error());
	$count = mysql_num_rows($sql);
	$schemes = '';
	for($i = 0;$i < $count;$i++) {
		mysql_data_seek($sql, $i);
		$arr = mysql_fetch_array($sql);
		$schemes .= $arr['id'].',';
	}
	$schemes = substr($schemes, 0, strlen($schemes)-1); //STRIP FINAL COMMA
	return $schemes;
}
function addToQueue($sku,$skuType,$sessionID) {
	if(!is_array($sku)) {
		$sku = trim(ltrim(rtrim($sku)));
		if($sku != '') {
			mysql_query("INSERT INTO itemQueue (skuType, skuID, merchantID, sessionID,status,admin) VALUES (
			".quote_smart($skuType).", ".quote_smart($sku).", '', ".quote_smart($sessionID).", 'pending',".quote_smart($_SESSION['login'])."
			)");
		}
	} else {
		$sku[0] = trim(ltrim(rtrim($sku[0])));
		$sku[1] = trim(ltrim(rtrim($sku[1])));
		if($sku[0] != '' && $sku[1] != '') {
			mysql_query("INSERT INTO itemQueue (skuType, skuID, merchantID, sessionID,status,admin) VALUES (
			".quote_smart($skuType).", ".quote_smart($sku[0]).", ".quote_smart($sku[1]).", ".quote_smart($sessionID).", 'pending',".quote_smart($_SESSION['login'])."
			)");
		}
	}
}
function addToQueueV2($sku,$skuType,$sessionID) {
	if(!is_array($sku)) {
		$sku = trim(ltrim(rtrim(str_replace(" ", "", str_replace("&nbsp;", "",$sku)))));
		if($sku != '') {
			mysql_query("INSERT INTO itemQueue (skuType, skuID, merchantID, sessionID,status,admin,versionNumber,itemAdded) VALUES (
			".quote_smart($skuType).", ".quote_smart($sku).", '', ".quote_smart($sessionID).", 'pending',".quote_smart($_SESSION['login']).",2,NOW()
			)");
		}
	} else {
		$sku[0] = trim(ltrim(rtrim(str_replace(" ", "", str_replace("&nbsp;", "",$sku[0])))));
		$sku[1] = trim(ltrim(rtrim(str_replace(" ", "", str_replace("&nbsp;", "",$sku[1])))));
		if($sku[0] != '' && $sku[1] != '') {
			mysql_query("INSERT INTO itemQueue (skuType, skuID, merchantID, sessionID,status,admin,versionNumber,itemAdded) VALUES (
			".quote_smart($skuType).", ".quote_smart($sku[0]).", ".quote_smart($sku[1]).", ".quote_smart($sessionID).", 'pending',".quote_smart($_SESSION['login']).",2,NOW()
			)");
		}
	}
}
function isAllowedExtension($fileName) {
  return in_array(end(explode(".", $fileName)), $GLOBALS['allowedExtensions']);
}
function get_url_contents($url){
        $crl = curl_init();
        $timeout = 5;
        curl_setopt ($crl, CURLOPT_URL,$url);
        curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $ret = curl_exec($crl);
        curl_close($crl);
        return $ret;
}
function getHiResImageURL($data) {
	if(substr_count($data, '"hiRes":"') > 0) {
		$string = '"hiRes":"';
		//echo 'F:getAsinParent() got contents<BR>'."\n";
		$start = strpos($data, $string) + strlen($string);
		$end = strpos($data, '"', $start+1);
		//echo 'F:getAsinParent() '.$start.' to '.$end.' string removal.<BR>'."\n";
		
		$string = substr($data, $start, ($end - $start));
		
		//echo 'F:getAsinParent() '.htmlspecialchars($string).'<BR>'."\n";
		$string = str_replace("\t", "", $string);
		$string = str_replace(" ", "", $string);
		$string = str_replace("\r", "", $string);
		$string = str_replace("\n", "", $string);
	} else {
		$string = '';
	}
	return $string;
}
function getAsinParent($data) {
	$string = '<input type="hidden" name="ASIN" value="';
	$string2 = '<input type="hidden" id="ASIN" name="ASIN" value="';
	$start = strpos($data, $string) + strlen($string);
	$end = strpos($data, '"', $start+1);
	$string = substr($data, $start, ($end - $start));
	if(substr_count($string2,"<") > 0) {
		$start = strpos($data, $string2) + strlen($string2);
		$end = strpos($data, '"', $start+1);
		$string = substr($data, $start, ($end - $start));
	}
	$string = str_replace("\t", "", $string);
	$string = str_replace(" ", "", $string);
	$string = str_replace("\r", "", $string);
	$string = str_replace("\n", "", $string);
	return $string;
}
function object2array($object) {
	global $echoDebug;
	$return = NULL;
	  
	if(is_array($object))
	{
		foreach($object as $key => $value)
			$return[$key] = object2array($value);
	}
	else if(is_object($object)) 
	{
		$var = get_object_vars($object);
		  
		if($var)
		{
			foreach($var as $key => $value)
				$return[$key] = ($key && !$value) ? NULL : object2array($value);
		}
		else return $object;
	} else {
		return $object;
	}

	return $return;
}

function lookupBySku($sku, $merchant, $sessionID,$admin='') {
	$itemID=$sku;
	
	$searchIndex = 'All';
	$accessKey = '';

	$responseGroups = 'SellerListing';

	$extra = '&Operation=SellerListingLookup';
	$extra .= '&MerchantId='.urlencode($merchant);
	$extra .= '&SellerId='.urlencode($merchant); 
	$itemIdType = 'SKU';
	
	$extra .= '&IdType='.urlencode($itemIdType);
	$extra .= '&ItemId='.$itemID;
	$extra .= '&Id='.$itemID;
	$extra .= '&SearchIndex='.$searchIndex;
	
	$url = "http://ecs.amazonaws.com/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=".urlencode($accessKey).$extra."&ResponseGroup=".$responseGroups;
	//define("PHP_URL_HOST",'http://ecs.amazonaws.com/onca/xml');
	$secret = '';
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
	
	if($xml_doc->SellerListings->Request->Errors->Error->Message != '') {
		$_SESSION['errorLog'] .= '(SKU,'.$itemID.') - '.$xml_doc->SellerListings->Request->Errors->Error->Message.'<BR>';
	} else {
		$asin = $xml_doc->SellerListings->SellerListing->ASIN;
		if($asin != '') {
			lookupByASIN(trim(ltrim(rtrim($asin))),$sessionID);
		} else {
			if(is_object($xml_doc->SellerListings)) {
				$baseArray = array2string(flatten_array(object2array($xml_doc->SellerListings->SellerListing)));
				unset($parent);
				
				if($baseArray != '') {
					mysql_query("INSERT INTO itemLookup (itemID, itemType, itemDetails, sessionID,admin) VALUES (
					".quote_smart($itemID).", 'SKU', ".quote_smart($baseArray).", ".quote_smart($sessionID).",".quote_smart($admin)."
					)") or die(mysql_error());
				} else {
				}
				unset($baseArray); //FREE MEMORY
			} else {
			}
		}
	}
}
function lookupByUPC($upc, $sessionID,$admin='') {
	$itemID=$upc;
	$searchIndex = 'All';
	$accessKey = '';
	$responseGroups = 'Images,Small,Medium,Large,Offers,OfferFull,OfferSummary,OfferListings,Variations,VariationImages,VariationSummary,ItemAttributes,ItemIds';
	$extra = '&Operation=ItemLookup';
	$itemIdType = 'UPC';
	
	$extra .= '&IdType='.urlencode($itemIdType);
	$extra .= '&ItemId='.$itemID;
	$extra .= '&Id='.$itemID;
	$extra .= '&SearchIndex='.$searchIndex;
	
	$url = "http://ecs.amazonaws.com/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=".urlencode($accessKey).$extra."&ResponseGroup=".$responseGroups;
	//define("PHP_URL_HOST",'http://ecs.amazonaws.com/onca/xml');
	$secret = '';
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
	
	if($xml_doc->SellerListings->Request->Errors->Error->Message != '') {
		$_SESSION['errorLog'] .= '(UPC,'.$itemID.') - '.$xml_doc->SellerListings->Request->Errors->Error->Message.'<BR>';
	} else {
		if(is_object($xml_doc->Items)) {
			$baseArray = object2array($xml_doc->Items);
			
			foreach($baseArray['Item'] as $k => $v) {
				$finalBase = array2string(flatten_array($v));
				$sql = "INSERT INTO itemLookup (itemID, itemType, itemDetails, sessionID,admin) VALUES (
				".quote_smart($itemID).", 'UPC', ".quote_smart($finalBase).", ".quote_smart($sessionID).",".quote_smart($admin)."
				)";
				mysql_query($sql) or die(mysql_error());
			}
			unset($parent);
			unset($baseArray); //FREE MEMORY
		} else {
		}
	}
}
function lookupByASIN($asin, $sessionID,$admin='') {
	$itemID=$asin;
	$searchIndex = 'All';
	$accessKey = '';
	$responseGroups = 'Images,Small,Medium,Large,Offers,OfferFull,OfferSummary,OfferListings,Variations,VariationImages,VariationSummary,ItemAttributes,ItemIds';
	$extra = '&Operation=ItemLookup';
	$itemIdType = 'ASIN';
	$extra .= '&IdType='.urlencode($itemIdType);
	$extra .= '&ItemId='.$itemID;
	$extra .= '&Id='.$itemID;
	$url = "http://ecs.amazonaws.com/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=".urlencode($accessKey).$extra."&ResponseGroup=".$responseGroups;
	//define("PHP_URL_HOST",'http://ecs.amazonaws.com/onca/xml');
	$secret = '';
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
	
	if($xml_doc->SellerListings->Request->Errors->Error->Message != '') {
		$_SESSION['errorLog'] .= '(ASIN,'.$itemID.') - '.$xml_doc->SellerListings->Request->Errors->Error->Message.'<BR>';
	} else {
		if(is_object($xml_doc->Items)) {
			
			$baseArray=flatten_array(object2array($xml_doc->Items->Item));
			if(count($baseArray) > 0) {
				
				$data = get_url_contents($xml_doc->Items->Item->DetailPageURL);
				$parent = getAsinParent($data);
				$highResImage = getHiResImageURL($data);
				if($highResImage != '') {
					$baseArray = array('HiResImageURL' => $highResImage)+$baseArray;
				}
				if($parent != $itemID) {
					$baseArray = array('ParentASIN' => $parent)+$baseArray;
					if($parent != '') lookupByASIN(trim(ltrim(rtrim($parent))),$sessionID);
				}
				
				
				$baseArray = array2string($baseArray);
				
				mysql_query("INSERT INTO itemLookup (itemID, itemType, itemDetails, sessionID,admin) VALUES (
				".quote_smart($itemID).", 'ASIN', ".quote_smart($baseArray).", ".quote_smart($sessionID).",".quote_smart($admin)."
				)") or die(mysql_error());
				
			
				unset($baseArr,$baseArray,$parent);
				
				
			} else {
			}
			unset($baseArray); //FREE MEMORY
		} else {
		}
	}
}
function lookupByEAN($ean, $sessionID,$admin='') {
	$itemID=$ean;
	$searchIndex = 'All';
	$accessKey = '';
	$responseGroups = 'Images,Small,Medium,Large,Offers,OfferFull,OfferSummary,OfferListings,Variations,VariationImages,VariationSummary,ItemAttributes,ItemIds';
	$extra = '&Operation=ItemLookup';
	$itemIdType = 'EAN';
	$extra .= '&IdType='.urlencode($itemIdType);
	$extra .= '&ItemId='.$itemID;
	$extra .= '&Id='.$itemID;
	$extra .= '&SearchIndex='.$searchIndex;
	$url = "http://ecs.amazonaws.com/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=".urlencode($accessKey).$extra."&ResponseGroup=".$responseGroups;
	//define("PHP_URL_HOST",'http://ecs.amazonaws.com/onca/xml');
	$secret = '';
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
	
	if($xml_doc->SellerListings->Request->Errors->Error->Message != '') {
		$_SESSION['errorLog'] .= '(EAN,'.$itemID.') - '.$xml_doc->SellerListings->Request->Errors->Error->Message.'<BR>';
	} else {
		if(is_object($xml_doc->Items)) {
			$baseArray=flatten_array(object2array($xml_doc->Items->Item));
			if(count($baseArray) > 0) {
				$parent = getAsinParent($xml_doc->Items->Item->DetailPageURL);
				
				if($parent != $itemID) { 
					$baseArray = array('ParentASIN' => $parent)+$baseArray;
					if($parent != '') lookupByASIN(trim(ltrim(rtrim($parent))),$sessionID);
				}
				
				$baseArray = array2string($baseArray, $baseArr, $parent);
				
				mysql_query("INSERT INTO itemLookup (itemID, itemType, itemDetails, sessionID,admin) VALUES (
				".quote_smart($itemID).", 'EAN', ".quote_smart($baseArray).", ".quote_smart($sessionID).",".quote_smart($admin)."
				)") or die(mysql_error());				
				unset($baseArr,$baseArray,$parent);
			} else {
			}
			unset($baseArray); //FREE MEMORY
		} else {
		}
	}
}
function flatten_array($array, $prefix = null) {
  if ($prefix) $prefix .= '_';

  $items = array();
  if(is_array($array)) {
	  if(count($array) > 0) {
		  foreach ($array as $key => $value) {
			if (is_array($value))
			  $items = array_merge($items,  flatten_array($value, $prefix . $key));
			else
			  $items[$prefix . $key] = $value;
		  }
	  }
  }
  return $items;
}

function array2string2($myarray,$output = "",$parentkey = ""){
	
	global $newValueDelimiter,$keyValueDelimiter;
	if($newValueDelimiter != '') $nvDelimiter = $newValueDelimiter; else $nvDelimiter = "{|ND}";
	if($keyValueDelimiter != '') $kvDelimiter = $keyValueDelimiter; else $kvDelimiter = "{|KD}";
	
  foreach($myarray as $key=>$value){
	 if (is_array($value)) {
		$parentkey .= $key.$kvDelimiter;
		$output .= array2string($value,$output,$parentkey);
		$parentkey = "";
	 } else {
		$output .= $parentkey.$key.$kvDelimiter.$value.$nvDelimiter;
	 }
  }
  return $output;
}

function array2string($myarray,$output='',$parentkey=''){
	
	global $newValueDelimiter,$keyValueDelimiter;
	if($newValueDelimiter != '') $nvDelimiter = $newValueDelimiter; else $nvDelimiter = "{|ND}";
	if($keyValueDelimiter != '') $kvDelimiter = $keyValueDelimiter; else $kvDelimiter = "{|KD}";
	
  foreach($myarray as $key=>$value){
	 if (is_array($value)) {
		$parentkey .= $key.$kvDelimiter;
		$output .= array2string($value,'',$parentkey);
		$parentkey = "";
	 } else {
		$output .= $parentkey.$key.$kvDelimiter.$value.$nvDelimiter;
	 }
  }
  return $output;
}

function string2array($string,$myarray=''){
  $myarray = array();
	global $newValueDelimiter,$keyValueDelimiter;
	if($newValueDelimiter != '') $nvDelimiter = $newValueDelimiter; else $nvDelimiter = "{|ND}";
	if($keyValueDelimiter != '') $kvDelimiter = $keyValueDelimiter; else $kvDelimiter = "{|KD}";
	
  $lines = explode($nvDelimiter,$string);
  foreach ($lines as $value){
	 $items = explode($kvDelimiter,$value);
	 if (sizeof($items) == 2){
		$myarray[$items[0]] = $items[1];
	 }
	 else if (sizeof($items) == 3){
		$myarray[$items[0]][$items[1]] = $items[2];
	 }
  }
  return $myarray;
}
function genVars() {
	$string = "";
	foreach($_REQUEST as $k => $v) {
		$string .= "&$k=$v";
	}
	return $string;
}
function checkSearch($value) {
	$arr[0] = "'";
	$arr[1] = "%";
	$arr[2] = "\"";
	$arr[3] = "/";
	$arr[4] = "|";
	$arr[5] = "(";
	$arr[6] = ")";
	foreach($arr as $k => $v) {
		if(substr_count($value, $v) > 0) {
			echo "You have entered invalid search parameters. Please click back on your browser and try again.<BR>";
			return FALSE;
			exit;
		}
	}
	return TRUE;
}
function logSearch($searchfor, $seekfor) {
	mysql_query("INSERT INTO searchLogs (`username`, `timestamp`, `date`, `searchPhrase`, `seekfor`, `action`, `ipAddress`, `hostname`, `hostport`) VALUES (".quote_smart($_SESSION['login']).", '".time()."', '".date("m-d-y")."', ".quote_smart($searchfor).", ".quote_smart($seekfor).", ".quote_smart(genVars()).", ".quote_smart($_SERVER['REMOTE_ADDR']).", ".quote_smart($_SERVER['REMOTE_HOST']).", ".quote_smart($_SERVER['REMOTE_PORT']).")");
}
function parseMessage($message, $type = "text") {
	if($type == 'text') {
		$message = str_replace("<BR>", "\n", $message);
		$message = str_replace("\"", "'", $message);
		$message = str_replace("<BR />", "\n", $message);
		$message = str_replace("<br />", "\n", $message);
		$message = str_replace("<br>", "\n", $message);
		$message = str_replace("&amp;", "&", $message);
		$message = str_replace("&lt;", "<", $message);
		$message = str_replace("&gt;", ">", $message);
	} else if($type == 'html') {
		$message = str_replace("\n", "<BR>", $message);
		$message = str_replace("\"", "'", $message);
		//$message = str_replace("\r", "<BR>", $message);
	} else {
		$message = str_replace("\n", "<BR>", $message);
		$message = str_replace("\"", "'", $message);
		//$message = str_replace("\r", "<BR>", $message);
	}
	return stripslashes($message);
}

function theRealStripTags($string){
   while(strstr($string, '>')){
       $currentBeg = strpos($string, '<');
       $currentEnd = strpos($string, '>');
       $tmpStringBeg = @substr($string, 0, $currentBeg);
       $tmpStringEnd = @substr($string, $currentEnd + 1, strlen($string));
       $string = $tmpStringBeg.$tmpStringEnd;
   } 
   return $string;
}

function cdbg($var = "") {
	if($var != "") {
		if($_SESSION[$var] != 'yes') {
			return FALSE;
		} else {
			return TRUE;
		}
	} else {
		if($_SESSION['debugMode'] != 'yes') {
			return FALSE;
		} else {
			return TRUE;
		}
	}
}

function displaySponsorInfo($displaySponsorName, $displaySponsorPhone, $displaySponsorEmail, $sponsorBUSINESSNAME, $sponsorFNAME, $sponsorLNAME, $sponsorPHONE, $sponsorEMAIL) {
	if ($displaySponsorName == 'displaybusinessname') {
		echo $sponsorBUSINESSNAME;
	} else if ($displaySponsorName == 'displayfullname' || $displaySponsorName == '') {
		echo $sponsorFNAME." ".$sponsorLNAME;
	}
	if ($displaySponsorPhone == 'displayphone') {
		echo "<BR><img src=\"/images/_icon_messagecenter_small.gif\">".ltrim(trim(rtrim($sponsorPHONE)));
	}
	if($displaySponsorEmail == 'yes') {
		echo "<BR><img src=\"/images/icon_customersupport_small.gif\">".ltrim(trim(rtrim($sponsorEMAIL)));
	}
	return TRUE;
}

function timeRec($aType = NULL, $action = NULL) {
	$recType = $GLOBALS['timeRecordingType'];
	if($action == NULL) {
		if(isset($GLOBALS['timeRecExec'])) {
			if($GLOBALS['timeRecExec'] == '') {
				if(!isset($recType)) { 
					$GLOBALS['timeRecExec'] = $aType.": ".microtime();
				} else {
					$GLOBALS['timeRecExec'] = $aType.": ".time();
				}
				return TRUE;
			} else {
				if(!isset($recType)) { 
					$GLOBALS['timeRecExec'] .= "\n".$aType.": ".microtime();
				} else {
					$GLOBALS['timeRecExec'] .= "\n".$aType.": ".time();
				}
				return TRUE;
			}
		} else {
			if(!isset($recType)) { 
				$GLOBALS['timeRecExec'] = $aType.": ".microtime();
			} else {
				$GLOBALS['timeRecExec'] = $aType.": ".time();
			}
			return TRUE;
		}
	}
	if($action == "output") { 
		echo "<!-- \n ".$GLOBALS['timeRecExec']." \n -->";
		return TRUE;
	}
}
function removeSymbols($email) { 
	$email = str_replace(' ', '', $email);
	$email = str_replace('!', '', $email);
	$email = str_replace('#', '', $email);
	$email = str_replace('$', '', $email);
	$email = str_replace('%', '', $email);
	$email = str_replace('^', '', $email);
	$email = str_replace('&', '', $email);
	$email = str_replace('*', '', $email);
	$email = str_replace('(', '', $email);
	$email = str_replace(')', '', $email);
	$email = str_replace('=', '', $email);
	$email = str_replace('+', '', $email);
	$email = str_replace('/', '', $email);
	$email = str_replace(',', '', $email);
	$email = str_replace('<', '', $email);
	$email = str_replace('>', '', $email);
	$email = str_replace('?', '', $email);
	$email = str_replace(':', '', $email);
	$email = str_replace(';', '', $email);
	$email = str_replace('"', '', $email);
	$email = str_replace('\'', '', $email);
	$email = str_replace('[', '', $email);
	$email = str_replace(']', '', $email);
	$email = str_replace('\\', '', $email);
	$email = str_replace('{', '', $email);
	$email = str_replace('}', '', $email);
	$email = str_replace('|', '', $email);
	$email = str_replace('`', '', $email);
	$email = str_replace('~', '', $email);
	return $email;
}
function parseSymbols($value) {
	$value = strtolower($value);
	$value = str_replace(' ', '', $value);
	$value = str_replace('!', '', $value);
	$value = str_replace('@', '', $value);
	$value = str_replace('#', '', $value);
	$value = str_replace('$', '', $value);
	$value = str_replace('%', '', $value);
	$value = str_replace('^', '', $value);
	$value = str_replace('&', '', $value);
	$value = str_replace('*', '', $value);
	$value = str_replace('(', '', $value);
	$value = str_replace(')', '', $value);
	$value = str_replace('_', '', $value);
	$value = str_replace('-', '', $value);
	$value = str_replace('=', '', $value);
	$value = str_replace('+', '', $value);
	$value = str_replace('/', '', $value);
	$value = str_replace('.', '', $value);
	$value = str_replace(',', '', $value);
	$value = str_replace('<', '', $value);
	$value = str_replace('>', '', $value);
	$value = str_replace('?', '', $value);
	$value = str_replace(':', '', $value);
	$value = str_replace(';', '', $value);
	$value = str_replace('"', '', $value);
	$value = str_replace('\'', '', $value);
	$value = str_replace('[', '', $value);
	$value = str_replace(']', '', $value);
	$value = str_replace('\\', '', $value);
	$value = str_replace('{', '', $value);
	$value = str_replace('}', '', $value);
	$value = str_replace('|', '', $value);
	$value = str_replace('`', '', $value);
	$value = str_replace('~', '', $value);
	return $value;
}
function remSymbols($value) {
	$value = str_replace("-", "", $value);
	$value = str_replace("/", "", $value);
	$value = str_replace("\"", "", $value);
	$value = str_replace("\\", "", $value);
	$value = str_replace("(", "", $value);
	$value = str_replace(")", "", $value);
	$value = str_replace(" ", "", $value);
	$value = str_replace("{", "", $value);
	$value = str_replace("}", "", $value);
	$value = str_replace("[", "", $value);
	$value = str_replace("]", "", $value);
	$value = str_replace("?", "", $value);
	$value = str_replace(".", "", $value);
	$value = str_replace(",", "", $value);
	$value = str_replace(">", "", $value);
	$value = str_replace("<", "", $value);
	$value = str_replace("|", "", $value);
	$value = str_replace("*", "", $value);
	$value = str_replace("&", "", $value);
	$value = str_replace("^", "", $value);
	$value = str_replace("%", "", $value);
	$value = str_replace("$", "", $value);
	$value = str_replace("#", "", $value);
	$value = str_replace("@", "", $value);
	$value = str_replace("!", "", $value);
	$value = str_replace("`", "", $value);
	return $value;
}

function GetRandomString($length) {
	// you could repeat the alphabet to get more randomness
	$template = "1234567890abcdefghijklmnopqrstuvwxyz";
	$rndstring = '';
	$a = 0;
	$b = 0;   
	for ($a = 0; $a <= $length; $a++) {
		$b = rand(0, strlen($template) - 1);
		$rndstring .= $template[$b];
	}
	return $rndstring;
}

function quote_smart($value)
{
   // Stripslashes
   if (get_magic_quotes_gpc()) {
       $value = stripslashes($value);
   }
   // Quote if not integer
   if (!is_numeric($value)) {
       $value = "'" . mysql_real_escape_string($value) . "'";
   } else {
   		$value = "'" . $value . "'";
   }
   return $value;
}
//RETURNS A SINGLE VALUE OF A QUERY
function getValue($query) {
	if(getCount($query) > 0) {
		return mysql_result(mysql_query($query),0);
	}
}
function getArray($query) {
	if($GLOBALS['debugModeInitiated'] == 'yes') {
		$runQuery = mysql_query($query) or die(mysql_error());
		if(getCount($query) > 0) {
			$arr = mysql_fetch_array($runQuery);
		} else {
			$arr[] = "";
		}
		return $arr;
	} else {
		if($GLOBALS['echoDebug']) $runQuery = mysql_query($query) or die("Error with SQL Query: $query<BR>The error was ".mysql_error()."");
		else $runQuery = @mysql_query($query);
		$arr = @mysql_fetch_array($runQuery);
		return $arr;
	}
}

function getFullArray($query) {
	if($GLOBALS['debugModeInitiated'] == 'yes') {
		$runQuery = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($runQuery) == 1) {
			$arr = mysql_fetch_array($runQuery);
		} else if(mysql_num_rows($runQuery) > 1) {
			$count = mysql_num_rows($runQuery);
			$finalArray = array();
			for($i = 0;$i < $count;$i++) {
				mysql_data_seek($runQuery, $i);
				$finalArray[] = mysql_fetch_array($runQuery);
			}
			$arr = $finalArray;
		} else {
			$arr[] = "";
		}
		return $arr;
	} else {
		$runQuery = @mysql_query($query);
		$count = @mysql_num_rows($runQuery);
		$finalArray = array();
		for($i = 0;$i < $count;$i++) {
			@mysql_data_seek($runQuery, $i);
			$finalArray[] = @mysql_fetch_array($runQuery);
		}
		$arr = $finalArray;
		return $arr;
	}
}

function getVars($query) {
	$arr = getArray($query);
	//if(count($arr) > 0) {
	if($GLOBALS['debugModeInitiated'] == 'yes') {
		echo "Functions.inc FetchVars (".count($arr).");<BR>\nQuery: ".$query."<BR>\n<BR>\n";
	}
	if(is_array($arr)) {
		if(count($arr) > 0) {
			foreach($arr AS $key => $value) {
				$GLOBALS[$key] = $value;
			}
		}
	}
	//}
	//return TRUE;
}

function getCount($query, $type = "", $countValue = "") {
	if($GLOBALS['echoDebug']) { $runQuery = mysql_query($query) or die("Error with SQL Query: $query<BR>The error was ".mysql_error().""); } else { $runQuery = @mysql_query($query); }
	if($type == "COUNT") {	
		return @mysql_result($runQuery,0,"COUNT(id)");
	} else if($type == "SUM") {
		if($countValue == "") {
			return mysql_result($runQuery,0,"SUM(id)");
		} else {
			return mysql_result($runQuery,0,$countValue);
		}
	} else {
		return mysql_num_rows($runQuery);
	}	
}

function checkSpam($email) {

	//$array[] = "@comcast.net";

	$array[] = "spam";
	$array[] = "abuse@";
	$array[] = "postmaster@";
	$array[] = "fake@";
	$array[] = "nospaming";
	$array[] = "nospamming";
	$array[] = "nospam";
	$array[] = "spammer@";
	$array[] = "donotsend@";
	$array[] = "root@";
	$array[] = "spam.com";
	$array[] = "spammotel.com";
	$array[] = "not2bspammed.com";
	$array[] = "scam.com";
	$array[] = "SPAMMERS.COM";
	$array[] = "spambob.net";
	$array[] = "SPAMME@";
	$array[] = "fuckyou";
	$array[] = "fucku";
	$array[] = "MISSPAMELABOX@AOL.COM";
	$array[] = "nospam.net";
	$array[] = "outblaze.com";
	$array[] = "outblaze.org";
	$array[] = "surbl.org";
	$array[] = "nobody@";
	$array[] = "spammers.net";
	$array[] = "spambox.info";
	$array[] = "fakemail.com";
	$array[] = "eat-crap.com";
	$array[] = "UCE.GOV";
	$array[] = "spam@comcast.net";
	$array[] = "spam@gmail.com";
	$array[] = "@spambob.org";
	$array[] = "fucker";
	$array[] = "fukoff";
	$array[] = "scammer@";
	$array[] = "scam@";
	$array[] = "MAILER-DAEMON@";
	$key = 1;
	foreach($array as $k => $v) {
		if(substr_count(strtolower($email), strtolower($v)) > 0) {
			$key = 0;
		}
	}
	if($key == 0) {
		return FALSE;
	} else {
		return TRUE;
	}
}
function checkUnsubscribe($email) {
	if(getCount("SELECT COUNT(id) FROM globalunsubscribe WHERE email = ".quote_smart($email)."", "COUNT") > 0) {
		return FALSE;
	} else {
		return TRUE;
	}
}
function checkEmail($email) {
	$echoDebug = $GLOBALS['echoDebug'];
	if($echoDebug) timeRec("\t\t\t(checkEmail) Function Started");
	$email = str_replace("..", ".", $email);
	if($echoDebug) timeRec("\t\t\t(checkEmail) eRegi Check");
	if(eregi("^[a-zA-Z0-9_]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$]", $email)) {
		if($echoDebug) timeRec("\t\t\t(checkEmail) eRegi Failed");
    	return FALSE;
	} else {
		if($echoDebug) timeRec("\t\t\t(checkEmail) Checking Spam");
		if(checkSpam($email)) {
			if($echoDebug) timeRec("\t\t\t(checkEmail) Spam Check Success, Checking Unsubscribe");
			if(checkUnsubscribe($email)) {
				if($echoDebug) timeRec("\t\t\t(checkEmail) Unsubscribe Check Success");
				return TRUE;
			} else {
				if($echoDebug) timeRec("\t\t\t(checkEmail) Unsubscribe Check Failed");
				return FALSE;
			}
		} else {
			if($echoDebug) timeRec("\t\t\t(checkEmail) Check Spam Failed");
			return FALSE;
		}
   }
}

function getFile($file, $page = "") {
	$getFile = @include($file);
	if($getFile) {
		return TRUE;
	} else {
		if($page != "") {
			echo "The page you are requesting (".$page.") is currently being updated. Please check back at a later time.<BR><BR>We appologise for any inconvenience this may have caused.<BR>";
			exit;
		} else {
			echo "The page you are requesting is currently being updated. Please check back at a later time.<BR><BR>We appologise for any inconvenience this may have caused.<BR>";
			exit;
		}
	}
}

function usersOnline($username, $userType) { //CALL THIS ON EVERY PAGE ! USERTYPE IS EITHER PROSPECT OR MEMBER - USERNAME IS THE ID OF THE PROSPECT OR THE MEMBERS USERNAME
	$query = mysql_query("SELECT username FROM usersOnline WHERE username=".quote_smart($username)."");
	$count = mysql_num_rows($query);
	$time = time();
	if($count == 0) {
		$query = mysql_query("INSERT INTO usersOnline (`ip`, `timestamp`, `username`, `userType`) VALUES (".quote_smart($_SERVER['REMOTE_ADDR']).", '$time', ".quote_smart($username).", ".quote_smart($userType).")");
	} else {
		$query = mysql_query("UPDATE usersOnline SET timestamp = '$time' WHERE username = ".quote_smart($username)."");
	}
	//PURGE OLD RESULTS
	$newTime = $time - 300;
	$query = mysql_query("DELETE FROM usersOnline WHERE timestamp < '$newTime'");
	return TRUE;
}

function getOnlineUsers($var = NULL) {
	if($var == "member" || $var == "members") {
		$count = mysql_query("SELECT COUNT(id) FROM usersOnline WHERE userType = 'member'");
	} else if($var == "prospect" || $var == "tourtaker" || $var == "tourtakers") {
		$count = mysql_query("SELECT COUNT(id) FROM usersOnline WHERE userType = 'prospect'");
	} else {
		$count = mysql_query("SELECT COUNT(id) FROM usersOnline");
	}
	$arr = mysql_fetch_array($count);
	$newCount = $arr['COUNT(id)'];
	return $newCount;
}

function getOnlineStatus($username) { //RETURNS BOOLEAN VALUE OF WEATHER OR NOT USER IS ONLINE
	$query = mysql_query("SELECT COUNT(id) FROM usersOnline WHERE username = ".quote_smart($username)."");
	$arr = mysql_fetch_array($query);
	$count = $arr['COUNT(id)'];
	if($count == 0) {
		return FALSE;
	} else {
		return TRUE;
	}
}

#######################################################################################
###################################################### WORK WITH FAQ SYSTEMS CATEGORIES
#######################################################################################
function dispCatJump($parentCategory = '', $subStep = '', $begValOptions = '', $endValOptions = '', $catSelection = '') {
	if($parentCategory == '') {
		$query = mysql_query("SELECT id, categoryName FROM FAQstructure WHERE parentCategory = ''");
		$count = mysql_num_rows($query);
		$subStep .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		for($i = 0;$i < $count;$i++) {
			mysql_data_seek($query, $i);
			$array = mysql_fetch_array($query);
			echo "<option value=\"".$begValOptions.$array['id'].$endValOptions."\"";
			if($array['id'] == $catSelection) { echo " selected"; }
			echo ">".$array['categoryName']."</option>";
			
			if(getCount("SELECT COUNT(id) FROM FAQstructure WHERE parentCategory = ".quote_smart($array['id'])."", "COUNT") > 0) {
				dispCatJump($array['id'], $subStep, $begValOptions, $endValOptions);
			}// else {
				
			//}
		}
	} else {
		//echo "<option value=\"".$parentCategory."\">".$subStep."".getValue("SELECT categoryName FROM FAQstructure WHERE id = ".quote_smart($parentCategory)."")."</option>";
		if(getCount("SELECT COUNT(id) FROM FAQstructure WHERE parentCategory = ".quote_smart($parentCategory)."", "COUNT") > 0) {
			$query = mysql_query("SELECT id, categoryName FROM FAQstructure WHERE parentCategory = ".quote_smart($parentCategory)."");
			$count = mysql_num_rows($query);
			for($i = 0;$i < $count;$i++) {
				mysql_data_seek($query, $i);
				$array = mysql_fetch_array($query);
				echo "<option value=\"".$begValOptions.$array['id'].$endValOptions."\">".$subStep."".$array['categoryName']."</option>";
				dispCatJump($array['id'], $subStep."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $begValOptions, $endValOptions);
			}
		} else {
			$query = mysql_query("SELECT id, categoryName FROM FAQstructure WHERE parentCategory = ".quote_smart($parentCategory)."");
			$count = mysql_num_rows($query);
			for($i = 0;$i < $count;$i++) {
				mysql_data_seek($query, $i);
				$array = mysql_fetch_array($query);
				echo "<option value=\"".$begValOptions.$array['id'].$endValOptions."\">".$subStep.$array['categoryName']."</option>";
			}
		}
	}
}
function dispCategories($parentCategory = '', $subStep = '', $output="[category]<BR>", $catTag='[category]', $qOutput='[question]', $aOutput='[answer]') {
	if($parentCategory == '') {
		$query = mysql_query("SELECT id, categoryName FROM FAQstructure WHERE parentCategory = '' ORDER BY categoryName DESC");
		$count = mysql_num_rows($query);
		$subStep .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		for($i = 0;$i < $count;$i++) {
			mysql_data_seek($query, $i);
			$array = mysql_fetch_array($query);
			?><h3 onclick="expandcontent(this, 'C<?php echo $array['categoryName']; ?>')" style="cursor:hand; cursor:pointer"><span class="showstate"></span><?php echo "<span class=\"style2\">".$array['categoryName']."</span>"; ?></h3><div id="C<?php echo $array['categoryName']; ?>" class="switchcontent">
			<?php
			if(getCount("SELECT COUNT(id) FROM FAQstructure WHERE parentCategory = ".quote_smart($array['id'])."", "COUNT") > 0){
				dispCategories($array['id'], $subStep);
			}
			$questions = "<h3 onclick=\"expandcontent(this, 'Q".$array['id']."-[i]')\" style=\"cursor:hand; cursor:pointer; padding-left: 12px;\"><span class=\"showstate\"></span><span class=\"style3\">[question]</span></h3><div id=\"Q".$array['id']."-[i]\" class=\"switchcontent\"><span class=\"style5\" style=\"padding-left: 25px;\">[answer]</span></div>";
			dispQuestions($array['id'],$questions,'[question]','[answer]', $subStep, "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(X3)There are no questions in $catname.<BR>");
			echo "</div>";
		}
	} else {
		if(getCount("SELECT COUNT(id) FROM FAQstructure WHERE parentCategory = ".quote_smart($parentCategory)."", "COUNT") > 0) {
			$query = mysql_query("SELECT id, categoryName FROM FAQstructure WHERE parentCategory = ".quote_smart($parentCategory)." ORDER BY categoryName ASC");
			$count = mysql_num_rows($query);
			for($i = 0;$i < $count;$i++) {
				mysql_data_seek($query, $i);
				$array = mysql_fetch_array($query);
				$catname = $array['categoryName'];
				?><h3 onclick="expandcontent(this, 'C<?php echo $catname; ?>')" style="cursor:hand; cursor:pointer"><span class="showstate"></span><?php echo "<span class=\"style2\">".$subStep.$catname."</span>"; ?></h3><div id="C<?php echo $catname; ?>" class="switchcontent">
				<?php
				dispCategories($array['id'], $subStep."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
				$questions = "<h3 onclick=\"expandcontent(this, 'Q$id-[i]')\" style=\"cursor:hand; cursor:pointer; padding-left: 12px;\"><span class=\"showstate\"></span><span class=\"style3\">[question]</span></h3><div id=\"Q$id-[i]\" class=\"switchcontent\"><span class=\"style5\" style=\"padding-left: 25px;\">[answer]</span></div>";
				dispQuestions($array['id'],$questions,'[question]','[answer]', $subStep, "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(X3)There are no questions in $catname.<BR>");
				echo "</div>";
			}
		} else {
			$query = mysql_query("SELECT id, categoryName FROM FAQstructure WHERE parentCategory = ".quote_smart($parentCategory)." ORDER BY categoryName ASC");
			$count = mysql_num_rows($query);
			if($count > 0):
				for($i = 0;$i < $count;$i++) {
					mysql_data_seek($query, $i);
					$array = mysql_fetch_array($query);
					$catname = $array['categoryName'];
					echo $subStep."&bull;&nbsp;&nbsp;&nbsp;<STRONG>".str_replace($catTag, $catname, str_replace("[id]", $array['id'], $output))."</STRONG>";
					$questions = "<h3 onclick=\"expandcontent(this, 'Q$id-[i]')\" style=\"cursor:hand; cursor:pointer; padding-left: 12px;\"><span class=\"showstate\"></span><span class=\"style3\">[question]</span></h3><div id=\"Q$id-[i]\" class=\"switchcontent\"><span class=\"style5\" style=\"padding-left: 25px;\">[answer]</span></div>";
					dispQuestions($array['id'],$questions,'[question]','[answer]', $subStep, "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(X3)There are no questions in $catname.<BR>");
				}
			endif;
		}
	}
}

function dispQuestions($category,$output = '[question]<BR>[answer]<HR>',$qTag='[question]',$aTag='[answer]', $subStep='', $else = 'There are no questions in this category.<BR>') {
	if(getCount("SELECT COUNT(id) FROM FAQ WHERE publicCat = ".quote_smart($category)."", "COUNT") > 0):
		$q = mysql_query("SELECT question, answer FROM FAQ WHERE publicCat = ".quote_smart($category)." ORDER BY id ASC") or die(mysql_error());
		$count = mysql_num_rows($q);
		$oOutput = $output;
		for($i = 0;$i < $count;$i++):
			mysql_data_seek($q, $i);
			$arr = mysql_fetch_array($q);
			$output = $oOutput;
			$answer = str_replace("\n", "<BR>", $arr['answer']);
			$answer = str_replace("\r", "<BR>", $answer);
			$answer = rtrim(trim($answer));
			$question = rtrim(trim($arr['question']));
			$output = str_replace($qTag, cutThanks($question), $output);
			$output = str_replace($aTag, cutThanks($answer), $output);
			$output = str_replace("[i]", $i, $output);
			$output = str_replace("", "\"", $output);
			$output = str_replace("", "\"", $output);
			echo $output;
		endfor;
	endif;
}

function cutThanks($value) {
	$value = str_replace("Thank you,", "", $value);
	$value = str_replace("tryedge.com Administration cs@tryedge.com", "", $value);
	$value = str_replace("tryedge.com Administration", "", $value);
	$value = str_replace("Thanks,", "", $value);
	$value = str_replace("Thanks", "", $value);	
	return $value;
}
#######################################################################################
#######################################################################################

function checkUserSpam($username) {
	if(getCount("SELECT COUNT(id) FROM spamUsers WHERE username = ".quote_smart($username)."", "COUNT") > 0) {
		$information = getValue("SELECT reasonAdded FROM spamUsers WHERE username = ".quote_smart($username)."");
		echo "This user account has been disabled and purged from our system and is no longer active due to spam complaints received against this users account.<BR><BR><BR>";
		echo parseMessage($information, "html");
		exit;
	} else {
		return FALSE;
	}
}

function removeWordCharacters($text) {
	$text = str_replace('“', '"', $text);
	$text = str_replace('”', '"', $text);
	$text = str_replace('‘', '\'', $text);
	$text = str_replace('’', '\'', $text);
	$text = str_replace('…', '...', $text);
	$text = str_replace('—', '--', $text);
	$text = str_replace('™', '(TM)', $text);
	$text = str_replace('¦', '|', $text);
	$text = str_replace('€', '€', $text);
	$text = str_replace('â', 'a', $text);
	
	return $text;
}
//function mysql_date($timestamp='EMPTY') {
	//if($timestamp === 'EMPTY') $timestamp = time();
	//return date("Y-m-d H:i:s",$timestamp);
//}

?>