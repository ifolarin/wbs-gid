<?php
error_reporting(E_ALL);
/**
 * Plugin Name: Get It Done Plugins by Webbased Solutions
 * Plugin URI: http://www.webbasedsol.com
 * Description: Reminder form, quote form for Get It Done
 * Author: Korede Folarin
 * Author URI: http://www.webbasedsol.com
 * Version: 0.6.0
 * License: GPLv2
 */


if(!defined('ABSPATH')){
	exit;
}

require_once 'contact_form.php';
require_once 'reminder_form.php';
require_once 'quote_form.php';
require_once 'gid_vreg_form.php';



add_shortcode('wbs_contact', 'wbs_contact_sc');

add_shortcode('gid_reminder', 'gid_reminder_sc');

add_shortcode('gid_quote', 'gid_quote_sc');

add_shortcode('gid_vreg', 'gid_vreg_sc');

// function wbs_gid_enqueue_scripts(){

// 	wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
// 	wp_enqueue_style('wbs-gid-css', plugins_url('css/wbs-gid.css', __FILE__), array('jquery-style'));
// 	wp_enqueue_script('wbs-gid-js', plugins_url('js/wbs-gid.js', __FILE__), array('jquery', 'jquery-ui-datepicker'));
		
// }

function wbs_gid_enqueue_scripts(){

	//Enqueue bootstrap
	$style = 'bootstrap';
	if( ( ! wp_style_is( $style, 'queue' ) ) && ( ! wp_style_is( $style, 'done' ) ) ) {
		//queue up your bootstrap
		wp_enqueue_style( $style, plugins_url('css/bootstrap.css', __FILE__));
		wp_enqueue_script('boostrap-js', plugins_url('js/bootstrap.js', __FILE__));
	}

	wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
	wp_enqueue_style('jquery-theme', plugins_url('css/jquery-ui.theme.css', __FILE__), array('jquery-style'));
	wp_enqueue_style('roboto-font', 'https://fonts.googleapis.com/css?family=Roboto');
	wp_enqueue_style('gid-reminder-css', plugins_url('css/wbs-gid-reminder.css', __FILE__));

	// 	wp_enqueue_script('jquery-ui-js', plugins_url('js/jquery-ui.js', __FILE__));
	wp_enqueue_script('gid-reminder-js', plugins_url('js/wbs-gid-reminder.js', __FILE__), array('jquery', 'jquery-ui-datepicker'));

}

add_action('wp_enqueue_scripts', 'wbs_gid_enqueue_scripts');


// Change default WordPress from email address
add_filter('wp_mail_from', 'new_mail_from');
add_filter('wp_mail_from_name', 'new_mail_from_name');

function new_mail_from($old) {
	return isset($_POST["contact_email"]) ? $_POST["contact_email"] : 'wordpress@getitdone.ng';
}
function new_mail_from_name($old) {
	return isset($_POST["contact_name"]) ? $_POST["contact_name"] : 'WordPress Admin';
}