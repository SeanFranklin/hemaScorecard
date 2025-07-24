
/******************************************************************************/

const VIDEO_STREAM_MATCH    = 1;
const VIDEO_STREAM_LOCATION = 2;
const VIDEO_STREAM_VIRTUAL  = 3;

const VIDEO_SOURCE_UNKNOWN 		= 0;
const VIDEO_SOURCE_YOUTUBE 		= 1;
const VIDEO_SOURCE_NONE   		= 2;
const VIDEO_SOURCE_GOOGLE_DRIVE = 3;

var streamMatchInfo = {
	'lastExch': -1, 'matchTime': 0
};

/******************************************************************************/

function getStreamMatchInfo(){

	var streamMode = document.getElementById('stream-mode').value;
	var videoSource = document.getElementById('stream-video-source').value;

	if(streamMode == VIDEO_STREAM_LOCATION){
		var identifier = document.getElementById('stream-locationID').value;
	} else {
		var identifier = document.getElementById('stream-matchID').value;
	}

	if(identifier == 0){
		return;
	}

	if(streamMode == VIDEO_STREAM_VIRTUAL){
		var synchTime = document.getElementById('stream-synch-time').value;
		var synchTime2 = document.getElementById('stream-synch-time-2').value;;
	} else {
		var synchTime = 0;
		var synchTime2 = 0;
	}

	videoTime = 0;
	if(videoSource == VIDEO_SOURCE_YOUTUBE){
  
		videoTime = Math.floor(player.getCurrentTime());
    
	} else if(videoSource == VIDEO_SOURCE_GOOGLE_DRIVE) {
  
		videoTime = 0;
    
	} else if(videoSource == VIDEO_SOURCE_NONE){

		var videoTimeElement = document.getElementById("stream-video-time");

		if(document.body.contains(videoTimeElement) == true){
			videoTime = document.getElementById('stream-video-time').value;
		}

	}

	var query = "mode=getStreamOverlayInfo";
	query = query + "&streamMode="+streamMode;
	query = query + "&identifier="+identifier;
	query = query + "&lastExchange="+streamMatchInfo.lastExch;
	query = query + "&videoTime="+videoTime
	query = query + "&synchTime="+synchTime;
	query = query + "&synchTime2="+synchTime2;

	var xhr = new XMLHttpRequest();
	xhr.open("GET", "/includes/functions/AJAX.php?"+query, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send();

	xhr.onreadystatechange = function (){
		if(this.readyState == 4 && this.status == 200){

			if(this.responseText.length > 1){
				//console.log(this.responseText);
				matchInfo= JSON.parse(this.responseText);
				updateStreamOverlay(matchInfo);
			}
		}
	}
}

/******************************************************************************/

function updateStreamOverlay(matchInfo){

// Match Info
	var streamMode = document.getElementById('stream-mode').value;

	document.getElementById('timeDiv').innerHTML = secondsToDisplay(matchInfo['matchTime']);
	streamMatchInfo.matchTime  = matchInfo['matchTime'];

	var opactity = 1 - document.getElementById('stream-overlay-transparency').value/100;
	$(".overlay-container").css("opacity", opactity);

	if(streamMatchInfo.lastExch == matchInfo['lastExchange']){
		return;
	}

	streamMatchInfo.lastExch = matchInfo['lastExchange'];

	document.getElementById('tournamentName').innerHTML = matchInfo['tournamentName'];
	document.getElementById('matchName').innerHTML = matchInfo['matchName'];
	document.getElementById('doublesDiv').innerHTML = matchInfo['doubles']+" Doubles";

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
	document.getElementById('color1Div').style.color = matchInfo['color1Contrast'];

	document.getElementById('fighter2Name').innerHTML = matchInfo['fighter2Name'];
	document.getElementById('fighter2School').innerHTML = matchInfo['fighter2School'];
	document.getElementById('fighter2Score').innerHTML = matchInfo['fighter2Score'];
	document.getElementById('color2Div').style.background = matchInfo['color2Code'];
	document.getElementById('color2Div').style.color = matchInfo['color2Contrast'];

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

function updateStream(){

	getStreamMatchInfo();
}

/******************************************************************************/

function openVideoWindow(identifier, streamMode){

	if(streamMode == VIDEO_STREAM_LOCATION){
		locationID = identifier;
		matchID    = 0;
	} else {
		locationID = 0;
		matchID    = identifier;
	}

	updateStreamSession(streamMode ,matchID, locationID);

	streamMatchInfo.lastExch  = -1;
	streamMatchInfo.matchTime =  0;

	window.open('videoWatchWindow.php?l='+locationID,'streamWindow','toolbar=0,location=0,menubar=0');
}

/******************************************************************************/

function updateStreamSession(mode, matchID, locationID){
// Only certain values will be accepted by the server

    var query = "mode=updateStream";
    query = query + "&streamMode="+mode.toString();
    query = query + "&streamMatchID="+matchID.toString();
    query = query + "&streamLocationID="+locationID.toString();

    var xhr = new XMLHttpRequest();
    xhr.open("POST", AJAX_LOCATION+"?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){

            if(this.responseText.length > 1){
                //console.log(this.responseText);
                // Could read the success/failure message here.
            }
        }
    };

}

/******************************************************************************/

function validateVideoLink(){

	var buttons = document.getElementsByClassName('videoSubmitButton');

	var url = document.getElementById('updateVideoSource[sourceLink]').value;

	if(    url.startsWith("https://www.youtube.com")
		|| url.startsWith("https://youtu.be")
		|| url.startsWith("https://drive/google.com/file")
		|| url == ''){
		buttons[1].disabled = false;
		buttons[0].disabled = false;
	} else {
		buttons[1].disabled = true;
		buttons[0].disabled = true;
	}

}

/******************************************************************************/

function updateStreamMatchTime(){

	var isActive = document.getElementById('stream-run-match').checked;

	if(isActive == true){
		var time = parseFloat(document.getElementById('stream-video-time').value);
		time += 0.1;
		time = Math.round(time * 10) / 10

		document.getElementById('stream-video-time').value = time;
	}
}

/******************************************************************************/
