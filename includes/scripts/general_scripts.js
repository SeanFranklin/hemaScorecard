

// CONSTANT DECLARATIONS ///////////////////////////////////////
const AJAX_LOCATION = "/includes/functions/AJAX.php";

// Tournament Formats
const FORMAT_RESULTS    = 1;
const FORMAT_MATCH      = 2;
const FORMAT_SOLO       = 3;
const FORMAT_COMPOSITE  = 4;

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

/************************************************************************************/

function show(text){
// Alias of console.log() used to maintain symetry with data dump function from php
    console.log(text);
}

/************************************************************************************/

function rankingDescriptionToggle(rankingID){
    $(".rankingDescription").hide();
    var divName = "rankingID"+rankingID; 
    $("#"+divName).show();
}

/************************************************************************************/

function autoRefresh(timeInterval){
// Automatically refreshes a page for a given time interval.
// timeInterval is in msec
	
	if(timeInterval == 0){ return; }
	var refreshPeriod = timeInterval * 1000; // seconds
	
	var intervalID = window.setInterval(function(){ a(); }, refreshPeriod);
	
	function a(){ 
		location.reload();
	}
}


/**********************************************************************/

function openModal(modalName){
	$("#"+modalName).foundation("open");
}

/**********************************************************************/

function safeReload(){
	location.reload();
}

/**********************************************************************/

$( "#createNewEventToggleButton" ).click(function() {
  $( "#createNewEventField" ).slideToggle( "slow", function() {
    // Animation complete.
  });
});

