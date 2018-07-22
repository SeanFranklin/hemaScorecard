/************************************************************************************/

const NO_AFTERBLOW = 1;
const DEDUCTIVE_AFTERBLOW = 2;
const FULL_AFTERBLOW = 3;
const DOUBLE_TYPE = $('#doubleType').val();

function isValidExchange(){
	var exchButton = document.getElementById('New_Exchange_Button');
	
	var fighter1Score = document.getElementById('fighter1_score_dropdown');
	var fighter2Score = document.getElementById('fighter2_score_dropdown');
	
	
	var radioVal = document.querySelector('input[name="mod"]:checked').value;
	var radioValOverlap = false;
	var isValid = true;
	
	if(radioVal == "hit" || radioVal == "penalty"){
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

// Penalty mode
	if(radioVal == "penalty"){
		var fighter1Penalty = document.getElementById('fighter1_penalty_dropdown');
		var fighter2Penalty = document.getElementById('fighter2_penalty_dropdown');

		if( fighter1Score.value != "" || fighter2Score.value != ""){
			isValid = false;
		}
		if( (fighter1Penalty.value == "" && fighter2Penalty.value == "")
			|| (fighter1Penalty.value != "" && fighter2Penalty.value != ""))
			{
			isValid = false;
		}
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

function penaltyDropDownChange(){

	var exchButton = document.getElementById('New_Exchange_Button');
	var radioVal = document.querySelector('input[name="mod"]:checked').value;

	var fighter1Penalty = document.getElementById('fighter1_penalty_dropdown');
	var fighter2Penalty = document.getElementById('fighter2_penalty_dropdown');

	
	$('#fighter1_score_dropdown').prop('selectedIndex',0);
	$('#fighter2_score_dropdown').prop('selectedIndex',0);

	if(DOUBLE_TYPE == DEDUCTIVE_AFTERBLOW){
		var fighter1Afterblow = document.getElementById('fighter1_afterblow_dropdown');
		fighter1Afterblow.selectedIndex = 0;
		fighter1Afterblow.disabled = "disabled";

		var fighter2Afterblow = document.getElementById('fighter2_afterblow_dropdown');
		fighter2Afterblow.selectedIndex = 0;
		fighter2Afterblow.disabled = "disabled";
	}

	document.getElementById('fighter1_penalty_div').classList.remove('hidden');
	document.getElementById('fighter2_penalty_div').classList.remove('hidden');

	setExchButtonClasses("alert");
	
	if(fighter1Penalty.value != "" || fighter2Penalty.value != ""){
		document.getElementById('Penalty_Radio').checked = 'checked';
		exchButton.value = "penalty";
		exchButton.disabled = false;
		exchButton.innerHTML = "Add: Penalty";
	} else if(radioVal == "penalty") {
		exchButton.disabled = true;
		exchButton.innerHTML = "Select Penalty Value";
		exchButton.value = "penalty";
	} else {
		exchButton.disabled = true;
		exchButton.innerHTML = "Invalid Input";
		exchButton.value = "";
	}

	isValidExchange();
	
}

/************************************************************************************/

function scoreDropdownChange(selectID){
	var exchButton = document.getElementById('New_Exchange_Button');
	

	var fighter1Score = document.getElementById('fighter1_score_dropdown');
	var fighter1Penalty = document.getElementById('fighter1_penalty_dropdown');
	
	var fighter2Score = document.getElementById('fighter2_score_dropdown');
	var fighter2Penalty = document.getElementById('fighter2_penalty_dropdown');
	
	var radioVal = document.querySelector('input[name="mod"]:checked').value;
	
	document.getElementById('fighter1_penalty_div').classList.add('hidden');
	document.getElementById('fighter2_penalty_div').classList.add('hidden');
	fighter1Penalty.selectedIndex = 0;
	fighter2Penalty.selectedIndex = 0;
	
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
				exchButton.innerHTML = "Add: Double Hit";
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
		} else if(fighter1Penalty.value !== "" && fighter2Penalty.value !== ""){
			
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
	$('#fighter1_penalty_dropdown').prop('selectedIndex',0);
	$('#fighter2_penalty_dropdown').prop('selectedIndex',0);
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

	document.getElementById('fighter1_penalty_div').classList.add('hidden');
	document.getElementById('fighter2_penalty_div').classList.add('hidden');
	
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
		case 'penalty':
			penaltyDropDownChange();
			break;
	}
	
	isValidExchange();

}

/**********************************************************************/

function editExchange(exchangeID){

	$('.exchangeID').val(exchangeID);

	if(exchangeID == ''){
		$('#editExchangeButton').show();
		$('#cancelEditExchangeButton').hide();
		$('.editExchangeWarningDiv').hide();
		 $('body').css('background-color', '');
	} else {
		$('#editExchangeButton').hide();
		$('#cancelEditExchangeButton').show();
		$('.editExchangeWarningDiv').show();
		$('body').css('background-color', '#ddd');
	}

}

/**********************************************************************/

function validateYoutube(){

	var buttons = document.getElementsByClassName('youtubeSubmitButton');
	
	var url = document.getElementById('youtubeField').value;
	
	if(url.startsWith("https://www.youtube.com") || url == ''){
		buttons[1].disabled = false;
		buttons[0].disabled = false;
	} else {
		buttons[1].disabled = true;
		buttons[0].disabled = true;
	}

}

/************************************************************************************/

function refreshOnNewExchange(matchID, exchangeID){
	
	var refreshPeriod = 10 * 1000; // seconds
	
	var intervalID = window.setInterval(function(){ a(); }, refreshPeriod);
	
	function a(){ 
		var query = "mode=newExchange";
		query = query + "&matchID=" + matchID.toString();
		query = query + "&exchangeID=" + exchangeID.toString();
		
		var xhr = new XMLHttpRequest();
		xhr.open("POST", AJAX_LOCATION+"?"+query, true);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.send();

		xhr.onreadystatechange = function (){
			if(this.readyState == 4 && this.status == 200){
				if(this.responseText.length > 1){ // If the fighter has already fought
					location.reload();
				}
			}
		};	
	}
}

/******************************************************************************/

function startTimer(timeDiv){
	
	if(timeDiv.classList.contains('running')){
		timeDiv.classList.remove('running');
		timeDiv.classList.remove('alert');
		timeDiv.classList.add('success');
		document.getElementById('manualTimerToggle').classList.remove('hidden');
		clearInterval(timerClock);
	} else {
		timeDiv.classList.add('running');
		timeDiv.classList.add('alert');
		timeDiv.classList.remove('success');
		document.getElementById('manualTimerToggle').classList.add('hidden');
		timerClock = setInterval(increaseTime,1000);
	}
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
	updateTimerDisplay();
}

/******************************************************************************/

function updateTimerDisplay(){
	time = document.getElementById('matchTime').value;
	
	// Update the form fields
	timerInputs = document.getElementsByClassName('matchTime');
	for(var i=0; i<timerInputs.length; i++){
		timerInputs[i].value = time;
	}
	
	// Update the button in M:SS format
	minutes = Math.floor(time/60);
	seconds = time - (minutes * 60);
	document.getElementById('timerMinutes').value = minutes;
	document.getElementById('timerSeconds').value = seconds;
	if(seconds < 10){
		seconds = "0"+seconds.toString();
	}
	str = minutes.toString()+":"+seconds.toString();
	document.getElementById('currentTime').innerHTML = str;
	
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

function manualTimeSet(){
	time = parseInt(document.getElementById('timerMinutes').value) * 60;
	time += parseInt(document.getElementById('timerSeconds').value);
	document.getElementById('matchTime').value = time;
	updateTimerDisplay();
	document.getElementById('manualSetDiv').classList.add('hidden')
}

/******************************************************************************/
