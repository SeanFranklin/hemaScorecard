


/************************************************************************************/


function toggle(divName, divName2 = null) {
	
    var x = document.getElementById(divName);
    if (x.offsetHeight == 0) {
        x.style.display = 'block';
    } else {
        x.style.display = 'none';
    }
    
    if(divName2 == null){return;}
    
    var x = document.getElementById(divName2);
    if (x.offsetHeight == 0) {
        x.style.display = 'block';
    } else {
        x.style.display = 'none';
    }
    
}

/******************************************************************************/

function submitAddMultipleToRound(groupID){
// Used for adding multiple fighters to a round at a time
// The button to do this is inside another form, which needs to have it's fields
// manipulated by JS in order to only submit part of it
	
	var form = document.getElementById('roundRosterForm');
	
	var formName = document.createElement('input');
	formName.type = 'hidden';
	formName.name = 'formName';
	formName.value = 'addMultipleFighterToRound';
	form.appendChild(formName);
	
	var groupIDField = document.createElement('input');
	groupIDField.type = 'hidden';
	groupIDField.name = 'groupID';
	groupIDField.value = groupID;
	form.appendChild(groupIDField);

	form.submit();
}

/******************************************************************************/

function changeRosterOrderType(type){
	
	var myForm = document.getElementById('rosterViewMode');
	myForm.method = 'POST';

	
	var mode = document.createElement('input');
	mode.type = 'hidden';
	mode.value = type;
	mode.name = 'rosterViewMode'

	myForm.appendChild(mode);

	myForm.submit();

}

/************************************************************************************/

function toggleEventList(year, linkElement){
	
	var divList = document.getElementById('eventListContainer').getElementsByTagName('div');
	
	for(var i=0; i < divList.length; i++){
		if(!(divList[i].id)){continue;}
		

		if(divList[i].id == 'events-'+year){
			divList[i].style.display = 'inline';
			document.getElementById('browseEventsTitle').innerHTML = linkElement.innerHTML;
		} else {
			divList[i].style.display = 'none';
		}

	}
	
}


/************************************************************************************/

function toggleTableRow(divName, divName2 = null) {
    var x = document.getElementById(divName);

    if (x.style.display == '') {
        x.style.display = 'table-row';
    } else {
        x.style.display = '';
    }
    
    if(divName2 == null){return;}
    
    var x = document.getElementById(divName2);
    if (x.style.display == '') {
        x.style.display = 'table-row';
    } else {
        x.style.display = '';
    }
    
}

/************************************************************************************/

function toggleCheckbox(checkboxID, divID){
	checkbox = document.getElementById(checkboxID);
	
	if(checkbox.checked){
		checkbox.checked = false;
		divID.style.background = 'none';
	} else {
		checkbox.checked = true;
		divID.style.background = '#3adb76';
	}

	checkIfFought(checkbox);
}

/************************************************************************************/

function toggleRadio(radioID){
	document.getElementById(radioID).checked = true;
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

/************************************************************************************/

function autoRefresh(timeInterval){
	
	if(timeInterval == 0){ return; }
	var refreshPeriod = timeInterval * 1000; // seconds
	
	var intervalID = window.setInterval(function(){ a(); }, refreshPeriod);
	
	function a(){ 
		location.reload();
	}
}

/************************************************************************************/

function hideRankingInputs(elimType, formName) {
	var Pool_Sets = document.getElementById(formName + '_Pool_Sets');
	var Pool_Bracket = document.getElementById(formName + '_Pool_Bracket');
	var Scored_Event = document.getElementById(formName + '_Scored_Event');
	var Double_Type = document.getElementById(formName + '_Double_Type');


    Pool_Sets.style.display = 'none';
    Pool_Bracket.style.display = 'none';
    Scored_Event.style.display = 'none';
    
    if(elimType.value == 2){
		Pool_Bracket.style.display = 'block';
	}
	if(elimType.value == 4){
		Pool_Sets.style.display = 'block';
    }
    if(elimType.value == 5){
		Scored_Event.style.display = 'block';
	}
	
	if(elimType.value == 1 || elimType.value == 5){
		Double_Type.style.display = 'none';
	} else {
		Double_Type.style.display = 'block';
	}
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
			break;
		case 'doubleHit':
			exchButton.value = "doubleHit";
			exchButton.innerHTML = "Add: Double Hit";
			break;
		case 'clearLast':
			exchButton.value = "clearLastExchange";
			exchButton.innerHTML = "Remove: Last Exchange";
			break;
		case 'clearAll':
			exchButton.value = "clearAllExchanges";
			exchButton.innerHTML = "Remove: All Exchanges";
			break;
		case 'penalty':
			penaltyDropDownChange();
			break;
	}
	
	isValidExchange();

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


	
// Select no exchange if no scores are selected
	if(fighter1Score.value === "" && fighter2Score.value == ""){
		document.getElementById('No_Exchange_Radio').checked = 'checked';
		exchButton.value = "noExchange";
		exchButton.innerHTML = "Add: No Exchange";
	} else {
		if(fighter1Score.value == "noQuality" || fighter2Score.value == "noQuality"){
			exchButton.value = "noQuality";
			exchButton.innerHTML = "Add: No Quality";
		} else if(fighter1Penalty.value !== "" && fighter2Penalty.value !== ""){
			
		} else {
			exchButton.value = "scoringHit";	
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

/**********************************************************************/

function editParticipant(rosterID){
	var div = document.getElementById('editParticipantModal');
	checkIfFought.numConflictsChecked = 0;
	hasAlreadyFoughtWarning(false, div);
	
	if(rosterID == 0){
		$("#editParticipantModal").foundation("close");
		return;
	}
	
	$("#editParticipantModal").foundation("open");

	var eventID = document.getElementById('eventID').value;
	
	var query = "mode=fighterInfo&rosterID="+rosterID.toString();
	query = query + "&eventID=" + eventID.toString();
	var xhr = new XMLHttpRequest();
	xhr.open("POST", "/v6/includes/functions/AJAX.php?"+query, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send();

	xhr.onreadystatechange = function (){
		if(this.readyState == 4 && this.status == 200){
			if(this.responseText.length > 1){ // If the fighter has already fought
				
				console.log(this.responseText); //////////////////////////////////////////////////////////////////
				var data = JSON.parse(this.responseText);
				fillInFields(data);
			}
		}
	};
	
// Populate fields of the form with data
	function fillInFields(data){
		var elementList = document.getElementById('editParticipantForm');
		for(var i =0; i < elementList.length; i++){
			elementList[i].checked = false;
		}
		psudoButtons = document.getElementById('editTournamentListDiv').getElementsByTagName('div');
		for(var i=0; i < psudoButtons.length; i++){
			psudoButtons[i].style.background = 'none';
		}
		
		document.getElementById('editRosterID').value = rosterID;
		document.getElementById('editFirstName').value = data.firstName;
		document.getElementById('editLastName').value = data.lastName;
		document.getElementById('editSchoolID').value = data.schoolID;
		document.getElementById('editFullName').innerHTML = data.firstName+" "+data.lastName;
		document.getElementById('rosterIDforDelete').name = "deleteFromEvent["+rosterID+"]";

		for(var tournamentID in data.tournamentIDs){
			document.getElementById('editTournamentID'+tournamentID).checked = true;
			document.getElementById('divForeditTournamentID'+tournamentID).style.background = '#3adb76';
		}
		
		divList = document.getElementsByClassName('tournamentSelectBox');
		
		for(var i=0; i < divList.length; i++){
			divList[i].style.color = null;
		}
		
	}
	
// Check if the fighter has already fought
	var query = "mode=hasFought";
	query = query + "&rosterID=" + rosterID.toString();
	query = query + "&eventID=" + document.getElementById('eventID').value;
	
	var xhr = new XMLHttpRequest();
	xhr.open("POST", "/v6/includes/functions/AJAX.php?"+query, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send();

	xhr.onreadystatechange = function (){
		if(this.readyState == 4 && this.status == 200){
			text = document.getElementById('warnIfFought');
			if(this.responseText.length > 1){ // If the fighter has already fought
				text.innerHTML = "<div class='callout alert'><u>Note:</u> This participant has already started competing</div>";
			} else {
				text.innerHTML = null;
			}
		}
	};
	
	
}

/**********************************************************************/

function openModal(modalName){
	$("#"+modalName).foundation("open");
}

/**********************************************************************/

function safeReload(){
	window.location = window.location.href;
}

/**********************************************************************/

function logInEventToggle(selectElement){
	if(selectElement.value == 3 || selectElement.value == 4){
		document.getElementById('logInEventList').style.display='block';
	} else {
		document.getElementById('logInEventList').style.display='none';
	}
}

/************************************************************************************/

function poolNumberChange(callingSelect){
	
	var originalIndex = callingSelect.getAttribute('data-current-index')
	var newIndex = callingSelect.selectedIndex;
	callingSelect.setAttribute('data-current-index',newIndex);
	
	var poolSelectDivs = document.getElementsByClassName('pool-number-select');
	
	if(newIndex < originalIndex){

		for(var i = 0; i < poolSelectDivs.length; i++){
			if(poolSelectDivs[i].selectedIndex >= newIndex &&
				poolSelectDivs[i].selectedIndex <= originalIndex ){
		
			if(poolSelectDivs[i].id == callingSelect.id){continue;}

			poolSelectDivs[i].selectedIndex += 1;
			poolSelectDivs[i].setAttribute('data-current-index',poolSelectDivs[i].selectedIndex);
			}
		}

	} else if(newIndex > originalIndex){
		for(var i = 0; i < poolSelectDivs.length; i++){
			if(poolSelectDivs[i].selectedIndex <= newIndex &&
				poolSelectDivs[i].selectedIndex >= originalIndex ){
		
			if(poolSelectDivs[i].id == callingSelect.id){continue;}

			poolSelectDivs[i].selectedIndex -= 1;
			poolSelectDivs[i].setAttribute('data-current-index',poolSelectDivs[i].selectedIndex);
			}
		}
	}

}

/************************************************************************************/

function reOrderPools(button){
	
	var mainDiv = document.getElementById('poolRosterDiv');
	
	if(button.value == 'editing'){
		// Submit Form
		rosterForm = document.getElementById('poolRosterForm');
		
		var formName = document.createElement('input');
		formName.type='hidden';
		formName.name='formName';
		formName.value='changeGroupOrder';
		
		rosterForm.appendChild(formName);
		rosterForm.submit();
		
	} else {
		// Enable Editing
		button.value = 'editing';
		button.innerHTML = 'Done';
		mainDiv.disabled = true;
		disableFields(mainDiv, true);
	}
	
	function disableFields(mainDiv, isEditing){
		var allInputs = document.getElementsByTagName('input');
		for(var i = 0; i < allInputs.length; i++){
			allInputs[i].disabled = isEditing;
		}
		
		var allSelects = document.getElementsByTagName('select');
		for(var i = 0; i < allSelects.length; i++){
			allSelects[i].disabled = isEditing;
		}
		
		var allButtons = document.getElementsByTagName('button');
		for(var i = 0; i < allButtons.length; i++){
			
			if(allButtons[i].classList.contains('dont-disable')){continue;}
			allButtons[i].disabled = isEditing;
		}
	
		var opacityElements = mainDiv.getElementsByClassName('opacity-toggle');
		for(var i = 0; i < opacityElements.length; i++){
		
			if(isEditing){
				if(opacityElements[i].tagName == 'SELECT'){
					opacityElements[i].style.opacity = '0.2';
				} else {
					opacityElements[i].style.opacity = '0.6';
				}
			} else {
				opacityElements[i].style.opacity = 1;
			}
		}
		

		var poolNameDivs = document.getElementsByClassName('hide-toggle');
		for(var i = 0; i < poolNameDivs.length; i++){
			if(poolNameDivs[i].offsetHeight == 0){
				poolNameDivs[i].style.display = 'inline';	
			} else {
				poolNameDivs[i].style.display = 'none';	
			}
		}
		
		var poolSelectDivs = mainDiv.getElementsByClassName('pool-number-select');
		for(var i = 0; i < poolSelectDivs.length; i++){
			poolSelectDivs[i].disabled = !poolSelectDivs[i].disabled;
		}
		
	}
	

}





