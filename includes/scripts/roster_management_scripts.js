
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
    var MAX_ROWS = 15;      // per submit, so a lost page (timeout, misclick) costs little

    var rowsContainer, template, form, errorBox, addButton, limitNote;
    var submitting = false;
    var collapsible = false; // many tournaments: see setActive()

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
        addButton = document.getElementById('addParticipantRow');
        limitNote = document.getElementById('newParticipantLimit');

        if(!rowsContainer || !template || !form){ return; }

        collapsible = form.closest('.tournaments-collapse') != null;
        limitNote.textContent = MAX_ROWS + ' rows at a time. Add these, then keep going.';
        setActive(addRow());

        addButton.addEventListener('click', function(e){
            e.preventDefault();
            var row = addRow();
            if(row){ row.querySelector('.name-inputs input').focus(); }
        });

        form.addEventListener('submit', onSubmit);
        window.addEventListener('beforeunload', onLeave);
    }

/*----------------------------------------------------------------------------*/

    // Returns null once MAX_ROWS rows exist.
    function addRow(){
        if(allRows().length >= MAX_ROWS){ return null; }
        var html = template.innerHTML.replace(/__K__/g, String(nextIndex++));
        var holder = document.createElement('div');
        holder.innerHTML = html;
        var row = holder.querySelector('.new-participant-row');
        rowsContainer.appendChild(row);
        wireRow(row);
        updateRowLimit();
        return row;
    }

    function removeRow(row){
        var neighbor = row.nextElementSibling || row.previousElementSibling;
        var wasActive = row.classList.contains('is-active');
        var hadFocus = row.contains(document.activeElement);
        row.parentNode.removeChild(row);
        if(rowsContainer.children.length == 0){
            neighbor = addRow();
        }
        if(wasActive){ setActive(neighbor); }
        // Removed from the keyboard: stay in the rows rather than drop to the page.
        if(hadFocus){ neighbor.querySelector('.remove-row').focus(); }
        updateRowLimit();
    }

    // Marks the row being worked on. With many tournaments (.tournaments-collapse)
    // only that row shows every chip; the others show just their picks.
    function setActive(row){
        allRows().forEach(function(r){ r.classList.toggle('is-active', r == row); });
    }

    function updateRowLimit(){
        var full = allRows().length >= MAX_ROWS;
        addButton.classList.toggle('disabled', full);
        addButton.setAttribute('aria-disabled', full ? 'true' : 'false');
        limitNote.hidden = !full;
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
                detail: function(f){ return f.schoolName; },
                onSelect: function(f){ selectFighter(row, els, f); }
            });
            input.addEventListener('input', function(){
                // Editing a picked fighter's name turns the row back into a new entry.
                if(els.systemRosterID.value != '0'){
                    els.systemRosterID.value = '0';
                    unlockSchool(els);
                }
            });
        });

        // School combobox
        attachSchoolCombobox(els, schools);

        // Tournament chips
        var chips = row.querySelectorAll('.tournament-box');
        for(var i = 0; i < chips.length; i++){
            chips[i].addEventListener('click', function(){
                toggleChip(this);
                keepBlankRowBelow(row);
            });
        }
        row.addEventListener('keydown', onChipKey);

        // Remove
        row.querySelector('.remove-row').addEventListener('click', function(){ removeRow(row); });

        row.addEventListener('keydown', function(e){ onEnter(e, row); });
        row.addEventListener('input', function(){ keepBlankRowBelow(row); });
        row.addEventListener('focusin', function(){ setActive(row); });

        // Clicks off the inputs and chips mark the row too. A collapsed row shows
        // only its picks: clicking one reopens the row rather than unpicking it.
        // The chip takes focus (marking the row) before the click lands, so
        // whether the row was collapsed is read on mousedown.
        var reopening = false;
        row.addEventListener('mousedown', function(e){
            reopening = collapsible && !row.classList.contains('is-active')
                && e.target.closest('.tournament-box') != null;
        }, true);
        row.addEventListener('click', function(e){
            setActive(row);
            if(reopening){
                reopening = false;
                e.stopPropagation();
            }
        }, true);
    }

    // Keep one blank row below the last row with anything in it. Focus alone
    // doesn't add one, so Tab can move on past a blank last row.
    function keepBlankRowBelow(row){
        if(row == rowsContainer.lastElementChild){ addRow(); }
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
        chip.setAttribute('aria-checked', checkbox.checked ? 'true' : 'false');
    }

    function isChip(el){
        return el.classList.contains('tournament-box');
    }

    // Chips are focusable checkboxes: Space toggles one as a click does.
    function onChipKey(e){
        if(e.key != ' ' || !isChip(e.target)){ return; }
        e.preventDefault();
        if(!e.repeat){ e.target.click(); }
    }

    // Enter in a text box or on a chip moves to the next row (adding one if
    // needed) instead of submitting.
    function onEnter(e, row){
        if(e.key != 'Enter' || e._autocompleteHandled){ return; }
        if(e.target.type != 'text' && !isChip(e.target)){ return; }
        e.preventDefault();
        var next = row.nextElementSibling || addRow();
        if(next){ next.querySelector('.name-inputs input').focus(); }
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
            onSelect: function(s){ setSchool(els, s); },
            highlightFirst: true // the school must come from the list
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

    // Tab takes the first match, so an exact match leads (tabbing through a
    // picked school keeps it), then schools starting with the text.
    function matchSchools(schoolList, text){
        text = text.trim().toLowerCase();
        var exact = [], starts = [], contains = [];
        for(var i = 0; i < schoolList.length; i++){
            var label = schoolList[i].label.toLowerCase();
            var at = label.indexOf(text);
            if(at < 0){ continue; }
            if(label == text){
                exact.push(schoolList[i]);
            } else if(at == 0){
                starts.push(schoolList[i]);
            } else {
                contains.push(schoolList[i]);
            }
        }
        return exact.concat(starts, contains);
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
            return;
        }
        submitting = true;
    }

    // Ask before a navigation throws away typed rows.
    function onLeave(e){
        if(submitting || !allRows().some(rowHasName)){ return; }
        e.preventDefault();
        e.returnValue = '';
    }

/*----------------------------------------------------------------------------*/
// Minimal autocomplete: renders a listbox under the input, keyboard navigable.
//   opts.items(showAll) -> array of items for the current input value, or a Promise of one
//   opts.render(item)   -> label string
//   opts.detail(item)   -> optional muted text after the label
//   opts.onSelect(item)
//   opts.highlightFirst -> highlight the first item as the list opens
// Down opens the list; Enter or Tab takes the highlighted item.
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
            requestID++; // a search landing after focus moves on stays closed
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
                var detail = opts.detail && opts.detail(item);
                if(detail){
                    var span = document.createElement('span');
                    span.className = 'autocomplete-detail';
                    span.textContent = detail;
                    li.appendChild(span);
                }
                li.addEventListener('mousedown', function(e){
                    e.preventDefault(); // keep focus in the input
                    choose(i);
                });
                list.appendChild(li);
            });
            list.hidden = false;
            highlight(opts.highlightFirst ? 0 : -1);
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
            if(i >= 0){ lis[i].scrollIntoView({ block: 'nearest' }); }
        }

        input.addEventListener('input', function(){ open(false); });
        input.addEventListener('focus', function(){ if(input.value.trim() != ''){ open(false); } });
        input.addEventListener('blur', close);
        input.addEventListener('keydown', function(e){
            if(!isOpen()){
                if(e.key == 'ArrowDown'){
                    e.preventDefault();
                    open(input.value.trim() == '');
                }
                return;
            }
            if(e.key == 'Tab'){
                if(active >= 0){ choose(active); } // and let focus move on
            } else if(e.key == 'ArrowDown'){
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

    return { init: init, attachSchoolCombobox: attachSchoolCombobox, toggleChip: toggleChip, onChipKey: onChipKey };

})();

/******************************************************************************/
// Inline editor row shared by the roster table and the import review: names,
// school combobox and weapon-grouped tournament chips, built from a <template>
// whose fields come from rosterEditorFields() in display_functions.php.
/******************************************************************************/

var RosterEditor = (function(){

    // opts: template, formID, fieldName, firstName, lastName, schoolID,
    // tournamentIDs[], schools, tournamentGroups, manyTournaments,
    // onChip(tournamentID, chip) (optional), onEnter(), onCancel().
    // Returns the editor <tr>, not yet inserted.
    function build(opts){
        var holder = document.createElement('tbody');
        holder.innerHTML = opts.template.innerHTML;
        var editor = holder.firstElementChild;
        var entered = {};
        (opts.tournamentIDs || []).forEach(function(id){ entered[id] = true; });

        editor.querySelector('.edit-first-name').value = opts.firstName;
        editor.querySelector('.edit-last-name').value = opts.lastName;
        var names = editor.querySelectorAll('.name-inputs input');
        names.forEach(function(input){
            input.addEventListener('input', function(){
                names.forEach(function(n){ n.classList.remove('is-invalid-input'); });
            });
        });

        var els = {
            school: editor.querySelector('.edit-school-input'),
            schoolID: editor.querySelector('.edit-school-id'),
            schoolToggle: editor.querySelector('.school-toggle')
        };
        RosterEntry.attachSchoolCombobox(els, opts.schools);
        opts.schools.forEach(function(s){
            if(s.schoolID == opts.schoolID){ els.school.value = s.label; els.schoolID.value = opts.schoolID; }
        });

        // Chips grouped by weapon as on the entry card; many get a line of their own.
        var chipHolder = editor.querySelector('.edit-tournaments');
        opts.tournamentGroups.forEach(function(group){
            var line = document.createElement('div');
            line.className = 'tournament-group';
            if(group.weapon != ''){
                var label = document.createElement('span');
                label.className = 'tournament-group-label';
                label.textContent = group.weapon;
                line.appendChild(label);
            }
            group.tournaments.forEach(function(t){
                var chip = document.createElement('div');
                chip.className = 'tournament-box' + (entered[t.tournamentID] ? ' is-selected' : '');
                chip.tabIndex = 0;
                chip.setAttribute('role', 'checkbox');
                chip.setAttribute('aria-checked', entered[t.tournamentID] ? 'true' : 'false');
                chip.innerHTML = "<input type='checkbox' class='hidden' form='" + opts.formID + "'"
                    + " name='" + opts.fieldName + "[tournamentIDs][" + t.tournamentID + "]'"
                    + " value='" + t.tournamentID + "'" + (entered[t.tournamentID] ? ' checked' : '') + ">";
                chip.appendChild(document.createTextNode(t.label));
                chip.addEventListener('click', function(){
                    RosterEntry.toggleChip(chip);
                    if(opts.onChip){ opts.onChip(t.tournamentID, chip); }
                });
                line.appendChild(chip);
            });
            chipHolder.appendChild(line);
        });
        editor.classList.toggle('tournaments-wide', !!opts.manyTournaments);

        editor.querySelector('.roster-cancel').addEventListener('click', opts.onCancel);
        // Space toggles a chip; Enter in a text box or on a chip saves.
        editor.addEventListener('keydown', function(e){
            RosterEntry.onChipKey(e);
            if(e.key != 'Enter' || e._autocompleteHandled){ return; }
            if(e.target.type != 'text' && !e.target.classList.contains('tournament-box')){ return; }
            e.preventDefault();
            if(!editor.querySelector('.roster-save').disabled){ opts.onEnter(); }
        });
        return editor;
    }

    // Never post a blank name (both boxes empty, matching the entry form), or
    // schoolID 0 (a cleared box, or a school with no short name) against the FK.
    // Marks the inputs at fault; returns whether the editor can be saved.
    function validate(editor){
        var names = editor.querySelectorAll('.name-inputs input');
        var blankName = Array.prototype.every.call(names, function(input){ return input.value.trim() == ''; });
        var noSchool = editor.querySelector('.edit-school-id').value == '0';

        names.forEach(function(input){ input.classList.toggle('is-invalid-input', blankName); });
        if(noSchool){ editor.querySelector('.edit-school-input').classList.add('is-invalid-input'); }
        return !blankName && !noSchool;
    }

    return { build: build, validate: validate };

})();

/******************************************************************************/
// Event roster table (participantsEvent.php). Organizers click a row to edit
// it in place; public viewers click a row to reveal its schedule block.
/******************************************************************************/

var RosterTable = (function(){

    var schools = [];           // {schoolID, label}
    var tournamentGroups = [];  // see rosterTournamentGroups() in display_functions.php
    var manyTournaments = false;
    var template, form, rosterIDField;
    var current = null;     // {row, editor, fought:{tournamentID:true}} while an editor is open

    function init(config){
        schools = config.schools || [];
        tournamentGroups = config.tournamentGroups || [];
        manyTournaments = !!config.manyTournaments;
        template = document.getElementById('rosterEditorTemplate');
        form = document.getElementById('rosterEditForm');
        rosterIDField = document.getElementById('editRosterID');

        var rows = document.querySelectorAll('tr.roster-row');
        for(var r = 0; r < rows.length; r++){
            rows[r].addEventListener('click', template ? onEditClick : onScheduleClick);
            rows[r].addEventListener('keydown', onRowKey);
        }
        document.addEventListener('keydown', function(e){
            if(e.key == 'Escape' && !e._autocompleteHandled){ closeEditor(); }
        });
        if(form){ form.addEventListener('submit', onSubmit); }
    }

    // Clickable rows are focusable: Enter or Space opens one as a click does.
    function onRowKey(e){
        if(e.target != this || (e.key != 'Enter' && e.key != ' ')){ return; }
        e.preventDefault();
        this.click();
    }

    function onSubmit(e){
        if(!current){ return; }
        if(!RosterEditor.validate(current.editor)){ e.preventDefault(); }
    }

    function onScheduleClick(){
        var schedule = document.getElementById('tList-' + this.getAttribute('data-roster-id'));
        if(schedule){ schedule.classList.toggle('hidden'); }
    }

    function onEditClick(e){
        if(e.target.closest('.roster-remove')){ return; } // the delete checkbox
        if(!current || current.row != this){ openEditor(this); }
    }

/*----------------------------------------------------------------------------*/

    function openEditor(row){
        closeEditor();

        var rosterID = row.getAttribute('data-roster-id');
        var entered = row.getAttribute('data-tournament-ids').split(',').filter(function(id){ return id != ''; });

        // Names come from data attributes: the name cell may include a participant ID.
        var editor = RosterEditor.build({
            template: template,
            formID: 'rosterEditForm',
            fieldName: 'editParticipantData',
            firstName: row.getAttribute('data-first-name'),
            lastName: row.getAttribute('data-last-name'),
            schoolID: row.getAttribute('data-school-id'),
            tournamentIDs: entered,
            schools: schools,
            tournamentGroups: tournamentGroups,
            manyTournaments: manyTournaments,
            onChip: function(tournamentID, chip){
                if(entered.indexOf(String(tournamentID)) != -1){ checkFought(rosterID, tournamentID, chip.firstChild); }
            },
            onEnter: function(){ form.requestSubmit(); },
            onCancel: closeEditor
        });

        var scheduleRow = document.getElementById('tList-' + rosterID);
        if(scheduleRow){
            editor.querySelector('.roster-editor-schedule').innerHTML = scheduleRow.firstElementChild.innerHTML;
        }

        rosterIDField.value = rosterID;
        row.parentNode.insertBefore(editor, row.nextSibling);
        row.classList.add('is-editing');
        current = { row: row, editor: editor, fought: {} };
        editor.querySelector('.name-inputs input').focus();
    }

    function closeEditor(){
        if(!current){ return; }
        var hadFocus = current.editor.contains(document.activeElement);
        current.editor.parentNode.removeChild(current.editor);
        current.row.classList.remove('is-editing');
        // Closed from the keyboard: back to the row, not the top of the page.
        if(hadFocus){ current.row.focus(); }
        rosterIDField.value = '0';
        current = null;
    }

    // Removing a fighter from a tournament they have already fought in shows a
    // warning and disables Save until the explicit confirm button is used.
    function checkFought(rosterID, tournamentID, box){
        var ctx = current;
        if(box.checked){
            delete ctx.fought[tournamentID];
            updateWarning(ctx);
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.open('GET', AJAX_LOCATION + '?mode=hasFought&rosterID=' + rosterID + '&tournamentID=' + tournamentID, true);
        xhr.onload = function(){
            // Ignore if the editor changed or the chip was re-checked while in flight.
            if(current == ctx && !box.checked && this.responseText.slice(-10) == 'HAS FOUGHT'){
                ctx.fought[tournamentID] = true;
                updateWarning(ctx);
            }
        };
        xhr.send();
    }

    function updateWarning(ctx){
        var show = Object.keys(ctx.fought).length > 0;
        ctx.editor.querySelector('.roster-editor-warning').classList.toggle('hidden', !show);
        ctx.editor.querySelector('.roster-save').disabled = show;
    }

    return { init: init };

})();

/******************************************************************************/
// Participant import review (participantsImport.php). Each row is ticked by
// hand; Import counts the ticked rows and waits for the final confirmation.
// A row that could be more than one fighter can't be ticked until one is
// chosen. Clicking a row's name or school opens the roster editor under it;
// Save posts the page and the server checks the row again. Kept above the
// charts code below, which stops this file if Google's loader is blocked.
/******************************************************************************/

var ImportReview = (function(){

    var form, confirmed, importButton, template;
    var schools = [];           // {schoolID, label}
    var tournamentGroups = [];  // see rosterTournamentGroups() in display_functions.php
    var manyTournaments = false;
    var current = null;         // {row, editor} while a row is being edited

    function init(config){
        config = config || {};
        schools = config.schools || [];
        tournamentGroups = config.tournamentGroups || [];
        manyTournaments = !!config.manyTournaments;
        form = document.getElementById('importForm');
        confirmed = document.getElementById('importConfirmed');
        importButton = form.querySelector("button[name='formName'][value='confirmParticipantsImport']");
        template = document.getElementById('importEditorTemplate');

        form.addEventListener('change', function(e){
            if(e.target.classList.contains('import-choice')){ choose(e.target); }
            refresh();
        });
        form.addEventListener('submit', onSubmit);

        var cells = form.querySelectorAll('tr.import-row > td.import-name, tr.import-row > td.import-school');
        for(var i = 0; i < cells.length; i++){
            cells[i].addEventListener('click', onEditClick);
        }
        document.addEventListener('keydown', function(e){
            if(e.key == 'Escape' && !e._autocompleteHandled){ closeEditor(); }
        });

        // A choice made while the footer's scripts loaded, or restored by the
        // browser on reload, fired no change event here
        var chosen = form.querySelectorAll('input.import-choice:checked');
        for(var j = 0; j < chosen.length; j++){
            choose(chosen[j]);
        }
        refresh();
    }

/*----------------------------------------------------------------------------*/

    function onEditClick(e){
        if(e.target.closest('input, label')){ return; } // choosing a fighter isn't editing
        var row = this.closest('tr.import-row');
        if(!current || current.row != row){ openEditor(row); }
    }

    // The editor opens on the row's own values from the file, not the stored
    // fighter's: edits change only the import row.
    function openEditor(row){
        closeEditor();

        var editor = RosterEditor.build({
            template: template,
            formID: 'importForm',
            fieldName: 'importEdit',
            firstName: row.getAttribute('data-first-name'),
            lastName: row.getAttribute('data-last-name'),
            schoolID: row.getAttribute('data-school-id'),
            tournamentIDs: row.getAttribute('data-tournament-ids').split(',').filter(function(id){ return id != ''; }),
            schools: schools,
            tournamentGroups: tournamentGroups,
            manyTournaments: manyTournaments,
            onEnter: function(){ form.requestSubmit(editor.querySelector('.roster-save')); },
            onCancel: closeEditor
        });
        editor.querySelector('.edit-index').value = row.getAttribute('data-index');

        row.parentNode.insertBefore(editor, row.nextSibling);
        current = { row: row, editor: editor };
        refresh(); // Import waits until the edit is saved or cancelled
        editor.querySelector('.name-inputs input').focus();
    }

    function closeEditor(){
        if(!current){ return; }
        current.editor.parentNode.removeChild(current.editor);
        current = null;
        refresh();
    }

    // Save never posts a blank name or schoolID 0. The page's other buttons
    // (only Cancel while editing) post without checking the editor.
    function onSubmit(e){
        if(!current || e.submitter != current.editor.querySelector('.roster-save')){ return; }
        if(!RosterEditor.validate(current.editor)){ e.preventDefault(); }
    }

/*----------------------------------------------------------------------------*/

    // Shows what the chosen fighter means for the row. Choosing someone already
    // in the event with nothing to add leaves nothing to tick.
    function choose(radio){
        var row = radio.closest('tr.import-row');
        var outcome = radio.getAttribute('data-outcome');
        var box = row.querySelector('input.import-check');
        if(box){
            box.disabled = outcome == 'nothing';
            if(box.disabled){ box.checked = false; }
        }

        var status = row.querySelector('td.import-status');
        var text = document.createTextNode(radio.getAttribute('data-status'));
        status.textContent = '';
        if(outcome == 'new'){
            status.appendChild(document.createElement('b')).appendChild(text);
        } else {
            status.appendChild(text);
        }
    }

    // What a row would do right now: the chosen fighter's outcome if one is
    // picked, otherwise the row's own.
    function currentOutcome(row){
        var picked = row.querySelector('input.import-choice:checked');
        var source = picked || row;
        return { kind: source.getAttribute('data-outcome'), noSchool: source.getAttribute('data-no-school') == '1' };
    }

    function refreshSummary(){
        var rows = form.querySelectorAll('tr.import-row');
        var counts = { known: 0, new: 0, entered: 0, noSchool: 0, schoolPrompt: 0 };
        var newNames = [];
        var numTicked = 0;

        for(var i = 0; i < rows.length; i++){
            var box = rows[i].querySelector('input.import-check');
            if(!box || !box.checked || box.disabled){ continue; }
            numTicked++;
            var outcome = currentOutcome(rows[i]);
            if(counts.hasOwnProperty(outcome.kind)){ counts[outcome.kind]++; }
            if(outcome.kind == 'new'){
                newNames.push(rows[i].getAttribute('data-name'));
                if(outcome.noSchool){ counts.noSchool++; }
            }
        }
        counts.leftOut = rows.length - numTicked;

        for(var key in counts){
            document.querySelector("#importSummary [data-count='" + key + "'] .count").textContent = counts[key];
        }
        document.getElementById('importSummaryHeading').textContent =
            "You're importing " + numTicked + " of " + rows.length + " rows";
        var names = document.getElementById('importNewNames');
        names.textContent = newNames.join(', ');
        names.hidden = newNames.length == 0;
    }

    function refresh(){
        refreshSummary();
        var numTicked = form.querySelectorAll('input.import-check:checked:enabled').length;
        importButton.textContent = 'Import ' + numTicked + ' participants';
        importButton.disabled = !confirmed.checked || numTicked == 0 || current != null;
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
