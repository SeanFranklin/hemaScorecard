
AJAX_LOCATION = "/includes/functions/AJAX.php";

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

