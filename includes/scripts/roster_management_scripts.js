
/************************************************************************************/

function toggleTableRow(divName, divName2 = null) {
    var x = document.getElementById(divName);

    if (x.style.display == '') {
        x.style.display = 'table-row';
    } else {
        x.style.display = '';
    }

    if(divName2 == null){return;}

    var x = document.getElementById(divName2);
    if (x.style.display == '') {
        x.style.display = 'table-row';
    } else {
        x.style.display = '';
    }

}

/************************************************************************************/

function toggleCheckbox(checkboxID, divID, dontCheck){
    checkbox = document.getElementById(checkboxID);

    if(checkbox.checked){
        checkbox.checked = false;
        divID.style.background = 'none';
    } else {
        checkbox.checked = true;
        divID.style.background = '#3adb76';
    }
    if(typeof dontCheck === 'undefined'){
        checkIfFought(checkbox);
    }
}

/******************************************************************************/

function changeParticipantOrdering(sortWhat,sortHow){

    // Create form
    var myForm = document.createElement("form");
    myForm.method = 'POST';

    // Form Name
    var formName = document.createElement('input');
    formName.type = 'hidden';
    formName.value = 'changeSortType';
    formName.name = 'formName';
    myForm.appendChild(formName);

    // What to sort
    var what = document.createElement('input');
    what.type = 'hidden';
    what.value = sortWhat;
    what.name = 'sortWhat';
    myForm.appendChild(what);

    // How to sort it
    var how = document.createElement('input');
    how.type = 'hidden';
    how.value = sortHow;
    how.name = 'sortHow';
    myForm.appendChild(how);

    document.getElementsByTagName('body')[0].appendChild(myForm);

    myForm.submit();

}

/******************************************************************************/

function goToPersonalSchedule(rosterID){

    // Create form
    var myForm = document.createElement("form");
    myForm.method = 'POST';

    // Form Name
    var formName = document.createElement('input');
    formName.type = 'hidden';
    formName.value = 'personalSchedule';
    formName.name = 'formName';
    myForm.appendChild(formName);

    // rosterID
    var what = document.createElement('input');
    what.type = 'hidden';
    what.value = rosterID;
    what.name = 'rosterID';
    myForm.appendChild(what);

    document.getElementsByTagName('body')[0].appendChild(myForm);

    myForm.submit();

}

/**********************************************************************/

function editParticipant(rosterID){
    var div = document.getElementById('editParticipantModal');
    checkIfFought.numConflictsChecked = 0;
    hasAlreadyFoughtWarning(false, div);

    if(rosterID == 0){
        $("#editParticipantModal").foundation("close");
        return;
    }

    $("#editParticipantModal").foundation("open");

    var eventID = document.getElementById('eventID').value;

    var query = "mode=fighterInfo&rosterID="+rosterID.toString();
    query = query + "&eventID=" + eventID.toString();
    var xhr = new XMLHttpRequest();
    xhr.open("POST", AJAX_LOCATION+"?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){
            if(this.responseText.length > 1){ // If the fighter has already fought
                var data = JSON.parse(this.responseText);
                fillInFields(data);
            }
        }
    };

// Populate fields of the form with data
    function fillInFields(data){
        var elementList = document.getElementById('editParticipantForm');
        for(var i =0; i < elementList.length; i++){
            elementList[i].checked = false;
        }
        psudoButtons = document.getElementById('editTournamentListDiv').getElementsByTagName('div');
        for(var i=0; i < psudoButtons.length; i++){
            psudoButtons[i].style.background = 'none';
        }

        document.getElementById('editRosterID').value = rosterID;
        document.getElementById('editFirstName').value = data.firstName;
        document.getElementById('editLastName').value = data.lastName;
        document.getElementById('editSchoolID').value = data.schoolID;
        document.getElementById('editFullName').innerHTML = data.firstName+" "+data.lastName;
        document.getElementById('rosterIDforDelete').name = "deleteFromEvent["+rosterID+"]";

        for(var tournamentID in data.tournamentIDs){
            document.getElementById('editTournamentID'+tournamentID).checked = true;
            document.getElementById('divForeditTournamentID'+tournamentID).style.background = '#3adb76';
        }

        divList = document.getElementsByClassName('tournamentSelectBox');

        for(var i=0; i < divList.length; i++){
            divList[i].style.color = null;
        }

    }

// Check if the fighter has already fought
    var query = "mode=hasFought";
    query = query + "&rosterID=" + rosterID.toString();
    query = query + "&eventID=" + document.getElementById('eventID').value;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", AJAX_LOCATION+"?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){
            text = document.getElementById('warnIfFought');
            if(this.responseText.length > 1){ // If the fighter has already fought
                text.innerHTML = "<div class='callout alert'><u>Note:</u> This participant has already started competing</div>";
            } else {
                text.innerHTML = null;
            }
        }
    };

}

/**********************************************************************/

function editSystemParticipant(systemRosterID){

    var div = document.getElementById('editSystemParticipantModal');

    if(systemRosterID == 0){
        document.getElementById('editSystemRosterID').value = systemRosterID;
        document.getElementById('displaySystemRosterID').value = systemRosterID;
        document.getElementById('editSystemFirstName').value = data.firstName;
        document.getElementById('editSystemLastName').value = data.lastName;
        document.getElementById('editSystemHemaRatingsID').value = data.HemaRatingsID;
        document.getElementById('editSystemSchoolID').value = data.schoolID;
        return;
    }

    var query = "mode=fighterSystemInfo&systemRosterID="+systemRosterID.toString();
    var xhr = new XMLHttpRequest();
    xhr.open("POST", AJAX_LOCATION+"?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){
            if(this.responseText.length > 1){ // If the fighter has already fought

                var data = JSON.parse(this.responseText);

                document.getElementById('editSystemRosterID').value = systemRosterID;
                document.getElementById('displaySystemRosterID').value = systemRosterID;
                document.getElementById('editSystemFirstName').value = data.firstName;
                document.getElementById('editSystemLastName').value = data.lastName;
                document.getElementById('editSystemHemaRatingsID').value = data.HemaRatingsID;
                document.getElementById('editSystemSchoolID').value = data.schoolID;
            }
        }
    };

}

/******************************************************************************/

var SeedingData = {};

google.charts.load('current', {'packages':['corechart']});

/******************************************************************************/

function divSeedingPickDiv(){

    var divID = parseInt(document.getElementById('split-div-id').value);

    var query = "mode=divisionSeedingInfo&divisionID="+divID.toString();
    var xhr = new XMLHttpRequest();
    xhr.open("POST", AJAX_LOCATION+"?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){
            if(this.responseText.length > 1){ // If the fighter has already fought


                var data = JSON.parse(this.responseText);
                SeedingData = {tournaments:{}};
                SeedingData.tournaments.inDiv = [];


                var selectElement = document.getElementById("split-donor-id");
                selectElement.innerHTML = "";

                var option = document.createElement("option");
                option.text = "-- select --";
                option.value = -1;
                selectElement.add(option);


                for(var i = 0; i < data.length; i++){

                    var option = document.createElement("option");
                    option.text = data[i].name;
                    option.value = i;
                    selectElement.add(option);

                    SeedingData.tournaments.inDiv.push(data[i]);

                }

                document.getElementById('donor-info-div').innerHTML = '';
                jQuery(".ratings-form-submit").prop("disabled", true);
                document.getElementById('ratings-chart').innerHTML = "<div class='callout primary text-center'>Select donor tournament.</div>";

            }
        }
    };

}

/******************************************************************************/

function divSeedingPickDonor(selectData){


    var selectIndex = selectData.value;

    SeedingData.tournaments.donor = {};
    SeedingData.tournaments.destinations = [];

    if(selectIndex < 0){
        document.getElementById('split-items-table').innerHTML = "";
        jQuery(".ratings-form-submit").prop("disabled", true);
        document.getElementById('ratings-chart').innerHTML = "<div class='callout primary text-center'>Select donor tournament.</div>";
        return;
    }



    for (var i = 0; i < SeedingData.tournaments.inDiv.length; i++) {

        if(i == selectData.value){
            SeedingData.tournaments.donor = SeedingData.tournaments.inDiv[i];

        } else {
            SeedingData.tournaments.destinations.push(SeedingData.tournaments.inDiv[i]);
        }

    }

    var outputText = "";

    for (var i = 0; i < SeedingData.tournaments.destinations.length; i++) {

            var t = SeedingData.tournaments.destinations[i];

            outputText = outputText + "<tr>";
            outputText = outputText + "<td>" + t.name + "<input type='hidden' class='tournament-ids' value="+ t.tournamentID + "></td>";
            outputText = outputText + "<td >"+ t.sizeBefore + " -> </td>";
            outputText = outputText + "<td id='split-final-count-cell-"+ t.tournamentID + "'></td>";
            outputText = outputText + "<td><input type='number' id='split-rating-entry-"+t.tournamentID+"' \
                oninput=\"divSeedingDistribute()\" style='width:100px' name='divSeeding[ratingForID]["+t.tournamentID+"]'></td>";
            outputText = outputText + "</tr>";
    }

    document.getElementById('donor-id-form').value = SeedingData.tournaments.donor.tournamentID;
    document.getElementById('split-items-table').innerHTML = outputText;
    jQuery(".ratings-form-submit").prop("disabled", false);

    divSeedingUpdateList();

}

/******************************************************************************/

function divSeedingUpdateList(){

    var query = "mode=tournamentRatings&tournamentID="+SeedingData.tournaments.donor.tournamentID.toString();
    var xhr = new XMLHttpRequest();
    xhr.open("POST", AJAX_LOCATION+"?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){
            if(this.responseText.length > 1){ // If the fighter has already fought

                var data = JSON.parse(this.responseText);

                SeedingData.fighters = [];
                SeedingData.defaultRating = data.defaultRating;
                SeedingData.counts = {rated:0,unrated:0,total:0};


                for(var i = 0; i < data.fighters.length; i++){

                    var tmp = {};
                    SeedingData.counts.total++;

                    if(parseFloat(data.fighters[i].rating) > 0){
                        SeedingData.counts.rated++;
                        tmp.rating = parseFloat(data.fighters[i].rating);
                    } else {
                        SeedingData.counts.unrated++;
                        tmp.rating = SeedingData.defaultRating;
                    }

                    tmp.tournamentNum = 0;

                    SeedingData.fighters.push(tmp);

                }

                if(SeedingData.counts.total != 0){
                    var txt = "";
                    txt = "<p>"+SeedingData.counts.total+" in donor tournament, "+SeedingData.counts.rated+" rated and "+SeedingData.counts.unrated+" unrated."
                    if(SeedingData.counts.unrated != 0){
                        txt = txt + "<BR><b class='red-text'>Unrated fighters will be treated as having a rating of "+SeedingData.defaultRating+"</b></p>";
                    }
                    document.getElementById('donor-info-div').innerHTML = txt;
                } else {
                    document.getElementById('donor-info-div').innerHTML = "";
                }


                divSeedingUpdateChart();
            }
        }
    };
}

/******************************************************************************/

function divSeedingDistribute(){

    var fighterIndex = 0;

    for(var i = 0; i < SeedingData.fighters.length; i++){
        SeedingData.fighters[i].tournamentNum = 0;
    }

    for(var i=0; i < SeedingData.tournaments.destinations.length; i++){


        var t = SeedingData.tournaments.destinations[i];

        SeedingData.tournaments.destinations[i].rating = document.getElementById('split-rating-entry-'+t.tournamentID).value;
        SeedingData.tournaments.destinations[i].count = 0;

        for(; fighterIndex < SeedingData.fighters.length; fighterIndex++){

            if(SeedingData.fighters[fighterIndex].rating >= SeedingData.tournaments.destinations[i].rating){
                SeedingData.fighters[fighterIndex].tournamentNum = i + 1;
                SeedingData.tournaments.destinations[i].count++;
            } else {
                break;
            }
        }

        document.getElementById('split-final-count-cell-'+t.tournamentID).innerHTML = SeedingData.tournaments.destinations[i].count;

    }

    divSeedingUpdateChart();

}

/******************************************************************************/

function divSeedingUpdateChart(){

    if(SeedingData.fighters.length == 0){
        document.getElementById('ratings-chart').innerHTML = "<div class='callout warning text-center'>No fighters in donor tournament</div>";
        return;
    }

    var dataTable = [];
    var numTournaments = SeedingData.tournaments.inDiv.length;

    // Header row in data table
    var tmp = [];
    tmp[0] = 'Rank';
    for(var i = 0; i < numTournaments; i++){
        tmp.push(SeedingData.tournaments.inDiv[i].name);
    }
    dataTable[0] = tmp;
    var ticksArray = [];

    for(var i = 0; i < SeedingData.fighters.length; i++){

        var tmp = [];
        tmp[0] = i; //fighter rank

        for(var j = 0; j < numTournaments; j++){

            if(SeedingData.fighters[i].tournamentNum == j){
                tmp.push(SeedingData.fighters[i].rating);
            } else {
                tmp.push(0);
            }

        }

        dataTable[i+1] = tmp;
        if((i+1) % 10 == 0){
            ticksArray.push(i+1);
        }

    }

    var data = google.visualization.arrayToDataTable(dataTable);

    var options = {};
    options.legend = { position: 'none' };
    options.chartArea = {left: 40, bottom: 30, top: 10,'width': '100%', 'height': '100%'};
    options.hAxis = {ticks: ticksArray};


    var chart = new google.visualization.LineChart(document.getElementById('ratings-chart'));

    chart.draw(data, options);

}

/******************************************************************************/

function updateFighterRating(tournamentRosterID){


    var rating = document.getElementById('rating-input-'+tournamentRosterID).value;

    var query = "mode=updateFighterRating";
    query = query + "&tournamentRosterID="+tournamentRosterID;
    query = query + "&rating="+rating;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", AJAX_LOCATION+"?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){
            if(this.responseText.length > 1){

                //console.log(this.responseText);
                var data = JSON.parse(this.responseText);
                //console.log(data);
                
                var tournamentRosterID = data['tournamentRosterID'];
                document.getElementById('rating-output-'+tournamentRosterID).innerHTML = data['rating'];
            }
        }
    };

}

/******************************************************************************/
