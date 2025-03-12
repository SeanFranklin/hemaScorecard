
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

/******************************************************************************/
    tinymce.init({
        selector: 'textarea.tiny-mce',
        plugins: 'lists link anchor',
        toolbar_location: 'top',
        font_size_formats:'',
        line_height_formats: '',
        color_map: [],
        style_formats: [],
        menubar: '',
        link_context_toolbar: true,
        link_title: false,
        link_target_list: [
            { title: 'Same page', value: '_self' },
            { title: 'New page', value: '_blank' }
          ],
        content_style: 'h1{font-size: 2.3em;border-bottom:  1px solid black;margin-top: 1.0em;margin-bottom: 0.5em;} '+
            'h2{ font-size: 1.9em; color: #1779ba; margin-top: 1.0em; margin-bottom: 0.0em;} '+
            'h3{ font-size: 1.4em; color: #F08A24; margin-top: 1.0em; margin-bottom: 0.0em;} '+
            'h4{ font-size: 1.2em; margin-top: 0.7em; margin-bottom: 0.2em;} '+
            'p + ul { margin-top: -10px;} '+
            'p + ol { margin-top: -10px;} '+
            'p, h1, h2, h3, h4, h5 {font-weight:normal; font-family: "Chivo", sans-serif;} ',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | numlist bullist | anchor link | hr indent outdent | removeformat ',
    });
/******************************************************************************/

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

/******************************************************************************/

function toggleClass(className){
    $('.'+className).toggle();
}

/******************************************************************************/

function show(text){
// Alias of console.log() used to maintain symetry with data dump function from php
    console.log(text);
}

/******************************************************************************/

function rankingDescriptionToggle(rankingID){
    $(".rankingDescription").hide();
    var divName = "rankingID"+rankingID;
    $("#"+divName).show();
}

/******************************************************************************/

function showForOption(selectElement, value, classToToggle){

    var formValue = selectElement.value;

    if(formValue == value){
        $("."+classToToggle).show();
    } else {
        $("."+classToToggle).hide();
    }
}

/******************************************************************************/

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


/******************************************************************************/

function openModal(modalName){
	$("#"+modalName).foundation("open");
}

/******************************************************************************/

function safeReload(){
	location.reload();
}

/******************************************************************************/

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

/******************************************************************************/

function submitForm(formID, formName, directMode = false){

    if(directMode == false){
        form = document.getElementById(formID);
    } else {
        form = formID;
    }

    var classList = form.getElementsByClassName('input-datalist');

    for(var i = 0; i < classList.length; i++){

        var inputElement = classList[i];
        var datalist = document.getElementById('staff-select-datalist').options;

        for(var j = 0; j < datalist.length; j++){
            
            if(datalist[j].label === inputElement.value){
                
                var formNameInput = document.createElement('input');
                formNameInput.type = 'hidden';
                formNameInput.name = inputElement.dataset.name;
                formNameInput.value = datalist[j].dataset.value;
                form.appendChild(formNameInput);
            }
            
        }
    }

    var formNameInput = document.createElement('input');
    formNameInput.type = 'hidden';
    formNameInput.name = 'formName';
    formNameInput.value = formName;
    form.appendChild(formNameInput);

    form.submit();
}

/******************************************************************************/

function toggleWithButton(className, onStatus){

    if(onStatus == true){
        $("."+className).show();
        $("."+className+'-on-button').hide();
    } else {
        $("."+className).hide();
        $("."+className+'-on-button').show();
    }

}

/******************************************************************************/

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

/******************************************************************************/

function submit_updateBracketRings(){

    form = document.getElementById('bracketForm');

    var formNameInput = document.createElement('input');
    formNameInput.type = 'hidden';
    formNameInput.name = 'locationID';
    formNameInput.value = $('#bracketLocationID').val();
    form.appendChild(formNameInput);

    submitForm('bracketForm','assignMatchesToLocations');
}

/******************************************************************************/

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

/******************************************************************************/

$( "#createNewEventToggleButton" ).click(function() {
  $( "#createNewEventField" ).slideToggle( "slow", function() {
    // Animation complete.
  });
});

/******************************************************************************/

function changeEventJs(eventID){

    var form = document.createElement("form");
    form.setAttribute("method", "post");
    document.body.appendChild(form);

    var changeEventToField = document.createElement("input");
    changeEventToField.setAttribute("type", "hidden");
    changeEventToField.setAttribute("name", "changeEventTo");
    changeEventToField.setAttribute("value", eventID);
    form.appendChild(changeEventToField);

    submitForm(form, 'selectEvent', true);

}

/******************************************************************************/

function changeTournamentJs(tournamentID, landingPage){

    var form = document.createElement("form");
    form.setAttribute("method", "post");
    document.body.appendChild(form);

    var tournamentIDField = document.createElement("input");
    tournamentIDField.setAttribute("type", "hidden");
    tournamentIDField.setAttribute("name", "newTournament");
    tournamentIDField.setAttribute("value", tournamentID);
    form.appendChild(tournamentIDField);

    if(landingPage != ''){
        var landingPageField = document.createElement("input");
        landingPageField.setAttribute("type", "hidden");
        landingPageField.setAttribute("name", "newPage");
        landingPageField.setAttribute("value", landingPage);
        form.appendChild(landingPageField);
    }

    submitForm(form, 'changeTournament', true);

}

/******************************************************************************/

jQuery(".edit-staff-list").change(function(event){

    var formData = [];
    formData['functionName'] = 'logisticsStaffFromRoster';

    formData['rosterID'] = $(event.target).attr("data-rosterID");
    var str = "#editStaffList-"+formData['rosterID'];

    formData['isStaff'] = ($(str+"-isStaff").prop('checked') === true ? 1 : 0);
    formData['staffCompetency'] = $(str+"-staffCompetency").val();
    formData['staffHoursTarget'] = $(str+"-staffHoursTarget").val();
    formData['eventID'] = $("#eventID").val();

    postForm(formData);

});

/******************************************************************************/

function postForm(formData){
    var query = "mode=postForm";

    for (var key in formData) {
        query =  query + "&" + key + "=" + formData[key];
    }


    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/includes/functions/AJAX.php?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){

            if(this.responseText.length > 1){
                //console.log(this.responseText);

            }
        }
    }
}

/******************************************************************************/

function secondsToMinAndSec(time, displayNegative = false){

    var neg = '';

    if(time < 0){
        if(displayNegative == false){
            time = 0;
        } else {
            time = Math.abs(time);
            neg = '-';
        }
    }

    minutes = Math.floor(time/60);
    seconds = time - (minutes * 60);

    if(seconds < 10){
        seconds = "0"+seconds.toString();
    }

    str = neg+minutes.toString()+":"+seconds.toString();

    return (str);
}

/******************************************************************************/

function wysiwygInit(textareaID){

    if(textareaID.length == 0){
        return;
    }

    var textAreaElement = document.getElementById(textareaID);

    if(textAreaElement.classList.contains('tiny-mce')){
        // has already been inited. Return.
        return;
    }

    textAreaElement.classList.add("tiny-mce");

    var selectorText = 'textarea#' + textareaID;

    tinymce.init({
        selector: selectorText,
        plugins: 'lists link anchor',
        toolbar_location: 'top',
        font_size_formats:'',
        line_height_formats: '',
        color_map: [],
        style_formats: [],
        menubar: '',
        link_context_toolbar: true,
        link_title: false,
        link_target_list: [
            { title: 'Same page', value: '_self' },
            { title: 'New page', value: '_blank' }
          ],
        content_style: 'h1{font-size: 2.3em;border-bottom:  1px solid black;margin-top: 1.0em;margin-bottom: 0.5em;} '+
            'h2{ font-size: 1.9em; color: #1779ba; margin-top: 1.0em; margin-bottom: 0.0em;} '+
            'h3{ font-size: 1.4em; color: #F08A24; margin-top: 1.0em; margin-bottom: 0.0em;} '+
            'h4{ font-size: 1.2em; margin-top: 0.7em; margin-bottom: 0.2em;} '+
            'p + ul { margin-top: -10px;} '+
            'p + ol { margin-top: -10px;} '+
            'p, h1, h2, h3, h4, h5 {font-weight:normal; font-family: "Chivo", sans-serif;} ',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | numlist bullist | anchor link | hr indent outdent | removeformat ',
    });
}



/******************************************************************************/


function number_format (number, decimals, dec_point, thousands_sep) {
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

/******************************************************************************/

function switchActiveTab(tabNum, groupClass){

    var tabs = document.getElementsByClassName(groupClass+"-tab");

    for (let i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove("selected");
    }

    document.getElementById(groupClass+"-tab-"+tabNum).classList.add("selected");

    var bodies = document.getElementsByClassName(groupClass+"-body");

    for (let i = 0; i < bodies.length; i++) {
        bodies[i].classList.add("hidden");
    }

    document.getElementById(groupClass+"-body-"+tabNum).classList.remove("hidden");

}

/******************************************************************************/

/******************************************************************************/

function validateNameSelection(name, num){

    var dataId = name+"-"+num;
    var inputElement = document.querySelectorAll("[data-id='"+dataId+"']")[0];
    var datalist = document.getElementById(name+'-datalist').options;

    var valid = false;


    if(inputElement.value.trim() == ""){
        inputElement.value = "";
        valid = false;

    } else {
        for(var j = 0; j < datalist.length; j++){

            if(datalist[j].label === inputElement.value){

                valid = true;
            }

        }
    }


    if(valid == true){
        document.getElementById('submit-name-button').classList.remove("secondary");
        document.getElementById('submit-name-button').classList.remove("hollow");
        document.getElementById('submit-name-is-valid').value = 1;
    } else {
        document.getElementById('submit-name-button').classList.add("secondary");
        document.getElementById('submit-name-button').classList.add("hollow");
        document.getElementById('submit-name-is-valid').value = 0;
    }


}

/******************************************************************************/

function submitNameForm(formID, formName, directMode = false){

    if(directMode == false){
        form = document.getElementById(formID);
    } else {
        form = formID;
    }

    var classList = form.getElementsByClassName('input-datalist');

    var isValid = document.getElementById('submit-name-is-valid').value;
    if(isValid == 0){
        return;
    }

    for(var i = 0; i < classList.length; i++){

        var inputElement = classList[i];
        var datalist = document.getElementById('event-roster-datalist').options;

        for(var j = 0; j < datalist.length; j++){

            if(datalist[j].label === inputElement.value){
                var formNameInput = document.createElement('input');
                formNameInput.type = 'hidden';
                formNameInput.name = inputElement.dataset.name;
                formNameInput.value = datalist[j].dataset.value;
                form.appendChild(formNameInput);
            }

        }
    }

    var formNameInput = document.createElement('input');
    formNameInput.type = 'hidden';
    formNameInput.name = 'formName';
    formNameInput.value = formName;
    form.appendChild(formNameInput);

    form.submit();
}

