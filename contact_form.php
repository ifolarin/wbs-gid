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
					"label_submit" => "Send",
					// the error message when at least one of the required fields is empty
					"error_empty" => 'Please fill in all the required fields.',
					// the error message when the e-mail address is not valid:
					"error_noemail" => "Please enter a valid e-mail address.",
					// the success message when the email is sent:
					"success_message" => "Thank you for your email! Someone from our team will respond as soon as we can",
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

	$output .= "<form id='wbs-contact-form' class='contact-form' method='post' action='" . get_permalink(). "'>";
	$output .= "<div class='wbs-contact-row-name'>";
	$output .= "	<label for='wbs-contact-name'>" . $atts['label_contact_name'] . "</label>";
	$output .= "	<input placeholder='Name' required type='text' name='contact_name' id='wbs-contact-name' class='wbs-contact required' value='{$form_data['contact_name']}' />" ;
	$output .= "</div>";
	$output .= "<div class='wbs-contact-row-email'>";
	$output .= "	<label for='wbs-contact-email'>" . $atts['label_contact_email'] . "</label>";
	$output .= "	<input placeholder='Email' required type='text' name='contact_email' id='wbs-contact-email' class='wbs-contact' value='{$form_data['contact_email']}'/>";
	$output .= "</div>";
	$output .= "<div class='wbs-contact-row-subject'>";
	$output .= "	<label for='wbs-contact-subject'>" . $atts['label_subject'] . "</label>";
	$output .= "	<input placeholder='Subject' required type='text' name='subject' id='wbs-contact-subject' class='wbs-contact' value='{$form_data['subject']}'/>";
	$output .= "</div>";
	$output .= "<div class='wbs-contact-row-message'>";
	$output .= "	<label for='wbs-contact-message'>" . $atts['label_message'] . "</label>";
	$output .= "	<textarea placeholder='Message' required name='message' id='wbs-contact-message' class='wbs-contact' rows='15'>{$form_data['message']}</textarea>";
	$output .= "</div>";
	$output .= "<div class='wbs-contact-row-btn'>";
	$output .= "	<button type='submit' class='wbs-contact-btn'>" . $atts['label_submit'] . "</button>";
	$output .= "</div>";
	$output .= "</form>";

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




