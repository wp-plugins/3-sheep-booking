<?php
/*
Plugin Name: 3 Sheep Appointment Booking
Plugin URI: http://3sheep.co.uk
Description: A simple appointment booking system from 3 Sheep Ltd.
Author: 3 Sheep Ltd.
Version: 1.00
Author URI: http://3sheep.co.uk
*/

$tsbooking_version = '1.00';
$bookings_table = "tsbooking_bookings";

// Create the database extension
function tsbooking_install() {
   global $wpdb;
   global $tsbooking_version;
   $bookings_table = "tsbooking_bookings";

   $table_name = $wpdb->prefix . $bookings_table;
   $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  first_name text,
	  last_name text,
	  job text,
	  company text,
	  email text,
	  telephone text,
	  mailinglist boolean,
	  source tinytext,
	  UNIQUE KEY id (id)
    );";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($sql);
 
   add_option("tsbooking_version", $tsbooking_version);
}
register_activation_hook(__FILE__,'tsbooking_install');

// Brandword Availability Checking
function tsbooking_makebooking( $atts ) {
   	global $wpdb;
	global $bookings_table;
	
	$booking_length = get_option('tsbooking_booklength') * 60;
	
	extract( shortcode_atts( array(), $atts));

	// Get the list of available appointments
   	$table_name = $wpdb->prefix . $bookings_table;
   	$dbresult = $wpdb->get_results("SELECT time FROM $table_name  WHERE first_name IS NULL OR LENGTH(first_name) = 0 ORDER BY time");
  	
	if (count($dbresult) == 0) {
		$result = '<div id="tsbooking">
				<div id="tsbooking_message"><h3>There are no appointments currently available.</h3></div>
			</div>';
	} else {
		$result = '<div id="tsbooking">
				<div id="tsbooking_message"></div>
				<div id="tsbooking_book">
					<form>
						<p class="tsbooking_footnote">*(denotes required field)</p>
						<label class="tsbooking_itemlabel" for="tsbooking_selecttime">Select Appointment</label>
						<select class="tsbooking_itemselect" id="tsbooking_selecttime" name="tsbooking_selecttime">';
				
		
		foreach($dbresult as $aResult) {
			$result .= '<option value="' . $aResult->time . '">' . date("H:i", strtotime($aResult->time)) . '-' . date("H:i", strtotime($aResult->time) + $booking_length) . ' ' . date("d/m/Y", strtotime($aResult->time)) . '</option>';
		}

		$result .= '	</select>
						<br />
						
						<label class="tsbooking_itemlabel" for="tsbooking_firstname">First Name*</label>
						<input class="tsbooking_itemtextentry" type="text" id="tsbooking_firstname" name="tsbooking_firstname"/> <br />
		
						<label class="tsbooking_itemlabel" for="tsbooking_lastnamename">Last Name*</label>
						<input class="tsbooking_itemtextentry" type="text" id="tsbooking_lastnamename" name="tsbooking_lastnamename"/> <br />
	
						<label class="tsbooking_itemlabel" for="tsbooking_job">Job Title*</label>
						<input class="tsbooking_itemtextentry" type="text" id="tsbooking_job" name="tsbooking_job"/> <br />
	
						<label class="tsbooking_itemlabel" for="tsbooking_company">Company*</label>
						<input class="tsbooking_itemtextentry" type="text" id="tsbooking_company" name="tsbooking_company"/> <br />
	
						<label class="tsbooking_itemlabel" for="tsbooking_email">Email Address*</label>
						<input class="tsbooking_itemtextentry" type="text" id="tsbooking_email" name="tsbooking_email"/> <br />
	
						<label class="tsbooking_itemlabel" for="tsbooking_telephone">Contact Telephone</label>
						<input class="tsbooking_itemtextentry" type="text" id="tsbooking_telephone" name="tsbooking_telephone"/> <br />

						<label class="tsbooking_itemlabel" for="tsbooking_source">Where did you hear about us?*</label>
						<select class="tsbooking_itemselect" id="tsbooking_source" name="tsbooking_source" onchange="updateOther();" >
							<option value="">Please Select</option>
							<option value="Advert">Advert</option>
							<option value="Invitation">Invitation</option>
							<option value="Twitter">Twitter</option>
							<option value="Other">Other</option>
						</select>
						<br />	
						<label class="tsbooking_itemlabel" for="tsbooking_othersource"></label>
						<input class="tsbooking_itemtextentry" type="text" id="tsbooking_othersource" name="tsbooking_othersource"  disabled="disabled"/> <br />
						
						<label class="tsbooking_itemlabel" for="tsbooking_mailinglist">Add me to the mailing list</label>
						<input class="tsbooking_itemchcekbox" type="checkbox" id="tsbooking_mailinglist" name="tsbooking_mailinglist" /> <br />
						<br />
						
						<input type="submit" value="Book Appointment" onclick="TSBookingBookAppointment(\'' . WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) . '\'); return false;" />
					</form>
				</div>
			</div>';
	}

	return $result;
}
add_shortcode( 'tsbooking', tsbooking_makebooking);

// The Settings Interface

add_option('tsbooking_booklength', 15); 																		// Booking Length
add_option('tsbooking_email_from',  'No Reply <no@reply.com>'); 											// From Email Address
add_option('tsbooking_email_bcc', 'my@email.address.com'); 														// Email BCC Address for confirmation emails
add_option('tsbooking_email_subject', "Meeting Booked"); 											// Email subject prefix
add_option('tsbooking_email_message', ", see you there."); // Email message completion

function tsbooking_options_page() {
	global $wpdb;
	global $bookings_table;
	
   	$table_name = $wpdb->prefix . $bookings_table;

	global $tsbooking_version;

	if (isset($_POST['set_defaults'])) {
		echo '<div id="message" class="updated fade"><p><strong>';

		update_option('tsbooking_booklength', 15); 																			// Booking Length
		update_option('tsbooking_email_from',  'No Reply <no@reply.com>'); 											// From Email Address
		update_option('tsbooking_email_bcc', 'my@email.address.com'); 														// Email BCC Address for confirmation emails
		update_option('tsbooking_email_subject', "Meeting Booked"); 												// Email subject prefix
		update_option('tsbooking_email_message', ", see you there."); 	// Email message completion

		echo 'Default Options Loaded!';
		echo '</strong></p></div>';

	} else if (isset($_POST['appts_reset'])) {
		echo '<div id="message" class="updated fade"><p><strong>';
		$selectedAppts = $_POST['tsbooking_appt_row'];
		$itemCount = count($selectedAppts);
		
		for ($pos = 0; $pos < $itemCount; ++$pos) {
			$wpdb->update( 
					$table_name, 
					array( 
						'first_name' => NULL,
						'last_name' => NULL,
						'job' => NULL,
						'email' => NULL,
						'company' => NULL,
						'telephone' => NULL,
						'source' => NULL,
						'mailinglist' => "0"),
					array( 'id' => $selectedAppts[$pos] )
				);	
		}
		
		echo 'Reset ' . $itemCount . ' appointment(s).';
		echo '</strong></p></div>';
	} else if (isset($_POST['appts_delete'])) {
		echo '<div id="message" class="updated fade"><p><strong>';
		$selectedAppts = $_POST['tsbooking_appt_row'];
		$itemCount = count($selectedAppts);
		
		if ($itemCount > 0) {
			$query = "DELETE FROM $table_name WHERE id IN (";
			
			for ($pos = 0; $pos < $itemCount; ++$pos) {
				if ($pos != 0) $query .= ',';
				$query .= "'" . $selectedAppts[$pos] . "'";
			}
			$query .= ")";
			
			$wpdb->query($query);
		}
		
		echo 'Deleted ' . $itemCount . ' appointment(s).';
		echo '</strong></p></div>';
	} else if (isset($_POST['appts_add'])) {
		echo '<div id="message" class="updated fade"><p><strong>';
		$aptsDate = $_POST['tsbooking_apptdate'];
		$aptsTime = $_POST['tsbooking_appttime'];
		$newAppt = strtotime($aptsTime . ' ' . $aptsDate);
		
		if ($newAppt) {	
			$wpdb->insert( 
				$table_name, 
				array( 
					'time' =>  date("Y/m/d H:i:s", $newAppt)
				)
			);
			echo 'Added appointment at ' . date("H:i d/m/Y", $newAppt);
		} else {
			echo 'Requested appointment date not understood';
		}
		echo '</strong></p></div>';
	} else if (isset($_POST['info_update'])) {
		$length = $_POST['tsbooking_settings_length'];
		$from = $_POST['tsbooking_settings_from'];
		$bcc = $_POST['tsbooking_settings_bcc'];
		$subject = $_POST['tsbooking_settings_subject'];
		$message = $_POST['tsbooking_settings_emailmessage'];
		
		echo '<div id="message" class="updated fade"><p><strong>';

		update_option('tsbooking_booklength', $length); 		// Booking Length
		update_option('tsbooking_email_from', $from); 			// From Email Address
		update_option('tsbooking_email_bcc', $bcc); 			// Email BCC Address for confirmation emails
		update_option('tsbooking_email_subject', $subject); 	// Email subject prefix
		update_option('tsbooking_email_message', $message); 	// Email message completion

		echo 'Configuration Updated!';
		echo '</strong></p></div>';
	} 
	?>

	<div class=wrap>

	<h2>3 Sheep Appointment Booking v<?php echo $tsbooking_version; ?></h2>


	<h3>Usage</h3>
	<ul>
	<li>To insert the booking form into a page or post use the [tsbooking] shortcode.</li>
	</ul>

	<!-- The list of current appointments -->
 	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<h3>Current Appointments</h3>
 	
 	<table class="tsbooking_appts">
 	<tr><th></th><th>Appointment</th><th>Name</th><th>Job Title</th><th>Company</th><th>Email Address</th><th>Telephone</th><th>Source</th><th>Mailing List</th></tr>
 	<?php 
 	$dbresult = $wpdb->get_results("SELECT * FROM $table_name ORDER BY time");
 	$booking_length = get_option('tsbooking_booklength') * 60;

 	foreach($dbresult as $aResult) {
 		echo '<tr>';
 		echo '<td><input type="checkbox" name="tsbooking_appt_row[]" value="' . $aResult->id . '"/></td>';
 		echo '<td>' . date("H:i", strtotime($aResult->time)) . '-' . date("H:i", strtotime($aResult->time) + $booking_length) . ' ' . date("d/m/Y", strtotime($aResult->time)) . '</td>';
 		echo '<td>' . $aResult->first_name . ' ' . $aResult->last_name . '</td>';
 		echo '<td>' . $aResult->job . '</td>';
 		echo '<td>' . $aResult->company . '</td>';
 		echo '<td>' . $aResult->email . '</td>';
 		echo '<td>' . $aResult->telephone . '</td>';
 		echo '<td>' . $aResult->source . '</td>';
 		echo '<td>' . ($aResult->mailinglist== 0 ? 'No' :  'Yes') . '</td>';
 		echo '</tr>';
	}
 	?>
 	</table>
 	
	<div class="submit">
		<input type="submit" name="appts_delete" onclick="return confirmAction('Are you sure you want to delete the selected appointments?');" value="<?php _e('Delete Appointments'); ?> &raquo;" />
		<input type="submit" name="appts_reset" onclick="return confirmAction('Are you sure you want to reset the selected appointments?');" value="<?php _e('Reset Appointments'); ?> &raquo;" />
	</div>
	
 	</form>

	<!-- The Add an  appointments -->
 	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<h3>Add an Appointment</h3>
	
	<label class="tsbooking_itemlabel" for="tsbooking_apptdate">Date</label>
	<input class="tsbooking_itemtextentry" type="text" id="tsbooking_apptdate" name="tsbooking_apptdate"/> <i>(i.e. 4 March 2012)</i><br />
	<label class="tsbooking_itemlabel" for="tsbooking_appttime">Time</label>
	<input class="tsbooking_itemtextentry" type="text" id="tsbooking_appttime" name="tsbooking_appttime"/>  <i>(i.e. 14:30)</i><br />
	 
	<div class="submit">
		<input type="submit" name="appts_add" value="<?php _e('Add Appointment'); ?> &raquo;" />
	</div>
	
 	</form>


	<h3>Settings</h3>
	<div id="tsbooking_settings_message" class="updated fade"></div>

	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<input type="hidden" name="info_update" id="info_update" value="true" />

	<label class="tsbooking_itemlabel" for="tsbooking_settings_length">Booking Length</label>
	<input class="tsbooking_itemnumericentry" type="text" id="tsbooking_settings_length" name="tsbooking_settings_length"  onkeypress="validateNumberEntry(event)" value="<?php echo get_option('tsbooking_booklength') ?>"/> minutes <br />
	<label class="tsbooking_itemlabel" for="tsbooking_settings_from">From Email Adresss</label>
	<input class="tsbooking_itemlongtextentry" type="text" id="tsbooking_settings_from" name="tsbooking_settings_from"  value="<?php echo get_option('tsbooking_email_from') ?>"/> <br />
	<label class="tsbooking_itemlabel" for="tsbooking_settings_bcc">BCC Email Adresss</label>
	<input class="tsbooking_itemlongtextentry" type="text" id="tsbooking_settings_bcc" name="tsbooking_settings_bcc"  value="<?php echo get_option('tsbooking_email_bcc') ?>"/> <br />
	<label class="tsbooking_itemlabel" for="tsbooking_settings_subject">Email Subject Prefix</label>
	<input class="tsbooking_itemlongtextentry" type="text" id="tsbooking_settings_subject" name="tsbooking_settings_subject"  value="<?php echo get_option('tsbooking_email_subject') ?>"/><i> - 10:00-<?php echo date("H:i", strtotime("10:00") + $booking_length)?>  01/01/2012</i><br />
	<label class="tsbooking_itemlabel" for="tsbooking_settings_emailmessage">Email Message</label><br>
	&quot;An appointment has been made at <i>10:00  on 01/01/2012</i> 
	<input class="tsbooking_itemlongtextentry" type="text" id="tsbooking_settings_emailmessage" name="tsbooking_settings_emailmessage"  value="<?php echo get_option('tsbooking_email_message') ?>"/>&quot; <br />
	 
	<div class="submit">
		<input type="submit" name="set_defaults" value="<?php _e('Load Default Options'); ?> &raquo;" />
		<input type="submit" name="info_update" value="<?php _e('Update options'); ?> &raquo;" onclick="return TSBookingValidateSettings();" />
	</div>

	</form>
	</div><?php
}

function tsbooking_add_option_pages() {
	if (function_exists('add_options_page')) {
		add_plugins_page("3 Sheep Appointment Booking", 'Appointment Booking', 'manage_options', 'tsbooking_options_page', 'tsbooking_options_page');
	}		
}
add_action('admin_menu', 'tsbooking_add_option_pages');

// Include the external resources
wp_enqueue_script('tsbooking', WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) . 'js/3SheepBooking.js');
wp_enqueue_style('tsbooking', WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) . 'css/3SheepBooking.css');
?>
