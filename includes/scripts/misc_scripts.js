/*******************************************************************************

		MISC SCRIPTS

		Miscelaneous scripts which belong to a single page, but
		are not long enough to warrant the page having it's own script
		file.

*******************************************************************************/



/******************************************************************************/

function toggleRadio(radioID){
// Used in cutQual Tournament

	document.getElementById(radioID).checked = true;
}

/******************************************************************************/

function logInEventToggle(selectElement){
// Toggles the event field on or off depending on the log in type.
// Certain types of log ins are tied to event, and others are system wide.
// Used in adminLogIn.php

	if(selectElement.value == 3 || selectElement.value == 4){
		document.getElementById('logInEventList').style.display='block';
	} else {
		document.getElementById('logInEventList').style.display='none';
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