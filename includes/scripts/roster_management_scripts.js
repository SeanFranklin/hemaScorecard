
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
// Event roster entry form (participantsEvent.php)
//
// Builds "new participant" rows from a <template>, and attaches a small
// vanilla-JS autocomplete to the name and school inputs. No jQuery.
/******************************************************************************/

var RosterEntry = (function(){

    // Schools are embedded in the page (a short list); fighters are searched
    // server side (AJAX 'fighterSearch') since the system roster runs to
    // tens of thousands of names.
    var schools = [];    // {schoolID, label}
    var schoolsByID = {};
    var nextIndex = 1;
    var MAX_SUGGESTIONS = 8;
    var SEARCH_DELAY_MS = 150;

    var rowsContainer, template, form, errorBox;

/*----------------------------------------------------------------------------*/

    function init(config){
        schools = config.schools || [];
        schoolsByID = {};
        for(var i = 0; i < schools.length; i++){
            schoolsByID[schools[i].schoolID] = schools[i];
        }

        rowsContainer = document.getElementById('newParticipantRows');
        template = document.getElementById('newParticipantRowTemplate');
        form = document.getElementById('addParticipantsForm');
        errorBox = document.getElementById('newParticipantError');

        if(!rowsContainer || !template || !form){ return; }

        addRow();

        document.getElementById('addParticipantRow').addEventListener('click', function(e){
            e.preventDefault();
            addRow(); // not focused, or the focus rule below would add a second
        });

        form.addEventListener('submit', onSubmit);
    }

/*----------------------------------------------------------------------------*/

    function addRow(){
        var html = template.innerHTML.replace(/__K__/g, String(nextIndex++));
        var holder = document.createElement('div');
        holder.innerHTML = html;
        var row = holder.querySelector('.new-participant-row');
        rowsContainer.appendChild(row);
        wireRow(row);
        return row;
    }

    function removeRow(row){
        row.parentNode.removeChild(row);
        if(rowsContainer.children.length == 0){
            addRow();
        }
    }

    function allRows(){
        return Array.prototype.slice.call(rowsContainer.querySelectorAll('.new-participant-row'));
    }

/*----------------------------------------------------------------------------*/

    function wireRow(row){
        var els = rowElements(row);

        // Name autocomplete on both name inputs
        [els.first, els.last].forEach(function(input){
            attachAutocomplete(input, {
                items: function(){ return matchFighters(row, els); },
                render: function(f){ return f.firstName + ' ' + f.lastName; },
                onSelect: function(f){ selectFighter(row, els, f); }
            });
            input.addEventListener('input', function(){
                // Editing a picked fighter's name turns the row back into a new entry.
                if(els.systemRosterID.value != '0'){
                    els.systemRosterID.value = '0';
                    unlockSchool(els);
                }
            });
            input.addEventListener('keydown', function(e){ onEnter(e, row); });
        });

        // School combobox
        attachSchoolCombobox(els, schools);
        els.school.addEventListener('keydown', function(e){ onEnter(e, row); });

        // Tournament chips
        var chips = row.querySelectorAll('.tournament-box');
        for(var i = 0; i < chips.length; i++){
            chips[i].addEventListener('click', function(){ toggleChip(this); });
        }

        // Remove
        row.querySelector('.remove-row').addEventListener('click', function(){ removeRow(row); });

        // Keep one fresh row below whatever the user is editing.
        row.addEventListener('focusin', function(){
            if(row == rowsContainer.lastElementChild){ addRow(); }
        });
    }

    function rowElements(row){
        return {
            first: row.querySelector('.first-name-input'),
            last: row.querySelector('.last-name-input'),
            systemRosterID: row.querySelector('.system-roster-id'),
            school: row.querySelector('.school-input'),
            schoolID: row.querySelector('.school-id'),
            schoolToggle: row.querySelector('.school-toggle')
        };
    }

    function toggleChip(chip){
        var checkbox = chip.querySelector('input[type=checkbox]');
        checkbox.checked = !checkbox.checked;
        chip.classList.toggle('is-selected', checkbox.checked);
    }

    // Enter moves to the next row (adding one if needed) instead of submitting.
    function onEnter(e, row){
        if(e.key != 'Enter' || e._autocompleteHandled){ return; }
        e.preventDefault();
        var next = row.nextElementSibling || addRow();
        next.querySelector('.name-inputs input').focus();
    }

/*----------------------------------------------------------------------------*/

    function selectedSystemIDs(exceptRow){
        var ids = {};
        allRows().forEach(function(r){
            if(r == exceptRow){ return; }
            var id = r.querySelector('.system-roster-id').value;
            if(id != '0'){ ids[id] = true; }
        });
        return ids;
    }

    // Resolves to fighters matching the row's typed names, minus any already
    // picked in another row. Debounced; the autocomplete discards stale results.
    function matchFighters(row, els){
        var first = els.first.value.trim();
        var last = els.last.value.trim();
        if(first == '' && last == ''){ return []; }

        return new Promise(function(resolve){
            clearTimeout(row._searchTimer);
            row._searchTimer = setTimeout(function(){
                var xhr = new XMLHttpRequest();
                xhr.open('GET', AJAX_LOCATION + '?mode=fighterSearch&firstName=' + encodeURIComponent(first)
                    + '&lastName=' + encodeURIComponent(last), true);
                xhr.onload = function(){
                    var taken = selectedSystemIDs(row);
                    resolve(JSON.parse(this.responseText).filter(function(f){
                        return !taken[f.systemRosterID];
                    }).slice(0, MAX_SUGGESTIONS));
                };
                xhr.send();
            }, SEARCH_DELAY_MS);
        });
    }

    function selectFighter(row, els, f){
        els.first.value = f.firstName;
        els.last.value = f.lastName;
        els.systemRosterID.value = String(f.systemRosterID);
        var school = schoolsByID[f.schoolID];
        if(school){
            // Known fighters keep their school; the field unlocks if the name is edited.
            setSchool(els, school);
            els.school.readOnly = true;
            els.school.parentNode.classList.add('is-locked');
        } else {
            // systemRoster.schoolID can be null: leave the school for the user to pick.
            els.school.value = '';
            els.schoolID.value = '0';
            unlockSchool(els);
        }
    }

    function unlockSchool(els){
        els.school.readOnly = false;
        els.school.parentNode.classList.remove('is-locked');
    }

/*----------------------------------------------------------------------------*/

    // School combobox: els = {school (text input), schoolID (hidden), schoolToggle (caret)}
    function attachSchoolCombobox(els, schoolList){
        attachAutocomplete(els.school, {
            items: function(showAll){ return matchSchools(schoolList, showAll ? '' : els.school.value); },
            render: function(s){ return s.label; },
            onSelect: function(s){ setSchool(els, s); }
        });
        els.school.addEventListener('input', function(){
            var exact = findSchoolByLabel(schoolList, els.school.value);
            els.schoolID.value = exact ? exact.schoolID : '0';
            els.school.classList.remove('is-invalid-input');
        });
        els.schoolToggle.addEventListener('mousedown', function(e){
            e.preventDefault();
            if(els.school.readOnly){ return; }
            els.school.focus();
            els.school._autocomplete.open(true);
        });
    }

    function matchSchools(schoolList, text){
        text = text.trim().toLowerCase();
        var out = [];
        for(var i = 0; i < schoolList.length; i++){
            if(text == '' || schoolList[i].label.toLowerCase().indexOf(text) >= 0){
                out.push(schoolList[i]);
            }
        }
        return out;
    }

    function findSchoolByLabel(schoolList, label){
        label = label.trim().toLowerCase();
        for(var i = 0; i < schoolList.length; i++){
            if(schoolList[i].label.toLowerCase() == label){ return schoolList[i]; }
        }
        return null;
    }

    function setSchool(els, school){
        els.school.value = school.label;
        els.schoolID.value = String(school.schoolID);
        els.school.classList.remove('is-invalid-input');
    }

/*----------------------------------------------------------------------------*/

    function rowHasName(row){
        return row.querySelector('.first-name-input').value.trim() != ''
            || row.querySelector('.last-name-input').value.trim() != '';
    }

    function onSubmit(e){
        errorBox.textContent = '';
        var valid = true;
        var anyToAdd = false;

        allRows().forEach(function(row){
            if(!rowHasName(row)){
                // Blank rows are dropped so they never post.
                row.parentNode.removeChild(row);
                return;
            }
            anyToAdd = true;
            var els = rowElements(row);
            if(els.schoolID.value == '0'){
                els.school.classList.add('is-invalid-input');
                valid = false;
            }
        });

        if(!anyToAdd){
            e.preventDefault();
            errorBox.textContent = 'Enter at least one participant.';
            addRow();
            return;
        }
        if(!valid){
            e.preventDefault();
            errorBox.textContent = 'Pick a school for each highlighted participant.';
        }
    }

/*----------------------------------------------------------------------------*/
// Minimal autocomplete: renders a listbox under the input, keyboard navigable.
//   opts.items(showAll) -> array of items for the current input value, or a Promise of one
//   opts.render(item)   -> label string
//   opts.onSelect(item)
// Enter/Escape handled here set e._autocompleteHandled so outer handlers skip them.

    function attachAutocomplete(input, opts){
        var wrap = input.parentNode;
        var list = document.createElement('ul');
        list.className = 'autocomplete-list';
        list.setAttribute('role', 'listbox');
        list.hidden = true;
        wrap.appendChild(list);

        var items = [];
        var active = -1;
        var requestID = 0;

        function isOpen(){ return !list.hidden; }

        function close(){
            list.hidden = true;
            list.innerHTML = '';
            items = [];
            active = -1;
        }

        function open(showAll){
            if(input.readOnly){ return; }
            var token = ++requestID;
            Promise.resolve(opts.items(showAll)).then(function(result){
                if(token == requestID){ show(result); }
            });
        }

        function show(result){
            items = result;
            list.innerHTML = '';
            if(items.length == 0){ close(); return; }
            items.forEach(function(item, i){
                var li = document.createElement('li');
                li.className = 'autocomplete-item';
                li.setAttribute('role', 'option');
                li.textContent = opts.render(item);
                li.addEventListener('mousedown', function(e){
                    e.preventDefault(); // keep focus in the input
                    choose(i);
                });
                list.appendChild(li);
            });
            active = -1;
            list.hidden = false;
        }

        function choose(i){
            var item = items[i];
            close();
            if(item){ opts.onSelect(item); }
        }

        function highlight(i){
            var lis = list.children;
            for(var j = 0; j < lis.length; j++){
                lis[j].classList.toggle('is-active', j == i);
            }
            active = i;
        }

        input.addEventListener('input', function(){ open(false); });
        input.addEventListener('focus', function(){ if(input.value.trim() != ''){ open(false); } });
        input.addEventListener('blur', close);
        input.addEventListener('keydown', function(e){
            if(!isOpen()){ return; }
            if(e.key == 'ArrowDown'){
                e.preventDefault();
                highlight((active + 1) % items.length);
            } else if(e.key == 'ArrowUp'){
                e.preventDefault();
                highlight((active - 1 + items.length) % items.length);
            } else if(e.key == 'Enter'){
                e.preventDefault();
                e._autocompleteHandled = true;
                if(active >= 0){ choose(active); } else { close(); }
            } else if(e.key == 'Escape'){
                e._autocompleteHandled = true;
                close();
            }
        });

        input._autocomplete = { open: open, close: close, isOpen: isOpen };
    }

    return { init: init };

})();

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
