<?php

error_reporting(E_ALL);


if(!defined('ABSPATH')){
	exit;
}

// require_once  (plugin_dir_path(__FILE__). 'phpxmlrpc-4.0.0/src/Autoloader.php');
// PhpXmlRpc\Autoloader::register();

// $GLOBALS['xmlrpc_null_extension'] = true;
// \PhpXmlRpc\PhpXmlRpc::importGlobals();

add_shortcode('wbs-ofbiz-optin', function($atts, $content){

	$atts = shortcode_atts(
			array(
				'lbl_fname' => 'First Name',	
				'lbl_lname' => 'Last Name',
				'lbl_email' => 'Email',
				'lbl_submit' => 'Sign Me Up'
			), $atts
	);
	
	extract($atts);
	
	$form_data = array();
	$resp = NULL;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$error = false;
		
		$required_fields = array('fname', 'lname', 'email');
		
		$form_data = wbs_get_form_data();
		
		$form_data = wbs_validate_form_data($atts, $form_data, $required_fields);
		
		$resp = wbs_ofbiz_createLead($form_data);
		
		if($resp->faultCode()){
// 			Log details of fault
// 			$fault = array();
// 			$fault['code'] = $resp->faultCode();
// 			$fault['string'] = $resp->faultString();
		}
		
	}	
	
	return wbs_build_optin_form($atts, $form_data, $resp);
	
});

function wbs_build_optin_form($atts, $form_data, $resp){
// 	print_r($form_data);
// 	print_r($resp);
if(isset($form_data['error']) && $form_data['error'] == true){
	?>
		<div class="notice notice-error"><?php echo $form_data['result']; ?></div>
	<?php 
	}
// 	if(!$resp->faultCode()){
// 		$value = $resp->value();
// 		$result = array();
// 		$result['partyId'] = $resp->value()->structmem('partyId')->scalarval();
// 		$result['roleTypeId'] = $resp->value()->structmem('roleTypeId')->scalarval();
// 	}
	if(!is_null($resp) && !$resp->faultCode()){
		$form_data = array();	
	}
	?>
	<form name="optin-form" method="post" action="<?php echo get_permalink()?>">
		<div>
			<label for="optin_fname"><?php echo $atts['lbl_fname']?>:</label>
			<input name="fname" id="optin_fname" type="text" class="widefat required" value="<?php echo (isset($form_data['fname']) || array_key_exists('fname', $form_data)) ?  esc_attr($form_data['fname']) : ''; ?>"  />
		</div>
		<div>
			<label for="optin_lname"><?php echo $atts['lbl_lname']?>:</label>
			<input name="lname" id="optin_lname" type="text" class="widefat required" value="<?php echo (isset($form_data['lname']) || array_key_exists('lname', $form_data)) ? esc_attr($form_data['lname']) : ''; ?>"  /> 
		</div>
		<div>
			<label for="optin_email"><?php echo $atts['lbl_email']?>:</label>
			<input name="email" id="optin_email" type="text" class="widefat required" value="<?php echo (isset($form_data['email']) || array_key_exists('email', $form_data)) ? esc_attr($form_data['email']) : ''; ?>"  /> 
		</div>
		<div>
			<input type="submit" value="<?php echo $atts['lbl_submit']; ?>" name="send" id="optin_send" />
		</div>
	</form>
	
	<?php 
}




function wbs_ofbiz_createLead($form_data){
	$xmlrpc_url = 'http://localhost:8080/webtools/control/xmlrpc';
	$username = 'admin';
	$password = 'ofbiz';	
	
	$params = new PhpXmlRpc\Value (
			array(
			"thestruct" => new PhpXmlRpc\Value(
				array(
					"login.username" => new PhpXmlRpc\Value('admin'),
					"login.password" => new PhpXmlRpc\Value('ofbiz'),
					"emailAddress" => new PhpXmlRpc\Value($form_data['email']),
					"firstName" => new PhpXmlRpc\Value($form_data['fname']),
					"lastName" => new PhpXmlRpc\Value($form_data['lname'])
				),
				"struct"
				),
			),
			"struct"
			);
	
	$req = new PhpXmlRpc\Request('createLead', $params);
// 	print "Sending the following request: <pre>\n\n" . htmlentities($req->serialize());
	
	$client = new PhpXmlRpc\Client($xmlrpc_url);
	// 	$client->setCredentials($username, $password);
	// 	$client->setDebug(1);
	$resp = $client->send($req);
	
	return $resp;
	
}