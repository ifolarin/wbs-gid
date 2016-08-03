<?php


require_once 'functions.php';
require_once 'validation_functions.php';
require_once 'email_functions.php';

function wbs_contact_sc($atts, $content){
	$errors = array();
	$message = "";
	$form_data = reset_contact_us_form(array());

	$atts = shortcode_atts(
			array(
					"email" => "services+contact@getitdone.ng", //get_bloginfo('admin_email'),
					"subject" => "",
					"label_contact_name" => "Your Name",
					"label_contact_email" => "Your E-mail Address",
					"label_subject" => "Subject",
					"label_message" => "Your Message",
					"label_submit" => "Send Message",
					// the error message when at least one of the required fields is empty
					"error_empty" => 'Please fill in all the required fields.',
					// the error message when the e-mail address is not valid:
					"error_noemail" => "Please enter a valid e-mail address.",
					// the success message when the email is sent:
					"success_message" => "Thank you for your message.<br />A member of our team will get in touch with you shortly",
					"form_style" => "default",
			), $atts
			);
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){

		$form_data = wbs_get_post_data();
		$required_fields = array("contact_name", "contact_email", "message", "subject");
		
		$errors = wbs_validate_contact_us_data($atts, $form_data, $required_fields);

		if(empty($errors)){
				
			$resp = send_email(
						$atts["email"], 
						$form_data["subject"], 
						build_email_message($form_data), 
						build_email_headers($form_data["contact_email"], $form_data["contact_name"]));			
				
			if($resp === false){
				$errors["message_not_sent"] = "Oops there was an error sending your message. This issue has been reported.";
			}else {
				$form_data = reset_contact_us_form($form_data);
			}
		}
	}

	return wbs_build_contact_us_form($atts, $form_data, $errors);
}


function build_email_message($data){
	$message  = $data["message"] . "\n";
	$message .= "Client-IP-Address: " . wbs_get_the_ip();
	
	return $message;
}

// function wbs_contact_email_params($atts, $form_data){
// 	$email_subject = "[" . get_bloginfo('name') . "] " . $form_data['subject'];
// 	$email_message = $form_data['message'] . "\n\nIP: " . wbs_get_the_ip();


// 	$params = array(
// 			"to" => $atts['email'],
// 			"subject" => $email_subject,
// 			"message" => wordwrap($email_message,70),
// 			"headers" => build_email_headers($form_data["contact_email"], $form_data["contact_name"])
				
// 	);

// 	return $params;
// }





function wbs_build_contact_us_form($atts, $form_data, $errors=array()){

	$output = "";
	
	$output .= render_messages($errors, $atts);	
	$form_action = get_permalink();
	
	if (strtolower($atts["form_style"]) === "horizontal")
	{
		$output .= <<< RENDERHORIZONTAL
	<fieldset>
<form id='wbs-contact-form' class='gid-form form-horizontal' method='post' action="{$form_action}">
	<div class="form-group">
		<label for="contact_name" class="col-sm-3 control-label">{$atts['label_contact_name']}</label>
		<div class="col-sm-9"><input type="text" class="form-control" id="contact_name"
			name="contact_name" placeholder="Your Full Name" value="{$form_data['contact_name']}" required>
		</div>
	</div>
	<div class="form-group">
		<label for="contact_email" class="col-sm-3 control-label">{$atts['label_contact_email']}</label>
		<div class="col-sm-9"><input
			type="email" required class="form-control" id="contact_email"
			name="contact_email" placeholder="Your Email Address" value="{$form_data['contact_email']}" required>
		</div>
	</div>
	<div class="form-group">
		<label for="subject"  class="col-sm-3 control-label">{$atts['label_subject']}</label>
		<div class="col-sm-9"><input
			type="text" class="form-control" id="subject"
			name="subject" placeholder="Subject" value="{$form_data['subject']}" required>
		</div>
	</div>
	<div class="form-group">
		<label for="message" class="col-sm-3 control-label">{$atts['label_message']}</label>
		<div class="col-sm-9"><textarea
			type="text" class="form-control" id="message"
			name="message" placeholder="Your message..." value="{$form_data['message']}" required rows="10"></textarea>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<button class="btn btn-default">{$atts['label_submit']}</button>
		</div>
	</div>
</form>
</fieldset>
RENDERHORIZONTAL;
	}else{
		$output .= <<< RENDERDEFAULT
	<fieldset>
	<form id='wbs-contact-form' class='gid-form' method='post' action="{$form_action}">
		<div class="row">
			<div class="col-xs-12 col-md-9">
				<div class="form-group">
					<label for="contact_name">{$atts['label_contact_name']}</label> <input
						type="text" class="form-control" id="contact_name"
						name="contact_name" placeholder="Your Full Name" value="{$form_data['contact_name']}" required>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 col-md-9">
				<div class="form-group">
					<label for="contact_email">{$atts['label_contact_email']}</label> <input
						type="email" required class="form-control" id="contact_email"
						name="contact_email" placeholder="Your Email Address" value="{$form_data['contact_email']}" required>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 col-md-9">
				<div class="form-group">
					<label for="subject">{$atts['label_subject']}</label> <input
						type="text" class="form-control" id="subject"
						name="subject" placeholder="Subject" value="{$form_data['subject']}" required>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 col-md-9">
				<div class="form-group">
					<label for="message">{$atts['label_message']}</label> <textarea
						type="text" class="form-control" id="message"
						name="message" placeholder="Your message..." value="{$form_data['message']}" required rows="10"></textarea>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<button class="btn btn-default">{$atts['label_submit']}</button>
			</div>
		</div>
	</form>
</fieldset>
RENDERDEFAULT;
		
	}
	return $output;

}



function reset_contact_us_form($form_data){

	$form_data['contact_name'] = "";
	$form_data['contact_email'] = "";
	$form_data['message'] = "";
	$form_data['subject'] = "";

	return $form_data;

}

function wbs_validate_contact_us_data($atts, $form_data, $required_fields){
	$errors = array();

	$errors = has_required_fields($atts, $form_data, $required_fields);

	$errors = has_valid_email('contact_email', $atts, $form_data, $errors);

	return $errors;
}




