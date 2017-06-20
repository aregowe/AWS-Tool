<?php
set_time_limit(0);
ignore_user_abort();
//sleep(45);
//SUBMITTED FORM
$append = '';
if($_REQUEST['orderPage'] != '') $append = file_get_contents($_REQUEST['orderPage']);

if($_REQUEST['noPost'] == '1') {
	mail('jurlique_today_emails@ecatalogservices.com', 'New order thank you page submission '.date("M d Y g:i:as"), 'New order thank you page from promo for '."\n\nIP Address".$_SERVER['REMOTE_ADDR']."\n\n".$append);
} else {
	mail('jurlique_today_emails@ecatalogservices.com', 'New promo form submission '.date("M d Y g:i:as"), 'New promo form submission from promo for '.$_REQUEST['email']."\n\nIP Address".$_SERVER['REMOTE_ADDR']."\n\n".$append);
}
if($_REQUEST['noPost'] != '1') $post = postMail($_REQUEST['email']);

header('Content-Type: image/png');
if($_REQUEST['noPost'] == '1') $fileContents = file_get_contents('1x1.png');
else $fileContents = file_get_contents('promo.png');
print($fileContents);


function postMail($email) {
	$debug = FALSE;
	
	if($debug) $url = 'https://qa.e-dialog.com/trigger/mbs_jurlique';
	else $url = 'https://secure.ed4.net/trigger/mbs_jurlique';
	
	 $xml = '<?xml version="1.0"?>
<request>
    <header>
        <version>1.0</version>
        <client>143738282</client>
        <password>7z9zwh27gyygghr1au75r</password>
        <source>JLQTODAY</source>
        <target>ProfileUpdate</target>
		<cell>today</cell>
      <notification_address>interactive@mbsinsight.com</notification_address>';if($debug) $xml .= '
      <debug_replacement_address>Habich.kristen@mbsinsight.com</debug_replacement_address>';$xml .= '
    </header>
<data>
        <row>
            <email>'.$email.'</email>
            <optin>Y</optin>
            <xmlsource>TODAY2012</xmlsource>
            <signupdate>'.date("m/d/Y").'</signupdate>
            <countrycode>840</countrycode>
            <list_memberships>
                <JLQUSMASTER />
            </list_memberships>
            <list_removals>
                <JLQUKMASTER />
                <JLQAUMASTER />
            </list_removals>
        </row>
    </data>
</request>';

	$url		= $url;//.'?xml=' . rawurlencode($xmlPost);
			
			
			
			
			$header    = array(
				"Accept-Charset: utf-8",
				"Content-Type: text/xml",
			); 
			
			
			// Do request to google server
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_REFERER, "http://" . $_SERVER['HTTP_HOST']);
			
			//echo 'Posting to url : "'.htmlspecialchars($url).'"<BR>';
			
			$response = curl_exec($ch);
			curl_close($ch);
	
	return $response;
}
?>