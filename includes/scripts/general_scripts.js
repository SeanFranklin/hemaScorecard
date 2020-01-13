

// CONSTANT DECLARATIONS ///////////////////////////////////////
const AJAX_LOCATION = "/includes/functions/AJAX.php";

// Tournament Formats
const FORMAT_RESULTS    = 1;
const FORMAT_MATCH      = 2;
const FORMAT_SOLO       = 3;
const FORMAT_META       = 4;

// Arrows
const TIE_TOP     = '↰';
const TIE_MIDDLE  = '|';
const TIE_BOTTOM  = '↲';
const TIE_NO      = '&nbsp;';

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

function toggleClass(className){
    $('.'+className).toggle();
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

function updateSession(index, value){
// Only certain values will be accepted by the server

    var query = "mode=updateSession";
    query = query + "&index="+index.toString();
    query = query + "&value="+value.toString();
    var xhr = new XMLHttpRequest();
    xhr.open("POST", AJAX_LOCATION+"?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){
            
            if(this.responseText.length > 1){ 
                // Could read the success/failure message here.
            }
        }
    };

}

/**********************************************************************/

function submitForm(formID, formName){
   
    form = document.getElementById(formID);

    var formNameInput = document.createElement('input');
    formNameInput.type = 'hidden';
    formNameInput.name = 'formName';
    formNameInput.value = formName;
    form.appendChild(formNameInput);
    
    form.submit();
}

/**********************************************************************/

function toggleWithButton(className, onStatus){

    if(onStatus == true){
        $("."+className).show();
        $("."+className+'-on-button').hide();
    } else {
        $("."+className).hide();
        $("."+className+'-on-button').show();
    }

}

/**********************************************************************/

function togglePolarGeneration(selectDiv){

    if($(selectDiv).val() == 'polar'){
        $(".polar-disables").css("text-decoration","line-through");
        $(".polar-disables").prop("checked",false);
        $(".polar-disables").prop("disabled",true);
    } else {
        $(".polar-disables").css("text-decoration","");
        $(".polar-disables").prop("disabled",false);
    }
}

/**********************************************************************/

function submit_updateBracketRings(){

    form = document.getElementById('bracketForm');

    var formNameInput = document.createElement('input');
    formNameInput.type = 'hidden';
    formNameInput.name = 'locationID';
    formNameInput.value = $('#bracketLocationID').val();
    form.appendChild(formNameInput);
    
    submitForm('bracketForm','assignMatchesToLocations');
}

/**********************************************************************/

function placingsDeclareTie(place1, maxPlace){

    if(place1 == maxPlace){
        alert('Please only click on the top match of a tie');
        return;
    }

    var place2 = place1 + 1;

    var state1 = $.trim($("#declare-tie-"+place1).html());
    var state2 = $.trim($("#declare-tie-"+place2).html());
    var set1 = '';
    var set2 = '';

    switch(state1){
        case TIE_NO: 
            set1 = TIE_TOP;

            if(state2 == TIE_NO){
                set2 = TIE_BOTTOM;
            } else if(state2 == TIE_TOP) {
                set2 = TIE_MIDDLE;
            }
            break;

        case TIE_TOP:

            set1 = TIE_NO;

            if(state2 == TIE_BOTTOM){
                set2 = TIE_NO;
            } else if(state2 == TIE_MIDDLE){
                set2 = TIE_TOP;
            }

            break;

        case TIE_BOTTOM:
            set1 = TIE_MIDDLE;
            if(state2 == TIE_NO){
                set2 = TIE_BOTTOM;
            } else if(state2 == TIE_TOP){
                set2 = TIE_MIDDLE;
            }
            break;

        case TIE_MIDDLE:
            set1 = TIE_NO;
           
            if(state2 == TIE_BOTTOM){
                set2 = TIE_NO;
            } else if(state2 == TIE_MIDDLE){
                set2 = TIE_TOP;
            }

            var place0 = place1 - 1;
            var set0 = '';
            var state0 = $.trim($("#declare-tie-"+place0).html());

            if(state0 == TIE_TOP){
                set0 = TIE_NO;
            } else if(state0 == TIE_MIDDLE){
                set0 = TIE_BOTTOM;
            }
            if(set0 != ''){
                $("#declare-tie-"+place0).html(set0);
            }
            break;

        default:

    }

    if(set1 != ''){
        $("#declare-tie-"+place1).html(set1);
    }
    if(set2 != ''){
        $("#declare-tie-"+place2).html(set2);
    }

    var startOfTie = 0;
    var tieSize = 0;
    var endOfTie = 0;
    for(i = 1; i <= maxPlace; i++){
        state =  $.trim($("#declare-tie-"+i).html());

    // Write data
        if(state == TIE_NO){
            $("#place-label-"+i).removeClass('blue-text');
            $("#place-label-"+i).html(i);
             $("#place-value-"+i).val(i);
            $("#place-tie-"+i).val(0);
        } else {
            if(state == TIE_TOP){
                startOfTie = i;
                tieSize++;
            }
            if(state == TIE_MIDDLE){
                tieSize++;
            }
            if(state == TIE_BOTTOM || i == maxPlace){
                tieSize++;
                endOfTie = i;
            }

            $("#place-label-"+i).addClass('blue-text');
            $("#place-label-"+i).html(startOfTie);
            $("#place-value-"+i).val(startOfTie);
  
        }

    // Detect end of a tie
        if(endOfTie != 0){
           
            for(j = startOfTie;j <= endOfTie; j++){
                $("#place-tie-"+j).val(tieSize);
                $("#place-tie-start-"+j).val(tieSize);
                $("#place-tie-end-"+j).val(tieSize);
            }
            tieSize = 0;
            endOfTie = 0;
            startOfTie = 0;
        }

    }

}

/**********************************************************************/


$( "#createNewEventToggleButton" ).click(function() {
  $( "#createNewEventField" ).slideToggle( "slow", function() {
    // Animation complete.
  });
});

