<?php

function has_prescence($value){
	return isset($value) && $value !== "";
}

function has_max_length($value, $max){
	return strlen($value) <= $max;
}

function has_inclusion_in($value, $set){
	return in_array($value, $set);
}

function format_label($value){
	return ucfirst(str_replace($value, "_", " "));
}

function has_required_fields($atts, $form_data, $required_fields){
	$errors = array();

	foreach ($required_fields as $required_field) {
		$value = trim($form_data[$required_field]);
		if(!has_prescence($value)){
			$errors[$required_field] = ucfirst(str_replace("_", " ",$atts["label_" . $required_field])) . " can not be blank.";

		}
	}

	return $errors;
}

function is_mobile($mobile){
	return preg_match("/^\+?(234|0){1}[789]{1}\d{9,}$/", $mobile);
}

function form_errors($errors=array()){
	$output = "";
	if(!empty($errors)){
		$output = "<div class='notice notice-error is-dismissible alert alert-danger' role='alert' >";
		$output .= "Please fix the following errors:";
		foreach ($errors as $key => $error){
			$output .= "<p>{$error}</p>";
		}
		$output .= "</div>";
	}

	return $output;
}

function form_message($message){
	$output = "";
	if(has_prescence($message)){
		$output .= "<div class='notice notice-success is-dismissible alert alert-success' role='alert'>";
		$output .= "<p><strong>{$message}</strong></p>";
		$output .= "</div>";
	}
	return $output;
}


function render_messages($errors, $atts){
	$output = "";
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		if(!empty($errors)){
			$output .= form_errors($errors);
		}else{
			$output .= form_message($atts["success_message"]);
		}
	}

	return $output;
}

//Contact Form Validations
function validate_contact_data($atts, $form_data, $required_fields, $errors){
	
	$errors = has_valid_email('contact_email', $atts, $form_data, $errors);

	$errors = has_valid_mobile('contact_mobile', $atts, $form_data, $errors);

	return $errors;
}

function has_valid_email($key, $atts, $form_data, $errors){
	if( !isset($errors[$key]) && !is_email($form_data[$key])){
		$errors[$key] = ucfirst(str_replace("_", " ",$atts["label_" . $key])) . " is not valid.";
	}
	return $errors;
}

function has_valid_mobile($key, $atts, $form_data, $errors){
	if(has_prescence($form_data[$key]) && !is_mobile($form_data[$key])){
		$errors[$key] = ucfirst(str_replace("_", " ",$atts["label_" . $key])) . " is not valid.";
	}

	return $errors;
}


