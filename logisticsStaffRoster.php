<?php
/*******************************************************************************
	Logistics Staff Roster

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Staff Roster';
$includeTournamentName = false;
$hideEventNav = true;
$jsIncludes[] = 'roster_management_scripts.js';
include('includes/header.php');


if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false) {
	pageError('user');
} elseif($_SESSION['isMetaEvent'] == true){
	redirect('infoSummary.php');
} else {

	// If they are just viewing the page without management permisions all the forms are locked.
	if(ALLOW['EVENT_MANAGEMENT'] == true){
		$formLock = '';
	} else {
		$formLock = 'disabled';
	}

	$staffRoster = logistics_getEventStaff($_SESSION['eventID']);
	$nonStaffRoster = logistics_getEventStaff($_SESSION['eventID'],false);
	$defaults = getEventDefaults($_SESSION['eventID']);

	if(isset($_SESSION['staffViewMode']) == false){
		$_SESSION['staffViewMode'] = 'name-asc';
	}

	$nameArrow = '';
	$compArrow = '';
	switch($_SESSION['staffViewMode']){
		case 'name-desc':
			$nameArrow = "&#8593";
			$nameMode = 'name-asc';
			$compMode = 'comp-desc';
			break;
		case 'comp-asc':
			$compArrow = "&#8595";
			$nameMode = 'name-asc';
			$compMode = 'comp-desc';
			break;
		case 'comp-desc':
			$compArrow = "&#8593";
			$nameMode = 'name-asc';
			$compMode = 'comp-asc';
			break;
		case 'name-asc':
		default:
			$nameArrow = "&#8595";
			$nameMode = 'name-desc';
			$compMode = 'comp-desc';
			break;
	}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<input type='hidden' id='eventID' value="<?=$_SESSION['eventID']?>">

<!-- Tabs -->
	<ul class="tabs" data-tabs id="staffRoster-tab">


		<li class="tabs-title is-active">
			<a data-tabs-target="panel-roster" href="#panel-roster">
				Staff Roster
			</a>
		</li>


		<li class="tabs-title">
			<a data-tabs-target="panel-all" href="#panel-all">
				Event Roster
			</a>
		</li>

	</ul>

<!-- Tab Content -->
	<div class="tabs-content" data-tabs-content="staffRoster-tab">


		<div class="tabs-panel is-active" id="panel-roster">
			<div class='grid-x grid-margin-x'>
			<div class='large-8 medium-10 cell'>

			<fieldset <?=$formLock?> >
			<form method='POST'>

				<input type='hidden' name='editStaffList[eventID]' value='<?=$_SESSION['eventID']?>'>
				<table class='stack'>
					<tr>
						<th onclick="changeParticipantOrdering('staffViewMode','<?=$nameMode?>')">
							<a>Staff Member <?=$nameArrow?></a>
						</th>
						<th  onclick="changeParticipantOrdering('staffViewMode','<?=$compMode?>')">
							<a>Competency <?=$compArrow?></a>
							<?=tooltip("You can assign numbers to staff members to help sort them by skill sets. Higher numbers are sorted to the top.
							<BR><u>Example:</u>
							<ol>
							<li>Table Staff</li>
							<li>Judges</li>
							<li>Directors</li>
							</ol>")?>
						</th>
						<th>
							Target Hours
							<?=tooltip("This is just for your information. You can always assign more or less.")?>
						</th>
					</tr>

					<?php
						foreach($staffRoster as $index => $staffInfo){
							editStaffEntry($index, $staffInfo);
						}

						if($formLock == null){
							for($i=-1;$i>=-5;$i--){
								addStaffEntry($i, $nonStaffRoster, $defaults);
							}
						}
					?>
				</table>

				<button class='button success' name='formName' value='editStaffList' <?=$formLock?> >
					Update Staff List
				</button>
				<button class='button alert' name='formName' value='deleteStaffList' <?=$formLock?> >
					Delete Selected
				</button><BR>
				<em>(Deleting a staff member will remove them from all of their shifts, but not from matches
				they have been checked in to.)</em>

			</form>
			</fieldset>

			</div>
			</div>
		</div>


		<div class="tabs-panel" id="panel-all">
			<?=fullRosterDisplay($defaults)?>
		</div>

	</div>





<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

/******************************************************************************/

function fullRosterDisplay($defaults){

	$roster = getEventRoster(null, true);


	foreach($roster as $person){

		$tmp['nameStart'] = "editStaffList-{$person['rosterID']}";
		$tmp['name'] = getFighterName($person['rosterID']);
		$tmp['rosterID'] = $person['rosterID'];

		if($person['staffCompetency'] != 0){
			$tmp['comp'] = $person['staffCompetency'];
			$tmp['target'] = $person['staffHoursTarget'];
			$tmp['nameClass'] = 'bold';
			$tmp['checked'] = 'checked';
		} else {
			$tmp['comp'] = $defaults['staffCompetency'];
			$tmp['target'] = $defaults['staffHoursTarget'];
			$tmp['nameClass'] = '';
			$tmp['checked'] = '';
		}

		$rosterList[] = $tmp;
	}

?>
	<div class='callout primary'>
		I'm trying something new for form inputs here. <u>On this section alone</u> the data is updated as soon as it changes, without needing to submit. (It won't appear in the "Staff Roster" tab until you reload the page though.)<BR>
		<a class='button tiny no-bottom secondary' href='logisticsStaffRoster.php'>Reload Page</a>
	</div>

	<div class='grid-x grid-margin-x'>
	<div class='large-6 medium-11 cell'>

	<table>
		<tr >
			<th white-space: nowrap>Are Staff</th>
			<th>Name</th>
			<th>Staff Competency</th>
			<th>Staff Hours Target</th>
		</tr>


	<?php foreach($rosterList as $r):?>
		<tr>

			<td>

				<div class='switch text-center no-bottom'>
					<input class='switch-input edit-staff-list' type='checkbox'
						id='<?=$r['nameStart']?>-isStaff' <?=$r['checked']?> data-rosterID=<?=$r['rosterID']?>>
					<label class='switch-paddle' for='<?=$r['nameStart']?>-isStaff'>
					</label>
				</div>

			</td>

			<td class='<?=$r['nameClass']?> no-wrap'>
				<?=$r['name']?>
			</td>

			<td>
				<select class='edit-staff-list' id='<?=$r['nameStart']?>-staffCompetency' data-rosterID=<?=$r['rosterID']?>>
					<?php for($i=1;$i<=STAFF_COMPETENCY_MAX;$i++):?>
						<option <?=optionValue($i,$r['comp'])?> >
							<?=$i?>
						</option>
					<?php endfor ?>
				</select>
			</td>

			<td>
				<input class='no-bottom edit-staff-list' type='number' min="0" value="<?=$r['target']?>"
					id="<?=$r['nameStart']?>-staffHoursTarget" data-rosterID=<?=$r['rosterID']?>>
			</td>

		</tr>
	<?php endforeach ?>

	</table>

	</div>
	</div>

<?php
}

/******************************************************************************/

function editStaffEntry($index, $info){
?>

	<tr>
		<td>
			<input type='checkbox' name="editStaffList[deleteList][<?=$index?>]"
			value='<?=$info['rosterID']?>' class='no-bottom'>

			<?=getFighterName($info['rosterID'])?>
			<input type='hidden' name="editStaffList[staffList][<?=$index?>][rosterID]"
				value='<?=$info['rosterID']?>' >
		</td>
		<td>
			<select class='no-bottom' name="editStaffList[staffList][<?=$index?>][staffCompetency]">
				<?php for($i=1;$i<=STAFF_COMPETENCY_MAX;$i++): ?>
					<option <?=optionValue($i,$info['staffCompetency'])?> > <?=$i?> </option>
				<?php endfor ?>
			</select>
		</td>
		<td>
			<input type='number' class='no-bottom' value='<?=$info['staffHoursTarget']?>'
				name="editStaffList[staffList][<?=$index?>][staffHoursTarget]">
		</td>
	</tr>

<?php
}

/******************************************************************************/

function addStaffEntry($index, $roster, $defaults){
?>

	<tr>
		<td>
			<select name="editStaffList[staffList][<?=$index?>][rosterID]" class='no-bottom'>
				<option></option>
				<?php foreach($roster as $member):
					$rosterID = $member['rosterID'];
					?>
					<option <?=optionValue($rosterID,null)?> >
						<?=getFighterName($rosterID)?>
					</option>
				<?php endforeach ?>
			</select>
		</td>
		<td>
			<select class='no-bottom' name="editStaffList[staffList][<?=$index?>][staffCompetency]">
				<?php for($i=1;$i<=STAFF_COMPETENCY_MAX;$i++): ?>
					<option <?=optionValue($i,$defaults['staffCompetency'])?> > <?=$i?> </option>
				<?php endfor ?>
			</select>
		</td>
		<td>
			<input type='number' class='no-bottom' value='<?=$defaults['staffHoursTarget']?>'
				name="editStaffList[staffList][<?=$index?>][staffHoursTarget]">
		</td>
	</tr>

<?php
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
