<?php
function build_email_headers($from_email, $from_name=""){
	
	if (trim(empty($from_email))){
		throw new Exception("From email cannot be empty");
	}
	
	if(trim(empty($from_name))){
		$headers = "From: {$from_email}\n"; 
	}
	else {
		$headers = "From: {$from_name} < $from_email>\n"; 
	}
	
	$headers .= "Content-Type: text/plain; charset=UTF-8\n";
	$headers .= "Content-Transfer-Encoding: 8bit\n";
	$headers .= "X-Mailer: PHP/" .phpversion() . "\n";
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "X-Client-IP-Address: " . wbs_get_the_ip() ;
	
	return $headers;
}

function send_email($receipient, $subject, $message, $headers) {
	$resp = wp_mail($receipient, $subject, wordwrap($message, 70), $headers);
	return $resp;
}