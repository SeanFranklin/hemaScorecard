/******************************************************************************/

/******************************************************************************/

//getLivestreamMatchInfo.lastExch = 0;
getLivestreamMatchInfo.matchTime = 0;

function getLivestreamMatchInfo(){
	
	eventID = document.getElementById('eventID').value;
	
	var query = "mode=getLivestreamMatch&eventID="+eventID;
	query = query + "&lastExchange="+getLivestreamMatchInfo.lastExch;

	var xhr = new XMLHttpRequest();
	xhr.open("GET", "/v6/includes/functions/AJAX.php?"+query, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send();

	xhr.onreadystatechange = function (){
		if(this.readyState == 4 && this.status == 200){
			if(this.responseText.length > 1){
				//console.log(this.responseText);
				matchInfo= JSON.parse(this.responseText);
				updateOverlay(matchInfo);
				//console.log(getLivestreamMatchInfo.lastExch);
				
			}
		}
	}
}

/******************************************************************************/

function updateOverlay(matchInfo){

// Match Info
	document.getElementById('timeDiv').innerHTML = secondsToDisplay(matchInfo['matchTime']);
	getLivestreamMatchInfo.matchTime  = matchInfo['matchTime'];
	if(Object.keys(matchInfo).length <= 1){ return;}
	
	document.getElementById('tournamentName').innerHTML = matchInfo['tournamentName'];
	document.getElementById('matchName').innerHTML = matchInfo['matchName'];
	getLivestreamMatchInfo.lastExch = matchInfo['lastExchange'];
	document.getElementById('doublesDiv').innerHTML = matchInfo['doubles']+" Double Hits";
		

// If match is concluded
	if(matchInfo['endType'] == 'doubleOut'){
		document.getElementById('fighter1Name').style.textDecoration = 'line-through';
		document.getElementById('fighter2Name').style.textDecoration = 'line-through';
	} else if(matchInfo['winner'] == 1){
		document.getElementById('fighter1Name').style.textDecoration = 'underline';
		document.getElementById('fighter2Name').style.textDecoration = 'line-through';
	} else if(matchInfo['winner'] == 2){
		document.getElementById('fighter1Name').style.textDecoration = 'line-through';
		document.getElementById('fighter2Name').style.textDecoration = 'underline';
	} else {
		document.getElementById('fighter1Name').style.textDecoration = null;
		document.getElementById('fighter2Name').style.textDecoration = null;
	}
// Fighter Info
	document.getElementById('fighter1Name').innerHTML = matchInfo['fighter1Name'];
	document.getElementById('fighter1School').innerHTML = matchInfo['fighter1School'];
	document.getElementById('fighter1Score').innerHTML = matchInfo['fighter1Score'];
	document.getElementById('color1Div').style.background = matchInfo['color1Code'];
	
	document.getElementById('fighter2Name').innerHTML = matchInfo['fighter2Name'];
	document.getElementById('fighter2School').innerHTML = matchInfo['fighter2School'];
	document.getElementById('fighter2Score').innerHTML = matchInfo['fighter2Score'];
	document.getElementById('color2Div').style.background = matchInfo['color2Code'];
	

	
// Last Exchange
	exchName = getExchangeName( matchInfo['exchangeType'], matchInfo['points']);
	document.getElementById('exchangeType').innerHTML = exchName[0];
	document.getElementById('exchangePoints').innerHTML =  exchName[1];
	if(matchInfo['lastColor'] == 1){
		document.getElementById('lastExchange').style.background = matchInfo['color1Code'];
	} else if(matchInfo['lastColor'] == 2){
		document.getElementById('lastExchange').style.background = matchInfo['color2Code'];
	} else {
		document.getElementById('lastExchange').style.background = "#555";
	}
	
}

/******************************************************************************/

function getExchangeName(type, points){
	
	if(Math.abs(points) == 1){
		pts = points+" Point";
	} else {
		pts = points+" Points";
	}
	
	switch(type){
		case 'clean':
			return ['Clean Hit', pts];
		case 'afterblow':
			return ['Afterblow', pts];
		case 'penalty':
			return ['Penalty',pts];
		case 'noQuality':
			return ['No Quality','0 Points'];
		case 'noExchange':
			return ['No Exchange','No Score'];
		case 'double':
			return ['Double Hit','No Score'];
		default:
			return ['&nbsp;','&nbsp;'];
	}
	
	
}

/******************************************************************************/

function updateLivestream(){
	getLivestreamMatchInfo();
}

/******************************************************************************/
