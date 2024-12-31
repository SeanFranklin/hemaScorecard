
/**********************************************************************/

function statsUpdateTournamentTimeCalc(){

	var errorString = "";

	var numFighters = parseInt(document.getElementById('t-time-calc-num-fighters').value);
	var poolSize = parseInt(document.getElementById('t-time-calc-pool-size').value);
	var timePerMatch = parseInt(document.getElementById('time-per-match').value);
	var timeBetweenPools = parseInt(document.getElementById('time-between-pools').value);
	var numRings = parseInt(document.getElementById('t-time-calc-num-rings').value);
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
		document.getElementById('t-time-calc-num-pools').innerHTML = numPools;
	} else {
		document.getElementById('t-time-calc-num-pools').innerHTML = "-";
	}

// Total number of fights

	normalFights = normalPools * numFightsPerPool(poolSize);
	oversizeFights = oversizePools * numFightsPerPool(poolSize+1);
	totalFights = normalFights + oversizeFights;

	if(isNaN(totalFights) == false){
		document.getElementById('t-time-calc-num-fights').innerHTML = totalFights;
	} else {
		document.getElementById('t-time-calc-num-fights').innerHTML = "-";
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
		document.getElementById('t-time-calc-total-time').innerHTML = totalTime;
	} else {
		document.getElementById('t-time-calc-total-time').innerHTML = "-";
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

function calculateCuttingMats(){

	var groupIDs = document.getElementsByClassName('groupID');
	var tournamentID_old = 0;
	var matsInTournament = 0;
	var matsTotal = 0;

	for(var i = 0; i < groupIDs.length; i++){

		var groupID = groupIDs[i].value;

		var tournamentID = document.getElementById('tournamentID-for-'+groupID).value;

		if(tournamentID != tournamentID_old){
			if(tournamentID_old != 0){
				document.getElementById('num-mats-tournament-'+tournamentID_old).innerHTML = matsInTournament;
			}

			tournamentID_old = tournamentID;
			matsInTournament = 0;
		}


		var matsPer = document.getElementById('mats-per-'+groupID).value;
		var groupSize = document.getElementById('group-size-'+groupID).value;
		var numMats = matsPer * groupSize;
		document.getElementById('num-mats-'+groupID).innerHTML = numMats;

		matsInTournament += numMats;
		matsTotal += numMats;

	}

	if(tournamentID_old != 0){
		document.getElementById('num-mats-tournament-'+tournamentID_old).innerHTML = matsInTournament;
	}

	document.getElementById('num-mats-total-'+tournamentID_old).innerHTML = matsTotal;

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

function listSlider(baseID){

	var value = document.getElementById(baseID+'-slider').value;
	document.getElementById(baseID+'-count').innerHTML = value;
	
	for(var i = 0; i <= 300; i++){
		if(i <= value){
			$('#'+baseID+'-'+i).removeClass('hidden');
		} else {
			$('#'+baseID+'-'+i).addClass('hidden');
		}
		
	}

}

/**********************************************************************/

function getDataForYearType(year, id, color = '#D6E5FA', numToShow = 5, hidePlacing = false){


	if(typeof(id) == 'string'){
		getDataForYear(year, id, color, numToShow, hidePlacing);
	} else {

		for(var i = 0; i < id.length; i++){

			if(typeof(numToShow) == 'number'){
				var toShow = numToShow;
			} else {
				var toShow = numToShow[i];
			}

			getDataForYear(year, id[i], color, toShow, hidePlacing);
		}

	}


}

/**********************************************************************/

function getDataForYear(year, id, color = '#D6E5FA', numToShow = 5, hidePlacing = false){

    $i = 0;

    var query = "mode=getDataForYear&year="+year;
    query = query + "&dataType=" + id;

    var xhr = new XMLHttpRequest();
    xhr.open("GET", AJAX_LOCATION+"?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){
            if(this.responseText.length > 1){

            	//console.log(this.responseText);
                data = JSON.parse(this.responseText);


                var maxValue = 1;

                if(numToShow > data.length || numToShow == 0){
                    numToShow = data.length;
                }


                for(var i = 0; i < data.length; i++){

                    if(parseInt(data[i]['value']) > maxValue){
                        maxValue = data[i]['value'];
                    }

                    if(typeof data[i]['nameShort'] === 'undefined'){
                        data[i]['nameShort'] = data[i]['name'];
                    }

                }

                var table = document.getElementById(id+"-table");
                table.innerHTML = "";

                for(var i = 0; i < data.length; i++){

                	var col = 0;
                    var row = table.insertRow(i);
                    row.id = id + "-" + (i+1);

                    if(i >= numToShow){
                        row.classList.add("hidden");
                    }

                // Column for the rank
                    if(hidePlacing == false){
                    	var tdPlace = row.insertCell(col++);
	                    tdPlace.style.fontSize = "0.75em";
	                    tdPlace.style.width = "1px";
                    	tdPlace.innerHTML = (i+1);
                    }

                // Column for the name
                    var tdNameSmall = row.insertCell(col++);
                    tdNameSmall.innerHTML = data[i]['nameShort'];
                    tdNameSmall.classList.add("hide-for-small-only");

                    if(data[i]['nameShort'].length > 40){
                        tdNameSmall.style.maxWidth = "500px";
                        tdNameSmall.style.minWidth = "250px";
                    } else {
                        tdNameSmall.style.width = "0.1%";
                        tdNameSmall.style.whiteSpace = "nowrap";
                    }

                    var tdNameBig = row.insertCell(col++);
                    tdNameBig.innerHTML = data[i]['nameShort'];
                    tdNameBig.classList.add("show-for-small-only");

                // Column for the count
                    var tdValue = row.insertCell(col++);
                    tdValue.innerHTML = number_format(data[i]['value']);
                    tdValue.classList.add("text-right");
                    tdValue.style.width = "0.1%";
                    tdValue.style.whiteSpace = "nowrap";
                    tdValue.style.borderRight = "solid 1px black";

                // Column for the bar
                    var tdBar = row.insertCell(col);
                    var barWidth = 100 * (data[i]['value'] / maxValue);
                    tdBar.style.minWidth = '100px';
                    tdBar.style.padding = '0px';
                    tdBar.style.height = '1px';

                    var bar = document.createElement("div");
                    bar.style.width = barWidth + "%";
                    bar.style.display = 'inline-block';
                    bar.style.margin = '0px';
                    bar.style.padding = '0px';
                    bar.style.height = '100%';
                    bar.style.backgroundImage = 'linear-gradient(to right, white, ' + color + ')';
                    bar.innerHTML = "&nbsp;";
                    tdBar.appendChild(bar);

                    var slider = document.getElementById(id+"-slider");
                    slider.max = data.length;
                    slider.value = numToShow;

                    document.getElementById(id+"-count").innerHTML = numToShow;
                    document.getElementById(id+"-total").innerHTML = data.length;

                }



            }
        }
    }

}

/**********************************************************************/



/**********************************************************************/


