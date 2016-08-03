<?php
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

function wbs_get_post_data() {
	$post_data = array();

	foreach ($_POST as $field => $value) {
		if(get_magic_quotes_gpc()){
			$value = stripslashes($value);
		}
			
		$post_data[$field] = strip_tags($value);
	}
	return $post_data;
}

function build_select_options($options, $form_data, $form_key){
	$selected = "";
	if (array_key_exists($form_key, $form_data)){
		$selected = $form_data[$form_key];
	}

	$output = "";
	foreach ($options as $key => $value){
			$output .= "<option value='{$key}' " . ($selected === $key ? "selected" : "") . " >{$value}</option>";		
	}

	return $output;
}

function build_radio_options($options, $form_data, $form_key, $required=false){

	$output = "";
	$checked = "";
	if(array_key_exists("$form_key", $form_data)){
		$checked = $form_data["$form_key"];
	}

	foreach ($options as $key => $value) {
			$output .= "<input type='radio' name='{$form_key}' value='{$key}' " . ($checked === $key ? " checked " : "") . (true == $required ? " required " : "") . " > {$value}<br />";
	}
	return $output;
}

function build_contact_section($form_data, $atts){
	$output = <<< RENDERSECTION
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
RENDERSECTION;
	
	return $output;
}