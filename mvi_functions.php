<?php
function build_mvi_segment($form_data, $atts){
	$build_mvi_options = 'build_mvi_options';
	$output = <<< RENDERFORM
<div class="row">
	<div class="col-xs-12 col-md-7">
		<div class="form-group">
			<label for='mvi_type'>  {$atts['label_mvi_type']} </label>
			<select name='mvi_type' id='mvi_type' class="form-control">
				{$build_mvi_options($form_data, $atts)}
			</select>
		</div>
	</div>
	<div class="col-xs-12 col-md-5">
		<div class="form-group">
			<label for='mvi_value'> {$atts['label_mvi_value']} </label>
			<div class="input-group">
				<div class="input-group-addon">&#8358;</div>
					<input type='number' class="form-control" name='mvi_value' id='mvi_value' placeholder='Min 500,000' min='500000' step='100000' size='8' value='{$form_data['mvi_value']}'/
				</div>
			</div>
		</div>
	</div>
</div>
RENDERFORM;
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
			"" => "--Select Insurance Type--",
			"mvi01" => "Third Party Insurance",
			"mvi02" => "Comprehensive Insurance",
	);
	
	return build_select_options($mvi_types, $form_data, 'mvi_type');
}