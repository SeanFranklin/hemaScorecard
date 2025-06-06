/*******************************************************************************

		MISC SCRIPTS

		Miscelaneous scripts which belong to a single page, but
		are not long enough to warrant the page having it's own script
		file.

*******************************************************************************/


/******************************************************************************/

function strikeOutDuplicateFighters(indexToClear, buttonToActivate){

	$('.combineFighersRow').addClass('strike-through');
	$('#'+indexToClear).removeClass('strike-through');
	$('#'+buttonToActivate).removeAttr("disabled");
}


/******************************************************************************/

function toggleRadio(radioID){
// Used in cutQual Tournament

	document.getElementById(radioID).checked = true;
}

/******************************************************************************/

function logInTypeToggle(selectElement){
// Toggles the event field on or off depending on the log in type.
// Certain types of log ins are tied to event, and others are system wide.
// Used in adminLogIn.php

	if(selectElement.value == 'logInStaff' || selectElement.value == 'logInOrganizer'){
		$('#logInEventListDiv').show();
		$('#logInUserNameDiv').hide();
		logInEventToggle();
	} else {
		$('#logInEventListDiv').hide();
		$('#logInUserNameDiv').show();
		$('#logInUserName').val('');
	}

	logInSubmitEnable();
}

/******************************************************************************/

function logInEventToggle(){
// Appends the event name to the user name field
// Helps facilitate password managers
// Used in adminLogIn.php

	eventName = $("#logInEventID option:selected").text().trim();

	eventName = eventName;
	$('#logInUserName').val(eventName);

	logInSubmitEnable();

}

/******************************************************************************/

function logInSubmitEnable(){
// Enables the login button only if the user has entered data in.
// Used in adminLogIn.php

	var userName = $('#logInUserName').val();

	if($('#logInUserName').val().length > 0){
		$("#logInSubmitButton").prop("disabled",false);
	} else {
		$("#logInSubmitButton").prop("disabled",true);
	}
}

$('#logInUserName').bind('input', function() {
    logInSubmitEnable();
});

/******************************************************************************/

function schoolInputPlaceholders(){
// Creates placeholder text for a form field, based on what is entered in
// another form field.

	document.getElementById('schoolShort').placeholder = schoolFull.value;

}

/******************************************************************************/

function hemaRatings_getByName(buttonID, name, systemRosterID){

	if(typeof HEMA_RATINGS_TOKEN === 'undefined'){
		alert("No Token");
		return;
	}

	$(buttonID).addClass("secondary");
	$(buttonID).removeClass("warning");

	name = encodeURIComponent(JSON.stringify(name));

	var path = "https://hemaranking.azurewebsites.net/api/OrganizerToolsApi/Search/?";
	path = path + "token=" + HEMA_RATINGS_TOKEN;
	path = path + "&fighterName=" + name;
	path = path + "&fbclid=" + HEMA_RATINGS_BY_NAME;

	/* Unused code to fetch fighter by ID
	var path = "http://hemaranking.azurewebsites.net/api/OrganizerToolsApi/GetById/?";
	path = path + "token=" + HEMA_RATINGS_TOKEN;
	path = path + "&id=6=";
	path = path + "&fbclid=" + HEMA_RATINGS_BY_ID;
	*/

	var xhr = new XMLHttpRequest();
	xhr.open("GET", path, true);
	xhr.send();

	xhr.onreadystatechange = function (){
		if(this.readyState == 4 && this.status == 200){
			if(this.responseText.length > 0){
				var data = JSON.parse(this.responseText);


				if(data.length == 0){

					$("#hema-ratings-unidentifed-warning").show();
					$('#unrated-row-'+systemRosterID).remove();

				} else {

					var divName = "#divFor-"+systemRosterID;
					$(divName).html("");
					var isFirst = true;

					data.forEach(function(fighterInfo){

	                    var str = "";

	                    if(isFirst){
	                    	isFirst = false;
	                    } else {
	                    	str = "<BR><BR>";
	                    }

	                    var str = str + "<span";
	                    if(name.toLowerCase() == fighterInfo['name'].toLowerCase()){
	                    	str = str + " class='red-text'";
	                    }
	                    str = str + ">";

	                    var hemaRatingsId = fighterInfo['id'];
	                    str = str + `<input type='checkbox' class='no-bottom'
	                    				name='hemaRatings[hemaRatingsIdFor][${systemRosterID}]'
	                    				value='${hemaRatingsId}'>`;

	                    str = str + "<strong>";

	                    str = str + fighterInfo['name'];
	                    str = str + "</strong><BR>";
	                    str = str + fighterInfo['clubName'];
	                    str = str + "<BR>";
	                    str = str + fighterInfo['nationality'];
	                    str = str + "</span>";

	                    $(divName).append(str);

	                });
				}

			} else {
				alert("!");
			}
		}
	};
}

/******************************************************************************/

function hemaRatings_getByNameAll(){

	if(typeof HEMA_RATINGS_TOKEN === 'undefined'){
		alert("No Token");
		return;
	}

	$(".hemaRatingsGetInfo").each(function(){
		//console.log(this);
		(this).onclick();
	});

}

/******************************************************************************/

function hemaRatings_getById(hemaRatingsID){

	if(typeof HEMA_RATINGS_TOKEN === 'undefined'){
		alert("No Token");
		return;
	}

	var path = "https://hemaranking.azurewebsites.net/api/OrganizerToolsApi/Search/?";
	path = path + "token=" + HEMA_RATINGS_TOKEN;
	path = path + "&id=6=" + hemaRatingsID;
	path = path + "&fbclid=" + HEMA_RATINGS_BY_NAME;


	var xhr = new XMLHttpRequest();
	xhr.open("GET", path, true);
	xhr.send();

	xhr.onreadystatechange = function (){
		if(this.readyState == 4 && this.status == 200){
			if(this.responseText.length > 0){
				var data = JSON.parse(this.responseText);


				if(data.length == 0){

					$("#hema-ratings-unidentifed-warning").show();
					$('#unrated-row-'+systemRosterID).remove();

				} else {


					var divName = "#divFor-"+systemRosterID;
					$(divName).html("");
					var isFirst = true;

					data.forEach(function(fighterInfo){

	                    var str = "";

	                    if(isFirst){
	                    	isFirst = false;
	                    } else {
	                    	str = "<BR><BR>";
	                    }

	                    var str = str + "<span";
	                    if(name.toLowerCase() == fighterInfo['name'].toLowerCase()){
	                    	str = str + " class='red-text'";
	                    }
	                    str = str + ">";

	                    var hemaRatingsId = fighterInfo['id'];
	                    str = str + `<input type='checkbox' class='no-bottom'
	                    				name='hemaRatings[hemaRatingsIdFor][${systemRosterID}]'
	                    				value='${hemaRatingsId}'>`;

	                    str = str + "<strong>";

	                    str = str + fighterInfo['name'];
	                    str = str + "</strong><BR>";
	                    str = str + fighterInfo['clubName'];
	                    str = str + "<BR>";
	                    str = str + fighterInfo['nationality'];
	                    str = str + "</span>";

	                    $(divName).append(str);

	                });
				}

			} else {
				alert("!");
			}
		}
	};
}

/******************************************************************************/

function eventStartDateUpdated(startDateStr){

    end = document.getElementById("event-end-date").value;

    startDateObj = new Date(startDateStr);
    endDateObj = new Date(document.getElementById("event-end-date").value);

    if(isNaN(endDateObj) || endDateObj < startDateObj){
        document.getElementById("event-end-date").value = startDateStr;
    }
}

/******************************************************************************/

function updateCutQualInfo(systemRosterID){

	var button =  document.getElementById('button-'+systemRosterID);

    button.classList.remove("hollow");
    operation = button.value;
    var qualID = document.getElementById('qualID-'+systemRosterID).value;

    var query = "mode=updateCuttingQual&systemRosterID="+systemRosterID;
    query = query + "&operation=" + button.value;
    query = query + "&qualID=" + qualID;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", AJAX_LOCATION+"?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){
            if(this.responseText.length > 1){ // If the fighter has already fought

            	var button =  document.getElementById('button-'+systemRosterID);
                var data = JSON.parse(this.responseText);

                button.classList.remove("alert");
                button.classList.remove("success");

                if(data['txt'] == 'Add'){
                	button.classList.add("success");
                	button.innerHTML = data['txt'];
                } else if (data['txt'] == 'Remove'){
                	button.innerHTML = data['txt'];
                	button.classList.add("alert");
                } else {
                	// Default color is ok for 'update'
                }

                document.getElementById('qualID-'+systemRosterID).value = data['qualID'];
                button.value = data['txt'];

                button.classList.add("hollow");
            }
        }
    };
}

/******************************************************************************/