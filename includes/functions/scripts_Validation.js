
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

function schoolInputPlaceholders(){

	var abreviation = document.getElementById('schoolFull').value;
	abreviation = abreviation.replace( /[^A-Z]/g, '' );
	document.getElementById('schoolAbreviation').placeholder = abreviation;
	
	document.getElementById('schoolShort').placeholder = schoolFull.value;
	
}

/**********************************************************************/

function edit_doubleType(tournamentID){

	doubleID = document.getElementById('doubleID_select'+tournamentID).value;
	
	if(doubleID == 3){
		document.getElementById('maxDoubles_div'+tournamentID).style.display = 'none';
	} else {
		document.getElementById('maxDoubles_div'+tournamentID).style.display = 'inline';
	}
	
	enableTournamentButton(tournamentID);

}


/**********************************************************************/

function edit_elimType(tournamentID){

	elimID = document.getElementById('elimID_select'+tournamentID).value;
	
	toggleTournamentEditingFields(tournamentID, elimID);

	//if(tournamentID == 'new'){
		if(elimID == 1){
			document.getElementById('editTournamentButton'+tournamentID).disabled = false;
			return;
		} else if(elimID == 3){
			enableTournamentButton(tournamentID);
			return;
		}	
	//}

	var query = "mode=getRankingTypes&elimID="+elimID;

	var xhr = new XMLHttpRequest();
	xhr.open("POST", "/v6/includes/functions/AJAX.php?"+query, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send();

	xhr.onreadystatechange = function (){
		if(this.readyState == 4 && this.status == 200){
			if(this.responseText.length > 1){
				
				rankingTypes = JSON.parse(this.responseText);
				select = document.getElementById('rankingID_select'+tournamentID);
				select.length = 0;

				var option = document.createElement('option');
				option.disabled = true;
				option.selected = true;
				select.appendChild(option);

				for(var rankingID in rankingTypes){
					
					var option = document.createElement('option');
					option.value = rankingID;
					option.innerHTML = rankingTypes[rankingID];
					select.appendChild(option);
					
				}

				enableTournamentButton(tournamentID);
			}
		}
	}
}

/**********************************************************************/

function toggleTournamentEditingFields(tournamentID, elimID){
	
	displayOn = 'inline'
	
	document.getElementById('rankingID_select'+tournamentID).selectedIndex = 0;
	
	switch(+elimID){
		case 1: // Results Only
			document.getElementById('doubleID_div'+tournamentID).style.display = 'none';
			document.getElementById('doubleID_select'+tournamentID).selectedIndex = 0;
			document.getElementById('rankingID_div'+tournamentID).style.display = 'none';
			document.getElementById('baseValue_div'+tournamentID).style.display = 'none';
			document.getElementById('color1_div'+tournamentID).style.display = 'none';
			document.getElementById('color2_div'+tournamentID).style.display = 'none';
			document.getElementById('maxDoubles_div'+tournamentID).style.display = 'none';
			document.getElementById('maxPoolSize_div'+tournamentID).style.display = 'none';
			document.getElementById('normalizePoolSize_div'+tournamentID).style.display = 'none';
			document.getElementById('allowTies_div'+tournamentID).style.display = 'none';
			document.getElementById('isCuttingQual_div'+tournamentID).style.display = 'none';
			document.getElementById('maxExchanges_div'+tournamentID).style.display = 'none';
			document.getElementById('useTimer_div'+tournamentID).style.display = 'none';
			break;
		case 2: // Pool & Bracket
			document.getElementById('doubleID_div'+tournamentID).style.display = displayOn;
			document.getElementById('rankingID_div'+tournamentID).style.display = displayOn;
			document.getElementById('baseValue_div'+tournamentID).style.display = 'none';
			document.getElementById('color1_div'+tournamentID).style.display = displayOn;
			document.getElementById('color2_div'+tournamentID).style.display = displayOn;
			document.getElementById('maxDoubles_div'+tournamentID).style.display = 'none';
			if(document.getElementById('doubleID_select'+tournamentID).value != 3){
				document.getElementById('maxDoubles_div'+tournamentID).style.display = displayOn;
			} else {
				document.getElementById('maxDoubles_div'+tournamentID).style.display = 'none';
			}
			document.getElementById('maxPoolSize_div'+tournamentID).style.display = displayOn;
			document.getElementById('normalizePoolSize_div'+tournamentID).style.display = displayOn;
			document.getElementById('allowTies_div'+tournamentID).style.display = displayOn;
			document.getElementById('isCuttingQual_div'+tournamentID).style.display = displayOn;
			document.getElementById('maxExchanges_div'+tournamentID).style.display = displayOn;
			document.getElementById('useTimer_div'+tournamentID).style.display = displayOn;
			break;
		case 3: // Direct Bracket
			document.getElementById('doubleID_div'+tournamentID).style.display = displayOn;
			document.getElementById('rankingID_div'+tournamentID).style.display ='none';
			document.getElementById('baseValue_div'+tournamentID).style.display = 'none';
			document.getElementById('color1_div'+tournamentID).style.display = displayOn;
			document.getElementById('color2_div'+tournamentID).style.display = displayOn;
			if(document.getElementById('doubleID_select'+tournamentID).value != 3){
				document.getElementById('maxDoubles_div'+tournamentID).style.display = displayOn;
			} else {
				document.getElementById('maxDoubles_div'+tournamentID).style.display = 'none';
			}
			document.getElementById('maxPoolSize_div'+tournamentID).style.display = 'none';
			document.getElementById('normalizePoolSize_div'+tournamentID).style.display = 'none';
			document.getElementById('allowTies_div'+tournamentID).style.display = displayOn;
			document.getElementById('isCuttingQual_div'+tournamentID).style.display = displayOn;
			document.getElementById('maxExchanges_div'+tournamentID).style.display = displayOn;
			document.getElementById('useTimer_div'+tournamentID).style.display = displayOn;
			break;
		case 4: // Pool Sets
			document.getElementById('doubleID_div'+tournamentID).style.display = displayOn;
			document.getElementById('rankingID_div'+tournamentID).style.display = displayOn;
			document.getElementById('baseValue_div'+tournamentID).style.display = 'none';
			document.getElementById('color1_div'+tournamentID).style.display = displayOn;
			document.getElementById('color2_div'+tournamentID).style.display = displayOn;
			if(document.getElementById('doubleID_select'+tournamentID).value != 3){
				document.getElementById('maxDoubles_div'+tournamentID).style.display = displayOn;
			} else {
				document.getElementById('maxDoubles_div'+tournamentID).style.display = 'none';
			}
			document.getElementById('maxPoolSize_div'+tournamentID).style.display = displayOn; 
			document.getElementById('normalizePoolSize_div'+tournamentID).style.display = displayOn;
			document.getElementById('allowTies_div'+tournamentID).style.display = displayOn;
			document.getElementById('isCuttingQual_div'+tournamentID).style.display = displayOn;
			document.getElementById('maxExchanges_div'+tournamentID).style.display = displayOn;
			document.getElementById('useTimer_div'+tournamentID).style.display = displayOn;
			break;
		case 5: // Scored Event
			document.getElementById('doubleID_div'+tournamentID).style.display = 'none';
			document.getElementById('doubleID_select'+tournamentID).selectedIndex = 0;
			document.getElementById('rankingID_div'+tournamentID).style.display = displayOn;
			document.getElementById('baseValue_div'+tournamentID).style.display = displayOn;
			document.getElementById('baseValue_div'+tournamentID).value = null;
			document.getElementById('color1_div'+tournamentID).style.display = 'none';
			document.getElementById('color2_div'+tournamentID).style.display = 'none';
			document.getElementById('maxDoubles_div'+tournamentID).style.display = 'none';
			document.getElementById('maxPoolSize_div'+tournamentID).style.display = 'none';
			document.getElementById('normalizePoolSize_div'+tournamentID).style.display = 'none';
			document.getElementById('allowTies_div'+tournamentID).style.display = 'none';
			document.getElementById('isCuttingQual_div'+tournamentID).style.display = displayOn;
			document.getElementById('maxExchanges_div'+tournamentID).style.display = 'none';
			document.getElementById('useTimer_div'+tournamentID).style.display = 'none';
			break;
		default: // No Selection
			document.getElementById('doubleID_div'+tournamentID).style.display = 'none';
			document.getElementById('rankingID_div'+tournamentID).style.display = 'none';
			document.getElementById('doubleID_select'+tournamentID).selectedIndex = 0;
			document.getElementById('baseValue_div'+tournamentID).style.display = 'none';
			document.getElementById('color1_div'+tournamentID).style.display = 'none';
			document.getElementById('color2_div'+tournamentID).style.display = 'none';
			document.getElementById('maxDoubles_div'+tournamentID).style.display = 'none';
			document.getElementById('maxPoolSize_div'+tournamentID).style.display = 'none';
			document.getElementById('normalizePoolSize_div'+tournamentID).style.display = 'none';
			document.getElementById('isCuttingQual_div'+tournamentID).style.display = displayOn;
			document.getElementById('maxExchanges_div'+tournamentID).style.display = 'none';
			document.getElementById('useTimer_div'+tournamentID).style.display = 'none';
	}
}

/**********************************************************************/

function enableTournamentButton(tournamentID){

	elimID = document.getElementById('elimID_select'+tournamentID).value;
	button = document.getElementById('editTournamentButton'+tournamentID);

	if(elimID.length == 0){
		button.disabled = true;
		return;
	}

	if(elimID == 2 || elimID == 3 || elimID == 4){
		doubleID = document.getElementById('doubleID_select'+tournamentID).value;
		if(doubleID.length == 0){
			button.disabled = true;
			return;
		}
	}

	if(elimID == 2 || elimID == 4 || elimID == 5){
		rankingID = document.getElementById('rankingID_select'+tournamentID).value;
		if(rankingID.length == 0){
			button.disabled = true;
			return;
		}
	}

	if(elimID == 5){
		baseValue = document.getElementById('baseValue_select'+tournamentID).value;
		if(baseValue == '' || baseValue < 0 || baseValue > 100){
			button.disabled = true;
			return;
		}
	}
	
	button.disabled = false;
	
}

/**********************************************************************/

checkIfFought.numConflictsChecked = 0;

function checkIfFought(checkbox){
	
	var alertTextColor = 'red';

	divElement = document.getElementById('divFor'+checkbox.id);

	var elementName = checkbox.name;
	var elementType = elementName.substring(0, elementName.indexOf('['));
	
	var deleteOptionSelected = checkbox.checked;
	if(elementType == 'editParticipantData'){
		// When editing an event, removing a checkbox indicates removing from a tournament
		deleteOptionSelected = !deleteOptionSelected;
	}

	if(deleteOptionSelected){
		
		var extractedData = elementName.match(/[^[\]]+(?=])/g);
		var query = "mode=hasFought";
		
		switch(elementType){
			case 'deleteFromGroup':
				query = query + "&groupID=" + extractedData[0].toString();
				query = query + "&rosterID=" + extractedData[1].toString();
				break;
			case 'deleteGroup':
				query = query + "&groupID=" + extractedData[0].toString();
				break;
			case 'deleteFromTournament':
				query = query + "&rosterID=" + extractedData[0].toString();
				query = query + "&tournamentID=" + document.getElementById('tournamentID').value;
				break;
			case 'deleteFromEvent':
				query = query + "&rosterID=" + extractedData[0].toString();
				query = query + "&eventID=" + document.getElementById('eventID').value;
				break;
			case 'editParticipantData':
				query = query + "&rosterID=" + document.getElementById('editRosterID').value;
				query = query + "&tournamentID=" + extractedData[1].toString();
				
		}

		var xhr = new XMLHttpRequest();
		xhr.open("POST", "/v6/includes/functions/AJAX.php?"+query, true);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.send();

		xhr.onreadystatechange = function (){
			if(this.readyState == 4 && this.status == 200){
				if(this.responseText.length > 1){ // If the fighter has already fought
					hasAlreadyFoughtWarning(true, divElement);
					checkIfFought.numConflictsChecked++;
				}
			}
		};

	} else {
		if(divElement.style.color == alertTextColor){
			checkIfFought.numConflictsChecked--;
			if(checkIfFought.numConflictsChecked == 0){	
				hasAlreadyFoughtWarning(false, divElement);
			}
			
		}
		divElement.style.color = null;
	}
	
}

/**********************************************************************/

function hasAlreadyFoughtWarning(show, textElement){
	var alertTextColor = 'red';

	container = document.getElementById('deleteButtonContainer');
	
	if(show){
		if(container != null){
			container.innerHTML = "<a class='button alert hollow' data-open='confirmDelete' \
								id='deleteButton'> Delete Selected </a>";
		}
		textElement.style.color = alertTextColor;
		
		var block = document.getElementById('confirmEditSubmit');
		if(block != null){
			block.style.display = 'block';
			document.getElementById('normalEditSubmit').disabled = true;
		}
		
		
	} else {
		if(container != null){
			container.innerHTML = "<button class='button alert hollow' name='formName' \
										id='deleteButton'>Delete Selected</button>";
		}
		textElement.style.color = null;
		
		var deleteButton = document.getElementById('deleteButton');
		
		if(deleteButton != null){
			deleteButton.value = document.getElementById('deleteFormName').value;
			
		}

		var block = document.getElementById('confirmEditSubmit');
		if(block != null){
			block.style.display = null;
			document.getElementById('normalEditSubmit').disabled = false;
		}
	}
	
}

	
