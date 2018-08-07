/**********************************************************************/

function toggleTournamentEditingFields(tournamentID, elimID){
	
	displayOn = 'inline'

	var hideSpeed = 'fast';
	var showSpeed = 'fast';
	
	var fieldsToDisplay = [];
	
	// Results Only
	fieldsToDisplay [1] = {			
		elimID: 'show'
	};
	
	// Pool & Bracket 
	fieldsToDisplay [2] = {
		elimID: 'show',			
		doubleID: 'show',
		rankingID: 'refresh',
		color1: 'show',
		color2: 'show',
		maxDoubles: 'show',
		maxPoolSize: 'show',
		normalizePoolSize: 'show',
		allowTies: 'show',
		isCuttingQual: 'show',
		maxExchanges: 'show',
		useTimer: 'show',
		controlPoint: 'show',
		isPrivate: 'show',
		reverseScore: 'show'
	};
	
	// Direct Bracket
	fieldsToDisplay [3] = {
		elimID: 'show',			 
		doubleID: 'show',
		color1: 'show',
		color2: 'show',
		maxDoubles: 'show',
		allowTies: 'show',
		isCuttingQual: 'show',
		maxExchanges: 'show',
		useTimer: 'show',
		controlPoint: 'show',
		isPrivate: 'show',
		reverseScore: 'show'
	};
	
	// Pool Sets
	fieldsToDisplay [4] = {
		elimID: 'show',			 
		doubleID: 'show',
		rankingID: 'refresh',
		color1: 'show',
		color2: 'show',
		maxDoubles: 'show',
		maxPoolSize: 'show',
		normalizePoolSize: 'show',
		allowTies: 'show',
		isCuttingQual: 'show',
		maxExchanges: 'show',
		useTimer: 'show',
		controlPoint: 'show',
		isPrivate: 'show',
		reverseScore: 'show'
	};
	
	// Scored Event
	fieldsToDisplay [5] = {
		elimID: 'show',			 
		rankingID: 'refresh',
		baseValue: 'show',
		isCuttingQual: 'show',
		isPrivate: 'show'
	};
	
	function toggleTournamentEntryDiv(){
		var divID = $(this).attr('Id');
		if(typeof divID !== 'string'){ return; }
		
		
		var divName = divID.substring(0,divID.lastIndexOf("_"));
		if(divName == 'elimID'){ return; }

		switch(fieldsToDisplay[+elimID][divName]){
			case 'show':
				$(this).show(showSpeed);
				break;
			case 'refresh':
				$("#"+divName+"_select"+tournamentID).prop('selectedIndex',0);
				$(this).hide(hideSpeed).show(showSpeed);
				break;
			default:
				$(this).hide(hideSpeed);
				break;
		};
	};

// Toggle fields on or off based on fieldsToDisplay table
	$("#requiredFields_"+tournamentID).children().each(toggleTournamentEntryDiv);
	$("#optionalFields_"+tournamentID).children().each(toggleTournamentEntryDiv);
	

// Check for fields which are just toggled by double hits
	if(fieldsToDisplay[elimID]['maxDoubles'] == 'show'){
		edit_doubleType(tournamentID);
	}
	
}

/**********************************************************************/

function enableTournamentButton(tournamentID){

	var elimID = document.getElementById('elimID_select'+tournamentID).value;
	var button = document.getElementById('editTournamentButton'+tournamentID);
	var warrningMessages = [];

	if(elimID.length == 0){
		warrningMessages.push('No Elim Type selected');
	}

// Check modes related to fighting matches

	if(elimID == 2 || elimID == 3 || elimID == 4){
		doubleID = document.getElementById('doubleID_select'+tournamentID).value;
		if(doubleID.length == 0){
			warrningMessages.push('Please select Double/Afterblow Type');
		}

		if(doubleID == 3){
			netScoreMode = document.getElementById('notNetScore_select'+tournamentID).value;
			if(netScoreMode.length == 0){
				warrningMessages.push('Please select Net Score preference');
			}
		}
	}

// Check modes relating to score/rankings

	if(elimID == 2 || elimID == 4 || elimID == 5){
		rankingID = document.getElementById('rankingID_select'+tournamentID).value;
		if(rankingID.length == 0){
			warrningMessages.push('Please select No Ranking Type');
		}
	}

	if(elimID == 5){
		baseValue = document.getElementById('baseValue_select'+tournamentID).value;
		if(baseValue == '' || baseValue < 0 || baseValue > 100){
			warrningMessages.push('Please input a Base Score Value');
		}
	} else { // If it isn't a scored event, still have to manage the base score value for reverse score tournaments
		if($('#reverseScore_select'+tournamentID).val() > 0){
			$("#baseValue_div"+tournamentID).show();
		} else {
			$("#baseValue_div"+tournamentID).hide();
		}
	}

// Check if the reverse score option is selected

	if($('#reverseScore_select'+tournamentID).val() > 0){
		if($('#doubleID_select'+tournamentID).val() == 2){
			warrningMessages.push('Reverse Score can not be used with Deductive Afterblow');
		}
		if($('#doubleID_select'+tournamentID).val() == 3 && $('#notNetScore_select'+tournamentID).val() == 0){
			warrningMessages.push('Reverse Score can not be used with No Net Points');
		}
	}



// Set warning messages

	if(warrningMessages.length == 0){
		$('#tournamentWarnings_'+tournamentID).html('<BR>');
		button.disabled = false;
	} else {
		$('#tournamentWarnings_'+tournamentID).html("<ul>");
		$.each(warrningMessages, function( index, value ) {
			var warningText = "<li class='red-text'>"+value+"</li>";
			$('#tournamentWarnings_'+tournamentID).append(warningText);
		});
		$('#tournamentWarnings_'+tournamentID).append("</ul>");
		button.disabled = true;
	}

	
}

/**********************************************************************/

function edit_doubleType(tournamentID){

	doubleID = document.getElementById('doubleID_select'+tournamentID).value;
	
	if(doubleID == 3){ // Full Afterblow
		$('#maxDoubles_div'+tournamentID).hide('fast');
		$('#notNetScore_div'+tournamentID).show('fast');
	} else {
		$('#maxDoubles_div'+tournamentID).show('fast');
		$('#notNetScore_div'+tournamentID).hide('fast');
		$("#notNetScore_select"+tournamentID)[0].selectedIndex = 0
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
	xhr.open("POST", AJAX_LOCATION+"?"+query, true);
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