<?php

require_once 'functions.php';
require_once 'validation_functions.php';
require_once 'email_functions.php';
require_once 'contact_form_functions.php';
require_once 'mvi_functions.php';

function gid_vreg_sc($atts, $content){
	$atts = shortcode_atts(
			array(
					"email" => "services+reg-quote@getitdone.ng", //get_bloginfo('admin_email'),
					//Form Labels 
					"label_vreg" => "Vehicle Registration Number",
					"label_vmake" => "Vehicle Make &amp; Model",
					"label_vmfg_year" => "Vehicle Manufacture Year",
					"label_vreg_type" => "Registration Type",
					"label_vchasis_no" => "Chasis No.",
					"label_vengine_no" => "Engine No.",
					"label_vcolor" => "Color",
					"label_vreg_tint" => "Factory Window Tints",
					"label_owner_title" => "Title",
					"label_owner_surname" => "Surname",
					"label_owner_first_name" => "First Name",
					"label_owner_other_name" => "Other Name",
					"label_owner_marital_status" => "Marital Status",
					"label_owner_id_means" => "Means of Identification",
					"label_owner_utility_type" => "Utility Bill",
					"label_owner_occupation" => "Occupation",
					"label_owner_address" => "Address",
					"label_owner_email" => "Email",
					"label_owner_mobile" => "Mobile",
					"label_mvi_type" => "Vehicle Insurance Type",
					"label_mvi_value" => "Insured Value",
					"label_vreg_marks" => "Vehicle Marks (e.g. old/previous registration number inscribed on car parts)",
					"label_submit" => "Submit",
					//Form attributes and properties
					"mobile_regex" => "^(234|0)+[789]{1}\d{9,}",
					"vmfg_year_min" => "1980",
					"vmake_regex" => "^((\b[a-zA-Z0-9]{2,40}\b)\s*){2,}$",
					"vchasis_no_regex" => "^([a-zA-Z0-9\-]{5,40})$",
					"vcolor_regex" => "^([a-zA-Z0-9\-]{3,20})$",
					// Input form placeholders
					"vreg_ph" => "Vehicle Registration Number",
					"vmake_ph" => "Vehicle Manufacturer & Model e.g. Toyota Corolla",
					"vmfg_year_ph" => "Vehicle Year of Manufacture",
					"vchasis_no_ph" => "Vehicle Chasis No.",
					"vengine_no_ph" => "Vehicle Engine No.",
					"vcolor_ph" => "Vehicle Color",
					// the error message when at least one of the required fields is empty
					"error_empty" => 'Please fill in all the required fields.',
					"vreg_empty" => 'Please provide vehicle registration number for change of ownership',
					"vchasis_no_error" => 'Please enter a chasis no. of at least 5 characters and contains numbers, letters and - only',
					"vcolor_error" => "Please the closest color to the color of the vehicle. Complex colors (e.g. midnight blue) not support",
					// the error message when the e-mail address is not valid:
					"error_noemail" => "Please enter a valid e-mail address.",
					// the success message when the email is sent:
					"success_message" => "Your vehicle details have been recorded and we will remind you before renewal is due",
					"message_not_sent" => "Oops! Something did not go as planned, an administrator has been notified.",
					"min_mvi_value" => 1000000,
			), $atts
		);
	
	$form_data = reset_vreg_quote_form(array());
	$errors = array();
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$form_data = wbs_get_post_data();
// 		print_r($form_data);
		$form_data = array_merge(reset_vreg_quote_form(array()), $form_data);
// 		print_r($form_data);
		$errors = validate_vreg_quote_data($form_data, $atts);
		
		if(empty($errors)){
			$resp = send_email(
					$atts['email'], 
					'Request for Vehicle Registration Quote', 
					wordwrap(build_vreg_quote_message($form_data, $atts), 70),
					build_email_headers($form_data['owner_email'], $form_data['owner_first_name'] . " " . trim(" ", $form_data['owner_other_name']) . " " . $form_data['owner_surname']));
			
			if(false === $resp){
				$errors['message_not_sent'] = $atts['message_not_sent'];
			}
			
			$form_data = reset_vreg_quote_form(array());
		}
	}
	
	
	
	return build_vreg_quote_form($atts, $form_data, $errors);
}


function build_vreg_quote_message($form_data, $atts){
	$message = "";
	
	foreach($form_data as $field => $value){
		$message .= $atts["label_{$field}"] . " = " . $value;
	}
	
	return $message;
}

function validate_vreg_quote_data($form_data, $atts){
	$required_fields = array("vreg_type", "vmake", "vmfg_year", "vchasis_no", "vcolor", "owner_title", "owner_first_name", "owner_surname", "owner_marital_status", "owner_id_means", "owner_utility_type",
			"owner_occupation", "owner_address", "owner_email");
	
	$errors = has_required_fields($atts, $form_data, $required_fields);
	
	$mvi_errors = validate_mvi_type($form_data, $atts);
	
	
	if( has_inclusion_in($form_data['vreg_type'], array("vreg03", "vreg04"))){
		if(!has_prescence($form_data["vreg"])){
			$errors['vreg'] = $atts['vreg_empty'];
		}
	}
	
	$errors = array_merge($errors, $mvi_errors);
	
	return $errors;
	
}

function reset_vreg_quote_form($data){
	$data["vreg"] = "";
	$data["vreg_type"] = "";
	$data["vmake"] = "";
	$data["vmfg_year"] = "";
	$data["vchasis_no"] = "";
	$data["vengine_no"] = "";
	$data["vcolor"] = "";
	$data["vreg_tint"] = false;
	$data["owner_title"] = "";
	$data["owner_first_name"] = "";
	$data["owner_other_name"] = "";
	$data["owner_surname"] = "";
	$data["owner_marital_status"] = "";
	$data["owner_id_means"] = "";
	$data["owner_utility_type"] = "";
	$data["owner_occupation"] = "";
	$data["owner_address"] = "";
	$data["owner_email"] = "";
	$data["owner_mobile"] = "";
	$data["mvi_type"] = "";
	$data["mvi_value"] = "";
	$data["vreg_marks"] = false;
	
	return $data;
}

function build_vreg_opts($form_data, $form_key){
	$vreg_types = array(
			"" => "--Select a registration option--",
			"vreg01" => "New unregistered vehicle (Brand New or Tokunbo)",
			"vreg02" => "Transfer of License plate to new unregistered vehicle",
			"vreg03" => "Change of Ownership and Registration of previously registered vehicle",
			"vreg04" => "Change of Ownership and transfer of license plate",
			"vreg05" => "Temporary Vehicle Permit"
	);
	
	return build_select_options($vreg_types, $form_data, $form_key);
}

function build_marital_status_options($form_data, $form_key){
	$vreg_marital_status = array(
			"" => "",
			"single" => "Single",
			"married" => "Married",
			"separated" => "Separated",
			"divorced" => "Divorced",
			"widowed" => "Widowed"
	);
	
	return build_select_options($vreg_marital_status, $form_data, $form_key);
}

function build_identity_options($form_data, $form_key){
	$vreg_identity = array(
			"" => "",
			"id01" => "National Driver&apos;s License",
			"id02" => "International Passport",
			"id03" => "National Identity Card",
			"id04" => "Voter's Card",
			"id05" => "Other"
	);
	
	return build_select_options($vreg_identity, $form_data, $form_key);
}

function build_utility_options($form_data, $form_key){
	$vreg_utility = array(
			"" => "",
			"util01" => "LAWMA Bill", 
			"util02" => "Electricity Bill",
			"util03" => "Water Bill",
			"util04" => "Land Use Charge",
			"util05" => "Other"
	);
	return build_select_options($vreg_utility, $form_data, $form_key);
}

function build_owner_title_list_options(){
	$vreg_owner_title = array(
			"Mr.",
			"Mrs.",
			"Miss",
			"Mr. & Mrs.",
			"Dr.",
			"Dr. (Mrs.)",
			"Dr. & Mrs.",
			"Mr. & Dr. (Mrs.)",
			"Prof.",
			"Chief",
			"Rev.",	
			"Pastor",
	);
	
	$output = "";
	foreach ($vreg_owner_title as $field => $value){
		$output .= "<option value='{$value}' />";
	}

	return $output;
}

function build_vreg_quote_form($atts, $form_data, $errors=array()){
	$output  = "";
	$output .= render_messages($errors, $atts);

	$output .= "<form id='vreg_quote_form' name='vreg_quote_form' method='post' action='" . get_permalink(). "'>";
	$output .= "<fieldset>";
	$output .= "<legend>Vehicle Details:</legend>";
	$output .= "<div>";
	$output .= "	<label for='vreg_type'>" . esc_attr($atts['label_vreg_type']) . "</label><br/>";
	$output .= "	<select name='vreg_type' id='vreg_type' required>";
	$output .= "		<option value=''>--Select a type of registration--</option>";
	$output .= "		<optgroup label='First Registration'>";
	$output .= "			<option value='vreg01'>Brand New or Imported Used (Tokunbo) Vehicle</option>";
	$output .= "			<option value='vreg02'>Transfer of License Plate to New or Imported Used (Tokunbo) Vehicle</option>";
	$output .= "		</optgroup>";
	$output .= "		<optgroup label='Change of Ownership / Re-Registration'>";
	$output .= "			<option value='vreg03'>Change of Ownership and Reregistration of previously registered vehicle</option>";
	$output .= "			<option value='vreg04'>Change of Ownership and transfer of license plate</option>";
	$output .= "		</optgroup>";
	$output .= "		<optgroup label='Other'>";
	$output .= "			<option value='tvt'>Temporary Vehicle Tag</option>";
	$output .= "		</optgroup>";	
	$output .= "	</select>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='vreg'>" . esc_attr($atts['label_vreg']) . "</label>";
	$output .= "	<input type='text' name='vreg' id='vreg' class='widefat' placeholder='" . esc_attr($atts['vreg_ph']) .  "' value='" . esc_attr($form_data['vreg']) . "'/>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='vmake'>" . esc_attr($atts['label_vmake']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='vmake' id='vmake' class='widefat' placeholder='" . esc_attr($atts['vmake_ph']). "' value='" . esc_attr($form_data['vmake']) . "' pattern='" . esc_attr($atts['vmake_regex']). "' title='Please enter the manufacturer and model of your car'/>";
	$output .= "</div>";	
	$output .= "<div>";
	$output .= "	<label for='vmfg_year'>" . esc_attr($atts['label_vmfg_year']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='number' size='4' min='" . esc_attr($atts['vmfg_year_min']) . "' max='" . (date('Y') + 1) . "' name='vmfg_year' id='vmfg_year' placeholder='" . esc_attr($atts['vmfg_year_ph']) .  "' value='" . esc_attr($form_data['vmfg_year']) . "' title='Please enter vehicle manufacture year. Enter " . esc_attr($atts['vmfg_year_min']) . " for vehicle manufactured before then.' />";
	$output .= "</div>";	
	$output .= "<div>";
	$output .= "	<label for='vchasis_no'>" . esc_attr($atts['label_vchasis_no']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='vchasis_no' id='vchasis_no' class='widefat' placeholder='" . esc_attr($atts['vchasis_no_ph']). "' value='{$form_data['vchasis_no']}' pattern='" . esc_attr($atts['vchasis_no_regex']). "' title='" . esc_attr($atts['vchasis_no_error']). "' />";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='vengine_no'>" . esc_attr($atts['label_vengine_no']) . "</label><span class='required'>*</span>";
	$output .= "	<input type='text' name='vengine_no' id='vengine_no' class='widefat' placeholder='" . esc_attr($atts['vengine_no_ph']) . "' value='{$form_data['vengine_no']}'/>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='vcolor'>" . esc_attr($atts['label_vcolor']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='vcolor' id='vcolor' class='widefat' placeholder='" .esc_attr($atts['vcolor_ph']). "' value='{$form_data['vcolor']}' pattern='" . esc_attr($atts["vcolor_regex"]). "' title='" . esc_attr($atts['vcolor_error']). "' />";
	$output .= "</div>";	
	$output .= "</fieldset>";
	$output .= "<fieldset>";
	$output .= "<legend>Owner Details:</legend>";
	$output .= "<div>";
	$output .= "	<label for='owner_title'>" . esc_attr($atts['label_owner_title']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='owner_title' id='owner_title' list='owner_title_list' placeholder='New Vehicle Owner&apos;s Title. e.g. Mr. Dr. Chief' value='{$form_data['owner_surname']}' />" ;
	$output .= "	<datalist id='owner_title_list'>";
	$output .= build_owner_title_list_options();
	$output .= "	</datalist>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='owner_surname'>" . esc_attr($atts['label_owner_surname']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='owner_surname' id='owner_surname' class='widefat required' placeholder='New Vehicle Owner&apos;s Surname' value='{$form_data['owner_surname']}' />" ;
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='owner_first_name'>" . esc_attr($atts['label_owner_first_name']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='owner_first_name' id='owner_first_name' class='widefat required' placeholder='New Vehicle Owner&apos;s First Name' value='{$form_data['owner_first_name']}' />" ;
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='owner_other_name'>" . esc_attr($atts['label_owner_other_name']) . "</label><span class='required'>*</span>";
	$output .= "	<input type='text' name='owner_other_name' id='owner_other_name' class='widefat required' placeholder='New Vehicle Owner&apos;s Other Name' value='{$form_data['owner_other_name']}' />" ;
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='owner_marital_status'>" . esc_attr($atts['label_owner_marital_status']) . "</label><span class='required'>*</span><br />";
	$output .= "	<select name='owner_marital_status' id='owner_marital_status' required>";
	$output .= build_marital_status_options($form_data, 'owner_marital_status');	
	$output .= "	</select>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='owner_id_means'>" . esc_attr($atts['label_owner_id_means']) . "</label><span class='required'>*</span><br />";
	$output .= "	<select name='owner_id_means' id='owner_id_means' required>";
	$output .= build_identity_options($form_data, 'owner_id_means');
	$output .= "	</select>";
	$output .= "</div>";	
	$output .= "<div>";
	$output .= "	<label for='owner_utility_type'>" . esc_attr($atts['label_owner_utility_type']) . "</label><span class='required'>*</span><br />";
	$output .= "	<select name='owner_utility_type' id='owner_utility_type' required>";
	$output .= build_utility_options($form_data, 'owner_utility_type');
	$output .= "	</select>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='owner_occupation'>" . esc_attr($atts['label_owner_occupation']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='owner_occupation' id='owner_occupation' class='widefat' placeholder='Owner&apos;s Occupation' value='" . esc_attr($form_data['owner_occupation']) . "'/>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='owner_address'>" . esc_attr($atts['label_owner_address']) . "</label><span class='required'>*</span>";
	$output .= "	<textarea required name='owner_address' id='owner_address' class='widefat' rows='5'>" . esc_textarea($form_data['owner_address']) . "</textarea>"; 
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='owner_email'>" . esc_attr($atts['label_owner_email']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='email' name='owner_email' id='owner_email' class='widefat' placeholder='Owner&apos;s Email Address' value='" . sanitize_email($form_data['owner_email']) . "'/>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='owner_mobile'>" . esc_attr($atts['label_owner_mobile']) . "</label><span class='required'>*</span>";
	$output .= "	<input required type='text' name='owner_mobile' id='owner_mobile' class='widefat' placeholder='Owner&apos;s Mobile Number. e.g. 08096633961 or 2348096633961' pattern='" . $atts['mobile_regex'] . "' value='" . esc_attr($form_data['owner_mobile']) . "'/>";
	$output .= "</div>";
	$output .= "</fieldset>";
	$output .= "<fieldset>";
	$output .= "<legend>Other Details:</legend>";
	$output .= build_mvi_segment($form_data, $atts);
	$output .= "<div>";
	$output .= "	<input type='checkbox' name='vreg_tint' value='vreg_tint'/>";
	$output .= "	<label for='vreg_tint'>" . $atts['label_vreg_tint'] . "</label>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<input type='checkbox' name='vreg_marks' value='vreg_marks'/>";
	$output .= "	<label for='vreg_marks'>" . $atts['label_vreg_marks'] . "</label>";
	$output .= "</div>";
	$output .= "</fieldset>";
	$output .= "<div>";
// 	$output .= "	<input type='submit' class='form-submit' value='" . $atts['label_submit'] . "' name='send' id='cf_send' />";
	$output .= "	<button type='submit'>" . $atts['label_submit'] . "</button>";
	$output .= "</div>";	
	$output .= "</form>";

	return $output;
}



