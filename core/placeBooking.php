<?php
/*
	3 Sheep Appointment Booking

	2012-03-14 - V.0.1 - Initial Version
*/

$HOME_DIR = getenv("DOCUMENT_ROOT");
$bookings_table = "tsbooking_bookings";
	
include_once($HOME_DIR . '/wp-config.php');
include_once($HOME_DIR . '/wp-load.php');
include_once($HOME_DIR . '/wp-includes/wp-db.php');

$booking_length = get_option('tsbooking_booklength') * 60;
$email_from = get_option('tsbooking_email_from');
$email_bcc = get_option('tsbooking_email_bcc');
$email_subject = get_option('tsbooking_email_subject');
$email_message = get_option('tsbooking_email_message');

$table_name = $wpdb->prefix .$bookings_table;
$appointment = $wpdb->escape(isset($_GET['appt']) ? $_GET['appt'] : '');
$first_name = $wpdb->escape(isset($_GET['fname']) ? $_GET['fname'] : '');
$last_name = $wpdb->escape(isset($_GET['lname']) ? $_GET['lname'] : '');
$job = $wpdb->escape(isset($_GET['job']) ? $_GET['job'] : '');
$company = $wpdb->escape(isset($_GET['company']) ? $_GET['company'] : '');
$email = $wpdb->escape(isset($_GET['email']) ? $_GET['email'] : '');
$tel = $wpdb->escape(isset($_GET['tel']) ? $_GET['tel'] : '');
$source = $wpdb->escape(isset($_GET['source']) ? $_GET['source'] : '');
$mail = $wpdb->escape(isset($_GET['mail']) ? $_GET['mail'] : '');

if ($wpdb->update( 
		$table_name, 
		array( 
			'first_name' => $first_name,
			'last_name' => $last_name,
			'job' => $job,
			'email' => $email,
			'company' => $company,
			'telephone' => $tel,
			'source' => $source,
			'mailinglist' => (strlen($mail) == 0 ? "0" : "1"),
		), 
		array( 'time' => $appointment )
	)) {
	$email_subject .= ' - ' . date("H:i", strtotime($appointment)) . '-' . date("H:i", strtotime($appointment) + $booking_length) . ' ' . date("d/m/Y", strtotime($appointment));
	$email_body = "An appointment has been made at " . date("H:i", strtotime($appointment)) . " on " . date("d/m/Y", strtotime($appointment)) . " " . $email_message . "\r\n";
	$email_body .= "Your Details:\r\n";
	$email_body .= $first_name . " " . $last_name ."\r\n";
	$email_body .= $job . "\r\n";
	$email_body .= $email. "\r\n";
	$email_body .= $company . "\r\n";
	$email_body .= "Email: " . $email . "\r\n";
	$email_body .= "Telephone: " . $tel . "\r\n\r\n";
	$email_body .= "How you heard about us: " . $source . "\r\n";
	$email_body .= "Thank you for interest.\r\n";
	$email_body = wordwrap($email_body, 70);
	
	// Email the confirmation
	$headers = 'From: ' . $email_from . "\r\n";
	$headers .= 'Bcc: ' . $email_bcc . "\r\n";
	mail($email, $email_subject, $email_body, $headers);
	
	echo "BOOKED";
} else {
	echo "ERROR";
}

?>
