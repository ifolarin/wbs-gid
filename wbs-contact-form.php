<?php
error_reporting(E_ALL);


if(!defined('ABSPATH')){
	exit;
}

require_once  (plugin_dir_path(__FILE__). 'phpxmlrpc-4.0.0/src/Autoloader.php');
PhpXmlRpc\Autoloader::register();

$GLOBALS['xmlrpc_null_extension'] = true;
\PhpXmlRpc\PhpXmlRpc::importGlobals();

add_action( 'phpmailer_init', 'my_phpmailer_settings' );
function my_phpmailer_settings( $phpmailer ) {
	$phpmailer->isSMTP();
	$phpmailer->Host = 'smtp.gmail.com';
	$phpmailer->SMTPAuth = true; // Force it to use Username and Password to authenticate
	$phpmailer->Port = 587;
	$phpmailer->Username = 'services@getitdone.ng';
	$phpmailer->Password = 'P@$$w0rd!';
	$phpmailer->SMTPSecure = "TLS"; 

// 	Additional settings…// Choose SSL or TLS, if necessary for your server
// 	$phpmailer->From = "you@yourdomail.com";
// 	$phpmailer->FromName = "Your Name";
}

add_shortcode('wbs-contact', function($atts, $content){

	$atts = shortcode_atts(
			array(
				"email" => "services+contact@getitdone.ng", //get_bloginfo('admin_email'),
				"subject" => "",
				"label_name" => "Your Name",
				"label_email" => "Your E-mail Address",
				"label_subject" => "Subject",
				"label_message" => "Your Message",
				"label_submit" => "Submit",
				"use_smtp" => true,
				// the error message when at least one of the required fields is empty
				"error_empty" => 'Please fill in all the required fields.',
				// the error message when the e-mail address is not valid:
				"error_noemail" => "Please enter a valid e-mail address.",
				// the success message when the email is sent:
				"success" => "Thank you for your email! We'll get back to you as soon as we can",
				"communicationEventTypeId" => 'WEB_SITE_COMMUNICATI',
				"contactMechTypeId" => 'WEB_ADDRESS',
			), $atts
	);
	
	extract($atts);
	
// 	var_dump($atts);
	
	$form_data = array();
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$error = false;
		//set "required fields" to check
		$required_fields = array("contact_name", "email", "message", "subject");
		
		
		// this part fetches everything that has been POSTed, sanitizes them and lets us use them as $form_data['subject']
		$form_data = wbs_get_form_data();
// 		print_r($form_data);
		// check that required fields are not empty and that email is valid
		$form_data = wbs_validate_form_data($atts,$form_data,$required_fields);
// 		print_r($atts);
	
		if(!isset($form_data['error']) || $form_data['error'] == false){
			$params = null;
			$resp = null;
			
			if($atts["use_smtp"] == true){
				$params = wbs_contact_email_params($atts, $form_data);
// 				var_dump($params);
				$resp = wbs_contact_send_email($params);
				var_dump($resp);
			}else{
				$params = wbs_ofbiz_build_params($atts, $form_data);
// 				var_dump($params);
				$resp = wbs_ofbiz_sendContact($params);
			}
			//$resp = wbs_ofbiz_sendContact($params);
			
// 			print_r($resp); die();
		}
				
	}
	
	wbs_build_contact_form($atts, $form_data);
});



function wbs_build_contact_form($atts, $form_data){
	if(isset($form_data['error']) && $form_data['error'] == true){
	?>
		<div class="notice notice-error"><?php echo $form_data['result']; ?></div>
	<?php 
	}
	if(isset($form_data['sent']) && $form_data['sent'] == true){
	?>
		<div class="notice"><?php echo $form_data['result']; $form_data = array(); ?></div>
		
	<?php 
	}
	?>
	<form class="contact-form" method="post" action="<?php echo get_permalink()?>">
	<div>
		<label for="cf_name"><?php echo $atts['label_name']; ?>: </label>
		<input type="text" name="contact_name" id="cf_name" class="widefat required" value="<?php echo (isset($form_data['contact_name'])||array_key_exists('contact_name',$form_data)) ? esc_attr($form_data['contact_name']): ""; ?>" />
		</div>
		<div>
			<label for="cf_email"><?php echo $atts['label_email']; ?>:</label>
			<input type="text" name="email" id="cf_email" class="widefat" value="<?php echo (isset($form_data['email'])||array_key_exists('email', $form_data)) ? sanitize_email($form_data['email']) : ""; ?>"/>
		</div>
		<div>
			<label for="cf_subjet"><?php echo $atts['label_subject']; ?>:</label>
			<input type="text" name="subject" id="cf_subject" class="widefat" value="<?php echo (isset($form_data['subject'])||array_key_exists('subject', $form_data)) ? esc_attr($form_data['subject']) : ""; ?>"/>
		</div>
		<div>
			<label for="cf_message"><?php echo $atts['label_message']; ?>:</label>
			<textarea name="message" id="cf_message" class="widefat" rows="15"><?php echo (isset($form_data['message'])||array_key_exists('message', $form_data)) ? esc_textarea($form_data['message']) : ""; ?></textarea>
		</div>
		<div>
			<input type="submit" class="form-submit" value="<?php echo $atts['label_submit']; ?>" name="send" id="cf_send" />
		</div>
	</form>

	<?php 
	
	
// 	echo '</form>';
	
	// 	echo $form;
}

function wbs_contact_email_params($atts, $form_data){
	print_r($form_data);
	$email_subject = "[" . get_bloginfo('name') . "] " . $form_data['subject'];
	$email_message = $form_data['message'] . "\n\nIP: " . wbs_get_the_ip();
	$headers = "From: " . $form_data['contact_name'] . " <" . $form_data['email'] . ">\n";
	$headers = "Reply-To: " . $form_data['contact_name'] . " <" . $form_data['email'] . ">\n";
	$headers .= "Content-Type: text/plain; charset=UTF-8\n";
	$headers .= "Content-Transfer-Encoding: 8bit\n";
	$headers .= "Client-Headers: " . wbs_get_the_ip();
	
	$params = array(
				"to" => $atts['email'],
				"subject" => $email_subject,
				"message" => $email_message,
				"headers" => $headers
			
	);
	
	return $params;
}

function wbs_ofbiz_build_params($atts, $form_data){
// 	print_r($form_data);
// 	$email_subject = "[" . get_bloginfo('name') . "]" . $form_data['subject'];
// 	$email_message = $form_data['message'] . "\n\nIP: " . wbs_get_the_ip();
// 	$headers = "From: " . $form_data['contact_name'] . "<" . $form_data['email'] . ">\n";
// 	$headers .= "Content-Type: text/plain; charset=UTF-8\n";
// 	$headers .= "Content-Transfer-Encoding: 8bit\n";
// 	$headers .= "Client-Headers: " . wbs_get_the_ip();
// 	wp_mail($atts['email'], $email_subject, $email_message, $headers);
// 	$form_data['result'] = $atts['success'];
// 	$form_data['sent'] = true;
	$note = "Sent from: " .  $form_data['email'] . "; Sent Name from: " . $form_data['contact_name'] . ";";
	
	
	$params = new PhpXmlRpc\Value (
			array(
					"thestruct" => new PhpXmlRpc\Value(
							array(
								"login.username" => new PhpXmlRpc\Value('admin'),
								"login.password" => new PhpXmlRpc\Value('ofbiz'),
								"headerString" => new PhpXmlRpc\Value($headers),
								"contentMimeTypeId" => new PhpXmlRpc\Value('text/plain'),
								"communicationEventTypeId" => new PhpXmlRpc\Value('EMAIL_COMMUNICATION'),
 								"contactMechTypeId" => new PhpXmlRpc\Value('EMAIL_ADDRESS'),
 								"partyIdTo" => new PhpXmlRpc\Value('admin'),
// 								"statusId" => new PhpXmlRpc\Value('COM_UNKNOWN_PARTY'),
// 								"toString" => new PhpXmlRpc\Value('services@getitdone.ng'),
								"fromString" => new PhpXmlRpc\Value($form_data['email']),
								"subject" => new PhpXmlRpc\Value($form_data['subject']),
								"content" => new PhpXmlRpc\Value($form_data['message']),
								"note" => new PhpXmlRpc\Value($note),
									
//								"contactMechId" => '10140'									
							),
							"struct"
							),
			),
			"struct"
			);
		
	return $params;
}

// add_filter('wp_mail_from', function($email){
// 	return 'ifolarin@gmail.com';
// });

function append_error($form_data, $error){
	$form_data['error'] = true;
	if(isset($form_data['result'])||array_key_exists('result', $form_data)){
		$form_data['result'] .= '<br />' . $error;		
	}
	else {
		$form_data['result'] = $error ;
	}
	return $form_data;
}

function wbs_validate_form_data($atts, $form_data, $required_fields){
	
	foreach ($required_fields as $required_field) {
		$value = trim($form_data[$required_field]);
		if(empty($value)){
			$form_data = append_error($form_data, $required_field . " - " . $atts['error_empty']);
		}
	}
	
	if(! is_email($form_data['email'])){
		$form_data = append_error($form_data, $atts['error_noemail']);
	}
	
	
	return $form_data;
}

function wbs_get_form_data() {
	$form_data = array();
	
	foreach ($_POST as $field => $value) {
			if(get_magic_quotes_gpc()){
				$value = stripslashes($value);
			}
			
			$form_data[$field] = strip_tags($value);
	}
	return $form_data;
}

function wbs_get_the_ip() {
	if(isset($_SERVER["HTTP_X_FORWARED_FOR"])){
		return $_SERVER["HTTP_X_FORWARDED_FOR"];
	}
	elseif (isset($_SERVER["HTTP_CLIENT_IP"])){
		return $_SERVER["HTTP_CLIENT_IP"];
	}
	else {
		return $_SERVER["REMOTE_ADDR"];
	}
}

function wbs_ofbiz_sendContact($params){
	$xmlrpc_url = 'http://localhost:8080/webtools/control/xmlrpc';
// 	$username = 'admin';
// 	$password = 'ofbiz';

// 	$params = new PhpXmlRpc\Value (
// 			array(
// 					"thestruct" => new PhpXmlRpc\Value(
// 							array(
// 									"login.username" => new PhpXmlRpc\Value('admin'),
// 									"login.password" => new PhpXmlRpc\Value('ofbiz'),
// 									"emailAddress" => new PhpXmlRpc\Value($form_data['email']),
// 									"firstName" => new PhpXmlRpc\Value($form_data['fname']),
// 									"lastName" => new PhpXmlRpc\Value($form_data['lname'])
// 							),
// 							"struct"
// 							),
// 			),
// 			"struct"
// 			);

	$req = new PhpXmlRpc\Request('createCommunicationEventWithoutPermission', $params);
	// 	print "Sending the following request: <pre>\n\n" . htmlentities($req->serialize());

	$client = new PhpXmlRpc\Client($xmlrpc_url);
	// 	$client->setCredentials($username, $password);
	// 	$client->setDebug(1);
	$resp = $client->send($req);

	return $resp;

}


function wbs_contact_send_email($params) {
	$resp = wp_mail($params['to'], $params['subject'], $params['message'], $params['headers']);
	return $resp;
}