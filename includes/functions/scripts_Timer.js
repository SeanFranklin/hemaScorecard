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
