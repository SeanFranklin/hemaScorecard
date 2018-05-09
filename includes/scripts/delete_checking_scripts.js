

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
		xhr.open("POST", AJAX_LOCATION+"?"+query, true);
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

	
