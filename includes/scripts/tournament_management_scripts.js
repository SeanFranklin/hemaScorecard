/**********************************************************************/

function toggleTournamentEditingFields(tournamentID, formatID){
	
	displayOn = 'inline'

	var hideSpeed = 'fast';
	var showSpeed = 'fast';
	
	var fieldsToDisplay = [];
	
	// Results Only
	fieldsToDisplay [FORMAT_RESULTS] = {			
		formatID: 'show',
		isTeams: 'show',
		hideFinalResults: 'show'
	};
	
	// Matches 
	fieldsToDisplay [FORMAT_MATCH] = {
		formatID: 'show',			
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
		reverseScore: 'show',
		isTeams: 'show',
		poolWinnersFirst: 'show',
		maxPoints: 'show',
		limitPoolMatches: 'show',
		checkInStaff: 'show',
		hideFinalResults: 'show',
		numSubMatches: 'show',
		subMatchMode: 'show',
		timeLimit: 'show',
		requireSignOff: 'show',
		maxPointSpread: 'show'
	};
	
	// Solo
	fieldsToDisplay [FORMAT_SOLO] = {
		formatD: 'show',			 
		rankingID: 'refresh',
		baseValue: 'show',
		isCuttingQual: 'show',
		reverseScore: 'show',
		isPrivate: 'show',
		isTeams: 'show',
		hideFinalResults: 'show'
	};
	
	// Composite
	fieldsToDisplay [FORMAT_COMPOSITE] = {
		formatD: 'show',			 
		rankingID: 'refresh',
		baseValue: 'show',
		hideFinalResults: 'show'
	};

	function toggleTournamentEntryDiv(){
		var divID = $(this).attr('Id');
		if(typeof divID !== 'string'){ return; }
		
		
		var divName = divID.substring(0,divID.lastIndexOf("_"));
		if(divName == 'formatID'){ return; }

		switch(fieldsToDisplay[+formatID][divName]){
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
	if(fieldsToDisplay[formatID]['maxDoubles'] == 'show'){
		edit_doubleType(tournamentID);
	}

// Team Modes
	if($('#isTeams_select'+tournamentID).val() > 0){
		$('#teamLogic_div'+tournamentID).show();
	} else {
		$('#teamLogic_div'+tournamentID).hide();
	}
	
}

/**********************************************************************/

function enableTournamentButton(tournamentID){

	var formatID = document.getElementById('formatID_select'+tournamentID).value;
	var button = document.getElementById('editTournamentButton'+tournamentID);
	var warrningMessages = [];

	if(formatID.length == 0){
		warrningMessages.push('No format selected');
	}

// Check modes related to fighting matches

	if(formatID == FORMAT_MATCH){
		doubleID = document.getElementById('doubleID_select'+tournamentID).value;
		if(doubleID.length == 0){
			warrningMessages.push('Please select Double/Afterblow Type');
		}

		if(doubleID == 3){

			netScoreMode = document.getElementById('notNetScore_select'+tournamentID).value;

			if(netScoreMode.length == 0){
				warrningMessages.push('Please select Net Score preference');
			}

			$("#overrideDoubles_div"+tournamentID).show();

			if($("#overrideDoubles_select"+tournamentID).val() != 0){
				$("#maxDoubles_div"+tournamentID).show();
			}

		} else {

			$("#overrideDoubles_div"+tournamentID).hide();
			
		}
	}

// Check modes relating to score/rankings

	if(formatID == FORMAT_MATCH || formatID == FORMAT_SOLO || formatID == FORMAT_COMPOSITE){
		rankingID = document.getElementById('rankingID_select'+tournamentID).value;
		if(rankingID.length == 0){
			warrningMessages.push('Please select Ranking Type');
		}
	}

	if(formatID == FORMAT_SOLO || formatID == FORMAT_COMPOSITE){
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

// Team Modes
	if($('#isTeams_select'+tournamentID).val() > 0){
		$('#teamLogic_div'+tournamentID).show();
	} else {
		$('#teamLogic_div'+tournamentID).hide();
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

function edit_formatType(tournamentID){

	formatID = document.getElementById('formatID_select'+tournamentID).value;
	
	toggleTournamentEditingFields(tournamentID, formatID);

	
	if(formatID == FORMAT_RESULTS){
		document.getElementById('editTournamentButton'+tournamentID).disabled = false;
		return;
	}
	

	var query = "mode=getRankingTypes&formatID="+formatID;

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

				for(var i in rankingTypes){
					var option = document.createElement('option');
					option.value = rankingTypes[i]['tournamentRankingID'];
					option.innerHTML = rankingTypes[i]['name'];
					select.appendChild(option);
				}

				enableTournamentButton(tournamentID);
			}
		}
	}
}