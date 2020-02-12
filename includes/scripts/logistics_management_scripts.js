
/**********************************************************************/

function logistics_changeScheduleBlock(){

    logistics_manageScheduleBlock($("#blockID").val());
}


/**********************************************************************/

function logistics_manageScheduleBlock(blockID){

    $("#blockID").val(blockID);

    logistics_esbPopulateForm();

    $("#scheduleBlockModal").foundation("open");

}


/**********************************************************************/

function logistics_esbPopulateForm(){
// Event Schedule Item

    blockID = $("#blockID").val();
    $("#esb-errorLog").html("");
    $("#esb-warningLog").html("");
    $("#esb-blockID").val(blockID);

    if(blockID == 0){
        $('#esb-form').trigger("reset");
        $("#esb-blockTypeID").val(0);
        $("#esb-submitButton").html("Add");
        $('#esb-deleteButton').prop("disabled", true);
        $('#esb-deleteButton').attr("disabled", 'disabled');
        $("#esb-tournamentID").attr("disabled",false);
        $("#esb-blockTypeID").attr("disabled", false);
        
        var query = "mode=getSessionDayNum";
        var xhr2 = new XMLHttpRequest();
        xhr2.open("POST", AJAX_LOCATION+"?"+query, true);
        xhr2.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr2.send();

        xhr2.onreadystatechange = function (){
            if(this.readyState == 4 && this.status == 200){
                if(this.responseText.length >= 1){
                    var dayNum = JSON.parse(this.responseText);
                    $("#esb-dayNum").val(dayNum);
                    
                }
            }
        };

    } else {
        $('#esb-deleteButton').prop("disabled", false);
        $('#esb-deleteButton').attr("disabled", false);

        var query = "mode=getScheduleBlockInfo&blockID="+blockID.toString();
        var xhr = new XMLHttpRequest();
        xhr.open("POST", AJAX_LOCATION+"?"+query, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send();

        xhr.onreadystatechange = function (){
            if(this.readyState == 4 && this.status == 200){
                if(this.responseText.length > 1){
                    
                    var data = JSON.parse(this.responseText);
             
                    // Schedule block data
                    $("#esb-blockID").val(data['blockID']);
                    $("#esb-blockTypeID").val(data['blockTypeID']);
                    $("#esb-blockTypeID").attr("disabled", true);
                    logistics_esbBlockTypeCheck();

                    $("#esb-tournamentID").val(data['tournamentID']);
                    $("#esb-tournamentID").attr("disabled", true);
                    $("#esb-numShifts").val(data['numShifts']);
                    $("#esb-dayNum").val(data['dayNum']);
                    if(data['suppressConflicts'] == 0){
                        $("#esb-suppressConflicts").prop('checked', false);
                    } else {
                        $("#esb-suppressConflicts").prop('checked', true);
                    }
                    

                    $("#esb-blockTitle").val(data['blockTitle']);
                    $("#esb-blockSubtitle").val(data['blockSubtitle']);
                    $("#esb-blockDescription").val(data['blockDescription']);
                    $("#esb-blockLink").val(data['blockLink']);
                    $("#esb-blockLinkDescription").val(data['blockLinkDescription']);

                    
                    // Start time
                    var hour = Math.floor(data['startTime']/60);
                    var min = data['startTime'] - (hour * 60);
                    $("#esb-startTimeHour").val(hour);
                    $("#esb-startTimeMinute").val(min);

                    // End time
                    var hour = Math.floor(data['endTime']/60);
                    var min = data['endTime'] - (hour * 60);
                    $("#esb-endTimeHour").val(hour);
                    $("#esb-endTimeMinute").val(min);

                    // Tournament rings
                    $('.esb-locationID').prop('checked', false);
                    data['locationIDs'].forEach(function(locationID){
                        $("#esb-location-"+locationID).prop('checked', true);
                    });

                    // Submit button text
                    $("#esb-submitButton").html("Update Schedule Block");

                }
            }
        };
    }

}

/**********************************************************************/

function logistics_esbRingCheck(checkboxID){

    if($("#esb-blockID").val() != 0 
        && $(checkboxID).prop('checked') == false){

        $("#esb-warningLog").html(`<li>Software is unpredictable if you remove locations 
            with staff assigned.<BR>You have been warned.`);
    }
}

/**********************************************************************/

function logistics_esbTournamentCheck(){

    if($("#esb-blockID").val() != 0 ){

        $("#esb-warningLog").html(`<li>Software is unpredictable if you change the 
            scheduled tournament with staff assigned.<BR>You have been warned.`);
    }
}

/**********************************************************************/

function logistics_esbBlockTypeCheck(){

    switch($("#esb-blockTypeID").val()){
        case '1': // 1 is the ID for a tournament block
            $("#esb-tournamentID-div").show();
            $("#esb-blockTitle-div").hide();
            $(".esb-matches-yes").show();
            $(".esb-matches-no").hide();
            break;
        case '2': // 2 is the ID for a class block
            $("#esb-tournamentID-div").hide();
            $("#esb-blockTitle-div").show();
            $(".esb-classes-yes").show();
            $(".esb-classes-no").hide();
            break;
        default:
            $("#esb-tournamentID-div").hide();
            $("#esb-blockTitle-div").show();
            $(".esb-location-checkbox").show();
            break;
    }

}

/**********************************************************************/

function logistics_esbSubmit(){

    var readyToSubmit = true;
    var errorLog = '';

// Check the tournament ID
    if(     $("#esb-blockTypeID").val() == 1 
        &&  $("#esb-tournamentID").prop('selectedIndex') == 0){

        errorLog = errorLog + "<li>No Tournament Selected</li>";
        readyToSubmit = false;
    }

// Check the Block Title
    if($("#esb-blockTypeID").val() == null ){
        errorLog = errorLog + "<li>No Block Type selected</li>";
        readyToSubmit = false;
    } else if(    $("#esb-blockTypeID").val() != 1 
               && $("#esb-blockTitle").val() == ''){
        
        errorLog = errorLog + "<li>No Block Name entered</li>";
        readyToSubmit = false;
    }

// Check the time input

    if( ($("#esb-startTimeHour").val() == '') && ($("#esb-startTimeMinute").val() == '') ){
        errorLog = errorLog + "<li>No Start Time Specified</li>";
        readyToSubmit = false;
    }

    if( ($("#esb-endTimeHour").val() == '') && ($("#esb-endtTimeMinute").val() == '') ){
        errorLog = errorLog + "<li>No End Time Specified</li>";
        readyToSubmit = false;
    }

    var startHour = parseInt($("#esb-startTimeHour").val()) || 0;
    var startMinute = parseInt($("#esb-startTimeMinute").val()) || 0;
    var endHour = parseInt($("#esb-endTimeHour").val()) || 0;
    var endMinute = parseInt($("#esb-endTimeMinute").val()) || 0;

    if( (startHour > endHour)
        || (   (startHour == endHour)
            && (startMinute >= endMinute) 
        )){
        errorLog = errorLog + "<li>End Time can not be before Start Time</li>";
        readyToSubmit = false;
    }

// Check the ring assignment
    if ($(".esb-ring-checkbox input:checkbox:checked").length == 0)
    {
        errorLog = errorLog + "<li>At least one ring must be chosen.</li>";
        readyToSubmit = false;
    }


// Set the error log
    $("#esb-errorLog").html(errorLog);

    if(readyToSubmit == true){
        $("#esb-tournamentID").attr("disabled",false);
        $("#esb-blockTypeID").attr("disabled", false);
        $("#esb-form").submit();
    } else {
        errorLog = "<strong><u>Errors in Form</u></strong>" + errorLog;
        $("#esb-errorLog").html(errorLog);
    }

}

/******************************************************************************/

function logistics_esbDeleteSubmit(){
    
    // Create form
    var myForm = document.createElement("form");
    myForm.method = 'POST';

    // Form Name
    var formName = document.createElement('input');
    formName.type = 'hidden';
    formName.value = 'deleteScheduleBlocks';
    formName.name = 'formName';
    myForm.appendChild(formName);

    // BlockID
    var what = document.createElement('input');
    what.type = 'hidden';
    what.value = $("#esb-blockID").val();
    what.name = 'deleteScheduleBlocks['+$("#esb-blockID").val()+']';
    myForm.appendChild(what);

    document.getElementsByTagName('body')[0].appendChild(myForm);

    myForm.submit();

}

/******************************************************************************/

function logistics_displayBlockDescription(blockID){


    var query = "mode=getScheduleBlockInfo&blockID="+blockID.toString();
    var xhr = new XMLHttpRequest();
    xhr.open("POST", AJAX_LOCATION+"?"+query, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();

    xhr.onreadystatechange = function (){
        if(this.readyState == 4 && this.status == 200){
            
            if(this.responseText.length > 1){ // If the fighter has already fought
             
                var data = JSON.parse(this.responseText);

                if(data['blockTypeID'] == 1){
                    $("#sbd-title").html(data['tournamentTitle']);
                } else {
                    $("#sbd-title").html(data['blockTitle']);
                }
                
                $("#sbd-subtitle").html(data['blockSubtitle']);
                $("#sbd-description").html(data['blockDescription']);
                $("#sbd-link").attr("href", data['blockLink']);
                $("#sbd-linkDescription").html(data['blockLinkDescription']);
                $("#sbd-time").html(data['startTimeHr']+' - '+data['endTimeHr']);
                

                $("#sbd-instructors").html("");
               
                if (data['instructors'] !== undefined && data['instructors'].length > 0) {

                    var text = "Instructor";
                    if(data['instructors'].length > 1){
                        text = text + "s";
                    }

                     $("#sbd-instructors").html(`<u>${text}</u>: `);

                    var instructNum = 0;
                    data['instructors'].forEach(function(instructor){
                        instructNum++;
                        if(instructNum > 1){
                             $("#sbd-instructors").append(', ');
                        }
                        $("#sbd-instructors").append(instructor['name']);
                    });
                }

                
               
                $("#sbd-modal").foundation("open");
            }
        }
    };

}


/******************************************************************************/
var logisticsLocationPaddlesUnchecked = [];

function logistics_locationsFormPaddleCheck(checkboxID){

    if($(checkboxID).prop("checked") === false){
        logisticsLocationPaddlesUnchecked[$(checkboxID).attr("id")] = '1';
    } else {
        delete logisticsLocationPaddlesUnchecked[$(checkboxID).attr("id")];
    }
}

/******************************************************************************/

function logistics_locationsFormSubmit(){

    if(Object.keys(logisticsLocationPaddlesUnchecked).length == 0){
        submitForm('ll-form','editLocations');
        $("#ll-form").submit();
    } else {
        $("#ll-confirm-modal").foundation("open");
    }
}

/******************************************************************************/

function logistics_staffShiftNumToAdd(){

    var numToAssign = $("#staffShift-numToAdd").val();

    for(var i = 1;i<=10;i++){
        if(i <= numToAssign){
            $(".add-staffShift-"+i).show();
        } else {
            $(".add-staffShift-"+i).hide();
            $(".staffShift-select-"+i).val(0);
        }
    }

}

/******************************************************************************/

function logistics_sdtToggle(classClicked){

    var buttonToToggle = "#"+classClicked+"-toggle-button";
    if($(buttonToToggle).hasClass("hollow") == true){
        $(buttonToToggle).removeClass("hollow");
    } else {
        $(buttonToToggle).addClass("hollow");
    }

    var showTournament = !$("#sdt-tournament-toggle-button").hasClass("hollow");
    var showWorkshop = !$("#sdt-workshop-toggle-button").hasClass("hollow");
    var showStaff = !$("#sdt-staff-toggle-button").hasClass("hollow");

    if(showTournament == true){
        $(".sdt-tournament").show();
    } else {
        $(".sdt-tournament").hide();
    }

    if(showWorkshop == true){
        $(".sdt-workshop").show();
    } else {
        $(".sdt-workshop").hide();
    }

    if(showStaff == true){
        $(".sdt-staff").show();
    } else {
        $(".sdt-staff").hide();
    }

    if(showTournament == true || showWorkshop == true){
        $(".sdt-multi").show();
    } else {
        $(".sdt-multi").hide();
    }

}

/******************************************************************************/

function logistics_toggleFloormap(){

    if($("#floor-map-toggle-button").hasClass("hollow") == true){
        $("#floor-map-toggle-button").removeClass("hollow");
    } else {
        $("#floor-map-toggle-button").addClass("hollow");
    }

    $("#floor-map").toggle();
}

/******************************************************************************/

function logistics_bulkAddStaff(shiftID){

    $("#bsa-shiftID").val(shiftID);
    $("#bulkStaffAssignBox").foundation("open");
}

/******************************************************************************/

function popOutSchedule(){

    var w = window.open();
    var is_chrome = Boolean(w.chrome);

    w.document.write(`
    <html>
    <head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/6.4.3/css/foundation.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.foundation.min.css">
    
    <link href="https://fonts.googleapis.com/css?family=Chivo:300,400,700" rel="stylesheet">
    <link rel="stylesheet" href="includes/foundation/css/app.css">
    <link rel="stylesheet" href="includes/foundation/css/custom.css">

    <style>
        @media print {
            fieldset {page-break-inside:avoid}
            body {
              -webkit-print-color-adjust: exact !important;
            }
        }
    </style>

    </head>
    <body>`);

    w.document.write(window.document.getElementById('print-schedule-header').innerHTML);

    w.document.write(window.document.getElementById('personal-schedule-div').innerHTML);


    w.document.write(`  
    <div class="large-12 cell text-right " style='border-top: 1px solid black; margin-top: 20px;'>
        <div class='grid-x grid-margin-x align-right'>
            <div class='shrink cell'>
                <strong>HEMA Scorecard</strong><BR>
                Developed by Sean Franklin <BR>
                A <em>HEMA Alliance</em> Project
            </div>
            <div class='shrink cell'>
                <img src='includes/images/hemaa_logo_s.png'>
            </div>

        </div>
    </div>`);


    w.document.write('</html>');
    

    if (is_chrome) {
        setTimeout(function() { // wait until all resources loaded 
            w.document.close(); // necessary for IE >= 10
            w.focus(); // necessary for IE >= 10
            w.print(); // change window to winPrint
            w.close(); // change window to winPrint
        }, 250);
    } else {
        w.document.close(); // necessary for IE >= 10
        w.focus(); // necessary for IE >= 10

        w.print();
        w.close();
    }

}

/******************************************************************************/