/************************************************************************************/

function isValidExchange(){
	var exchButton = document.getElementById('New_Exchange_Button');
	
	var fighter1Score = document.getElementById('fighter1_score_dropdown');
	var fighter1Afterblow = document.getElementById('fighter1_afterblow_dropdown');
	var fighter2Score = document.getElementById('fighter2_score_dropdown');
	var fighter2Afterblow = document.getElementById('fighter2_afterblow_dropdown');
	
	var radioVal = document.querySelector('input[name="mod"]:checked').value;
	var radioValOverlap = false;
	var isValid = true;
	
	if(radioVal == "hit" || radioVal == "penalty"){
		radioValOverlap = true;
	}
	
	var invalidText = "Invalid Exchange";
	
	if(fighter1Score.value != "" && (fighter2Score.value != "" || fighter2Afterblow.value != "") ){
		if(document.getElementById('isFullAfterblow').value != 1){
			isValid = false;
		}
	}
	if(fighter2Score.value != "" && (fighter1Score.value != "" || fighter1Afterblow.value != "") ){
		if(document.getElementById('isFullAfterblow').value != 1){
			isValid = false;
		}
	}
	if(fighter1Afterblow.value != "" && fighter1Score.value == ""){
		isValid = false;
	}
	if(fighter2Afterblow.value != "" && fighter2Score.value == ""){
		isValid = false;
	}
	
	if( (fighter1Score.value != "" || fighter2Score.value != "") && radioValOverlap == false){
		isValid = false;
	}
	
	if((fighter1Score.value == "" && fighter2Score.value == "") && radioVal == 'penalty'){
		 isValid = false;
		 invalidText = "Select Penalty Value";
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
	
	var fighter1Score = document.getElementById('fighter1_score_dropdown');
	var fighter1Afterblow = document.getElementById('fighter1_afterblow_dropdown');
	var fighter1Penalty = document.getElementById('fighter1_penalty_dropdown');
	
	var fighter2Score = document.getElementById('fighter2_score_dropdown');
	var fighter2Afterblow = document.getElementById('fighter2_afterblow_dropdown');
	var fighter2Penalty = document.getElementById('fighter2_penalty_dropdown');
	
	var radioVal = document.querySelector('input[name="mod"]:checked').value;
	
	fighter1Score.selectedIndex = 0;
	fighter1Afterblow.selectedIndex = 0;
	fighter1Afterblow.disabled = "disabled";
	
	fighter2Score.selectedIndex = 0;
	fighter2Afterblow.selectedIndex = 0;
	fighter2Afterblow.disabled = "disabled";
	
	document.getElementById('fighter1_penalty_div').classList.remove('hidden');
	document.getElementById('fighter2_penalty_div').classList.remove('hidden');

	$(exchButton).removeClass();
	$(exchButton).addClass("button large expanded");
	$(exchButton).addClass("alert");
	
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
	
}

/************************************************************************************/

function scoreDropdownChange(selectID){
	var exchButton = document.getElementById('New_Exchange_Button');
	
	var fighter1Score = document.getElementById('fighter1_score_dropdown');
	var fighter1Afterblow = document.getElementById('fighter1_afterblow_dropdown');
	var fighter1Penalty = document.getElementById('fighter1_penalty_dropdown');
	
	var fighter2Score = document.getElementById('fighter2_score_dropdown');
	var fighter2Afterblow = document.getElementById('fighter2_afterblow_dropdown');
	var fighter2Penalty = document.getElementById('fighter2_penalty_dropdown');
	
	var radioVal = document.querySelector('input[name="mod"]:checked').value;
	
	document.getElementById('fighter1_penalty_div').classList.add('hidden');
	document.getElementById('fighter2_penalty_div').classList.add('hidden');
	fighter1Penalty.selectedIndex = 0;
	fighter2Penalty.selectedIndex = 0;
	
	document.getElementById('NA_Radio').checked = 'checked';
	

// Disable Afterblow if there is no initial hit for a fighter
	if(fighter1Score.value == ""){
		fighter1Afterblow.selectedIndex=0;
		fighter1Afterblow.disabled = "disabled";
	} else {fighter1Afterblow.disabled = null;}
	
	if(fighter2Score.value == ""){
		fighter2Afterblow.selectedIndex=0;
		fighter2Afterblow.disabled = "disabled";
	} else {
		fighter2Afterblow.disabled = null;}

// Check if it is a full afterblow scoring
	var isFAB = $('#isFullAfterblow').attr('value');
	
// Select no exchange if no scores are selected
	if(fighter1Score.value === "" && fighter2Score.value == ""){
		document.getElementById('No_Exchange_Radio').checked = 'checked';
		exchButton.value = "noExchange";
		exchButton.innerHTML = "Add: No Exchange";
		$(exchButton).removeClass();
		$(exchButton).addClass("button large expanded");
		$(exchButton).addClass("");
	} else if(isFAB == 1) {
		if(		(fighter1Score.value == "noQuality" && fighter2Score.value == "")
			||  (fighter1Score.value == "" && fighter2Score.value == "noQuality"))
		{
			exchButton.value = "noQuality";
			exchButton.innerHTML = "Add: No Quality";	
		} else {
			exchButton.value = "scoringHit";
			if(fighter1Score.value !== "" && fighter2Score.value !== ""){
				exchButton.innerHTML = "Add: Double Hit";
			} else {
				exchButton.innerHTML = "Add: Clean Hit";
			}
		}
		
		
	} else {	
		if(fighter1Score.value == "noQuality" || fighter2Score.value == "noQuality"){
			exchButton.value = "noQuality";
			exchButton.innerHTML = "Add: No Quality";
				$(exchButton).removeClass();
				$(exchButton).addClass("button large expanded");
				$(exchButton).addClass("hollow");
		} else if(fighter1Penalty.value !== "" && fighter2Penalty.value !== ""){
			
		} else {
			exchButton.value = "scoringHit";
			$(exchButton).removeClass();
			$(exchButton).addClass("button large expanded");
			$(exchButton).addClass("success");		
			if(fighter1Afterblow.value != "" || fighter2Afterblow.value != ""){
				exchButton.innerHTML = "Add: Afterblow";
			} else {
				exchButton.innerHTML = "Add: Clean Hit";
			}
		}
		
		
	}

	isValidExchange();
	
}

/************************************************************************************/

function modifiersRadioButtons(){
	var exchButton = document.getElementById('New_Exchange_Button');
	
	var radioVal = document.querySelector('input[name="mod"]:checked').value;
	
	var fighter1Score = document.getElementById('fighter1_score_dropdown');
	var fighter1Afterblow = document.getElementById('fighter1_afterblow_dropdown');
	var fighter1Penalty = document.getElementById('fighter1_penalty_dropdown');
	
	var fighter2Score = document.getElementById('fighter2_score_dropdown');
	var fighter2Afterblow = document.getElementById('fighter2_afterblow_dropdown');
	var fighter2Penalty = document.getElementById('fighter2_penalty_dropdown');		
	
	fighter1Score.selectedIndex = 0;
	fighter1Afterblow.selectedIndex = 0;
	fighter1Penalty.selectedIndex = 0;
	fighter2Score.selectedIndex = 0;
	fighter2Afterblow.selectedIndex = 0;
	fighter2Penalty.selectedIndex = 0;
	
	document.getElementById('fighter1_penalty_div').classList.add('hidden');
	document.getElementById('fighter2_penalty_div').classList.add('hidden');
	
	switch(radioVal){
		case 'noExch':
			exchButton.value = "noExchange";
			exchButton.innerHTML = "Add: No Exchange";
			$(exchButton).removeClass();
			$(exchButton).addClass("button large expanded");
			$(exchButton).addClass("");
			break;
		case 'doubleHit':
			exchButton.value = "doubleHit";
			exchButton.innerHTML = "Add: Double Hit";
			$(exchButton).removeClass();
			$(exchButton).addClass("button large expanded");
			$(exchButton).addClass("alert");
			break;
		case 'clearLast':
			exchButton.value = "clearLastExchange";
			exchButton.innerHTML = "Remove: Last Exchange";
			$(exchButton).removeClass();
			$(exchButton).addClass("button large expanded");
			$(exchButton).addClass("warning");
			break;
		case 'clearAll':
			exchButton.value = "clearAllExchanges";
			exchButton.innerHTML = "Remove: All Exchanges";
			$(exchButton).removeClass();
			$(exchButton).addClass("button large expanded");
			$(exchButton).addClass("alert hollow");
			break;
		case 'penalty':
			penaltyDropDownChange();
			break;
	}
	
	isValidExchange();

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
		xhr.open("POST", "/v6/includes/functions/AJAX.php?"+query, true);
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
	xhr.open("POST", "/v6/includes/functions/AJAX.php?"+query, true);
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
