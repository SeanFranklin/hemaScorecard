
/**********************************************************************/

function statsUpdateTournamentTimeCalcAll(tournamentIDs){

	for(var i in tournamentIDs){
		statsUpdateTournamentTimeCalc(tournamentIDs[i]);
	}
}

/**********************************************************************/

function statsUpdateTournamentTimeCalc(tournamentID){

	var errorString = "";

	var numFighters = parseInt(document.getElementById('num-fighters-'+tournamentID).value);
	var poolSize = parseInt(document.getElementById('pool-size-'+tournamentID).value);
	var timePerMatch = parseInt(document.getElementById('time-per-match').value);
	var timeBetweenPools = parseInt(document.getElementById('time-between-pools').value);
	var numRings = parseInt(document.getElementById('num-rings-'+tournamentID).value);
	if(numRings < 1 || isNaN(numRings)){
		numRings = 1;
	}

	if(poolSize < 2){
		errorString = errorString + "<li>Pool size of 1 is not a valid number</li>";
	}

// Number of pools
	var numPools = Math.floor(numFighters/poolSize);
	var oversizePools = numFighters % poolSize;
	var normalPools = numPools - oversizePools;

	if(oversizePools > numPools){
		errorString = errorString + "<li>You can not make pools of "+poolSize+" (+1) with "+numFighters+" fighters</li>";
		numPools = NaN;
		normalPools = NaN;
		oversizePools = NaN;
	}

	if(isNaN(numPools) == false){
		document.getElementById('num-pools-'+tournamentID).innerHTML = numPools;
	} else {
		document.getElementById('num-pools-'+tournamentID).innerHTML = "-";
	}

// Total number of fights

	normalFights = normalPools * numFightsPerPool(poolSize);
	oversizeFights = oversizePools * numFightsPerPool(poolSize+1);
	totalFights = normalFights + oversizeFights;

	if(isNaN(totalFights) == false){
		document.getElementById('num-fights-'+tournamentID).innerHTML = totalFights;
	} else {
		document.getElementById('num-fights-'+tournamentID).innerHTML = "-";
	}
	
// Longest concurent fights in a pool
	
	if(numRings > numPools){
		numRings = numPools;
	}
	
	// Assume the worst case scenario of a ring that is the longest possible number 
	consecutivePoolsBase = Math.floor(numPools / numRings);
	ringsWithExtraPool = numPools % numRings;
	ringsWithoutExtraPool = numRings - ringsWithExtraPool;
	consecutivePoolsLong = consecutivePoolsBase;
	if(ringsWithExtraPool != 0){
		consecutivePoolsLong = consecutivePoolsBase + 1;
	}

	consecutivePoolsOversize = Math.ceil(oversizePools/numRings);
	consecutivePoolsNormal = consecutivePoolsLong - consecutivePoolsOversize; // Assume worst case scenario
	ringsWithBasePools = numRings - ringsWithExtraPool;
	numRingsNeedingExtraOversizePool = oversizePools % numRings;

	if(numRingsNeedingExtraOversizePool != 0 && numRingsNeedingExtraOversizePool <= ringsWithoutExtraPool && ringsWithExtraPool != 0){
		consecutivePoolsOversize--;
		consecutivePoolsNormal++;
	}
	
	totalConsecutivePools = consecutivePoolsOversize + consecutivePoolsNormal;
	normalPoolFights = consecutivePoolsNormal * numFightsPerPool(poolSize);
	oversizePoolFights = consecutivePoolsOversize * numFightsPerPool(poolSize+1);
	consecutivePoolFights = normalPoolFights + oversizePoolFights;

// Total time
	totalTime = consecutivePoolFights * timePerMatch;  // in seconds
	totalTime += (totalConsecutivePools - 1) * timeBetweenPools;
	totalTime = totalTime/(60 * 60);
	totalTime = Math.round(totalTime * 10)/10;

	if(isNaN(totalTime) == false){
		document.getElementById('total-time-'+tournamentID).innerHTML = totalTime;
	} else {
		document.getElementById('total-time-'+tournamentID).innerHTML = "-";
	}

// Update errors
	if(errorString.length > 1){
		errorString = "<b>Error</b>"+errorString;
		document.getElementById('time-calculation-error').innerHTML = errorString;
	} else {
		document.getElementById('time-calculation-error').innerHTML = null;
	}

}

/**********************************************************************/

function numFightsPerPool(size){
	return 0.5 * size * (size - 1);
}

/**********************************************************************/

function matchLengthEventSelect(index){

	eventID = document.getElementById('match-length-event-'+index).value;

	var query = "mode=getEventTournaments&eventID="+eventID;

	var xhr = new XMLHttpRequest();
	xhr.open("GET", AJAX_LOCATION+"?"+query, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send();

	xhr.onreadystatechange = function (){
		if(this.readyState == 4 && this.status == 200){
			if(this.responseText.length > 1){
				
				tournamentList = JSON.parse(this.responseText);

				select = document.getElementById('match-length-tournament-'+index);
				select.length = 0;

				var option = document.createElement('option');
				option.value = 0;
				option.selected = true;
				select.appendChild(option);

				for(var i in tournamentList){
					var option = document.createElement('option');
					option.value = tournamentList[i]['tournamentID'];
					option.innerHTML = tournamentList[i]['name'];
					select.appendChild(option);
				}

			}
		}
	}
}

/**********************************************************************/

