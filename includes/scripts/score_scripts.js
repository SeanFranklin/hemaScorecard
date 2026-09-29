
/************************************************************************************/

const NO_AFTERBLOW = 1;
const DEDUCTIVE_AFTERBLOW = 2;
const FULL_AFTERBLOW = 3;
const DOUBLE_TYPE = $('#doubleType').val();

const ATTACK_CONTROL_DB = 9;

const ATTACK_DISPLAY_MODE_NORMAL = 0;
const ATTACK_DISPLAY_MODE_GRID   = 1
const ATTACK_DISPLAY_MODE_CHECK  = 2;

const WHITE_CARD  = 0;
const YELLOW_CARD = 34;
const RED_CARD    = 35;
const BLACK_CARD  = 38;


/************************************************************************************/

function isValidExchange(){
// Determine if what the table entered is valid, and if they are allowed to submit
// the exchange. This function enables/disables the Submit button.

	if(DATA_ENTRY_MODE == ATTACK_DISPLAY_MODE_GRID){
		// This function is designed to check for conflicts between the dropdowns
		// of each fighter and the exchange types that apply to both.
		// In grid entry mode you can not have conflicting information, therefore this is not needed.
		return;
	}
	if(DOUBLE_TYPE == FULL_AFTERBLOW){
		// In a full afterblow mode there is no need to check the validity because
		// you are free to enter an exchange in each.
		return;
	}

	var exchButton = document.getElementById('New_Exchange_Button');
	var fighter1Score = getFighterScoreEntry(1, DATA_ENTRY_MODE);
	var fighter2Score = getFighterScoreEntry(2, DATA_ENTRY_MODE);
	var exchangeID = parseInt(document.getElementById('exchangeID').value);
	var radioVal = document.querySelector('input[name="mod"]:checked').value;


// __ Check for input conflicts ____________________________________
	var isValid = true;


// Two scores are selected
	if(DOUBLE_TYPE != FULL_AFTERBLOW)
	{
		if(fighter1Score != "" && fighter2Score != ""){
			isValid = false;
		}
	}

	if(isNaN(exchangeID) == false && radioVal == 'clearLast'){
		isValid = false;
	}


// An afterblow is selected with no score
	if(DOUBLE_TYPE == DEDUCTIVE_AFTERBLOW){

		var Ab1Value = +getFighterAfterblowEntry(1, DATA_ENTRY_MODE);
		var Ab2Value = +getFighterAfterblowEntry(2, DATA_ENTRY_MODE);

		if(    (fighter1Score == "" && Ab1Value != 0)
			|| (fighter2Score == "" && Ab2Value != 0)){
			isValid = false;
		}
	}


// A radio button and a score are checked
	if(    (fighter1Score != "" || fighter2Score != "")
		&& (radioVal != "hit"))
		{
		isValid = false;
	}


// Bonus Points
	var fighter1Bonus = document.getElementById('fighter1_control_check');
	var fighter2Bonus = document.getElementById('fighter2_control_check');
	if(fighter1Bonus != null && fighter2Bonus != null){
		if($(fighter1Bonus).is(':checked') || $(fighter2Bonus).is(':checked')){
			if(exchButton.value != 'scoringHit'){
				isValid = false;
			}
		}
	}


// __ Set submit button state _____________________________
	if(isValid){
		exchButton.disabled = null;
	} else {
		exchButton.innerHTML = "Invalid Exchange";
		exchButton.disabled = "Disabled";
	}

}

/************************************************************************************/

function getFighterScoreEntry(num, dataEntryMode = 0){
// Figure out what score the fighter number entered.
// Different checks are needed depending on the afterblow type and the score input mode.

	if(num !== 1 & num !== 2){
		return null;
	}

	var fighterScore = null;
	if(dataEntryMode != ATTACK_DISPLAY_MODE_CHECK){

		var fighterScoreInput = document.getElementById('fighter'+num+'_score_dropdown');

		// Different scoring modes, dropdown (select) vs grid (input)
		if(fighterScoreInput.tagName == 'SELECT'){
			// Using the dropdown to enter scores.
			fighterScore = fighterScoreInput.value;
		} else if(fighterScoreInput.tagName == 'INPUT'){
			// Using grid mode to enter scores.
			if(fighterScoreInput.checked == true){
				fighterScore = fighterScoreInput.value;
			}
		} else {
			// wtf?
		}

	} else {

		var radioButtonName = document.getElementById('radio-button-name-'+num);
		var radioButtons = document.getElementsByName(radioButtonName.value);

		for( i = 0; i < radioButtons.length; i++ ) {
	        if( radioButtons[i].checked ) {
	            fighterScore =  radioButtons[i].value;

	        }
	    }
	}

	return (fighterScore);
}

/************************************************************************************/

function getFighterAfterblowEntry(num, dataEntryMode = 0){
// Figure out what score the fighter number entered.
// Different checks are needed depending on the afterblow type and the score input mode.

	if(num !== 1 & num !== 2){
		return null;
	}

	var fighterAfterblow = 0;

	if(dataEntryMode != ATTACK_DISPLAY_MODE_CHECK){

		var fighterAfterblowInput = document.getElementById('fighter'+num+'_afterblow_input');

		// Different scoring modes, dropdown (select) vs grid or pre-defined toggles (input)
		if(fighterAfterblowInput.tagName == 'SELECT'){
			// Using a dropdown to enter the afterblow value
			fighterAfterblow = fighterAfterblowInput.value;
		} else if(fighterAfterblowInput.tagName == 'INPUT'){
			// Using a button to enter the afterblow value
			if(fighterAfterblowInput.checked == true){
				fighterAfterblow = fighterAfterblowInput.value;
			}
		} else {
			// There should not be a mode that gets you here. Keep the default zero.
		}

	} else {

		// Check-Box input mode
		var fighterAfterblowInput = document.getElementById('afterblow-box-'+num+'-value');

		// Check input exists because if they didn't specify the afterblow value
		// the input won't be created on the score entry page.
		if(fighterAfterblowInput != null && fighterAfterblowInput.checked == true){
			fighterAfterblow = fighterAfterblowInput.value;
		}
	}


	return (fighterAfterblow);
}

/************************************************************************************/

function enableAfterblowInput(num, fighterScore, dataEntryMode = 0){
// Enable or disable the afterblow field. This is only relevant in Deductive Afterblow
// mode, where we want to make sure the user can't submit and afterblow without also
// a scoring hit for the fighter.

	if(num !== 1 & num !== 2){
		// We only support two fighters in a match at once!
		return;
	}


	var afterblowEnabled = false;
	if(fighterScore != "" && fighterScore != "noQuality"){
		afterblowEnabled = true;
	}

	// Check has to be handled separately because it is custom created buttons
	// for the afterblow, rather than using an html element for the form.
	if(dataEntryMode != ATTACK_DISPLAY_MODE_CHECK){

		var fighterAfterblowInput = document.getElementById('fighter'+num+'_afterblow_input');

		if(afterblowEnabled == true){
			fighterAfterblowInput.disabled = null;
		} else {
			if(fighterAfterblowInput.tagName != 'INPUT'){
				// Afterblow is a dropdown
				fighterAfterblowInput.selectedIndex = 0;
			} else {
				// Afterblow is a check button
				fighterAfterblowInput.checked = false;
			}

			fighterAfterblowInput.disabled = "disabled";
		}

	} else {

		// Check-Box input mode
		var fighterAfterblowInput = document.getElementById('afterblow-box-'+num+'-value');
		var fighterAfterblowDiv = document.getElementById('afterblow-box-'+num+'-div');

		if(fighterAfterblowInput == null || fighterAfterblowInput == null){
			// If they haven't pre-specifed the afterblow value then it won't exist as
			// a button. This is an empty branch so we don't try to compare to a div
			// that doesn't exist.
		} else if(afterblowEnabled == true){
			fighterAfterblowDiv.classList.remove('afterblow-box-disabled');
			fighterAfterblowInput.disabled = null;
		} else {
			fighterAfterblowInput.checked = false;
			fighterAfterblowInput.disabled = "disabled";
			fighterAfterblowDiv.classList.add('afterblow-box-disabled');
			fighterAfterblowDiv.classList.remove('afterblow-box-on');
		}

	}
}

/************************************************************************************/

function toggleBonusPointButtons(fighter1Score, fighter2Score){
// Bonus was originally called "control" and is still refered to that in many places
// of the code that have not been refactored.

	fighter1Bonus = document.getElementById('fighter1_control_check');
	fighter2Bonus = document.getElementById('fighter2_control_check');

	if(fighter1Bonus != null && fighter2Bonus != null){
		if(fighter1Score != ""){
			$(fighter2Bonus).prop('checked', false);
			$(fighter2Bonus).prop('disabled', true);
			$(fighter1Bonus).prop('disabled', false);
		}
		if(fighter2Score != ""){
			$(fighter1Bonus).prop('checked', false);
			$(fighter1Bonus).prop('disabled', true);
			$(fighter2Bonus).prop('disabled', false);
		}
		if(fighter1Score != "" && fighter2Score != ""){
			$(fighter1Bonus).prop('disabled', true);
			$(fighter2Bonus).prop('disabled', true);
		}
		if(fighter1Score == "" && fighter2Score == ""){
			$(fighter1Bonus).prop('checked', false);
			$(fighter2Bonus).prop('checked', false);
			$(fighter1Bonus).prop('disabled', false);
			$(fighter2Bonus).prop('disabled', false);
		}

	}
}

/************************************************************************************/

function scoreDropdownChange(selectID){
// Main update function for when the user changes a score value. Ensures that
// illegal operations are inhibited and anything that should be un-checked because
// of the input is cleared.

	var exchButton = document.getElementById('New_Exchange_Button');
	var radioVal = document.querySelector('input[name="mod"]:checked').value;
	document.getElementById('NA_Radio').checked = 'checked';

	var fighter1Score = getFighterScoreEntry(1, DATA_ENTRY_MODE);
	var fighter2Score = getFighterScoreEntry(2, DATA_ENTRY_MODE);
	var Ab1Value = 0;
	var Ab2Value = 0;


// __ Afterblow ___________________________
	// Taking in the afterblow is only necessary in Deductive mode, as the "afterblow"
	// is created in Full_Afterblow mode by making the lower fighterScore the afterblow.
	if(DOUBLE_TYPE == DEDUCTIVE_AFTERBLOW){
		Ab1Value = getFighterAfterblowEntry(1, DATA_ENTRY_MODE);
		Ab2Value = getFighterAfterblowEntry(2, DATA_ENTRY_MODE);

		enableAfterblowInput(1, fighter1Score, DATA_ENTRY_MODE);
		enableAfterblowInput(2, fighter2Score, DATA_ENTRY_MODE);
	}


// __ Bonus Points ___________________________
	// aka 'control points'
	toggleBonusPointButtons(fighter1Score, fighter2Score);


// __ Update Submit Buttion __________________________________
	if(fighter1Score === "" && fighter2Score == ""){
		// Select no exchange if no scores are selected
		document.getElementById('No_Exchange_Radio').checked = 'checked';
		exchButton.value = "noExchange";
		exchButton.innerHTML = "Add: No Exchange";
		setExchButtonClasses("");
	} else if(DOUBLE_TYPE == FULL_AFTERBLOW) {

		// Full Afterblow differs in that you can have a bilateral if
		// there is a score entered for both fighters.
		if(		(fighter1Score == "noQuality" && fighter2Score == "")
			||  (fighter1Score == "" && fighter2Score == "noQuality"))
		{
			exchButton.value = "noQuality";
			exchButton.innerHTML = "Add: No Quality";
			setExchButtonClasses("hollow");
		} else {
			exchButton.value = "scoringHit";
			if(fighter1Score !== "" && fighter2Score !== ""){
				setExchButtonClasses("alert");
				exchButton.innerHTML = "Add: Bilateral Hit";
			} else {
				setExchButtonClasses("success");
				exchButton.innerHTML = "Add: Clean Hit";
			}
		}

	} else {

		// Deductive and No Afterblow work the same, as you can only
		// ever enter a score from a single fighter.
		if(fighter1Score == "noQuality" || fighter2Score == "noQuality"){
			exchButton.value = "noQuality";
			exchButton.innerHTML = "Add: No Quality";

			setExchButtonClasses("hollow");

		} else {
			exchButton.value = "scoringHit";

			setExchButtonClasses("success");
			if( DOUBLE_TYPE == DEDUCTIVE_AFTERBLOW){
				if(Ab1Value != 0 || Ab2Value != 0){
					exchButton.innerHTML = "Add: Afterblow";
				} else {
					exchButton.innerHTML = "Add: Clean Hit";
				}
			}else {
				exchButton.innerHTML = "Add: Clean Hit";
			}
		}
	}


// __ Check Exchange __________________________
	// Enable/disable the submit button depending on what the user entered.
	isValidExchange();

}

/************************************************************************************/

function scoreCheckboxChange(divID, radioButtonID, fighterNum){
// Function for when a custom checkbox is clicked when in Check-Box data entry mode.
// This changes the color and updates the hidden radio button which has the actual
// data form.

	var radioButton = document.getElementById(radioButtonID);
	radioButton.checked = 'checked';

	attackBoxDivs = document.getElementsByClassName("attack-box-"+fighterNum);

	for( i = 0; i < attackBoxDivs.length; i++ ) {
        attackBoxDivs[i].classList.remove('attack-box-on');
    }

    divID.classList.add('attack-box-on');

	scoreDropdownChange(this)

}

/************************************************************************************/

function scoreCheckboxChangeAfterblow(num){
// Function for when a custom afterblow checkbox is clicked when in Check-Box data .
// entry mode. This changes the color and updates the hidden radio button which
// has the actual data form. Additional logic because the afterblow button is disabled
// if you don't have a valid intial score active.

	var fighterAfterblowInput = document.getElementById('afterblow-box-'+num+'-value');
	var fighterAfterblowDiv = document.getElementById('afterblow-box-'+num+'-div');

	if(fighterAfterblowInput.disabled == false){
		fighterAfterblowInput.checked = !fighterAfterblowInput.checked;
		$(fighterAfterblowDiv).toggleClass('afterblow-box-on');
	} else {
		fighterAfterblowInput.checked = false;
		$(fighterAfterblowDiv).removeClass('afterblow-box-on');
		$(fighterAfterblowDiv).addClass('afterblow-box-disabled');
	}

	scoreDropdownChange(this);

}

/************************************************************************************/

function setExchButtonClasses(classes){
	$("#New_Exchange_Button").removeClass();
	$("#New_Exchange_Button").addClass("button large expanded");
	$("#New_Exchange_Button").addClass(classes);
}

/************************************************************************************/

function modifiersRadioButtons(){
	var exchButton = document.getElementById('New_Exchange_Button');

	var radioVal = document.querySelector('input[name="mod"]:checked').value;

	$('#fighter1_score_dropdown').prop('selectedIndex',0);
	$('#fighter2_score_dropdown').prop('selectedIndex',0);
	fighter1Control = document.getElementById('fighter1_control_check');
	fighter2Control = document.getElementById('fighter2_control_check');
	if(fighter1Control != null && fighter2Control != null){
		$(fighter1Control).prop('checked', false);
		$(fighter2Control).prop('checked', false);
		$(fighter1Control).prop('disabled', true);
		$(fighter2Control).prop('disabled', true);
	}

	if(DOUBLE_TYPE == DEDUCTIVE_AFTERBLOW){
		$('#fighter1_afterblow_dropdown').prop('selectedIndex',0);
		$('#fighter2_afterblow_dropdown').prop('selectedIndex',0);
		$('#fighter1_afterblow_input').prop('checked', false);
		$('#fighter2_afterblow_input').prop('checked', false);
	}


	switch(radioVal){
		case 'noExch':
			exchButton.value = "noExchange";
			exchButton.innerHTML = "Add: No Exchange";
			setExchButtonClasses("");
			break;
		case 'doubleHit':
			exchButton.value = "doubleHit";
			exchButton.innerHTML = "Add: Double Hit";
			setExchButtonClasses("alert");
			break;
		case 'clearLast':
			exchButton.value = "clearLastExchange";
			exchButton.innerHTML = "Remove: Last Exchange";
			setExchButtonClasses("warning");
			break;
		case 'clearAll':
			exchButton.value = "clearAllExchanges";
			exchButton.innerHTML = "Remove: All Exchanges";
			setExchButtonClasses("alert hollow");
			break;
	}

	isValidExchange();

}

/************************************************************************************/

function gridScoreUpdate(rosterID, id){

	var undefinedCombination = false;
	var scoreValue = 0;

	var target = $('input[name="score['+rosterID+'][attackTarget]"]:checked').val();
	var targetName = $('input[name="score['+rosterID+'][attackTarget]"]:checked').attr("data-attackName");

	var type = $('input[name="score['+rosterID+'][attackType]"]:checked').val();
	var typeName = $('input[name="score['+rosterID+'][attackType]"]:checked').attr("data-attackName");

	var prefix = $('input[name="score['+rosterID+'][attackPrefix]"]:checked').val();
	var afterblow = $('input[name="score['+rosterID+'][afterblow]"]:checked').val();


	property = id.name.split("[");
	property = property[2].split("]");
	property = property[0];

	var classID = "grid-"+property+"-div-"+rosterID;

	if(property == "attackTarget"){
		checkedValue = target;
		gridScoreToggleCategory(classID, 0);
	} else if(property == "attackType") {
		checkedValue = type;
		gridScoreToggleCategory(classID, 0);
	} else {

	}

	if(afterblow === undefined){
		afterblow = 0;
	}

	var isControl = false;
	if(prefix == ATTACK_CONTROL_DB){
		isControl = true;
	}

	if(gridAttackTypes[target] === undefined){
		target = 0;
	}

	if(gridAttackTypes[target] === undefined){
		undefinedCombination = true;
	} else {

		if(gridAttackTypes[target][type] === undefined){
			type = 0;
		}

		if(gridAttackTypes[target][type] === undefined){
			undefinedCombination = true;
		} else {

			if(gridAttackTypes[target][type][prefix] === undefined){
				prefix = 0;
			}

			if(gridAttackTypes[target][type][prefix] === undefined){
				undefinedCombination = true;
			} else {
				scoreValue = gridAttackTypes[target][type][prefix];
			}
		}

	}

	if(afterblow > scoreValue){
		afterblow = scoreValue;
	}


	var exchangeSummary = '';
	var finalPointValue = scoreValue;

	if(undefinedCombination == true ){
		exchangeSummary = "Undefined Exchange";
		$("input[name='score["+rosterID+"][hit]'][value='0']").prop('checked', true);

	} else {

		$("input[name='score["+rosterID+"][hit]'][value='"+scoreValue+"']").prop('checked', true);


		if(typeof typeName !== "undefined" || typeof typeName !== "undefined"){
			exchangeSummary = "[" + exchangeSummary;
			exchangeSummary = exchangeSummary + typeName + ", ";
			exchangeSummary = exchangeSummary + targetName;
			exchangeSummary = exchangeSummary + "] ";
		}

		exchangeSummary = exchangeSummary + scoreValue


		exchangeSummary = exchangeSummary + " Pt";
		if(scoreValue > 1){
			exchangeSummary = exchangeSummary + "s";
		}

		if(afterblow != 0){
			exchangeSummary = exchangeSummary + ", w/ Afterblow (-" + afterblow + ")";
			finalPointValue -= afterblow;
		}

		if(isControl == true){
			var bonusName = $("#bonus-point-name").val();
			exchangeSummary = exchangeSummary + ", with " + bonusName + " (+" + controlPointValue + ")";
			finalPointValue += controlPointValue;
		}

		exchangeSummary = exchangeSummary + " = <b>" + finalPointValue + "</b>";

	}

	$("#exchange-grid-summary-"+rosterID).html(exchangeSummary);

	gridScoreEnableSubmission(rosterID);

}

/************************************************************************************/

function gridScoreToggleCategory(className, visibility){

	if(visibility == 0){
		// Hide
		$("."+className+"-show-button").show();
		$("."+className+"-hide-button").hide();

		$("."+className).hide();
	} else {
		// Show
		$("."+className+"-hide-button").show();
		$("."+className+"-show-button").hide();

		$("."+className).show();
	}

}

/************************************************************************************/

function gridScoreManualPoints(rosterID){

	var scoreValue = $('input[name="score['+rosterID+'][hit]"]:checked').val();

	if(scoreValue == "noQuality"){
		$("#exchange-grid-summary-"+rosterID).html("No Quality");
	} else {
		$("#exchange-grid-summary-"+rosterID).html("MANUAL OVERIDE");
	}

	gridScoreEnableSubmission(rosterID);

}

/************************************************************************************/

function gridScoreEnableSubmission(rosterID){

	var scoreValue = $('input[name="score['+rosterID+'][hit]"]:checked').val();

	if(scoreValue == "noQuality"){
		$('#grid-add-new-exch-'+rosterID).attr("disabled",true);
		$('#grid-add-no-quality-'+rosterID).attr("disabled",false);
	} else if(scoreValue != 0){
		$('#grid-add-new-exch-'+rosterID).attr("disabled",false);
		$('#grid-add-no-quality-'+rosterID).attr("disabled",true);
	} else {
		$('#grid-add-new-exch-'+rosterID).attr("disabled",true);
		$('#grid-add-no-quality-'+rosterID).attr("disabled",true);
	}

}

/**********************************************************************/

function editExchange(exchangeID, exchangeTime){

	$('.exchangeID').val(exchangeID);

	if(exchangeID == ''){
		$('#editExchangeButton').show();
		$('#cancelEditExchangeButton').hide();
		$('.editExchangeWarningDiv').hide();
		$('body').css('background-color', '');
		$('.timer-input').attr("disabled",false);
		$('.conclude-match-button').attr("disabled",false);
		$('#Clear_Last_Radio').attr('disabled',false);
		$('.Clear_Last_Radio').css('text-decoration','none');

		disableTimer = false;
		$('#matchTime').attr('value',originalMatchTime);
		updateTimerDisplay();

	} else {
		$('#editExchangeButton').hide();
		$('#cancelEditExchangeButton').show();
		$('.editExchangeWarningDiv').show();
		$('body').css('background-color', '#ddd');
		$('.timer-input').attr("disabled",'disabled');
		$('.conclude-match-button').attr("disabled",'disabled');
		$('#Clear_Last_Radio').attr('disabled','disabled');
		$('.Clear_Last_Radio').css('text-decoration','line-through');

		disableTimer = true;
		originalMatchTime = document.getElementById('matchTime').value
		$('#matchTime').attr('value',exchangeTime);
		updateTimerDisplay();
		isValidExchange();
	}

}

/************************************************************************************/

function refreshOnNewExchange(matchID, exchangeID = 0){

	var refreshPeriod = 1 * 1000; // seconds
	var intervalID = window.setInterval(function(){ a(); }, refreshPeriod);

	function a(){
		var query = {};
		var query = "mode=newExchange";
		query = query + "&matchID=" + matchID.toString();
		query = query + "&exchangeID=" + exchangeID.toString();

		var xhr = new XMLHttpRequest();
		xhr.open("GET", AJAX_LOCATION+"?"+query, true);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.send();

		xhr.onreadystatechange = function (){
			if(this.readyState == 4 && this.status == 200){

				if(this.responseText.length >= 1){ // If the fighter has already fought
					recievedData = JSON.parse(this.responseText);

					if(recievedData['refresh'] == true){
						location.reload();
					} else {

						$('#matchTime').val(recievedData['matchTime'])
						updateTimerDisplay();
					}
				}
			}
		};	//*/
	}
}

var disableTimer = false;
var originalMatchTime = 0;

/******************************************************************************/

function startTimer(){

	if(disableTimer == true){
		return;
	}

	timeDiv = document.getElementById("timerButton");

	if(timeDiv.classList.contains('running')){

		timeDiv.classList.remove('running');
		$('#manualTimerToggle').show();
		clearInterval(timerClock);
		$(".restart-timer-input").val(0);

	} else {

		timeDiv.classList.add('running');
		$('#manualTimerToggle').hide();
		$('#manualSetDiv').hide();
		timerClock = setInterval(increaseTime,1000);
		$(".restart-timer-input").val(1);

	}

	setTimerButtonColor($('#matchTime').val());

}

/******************************************************************************/

function secondsToDisplay(time){
	if(time === undefined){
		time = document.getElementById('matchTime').value;
	}
	minutes = Math.floor(time/60);
	seconds = time - (minutes * 60);
	if(seconds < 10){
		seconds = "0"+seconds.toString();
	}
	str = minutes.toString()+":"+seconds.toString();

	return str;
}

/******************************************************************************/

function increaseTime(){
	time = parseInt(document.getElementById('matchTime').value);
	if(isNaN(time)){ time = 0; }
	time += 1;
	document.getElementById('matchTime').value = time;
	updateMatchTimer();
}

/******************************************************************************/

function updateMatchTimer(){
	time = document.getElementById('matchTime').value;
	timeLimit = document.getElementById('timeLimit').value;

	// Update the form fields
	timerInputs = document.getElementsByClassName('matchTime');

	for(var i=0; i<timerInputs.length; i++){
		timerInputs[i].value = time;
	}

	updateTimerDisplay(time);

	// Update the match time in the DB
	var query = "mode=updateMatchTime";
	query = query + "&matchID="+document.getElementById('matchID').value;
	query = query + "&matchTime="+time.toString();

	var xhr = new XMLHttpRequest();
	xhr.open("POST", AJAX_LOCATION+"?"+query, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send();

}

/******************************************************************************/

function updateTimerDisplay(time = null){
	// Update the button in M:SS format

	if(time == null){
		time = $('#matchTime').val()
	}

	if(document.getElementById('timerCountdown').value == 0){
		displayTime = time;
	} else {
		// In timer countdown mode
		displayTime = document.getElementById('timeLimit').value - time;
	}

	var displayNegative = false;
	if($("#hideNegativeTime").length == 0){
		displayNegative = true;
	}

	var str = secondsToMinAndSec(displayTime, displayNegative);
	document.getElementById('currentTime').innerHTML = str;

	setTimerButtonColor(time);

}

/******************************************************************************/

function setTimerButtonColor(time){

	target = document.getElementById("timerButton");
	if(target == null){
		return;
	}
	time = time;

	if( target.classList.contains('running') == true){
		isRunning = true;
	} else {
		isRunning = false;
	}

	timeLimit = document.getElementById('timeLimit').value;


	wasFilled = false;
	if(timeLimit > 0 && (timeLimit - time) <= 0){
		overTime = true;

		if( target.classList.contains('hollow') == false){
			wasFilled = true;
		}

	} else {
		overTime = false;
	}

	target.classList.remove('success');
	target.classList.remove('warning');
	target.classList.remove('alert');
	target.classList.remove('secondary');
	target.classList.add('hollow');

	if(overTime == false){
		if(isRunning == true){
			target.classList.add('alert');
		} else {
			target.classList.add('success');
		}
	} else {
		if(isRunning == true){
			target.classList.add('alert');
			if(wasFilled == false){
				target.classList.remove('hollow');
			}
		} else {
			target.classList.add('warning');
		}
	}

}


/******************************************************************************/

function manualTimeSet(){

	var minutes = parseInt($('#timerMinutes').val());
	if (Number.isInteger(minutes) == false){
		minutes = 0;
	}

	var seconds = parseInt($('#timerSeconds').val());
	if (Number.isInteger(seconds) == false) {
		seconds = 0;
	}

	time = (60 * minutes) + seconds;

	// If the timer is in countdown mode, need to do some manual calculations.
	if(document.getElementById('timerCountdown').value != 0){
		time = document.getElementById('timeLimit').value - time;
	}

	document.getElementById('matchTime').value = time;
	updateMatchTimer();
	document.getElementById('manualSetDiv').classList.add('hidden')
}

/******************************************************************************/

var miscTimerActive = false;

function miscTimerToggle(){

	if(miscTimerActive == false){
		timerClock = setInterval(miscTimerClockTick,1000);
		miscTimerActive = true;
		$('#misc-timer-container').removeClass('secondary');
		$('#misc-timer-button').removeClass('hollow');
		$('#misc-timer-button').html('Stop');
	} else {
		miscTimerStop();
	}

}

/******************************************************************************/

function miscTimerStop(){

	if(miscTimerActive == true){
		clearInterval(timerClock);
		miscTimerActive = false;
	}

	$('#misc-timer-container').addClass('secondary');
	$('#misc-timer-button').addClass('hollow');
	$('#misc-timer-button').html('Start');

}

/******************************************************************************/

function miscTimerClockTick(){

	var time = parseInt($('#misc-timer-value').val());
	time = time + 1;

	var str = secondsToMinAndSec(time);

	$('#misc-timer-value').val(time);
	$('#misc-timer-display').html(str);
}

/******************************************************************************/

function miscTimerReset(){

	miscTimerStop();

	$('#misc-timer-value').val(0);
	$('#misc-timer-display').html("0:00");
	$('#misc-timer-container').fadeTo(100, 0.1, function() { $(this).fadeTo(500, 1.0); });

}

/******************************************************************************/

function selectActiveFighter(rosterID, num, buttonClicked){

	var className = ".team-fighters-"+num;
	var elems = document.querySelectorAll(className);

	[].forEach.call(elems, function(el) {
	    el.classList.remove("alert");
	    el.classList.add("hollow");
	});

	buttonClicked.classList.add("alert");
	buttonClicked.classList.remove("hollow");

	var fieldId = "active-fighter-rosterID-"+num;
	document.getElementById(fieldId).value = rosterID;

	if(   document.getElementById('active-fighter-rosterID-1').value != 0
	   && document.getElementById('active-fighter-rosterID-2').value != 0){
		document.getElementById('switch-active-fighters-submit').disabled = false;
	} else {
		document.getElementById('switch-active-fighters-submit').disabled = true;
	}


}

/******************************************************************************/

function validateStaffSelection(baseID){

	var inputElement = document.querySelectorAll("[data-id='"+baseID+"']")[0];
	var datalist = document.getElementById('staff-select-datalist').options;

	var valid = false;
	

	if(inputElement.value.trim() == ""){
		inputElement.value = "";
		valid = true;

	} else {
		for(var j = 0; j < datalist.length; j++){
        
	        if(datalist[j].label === inputElement.value){
	            
	            valid = true;
	        }
	        
	    }
	}

    if(valid == true){

    	staffSetPending(baseID);

    } else {

    	document.getElementById(baseID+'-status').innerHTML = "!!";
    	$('.'+baseID+'-status').removeClass('background-primary-light');
    	$('.'+baseID+'-status').addClass('background-warning');
    }

}

/******************************************************************************/

function clearDatalist(baseID){

    //var a = document.getElementsByClassName('input-datalist').querySelectorAll('[data-id]');
    var a = document.querySelectorAll("[data-id='"+baseID+"']");
    a[0].value = "";
    staffSetPending(baseID);

}

/******************************************************************************/

function staffSetPending(baseID){

	$('.'+baseID+'-status').removeClass('background-warning');
	$('.'+baseID+'-status').addClass('background-primary-light');
	document.getElementById(baseID+'-status').innerHTML = "?";
	
}

/******************************************************************************/

function rollForTeamOrder(){

	var text = [];
	text[1] = $("#roll-team-1 option:selected").text();
	text[2] = $("#roll-team-2 option:selected").text();
	text[3] = $("#roll-team-3 option:selected").text();
	text[4] = $("#roll-team-4 option:selected").text();
	text[5] = $("#roll-team-5 option:selected").text();

	optionsList = [];
	for(var i = 1;i<=5;i++){
		if(text[i] !== ""){
			optionsList.push(i);
		}
	}

	outputList = [];
	while(optionsList.length > 0){
		var index = Math.floor(Math.random() * optionsList.length);

		outputList.push(optionsList[index]);
		optionsList.splice(index,1)

	}

 	for(var i = 1;i<=5;i++){
 		var index = i - 1;

 		if(typeof outputList[index] !== 'undefined'){
 			$("#roll-team-output-"+i).hide()
 			$("#roll-team-output-"+i).html(i+": "+text[outputList[index]]);
 			$("#roll-team-output-"+i).fadeIn(i*300);
 		} else {
			$("#roll-team-output-"+i).html("");
 		}
 	}

}

/******************************************************************************/

function rollForOffhand(){

	var text = [];
	text[1] = $("#roll-offhand-1 option:selected").text();
	text[2] = $("#roll-offhand-2 option:selected").text();
	text[3] = $("#roll-offhand-3 option:selected").text();
	text[4] = $("#roll-offhand-4 option:selected").text();
	text[5] = $("#roll-offhand-5 option:selected").text();
	text[6] = $("#roll-offhand-6 option:selected").text();

	optionsList = [];
	for(var i = 1;i<=5;i++){
		if(text[i] !== ""){
			optionsList.push(text[i]);
		}
	}

	if(optionsList.length != 0){

		var index = Math.floor(Math.random() * optionsList.length);

		$("#roll-offhand-output").hide();
		$("#roll-offhand-output").html(optionsList[index]);
		$("#roll-offhand-output").fadeIn();
	} else {
		$("#roll-offhand-output").html("");
	}

}

/******************************************************************************/

function calculatePenaltyEscalation(isUsed, matchID){

	if(isUsed == 0){
		return;
	}

	var outputText = "";

	var fighterNum = 0;
	if(document.getElementById('penalty-fighter-1').checked == true){
		fighterNum = 1;
	} else if(document.getElementById('penalty-fighter-2').checked == true) {
		fighterNum = 2;
	}


	var isSafety = false;
	var infractionID = document.getElementById('penalty-infraction').value;
	if(infractionID == ""){
		document.getElementById('penalty-escalation-notice').innerHTML = "";
		document.getElementById('penalty-escalation-warning').innerHTML = "";
		return;
	}

	var query = {};
	var query = "mode=penaltyEscalation";
	query = query + "&infractionID=" + infractionID.toString();
	query = query + "&fighterNum=" + fighterNum.toString();
	query = query + "&matchID=" + matchID.toString();

	var xhr = new XMLHttpRequest();
	xhr.open("GET", AJAX_LOCATION+"?"+query, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send();

	xhr.onreadystatechange = function (){

		if(this.readyState == 4 && this.status == 200){
			if(this.responseText.length >= 1){

				//console.log(this.responseText);
				recievedData = JSON.parse(this.responseText);
				//console.log(recievedData);

				var selectedColor = document.getElementById('penalty-color').value;
				if(selectedColor == ""){
					selectedColor = 0;
				}


				var colorText = "";
				switch(recievedData['colorID']){
					case (YELLOW_CARD): { colorText = "YELLOW"; 	break;	}
					case (RED_CARD):    { colorText = "RED"; 		break;	}
					case (BLACK_CARD):  { colorText = "BLACK"; 		break;	}
				    case (WHITE_CARD):  { colorText = "-none-"; 	break;	}
				}

				var isNonSafety = recievedData['isNonSafety'];
				var properColor = recievedData['colorID'];


				// Let the user know what the proper color should be.
				priorsTextText = "";

				if(isNonSafety == false){
					priorsTextText = recievedData['numPrior'] + " prior penalties in this " + recievedData['mode'] + ".<BR> ";
					priorsTextText = priorsTextText + "The correct card color is <b>" + colorText + "</b>";
				} else {
					priorsTextText = "This is a non-safety penalty.<BR>The correct card color is <b>NONE</b>.";
				}

				document.getElementById('penalty-escalation-notice').innerHTML = priorsTextText;


				// Bitch at the user if they didn't pick the proper color.
				warningText = "";
				if(selectedColor != properColor){
					warningText = "<div class='callout alert text-center'>";
					warningText = warningText + "<b>WARNING</b><BR>";
					warningText = warningText + "You have not selected the correct penalty color based on the prior penalties.";
					warningText = warningText + "</div>";
				}
				document.getElementById('penalty-escalation-warning').innerHTML = warningText;

			}
		}
	};

}

/******************************************************************************/

function updatePieceExchange(element, exchangeID){

	var attackID = element.value;

	var query = {};
	var query = "mode=updateExchange";
	query = query + "&exchangeID=" + exchangeID;
	query = query + "&field=" + element.dataset.deductiontype;
	query = query + "&value=" + attackID;

	var xhr = new XMLHttpRequest();
	xhr.open("POST", AJAX_LOCATION+"?"+query, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send();

	xhr.onreadystatechange = function (){

		if(this.readyState == 4 && this.status == 200){
			if(this.responseText.length >= 1){

				//console.log(this.responseText);
				matchData = JSON.parse(this.responseText);
				//console.log(matchData);

				if(matchData['error'] != ""){
					error = matchData['error'];
				} else {
					//console.log(matchData['exchanges']);

					document.getElementById('match-score').innerHTML = "Score: <b>"+matchData['matchScore']+"</b>";

					var error = null;

					for(var i = 0; i < matchData['exchanges'].length; i++){

						var exchangeID = matchData['exchanges'][i].exchangeID

						if(document.getElementById('tr-exchange-'+exchangeID) != null){
							updateScoredExchange(matchData['exchanges'][i]);
						} else {
							// There were extra exchanges returned which aren't in the PHP.
							// Alert the user to refresh the page.
							error = 'extraExchanges';
						}
					}
				}

				if(error == null){

					document.getElementById('reload-page-button').classList.remove("alert");
					document.getElementById('reload-page-button').classList.add("hollow");
					document.getElementById('warning-message-div').innerHTML = "";

				} else {

					document.getElementById('reload-page-button').classList.add("warning");
					document.getElementById('reload-page-button').classList.remove("hollow");

					var text = "<div class='callout warning'>";

					switch(error){
						case 'extraExchanges':
							text = text + "Another table has added extra exchanges. Please reload the page.</div>";
							break;
						case 'noData':
							text = text + "Working on an invalid exchange (maybe another table deleted it?). Please reload the page.</div>";
							break;
					}

					text = text + "</div>";

					document.getElementById('warning-message-div').innerHTML = text;

				}

			}
		}
	};
}


/******************************************************************************/

function updateScoredExchange(data){

	var exchangeID = data.exchangeID;

	var scoreDeduction = data.scoreDeduction;
	document.getElementById('scoreDeduction-'+exchangeID).innerHTML = (-scoreDeduction);

	var scoreValue = data.scoreValue;
	document.getElementById('scoreValue-'+exchangeID).value = scoreValue;

	if(document.getElementById('refPrefix-'+exchangeID) != null){
		// Adding 0 is a ghetto way to convert a possible NULL to a number
		document.getElementById('refPrefix-'+exchangeID).value = (data.refPrefix + 0);
	}

	if(document.getElementById('refTarget-'+exchangeID) != null){
		// Adding 0 is a ghetto way to convert a possible NULL to a number
		document.getElementById('refTarget-'+exchangeID).value = (data.refTarget + 0);
	}

	if(document.getElementById('refType-'+exchangeID) != null){
		// Adding 0 is a ghetto way to convert a possible NULL to a number
		document.getElementById('refType-'+exchangeID).value = (data.refType + 0);
	}

	var scoreFinal = scoreValue - scoreDeduction;
	document.getElementById('scoreFinal-'+exchangeID).innerHTML = Math.round(scoreFinal * 10) / 10 ;

	if(data.exchangeType = 'scored'){
		document.getElementById('tr-exchange-'+exchangeID).classList.remove("pending-exchange");
	}
}

/******************************************************************************/
