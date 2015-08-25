<?php
$dir = 0;
    while (!file_exists(str_repeat('../', $dir).'wp-load.php'))
        if (++$dir > 16) exit;
		$load_url = str_repeat('../', $dir).'wp-load.php';
	include_once($load_url);
	include_once( 'functions.php' );

//Check if the request came from Plimus IP
$plimusIps = array("93.86.148.110", 
	"62.219.121.253", "62.216.234.216", "62.216.234.218", "62.216.234.219", "62.216.234.220",
	"209.128.93.248", "209.128.93.229", "209.128.93.98", "209.128.93.230", "209.128.93.245", 
	"209.128.93.104", "209.128.93.105", "209.128.93.107", "209.128.93.108", "209.128.93.242", 
	"209.128.93.243", "209.128.93.254","209.128.104.18", "209.128.104.19", "209.128.104.20", 
	"209.128.104.21", "209.128.104.22", "209.128.104.23", "209.128.104.24", "209.128.104.25", 
	"209.128.104.26", "209.128.104.27", "209.128.104.28", "209.128.104.29", "209.128.104.30", 
	"209.128.104.31", "209.128.104.32", "209.128.104.33", "209.128.104.34", "209.128.104.35", 
	"209.128.104.36", "209.128.104.37",
	"72.20.107.242", "72.20.107.243", "72.20.107.244", "72.20.107.245", "72.20.107.246",
	"72.20.107.247", "72.20.107.248", "72.20.107.248", "72.20.107.250", 
	"99.186.243.9", "99.186.243.10", "99.186.243.11", "99.186.243.12", "99.186.243.13", 
	"99.180.227.233", "99.180.227.234", "99.180.227.235", "99.180.227.236", "99.180.227.237",
	"127.0.0.1","localhost");

if (array_search($_SERVER['REMOTE_ADDR'], $plimusIps) != false)
 {

	$pn_request = array(
		'transactionType' => $_REQUEST['transactionType'],
		'testMode' => $_REQUEST['testMode'],
		'referenceNumber' => $_REQUEST['testMode'],
		'originalReferenceNumber' => $_REQUEST['testMode'],
		'paymentMethod' => $_REQUEST['paymentMethod'],
		'creditCardType' => $_REQUEST['creditCardType'],
		'transactionDate' => $_REQUEST['transactionDate'],
		'untilDate' => $_REQUEST['untilDate'],
		'productId' => $_REQUEST['productId'],
		'productName' => $_REQUEST['productName'],
		'contractId' => $_REQUEST['contractId'],
		'contractName' => $_REQUEST['contractName'],
		'contractOwner' => $_REQUEST['contractOwner'],
		'oldProductId' => $_REQUEST['oldProductId'],
		'oldContractId' => $_REQUEST['oldContractId'],
		'newProductId' => $_REQUEST['newProductId'],
		'newContractId' => $_REQUEST['newContractId'],
		'contractPrice' => $_REQUEST['contractPrice'],
		'quantity' => $_REQUEST['quantity'],
		'currency' => $_REQUEST['currency'],
		'addCD' => $_REQUEST['addCD'],
		'coupon' => $_REQUEST['coupon'],
		'couponValue' => $_REQUEST['couponValue'],
		'referrer' => $_REQUEST['referrer'],
		'accountId' => $_REQUEST['accountId'],
		'title' => $_REQUEST['title'],
		'firstName' => $_REQUEST['firstName'],
		'lastName' => $_REQUEST['lastName'],
		'username' => $_REQUEST['username'],
		'company' => $_REQUEST['company'],
		'address1' => $_REQUEST['address1'],
		'address2' => $_REQUEST['address2'],
		'city' => $_REQUEST['city'],
		'state' => $_REQUEST['state'],
		'country' => $_REQUEST['country'],
		'zipCode' => $_REQUEST['zipCode'],
		'email' => $_REQUEST['email'],
		'workPhone' => $_REQUEST['workPhone'],
		'extension' => $_REQUEST['extension'],
		'mobilePhone' => $_REQUEST['mobilePhone'],
		'homePhone' => $_REQUEST['homePhone'],
		'faxNumber' => $_REQUEST['faxNumber'],
		'licenseKey' => $_REQUEST['licenseKeyz'],
		'shippingFirstName' => $_REQUEST['shippingFirstName'],
		'shippingLastName' => $_REQUEST['shippingLastName'],
		'shippingAddress1' => $_REQUEST['shippingAddress1'],
		'shippingAddress2' => $_REQUEST['shippingAddress2'],
		'shippingCity' => $_REQUEST['shippingCity'],
		'shippingState' => $_REQUEST['shippingState'],
		'shippingCountry' => $_REQUEST['shippingCountry'],
		'shippingZipCode' => $_REQUEST['shippingZipCode'],
		'remoteAddress' => $_REQUEST['remoteAddress'],
		'shippingMethod' => $_REQUEST['shippingMethod'],
		'couponCode' => $_REQUEST['couponCode'],
		'invoiceAmount' => $_REQUEST['invoiceAmount'],
		'invoiceAmountUSD' => $_REQUEST['invoiceAmountUSD'],
		'invoiceChargeCurrency' => $_REQUEST['invoiceChargeCurrency'],
		'invoiceChargeAmount' => $_REQUEST['invoiceChargeAmount'],
		'invoiceInfoURL' => $_REQUEST['invoiceInfoURL'],
		'promoteContractsNum' => $_REQUEST['promoteContractsNum']
	);
		
		$promotions = array();
		for($i = 0; $i < $pn_request['promoteContractsNum']; $i++){
			$promotions[$i] = array(
				"promoteContractId$i" => $_REQUEST["promoteContractId$i"],
				"promoteContractName$i" => $_REQUEST["promoteContractName$i"],
				"promoteContractOwner$i" => $_REQUEST["promoteContractOwner$i"],
				"promoteContractPrice$i" => $_REQUEST["promoteContractPrice$i"],
				"promoteContractQuantity$i" => $_REQUEST["promoteContractQuantity$i"],
				"promoteContractLicenseKey$i" => $_REQUEST["promoteContractLicenseKey$i"]
				);
		}
		
		
		global $wpdb;
		$id = $wpdb->get_var("SELECT pn_id FROM ".$wpdb->prefix."plimus_pn ORDER BY pn_id DESC");
		$id = $id ? $id : 0;
		$transaction_id = $_REQUEST['productId'].$id;
		$plimus_pn = $wpdb->prefix . "plimus_pn";
		if ($wpdb->get_var("show tables like '$plimus_pn'") != $plimus_pn) {
			$create_statement .= "CREATE TABLE " . $plimus_pn . " (
				pn_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				pn_orn bigint(20) UNSIGNED NOT NULL DEFAULT 0,
				pn_meta_key varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
				pn_meta_value longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
				PRIMARY KEY (pn_id)
			);";
			if($create_statement != ''){
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				$db_return = dbDelta($create_statement);
				if($db_return){
				foreach($pn_request as $key => $val){
					$rows_affected = $wpdb->insert($wpdb->prefix . "plimus_pn", array('pn_orn' => $transaction_id,'pn_meta_key' => $key,'pn_meta_value' => $val));
				}	
				foreach($promotions as $key => $val){
					foreach($val as $or_key  => $or_val)
						$rows_affected = $wpdb->insert($wpdb->prefix . "plimus_pn", array('pn_orn' => $transaction_id,'pn_meta_key' => $or_key,'pn_meta_value' => $or_val));
				}
					if(pn_is_ntf_mail_service_on() && $pn_request['transactionType'] == "CHARGE"){
						pn_send_email_notification($pn_request['email'], $pn_request['title'].' '.$pn_request['firstName'].' '.$pn_request['lastName'], $pn_request['productName'], $pn_request['contractName'], $pn_request['transactionDate'] );
					}
				}
			}
		}
		else {

			foreach($pn_request as $key => $val){
				$rows_affected = $wpdb->insert($wpdb->prefix . "plimus_pn", array('pn_orn' => $transaction_id,'pn_meta_key' => $key,'pn_meta_value' => $val));
			}	
			foreach($promotions as $key => $val){
				foreach($val as $or_key  => $or_val)
					$rows_affected = $wpdb->insert($wpdb->prefix . "plimus_pn", array('pn_orn' => $transaction_id,'pn_meta_key' => $or_key,'pn_meta_value' => $or_val));
			}
			if(pn_is_ntf_mail_service_on() && $pn_request['transactionType'] == "CHARGE"){
				pn_send_email_notification($pn_request['email'], $pn_request['title'].' '.$pn_request['firstName'].' '.$pn_request['lastName'], $pn_request['productName'], $pn_request['contractName'], $pn_request['transactionDate'] );
			}
		}
	if($pn_request['username'] != '' && pn_is_register_user_on() && $pn_request['transactionType'] == "CHARGE"){
			$pn_user = $pn_request['email'];
            $pn_email = $pn_request['email'];
            $pass_u = pn_create_user($pn_user, $pn_email, $pn_request['firstName'], $pn_request['lastName'], $pn_request['title']);
				if($pass_u){
				logToFile($pn_user.', '.$pn_email.', '.$pass_u);
                $m_sub = 'Account information';
                $m_msg = html_entity_decode(get_option('pn_email_template'));
                        $m_user = $pn_user;
                        $m_pass = $pass_u;
                        $m_login = get_bloginfo('wpurl') .'/wp-login.php';
                        $tmp_from = array('{USER_NAME}', '{PASSWORD}', '{LOGIN_URL}');
                        $tmp_to = array($m_user, $m_pass, $m_login);
                $m_msg = str_replace($tmp_from, $tmp_to, $m_msg);
                $m_headers  = 'MIME-Version: 1.0' . "\r\n";
                $m_headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $m_headers .= 'From: "Administrator" '. get_bloginfo('admin_email') . "\r\n";
                if(pn_is_reg_mail_service_on()){
					mail($pn_request['email'], $m_sub, $m_msg, $m_headers);
				}
            }
        }
	}
	function pn_create_user($user_name, $user_email, $first_name, $last_name, $title){
        require_once(ABSPATH . WPINC . '/pluggable.php'); //WP v3.0.1 - wp_generate_password
        require_once(ABSPATH . WPINC . '/registration.php'); //WP v3.0.1 - username_exists, email_exists
        if(email_exists($user_email)) return false;
        $user_id = username_exists( $user_name );
		if ( !$user_id ) {
            $random_password = wp_generate_password( 12, false );
			$user_role = get_option('pn_register_role') ? get_option('pn_register_role') : 'subscriber';
            $new_user = array();
            $new_user['user_pass'] = $random_password;
            $new_user['user_login'] = $user_name;
            $new_user['user_email'] = $user_email;
            $new_user['display_name'] = $title.' '.$first_name.' '.$last_name;
            $new_user['first_name'] = $first_name;
            $new_user['last_name'] = $last_name;
            $new_user['role'] = $user_role;
			if($id = wp_insert_user($new_user)){
                return $random_password;
            }else{
                return false;
            }
        } else {
            return false;
        }
	}
	function logToFile( $msg){
        $filename='log.txt';
        // open file
        $fd = fopen($filename, "a");
        // write string
        fwrite($fd, $msg . "\n");
        // close file
        fclose($fd);
    }
?>