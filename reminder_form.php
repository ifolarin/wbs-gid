<?php


require_once 'functions.php';
require_once 'validation_functions.php';
require_once 'email_functions.php';

function gid_reminder_sc($atts, $content){
	$atts = shortcode_atts(
			array(
					"email" => "services+renewal-reminder@getitdone.ng", //get_bloginfo('admin_email'),
					"subject" => "",
					"label_vreg" => "Vehicle Registration Number",
					"label_vmake" => "Vehicle Make &amp; Model",
					"label_mvl_exp" => "Vehicle Licence (AUTOREG<sup>&reg;</sup>) Expiry Date",
					"label_mvi_exp" => "Vehicle Insurance Expiry Date",
					"label_rwc_exp" => "Road Worthiness Certificate Expiry Date",
					"label_hkny_exp" => "Hackney Permit Expiry Date",
					"label_contact_name" => "Your Full Name",
					"label_contact_email" => "Your E-mail Address",
					"label_contact_mobile" => "Your Mobile Number",
					"label_submit" => "Remind me when renewals are due",
					"mobile_regex" => "^(?:234|0){1}[789]{1}\d{9,}?",	
					"vehicle_section_title" => "Vehicle Details",
					"contact_section_title" => "Contact Information",
					// the error message when at least one of the required fields is empty
					"error_empty" => 'Please fill in all the required fields.',
					// the error message when the e-mail address is not valid:
					"error_noemail" => "Please enter a valid e-mail address.",
					// the success message when the email is sent:
					"success_message" => "Your vehicle details have been recorded and you will be reminded before renewal is due",
			), $atts
		);
	
	$renewal_data = reset_renewal_form(array());
	$errors = array();
	$message = "";
	
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$renewal_data = wbs_get_post_data();
		$errors = validate_reminder_data($atts, $renewal_data);
		
// 		print_r($errors);
		
		if(empty($errors)){
			$resp = send_email(
						$atts["email"], 
						"Renewal Reminder " . $renewal_data["vreg"], 
						build_message($renewal_data), 
						build_email_headers($renewal_data["contact_email"], $renewal_data["contact_name"]));
			
			
			if(false === $resp){
				$errors["message_not_sent"] = "Oops there was an error sending your message. This issue has been reported.";
			}
			$renewal_data = reset_renewal_form($renewal_data);
			
			
		}
	}
	
	return build_reminder_form($atts, $renewal_data, $errors);
	
}

function build_message($data){
	$message = "";
	
	$message .= "Registration Number: {$data["vreg"]}\n";
	$message .= "Vehicle Make: {$data["vmake"]}\n";
	$message .= "MVL Expiry: {$data["mvl_exp"]}\n";
	$message .= "MVI Expiry: {$data["mvi_exp"]}\n";
	$message .= "RWC Expiry: {$data["rwc_exp"]}\n";
	$message .= "HKNY Expiry: {$data["hkny_exp"]}\n";
	$message .= "Contact Name: {$data["contact_name"]}\n";
	$message .= "Contact Email: {$data["contact_email"]}\n";
	$message .= "Contact Mobile: {$data["contact_mobile"]}\n";

	return wordwrap($message, 70);
}

function reset_renewal_form($data){
	$data["vreg"] = "";
	$data["vmake"] = "";
	$data["mvl_exp"] = "";
	$data["mvi_exp"] = "";
	$data["rwc_exp"] = "";
	$data["hkny_exp"] = "";
	$data["contact_name"] = "";
	$data["contact_email"] = "";
	$data["contact_mobile"] = "";
		
	return $data;
}

function validate_reminder_data($atts, $form_data){
	$required_fields = array("vreg", "contact_name", "contact_email");
	
	$errors =  has_required_fields($atts, $form_data, $required_fields);
	
	foreach (array("mvl_exp", "mvi_exp", "rwc_exp") as $required_field) {
		$value = trim($form_data[$required_field]);
		if(!empty($value) && (false === strtotime($value))){
			$errors[$required_field] = ucfirst(str_replace("_", " ",$atts["label_" . $required_field])) . " format is not valid.";
		}
	}
	
	$errors = validate_contact_data($atts, $form_data, $required_fields, $errors);
	
	return $errors;
}

function build_reminder_form($atts, $form_data, $errors=array()){
// 	print_r($form_data);
	$output  = "";
	
	$output .= render_messages($errors, $atts);
	$form_action = get_permalink();
	$output .= <<< RENDERFORM
		<h3> {$atts['label_submit']} </h3>
		<form id='gid-reminder-form'  name='gid-reminder-form' method='post' action="{$form_action}">
			<fieldset>
				<h3>{$atts['vehicle_section_title']}</h3>
				<div class="row">
					<div class="col-xs-12 col-md-6">
						<div class="form-group">
							<label for="vreg"> {$atts['label_vreg']} </label> <input
								type="text" class="form-control" id="vreg" name="vreg"
								placeholder="Vehicle Registration Number" value="{$form_data['vreg']}" required>
						</div>
					</div>
					<div class="col-xs-12 col-md-6">
						<div class="form-group">
							<label for="vmake">{$atts['label_vmake']}</label> <input
								type="text" class="form-control" id="vmake" name="vmake"
								placeholder="Vehicle Make & Model e.g Toyota Corolla" value="{$form_data['vmake']}">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-md-6">
						<div class="form-group">
							<label for="mvl_exp">{$atts['label_mvl_exp']}</label> <input type="text" class="form-control datepicker" id="mvl_exp"
								name="mvl_exp" placeholder="Vehicle License expiry date" value="{$form_data['mvl_exp']}">
						</div>
					</div>
					<div class="col-xs-12 col-md-6">
						<div class="form-group">
							<label for="mvi_exp">{$atts['label_mvi_exp']}</label> <input
								type="text" class="form-control datepicker" id="mvi_exp"
								name="mvi_exp" placeholder="Vehicle Insurance expiry date" value="{$form_data['mvi_exp']}">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-md-6">
						<div class="form-group">
							<label for="rwc_exp">{$atts['label_rwc_exp']}</label> <input type="text" class="form-control datepicker"
								id="rwc_exp" name="rwc_exp"
								placeholder="Road Worthiness Certificate expiry date" value="{$form_data['rwc_exp']}">
						</div>
					</div>
					<div class="col-xs-12 col-md-6">
						<div class="form-group">
							<label for="hkny_exp">{$atts['label_hkny_exp']}</label> <input
								type="text" class="form-control datepicker" id="hkny_exp"
								name="hkny_exp" placeholder="Hackney Permit expiry date" value="{$form_data['hkny_exp']}">
						</div>
					</div>					
				</div>
			</fieldset>
			<fieldset>
				<h3>{$atts['contact_section_title']}</h3>
				<div class="row">
					<div class="col-xs-12 col-md-6">
						<div class="form-group">
							<label for="contact_name">{$atts['label_contact_name']}</label> <input
								type="text" class="form-control" id="contact_name"
								name="contact_name" placeholder="Full Name" value="{$form_data['contact_name']}" required>
						</div>
					</div>
					<div class="col-xs-12 col-md-6">
						<div class="form-group">
							<label for="contact_email">{$atts['label_contact_email']}</label> <input
								type="email" required class="form-control" id="contact_email"
								name="contact_email" placeholder="Email Address" value="{$form_data['contact_email']}" required>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-md-6">
						<div class="form-group">
							<label for="contact_mobile">{$atts['label_contact_mobile']}</label> <input
								type="text" class="form-control" id="contact_mobile"
								name="contact_mobile" placeholder="Mobile Number" value="{$form_data['contact_mobile']}">
						</div>
					</div>

				</div>
			</fieldset>
			<div class="row">
						<div class="col-md-12">
							<button class="btn btn-default">{$atts['label_submit']}</button>
						</div>
					</div>
		</form>
RENDERFORM;
	return $output;
}
