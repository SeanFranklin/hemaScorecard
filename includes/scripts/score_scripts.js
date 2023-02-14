/************************************************************************************/

const NO_AFTERBLOW = 1;
const DEDUCTIVE_AFTERBLOW = 2;
const FULL_AFTERBLOW = 3;
const DOUBLE_TYPE = $('#doubleType').val();

const ATTACK_CONTROL_DB = 9;

/************************************************************************************/

function isValidExchange(){

	if(GRID_ENTRY_MODE == true){
		// This function is designed to check for conflicts between the dropdowns 
		// of each fighter and the exchange types that apply to both. 
		// In grid entry mode you can not have conflicting information, therefore this is not needed.
		return;
	}


	var exchButton = document.getElementById('New_Exchange_Button');
	
	var fighter1Score = document.getElementById('fighter1_score_dropdown');
	var fighter2Score = document.getElementById('fighter2_score_dropdown');
	var exchangeID = parseInt(document.getElementById('exchangeID').value);
	
	var radioVal = document.querySelector('input[name="mod"]:checked').value;
	var radioValOverlap = false;
	var isValid = true;
	
	if(radioVal == "hit"){
		radioValOverlap = true;
	}
	
	var invalidText = "Invalid Exchange";

// Two scores are selected	
	if(DOUBLE_TYPE != FULL_AFTERBLOW)
	{
		if(fighter1Score.value != "" && fighter2Score.value != ""){
			isValid = false;
		}
	}

	if(isNaN(exchangeID) == false && radioVal == 'clearLast'){
		isValid = false;
	}

// An afterblow is selected with no score
	if(DOUBLE_TYPE == DEDUCTIVE_AFTERBLOW){
		var fighter1Afterblow = document.getElementById('fighter1_afterblow_dropdown');
		var fighter2Afterblow = document.getElementById('fighter2_afterblow_dropdown');

		if( (fighter1Score.value == "" && fighter1Afterblow.value != "")
			|| (fighter2Score.value == "" && fighter2Afterblow.value != "")){
			isValid = false;
		}
	}


// A radio button and a score is checked
	if(    (fighter1Score.value != "" || fighter2Score.value != "") 
		&& (radioValOverlap == false))
		{
		isValid = false;
	}

// Control Points
	fighter1Control = document.getElementById('fighter1_control_check');
	fighter2Control = document.getElementById('fighter2_control_check');
	if(fighter1Control != null && fighter2Control != null){
		if($(fighter1Control).is(':checked') || $(fighter2Control).is(':checked')){
			if(exchButton.value != 'scoringHit'){
				isValid = false;
			}
		}
	}
	
	if(isValid){
		exchButton.disabled = null;
	} else {
		exchButton.innerHTML = invalidText;
		exchButton.disabled = "Disabled";
	}
	
}

/************************************************************************************/

function scoreDropdownChange(selectID){

	var exchButton = document.getElementById('New_Exchange_Button');
	var fighter1Score = document.getElementById('fighter1_score_dropdown');
	var fighter2Score = document.getElementById('fighter2_score_dropdown');
	var radioVal = document.querySelector('input[name="mod"]:checked').value;
	
	document.getElementById('NA_Radio').checked = 'checked';

	if(DOUBLE_TYPE == DEDUCTIVE_AFTERBLOW){
		var fighter1Afterblow = document.getElementById('fighter1_afterblow_dropdown');
		var fighter2Afterblow = document.getElementById('fighter2_afterblow_dropdown');

		// Disable Afterblow if there is no initial hit for a fighter
		if(fighter1Score.value == "" || fighter1Score.value == "noQuality"){
			fighter1Afterblow.selectedIndex=0;
			fighter1Afterblow.disabled = "disabled";
		} else {fighter1Afterblow.disabled = null;}
		
		if(fighter2Score.value == "" || fighter2Score.value == "noQuality"){
			fighter2Afterblow.selectedIndex=0;
			fighter2Afterblow.disabled = "disabled";
		} else {
			fighter2Afterblow.disabled = null;
		}
	}

// Toggle Control Point Button
	fighter1Control = document.getElementById('fighter1_control_check');
	fighter2Control = document.getElementById('fighter2_control_check');
	if(fighter1Control != null && fighter2Control != null){
		if(fighter1Score.value != ""){
			$(fighter2Control).prop('checked', false);
			$(fighter2Control).prop('disabled', true);
			$(fighter1Control).prop('disabled', false);
		} 
		if(fighter2Score.value != ""){
			$(fighter1Control).prop('checked', false);
			$(fighter1Control).prop('disabled', true);
			$(fighter2Control).prop('disabled', false);
		}
		if(fighter1Score.value != "" && fighter2Score.value != ""){
			$(fighter1Control).prop('disabled', true);
			$(fighter2Control).prop('disabled', true);
		}
		if(fighter1Score.value == "" && fighter2Score.value == ""){
			$(fighter1Control).prop('checked', false);
			$(fighter2Control).prop('checked', false);
			$(fighter1Control).prop('disabled', false);
			$(fighter2Control).prop('disabled', false);
		}

	}

	
// Select no exchange if no scores are selected
	if(fighter1Score.value === "" && fighter2Score.value == ""){
		document.getElementById('No_Exchange_Radio').checked = 'checked';
		exchButton.value = "noExchange";
		exchButton.innerHTML = "Add: No Exchange";
		setExchButtonClasses("");
	} else if(DOUBLE_TYPE == FULL_AFTERBLOW) {

		if(		(fighter1Score.value == "noQuality" && fighter2Score.value == "")
			||  (fighter1Score.value == "" && fighter2Score.value == "noQuality"))
		{
			exchButton.value = "noQuality";
			exchButton.innerHTML = "Add: No Quality";
			setExchButtonClasses("hollow");	
		} else {
			exchButton.value = "scoringHit";
			if(fighter1Score.value !== "" && fighter2Score.value !== ""){
				setExchButtonClasses("alert");	
				exchButton.innerHTML = "Add: Bilateral Hit";
			} else {
				setExchButtonClasses("success");	
				exchButton.innerHTML = "Add: Clean Hit";
			}
		}
		
	} else {	
		if(fighter1Score.value == "noQuality" || fighter2Score.value == "noQuality"){
			exchButton.value = "noQuality";
			exchButton.innerHTML = "Add: No Quality";

			setExchButtonClasses("hollow");
			
		} else {
			exchButton.value = "scoringHit";

			setExchButtonClasses("success");
			if( DOUBLE_TYPE == DEDUCTIVE_AFTERBLOW){
				if(fighter1Afterblow.value != "" || fighter2Afterblow.value != ""){
					exchButton.innerHTML = "Add: Afterblow";
				} else {
					exchButton.innerHTML = "Add: Clean Hit";
				}

			}else {

				exchButton.innerHTML = "Add: Clean Hit";
				
			}
		}
	}

	isValidExchange();
	
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
			exchangeSummary = exchangeSummary + ", with Control (+" + controlPointValue + ")";
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
		xhr.open("POST", AJAX_LOCATION+"?"+query, true);
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
		$("#restartTimerInput").val(0);

	} else {

		timeDiv.classList.add('running');
		$('#manualTimerToggle').hide();
		$('#manualSetDiv').hide();
		timerClock = setInterval(increaseTime,1000);
		$("#restartTimerInput").val(1);

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
