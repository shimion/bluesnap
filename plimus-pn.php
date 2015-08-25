<?php
/*
Plugin Name: Bluesnap For WordPress
Plugin URI: http://www.prelovac.com/wordpress-plugins/plimus-for-wordpress
Description: <a href="http://www.Bluesnap.com">Bluesnap</a> IPN integration with WordPress. Manage IPN notification and register buyers as blog users. Developed by <a href="http://www.prelovac.com">Prelovac Media</a>.
Version: 1.1.3
Author: Vladimir Prelovac
Author URI: http://www.prelovac.com/vladimir
*/

if( !session_id() )
	session_start();

define('PLIMUS_IPN_VERSION', '1.1.2');

include_once( 'functions.php' );

function pn_activate(){
	global $wpdb;
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
		}
	}
}
if ( function_exists('register_activation_hook') )
    register_activation_hook( __FILE__, 'pn_activate' );

if(is_admin()){
	global $pn_plugin_url, $pn_plugin_dir;
	
	if( defined('WP_PLUGIN_URL') )
		$pn_plugin_url = WP_PLUGIN_URL.'/'. basename ( dirname( __FILE__ ) );
	else 
		$pn_plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
	
	if( defined('WP_PLUGIN_DIR') )
		$pn_plugin_dir = WP_PLUGIN_URL.'/'. basename ( dirname( __FILE__ ) );
	else
		$pn_plugin_dir = ABSPATH.'/'. PLUGINDIR . basename ( dirname( __FILE__ ) );
		
	function pn_admin_menu_pages(){
		global $pn_plugin_url;
		
		add_menu_page('Bluesnap Payment Notifications', 'Bluesnap IPN', 'update_core', 'plimus-payment-notifications', 'pn_handle_options_page', $pn_plugin_url.'/images/pn_plugin_icon.png', 100);
		$option_page = add_submenu_page( 'plimus-payment-notifications', 'Options', 'Options', 'update_core', 'pn-options', 'pn_handle_pdf_page');
		add_action('admin_head-'.$option_page, 'pn_add_meta_tags');
	}

	function pn_handle_pdf_page(){
		global $pn_plugin_url;
		
		$action_url = $_SERVER['REQUEST_URI'];
		$run_file_path = $pn_plugin_url."/plimusipn.php";
		if(isset( $_REQUEST['clear_all'] )){
			$plimus_pn = $wpdb->prefix . "plimus_pn";
			$query_db = "DROP TABLE IF EXISTS $plimus_pn";
			$drop = $wpdb->query($query_db);
		}
		
		if(isset( $_REQUEST['save_settings'] ) != ''){
			$pn_options = array();
			$pn_options['pn_email_template'] = stripslashes(htmlspecialchars($_POST['pn_email_tmp']));
			$pn_options['pn_reg_email_on'] = isset( $_POST['pn_reg_mail_on'] ) && $_POST['pn_reg_mail_on'] == 'on'? 1: 0;
			$pn_options['pn_reg_user_on'] = isset( $_POST['pn_reg_user_on'] ) && $_POST['pn_reg_user_on'] == 'on'? 1: 0;
			$pn_options['pn_register_role'] = isset( $_POST['pn_register_role'] ) ? $_POST['pn_register_role'] : 'subscriber';
			$pn_options['pn_tpl_send'] = isset( $_POST['pn_send_mail_on'] ) && $_POST['pn_send_mail_on'] == 'on'? 1: 0;
			$pn_options['pn_tpl_name'] = isset( $_POST['your_name'] ) ? $_POST['your_name'] : '';
			$pn_options['pn_tpl_email'] = isset( $_POST['your_email'] ) ? $_POST['your_email'] : '';
			$pn_options['pn_tpl_email_text'] = stripslashes(htmlspecialchars($_POST['email_tmp']));
			$pn_options['pn_tpl_email_subject_text'] = stripslashes(htmlspecialchars($_POST['tpl_subject_text']));
					foreach($pn_options as $key => $val){
				add_option($key, $val) or update_option($key, $val);
			}
			
		}
		if( isset( $_REQUEST['refresh'] ) != ''){
			$pn_options['pn_tpl_send'] = $_POST['pn_send_mail_on'] == 'on'? 1: 0;
			$pn_options['pn_tpl_name'] = $_POST['your_name'];
			$pn_options['pn_tpl_email'] = $_POST['your_email'];
			$pn_options['pn_tpl_email_text'] = stripslashes(htmlspecialchars($_POST['email_tmp']));
			$pn_options['pn_tpl_email_subject_text'] = stripslashes(htmlspecialchars($_POST['tpl_subject_text']));
					foreach($pn_options as $key => $val){
				add_option($key, $val) or update_option($key, $val);
			}
			pn_send_email_notification($_REQUEST['your_email'], $_REQUEST['your_name'], 'Test Product Name', 'Test Contract Name','NOW', true);
		}
		$pn_options = pn_get_options();
			if(!empty($pn_options)){
				$pn_email_on = $pn_options['pn_tpl_send'] == 1? 'checked': '';
				$pn_reg_email_on = $pn_options['pn_reg_email_on'] == 1? 'checked': '';
				$pn_reg_user_on = $pn_options['pn_reg_user_on'] == 1? 'checked': '';
				$pn_your_name = $pn_options['pn_tpl_name'] != '' ? $pn_options['pn_tpl_name']: 'Your Name';
				$pn_your_email = $pn_options['pn_tpl_email'] != '' ? $pn_options['pn_tpl_email']: 'Your Email';
				$pn_tpl_reg_email_text = html_entity_decode($pn_options['pn_email_template'] != '' ? $pn_options['pn_email_template']: "Account successfully created.<br />\n<br />\nUser name: {USER_NAME}<br />\nPassword: {PASSWORD}<br />\nLogin URL: {LOGIN_URL}\n<br />\n<br />\n");			
				$pn_tpl_reg_email_subject = html_entity_decode($pn_options['pn_tpl_email_subject_text'] != '' ? $pn_options['pn_tpl_email_subject_text']: 'Insert Your Registration Email Subject Here');
				$pn_tpl_email_text = html_entity_decode($pn_options['pn_tpl_email_text'] != '' ? $pn_options['pn_tpl_email_text']: 'Insert Your Notification Email Template Here');
		}
		if( !function_exists('wp_dropdown_roles') )
			include_once ABSPATH . 'wp-admin/user-new.php';
		?>
		<div class="wrap" style="min-width:1000px !important;">
			<h2><a href="http://www.Bluesnap.com" title="Bluesnap - Take charge" target="_blank"><img src="<?php echo $pn_plugin_url; ?>/images/plimus_logo.png" alt="Bluesnap" id="plimus-logo" /></a>for WordPress Options</h2><br />
			<h3>Bluesnap IPN URL</h3>
			<i id="note">* This is the path to Bluesnap IPN file. Copy and paste this URL to General Settings page in your Bluesnap dashboard.</i><br /><br />
			<div id="plimus_run_path"><?php echo $run_file_path; ?></div><br /><br />
			<h3 id="notification">Registration Email</h3>
			<form action="<?php echo $action_url; ?>" method="post" id="settings_form">
				<input type="checkbox" name="pn_reg_user_on" <?php echo $pn_reg_user_on; ?> /> Register new buyers as 
					<select name="pn_register_role" id="adduser-role"> 
						<?php wp_dropdown_roles( get_option('pn_register_role')!='' ? get_option('pn_register_role'): 'subscriber' ); ?> 
					</select>
				<input type="submit" name="save_settings" id="save_settings" value="Save Settings" class="button-primary"/><br />
				<input type="checkbox" name="pn_reg_mail_on" <?php echo $pn_reg_email_on; ?> /> Send registration email to new users
					<br /><br /><i id="note">* you may use <code>{USER_NAME}</code> <code>{PASSWORD}</code> <code>{LOGIN_URL}</code> codes that will be replaced with new user data.</i><br />
				<textarea cols="90" rows="10" name="pn_email_tmp" id="pn_email_tmp" ><?php echo $pn_tpl_reg_email_text ?></textarea>
				<div id="legend"></div>
				<div style="clear:both"></div>
				<h3 id="notification">Purchase Notification Email</h3>
				<input type="checkbox" name="pn_send_mail_on" <?php echo $pn_email_on; ?> /> Turn purchase notification email on &nbsp;&nbsp;&nbsp;
				<input type="submit" name="save_settings" id="save_settings" value="Save Settings" class="button-primary"/><br />
				<div id="template_exp">
					<i id="note">Set your subject, name and email, that will appear in e-mail header.</i>
				  <br /><i id="note">* you may use <code>{NAME}</code> <code>{PRODUCT}</code> <code>{CONTRACT}</code> <code>{DATE}</code> codes that will be replaced with data from the invoice.</i><br />
				</div>
				<input type="text" name="tpl_subject_text" id="tpl_subject_text" size="120" value="<?php echo $pn_tpl_reg_email_subject; ?>" /><br />
				<input type="text" name="your_name" id="your_name" size="30" value="<?php echo $pn_your_name; ?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="text" name="your_email" id="your_email" size="60" value="<?php echo $pn_your_email; ?>" /><br />
				<div id="template_exp">
					
				</div>
				<textarea cols="90" rows="10" name="email_tmp" id="email_tmp" ><?php echo $pn_tpl_email_text; ?></textarea>
				
						  
			</form><br />
		</div>
		</div>
	<h5><a href="http://www.prelovac.com" title="Plugin by Prelovac Media" ><img src="<?php echo $pn_plugin_url;?>/images/logo.png"  style="margin-top: 30px;"/></a> </h5>
	<br clear="both">
	<?php
	}
	
	function pn_handle_options_page(){
		session_cache_limiter( FALSE );
		global $wpdb, $pn_plugin_url, $pn_plugin_dir;
		$action_url = $_SERVER['REQUEST_URI'];
		
		$unsent_mails = array();
		if(isset($_REQUEST['pnd'])){
			$plimus_pn = $wpdb->prefix . "plimus_pn";
			$id = $_REQUEST['pnd'];
			$query_db = "DELETE FROM $plimus_pn WHERE pn_orn = $id";
			$delete = $wpdb->query($query_db);
		}
		$search_term = isset($_POST['search_item']) ? trim($_POST['search_item']) : '' ;
		$global_days = null;
		if( isset($_REQUEST['filter_all']) && !empty($_REQUEST['filter_all']) && !empty($search_term) && $search_term != 'Filter By Keyword'){
			$filter = isset($_SESSION['filter_by_type']) ? $_SESSION['filter_by_type'] : 'date';
			$global_days = isset($_REQUEST['filter_by_dates']) ? $_REQUEST['filter_by_dates'] : $_SESSION['filter_by_dates'];
			$terms = explode(" ", $search_term);
			$search_where = ' WHERE pn_meta_value LIKE ';
			foreach($terms as $term){
				$search_where .= "('%{$term}%') OR pn_meta_value LIKE ";
			}
			$search_where = substr_replace($search_where, '', -22);
			$search_data = $wpdb->get_results("SELECT pn.pn_orn FROM {$wpdb->prefix}plimus_pn as pn $search_where", OBJECT_K);
			if(!empty($search_data))
				$search_query = " WHERE pn.pn_orn IN ('".implode("','", array_keys($search_data))."');";
			else
				$search_query = '';
			
		}else {
			$filter = 'date';
			if(isset($_REQUEST['show']) && $_REQUEST['show'] == 'all')
				$global_days = 0;
				
			unset($_SESSION['filter_by_type']);
			unset($_SESSION['filter_by_dates']);
			$search_query = '';
		}
		
		if($search_query != ''){
			$amount_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}plimus_pn as pn");
			$total_amount = array();
			if(!empty($amount_data)){
				foreach($amount_data as $key => $data){
					if($data->pn_meta_key == 'invoiceAmountUSD' || $data->pn_meta_key == 'transactionDate')
					$total_amount[$data->pn_orn][$data->pn_meta_key] = $data->pn_meta_value;
				}
			}
			$this_month = 0;
			$last_month = 0;
			$total_sum = 0;
			foreach($total_amount as $key => $data){
				$total_sum += (double)$data['invoiceAmountUSD'];
				
				if(date('mY') == date('mY', strtotime($data['transactionDate']))){
						$this_month += (double)$data['invoiceAmountUSD'];
					}
					$lmonth = date('m')-1;
					if(date(''.$lmonth.'Y') == date('mY', strtotime($data['transactionDate']))){
						$last_month += (double)$data['invoiceAmountUSD'];
					}
			}
		}
		
		if(isset($_REQUEST['filter_all'])){
			$filter = isset($_POST['filter_by_type']) ? $_POST['filter_by_type'] : $_SESSION['filter_by_type'];
			$_SESSION['filter_by_type'] = $filter;
		}
		$selected[$filter] = 'selected';
		
		
		if(isset($_REQUEST['filter_all']) && isset($_REQUEST['filter_by_dates']) && $_REQUEST['filter_by_dates'] != '0'){
			$global_days = isset($_REQUEST['filter_by_dates']) ? $_REQUEST['filter_by_dates'] : $_SESSION['filter_by_dates'];
			$_SESSION['filter_by_dates'] = $global_days;
		}
		
		$transaction_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}plimus_pn as pn $search_query"));
		$this_month = 0;
		$last_month = 0;
		$total_sum = 0;
		
		if(!empty($transaction_data)){
			$default_footer = '</div>';
			$table_rows = array();
			$products = array();
			$products_date = array();
			$remove_row = array();
			
			$transactions = array();
			foreach($transaction_data as $key => $data){
				$transactions[$data->pn_orn][$data->pn_meta_key] = $data->pn_meta_value;
			}
			$firt_entry = 0;
			$j = 0;
			
			if(!empty($transactions)){
				foreach($transactions as $key => $data_array){
					$item = '';
					$promotions_array = array();
					
					$days_past = (int)((strtotime('today')/86400) - (strtotime($data_array['transactionDate'])/86400));
					$firt_entry = $days_past > $firt_entry ? $days_past : $firt_entry;
						
					if(!empty($data_array)){
						//$key++;
						$invoice_url = preg_match('/http\:\/\//i', $data_array['invoiceInfoURL']) ? $data_array['invoiceInfoURL'] : 'http://'.$data_array['invoiceInfoURL'] ;
						$company = $data_array['company'] != '' ? "<font style='font-weight: bold;' >Coustomer company: </font>{$data_array['company']} <br />" : '';
						$address1 = $data_array['address1'] != '' ? "<font style='font-weight: bold;' >Coustomer address one: </font>{$data_array['address1']} {$data_array['zipCode']},{$data_array['city']}, {$data_array['state']}, {$data_array['country']} <br />" : '';
						$address2 = $data_array['address2'] != '' ? "<font style='font-weight: bold;' >Coustomer address two: </font>{$data_array['address2']} <br />" : '';
						$contact = $data_array['email'] != '' ? "<font style='font-weight: bold;' >Coustomer contact: </font> {$data_array['workPhone']} / {$data_array['extension']} " : '';
						$mobile_phone = $data_array['mobilePhone'] != '' ? "<font style='font-weight: bold;' > mobile: </font> {$data_array['mobilePhone']}" : '' ;
						$home_phone = $data_array['homePhone'] != '' ? "<font style='font-weight: bold;' > home: </font>{$data_array['homePhone']}" : '' ;
						$fax = $data_array['faxNumber'] != '' ? "<font style='font-weight: bold;' > fax: </font>{$data_array['faxNumber']} <br />" : '' ;
						$coustomer_cc = $data_array['creditCardType'] != '' ? "<font style='font-weight: bold;' > Credit card: </font>{$data_array['creditCardType']} <br />" : '' ;
						$ship_to = $data_array['shippingFirstName'] != '' || $data_array['shippingLastName'] != ''? " <font style='font-weight: bold;' >Shiping To: </font>{$data_array['shippingFirstName']} {$data_array['shippingLastName']} {$data_array['shippingZipCode']} {$data_array['shippingCity']} {$data_array['shippingState']} {$data_array['shippingCountry']}" : '' ;
						$shippingMethod = $data_array['shippingMethod'] != '' ? " <font style='font-weight: bold;' > shiping method: </font> {$data_array['shippingMethod']}<br />" : '' ;
						$invoiceChargeAmount = $data_array['invoiceChargeAmount'] != '' ? " <font style='font-weight: bold;' >Invoice Amount: </font> {$data_array['invoiceChargeAmount']} {$data_array['invoiceChargeCurrency']} ($ {$data_array['invoiceAmountUSD']}) <a href='{$invoice_url}' target='_blank'>{$data_array['invoiceInfoURL']}</a><br />" : '' ;
						$coupon = $data_array['coupon'] != '' ? "<font style='font-weight: bold;' >Used a coupon: </font> {$data_array['coupon']} " : '' ;
						$couponCode = $data_array['couponCode'] != '' ? " <font style='font-weight: bold;' > code: </font>  {$data_array['couponCode']} " : '' ;
						$couponValue = $data_array['couponValue'] != '' ? "  <font style='font-weight: bold;' >amount: </font>  {$data_array['couponValue']} {$data_array['invoiceChargeCurrency']} " : '' ;
						$referrer = $data_array['referrer'] != '' ? " <font style='font-weight: bold;' > referer: </font>  {$data_array['referrer']} <br />" : '' ;
						$table_coupon = $data_array['coupon'] != '' ? "{$data_array['coupon']} ({$data_array['couponCode']})" : '';
						$coupon = (double) str_replace(',', '', $data_array['couponValue']);
						
						$promotions_html = '';
						$currency = $data_array['currency'];
						
						$tr_date = str_replace(' ', '/',substr_replace((String) $data_array['transactionDate'], '', -9));
						$charge = 0;
						if($tr_date != '') {
							$product_name = $data_array['productName'];
							
							if($filter == 'contract')
								$product_name = $data_array['contractName'];
							
							$charge = str_replace(',', '.', $data_array['invoiceAmountUSD']);
							$products[$tr_date][$key] = array(
								'amount' => isset($products[$tr_date][$product_name]) && in_array($transactiontype, array('CHARGE', 'RECURRING') )? (double)$products[$tr_date][$product_name]+(double)$charge : 0,
								'name' => $product_name
							);
						}
						if($data_array['promoteContractsNum'] > 0){
							for($i = 0; $i < $data_array['promoteContractsNum']; $i++){
							$promotions_html .= 
								'<font style="font-weight: bold;" > Promotion: </font> '.$data_array["promoteContractName$i"].'
								<font style="font-weight: bold;" > owner: </font> '.$data_array["promoteContractOwner$i"].'
								<font style="font-weight: bold;" > price: </font> '.$data_array["promoteContractPrice$i"].'
								<font style="font-weight: bold;" > qty: </font> '.$data_array["promoteContractQuantity$i"].'
								<font style="font-weight: bold;" > licence: </font> '.$data_array["promoteContractLicenseKey$i"].'<br />';
							}
						}
						$remove_row[$tr_date][] = $key;
						
						$productname = isset($data_array['productName']) ? $data_array['productName'] : '';
						$contractname = isset($data_array['contractName']) ? $data_array['contractName'] : '';
						$quantity = isset($data_array['quantity']) ? $data_array['quantity'] : '';
						$invoiceamountusd = isset($data_array['invoiceAmountUSD']) ? $data_array['invoiceAmountUSD'] : '';
						$paymentmethod = isset($data_array['paymentMethod']) ? $data_array['paymentMethod'] : '';
						$transactiontype = isset($data_array['transactionType']) ? $data_array['transactionType'] : '';
						$prtitle = isset($data_array['title']) ? $data_array['title'] : '';
						$firstname = isset($data_array['firstName']) ? $data_array['firstName'] : '';
						$lastname = isset($data_array['lastName']) ? $data_array['lastName'] : '';
						$premail = isset($data_array['email']) ? $data_array['email'] : '';
						$transactiondate = isset($data_array['transactionDate']) ? $data_array['transactionDate'] : '';
						
						$table_rows[$key] = "<tr><td>{$productname}</td><td>{$contractname}</td><td>{$quantity}</td>
							<td>{$invoiceamountusd}</td><td> $table_coupon</td><td>{$paymentmethod}</td>
							<td>{$transactiontype}</td><td>{$prtitle} {$firstname} {$lastname}</td><td>{$premail}</td>
							<td>{$transactiondate}</td><td id='more-info-{$key}'><a href='javascript: void(0)' title='More Info' onclick='showMoreInfo({$key})'>More</a> </td>
							<td style='background: #f9f9f9;'>
								<div class='more-info-panel' id='mi-{$key}' style='display: none;'>
									{$company}{$address1}{$address2}{$contact}{$mobile_phone}{$home_phone}{$fax}{$coustomer_cc}{$ship_to}{$shippingMethod}{$invoiceChargeAmount}
									{$coupon}{$couponCode}{$couponValue}{$referrer}
									".$promotions_html."
								</div>
							</td></tr>";
							
						$un_date = str_replace(' ', '/',substr_replace($transactiondate, '', -9));
						$unsent_mails[] = array($un_date, $premail);
						
					}
					$j++;
				}
			}
			
			if($search_query == ''){
				
				foreach($products as $date => $data){
					foreach($data as $key => $value){
						$total_sum += (double)$value['amount'];
						if(date('mY') == date('mY', strtotime($date))){
							$this_month += $value['amount'];
						}
						$lmonth = $current_month = date('m')-1;
						if(date(''.$lmonth.'Y') == date('mY', strtotime($date))){
							$last_month += $value['amount'];
						}
					}
				}
			}
			// sort by days
			$selected_date = array(
				0 => '',
				7 => '',
				15 => '',
				30 => '',
				60 => '',
				120 => ''
			);
			//if($filter == 'date'){
			$has_results = false;
			$global_days = $global_days == 0 ? $firt_entry : $global_days;
			$selected_date[$global_days] = 'selected="selected"';
			while(!$has_results){
				$results = array();
				$data_dates = array();
				
				
				for($i = 0; $i < (int)$global_days; $i++){
					$date = date('m/d/Y', time()-($i*86400));
					$data_dates[$i] = $date;
				}
				$keys = array_keys($products);
				
				foreach($data_dates as $date){
					if(!array_key_exists($date, $products)){
						$products[$date][] = array();
						$results[$date] = false;
						continue;
					}
					$results[$date] = true;
				}
				foreach($results as $res){
					if($res){
						$has_results = true;
						break;
					}
				}
				if(!$has_results){
					if($global_days = 0)
						$global_days = 7;
					else if($global_days = 7)
						$global_days = 15;
					else
						$global_days = 2 * $global_days;
				}
				if($global_days > 120)
					break;
			}
				if($global_days != 0 && false){
					foreach($keys as $key){
						if(!in_array($key, $data_dates) && isset($remove_row[$key]) ){
							foreach($remove_row[$key] as $row_id){
								unset($table_rows[$row_id]);
							}
							unset($products[$key]);
						}
					}
				}
			
				foreach((array)$products as $date => $data){
					$date_key = strtotime($date);
					foreach($data as $key => $value){
						$products[$date_key][$key] = $value;
					}
					unset($products[$date]);
				}
				ksort($products);
				foreach($products as $date => $data){
					$date_key = date('m/d/Y', $date);
					foreach($data as $key => $value){
						$products[$date_key][$key] = $value;
					}
					unset($products[$date]);
				}				
			//}
			// end sort by days
		
			/* json */
				require_once('pn-chart/OFC/OFC_Chart.php');
				$title_is = '';
				$values = array();
				$tips = array();
				$bar = new OFC_Charts_Bar();
				$max_price = 0;
				$amount = array();
				$array_keys = array_keys($products);
				
				foreach($products as $date => $data){
					if($filter == 'date'){
						
						$price = array();
						foreach($data as $key => $value){
							if( !isset($price[$date]) )
								$price[$date] = 0;
								
							$price[$date] += isset( $value['amount'] ) ? $value['amount'] : 0;
						}
						$price = (double)$price[$date] < 0 ? 0 : (double)$price[$date];
						
						$x_label[] = new OFC_Elements_Axis_X_Label('$'.$price, '#999999',10, -50);
						$tip_price = number_format( $price , 2 , '.' , ',' );
						$tip = "\$$tip_price ($date)";
						$val = new OFC_Charts_Bar_Value($price);
						$val->set_tooltip($tip);
						$values[] = $val;
						$max_price = $max_price < $price ? $price+($price/4): $max_price; 
					}
					else {
						if($date != $array_keys[count($array_keys)-1]){
							foreach($data as $key => $value){
								if(!empty($value) && isset($value['amount'])){
									if( !isset($amount[$value['name']]) )
										$amount[$value['name']] = 0;
										
									$amount[$value['name']] += $value['amount'];
								}
							}
							continue;
						}
						foreach($data as $key => $value){
							if(!empty($value))
								$amount[$value['name']] += $value['amount'];
						}
						
						foreach($amount as $product_name => $product_val){
							$x_label[] = new OFC_Elements_Axis_X_Label(str_replace('|', ' ', $product_name), '#999999',10, -50);
							$tip_price = number_format( $product_val , 2 , '.' , ',' );
							$tip = ucwords($product_name)." ($ {$tip_price})";
							$val = new OFC_Charts_Bar_Value($product_val);
							$val->set_tooltip($tip);
							$values[] = $val;
							$max_price = $max_price < $product_val ? $product_val+($product_val/4): $max_price; 
						}
					}
				}
				
				$bar->set_colour('#119CD3');
				$bar->set_values($values);
				
				$title = new OFC_Elements_Title( $title_is );

				$x_l_set = new OFC_Elements_Axis_X_Label_Set();
				$x_l_set->set_steps( 1 );
				
				$x_l_set->set_labels( $x_label );
				
				$x_axis = new OFC_Elements_Axis_X();
				$x_axis->set_3d(0);
				$x_axis->set_colours('#DDDDDD', '#DDDDDD');
				$x_axis->set_stroke(1);
				$x_axis->set_labels( $x_l_set );
				$step = $max_price > 500 ?  $max_price > 5000 ?  $max_price > 50000 ?  $max_price > 500000 ?  $max_price > 5000000 ? 5000000 : 500000 : 50000 : 5000 : 500: 50;
				$y_axis = new OFC_Elements_Axis_Y();
				$y_axis->set_colours('#DDDDDD', '#DDDDDD');
				$y_axis->set_range( 0, $max_price , $step);

				$chart = new OFC_Chart();
				$chart->set_title( $title );
				$chart->set_bg_colour( '#F9F9F9' );
				$chart->set_x_axis( $x_axis );
				$chart->set_y_axis( $y_axis );
				$chart->add_element( $bar );
				
				$path = $pn_plugin_dir ."/pn-chart/data.json";
				$file = @fopen($path, "w");
				@fwrite($file, $chart->toString());
				@fclose($file);
			/* end json */
			$result = '';
			if(isset( $_REQUEST['filter_all'] ) && $search_term != 'Filter By Keyword' && $search_term != '' && count($table_rows)) 
				$result = '<i style="color: #777">Showing results for "<strong>'.$search_term.'</strong>"</i>';
			else if ( isset( $_REQUEST['filter_all']) && $search_term != 'Filter By Keyword' && $search_term != '' && !count($table_rows))
				$result = '<i style="color: #777">There is no transactions in this time period, that contain <strong>"'.$search_term.'"</strong>. Select your timespan in filter section.</i>';
			
			$master_table = $result."<br /><br /><table id='pn-table-main' class='tablesorter'>";
			$master_table .= "<thead><tr><th> Product name </th><th> Contract name </th><th> Qty </th>
				<th> Invoice amount (USD)</th><th> Coupon code</th><th> Method </th><th> Transaction type</th><th> Customer name </th><th> Customer email </th><th> Transaction Date </th><td></td></tr></thead><tbody>";
			
			$master_table .= implode('', $table_rows)."</tbody></table>";
			
			$master_table .= <<<END
							<div id="pager" class="pager">
								<form>
										<img src="{$pn_plugin_url}/images/first.png" class="first"/>
										<img src="{$pn_plugin_url}/images/prev.png" class="prev"/>
										<input type="text" style="width:50px; height:18px; padding: 0px 0px 3px 0px;" class="pagedisplay" />
										<img src="{$pn_plugin_url}/images/next.png" class="next"/>
										<img src="{$pn_plugin_url}/images/last.png" class="last"/>
										<select class="pagesize">
							  <option value="5">5 per page</option>
												<option value="10">10 per page</option>
												<option value="20">20 per page</option>
												<option value="50" selected="selected">50 per page</option>

										</select>
								</form>
						</div>
END;
	}
		?>
		<script type="text/javascript">
			swfobject.embedSWF("<?php echo $pn_plugin_url."/pn-chart/open-flash-chart.swf"; ?>", "pn_chart", "900px", "220px", "9.0.0", "expressInstall.swf", {"data-file":"<?php echo $pn_plugin_url; ?>/pn-chart/data.json"});
		</script>
		<div class="wrap">
		<h2><a href="http://www.bluesnap.com" title="Bluesnap - Take charge" target="_blank"><img src="<?php echo $pn_plugin_url; ?>/images/plimus_logo.png" alt="Plimu IPN" id="plimus-logo" /></a>for WordPress</h2>
		
		<h5><font color="#999999">This Month:</font> <font color="#555555"> $<?php echo number_format( $this_month , 2 , '.' , ',' ); ?></font> &nbsp;&nbsp;|&nbsp;&nbsp; <font color="#999999"> Last Month:</font> <font color="#555555"> $<?php echo number_format( $last_month , 2 , '.' , ',' ); ?></font> &nbsp;&nbsp;|&nbsp;&nbsp;  <font color="#999999"> Total Amount:</font> <font color="#555555"> $<?php echo number_format( $total_sum , 2 , '.' , ',' ); ?></font> </h5>
			<!--<i id="small_size">* Send registration notifications (again), dates in mm/dd/yyyy format please</i>
			 <form action="<?php //echo $action_url; ?>" method="post" id="new_email_form" >
				From <input type="text" name="new_from_date" value="12/1/2010" /> to <input type="text" name="new_to_date" value="12/16/2010" />
				<input type="submit" name="new_mails_submit" id="new_mails_submit" value="Send Registration Emails" class="button"/><br />
					<br /><i id="note">&nbsp;&nbsp;&nbsp;&nbsp;** in your <b>HTML new password email</b> template use <code>{USER_FULL_NAME}</code> <code>{NEW_PASSWORD}</code> <code>{LOGIN_URL}</code> shortcodes, to replace it with user</i>
				<textarea cols="90" rows="10" name="pn_email_tmp" id="pn_email_tmp" ><?php //echo $pn_tpl_reg_email_text ?></textarea><div id="legend"></div>
				<textarea cols="80" rows="10" name="pn_email_res" id="pn_email_res" ><?php //echo $email_message ?></textarea><div id="legend"></div>
			</form><br /><br /> -->
		<form action="<?php echo $action_url; ?>" id="search_form" method="POST">
			<select name="filter_by_type" id="filter_by_type" >
				<option value="date" <?php echo isset($selected['date']) ? $selected['date'] : '';?> >Show by date</option>
				<option value="product" <?php echo isset( $selected['product'] ) ? $selected['product'] : '';?> >Show by product</option>
				<option value="contract" <?php echo isset($selected['contract']) ? $selected['contract'] : '';?> >Show by contract</option>
			</select>
			<select name="filter_by_dates" id="filter_by_dates" >
				<option value="0" <?php echo $selected_date[0]; ?>>All transactions</option>
				<option value="7" <?php echo $selected_date[7]; ?>>Last 7 days</option>
				<option value="15" <?php echo $selected_date[15]; ?>>Last 15 days</option>
				<option value="30" <?php echo $selected_date[30]; ?>>Last 30 days</option>
				<option value="60" <?php echo $selected_date[60]; ?>>Last 60 days</option>
				<option value="120" <?php echo $selected_date[120]; ?>>Last 120 days</option>
			</select>
			<input type="text" name="search_item" id="search_item" size="40" value="<?php $search_term = strlen(trim($search_term)) ? $search_term : 'Filter By Keyword'; echo $search_term; ?>" />
			
			<input type="submit" value=" Submit  " name="filter_all" class="button" />
			<a href="<?php echo $action_url; ?>&show=all" title="Show All" class="button"> Show All </a>
		</form>
	<br />
		<?php  
		if(!empty($transaction_data)){
			
			?>
			<br />
			<div id="resize" style="width:1000px; height:250px; padding: 10px;">
				<div id="pn_chart"></div>
			</div>
			<?php 
			echo $master_table.$default_footer;
		}
		else {
			if((isset($_REQUEST['filter_all']) && ($search_term != '' || $search_term != 'Filter By Keyword')) && empty($transaction_data))
				echo "<h5></i>There are no results for '{$_POST['search_item']}'.</i></h5>";
			else 
				echo "<h5></i>There are no registered sales yet, but hopefully things are about to change.</i></h5>";
	}?>
		<h5><a href="http://www.prelovac.com" title="Plugin by Prelovac Media"><img src="<?php echo $pn_plugin_url;?>/images/logo.png"  style="margin-top: 30px;"/></a> </h5>
		
		</div><br />
		
		<?php
	}
	function pn_admin_styles(){
		global $pn_plugin_url;
		if( isset($_GET['page']) && ( $_GET['page'] == 'plimus-payment-notifications' ||  $_GET['page'] == 'pn-options' ) ){
			wp_register_style('pn_admin_style', $pn_plugin_url.'/css/pn_style.css');
			wp_enqueue_style('pn_admin_style');
			wp_register_style('pn_swf_style', $pn_plugin_url.'/css/base/jquery.ui.all.css');
			wp_enqueue_style('pn_swf_style');
		}
	}
	function pn_add_meta_tags(){
		if($_GET['page'] == 'plimus-payment-notifications' || $_GET['page'] == 'pn-options'){
			echo '<meta Http-Equiv="Expires" Content="Thu, 19 Nov 1981 08:52:00 GMT">';
			echo '<meta Http-Equiv="Cache-Control" Content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0">';
			echo '<meta Http-Equiv="Pragma" Content="no-cache">';
		}
	}
	function pn_admin_print_scripts(){
		global $pn_plugin_url;
		if($_GET['page'] == 'plimus-payment-notifications' || $_GET['page'] == 'pn-options'){
			wp_enqueue_script('pn_tablesorter', $pn_plugin_url.'/js/jquery.tablesorter.min.js');
			wp_enqueue_script('pn_tablesorter_pager', $pn_plugin_url.'/js/jquery.tablesorter.pager.js');
			wp_enqueue_script('pn_swf_script', $pn_plugin_url.'/js/swfobject.js');
			wp_enqueue_script('pn_script', $pn_plugin_url.'/js/script.js');

			//wp_enqueue_script('pn_script_ui', $plugin_url.'/js/jquery-ui-1.8.6.custom.min.js');
		}
	}
	add_action('admin_print_styles', 'pn_admin_styles');
	add_action('admin_menu','pn_admin_menu_pages');
	add_action('admin_print_scripts', 'pn_admin_print_scripts');

}
?>