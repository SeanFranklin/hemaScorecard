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

	if(selectElement.value == 'logInStaff' || selectElement.value == 'logInOrganizer'){
		$('#logInEventListDiv').show();
		$('#logInUserNameDiv').hide();
		logInEventToggle();
	} else {
		$('#logInEventListDiv').hide();
		$('#logInUserNameDiv').show();
		$('#logInUserName').val('');
	}

	logInSubmitEnable();
}

/******************************************************************************/

function logInEventToggle(){
// Appends the event name to the user name field
// Helps facilitate password managers
// Used in adminLogIn.php

	eventName = $("#logInEventID option:selected").text().trim();

	eventName = eventName;
	$('#logInUserName').val(eventName);

	logInSubmitEnable();
	
}

/******************************************************************************/

function logInSubmitEnable(){
// Enables the login button only if the user has entered data in.
// Used in adminLogIn.php

	var userName = $('#logInUserName').val();

	if($('#logInUserName').val().length > 0){
		$("#logInSubmitButton").prop("disabled",false);
	} else {
		$("#logInSubmitButton").prop("disabled",true);
	}
}

$('#logInUserName').bind('input', function() {
    logInSubmitEnable();
});

/******************************************************************************/

function schoolInputPlaceholders(){
// Creates placeholder text for a form field, based on what is entered in
// another form field.
// Used in participantsSchools.php

	document.getElementById('schoolShort').placeholder = schoolFull.value;

}