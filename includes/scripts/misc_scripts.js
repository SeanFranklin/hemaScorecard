/*******************************************************************************

		MISC SCRIPTS

		Miscelaneous scripts which belong to a single page, but
		are not long enough to warrant the page having it's own script
		file.

*******************************************************************************/


/******************************************************************************/

function strikeOutDuplicateFighters(indexToClear, buttonToActivate){

	$('.combineFighersRow').addClass('strike-through');
	$('#'+indexToClear).removeClass('strike-through');
	$('#'+buttonToActivate).removeAttr("disabled");
}


/******************************************************************************/

function toggleRadio(radioID){
// Used in cutQual Tournament

	document.getElementById(radioID).checked = true;
}

/******************************************************************************/

function logInTypeToggle(selectElement){
// Toggles the event field on or off depending on the log in type.
// Certain types of log ins are tied to event, and others are system wide.
// Used in adminLogIn.php

	if(selectElement.value == 3 || selectElement.value == 4){
		document.getElementById('logInEventList').style.display='block';
	} else {
		document.getElementById('logInEventList').style.display='none';
	}


	logInEventToggle();

}

/******************************************************************************/

function logInEventToggle(){
// Appends the event name to the user name field
// Helps facilitate password managers
// Used in adminLogIn.php


	eventName = $("#logInEventID option:selected").text();
	console.log(eventName);
	eventName = eventName.trim();
	
	switch($('#logInType').val()){
		case '-1':
			$('#LogInUserName').val('Video Manager');
			break;
		case '1':
			$('#LogInUserName').val('Guest');
			break;
		case '2':
			$('#LogInUserName').va('Analytics User');
			break;
		case '3':
			$('#LogInUserName').val('Event Staff: '+eventName);
			break;
		case '4':
			$("#LogInUserName").val("Event Organizer: "+eventName);
			break;
		case '5':
			$('#LogInUserName').val('#System Administrator');
			break;;
		default:
			$('#LogInUserName').val('Event Staff');
			break;
	}
	
}

/******************************************************************************/

function schoolInputPlaceholders(){
// Creates placeholder text for a form field, based on what is entered in
// another form field.
// Used in participantsSchools.php

	var abreviation = document.getElementById('schoolFull').value;
	abreviation = abreviation.replace( /[^A-Z]/g, '' );
	document.getElementById('schoolAbreviation').placeholder = abreviation;
	
	document.getElementById('schoolShort').placeholder = schoolFull.value;
	
}