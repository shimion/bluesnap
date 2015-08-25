<?php
function pn_is_reg_mail_service_on(){
	$reg_mail = get_option('pn_reg_email_on');
	if($reg_mail){
		if($reg_mail == 1) return true;
	}
	return false;
}
function pn_is_ntf_mail_service_on(){
	$reg_mail = get_option('pn_tpl_send');
	if($reg_mail){
		if($reg_mail == 1) return true;
	}
	return false;
}
function pn_is_register_user_on(){
	$reg_mail = get_option('pn_reg_user_on');
	if($reg_mail){
		if($reg_mail == 1) return true;
	}
	return false;
}
function pn_get_options(){
	$pn_options = array(
		'pn_tpl_send' => get_option('pn_tpl_send'),
		'pn_tpl_name' => get_option('pn_tpl_name'),
		'pn_tpl_email' => get_option('pn_tpl_email'),
		'pn_email_template' => get_option('pn_email_template'),
		'pn_reg_email_on' => get_option('pn_reg_email_on'),
		'pn_reg_user_on' => get_option('pn_reg_user_on'),
		'pn_tpl_email_text' => get_option('pn_tpl_email_text'),
		'pn_tpl_email_subject_text' => get_option('pn_tpl_email_subject_text'),
		'pn_tpl_html' => html_entity_decode(get_option('pn_tpl_html'))
	);
	return $pn_options;
}
function pn_send_email_notification($invoice_email = '', $invoice_name = '', $product_name = '', $contract_name = '', $transaction_date = ''){
	if(pn_is_ntf_mail_service_on()){
		$pn_options = pn_get_options();
		if(!empty($pn_options)){
			$pn_send_email = (int)$pn_options['pn_tpl_send'];
			$pn_your_name = $pn_options['pn_tpl_name'] != '' ? $pn_options['pn_tpl_name']: 'Your Name';
			$pn_your_email = $pn_options['pn_tpl_email'] != '' ? $pn_options['pn_tpl_email']: 'Your Email';
			$pn_tpl_email_text = $pn_options['pn_tpl_email_text'] != '' ? $pn_options['pn_tpl_email_text']: '';
			$pn_tpl_email_subject_text = $pn_options['pn_tpl_email_subject_text'] != '' ? $pn_options['pn_tpl_email_subject_text']: '';
			$pn_tpl_html = $pn_options['pn_tpl_html'] != '' ? $pn_options['pn_tpl_html']: '';
			$transaction_date = date('F jS, Y', strtotime($transaction_date));	
			
			$pn_tpl_email_subject_text = str_replace('{NAME}', htmlentities("$invoice_name"), $pn_tpl_email_subject_text);
			$pn_tpl_email_subject_text = str_replace('{PRODUCT}', htmlentities("$product_name"), $pn_tpl_email_subject_text);
			$pn_tpl_email_subject_text = str_replace('{CONTRACT}', htmlentities("$contract_name"), $pn_tpl_email_subject_text);
			$pn_tpl_email_subject_text = str_replace('{DATE}', htmlentities("$transaction_date"), $pn_tpl_email_subject_text);
			
			$pn_tpl_email_text = str_replace('{NAME}', htmlentities("$invoice_name"), $pn_tpl_email_text);
			$pn_tpl_email_text = str_replace('{PRODUCT}', "$product_name", $pn_tpl_email_text);
			$pn_tpl_email_text = str_replace('{CONTRACT}', "$contract_name", $pn_tpl_email_text);
			$pn_tpl_email_text = str_replace('{DATE}', "$transaction_date", $pn_tpl_email_text);
			
			$html = str_replace('{NAME}', htmlentities("$invoice_name"), $pn_tpl_html);
			$html = str_replace('{PRODUCT}', "$product_name", $html);
			$html = str_replace('{CONTRACT}', "$contract_name", $html);
			$html = str_replace('{DATE}', "$transaction_date", $html);
			
			$headers = "From: $pn_your_name <$pn_your_email>". "\r\n\\";
			wp_mail($pn_your_email, $pn_tpl_email_subject_text, $pn_tpl_email_text, $headers);
		}
	}
}
?>