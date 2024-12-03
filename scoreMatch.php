<?php
/*******************************************************************************
	Match Scoring

	Scores a match

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Match Score';
$hideEventNav = true;
$hidePageTitle = true;
$lockedTournamentWarning = true;
$jsIncludes[] = 'score_scripts.js';
$jsIncludes[] = 'video_scripts.js';
include('includes/header.php');

define('STAFF_CHECK_IN_TO_SHOW', 7);
define('STAFF_CHECK_IN_MAX',     12);

$matchID = $_SESSION['matchID'];
$tournamentID = $_SESSION['tournamentID'];
$eventID = $_SESSION['eventID'];

if($matchID == null || $tournamentID == null || $eventID == null){
	if(ALLOW['VIEW_SETTINGS'] == true){
		displayAlert("No Match Selected<BR><a href='poolMatches.php'>Match List</a>");
	} elseif($eventID == null){
		redirect('infoSelect.php');
	} elseif($tournamentID == null){
		redirect('infoSummary.php');
	} elseif($matchID == null){
		redirect('participantsEvent.php');
	} else {
		displayAlert("No Match Selected<BR><a href='poolMatches.php'>Match List</a>");
	}
} elseif(ALLOW['VIEW_MATCHES'] == false) {
	displayAlert("Event is still upcoming<BR>And you clearly got on this page by unorthodox means");
} else {

	$matchInfo = getMatchInfo($matchID, $tournamentID);

	if(isset($_SESSION['restartTimer'])){
		$matchInfo['restartTimer'] = true;
		unset($_SESSION['restartTimer']);
	} else {
		$matchInfo['restartTimer'] = false;
	}

// If there are prior penalties then let the table know.
	$isPriorPenalties = (bool)priorPenaltiesDisplayBox($matchInfo);
	define("IS_PRIOR_PENALTIES", $isPriorPenalties);

// If it is the last match in the tournament the staff is asked to finalize the event
	askForFinalization($matchInfo);

// If the livestream is active it asks to make this the displayed match
	livestreamMatchSet($matchInfo);

// Checks if the user has left unconcluded matches, and warns them
	$matchInfo['unconcludedMatchWarning'] = unconcludedMatchWarning($matchInfo);

//Passes data to Javascript
	echo "<input type='hidden' value='{$matchInfo['doubleType']}' id='doubleType'>";

// Auto refresh if match is in progress
	if(($matchInfo['lastExchange'] != null || $matchInfo['matchTime'] > 0) && $matchInfo['matchComplete'] == 0
		&& $matchInfo['ignoreMatch'] != 1 && ALLOW['EVENT_SCOREKEEP'] == false){
		echo "<script>window.onload = function(){refreshOnNewExchange($matchID, {$matchInfo['lastExchange']});}</script>";
	}

	$subMatchLock = subMatchBox($matchInfo);

	if(LOCK_TOURNAMENT != null || $subMatchLock == true){
		define("LOCK_MATCH", 'disabled');
	} else {
		define("LOCK_MATCH", '');
	}


	$attackDisplayMode = readOption('T',$matchInfo['tournamentID'],'ATTACK_DISPLAY_MODE');

	if($attackDisplayMode == ATTACK_DISPLAY_MODE_GRID){
		gridScoreBoxes($matchInfo);
	}

// Check to see if the match should be locked from ANY input because the staff check-in is mandatory.
	$lockMatchForStaffCheckIn = false;
	$matchName = "";

	$checkInLevel = logistics_getTournamentStaffCheckInLevel($matchInfo['tournamentID']);
	if($checkInLevel == STAFF_CHECK_IN_MANDATORY && ALLOW['EVENT_SCOREKEEP'] == true){

		$lockMatchForStaffCheckIn = !logistics_areMatchStaffCheckedIn($matchID);
		$matchName = '<b>'.getFighterName($matchInfo['fighter1ID'])."</b> vs <b>".getFighterName($matchInfo['fighter2ID'])."</b><BR>";

		if($matchInfo['matchType'] == 'pool'){
			$matchName .= "<i>".$matchInfo['groupName'].", Match ".$matchInfo['matchNumber']."</i>";
		} else {
			$matchName .= "<i>Bracket</i>";
		}

	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<script>
		var DATA_ENTRY_MODE = <?=$attackDisplayMode?>;
	</script>

	<!-- Set the timer mode -->
	<input type='hidden' id='timerCountdown' value='<?=isTimerCountdown($tournamentID)?>'>

<!-- Warning if match is ignored -->
	<?php if($matchInfo['ignoreMatch'] == 1): ?>
		<div class='callout secondary text-center'>
			<span class='red-text'>This match has been excluded from scoring calculations</span>
			<BR>Possible reasons include injury or disqualification from the tournament
		</div>

	<?php endif ?>

	<div class='callout alert text-center hidden editExchangeWarningDiv'>
		<strong>Warning!</strong><BR>
		You are editing an old exchange, not inserting a new one!<BR>
		<a class='button alert hollow' onclick="editExchange('')">Cancel Editing</a>
	</div>


	<div class='grid-x grid-margin-x'>

<!-- Main column -->
		<div class='medium-9 cell'>
			<?php backToListButton($matchInfo); ?>
			<?php confirmStaffBox($matchInfo) ?>
			<?php addPenaltyBox($matchInfo) ?>
			<?php switchFighersBox($matchInfo) ?>

			<!-- Fighter scores -->

			<?php if($lockMatchForStaffCheckIn == false): ?>
				<div class='large-12 cell'>


					<form method='POST'>
					<fieldset <?=LOCK_MATCH?>>
						<input type='hidden' name='formName' value='newExchange'>
						<input type='hidden' name='lastExchangeID' value='<?=$matchInfo['lastExchange']?>'>
						<input type='hidden' name='matchID' value='<?=$matchID?>' id='matchID'>
						<input type='hidden' class='matchTime' name='matchTime' value='<?=$matchInfo['matchTime']?>'>
						<input type='hidden' class='exchangeID' name='score[exchangeID]' id='exchangeID'>
						<?php dataEntryBox($matchInfo);	?>
					</fieldset>
					</form>

					<?php if(	ALLOW['EVENT_SCOREKEEP'] == true
							 && $matchInfo['matchComplete'] == 1
							 && isSignOffRequired($matchInfo['tournamentID'])
							){

							signOffForm($matchInfo);
					}?>


				</div>
			<?php else: ?>
				<div class='grid-x grid-margin-x'>

				<div class='cell' style='margin-top:30px;'>
					<?=$matchName?>
				</div>


				<div class='small-2 cell'></div>

				<a data-open='matchStaffConfirmBox'>
				<div class='small-8 cell callout alert text-center clickable'
					style='margin-top:50px;margin-bottom:100px;' >

					<h3>
						Match Staff must be confirmed<BR>
						(click here)

					</h3>
				</div>
				</a>
				</div>
				<div class='small-2 cell'></div>
			<?php endif?>


			<?php if(ALLOW['EVENT_SCOREKEEP'] == false): ?>
				<BR>
			<?php endif ?>


		</div>

	<!-- Side column -->
		<div class='medium-3 cell text-center callout'>
			<?php createSideBar($matchInfo); ?>
		</div>

	<!-- Exchange history -->
		<?php $exchangesNotNumbered = matchHistoryBar($matchInfo); ?>

	</div>

<!-- Match Video -->
	<?php addVideoLink($matchID);?>

<?php }

include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/******************************************************************************/

function signOffForm($matchInfo){

	$signOffs = getMatchSignOff($matchInfo['matchID']);

?>

	<form method="POST" id='sign-off-form'>
	<input class='hidden' name='formName' value='signOffFighters'>
	<input class='hidden' name='signOffInfo[matchID]' value='<?=$matchInfo['matchID']?>'>

	<div class='callout warning grid-x grid-margin-x text-center'>
	<div class='small-12'>
		<em>Clicking this box is considered a digital signature by the named individual.</em>
	</div>

	<div class='small-6 cell'>
		<h3><?=getFighterName($matchInfo['fighter1ID'])?></h3>
	</div>
	<div class='small-6 cell'>
		<h3><?=getFighterName($matchInfo['fighter2ID'])?></h3>
	</div>

	<div class='small-6 cell'>
		<?php if($signOffs['signOff1'] == 0): ?>
			<BR>
			<input type='checkbox' class='no-bottom' style="transform: scale(6)"
				name = 'signOffInfo[1]' value='1'
				onclick="$('#sign-off-form').submit()">
			<BR>
			<BR>
		<?php else: ?>
			<h3 class='no-bottom success-text'>Signed ✅</h3>
		<?php endif ?>
	</div>

	<div class='small-6 cell'>

		<?php if($signOffs['signOff2'] == 0): ?>
			<BR>
			<input type='checkbox' class='no-bottom' style="transform: scale(6)"
				name = 'signOffInfo[2]' value='1'
				onclick="$('#sign-off-form').submit()">
			<BR>
			<BR>
		<?php else: ?>
			<h3 class='no-bottom success-text'>Signed ✅</h3>
		<?php endif ?>
	</div>

	</div>
	</form>

<?php
}

/******************************************************************************/

function subMatchBox($matchInfo){

	if($matchInfo['isPlaceholder'] == 0 && $matchInfo['placeholderMatchID'] == null){
		return;
	}

	if($matchInfo['isPlaceholder'] == 1){
		$mainMatchID = $matchInfo['matchID'];
	} else {
		$mainMatchID = $matchInfo['placeholderMatchID'];
	}

	$allParts = getSubMatchParts($mainMatchID);
	$lockMatch = false;
	$showOverall = true;

	// If there are sub matches, redirect to the first one.
	if(ALLOW['EVENT_SCOREKEEP'] == true){

		$uncompletedSubMatchID = 0;
		foreach($allParts as $subMatch){
			if($subMatch['isPlaceholder'] == 1){
				continue;
			}

			if($subMatch['matchComplete'] == 0){
				$uncompletedSubMatchID = (int)$subMatch['matchID'];
				break;
			}
		}

		if($uncompletedSubMatchID != 0){
				if(   $_SESSION['matchID'] != $uncompletedSubMatchID
				   && $matchInfo['isPlaceholder'] == 1){

				$_SESSION['matchID'] = $uncompletedSubMatchID;
				redirect("scoreMatch.php");

			} else{
				$showOverall = false;
			}
		}
	}
//---------------------
?>

	<div class='callout small'>
		<form method='POST'>
			<input type='hidden' name='formName' value='goToMatch'>

			<?php foreach($allParts as $match):

				if($match['isPlaceholder'] == 1){
					$name = "Overall Result";
					if($showOverall == false){
						continue;
					}
				} else {
					$name = "Phase {$match['matchNumber']}";

					// If the parts aren't complete don't let them change the match info
					if($matchInfo['isPlaceholder'] == 1 && $match['matchComplete'] != 1){
						$lockMatch = true;
					}
				}
				if($match['matchID'] != $matchInfo['matchID']){
					$class = 'hollow';
				} else {
					$class = '';
				}


				if($match['matchComplete'] == 1 || $match['ignoreMatch'] == 1){
					if($match['isPlaceholder'] == 0){
						$class.=' secondary';
					}
				} elseif($match['isPlaceholder'] == 1){
					$class.=' secondary';
				}

				?>
				<button class='button no-bottom <?=$class?>' value='<?=$match['matchID']?>' name='matchID'>
					<?=$name?>
				</button>
			<?php endforeach ?>

		</form>

		<?php if(ALLOW['EVENT_SCOREKEEP'] == true && $matchInfo['isPlaceholder'] == 1): ?>
			<span class='red-text'>This is the match summary, not an actual match.</span>
			<?php if($lockMatch == true): ?>
				You can not manualy overide the data until all sub-matches are concluded.
			<?php else: ?>
				<strong>Do not mess with anything here unless you really know what you are doing.</strong>
			<?php endif ?>
		<?php endif ?>

	</div>

<?php
	return $lockMatch;
}

/******************************************************************************/

function confirmStaffBox($matchInfo){

	if(ALLOW['EVENT_SCOREKEEP'] == FALSE){
		return;
	}

	$rolesList = logistics_getRoles();
	$staffList = getEventRoster();
	$matchStaffList = logistics_getMatchStaffSuggestion($matchInfo);
	$hideRows = '';
	$possibleStaffShifts = logistics_getMatchStaffShifts($matchInfo);
	$rowCounter = 0;

?>

	<div class='reveal' id='matchStaffConfirmBox' data-reveal>

		<h4>Check In Staff

			<?php if($possibleStaffShifts != null): ?>
				<a class='button hollow small no-bottom' data-open='matchStaffLoadShiftBox'>
					Get Staff From Schedule
				</a>

			<?php endif ?>

		</h4>

		<?php if(IS_PRIOR_PENALTIES == true):?>
			<BR>
			<a class='button no-bottom hollow alert expanded' data-open='veiwPenaltiesBox'>
				! One Or More Fighters Have Prior Penalties !
			</a>
			<BR>
		<?php endif ?>

		<form method='POST' id='update-match-staff'>
		<input type='hidden' name='updateMatchStaff[matchID]' value='<?=$matchInfo['matchID']?>'>


	<!-- Top button bar -->

	<!-- Instructions ------>
		<em>
			<?=toggleClass('check-in-staff-instructions','(How Does This Work? &#8595;)','(Got It &#8593;)')?>
		</em>

		<div class='hidden check-in-staff-instructions callout'>
			<li>Start typing in name and <n>use auto-complete to select staff</n>. <u>You can only add staff from the existing list</u>.</li>
			<li>The red 'X' clears the text field. </li>
			<li>Leaving a field empty will clear any staff that was in it.</li>
			Status of the entry is shown on the left:<BR>
			[&nbsp;&nbsp;&nbsp;] w/ white/grey background: <i>Already Checked In </i><BR>
			[<strong>?</strong>] w/ blue background: <i>Pending Changes</i> <BR>
			[<strong>!!</strong>] w/ orange background: <i>Invalid Name (use the drop down names only)</i> <BR>
		</div>

	<!-- Datalist -------------->
		<datalist id="staff-select-datalist">
			<?php foreach($staffList as $s): ?>
				<option data-value='<?=$s['rosterID']?>'><?=getFighterName($s['rosterID'])?></option>
			<?php endforeach ?>
		</datalist>

	<!-- Data entry fields -->
		<table>
			<?php foreach($matchStaffList as $member):

				$rowCounter++;
				$i = $rowCounter;
				if($member['matchStaffID'] < 0){
					$isAlreadySet = '?';
					$isPending = 'background-primary-light';
				} else {
					$alreadySet[$member['rosterID']] = true;
					$isAlreadySet = '&nbsp';
					$isPending = '';
				}
				?>
				<tr>
					<th id='staff-select-<?=$i?>-status' class='staff-select-<?=$i?>-status <?=$isPending?>' style='font-size: 1.5em;'>
						<?=$isAlreadySet?>
					</th>
					<td  class='staff-select-<?=$i?>-status <?=$isPending?>'>

						<input class='input-datalist' list="staff-select-datalist"

							placeholder="- empty -"
							data-name='updateMatchStaff[staffList][<?=$i?>][rosterID]'
							data-id='staff-select-<?=$i?>'
							value='<?=getFighterName($member['rosterID'])?>'
							onchange="validateStaffSelection('staff-select-<?=$i?>')">

						<a class='button alert hollow no-bottom tiny' onclick="clearDatalist('staff-select-<?=$i?>')">x</a>

					</td>
					<td  class='staff-select-<?=$i?>-status <?=$isPending?>'>

						<select  name='updateMatchStaff[staffList][<?=$i?>][logisticsRoleID]'>
							<?php foreach($rolesList as $role):?>
								<option <?=optionValue($role['logisticsRoleID'],$member['logisticsRoleID'])?> >
									<?=$role['roleName']?>
								</option>
							<?php endforeach ?>
						</select>

					</td>

			<?php endforeach ?>


		<!-- Add New Staff -->
			<?php while($rowCounter < STAFF_CHECK_IN_MAX): // Negative values for new staff
				$rowCounter++;
				$i = $rowCounter;
				if($i > STAFF_CHECK_IN_TO_SHOW){
					$hideRows = "class='hiddenCheckStaffRow hidden'";
				}
				?>
				<tr <?=$hideRows?> >
					<th  id='staff-select-<?=$i?>-status'>
						<!-- Always empty -->
					</th>

					<td>
						<input data-id="staff-select-<?=$i?>" list="staff-select-<?=$i?>-datalist"  class='input-datalist' data-name='updateMatchStaff[staffList][<?=$i?>][rosterID]' placeholder="- empty -" onchange="validateStaffSelection('staff-select-<?=$i?>')">
							<datalist id="staff-select-<?=$i?>-datalist">
							<?php foreach($staffList as $s): ?>
								<option data-value='<?=$s['rosterID']?>'><?=getFighterName($s['rosterID'])?></option>
							<?php endforeach ?>
						</datalist>

						<a class='button alert hollow no-bottom tiny' onclick="clearDatalist('staff-select-<?=$i?>')">x</a>
					</td>

					<td>
						<select  name='updateMatchStaff[staffList][<?=$i?>][logisticsRoleID]'>
							<?php foreach($rolesList as $role):?>
								<option <?=optionValue($role['logisticsRoleID'],null)?> >
									<?=$role['roleName']?>
								</option>
							<?php endforeach ?>
						</select>
					</td>
				</tr>
			<?php endwhile ?>

		</table>

		<em>
			<?=toggleClass('hiddenCheckStaffRow','(Show More Rows &#8595;)','(Show Less Rows &#8593;)')?>
		</em>
		<HR>


	<!-- Submit buttons -->
		<?php if(isset($alreadySet) == true){
			$bText = 'Update Staff';
			$bClass= 'hollow';
		} else {
			$bText = 'Check In Staff';
			$bClass = '';
		} ?>

		<div class='grid-x grid-margin-x'>

			<a class='button success small-6 cell <?=$bClass?>'
				onclick="submitForm('update-match-staff', 'updateMatchStaff')">
				<?=$bText?>
			</a>

			<button class='button secondary small-6 cell' data-close aria-label='Close modal' type='button'>
				Cancel
			</button>
		</div>



		</form>

		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>

	</div>

<!-- Load existing shift staff -->
	<div class='reveal' id='matchStaffLoadShiftBox' data-reveal>
		<form method="POST">

			<input type='hidden' name='updateMatchStaffFromShift[matchID]'
				value='<?=$matchInfo['matchID']?>'>

			<h4>Load Staffing Shift</h4>
			Shifts avaliable for
			<em><?=getTournamentName($matchInfo['tournamentID'])?></em>,
			<strong><?=logistics_getLocationName($matchInfo['locationID'])?></strong>

			<div class='input-group'>
				<span class='input-group-label'>
					Load Shift:
				</span>
				<select class='input-group-field' name='updateMatchStaffFromShift[shiftID]'>
					<?php foreach($possibleStaffShifts as $shift): ?>
						<option value='<?=$shift['shiftID']?>'>
							<?=$shift['blockSubtitle']?>
							<?=min2hr($shift['startTime'])?> - <?=min2hr($shift['endTime'])?>
						</option>
					<?php endforeach ?>
				</select>
			</div>

			<div class='red-text'>
				<strong><u>Warning</u>:</strong> This will over-write any staff you have already assigned
			</div>


		<!-- Submit buttons -->
			<div class='grid-x grid-margin-x'>

				<button class='button success small-6 cell' name='formName' value='updateMatchStaffFromShift'>
					Confirm
				</button>

				<button class='button secondary small-6 cell'
					data-close aria-label='Close modal' type='button'>
					Cancel
				</button>

			</div>

		</form>

		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>

	</div>


<?php
}

/******************************************************************************/

function livestreamMatchSet($matchInfo){

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	$streamInfo = getStreamForLocation($matchInfo['locationID']);

	if(   ($streamInfo == [])
	   || ($streamInfo['isLive'] != 1 || $streamInfo['overlayEnabled'] != 1)
	   || ($streamInfo['matchID'] == $matchInfo['matchID']))
	{
		return;
	}

?>

	<form method='POST' onclick="this.submit()" class='pointer'>
	<input type='hidden' name='formName' value='videoStreamSetMatch'>
	<input type='hidden' name='videoStreamSetMatch[matchID]' value='<?=$matchInfo['matchID']?>'>
	<input type='hidden' name='videoStreamSetMatch[videoID]' value='<?=$streamInfo['videoID']?>'>
	<input type='hidden' name='videoStreamSetMatch[locationID]' value='<?=$matchInfo['locationID']?>'>

	<div class='callout alert text-center'>
		This match is currently not displayed on the livestream overlay<BR>
		<a>Change to Active Match</a>
	</div>
	</form>


<?php }

/******************************************************************************/

function askForFinalization($matchInfo){
/*	After the final match of a tournament has concluded this will prompt the
	scorekeeper to finalize the tournament results */

	if(    ALLOW['EVENT_SCOREKEEP'] == false
		|| LOCK_TOURNAMENT != null){ return;}

	$tournamentID = (int)$matchInfo['tournamentID'];

	$finalize = false;
	$finalize_bracket = false;
	if(isset($_SESSION['askForFinalization']) == true){

		$finalize = true;
		if(isBrackets($tournamentID) == true){
			$finalize_bracket = true;
			autoFinalizeSpecificationBox($tournamentID);
		} else {
			$finalize_bracket = false;
		}

	}


	$extend = false;
	$shorten = false;
	if(    $matchInfo['bracketLevel'] == 1
		&& $matchInfo['bracketPosition'] > 1
		&& $matchInfo['groupNumber'] == BRACKET_PRIMARY){

		$bracketInfo = getBracketInformation($tournamentID);

		if(    $bracketInfo['elimType'] == ELIM_TYPE_LOWER_BRACKET
			&& $matchInfo['winnerID'] != null){

			$extend = true;
		}

		if(    $bracketInfo['elimType'] == ELIM_TYPE_TRUE_DOUBLE
			&& $matchInfo['bracketPosition'] >= 2
			&& $matchInfo['lastExchange'] == null){
			$shorten = true;
		}

	}

	if($finalize == false && $extend == false && $shorten == false){
		return;
	}



	unset($_SESSION['askForFinalization']);
	?>

	<div class='callout alert text-center'>
	<form method='POST'>
	<input type='hidden' name='tournamentID' value='<?=$tournamentID?>'>

		<?php if($extend == true): ?>
			<p>Would you like to add second finals match to this double elim?</p>
		<?php endif ?>

		<?php if($finalize == true): ?>
			<p>This appears to be the last match of the tournament.
			Would you like to finalize the results?</p>
		<?php endif ?>

		<?php if($shorten == true): ?>
			Would you like to delet this match and have the previous match stand as the final result?
			<BR>(Ignore this dialogue if you don't want to delete the match)
			<BR>
			<button class='button no-bottom alert' name='formName' value='removeTrueDoubleElim'>
				Yes, Delete this match.
			</button>

		<?php else: ?>
			<HR>
			<?php if($extend == true): ?>
				<button class='button no-bottom' name='formName' value='createTrueDoubleElim'>
					Add Match
				</button>
			<?php endif ?>

			<?php if($finalize_bracket == true): ?>
				<a class='button no-bottom' data-open='autoFinalizeBox-<?=$tournamentID?>'>
					Finalize Tournament
				</a>
			<?php elseif($finalize == true): ?>
				<button class='button no-bottom' name='formName' value='autoFinalizeTournament'>
					Finalize Tournament
				</button>
			<?php endif ?>

			<button class='button secondary no-bottom'>
				Do Nothing
			</button>
		<?php endif ?>
	</form>
	</div>

<?php }


/******************************************************************************/

function backToListButton($matchInfo){
/* Creates a button to go back to the match list. The location is context dependent
	Attempting to enable the bracket helper - Buttons for list and bracket
	Pool match - Button returns to pool
	Winners bracket match - Button returns to winners bracket
	Consolation bracket match - Button returns to consolation bracket*/

	if($matchInfo['placeholderMatchID'] != null){
		$matchID = $matchInfo['placeholderMatchID'];
	} else {
		$matchID = $matchInfo['matchID'];
	}

	$name = getTournamentName();

	if(ALLOW['EVENT_SCOREKEEP'] == true){
		$hideForSmall = 'hide-for-small-only';
	} else {
		$hideForSmall = '';
	}
	?>

	<div class='grid-x align-middle grid-padding-x cell'>

	<div class='medium-shrink small-12 cell' style='margin-bottom: 10px;'>
	<?php if($_SESSION['bracketHelper'] == 'try'): ?>
		<a class='button no-bottom <?=$hideForSmall?>' href='finalsBracket.php'>
			Back To Bracket
		</a>
		<a class='button no-bottom <?=$hideForSmall?>' href='poolMatches.php#anchor{<?=$matchID?>'>
			Back To Match List
		</a>

	<?php elseif($matchInfo['matchType'] == 'pool'): ?>
		<a class='button small-expanded no-bottom  <?=$hideForSmall?>' href='poolMatches.php#anchor<?=$matchID?>'>
			Back To Match List
		</a>

	<?php else: ?>

		<a class='button no-bottom <?=$hideForSmall?>' href='finalsBracket.php#anchor<?=$matchID?>'>
			Back To Bracket
		</a>

	<?php endif ?>

	<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
		<a class='button no-bottom hollow hide-for-small-only'
			onclick="window.open('scoreMatchDisplay.php','scoreDisplayWindow','toolbar=0,location=0,menubar=0')">
			Display Window
			<?=tooltip("Opens a display window to put onto a projector. This window will <u>not</u> update itself when you switch to a different match. You will need to click the <strong>Display Window</strong> button to have the display window update to your currently active match.")?>
		</a>

		<?php if(IS_PRIOR_PENALTIES == true):?>
			<a class='button no-bottom hollow alert' data-open='veiwPenaltiesBox'>
				! Prior Penalties !
			</a>
		<?php endif ?>

	<?php endif ?>

	</div>

	<!-- Tournament name -->
	<div class='auto text-center cell hide-for-small-only' >
		<h5 class='inline-block'><?=$name?></h5>
	</div>

	</div>

<?php }

/******************************************************************************/

function dataEntryBox($matchInfo){
//	The main data entry box for the match. Contains boxes for each fighter
//	and the scorekeepers box bellow. Scorekeepers box only visiable if logged in.

	$doubleTypes = getDoubleTypes($matchInfo['tournamentID']);

	define("RE_OPEN_NO",0);
	define("RE_OPEN_ANY",1);
	define("RE_OPEN_CHECK",2);

	$reOpenStatus = RE_OPEN_NO;
	if(    $matchInfo['matchComplete'] == SQL_TRUE
		&& ALLOW['EVENT_SCOREKEEP'] == true){


		$signOffInfo = getMatchSignOff($matchInfo['matchID']);
		$numSignOff = (int)$signOffInfo['signOff1'] + (int)$signOffInfo['signOff2'];

		if($numSignOff == 0){
			$reOpenStatus = RE_OPEN_ANY;
		} elseif($numSignOff == 1){
			$reOpenStatus = RE_OPEN_CHECK;
		} elseif(ALLOW['EVENT_MANAGEMENT'] == true) {
			$reOpenStatus = RE_OPEN_CHECK;
		} else {
			$reOpenStatus = RE_OPEN_NO;
		}

	}
?>
	<?php if(ALLOW['EVENT_SCOREKEEP'] == true):?>
		<a onclick="$('#more-options-div').removeClass('hide-for-small-only')" class='show-for-small-only'>Show Exchange Types↓</a>
	<?php endif ?>
<!-- Score boxes for individual fighters -->

	<div class='grid-x grid-margin-x'>

		<?php
			if(isset($_SESSION['flipMatchSides']) && $_SESSION['flipMatchSides'] == true)
			{
				fighterDataEntryBox($matchInfo,2);
				fighterDataEntryBox($matchInfo,1);
			} else {
				fighterDataEntryBox($matchInfo,1);
				fighterDataEntryBox($matchInfo,2);
			}
		?>
	</div>


	<?php if(ALLOW['EVENT_SCOREKEEP'] == false){ return; } ?>

	<!-- If match is complete the only option is to re-open it -->
	<?php if($reOpenStatus != RE_OPEN_NO): ?>
		<div class='large-12 cell'>
			<?php if($reOpenStatus == RE_OPEN_CHECK): ?>

				<a class='re-open-match' onclick="toggleClass('re-open-match')">
					Unlock Match
				</a>
				<span class='re-open-match hidden'>
					<HR>
					<h4>
						<strong class='red-text'>Warning!</strong> Re-Opening the match will
						<u>delete</u> the sign-off signatures.
					</h4>
					<HR class='no-bottom'>
			<?php else: ?>
				<span>
			<?php endif ?>


			<BR>
			<button class='button success large ' id='New_Exchange_Button'
				name='lastExchange' value='clearLastExchange' <?=LOCK_MATCH?>>
				Re-Open Match
			</button>


			</span>

		</div>

		<?php return; ?>
	<?php endif ?>

<!-- Return if match is over -->
	<?php if($matchInfo['matchComplete'] == SQL_TRUE){ return; } ?>

<!-- Scoring form fields -->
	<div class='large-12 cell'>
	<div class='grid-x grid-margin-x grid-padding-x align-middle'>


	<div class='medium-6 cell'>

	<table class='hide-for-small-only' id='more-options-div'>

	<!-- No Exchange -->
	<tr>
		<td>No Exchange</td>
		<td>
			<div class='switch no-bottom'>
			<input class='switch-input no-bottom' type='radio' name='mod'
				value='noExch' id='No_Exchange_Radio' checked
				onchange="modifiersRadioButtons()">
			<label class='switch-paddle' for='No_Exchange_Radio'>
			</label>
			</div>
		</td>
	</tr>

	<!-- Double Hit -->
	<?php if(isDoubleHits($matchInfo['tournamentID'])): ?>
		<tr>
			<td>Double Hit</td>
			<td>
				<div class='switch no-bottom'>
				<input class='switch-input' type='radio' name='mod'
					value='doubleHit'id='Double_Hit_Radio'
					onchange="modifiersRadioButtons()">
				<label class='switch-paddle' for='Double_Hit_Radio'>
				</label>
				</div>
			</td>
		</tr>
	<?php endif ?>


<!-- Clear last exchange -->
	<tr>
		<td>
			<span class='Clear_Last_Radio'>Clear Last Exchange</span>
		</td>
		<td>
			<div class='switch no-bottom'>
			<input class='switch-input' type='radio' name='mod'
				value='clearLast' id='Clear_Last_Radio'
				onchange="modifiersRadioButtons()">
			<label class='switch-paddle' for='Clear_Last_Radio'>
			</label>
			</div>
		</td>
	</tr>

	<?php if($matchInfo['teamEntry'] == true && isTeamLogic($matchInfo['tournamentID']) == false): ?>
	<tr>
		<td>Switch Fighters</td>
		<td>
			<a class='button hollow warning no-bottom small' data-open='switchFightersBox'>
				Switch
			</a>
		</td>
	</tr>
	<?php endif ?>


<!-- Penalty -->
	<tr>
		<td>Penalty / Warning</td>
		<td>
			<a class='button hollow no-bottom small' data-open='addPenaltyBox'>
				More...
			</a>
		</td>
	</tr>

	<!-- Clear all exchanges, only for software admin-->
	<?php if(ALLOW['SOFTWARE_ADMIN'] == true): ?>
		<tr>
			<td>Clear All Exchanges</td>
			<td><div class='switch no-bottom'>
				<input class='switch-input' type='radio' name='mod'
					value='clearAll' id='Clear_All_Radio'
					onchange="modifiersRadioButtons()">
				<label class='switch-paddle' for='Clear_All_Radio'>
				</label>
				</div>
			</td>
		</tr>
	<?php endif ?>

	</table>

	<!-- Hidden button to be selected if a score is entered from the dropdowns -->
	<input type='radio' name='mod' value='hit' class='hidden' id='NA_Radio'>
	<input type='hidden' name='restartTimer' value='0' class='restart-timer-input'>

	</div>

	<!-- Submit button -->
	<div class='medium-6 cell '>
		<button class='button large expanded' id='New_Exchange_Button'
			name='lastExchange' value='noExchange' <?=LOCK_MATCH?>>
			Add: No Exchange
		</button>

		<div class='callout alert text-center hidden editExchangeWarningDiv'>
			<strong>Warning: </strong>
			You are editing an old exchange, not inserting a new one!<BR>
			<a class='button alert hollow' onclick="editExchange('')">
				Cancel Editing
			</a>
		</div>
	</div>

	</div>
	</div>

<?php }

/******************************************************************************/

function fighterDataEntryBox($matchInfo,$num){
// box with data entry fields for each fighter

	$isFinished = $matchInfo['winnerID'];
	$tournamentID = $matchInfo['tournamentID'];

	if($num == 1){
		$colorCode = COLOR_CODE_1;
		$colorName = COLOR_NAME_1;
		$pre = "fighter1";

	} else {
		$colorCode = COLOR_CODE_2;
		$colorName = COLOR_NAME_2;
		$pre = "fighter2";
	}

	$id = $matchInfo[$pre.'ID'];
	$fighterName = getCombatantName($id);
	$fighterSchool = $matchInfo[$pre.'School'];
	$score = $matchInfo[$pre.'score'];

	if($score === null){
		$score = "/";
	}

	$boxClass = "";

	$maxPoints = 8;


	// Teams Logic ----------------------------------------------------
	if(isTeamLogic($tournamentID)){
		$teamID = getFighterTeam($id, $tournamentID);
		$teamName = getTeamName($teamID);
	} else {
		$teamName = "";
	}


	// Attack Grid -----------------------------------------------------
	$attackDisplayMode = readOption('T',$matchInfo['tournamentID'],'ATTACK_DISPLAY_MODE');

	$hideInputs = false;
	if($isFinished || ALLOW['EVENT_SCOREKEEP'] == false){
		$hideInputs = true;
	}

	if($attackDisplayMode == ATTACK_DISPLAY_MODE_GRID && $hideInputs == false){
		$boxClass .= " clickable";
	}


	// Priority Text ----------------------------------------------------
	if(readOption('T',$matchInfo['tournamentID'],'PRIORITY_NOTICE_ON_NON_SCORING') != 0 && isLastExchZeroPointClean($matchInfo, $num) == true){
		$priorityText = " <span class='priority-text-notice'>Priority</span>";
	} else {
		$priorityText = "";
	}

	?>

<!-- Begin display -->

	<div class='small-6 cell fighter-score-box <?=$boxClass?>' style='background-color: <?=$colorCode?>; margin-bottom: 20px;'
		data-open='attack-grid-box-<?=$num?>'>


		<div class='grid-x' style='height: 100%'>

			<!-- Fighter information -->



			<div class='align-self-top cell'>
				<b style='font-size:20px;'>
					<?=$colorName?>:
				</b>

				<span style='font-size:20px;'> <?=$fighterName?></span>

				<?php if(ALLOW['EVENT_SCOREKEEP'] == false):?>
					<div>
						<span style='font-size:15px;'>
						<?=$fighterSchool?>
					</span>
					</div>
				<?php endif ?>


			</div>
			<div class='align-self-bottom cell'>



				<?php if(isTeamLogic($tournamentID)): ?>
					<div>
					<span style='font-size:15px;'>
						(<?=$teamName?>)
					</span>
					</div>
				<?php endif ?>

				<span style='font-size:30px;'>
					<?=$priorityText?>
				</span>
				<div>
				<span style='font-size:60px;'>
					<?=$score?>
				</span>

				</div>

			<!-- Input Fields -->
				<?php


					if($hideInputs == true){
						// Show nothing
					} else if($attackDisplayMode == ATTACK_DISPLAY_MODE_NORMAL){
						scoreSelectDropDown($id, $pre, isReverseScore($tournamentID));
						scoreAfterblowInput($id, $pre, $tournamentID, $maxPoints);
						scoreControlPointInput($id, $pre, $tournamentID);
					} else if($attackDisplayMode == ATTACK_DISPLAY_MODE_CHECK){
						scoreSelectCheckBox($id, $pre);
					} else { // $attackDisplayMode == ATTACK_DISPLAY_MODE_GRID
						// Don't show inputs for grid.
					}


				?>


			</div>

			<?=showFighterPenalties($num)?>
		</div>
	</div>

<?php
}

/******************************************************************************/


function gridScoreBoxes($matchInfo){

	if(ALLOW['EVENT_SCOREKEEP'] == false || LOCK_MATCH != ''){
		return;
	}


	$attacks = getTournamentAttacks($_SESSION['tournamentID']);
	foreach($attacks as $attack){
		$attackList[(int)$attack['attackTarget']][(int)$attack['attackType']][(int)$attack['attackPrefix']] = (int)$attack['attackPoints'];
	}

	$controlPointValue = (int)getControlPointValue($_SESSION['tournamentID']);;

	echo "<script type='text/javascript'>";
	$attackList_js = json_encode($attackList);
	echo "var gridAttackTypes = ".$attackList_js.";\n";
	echo "var controlPointValue = ".$controlPointValue.";\n";
	echo "</script>";

	gridScoreBox($matchInfo, 1);
	gridScoreBox($matchInfo, 2);
}


/******************************************************************************/

function gridScoreBox($matchInfo, $num){

	if($num == 1){
		$colorCode = COLOR_CODE_1;
		$colorName = COLOR_NAME_1;
		$pre = "fighter1";
		$otherPre = "fighter2";

	} else {
		$colorCode = COLOR_CODE_2;
		$colorName = COLOR_NAME_2;
		$pre = "fighter2";
		$otherPre = "fighter1";
	}

	$rosterID = $matchInfo[$pre.'ID'];
	$otherID = $matchInfo[$otherPre.'ID'];
	$fighterName = getCombatantName($rosterID);
?>
	<div class='reveal large' id='attack-grid-box-<?=$num?>' data-reveal >
	<form method="post">

		<div class='grid-x grid-margin-x'>

			<div class='callout large-12 cell' style='background-color: <?=$colorCode?>;'>
				<h5>Adding attack for <b><?=$fighterName?></b></h5>
			</div>

		</div>


		<?=scoreSelectGrid($matchInfo, $num, $rosterID, $otherID)?>
		<input type='hidden' class='restart-timer-input' name='restartTimer' value='0'>

	</form>
	<!-- Reveal close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>

<?php
}

/******************************************************************************/

function scoreSelectGrid($matchInfo, $num, $rosterID, $otherID){
	$attacks = getTournamentAttacks($_SESSION['tournamentID']);
	$parameters = getTournamentAttackParameters($_SESSION['tournamentID']);

	$pValue = (int)min($parameters['attackPoints']);
	$pMax = (int)max($parameters['attackPoints']);
	$pointValues = [];
	while($pValue <= $pMax){
		$pointValues[] = (int)$pValue;
		$pValue++;
	}

	$controlPointValue = getControlPointValue($_SESSION['tournamentID']);
	$prefixUsed = (bool)sizeof($parameters['attackPrefix']);
	if($controlPointValue != 0){
		$prefixUsed = true;
		foreach($parameters['attackPrefix'] as $index => $attackID){
			if($attackID == ATTACK_CONTROL_DB){
				unset($parameters['attackPrefix'][$index]);

			}
		}

	}

	$afterblowValue = (int)getAfterblowPointValue($_SESSION['tournamentID']);
	if($afterblowValue != 0){
		$parameters['afterblow'][] = $afterblowValue;
	}

?>

	<div class='grid-x grid-margin-x'>

		<input hidden name='scoreLookupMode' value='grid'>
		<input type='hidden' name='lastExchangeID' value='<?=$matchInfo['lastExchange']?>'>
		<input type='hidden' name='formName' value='newExchange'>
		<input type='hidden' name='score[<?=$otherID?>][hit]' value=''>
		<input type='hidden' class='exchangeID' name='score[exchangeID]' id='exchangeID-<?=$num?>'>
		<input type='hidden' class='matchTime' name='matchTime' value='<?=$matchInfo['matchTime']?>'>

<!-- Input Fields --------------------------------------------------------------->


		<div class='large-4 medium-6 cell'>
			<?=scoreGridOptionList($parameters, 'attackType', $rosterID, 'Action')?>
		</div>

		<div class='large-4 medium-6 cell'>
			<?=scoreGridOptionList($parameters, 'attackTarget', $rosterID, 'Target')?>
		</div>

		<div class='large-4 medium-6 cell'>
			<?=scoreGridOptionList($parameters, 'attackPrefix', $rosterID, null, true)?>

			<?php if($prefixUsed == true): ?>
				<hr>
			<?php endif ?>

			<?php if($afterblowValue != 0): ?>

				<?=scoreGridOptionList($parameters, 'afterblow', $rosterID)?>
			<?php else: ?>
				<input type='hidden' name='score[<?=$rosterID?>][afterblow]' value='0'>
			<?php endif ?>
		</div>

<!-- Exchange Summary --------------------------------------------------------------->

		<div class='callout large-12 cell text-center'>
			<h4 id='exchange-grid-summary-<?=$rosterID?>'>No Exchange</h4>
		</div>

<!-- Submit/Close Buttons --------------------------------------------------------------->

		<div class='large-4 medium-4 cell'>
			<a class='button secondary expanded' data-close aria-label='Close modal' >Cancel</a>

		</div>

		<div class='large-4 medium-4 cell'>
			<button class='button expanded' name='lastExchange' value='noQuality'
				id='grid-add-no-quality-<?=$rosterID?>' disabled>
				Add No Quallity
			</button>
		</div>

		<div class='large-4 medium-4 cell'>
			<button class='button success expanded' name='lastExchange' value='scoringHit'
				id='grid-add-new-exch-<?=$rosterID?>' disabled>
				Add Scoring Exchange
			</button>
		</div>

		<HR class='large-12 cell'>

<!-- Raw Score Values --------------------------------------------------------------->

		<div class='grid-x grid-margin-x cell' id="raw-points-grid">
			<div class='cell shrink'>
				Score Before Afterblow:
			</div>

			<div class='cell shrink'>
				<div class='switch input-group-button large no-bottom'>
					<span class='input-group-label'>No Quality</span>
					<input class='switch-input' type='radio' id='score[<?=$rosterID?>][hit][noQuality]'
						name='score[<?=$rosterID?>][hit]' value='noQuality'
						onchange="gridScoreManualPoints(<?=$rosterID?>)">
					<label class='switch-paddle' for='score[<?=$rosterID?>][hit][noQuality]'>
					</label>
				</div>
			</div>

			<div class='cell shrink'>
				<div class='switch input-group-button large no-bottom'>
					<span class='input-group-label'>0</span>
					<input class='switch-input' type='radio' id='score[<?=$rosterID?>][hit][0]'
						name='score[<?=$rosterID?>][hit]' value='0' checked
						onchange="gridScoreManualPoints(<?=$rosterID?>)">
					<label class='switch-paddle' for='score[<?=$rosterID?>][hit][0]'>
					</label>
				</div>
			</div>



			<?php foreach($pointValues as $attackPoints): ?>
				<div class='cell shrink'>

					<div class='switch input-group-button large no-bottom'>
						<span class='input-group-label'><?=$attackPoints?></span>
						<input class='switch-input' type='radio' id='score[<?=$rosterID?>][hit][<?=$attackPoints?>]'
							name='score[<?=$rosterID?>][hit]' value='<?=$attackPoints?>'
							onchange="gridScoreManualPoints(<?=$rosterID?>)">
						<label class='switch-paddle' for='score[<?=$rosterID?>][hit][<?=$attackPoints?>]'>
						</label>
					</div>
				</div>
			<?php endforeach ?>
		</div>



	</div>

<?php

}

/******************************************************************************/

function scoreGridOptionList($parameters, $paramType, $rosterID, $name ='', $isPrefix = false){

	$options = $parameters[$paramType];
	$controlAttackID = ATTACK_CONTROL_DB;

	$controlPointValue = (int)getControlPointValue($_SESSION['tournamentID']);
	$afterblowPointValue = (int)getAfterblowPointValue($_SESSION['tournamentID']);

	$numOptions = (int)(sizeof($options));
	if($controlPointValue != 0 && $isPrefix == true){
		$numOptions++;
	}

	if($numOptions == 0){
		echo "<input hidden name='score[{$rosterID}][{$paramType}]' value='0'>";
		return;
	}
	$id = 'grid-'.$paramType.'-div-'.$rosterID;
	$class = $id;

	if($paramType == 'attackType' || $paramType == 'attackTarget'){
		$hideNotApplicable = 'hidden';
	} else {
		$hideNotApplicable = '';
	}


?>
	<?php if($name != ''):?>
		<a class='<?=$id?>-show-button hidden' onclick="gridScoreToggleCategory('<?=$id?>',1)"><?=$name?> (Show) ↓</a>
		<a class='<?=$id?>-hide-button' onclick="gridScoreToggleCategory('<?=$id?>',0)"><?=$name?> (Hide) ↓</a>
	<?php endif ?>

	<table id="<?=$id?>" class="<?=$id?>">

		<tr id="score[<?=$rosterID?>][<?=$paramType?>]-none-row" class='<?=$hideNotApplicable?> <?=$id?>'>
			<td><i>n/a</i></td>
			<td>
				<div class='switch input-group-button large no-bottom'>
					<input class='switch-input' type='radio' id='score[<?=$rosterID?>][<?=$paramType?>][0]'
						name='score[<?=$rosterID?>][<?=$paramType?>]' value='0' checked onclick="gridScoreUpdate(<?=$rosterID?>,this)">
					<label class='switch-paddle' for='score[<?=$rosterID?>][<?=$paramType?>][0]'>
					</label>
				</div>
			</td>
		</tr>


		<?php foreach($options as $attackID):
			$attackName = getAttackName($attackID);
			?>
			<?php if((int)$attackID == 0){continue;}?>
			<tr>
				<td>
					<?php
						if($paramType != 'afterblow'){
							echo $attackName;
						} else {
							echo "Afterblow";
						}
					?>
				</td>
				<td>
					<div class='switch input-group-button large no-bottom'>
						<input class='switch-input' type='radio' id='score[<?=$rosterID?>][<?=$paramType?>][<?=$attackID?>]'
							name='score[<?=$rosterID?>][<?=$paramType?>]' value='<?=$attackID?>'
							onclick="gridScoreUpdate(<?=$rosterID?>,this)" data-attackName="<?=$attackName?>">
						<label class='switch-paddle' for='score[<?=$rosterID?>][<?=$paramType?>][<?=$attackID?>]'>
						</label>
					</div>
				</td>
			</tr>
		<?php endforeach?>

		<?php if($isPrefix == true & $controlPointValue != 0):?>

			<tr>
				<td>Control</td>
				<td>
					<div class='switch input-group-button large no-bottom'>
						<input class='switch-input' type='radio' id='score[<?=$rosterID?>][<?=$paramType?>]'
							name='score[<?=$rosterID?>][<?=$paramType?>]' value='<?=$controlAttackID?>'
							onchange="gridScoreUpdate(<?=$rosterID?>,this)">
						<label class='switch-paddle' for='score[<?=$rosterID?>][<?=$paramType?>]'>
						</label>
					</div>
				</td>
			</tr>
		<?php endif ?>


	</table>

<?php
}


/******************************************************************************/

function scoreAfterblowInput($id, $pre, $tournamentID, $maxPoints){

	$doubleTypes = getDoubleTypes($tournamentID);

	if($doubleTypes['afterblowType'] != 'deductive'){
		return;
	}

	$afterblowPointValue = readOption('T',$tournamentID,'AFTERBLOW_POINT_VALUE');

	if($afterblowPointValue != 0){
		$minAfterblow = $afterblowPointValue;
		$maxAfterblow = $afterblowPointValue;
	} else {
		$minAfterblow = 1;
		$maxAfterblow = $maxPoints;
	}

?>

	<div class='input-group grid-x'>
		<span class='input-group-label large-4 medium-6 small-12'>
			Afterblow
		</span>

		<?php if($afterblowPointValue == 0): ?>

			<select class='input-group-field' disabled name='score[<?=$id?>][afterblow]'
				id='<?=$pre?>_afterblow_input' onchange="scoreDropdownChange(this)">

					<option value=''></option>
					<?php for($i = $minAfterblow; $i<=$maxAfterblow;$i++): ?>
						<option value='<?=$i?>'><?=$i?> Points</option>
					<?php endfor ?>

			</select>

		<?php else: ?>

			<div class='switch text-center no-bottom'>

				<input type='hidden' name='score[<?=$id?>][afterblow]' value='0' >

				<input class='switch-input' type='checkbox'
					id='<?=$pre?>_afterblow_input' onchange="scoreDropdownChange(this)"
					name='score[<?=$id?>][afterblow]' value='<?=$afterblowPointValue?>'>

				<label class='switch-paddle' for='<?=$pre?>_afterblow_input'>
				</label>
			</div>

		<?php endif ?>
	</div>

<?php
}

/******************************************************************************/

function scoreControlPointInput($id, $pre, $tournamentID){

	$cVal = getControlPointValue($tournamentID);

	if($cVal == 0){
		return;
	}
?>

	<div class='input-group'>
		<span class='input-group-label large-4 medium-6 small-12'>
			Control <BR class='show-for-small-only'>(+<?=$cVal?> Point):
		</span>
		<div class='switch no-bottom' id='<?=$pre?>_control_div' style='display:inline'>
			<input class='switch-input' type='checkbox' name='attackModifier'
			value=9 id='<?=$pre?>_control_check' onclick="scoreDropdownChange()">
			<label class='switch-paddle' for='<?=$pre?>_control_check'>
			</label>
		</div>
	</div>

<?php
}

/******************************************************************************/

function scoreSelectDropDown($id, $pre, $isReverseScore){

	$attacks = getTournamentAttacks($_SESSION['tournamentID'], true);

	if($attacks == null){
		$minPoints = 1;
		$maxPoints = 10;

		if($isReverseScore == REVERSE_SCORE_INJURY){
			$dir = 1;
			$textPrefix = '-';
		} else {
			$dir = 1;
			$textPrefix = '';
		}

		for($i = $minPoints * $dir; abs($i)<=abs($maxPoints); $i += $dir){
			$attacks[$i]['tableID'] = $i;
			$attacks[$i]['attackText'] = $textPrefix.$i." Point".plrl($i);
		}
		$scoreMode = 'rawPoints';
	} else {
		$scoreMode = 'ID';
	}

?>

	<div class='input-group grid-x'>

		<span class='input-group-label large-4 medium-6 small-12'>Hit</span>

		<input hidden name='scoreLookupMode' value='<?=$scoreMode?>'>

		<select class='input-group-field ' name='score[<?=$id?>][hit]'
			id='<?=$pre?>_score_dropdown' onchange="scoreDropdownChange(this)">
			<option value=''></option>
			<option value='noQuality'>No Quality</option>
			<?php foreach((array)$attacks as $a):
				 ?>
				<option value='<?=$a['tableID']?>'><?=$a['attackText']?></option>
			<?php endforeach ?>
		</select>

	</div>

<?php
}

/******************************************************************************/

function scoreSelectCheckBox($id, $pre){
	$attacks = getTournamentAttacks($_SESSION['tournamentID'], true);

	if($attacks == null){

		$minPoints = 1;
		$maxPoints = 10;

		if($isReverseScore == REVERSE_SCORE_INJURY){
			$dir = 1;
			$textPrefix = '-';
		} else {
			$dir = 1;
			$textPrefix = '';
		}

		for($i = $minPoints * $dir; abs($i)<=abs($maxPoints); $i += $dir){
			$attacks[$i]['tableID'] = $i;
			$attacks[$i]['attackText'] = $textPrefix.$i." Point".plrl($i);
		}
		$scoreMode = 'rawPoints';

	} else {

		$scoreMode = 'ID';

	}

	if($pre == 'fighter1'){
		$num = 1;
	} else {
		$num = 2;

	}

	$divClass = 'medium-6 small-12';

?>

	<div class='input-group grid-x grid-margin-x'>

		<input hidden name='scoreLookupMode' value='<?=$scoreMode?>'>
		<input hidden id='radio-button-name-<?=$num?>' value='score[<?=$id?>][hit]'>


			<div class='cell shrink clickable attack-box-<?=$num?> attack-box attack-box-on <?=$divClass?>'
				onclick="scoreCheckboxChange(this, 'attack-box-<?=$id?>-value-none', <?=$num?>)">

				<input type='radio' name='score[<?=$id?>][hit]' value=''
					id='attack-box-<?=$id?>-value-none'    hidden
					checked onchange="scoreDropdownChange(this)">
				n/a

			</div>

<!--
			<div class='cell shrink clickable  attack-box-<?=$num?> attack-box <?=$divClass?>'
				onclick="scoreCheckboxChange(this, 'attack-box-<?=$id?>-value-noQuality', <?=$num?>)">

				<input type='radio' name='score[<?=$id?>][hit]' value='noQuality' class='attack-box-<?=$num?>'
					id='attack-box-<?=$id?>-value-noQuality'    hidden
					onchange="scoreDropdownChange(this)">

				No Quality

			</div>
		-->

			<div class='large-12'></div>


			<?php foreach((array)$attacks as $a): ?>

				 <div class='cell shrink clickable attack-box-<?=$num?> attack-box <?=$divClass?>'
				 	onclick="scoreCheckboxChange(this, 'attack-box-<?=$id?>-value-<?=$a['tableID']?>', <?=$num?>)">

				 	<input type='radio' name='score[<?=$id?>][hit]' value='<?=$a['tableID']?>' class=''
				 		id='attack-box-<?=$id?>-value-<?=$a['tableID']?>'    hidden
				 		onchange="scoreDropdownChange(this)">

				 	<?=$a['attackText']?>

				 </div>

			<?php endforeach ?>


	</div>

<?php
}

/******************************************************************************/

function switchFighersBox($matchInfo){

	if(isset($_SESSION['shouldSwitchFighters']) == true){
		$shouldSwitch = $_SESSION['shouldSwitchFighters'];
		unset($_SESSION['shouldSwitchFighters']);
	} elseif((int)$matchInfo['lastExchange'] == 0) {
		$shouldSwitch = [1 => true, 2 => true];
	} else {
		$shouldSwitch = [1 => false, 2 => false];
	}

	if($matchInfo['teamEntry'] != true || isTeamLogic($matchInfo['tournamentID']) != false){
		return;
	}


	if($shouldSwitch[1] == true || $shouldSwitch[2] == true){
		$GLOBALS['showSwitchFightersBox'] = true;
	}

	$colorCode1 = COLOR_CODE_1;
	$colorName1 = COLOR_NAME_1;
	$colorCode2 = COLOR_CODE_2;
	$colorName2 = COLOR_NAME_2;

	$activeFighter1ID = (int)getActiveFighterOnTeam($matchInfo['matchID'], $matchInfo['fighter1ID']);
	$activeFighter2ID = (int)getActiveFighterOnTeam($matchInfo['matchID'], $matchInfo['fighter2ID']);


	$teamSize = readOption('T', $matchInfo['tournamentID'], 'TEAM_SIZE');
	$teamSwitchPoints = readOption('T', $matchInfo['tournamentID'], 'TEAM_SWITCH_POINTS');
	$teamSwitchMode = readOption('T', $matchInfo['tournamentID'], 'TEAM_SWITCH_MODE');

	$nextPair = null;

	if($teamSize == 3 && $teamSwitchMode == TEAM_SWITCH_MODE_MOF && $teamSwitchPoints != 0){
		$isMofOrder = true;
		$highestScore = max($matchInfo['fighter1score'], $matchInfo['fighter2score']);
		$matchNum = (int)($highestScore / $teamSwitchPoints) + 1;

		$mofOrder[1] = [3,3];
		$mofOrder[2] = [1,2];
		$mofOrder[3] = [2,1];
		$mofOrder[4] = [1,3];
		$mofOrder[5] = [3,1];
		$mofOrder[6] = [2,2];
		$mofOrder[7] = [1,1];
		$mofOrder[8] = [2,3];
		$mofOrder[9] = [3,2];

		$shouldSwitch = [1 => false, 2 => false];


		if($matchNum < sizeof($mofOrder))
		{
			$nextPair = $mofOrder[$matchNum];

		}

	}


	if($activeFighter2ID == 0 || $activeFighter2ID == 0){
		$formDisabled = 'disable';
	} else {
		$formDisabled = '';
	}



	if($matchInfo['lastExchange'] == 0 || ($activeFighter1ID == 0)){
		$cancelText = "";
	} else {
		$cancelText = "";
	}

?>

	<div class='reveal medium open' id='switchFightersBox' data-reveal style="visibility: visible;">

	<h5>Select Active Fighters</h5>

	<form method='POST'>

	<input type='hidden' name='activeFighters[matchID]' value='<?=$matchInfo['matchID']?>'>
	<input type='hidden' name='activeFighters[lastExchangeID]' value='<?=$matchInfo['lastExchange']?>'>


	<?php if($shouldSwitch[1] == true):?>
		<div class='callout' style='background-color: <?=$colorCode1?>; border: 1px solid black'>
			<h4><?=$colorName1?> Should Switch Fighter</h4>
		</div>
	<?php endif ?>

	<?php if($shouldSwitch[2] == true):?>
		<div class='callout' style='background-color: <?=$colorCode2?>; border: 1px solid black'>
			<h4><?=$colorName2?> Should Switch Fighter</h4>
		</div>
	<?php endif ?>

	<?php if($nextPair != null):?>
		<div class='callout success' style='border: 1px solid black'>
			<h4>Next match should be: <b><?=$nextPair[0]?></b> vs <b><?=$nextPair[1]?></b></h4>
		</div>
	<?php endif ?>


    <i>Click on the active fighter for each team.</i>
	<div class='grid-x grid-padding-x grid-margin-x'>
		<div class='cell large-12'></div>
		<?=switchFighersBoxTeam($matchInfo, 1, $activeFighter1ID)?>
		<?=switchFighersBoxTeam($matchInfo, 2, $activeFighter2ID)?>
	</div>


	<HR>

	<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>
			<button class='button large-6 cell' name='formName' <?=$formDisabled?> disabled
				value='switchActiveFighters' id='switch-active-fighters-submit'>
				Update
			</button>
			<button class='button secondary large-6 cell' data-close aria-label='Close modal' type='button'>
				Cancel
			</button>
		</div>

		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>

	</form>

	<BR><BR><BR><BR>

	</div>



<?php
}

/******************************************************************************/

function switchFighersBoxTeam($matchInfo, $num, $activeFighterID){

	if($num == 1){
		$colorCode = COLOR_CODE_1;
		$colorName = COLOR_NAME_1;
		$pre = "fighter1";
		$border = "border-right";

	} else {
		$colorCode = COLOR_CODE_2;
		$colorName = COLOR_NAME_2;
		$pre = "fighter2";
		$border = "border-left";
	}

	$teamID = $matchInfo[$pre.'ID'];
	$teamRoster = getTeamRoster($teamID);

?>

	<div class='large-6 text-center cell'
		style='border-right: 1px solid black; border-left: 1px solid black; margin-top: 0.5em' >

		<div class='grid-x grid-padding-x grid-margin-x'>
			<div class='large-12 text-center cell' style='background-color: <?=$colorCode?>; border: 1px solid black'>
				<span style='font-size:1.5em '><?=$colorName?></span>
			</div>
		</div>

		<input type='hidden' name='activeFighters[<?=$num?>][rosterID]'
			value='<?=$activeFighterID?>' id='active-fighter-rosterID-<?=$num?>'>
		<input type='hidden' name='activeFighters[<?=$num?>][teamID]'
			value='<?=$teamID?>' id='active-fighter-rosterID-<?=$num?>'>

		<?php foreach($teamRoster as $index => $rosterID):
			if($rosterID == $activeFighterID){
				$class = 'alert';
			} else {
				$class = 'hollow';
			}

			?>


			<a class='button expanded cell <?=$class?> team-fighters-<?=$num?>' style='margin-top: 2em'
				onclick="selectActiveFighter(<?=$rosterID?>,<?=$num?>,this)">
				(<?=($index+1)?>) <?=getFighterName($rosterID)?>
			</a>



		<?php endforeach ?>


	</div>


<?php
}

/******************************************************************************/

function addPenaltyBox($matchInfo){

	if(   ALLOW['EVENT_SCOREKEEP'] == false
	   || LOCK_MATCH != ''
	   || $matchInfo['matchComplete'] == true){

		return;
	}

	$maxPenalty = -30; // Arbitrary Number
	$cards = getPenaltyColors();
	$actions = getPenaltyActions($_SESSION['eventID']);
	$escalation = readOption('T',$matchInfo['tournamentID'],'PENALTY_ESCALATION_MODE');
	$matchID = $matchInfo['matchID'];


	if((bool)readOption('E',$_SESSION['eventID'],'PENALTY_ACTION_IS_MANDATORY') == true){
		$req = 'required';
	} else {
		$req = '';
	}

	?>

	<div class='reveal tiny' id='addPenaltyBox' data-reveal>

		<h5>Insert Penalty</h5>

		<form method='POST'>
			<input type='hidden' name='formName' value='newExchange'>
			<input type='hidden' name='lastExchangeID' value='<?=$matchInfo['lastExchange']?>'>
			<input type='hidden' class='exchangeID' name='score[exchangeID]'>

		<!-- Select colors -->
			<strong>[<?=COLOR_NAME_1?>]</strong>
			<input type='radio' name='score[penalty][rosterID]' id='penalty-fighter-1' required
				value='<?=$matchInfo['fighter1ID']?>'
				onchange="calculatePenaltyEscalation(<?=$escalation?>, <?=$matchID?>)" >

			<BR>

			<strong>[<?=COLOR_NAME_2?>]</strong>
			<input type='radio' name='score[penalty][rosterID]' id='penalty-fighter-2'
				value='<?=$matchInfo['fighter2ID']?>'
				onchange="calculatePenaltyEscalation(<?=$escalation?>, <?=$matchID?>)">

		<!-- Point deduction -->
			<div class='input-group'>
				<span class='input-group-label'>
					Point Deduction
				</span>
				<select class='input-group-field' name='score[penalty][value]'>
					<?php for($penaltyVal = 0; $penaltyVal >= $maxPenalty; $penaltyVal--): ?>
						<option value='<?=$penaltyVal?>'><?=$penaltyVal?> Points</option>
					<?php endfor ?>
				</select>
			</div>

		<!-- Infraction selection -->
			<div class='input-group'>
				<span class='input-group-label'>
					Violation
				</span>
				<select class='input-group-field' name='score[penalty][action]' <?=$req?>
					onchange="calculatePenaltyEscalation(<?=$escalation?>, <?=$matchID?>)"
					id='penalty-infraction'>

					<option value=''>None</option>
					<?php foreach($actions as $a):
						if($a['isDisabled'] == true){continue;}?>
						<option value='<?=$a['attackID']?>'>
							<?=$a['name']?>
						</option>
					<?php endforeach ?>

				</select>
			</div>

			<div class='callout secondary' id='penalty-escalation-notice'>

			</div>

		<!-- Color/card selection -->
			<div class='input-group'>

				<span class='input-group-label'>
					Color
				</span>

				<select class='input-group-field' name='score[penalty][card]'  id='penalty-color'
					onchange="calculatePenaltyEscalation(<?=$escalation?>, <?=$matchID?>)">

					<option value=''>None</option>
					<?php foreach($cards as $attackID => $name): ?>
						<option value='<?=$attackID?>'>
							<?=$name?>
						</option>
					<?php endforeach ?>

				</select>
			</div>


			<span id='penalty-escalation-warning'></span>

		<!-- Submit buttons -->
			<div class='grid-x grid-margin-x'>
				<button class='button success small-6 cell' name='lastExchange' value='penalty'>
					Add
				</button>
				<button class='button secondary small-6 cell' data-close aria-label='Close modal' type='button'>
					Cancel
				</button>
			</div>

			<!-- Close button -->
			<button class='close-button' data-close aria-label='Close modal' type='button'>
				<span aria-hidden='true'>&times;</span>
			</button>

		</form>

	</div>

<?php }

/******************************************************************************/

function createSideBar($matchInfo){
/* 	box on the rights side of the screen with buttons to declare winners,
	links to other matches, and the option to switch fighter colors */

	$colorCode1 = COLOR_CODE_1;
	$colorCode2 = COLOR_CODE_2;

	$name1 = COLOR_NAME_1;
	$name2 = COLOR_NAME_2;

	$matchID = $matchInfo['matchID'];
	$tournamentID = $matchInfo['tournamentID'];
	$fighter1ID = $matchInfo['fighter1ID'];
	$fighter2ID = $matchInfo['fighter2ID'];
	$winnerID = $matchInfo['winnerID'];
	$lastExchangeID = $matchInfo['lastExchange'];

	$nextMatchInfo = getNextMatchInfo($matchInfo);
	$matchNumInfo = getPoolMatchesLeft($matchInfo);
	$doubles = getMatchDoubles($matchID);

// Check if staff need to be confirmed
	$staffConfirmActive = false;
	$staffConfirmRequired = false;
	if(ALLOW['EVENT_SCOREKEEP'] == true && $matchInfo['placeholderMatchID'] == null){
		$checkInLevel = logistics_getTournamentStaffCheckInLevel($matchInfo['tournamentID']);

		if($checkInLevel != STAFF_CHECK_IN_NONE){
			$staffConfirmActive = true;
		}

		if($checkInLevel == STAFF_CHECK_IN_MANDATORY){
			$staffConfirmRequired = !logistics_areMatchStaffCheckedIn($matchID);
		}
	}

// Check if fighters need to sign in scores
	if(isSignOffRequired($matchInfo['tournamentID']) == true
		&& $matchInfo['matchComplete'] == 1){

		$signOffs = getMatchSignOff($matchID);
		if($signOffs['signOff1'] == 1 && $signOffs['signOff2'] == 1){
			$signOffPending = '';
			$signOffMouse = '';
		} else {
			$signOffPending = 'disabled';
			$signOffMouse = 'no-mouse';
		}


	} else {
		$signOffPending = '';
		$signOffMouse = '';
	}


	if(LOCK_MATCH == 'disabled' || $staffConfirmRequired == true){
		$lockInputs = 'disabled';
	} else {
		$lockInputs = '';
	}

	if(isset($matchInfo['endType'])){
		$endType = $matchInfo['endType'];
	} else {
		$endType = '';
	}

	$endColor = '';
	switch($endType){
		case 'winner':
			$endText1 = 'Winner';
			if($winnerID == $fighter1ID){
				$endText2 = $name1;
				$endColor = $colorCode1;
			} elseif($winnerID == $fighter2ID){

				$endText2 = $name2;
				$endColor = $colorCode2;
			}
			break;
		case 'tie':
			$endText1 = '&nbsp;';
			$endText2 = 'Tie';
			break;
		case 'ignore':
			$endText1 = '';
			$endText2 = 'Match Incomplete';
			break;
		case 'doubleOut':
			$endText1 = 'No Winner';
			$endText2 = "<span class='red-text'>Double Out</span>";
			break;
		default:
			$endText1 = '';
			$endText2 = '';
			break;
	}

	$tieMode = readOption('T',$tournamentID,'MATCH_TIE_MODE');

	if(   $tieMode == MATCH_TIE_MODE_EQUAL
	   && $matchInfo['fighter1score'] == $matchInfo['fighter2score']){
		$allowMatchTie = true;
	} elseif ($tieMode == MATCH_TIE_MODE_UNEQUAL) {
		$allowMatchTie = true;
	} else {
		$allowMatchTie = false;
	}

///////////////////////////////////////////////// ?>


<!-- Match winner management/display -->
	<?php if($endText1 != null || $endText2 != null): ?>
		<h4><?=$endText1?></h4>
		<div class='match-winner-name' style='background-color:<?=$endColor?>'>
		<h3 class='no-bottom'><?=$endText2?></h3>
		</div>


	<?php else: ?>

	<!-- Timer -->

		<input type='hidden' class='matchTime' id='matchTime'
			name='matchTime' value='<?=$matchInfo['matchTime']?>'>
		<input type='hidden' id='timeLimit' value='<?=$matchInfo['timeLimit']?>'>
		<?php if(ALLOW['EVENT_SCOREKEEP'] == true && $lockInputs == ''): ?>
			<script>
				window.onload = function(){

					<?php
						if(isset($GLOBALS['showSwitchFightersBox'])){
							unset($GLOBALS['showSwitchFightersBox']);
							echo "$('#switchFightersBox').foundation('open');";
						}
					?>

					updateTimerDisplay();

					<?php if($matchInfo['restartTimer'] == true): ?>
						startTimer();
					<? endif ?>

				};
			</script>

			Timer:
			<a class='button hollow expanded success no-bottom timer-input'
				onclick="startTimer()" id='timerButton'>
			<h4 class='no-bottom button-extra-pad-small' id='currentTime'>0:00</h4>
			</a>

			<!--Manual Time Set -->
			<a onclick="$('#manualSetDiv').toggle();"
				id='manualTimerToggle'>
				Manual Time Set
			</a>

			<div class='hidden' id='manualSetDiv'>
			<div class='input-group grid-x'>
				<input class='input-group-field timer-input' type='number' name='timerMinutes'
					id='timerMinutes' placeholder='Min'>
				<input class='input-group-field timer-input' type='number' name='timerSeconds'
					id='timerSeconds' placeholder='Sec'>
				<button class='button success input-group-button large-shrink
							medium-12 small-shrink timer-input'
					onclick="manualTimeSet()">
					&#10004;
				</button>
			</div>

			</div>

			<HR>
		<?php else: ?>

			<script>
				window.addEventListener("load",function(event) {
						updateTimerDisplay();
					});
			</script>

			<?php if($matchInfo['matchTime'] != 0){
				$hideTimer = '';
			} else {
				$hideTimer = 'hidden';
			}


			?>
			<div class='match-winner-name <?=$hideTimer?> alert' id='currentTimeDiv'>
				<h3 class='no-bottom' id='currentTime'>
					0:00
				</h3>
			</div>

		<?php endif?>


	<!-- Match Winner -->
		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
			<form method='POST'>
			<fieldset <?=$lockInputs?>>
			<input type='hidden' name='formName' value='matchWinner'>
			<input type='hidden' name='lastExchangeID' value='<?=$lastExchangeID?>'>
			<input type='hidden' name='matchID' value='<?=$matchID?>'>
			<input type='hidden' class='matchTime' name='matchTime' value='<?=$matchInfo['matchTime']?>'>

			Winner:
			<div class='grid-x'>

			<?php
				$winButton1 = "
				<div class='small-6 medium-12 large-6 cell match-winner-button-div'>
					<button class='button large success no-bottom expanded conclude-match-button'
						style='background-color:{$colorCode1};'
						name='matchWinnerID' value='{$fighter1ID}' {$lockInputs} >
						{$name1}
					</button>
				</div>";

				$winButton2 = "
				<div class='small-6 medium-12 large-6 cell match-winner-button-div'>
					<button class='button large success no-bottom expanded conclude-match-button'
						style='background-color:{$colorCode2}; '
						name='matchWinnerID' value='{$fighter2ID}' {$lockInputs} >
						{$name2}
					</button>
				</div>";

				if(isset($_SESSION['flipMatchSides']) && $_SESSION['flipMatchSides'] == true)
				{
					echo $winButton2;
					echo $winButton1;
				} else {
					echo $winButton1;
					echo $winButton2;
				}
			?>

		<!-- Tie -->
			<?php if($allowMatchTie == true): ?>
				<div class='small-12 cell'>

				<button class='button large hollow  expanded no-bottom' style='margin-top: 10px;'
					name='matchWinnerID' value='tie' <?=$lockInputs?>>
				Tie
				</button>
				</div>
			<?php endif ?>
			</div>

			</fieldset>
			</form>

		<?php elseif($matchInfo['ignoreMatch']): ?>
			<h4>Match Incomplete</h4>
		<?php elseif($matchInfo['lastExchange'] != null || $matchInfo['matchTime'] > 0): ?>
			<h4>In Progress</h4>
		<?php else: ?>
			<h4>Not Started</h4>
		<?php endif ?>
	<?php endif ?>


<!-- Doubles management/display -->
	<?php if(isDoubleHits($matchInfo['tournamentID'])): ?>
		<hr>

		<form method='POST'>
		<fieldset <?=$lockInputs?>>
		<input type='hidden' name='formName' value='matchWinner'>
		<input type='hidden' name='lastExchangeID' value='<?=$lastExchangeID?>'>
		<input type='hidden' name='matchID' value='<?=$matchID?>'>
		<input type='hidden' class='matchTime' name='matchTime' value=''>

		<?php doublesText($doubles, $matchInfo) ?>
		</fieldset>
		</form>
	<?php endif ?>


<!-- Go to next match buttons -->

	<?php if(ALLOW['EVENT_SCOREKEEP'] == true || ALLOW['VIEW_SETTINGS'] == true): ?>


	<?php if(isset($nextMatchInfo)): ?>
		<HR>
		This is match #: <?=$matchNumInfo['matchNumber']?>/<?=$matchNumInfo['numMatches']?>
		<BR>
		Next Match: <?= tooltip('Skipping matches which have fighters removed due to injury/disqualification');?> <BR>

		<?php if($matchInfo['unconcludedMatchWarning'] && ALLOW['EVENT_SCOREKEEP'] == true): ?>


			<a class='button hollow expanded' data-open='confirmNextPoolNavigation'>
				<?=getCombatantName($nextMatchInfo['fighter1ID'])?>
				<BR> <?=$name1?>
				<BR><BR> vs.<BR>
				<BR> <?=getCombatantName($nextMatchInfo['fighter2ID'])?>
				<BR> <?=$name2?>
			</a>

			<div class='reveal tiny' id='confirmNextPoolNavigation' data-reveal>

			<h5>Alert</h5>
			You haven't closed this match yet. The software doesn't know if it is done or still running.<BR>
			Make sure to conclude the match (declare winner/double out/tie/etc..) when a match is finished.<BR>
			<i>If a match is not fought due to injury/disqualification, be sure that an event organizer removes them from the pool scoring calculations.</i>

			<form method='POST'>
			<input type='hidden' value='<?=$nextMatchInfo['matchID']?>' name='matchID'>

		<!-- Submit buttons -->
			<div class='grid-x grid-margin-x'>
				<button class='success button small-6 cell' name='formName' value='goToMatch'>
					Go To The Next Match
				</button>
				<button class='secondary button small-6 cell' data-close aria-label='Close modal' type='button'>
					Stay Here
				</button>
				<button class='warning button small-12 cell' name='formName' value='ignorePastIncompletes'>
					Go To The Next Match And Dont' Warn Me Again
				</button>
			</div>
			</form>


		<!-- Reveal close button -->
			<button class='close-button' data-close aria-label='Close modal' type='button'>
				<span aria-hidden='true'>&times;</span>
			</button>

			</div>

		<?php else: ?>

			<form method='POST'>
			<input type='hidden' name='formName' value='goToMatch'>

			<button class='button hollow expanded' value='<?=$nextMatchInfo['matchID']?>'
				name='matchID' <?=$lockInputs?> <?=$signOffPending?>>
				<?=getCombatantName($nextMatchInfo['fighter1ID'])?>
				<BR> <?=$name1?>
				<BR><BR> vs.<BR>
				<BR> <?=getCombatantName($nextMatchInfo['fighter2ID'])?>
				<BR> <?=$name2?>
			</button>

			</form>


		<?php endif ?>

		</form>

	<?php elseif($matchInfo['matchType'] == 'pool'): ?>
		<HR><BR>
		<a class='button warning large <?=$signOffMouse?>' href='poolMatches.php' <?=$lockInputs?> <?=$signOffPending?> >
			End of Pool
		</a>
	<?php elseif($matchInfo['matchType'] == 'elim'): ?>
		<HR><BR>
		<a class='button expanded' href='finalsBracket.php#anchor<?=$matchID?>'>
			Back To Bracket
		</a>
	<?php endif ?>
	<?php endif ?>

<!-- Staff Confirmation Optional Request -->
	<?php if($staffConfirmActive == true && $staffConfirmRequired == false): ?>
		<HR>

			<strong>
				<a data-open='matchStaffConfirmBox'>
					Edit Match Staff
				</a>
			</strong>


	<?php endif ?>

	<?=matchOptionsBox($matchInfo)?>

<?php }

/******************************************************************************/

function matchOptionsBox($matchInfo){

	if(    ALLOW['EVENT_SCOREKEEP'] == false
		|| LOCK_TOURNAMENT == true
		|| $matchInfo['matchComplete'] == 1){
		return;
	}

	$maxSubMatches = 7; /// Arbitrary number

	$mainMatchID = $matchInfo['placeholderMatchID'];
	if($mainMatchID == null){
		$mainMatchID = $matchInfo['matchID'];
	}

	$subMatchMode 			= getSubMatchMode($matchInfo['tournamentID']);
	$numSubMatches 			= getNumSubMatchesByMatch($mainMatchID);
	$exchangeInfo 			= getMatchExchanges($matchInfo['matchID']);

	$showSubMatchOption = false;
	if(   isFinalsMatch($mainMatchID) == 1
	   && getNumSubMatches($matchInfo['tournamentID']) == 0){
		// Only allow editing of sub matches for finals in tournaments
		// that aren't already sub-match tournaments.
		$showSubMatchOption = true;
	}


?>

	<HR>
	<a class='button warning hollow no-bottom expanded' data-open='matchOptionsBox'>
		Match Options
	</a>

	<div class='reveal' id='matchOptionsBox' data-reveal>
		<h4>Match Options</h4>

		<hr>
		<div class="grid-x grid-margin-x">

			<input type='hidden' id='misc-timer-value' value=0>

			<div class='cell small-6'>
				Stopwatch:
				<div class='callout text-center bold secondary' id='misc-timer-container'>
					<h3 id='misc-timer-display'>0:00</h3>
				</div>
			</div>

			<div class='cell medium-4 small-6'>
				<a id='misc-timer-button' class='button align-self-middle hollow expanded' onclick="miscTimerToggle()">
					Start
				</a>
				<a class='button align-self-middle warning hollow expanded'  onclick="miscTimerReset()">
					Reset
				</a>
			</div>
		</div>

		<?php if($matchInfo['lastExchange'] != null): ?>
			<HR>
			<button class='button no-bottom' id='editExchangeButton' data-open='editExchangeBox'>
				Edit Previous Exchange
			</button>
			<button class='button hidden warning no-bottom' id='cancelEditExchangeButton'
				onclick="editExchange('')">
				Cancel Editing
			</button>


			<div class='reveal tiny' id='editExchangeBox' data-reveal>


				<h5>Edit Exchange</h5>

				<?php foreach($exchangeInfo as $exchange):

					if($exchange['exchangeType'] == 'winner'
					   || $exchange['exchangeType'] == 'tie'
					   || $exchange['exchangeType'] == 'doubleOut'){
						break;
					}
					?>
					<a class='button hollow small-6 cell' data-close aria-label='Close modal'
						type='button'
						onclick="editExchange('<?=$exchange['exchangeID']?>',
												'<?=$exchange['exchangeTime']?>')">
						[Edit #<?=$exchange['exchangeNumber']?>]
						<?=convertExchangeIntoText($exchange, $matchInfo['fighter1ID'])?>
					</a>
				<?php endforeach ?>

				<a class='button secondary small-6 cell' data-close aria-label='Close modal'
					type='button' onclick="editExchange('')">
					Cancel
				</a>

				<!-- Close button -->
				<button class='close-button' data-close aria-label='Close modal' type='button'>
					<span aria-hidden='true'>&times;</span>
				</button>
			</div>

		<?php endif ?>

		<HR>
		<form method='POST'>
			<input type='hidden' name='swapMatchFighters[matchID]' value='<?=$_SESSION['matchID']?>'>
			<button class='button warning' name='formName' value='swapMatchFighters'>
				Switch Fighter Colors
			</button>
			<BR>

			<button class='button no-bottom warning hollow' name='formName' value='flipMatchSides'>
				Swap Fighter Sides
				<?=tooltip("Swap fighters right/left on the Display Window AND this screen, while keeping colors the same.<BR><BR>
						You will need to press the <strong>Display Window</strong> button again to refresh the window.")?>
			</button>

			<button class='button no-bottom warning hollow' name='formName' value='mirrorMatchDisplay'>
				Flip Match Display
				<?=tooltip("Swap fighters right/left on the Display Window but NOT on this screen, while keeping colors the same.<BR><BR>
						You will need to press the <strong>Display Window</strong> button again to refresh the window.")?>
			</button>

		</form>

		<?php if($showSubMatchOption == true): ?>

			<HR>

			<form method='POST'>

			<input type='hidden' name='updateSubMatchesByMatch[matchID]'
				value='<?=$mainMatchID?>'>

			<div class='input-group'>
				<span class='input-group-label'>
					Number of Sub-Matches
					<?=tooltip("Sub-matches will create multiple stages for this match.
						<BR><u>Example</u>: A best 2 out of 3 finals match.")?>
				</span>
				<select class='input-group-field' name='updateSubMatchesByMatch[numSubMatches]'>
					<option value='0'>0 (single match)</option>
					<?php for($i = 2; $i<=$maxSubMatches; $i++): ?>
						<option <?=optionValue($i, $numSubMatches)?> >
							<?=$i?>
						</option>
					<?php endfor ?>
				</select>
			</div>

			<div class='input-group'>
				<span class='input-group-label'>
					Sub-Match Mode
					<?=tooltip("<u>Analog</u>: The points from all sub-matches are added to determine
						the match winner.
						<BR><BR><u>Digital</u>: Winner is determined by who wins the most sub-matches,
						regardless of what the scores were.")?>
				</span>
				<select class='input-group-field' name='updateSubMatchesByMatch[subMatchMode]'>
					<option <?=optionValue(SUB_MATCH_ANALOG,$subMatchMode)?> >	Analog 	</option>
					<option <?=optionValue(SUB_MATCH_DIGITAL,$subMatchMode)?> >	Digital	</option>
				</select>
			</div>

			<em><u>Note</u>: Changing the Sub-Match Mode affects all sub-matches in the tournament.</em>
			<BR>

			<button class='button success no-bottom' name='formName' value='updateSubMatchesByMatch'>
				Update Sub-Match Settings
			</button>

			</form>
		<?php endif // if($matchInfo['bracketLevel'] == 1)?>

		<HR>

		<?=displayRandomizer()?>

		<HR>

		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>

	</div>



<?php
}

/******************************************************************************/

function displayRandomizer(){
?>

	<a onclick="$('#random-num-div').toggle()">Random Number Generators ↓</a>

	<div class='hidden' id='random-num-div'>

	<HR>

	<div class='grid-x grid-margin-x'>

		<h4 class='cell medium-9 no-bottom'>Team Order:</h4>

		<a class='button cell medium-3 no-bottom' onclick='rollForTeamOrder()'>
			Do it!
		</a>

		<div class='cell medium-6'>
			<select id='roll-team-1'>
				<option></option>
				<option selected>Longsword (1)</option>
				<option>Longsword (2)</option>
				<option>Longsword (3)</option>
				<option>Longsword (4)</option>
				<option>Longsword (5)</option>
				<option>Rapier (1)</option>
				<option>Rapier (2)</option>
				<option>Rapier (3)</option>
				<option>Rapier (4)</option>
				<option>Rapier (5)</option>
				<option>Saber (1)</option>
				<option>Saber (2)</option>
				<option>Saber (3)</option>
				<option>Saber (4)</option>
				<option>Saber (5)</option>
			</select>
			<select id='roll-team-2'>
				<option></option>
				<option>Longsword (1)</option>
				<option>Longsword (2)</option>
				<option>Longsword (3)</option>
				<option>Longsword (4)</option>
				<option>Longsword (5)</option>
				<option selected>Rapier (1)</option>
				<option>Rapier (2)</option>
				<option>Rapier (3)</option>
				<option>Rapier (4)</option>
				<option>Rapier (5)</option>
				<option>Saber (1)</option>
				<option>Saber (2)</option>
				<option>Saber (3)</option>
				<option>Saber (4)</option>
				<option>Saber (5)</option>
			</select>
			<select id='roll-team-3'>
				<option></option>
				<option>Longsword (1)</option>
				<option>Longsword (2)</option>
				<option>Longsword (3)</option>
				<option>Longsword (4)</option>
				<option>Longsword (5)</option>
				<option>Rapier (1)</option>
				<option>Rapier (2)</option>
				<option>Rapier (3)</option>
				<option>Rapier (4)</option>
				<option>Rapier (5)</option>
				<option selected>Saber (1)</option>
				<option>Saber (2)</option>
				<option>Saber (3)</option>
				<option>Saber (4)</option>
				<option>Saber (5)</option>
			</select>
			<select id='roll-team-4'>
				<option selected></option>
				<option>Longsword (1)</option>
				<option>Longsword (2)</option>
				<option>Longsword (3)</option>
				<option>Longsword (4)</option>
				<option>Longsword (5)</option>
				<option>Rapier (1)</option>
				<option>Rapier (2)</option>
				<option>Rapier (3)</option>
				<option>Rapier (4)</option>
				<option>Rapier (5)</option>
				<option>Saber (1)</option>
				<option>Saber (2)</option>
				<option>Saber (3)</option>
				<option>Saber (4)</option>
				<option>Saber (5)</option>
			</select>
			<select id='roll-team-5'>
				<option selected></option>
				<option>Longsword (1)</option>
				<option>Longsword (2)</option>
				<option>Longsword (3)</option>
				<option>Longsword (4)</option>
				<option>Longsword (5)</option>
				<option>Rapier (1)</option>
				<option>Rapier (2)</option>
				<option>Rapier (3)</option>
				<option>Rapier (4)</option>
				<option>Rapier (5)</option>
				<option>Saber (1)</option>
				<option>Saber (2)</option>
				<option>Saber (3)</option>
				<option>Saber (4)</option>
				<option>Saber (5)</option>
			</select>
		</div>
		<div class='cell medium-6 callout'>
			<div id='roll-team-output-1'></div>
			<div id='roll-team-output-2'></div>
			<div id='roll-team-output-3'></div>
			<div id='roll-team-output-4'></div>
			<div id='roll-team-output-5'></div>
		</div>
	</div>

	<hr>

	<div class='grid-x grid-margin-x'>
		<h4 class='cell medium-9 no-bottom'>Offhand Weapon:</h4>

		<a class='button cell medium-3 no-bottom' onclick='rollForOffhand()'>
			Do it!
		</a>

		<div class='cell medium-6'>
			<select id='roll-offhand-1'>
				<option></option>
				<option selected>No-Offhand</option>
				<option>Any Offhand</option>
				<option>Dagger</option>
				<option>Buckler</option>
				<option>Cloak</option>
				<option>Some Other Craziness</option>
			</select>
			<select id='roll-offhand-2'>
				<option></option>
				<option>No-Offhand</option>
				<option selected>Yes-Offhand</option>
				<option>Dagger</option>
				<option>Buckler</option>
				<option>Cloak</option>
				<option>Some Other Craziness</option>
			</select>
			<select id='roll-offhand-3'>
				<option selected></option>
				<option>No-Offhand</option>
				<option>Any Offhand</option>
				<option>Dagger</option>
				<option>Buckler</option>
				<option>Cloak</option>
				<option>Some Other Craziness</option>
			</select>
			<select id='roll-offhand-4'>
				<option selected></option>
				<option>No-Offhand</option>
				<option>Any Offhand</option>
				<option>Dagger</option>
				<option>Buckler</option>
				<option>Cloak</option>
				<option>Some Other Craziness</option>
			</select>
			<select id='roll-offhand-5'>
				<option selected></option>
				<option>No-Offhand</option>
				<option>Any Offhand</option>
				<option>Dagger</option>
				<option>Buckler</option>
				<option>Cloak</option>
				<option>Some Other Craziness</option>
			</select>
			<select id='roll-offhand-6'>
				<option selected></option>
				<option>No-Offhand</option>
				<option>Any Offhand</option>
				<option>Dagger</option>
				<option>Buckler</option>
				<option>Cloak</option>
				<option>Some Other Craziness</option>
			</select>
		</div>
		<div class='cell medium-6 callout'>
			<div id='roll-offhand-output'></div>
		</div>
	</div>
	</div>

<?php
}

/******************************************************************************/

function doublesText($doubles, $matchInfo){
// adds smiley and frowny faces depending on the number of double hits
// adds button to declare match as a double out


	$reverseScore = isReverseScore($matchInfo['tournamentID']);
	$basePointValue = getBasePointValue($matchInfo['tournamentID'], $_SESSION['groupSet']);

	$doubleOut = false;
	if(    (int)$matchInfo['maxDoubles'] != 0
		&& (int)$doubles >= (int)$matchInfo['maxDoubles']){

		$doubleOut = true;

	} else if($reverseScore == REVERSE_SCORE_INJURY){

		if( 	(  $matchInfo['fighter1score'] <= 0
				&& $matchInfo['fighter2score'] <= 0
				&& $matchInfo['lastExchange'] != 0)
			|| ($basePointValue == 0)
			)
		{
			$doubleOut = true;
		}

	} elseif($reverseScore == REVERSE_SCORE_GOLF){

		if(		(  $matchInfo['fighter1score'] >= $basePointValue
				&& $matchInfo['fighter2score'] >= $basePointValue)
			&& $basePointValue != 0)
		{
			$doubleOut = true;
		}

	} else {

		$doubleOut = false;

	}

	$class = ifSet($doubleOut,"class='red-text'");
	$string = "{$doubles} Double Hit".ifSet($doubles != 1, "s");

	switch ($doubles){
	case 0:
		$string .= " :)";
		break;
	case 1:
		break;
	case 2:
		$string .= " :(";
	default:
		for($i=2;$i<$doubles&&$i<9;$i++){
			$string .="!";
		}
		break;
	}
	?>

	<span <?=$class?>><?=$string?></span>
	<?php if($doubleOut && !$matchInfo['matchComplete'] && ALLOW['EVENT_SCOREKEEP'] == true): ?>
		<BR>
		<button class='button large alert no-bottom conclude-match-button' name='matchWinnerID'
			value='doubleOut' <?=LOCK_MATCH?>>
			Double Out
		</button>
	<?php endif ?>

<?php }

/******************************************************************************/

function showFighterPenalties($num){

	return; // This is kind of unecessary given the Prior Penalties and Match History

	$penaltyList = getFighterMatchPenalties($_SESSION['matchID'], $num);
?>

	<?php foreach($penaltyList as $penalty):

		switch($penalty['card']){
			case 'yellowCard':
				$class = 'penalty-card-yellow';
				break;
			case 'redCard':
				$class = 'penalty-card-red';
				break;
			case 'blackCard':
				$class = 'penalty-card-black';
				break;
			default:
				continue 2;
				break;
		}

		?>

		<span class='<?=$class?> penalty-card-display'>
			<?=$penalty['name']?>
		</span>
	<?php endforeach ?>

<?php
}

/******************************************************************************/

function priorPenaltiesDisplayBox($matchInfo){

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return (false);
	}

	$penaltyWarnings['fighter'] = (array)getEventPenalties($_SESSION['eventID'],
													  [$matchInfo['fighter1ID'],
													  $matchInfo['fighter2ID']]);

	if($matchInfo['matchType'] == 'elim'){
		$penaltyWarnings['doubles'] = (array)getBracketPriorDoubles($matchInfo);
	} else {
		$penaltyWarnings['doubles'] = [];
	}

	if($penaltyWarnings['fighter'] == [] && $penaltyWarnings['doubles'] == []){
		return (false);
	}

?>

<!-- Box for penalty info display -->
	<div class='reveal medium' id='veiwPenaltiesBox' data-reveal>

		<h5>Prior Penalties</h5>
		The fighters have accrued the following penalties over <u>all</u> tournaments in this event.
		<i>Use, or don't use, this information as event procedure dictates.</i>

		<?php
			foreach($penaltyWarnings['doubles'] as $match){

					echo "<hr><b>";
					echo getFighterName($match['fighterID']);
					echo "</b> had <b>{$match['numDoubles']} Doubles</b> in the last match <i>(vs ";
					echo getFighterName($match['versusID']);
					echo ")</i>";
			}

			foreach($penaltyWarnings['fighter'] as $fighter){

					echo "<HR><h5>".$fighter['name'];
					echo " [".$fighter['numPenalties']." Penalties]</h5>";

				foreach($fighter['list'] as $penalty){
					displayPenalty($penalty);
				}

			}
		?>

	<!-- Reveal close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>

<?php

	return (true);
}

/******************************************************************************/

function unconcludedMatchWarning($matchInfo){

	$warningSize = isUnconcludedMatchWarning($matchInfo);

	if($warningSize != 0 && ALLOW['EVENT_SCOREKEEP'] == true){

		$string = "<strong>The last <span class='red-text'>{$warningSize}</span> matches have not been concluded.</strong><BR>
		Make sure to conclude the match (declare winner/double out/tie/etc..) when a match is finished.<BR>
		<i>If a match is not fought due to injury/disqualification, be sure that an event organizer removes them from the pool scoring calculations.</i>
		<form method='POST'>
			<input type='hidden' name='formName' value='ignorePastIncompletes'>
			<button class='button hollow no-bottom'>Don't Warn Me Again</button>
		</form>

		";
		displayAlert($string,'warning');
		$isWarning = true;
	} else {
		$isWarning = false;
	}

	return $isWarning;

}

/******************************************************************************/

function inlineHelp(){
/*	Displays a link which opens a reveal containing a help menu
	for scorekeepers who are new to the program */
?>
	<div class='callout alert'>
		<a data-open='newUserHelp'>First Time Using Scorecard?</a>
	</div>

	<div class='reveal large' id='newUserHelp' data-reveal>
		<h5>Entering Exchanges</h5>
		Each exchange can be one of the following:
		<ul>
		<li><u>No Exchange:</u> If no item is selected the exchange will be 'No Exchange' and no score is assigned to either fighter.</li>
		<li><u>No Quality:</u> A fighter has hit, but the attack is deemed insuficient. Located above the point values in the drop down menus.</li>
		<li><u>Clean Hit:</u> Select a score for one of the fighters.</li>
		<li><u>Afterblow:</u> When using deductive afterblows select a score for a fighter and select the afterblow value.
			For full afterblow rules select scores for each of the fighters.</li>
		<li><u>Double Hit:</u> Select the double hit switch if the exchange is double.</li>
		<li><u>Penalty:</u> Selecting the penalty switch will change the scores to negative values to asses a fighter a score penalty.</li>
		<li><u>Clear Last Exchange:</u> Removes the last exchange inputted.</li>
		</ul>

		<div class='callout alert'>
		<h5 class='text-center'>ENTER ALL DATA IN THE SOFTWARE</h5>
		Make sure to enter all non-scoring exchanges and no quality hits.
		If there is a hit with a value of 2 and an afterblow deduction of 1
		<u>do not</u> enter a clean hit of 1 point.<BR>
		<i>You may not think this is important, but I do.
		Having good quality tournament data is the reason I put so much time
		into developing free software for you to use. :)</i>
		</div>

		<h5>Concluding Matches</h5>
		The buttons to conclude a match are located right bellow what you clicked on to get this help menu.
		Once a winner has been determined for the match select the appropriate button.
		If the fight has reached the maximum number of double hits a red <strong>Double Out</strong>
		 button will appear, to conclude the match as a double loss.
		<BR>Selecting <strong>Re-Open Match</strong> after a match has been concluded will re-open the match to the last recorded exchange.
		<ul><li><u>Important:</u>
		If a match is not concluded properly the scoring calculations will not
		function properly, and the Bracket Helper will not know which fighters to advance.</li></ul>

		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>

<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
