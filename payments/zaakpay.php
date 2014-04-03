<?php
//
// Zaakpay_v0.1 - CSCart
//

if ( !defined('AREA') ) { die('Access denied'); }
$index_script = Registry::get('customer_index');
include_once('checksum.php');


// Handling response from Zaakpay 

if (defined('PAYMENT_NOTIFICATION')) {

	
	$order_id = $_POST['orderId'];
	$res_code = $_POST['responseCode'];
	$res_desc = $_POST['responseDescription'];
	$checksum_recv = $_POST['checksum'];
	if (fn_check_payment_script('zaakpay.php', $_REQUEST['orderId'], $processor_data)){
	
		if (empty($processor_data)) {
				$processor_data = fn_get_processor_data($order_info['email']);
				}
	$secret_key = $processor_data['params']['secret_key'];

	//Zaakpay Checksum Part
	
	$check = new Checksum();
	$all = ("'". $order_id ."''". $res_code ."''". $res_desc."'");
	$bool = 0;
	$bool = $check->verifyChecksum($checksum_recv, $all, $secret_key);


	if ($mode == 'returnUrl' && !empty($_REQUEST['orderId'])) {		
		if (fn_check_payment_script('zaakpay.php', $_REQUEST['orderId'], $processor_data)) {		
			$pp_response = array();			
			$order_info = fn_get_order_info($_REQUEST['orderId']);			
			if($bool == 1){
				if($res_code == 100){
					$pp_response['order_status'] = 'P';
					$pp_response['reason_text'] = "Thank you. Your order has been processed successfully. ";
				}
				else{
					$pp_response['order_status'] = 'F';
					$pp_response['reason_text'] = "Thank you. Your order has been unsuccessfull";
				}
			}
			else {
				$pp_response['order_status'] = 'D';
				$pp_response['reason_text'] = "Thank you. Your order has been declined due to security reasons.";
			}	
			fn_finish_payment($order_id, $pp_response);
			fn_order_placement_routines($order_id, '', true, '');
		}
		exit;
	} else {
		$order_info = fn_get_order_info($_REQUEST['orderId']);
		$pp_response['order_status'] = 'O';
		$pp_response['reason_text'] = '';	
		fn_order_placement_routines($order_id, false);
	}
} 
}else {
	
	$merchant_id = $processor_data['params']['merchant_id'];
	$current_location = Registry::get('config.current_location');
	$zaakpay_url = "https://api.zaakpay.com/transact";
	
	$mod = $processor_data['params']['transaction_mode'];
	$merchant_ip = $processor_data['params']['ip_address'];
	$log = $processor_data['params']['log_params'];
	
	//Order Total
	$zaakpay_total = fn_format_price($order_info['total']) ;
	$amount = 100 * $zaakpay_total ;							// Should be in Paisa 
	$zaakpay_shipping = fn_order_shipping_cost($order_info);
	$zaakpay_order_id = $processor_data['params']['order_prefix'].(($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id);
	$date = date('Y-m-d');
	
	$msg = fn_get_lang_var('text_cc_processor_connection');
	$msg = str_replace('[processor]', 'Zaakpay', $msg);
	
	if (!empty($order_info['items'])) {
		foreach ($order_info['items'] as $k => $v) {
			$v['product'] = htmlspecialchars($v['product']);

		}
	}
	if($mod == "test")
	$mode = 0;
	else 
	$mode = 1;
	
	$return_url = $current_location."/".$index_script."?dispatch=payment_notification.returnUrl&payment=zaakpay&order_id=".$order_id;
	
	$post_variables = array(
		"merchantIdentifier" => $merchant_id,
		"orderId" => $order_id,
		"returnUrl" => $return_url,
		"buyerEmail" => $order_info['email'],
		"buyerFirstName" => $order_info['b_firstname'],
		"buyerLastName" => $order_info['b_lastname'],
		"buyerAddress" => $order_info['b_address']." ".$order_info['b_address_2'],
		"buyerCity" => $order_info['b_city'],
		"buyerState" => $order_info['b_state'],
		"buyerCountry" => $order_info['b_country_descr'],
		"buyerPincode" => $order_info['b_zipcode'],
		"buyerPhoneNumber" => $order_info['phone'],
		"txnType" => '1',
		"zpPayOption" => '1',
		"mode"	=> $mode,
		"currency" => "INR", 						// Zaakpay accepts only INR(Indian Rupee)
		"amount" =>  $amount,
		"merchantIpAddress" => $merchant_ip, 
		"purpose" => '1',
		"productDescription" => $v['product'],
		"shipToAddress" => $order_info['s_address'],
		"shipToCity" => $order_info['s_city'],
		"shipToState" => $order_info['s_state'],
		"shipToCountry" => $order_info["s_country_descr"],
		"shipToPincode" => $order_info["s_zipcode"],
		"shipToPhonenNumber" => $order_info['phone'],
		"shipToFirstname" => $order_info['s_firstname'],
		"shipToLastname" => $order_info['s_lastname'],
		"txnDate" => $date,		
	
	);
	
	$secret_key = $processor_data['params']['secret_key'];
	$sum = new Checksum();
		$all = '';
		foreach($post_variables as $name => $value)	{
			if($name != 'checksum') {
				$all .= "'";
				if ($name == 'returnUrl') {
					$all .= $sum->sanitizedURL($value);
				} else {				
					
					$all .= $sum->sanitizedParam($value);
				}
				$all .= "'";
			}
		}
		
	if($log == "yes")
	{
		error_log("All Params(Parameters which are posting to Zaakpay) : " .$all);
		error_log("Zaakpay Secret Key : " .$secret_key);
	}

	$checksum = $sum->calculateChecksum($secret_key,$all);
	
	echo <<<EOT
	<html>
	<body onLoad="document.zaakpay_form.submit();">
	<form action="{$zaakpay_url}" method="post" name="zaakpay_form">
	
	<input type=hidden name="merchantIdentifier" value="{$merchant_id}">
	<input type=hidden name="orderId" value="$order_id">
	<input type=hidden name="returnUrl" value="$current_location/$index_script?dispatch=payment_notification.returnUrl&payment=zaakpay&order_id=$order_id">
	<input type=hidden name="buyerEmail" value="{$order_info['email']}">
	<input type=hidden name="buyerFirstName" value="{$order_info['b_firstname']}">
	<input type=hidden name="buyerLastName" value="{$order_info['b_lastname']}">
	<input type=hidden name="buyerAddress" value="{$sum->sanitizedParam($order_info['b_address']." ".$order_info['b_address_2'])}">
	<input type=hidden name="buyerCity" value="{$sum->sanitizedParam($order_info['b_city'])}">
	<input type=hidden name="buyerState" value="{$sum->sanitizedParam($order_info['b_state'])}">
	<input type=hidden name="buyerCountry" value="{$sum->sanitizedParam($order_info['b_country_descr'])}">
	<input type=hidden name="buyerPincode" value="{$sum->sanitizedParam($order_info['b_zipcode'])}">
	<input type=hidden name="buyerPhoneNumber" value="{$sum->sanitizedParam($order_info['phone'])}">
	
	<input type=hidden name="txnType" value="1">
	<input type=hidden name="zpPayOption" value="1">
	
	
	<input type=hidden name="mode" value="$mode">
	<input type=hidden name="currency" value="INR">
	<input type=hidden name="amount" value="{$amount}">	
	
	<input type=hidden name="merchantIpAddress" value="{$merchant_ip}">
	<input type=hidden name="purpose" value="1">
	
	<input type=hidden name="productDescription" value="{$sum->sanitizedParam($v['product'])}">
	<input name="shipToAddress" type="hidden" value="{$sum->sanitizedParam($order_info['s_address'])}" />
	<input name="shipToCity" type="hidden" value="{$sum->sanitizedParam($order_info['s_city'])}" />
	<input name="shipToState" type="hidden" value="{$sum->sanitizedParam($order_info['s_state'])}" />
	<input name="shipToCountry" type="hidden" value="{$sum->sanitizedParam($order_info['s_country_descr'])}" />
	<input name="shipToPincode" type="hidden" value="{$sum->sanitizedParam($order_info['s_zipcode'])}" />
	<input name="shipToPhoneNumber" type="hidden" value="{$sum->sanitizedParam($order_info['phone'])}" />
	<input name="shipToFirstname" type="hidden" value="{$order_info['s_firstname']}" />
	<input name="shipToLastname" type="hidden" value="{$order_info['s_lastname']}" />
	<input name="txnDate" type="hidden" value="{$date}" />
	<input name="checksum" type="hidden" value="{$checksum}" />

	
EOT;

/*$i = 1;
// Products
if (empty($order_info['use_gift_certificates']) && !floatval($order_info['subtotal_discount'])) {
	if (!empty($order_info['items'])) {
		foreach ($order_info['items'] as $k => $v) {
			$suffix = '_'.($i++);
			$v['product'] = htmlspecialchars($v['product']);
			$v['price'] = fn_format_price(($v['subtotal'] - fn_external_discounts($v)) / $v['amount']);
			echo <<<EOT
			<input type="hidden" name="item_name{$suffix}" value="{$v['product']}" />
			<input type="hidden" name="amount{$suffix}" value="{$v['price']}" />
			<input type="hidden" name="quantity{$suffix}" value="{$v['amount']}" />
EOT;
			if (!empty($v['product_options'])) {
				foreach ($v['product_options'] as $_k => $_v) {
					$_v['option_name'] = htmlspecialchars($_v['option_name']);
					$_v['variant_name'] = htmlspecialchars($_v['variant_name']);
					echo <<<EOT
						<input type="hidden" name="on{$_k}{$suffix}" value="{$_v['option_name']}" />
						<input type="hidden" name="os{$_k}{$suffix}" value="{$_v['variant_name']}" />
EOT;
				}
			}
		}
	}

	// Gift Certificates
	if (!empty($order_info['gift_certificates'])) {
		foreach ($order_info['gift_certificates'] as $k => $v) {
			$suffix = '_'.($i++);
			$v['gift_cert_code'] = htmlspecialchars($v['gift_cert_code']);
			$v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : $v['amount'];
			echo <<<EOT
			<input type="hidden" name="item_name{$suffix}" value="{$v['gift_cert_code']}" />
			<input type="hidden" name="amount{$suffix}" value="{$v['amount']}" />
			<input type="hidden" name="quantity{$suffix}" value="1" />
EOT;
		}
	}

	// Payment surcharge
	if (floatval($order_info['payment_surcharge'])) {
		$suffix = '_' . ($i++);
		$name = fn_get_lang_var('surcharge');

		echo <<<EOT
		<input type="hidden" name="item_name{$suffix}" value="{$name}" />
		<input type="hidden" name="amount{$suffix}" value="{$order_info['payment_surcharge']}" />
		<input type="hidden" name="quantity{$suffix}" value="1" />
EOT;
	}
} else {
	$total_description = fn_get_lang_var('total_product_cost');
	echo <<<EOT
	<input type="hidden" name="item_name_1" value="{$total_description}" />
	<input type="hidden" name="amount_1" value="{$zaakpay_total}" />
	<input type="hidden" name="quantity_1" value="1" />
EOT;
}

*/

	echo <<<EOT
	</form>
	<div align=center>{$msg}</div>
	</body>
	</html>
EOT;

	fn_flush();
}
exit;
?>
