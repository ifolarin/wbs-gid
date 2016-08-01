<?php


require_once 'functions.php';
require_once 'validation_functions.php';
require_once 'email_functions.php';
require_once 'mvi_functions.php';





function gid_quote_sc($atts, $content){
	$atts = shortcode_atts(
			array(
					"email" => "services+quote@getitdone.ng", //get_bloginfo('admin_email'),
					"subject" => "",
					"label_vreg" => "Vehicle Registration Number",
					"label_vmake" => "Vehicle Make &amp; Model",
					"label_service_type" => "Service Type",
					"label_mvl_type" => "Vehicle Type",
					"label_mvi_type" => "Vehicle Insurance Type",
					"label_rwc" => "Road Worthiness Certificate",
					"label_contact_name" => "Your Full Name",
					"label_contact_email" => "Your E-mail Address",
					"label_contact_mobile" => "Your Mobile Number",
					"label_mvi_value" => "Insured Value",
					"label_submit" => "Send me a quote",
					// the error message when at least one of the required fields is empty
					"error_empty" => 'Please fill in all the required fields.',
					// the error message when the e-mail address is not valid:
					"error_noemail" => "Please enter a valid e-mail address.",
					// the success message when the email is sent:
					"success_message" => "Your vehicle details have been recorded and we will remind you before renewal is due",
					"message_not_sent" => "Oops there was an error sending your message. This issue has been reported.",
					"min_mvi_value" => 500000,
			), $atts
		);
	
	
	$renewal_data = reset_renewal_quote_form(array());
	$errors = array();
	$message = "";
	
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$renewal_data = wbs_get_post_data();
		$errors = validate_renewal_quote_data($atts, $renewal_data);
		
// 		print_r($errors);
		
		if(empty($errors)){
			$resp = send_email(
						$atts["email"], 
						"Renewal Quote for " . $renewal_data["vreg"] , 
						build_quote_message($renewal_data), 
						build_email_headers($renewal_data["contact_email"], $renewal_data["contact_name"]));
			
			
			if(false === $resp){
				$errors["message_not_sent"] = $atts['message_not_sent'];
			}
			$renewal_data = reset_renewal_quote_form($renewal_data);
			
			
		}
	}
	
	return build_quote_form($atts, $renewal_data, $errors);
	
}

function get_mvl_types(){
	return array(
			"mvl01" =>  "Small salon - e.g. accent,picanto", //"Vehicle with Engine  Below 1.6cc",
			"mvl02" =>  "Regular salon - e.g. civic,corolla,elantra, cerato" , //"Vehicle with Engine between 1.6-2.0",
			"mvl03" =>  "SUVs/Jeeps/Mini buses/Pickup", //"Vehicle with Engine between 2.1-3.0",
			"mvl04" =>  "Vehicles with V6/V8 Engines 3.0cc and above",
	);
}

function build_mvl_opts($mvl_types, $form_data){
	$selected = "";
	if (array_key_exists("mvl_type", $form_data)){
		$selected = $form_data["mvl_type"];
	}
	
	$output = "<option value=''>--Select a vehicle type--</option>";
	foreach ($mvl_types as $key => $value){
		if($selected == $key){
			$output .= "<option value='{$key}' selected>{$value}</option>";
		}
		else{
			$output .= "<option value='{$key}'>{$value}</option>";
		}
	}
	
	return $output;
}

function build_quote_message($data){
	
	$message = "";
	
	$message .= "Registration Number: {$data["vreg"]}\n";
	$message .= "Vehicle Make: {$data["vmake"]}\n";
	$message .= "Vehicle Type: {$data["mvl_type"]}\n";
	if (array_key_exists("mvi_type", $data)) {
		$message .= "Vehicle Insurance: {$data["mvi_type"]}\n";
	}
	$message .= "Insured Value: {$data["mvi_value"]}\n";
	if(array_key_exists("rwc", $data)){
		$message .= "RWC: {$data["rwc"]}\n";
	}
	else {
		$message .= "RWC: false";
	}
	$message .= "Contact Name: {$data["contact_name"]}\n";
	$message .= "Contact Email: {$data["contact_email"]}\n";
	$message .= "Contact Mobile: {$data["contact_mobile"]}\n";

	return wordwrap($message, 70);
}

function reset_renewal_quote_form($data){
	$data["service_type"] = "";
	$data["vreg"] = "";
	$data["vmake"] = "";
	$data["mvl_type"] = "";
	$data["mvi_type"] = "";
	$data["mvi_value"] = "";
	$data["rwc"] = "";
	$data["contact_name"] = "";
	$data["contact_email"] = "";
	$data["contact_mobile"] = "";
	
	return $data;
}

function validate_renewal_quote_data($atts, $form_data){
	$required_fields = array("service_type", "vreg", "vmake","mvl_type", "contact_name", "contact_email");
	
	$errors = has_required_fields($atts, $form_data, $required_fields);
	
	$mvi_errors = validate_mvi_type($form_data, $atts);
	
	$errors = array_merge($errors, $mvi_errors);
	
	if(0 !== strlen($form_data["contact_mobile"]) && !is_mobile($form_data["contact_mobile"])){
		$errors["contact_mobile"] = "Please enter a valid mobile contact. e.g. 08096633961 or 2348096633961";
	}
	
	return $errors;
}

function build_quote_form($atts, $form_data, $errors=array()){
	
	$output  = "";
	
	$output .= render_messages($errors, $atts);
	
	$output .= "<form id='quote_form' name='quote_form' method='post' action='" . get_permalink(). "'>";
	$output .= "<fieldset>";
	$output .= "<legend>Vehicle Details:</legend>";
	$output .= "<div>";
	$output .= "	<label for='service_type'>" . esc_attr($atts['label_service_type']) . "</label><span class='required'>*</span>";
	$output .= "	<select name='service_type' id='service_type' required>";
	$output .= "		<option value=''>--Select a service--</option>";
	$output .= "		<option value='vrnwl' " . restore_service_type_state('vrnwl', $form_data['service_type']) . " >Vehicle Document Renewal</option>";
	$output .= "		<optgroup label='First Registration'>";
	$output .= "			<option value='vreg01' " . restore_service_type_state('vreg01', $form_data['service_type']). " >Brand New or Imported Used (Tokunbo) Vehicle</option>";
	$output .= "			<option value='vreg02' " . restore_service_type_state('vreg02', $form_data['service_type']). " >Transfer of License Plate to New or Imported Used (Tokunbo) Vehicle</option>";
	$output .= "		</optgroup>";
	$output .= "		<optgroup label='Change of Ownership / Re-Registration'>";
	$output .= "			<option value='vreg03' " . restore_service_type_state('vreg03', $form_data['service_type']). " >Change of Ownership and Reregistration of previously registered vehicle</option>";
	$output .= "			<option value='vreg04' " . restore_service_type_state('vreg04', $form_data['service_type']). " >Change of Ownership and transfer of license plate</option>";
	$output .= "		</optgroup>";
	$output .= "		<optgroup label='Other'>";
	$output .= "			<option value='tvt' " . restore_service_type_state('tvt', $form_data['service_type']). " >Temporary Vehicle Tag</option>";
	$output .= "		</optgroup>";	
	$output .= "	</select>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='vreg'>" . $atts['label_vreg'] . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='vreg' id='vreg' class='widefat' value='{$form_data['vreg']}'/>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='vmake'>" . $atts['label_vmake'] . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='vmake' id='vmake' class='widefat' value='{$form_data['vmake']}'/>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='mvl_type'>" . $atts['label_mvl_type'] . "</label>";
	$output .= "	<select name='mvl_type' id='mvl_type'>";
	$output .= build_mvl_opts(get_mvl_types(), $form_data);
	$output .= "	</select>";
	$output .= "</div>";
	$output .= build_mvi_segment($form_data, $atts);
	$output .= "<div>";
 	$output .= "	<input type='checkbox' id='rwc' name='rwc' value='true' " . restore_checkbox_state('rwc', $form_data) . " />";
	$output .= "	<label for='rwc'>" . $atts['label_rwc'] . "</label>";
	$output .= "</div>";	
	$output .= "</fieldset>";
	$output .= "<fieldset>";
	$output .= "<legend>Contact Details:</legend>";
	$output .= "<div>";
	$output .= "	<label for='contact_name'>" . $atts['label_contact_name'] . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='contact_name' id='contact_name' class='widefat required' placeholder='Full Name' value='{$form_data['contact_name']}' />" ;
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='contact_email'>" . $atts['label_contact_email'] . "</label><span class='required'>*</span>";
	$output .= "	<input required type='email' name='contact_email' id='contact_email' class='widefat' placeholder='Email Address' value='{$form_data['contact_email']}'/>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='contact_mobile'>" . $atts['label_contact_mobile'] . "</label>";
	$output .= "	<input type='text' name='contact_mobile' id='contact_mobile' class='widefat' placeholder='Mobile Number' value='{$form_data['contact_mobile']}'/>";
	$output .= "</div>";
	$output .= "</fieldset>";
	$output .= "<div>";
	$output .= "	<input type='submit' class='form-submit' value='" . $atts['label_submit'] . "' name='send' id='cf_send' />";
	$output .= "</div>";
	$output .= "</form>";
	
	return $output;
}

function restore_checkbox_state($key, $value){
	$state = '';
	if (array_key_exists($key, $value)){
		if (has_prescence($value[$key])){
			$state = 'checked';
		}
	}
	return $state;
	
}

function restore_service_type_state($value, $selected){
	return ($value === $selected) ? 'selected' : '';
}
