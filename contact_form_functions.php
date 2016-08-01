<?php
function build_contact_form($atts, $form_data){

	$output = "";
	$output .= "<div id='contact-name-group' class='form-group'>";
	$output .= "	<label for='contact_name'>" . esc_attr($atts['label_contact_name']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='contact_name' id='contact_name' class='widefat required' placeholder='Full Name' value='" . esc_attr($form_data['contact_name']) . "' />" ;
	$output .= "</div>";
	$output .= "<div id='contact-email-group' class='form-group'>";
	$output .= "	<label for='contact_email'>" . esc_attr($atts['label_contact_email']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='email' name='contact_email' id='contact_email' class='widefat' placeholder='Email Address' value='" . esc_attr($form_data['contact_email']) . "'/>";
	$output .= "</div>";
	$output .= "<div id='contact-mobile-group' class='form-group'>";
	$output .= "	<label for='contact_mobile'>" . esc_attr($atts['label_contact_mobile']) . "</label><span class='required'>*</span>";		
	$output .= "	<input type='text' name='contact_mobile' id='contact_mobile' class='widefat' placeholder='Mobile Number. e.g. 08096633961 or 2348096633961' pattern='" . $atts['mobile_regex'] . "' value='" . esc_attr($form_data['contact_mobile']) . "'/>";
	$output .= "</div>";
	
	return $output;
}

function reset_contact_data($data){
	$data["contact_name"] = "";
	$data["contact_email"] = "";
	$data["contact_mobile"] = "";

	return $data;
}

