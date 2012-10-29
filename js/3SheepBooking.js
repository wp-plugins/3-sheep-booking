/*
	3 Sheep Appointment Booking

	2012-03-14 - V.0.1 - Initial Version
*/


// Utility Functions
function isAlpha(xStr) {  
    var regEx = /^[A-Z]+/;  
    return xStr.match(regEx);  
} 

function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/;
    return re.test(email);
} 

function validateNumberEntry(evt) {
  var theEvent = evt || window.event;
  var key = theEvent.keyCode || theEvent.which;
  key = String.fromCharCode( key );
  var regex = /[0-9]|\./;
  
  if( !regex.test(key) ) {
    theEvent.returnValue = false;
    if(theEvent.preventDefault) theEvent.preventDefault();
  }
}

function confirmAction(message) {
	return confirm(message);
}

// Handle selection change events

function updateOther() {
	source = document.getElementById('tsbooking_source');
	source_other = document.getElementById('tsbooking_othersource');

	if (source.value == 'Other') {
		source_other.disabled = false;
		source_other.style.display = 'inline-block';
	} else {
		source_other.disabled = true;
		source_other.style.display = 'none';
	}
}


// Process an appointment booking request

function TSBookingBookAppointment(plugin_directory) {
	controlDiv = document.getElementById('tsbooking_book');
	messageDiv = document.getElementById('tsbooking_message');
	
	appointment = document.getElementById('tsbooking_selecttime');
	firstname = document.getElementById('tsbooking_firstname');
	lastname = document.getElementById('tsbooking_lastnamename');
	job = document.getElementById('tsbooking_job');
	company = document.getElementById('tsbooking_company');
	email = document.getElementById('tsbooking_email');
	telephone = document.getElementById('tsbooking_telephone');
	source = document.getElementById('tsbooking_source');
	source_other = document.getElementById('tsbooking_othersource');
	mailing = document.getElementById('tsbooking_mailinglist');

	// Verify the data entered
	dataOK = true;
	messageDiv.innerHTML = '';
	
	if (firstname.value.length == 0) {
		firstname.style.background = 'red';
		dataOK = false;
	} else {
		firstname.style.background = 'white';
	}
	
	if (lastname.value.length == 0) {
		lastname.style.background = 'red';
		dataOK = false;
	} else {
		lastname.style.background = 'white';
	}
	
	if (job.value.length == 0) {
		job.style.background = 'red';
		dataOK = false;
	} else {
		job.style.background = 'white';
	}
	
	if (company.value.length == 0) {
		company.style.background = 'red';
		dataOK = false;
	} else {
		company.style.background = 'white';
	}
	
	if (source.value.length == 0 || (source.value == 'Other' && source_other.value.length == 0)) {
		if (source.value == 'Other') {
			source_other.style.background = 'red';
			source.style.background = 'white';
		} else {
			source_other.style.background = 'white';
			source.style.background = 'red';
		}
		dataOK = false;
	} else {
		source_other.style.background = 'white';
		source.style.background = 'white';
	}
	
	if (email.value.length == 0 || !validateEmail(email.value)) {
		email.style.background = 'red';
		dataOK = false;
	} else {
		email.style.background = 'white';
	}

	// Try to make the appointment
	if (messageDiv && dataOK) {
		controlDiv.style.display = 'none';
		messageDiv.innerHTML = 'Booking appointment...';

		// Do the check
		setTimeout(function() {
				var req = false;

				
				if (window.XMLHttpRequest) {
						// For Safari, Firefox, and other non-MS browsers
						try {
								req = new XMLHttpRequest();
						} catch (e) {
								req = false;
						}
				} else if (window.ActiveXObject) {
						// For Internet Explorer on Windows
						try {
								req = new ActiveXObject("Msxml2.XMLHTTP");
						} catch (e) {
								try {
										req = new ActiveXObject("Microsoft.XMLHTTP");
								} catch (e) {
										req = false;
								}
						}
				}
		
				if (req) {
						var sourceString = source.value;
						
						if (sourceString == 'Other') {
							sourceString = source_other.value;
						}
						
						// Synchronous request, wait till we have it all
						var request = plugin_directory + 'core/placeBooking.php?appt=' + encodeURIComponent(appointment.value) +
															'&fname=' + encodeURIComponent(firstname.value) +
															'&lname=' + encodeURIComponent(lastname.value) +
															'&job=' + encodeURIComponent(job.value) +
															'&company=' + encodeURIComponent(company.value) +
															'&email=' + encodeURIComponent(email.value) +
															'&tel=' + encodeURIComponent(telephone.value) +
															'&source=' + encodeURIComponent(sourceString) +
															'&mail=' + encodeURIComponent(mailing.value);
						
						req.open('GET', request, false);
						req.send(null);       
						if (req.responseText == "BOOKED") {
							messageDiv.innerHTML = '<hr style="margin-bottom: 1em;"><h3>Your appointment has been made and a confirmation email sent.</h3>';
						} else {
							messageDiv.innerHTML = 'Unfortunately the requested appointment could not be made.<br /><a href="' + document.URL + '">Try Again</a>';
						}
				}	
		}, 10);
	} else {
		messageDiv.innerHTML += '<p>Please make sure all required fields are completed correctly and try again</p>';
	}
}

function TSBookingValidateSettings() {
	controlDiv = document.getElementById('tsbooking_book');
	messageDiv = document.getElementById('tsbooking_settings_message');
	
	booking_length = document.getElementById('tsbooking_settings_length');
	from = document.getElementById('tsbooking_settings_from');
	bcc = document.getElementById('tsbooking_settings_bcc');
	subject = document.getElementById('tsbooking_settings_subject');
	message = document.getElementById('tsbooking_settings_emailmessage');

	// Verify the data entered
	dataOK = true;
	messageDiv.innerHTML = '';
	
	if (booking_length.value.length == 0 || isNaN(booking_length.value)) {
		booking_length.style.background = 'red';
		dataOK = false;
	} else {
		booking_length.style.background = 'white';
	}
	
	if (from.value.length == 0) {
		from.style.background = 'red';
		dataOK = false;
	} else {
		from.style.background = 'white';
	}
	
	if (bcc.value.length == 0 || !validateEmail(bcc.value)) {
		bcc.style.background = 'red';
		dataOK = false;
	} else {
		bcc.style.background = 'white';
	}
		
	if (dataOK) {
		messageDiv.style.display = 'none';
	} else {
		messageDiv.innerHTML = 'Please check the settings and try again.';
		messageDiv.style.display = 'block';
	}
	
	return dataOK;
}