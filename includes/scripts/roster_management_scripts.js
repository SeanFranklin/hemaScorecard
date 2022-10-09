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
                console.log(data);

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

