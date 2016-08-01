<?php
function build_mvi_segment($form_data, $atts){
	$output = "";

	$output .= "<div>";
	$output .= "	<label for='mvi_type'>" . $atts['label_mvi_type'] . "</label>";
	$output .= "	<select name='mvi_type' id='mvi_type'>";
	$output .= build_mvi_options($form_data, $atts);
	$output .= "	</select>";
	$output .= "</div>";
	$output .= "<div>";
	$output .= "	<label for='mvi_value'>" . $atts['label_mvi_value'] . "</label>";
	$output .= "	<input type='number' name='mvi_value' id='mvi_value' placeholder='Value of vehicle for comprehensive insurance. Must be at least N1M.' min='1000000' step='100000' size='8' value='{$form_data['mvi_value']}'/>";
	$output .= "</div>";

	return $output;
}

function validate_mvi_type($form_data, $atts){
	$errors = array();
	
	if(array_key_exists("mvi_type", $form_data) && "mvi02" === $form_data["mvi_type"]){
		if(empty($form_data["mvi_value"]) || 0 === strlen($form_data["mvi_value"])){
			$errors["mvi_value"] = "Please provide insured vehicle value for comprehensive insurance.";
		} else if($atts["min_mvi_value"] >= (int)$form_data["mvi_value"]){
			$errors["mvi_value"] = "Minimum Insured Value for comprehensive insurance is ". number_format($atts['min_mvi_value']) . ".";
		}
	}
	
	return $errors;
}

function build_mvi_options($form_data){
	$mvi_types = array(
			"" => "",
			"mvi01" => "Third Party Insurance",
			"mvi02" => "Comprehensive Insurance",
	);
	
	return build_select_options($mvi_types, $form_data, 'mvi_type');
}