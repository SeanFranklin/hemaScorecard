<?php
/*******************************************************************************
	Match Scoring
	
	Scores a match
	LOGIN
		- STAFF and higher can score & conclude matches
		- YOUTUBE and higher can add links to youtube
		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Match Score';
$hideEventNav = true;
$hidePageTitle = true;
$lockedTournamentWarning = true;
$jsIncludes[] = 'score_scripts.js';
include('includes/header.php');

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
		redirect('participantsRoster.php');
	} else {
		displayAlert("No Match Selected<BR><a href='poolMatches.php'>Match List</a>");
	}
} else {

	$matchInfo = getMatchInfo($matchID, $tournamentID);
	if(isset($_SESSION['restartTimer'])){
		$matchInfo['restartTimer'] = true;
		unset($_SESSION['restartTimer']);
	} else {
		$matchInfo['restartTimer'] = false;
	}

	$exchangeInfo = getMatchExchanges($matchID);

// If it is the last match in the tournament the staff is asked to finalize the event
	askForFinalization($matchInfo); 
	
// If the livestream is active it asks to make this the displayed match
	livestreamMatchSet($matchID);

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
	
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>



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
			
			<!-- Fighter scores -->
			<div class='large-12 cell'>
				<form method='POST'>
				<fieldset <?=LOCK_MATCH?>>
					<input type='hidden' name='formName' value='newExchange'>
					<input type='hidden' name='matchID' value='<?=$matchID?>' id='matchID'>
					<input type='hidden' class='matchTime' name='matchTime' value='<?=$matchInfo['matchTime']?>'>
						<input type='hidden' class='exchangeID' name='score[exchangeID]'>
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
		<?php
		if(ALLOW['EVENT_SCOREKEEP'] == true 
		   && $exchangesNotNumbered == false 
		   && LOCK_MATCH ==''
		   && $matchInfo['matchComplete'] == false
		   && count($exchangeInfo) > 0){
			?>
			<div class='large-12 cell'>	
			<BR>
			<button class='button' id='editExchangeButton' data-open='editExchangeBox'>
				Edit Exchange
			</button>
			<button class='button hidden warning' id='cancelEditExchangeButton' onclick="editExchange('')">
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
		</div>
			<?php
		}


		?>
			
	</div>
	
<!-- Youtube -->
	<?php addYoutube($matchID); // display_functions.php ?>
	

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

function confirmStaffBox($matchInfo, $staffList = null){

	if(ALLOW['EVENT_SCOREKEEP'] == FALSE){
		return;
	}

	$rolesList = logistics_getRoles();
	$staffList = getEventRoster();
	$matchStaffList = logistics_getMatchStaffSuggestion($matchInfo);
	$hideRows = '';
	$msIDcounter = -1;
	$possibleStaffShifts = logistics_getMatchStaffShifts($matchInfo);
	$totalRowsShowing = 0;

?>

	<div class='reveal' id='matchStaffConfirmBox' data-reveal>
	
		<h4>Check In Staff</h4>
		<form method='POST'>
		<input type='hidden' name='updateMatchStaff[matchID]' value='<?=$matchInfo['matchID']?>'>

	<!-- Top button bar -->
		<?php if($possibleStaffShifts != null): ?>
			<a class='button hollow no-bottom' data-open='matchStaffLoadShiftBox'>
				Get Staff From Schedule
			</a>
			<BR>
		<?php endif ?>

		[<strong>&#x2714;</strong>]: Already Checked In <BR>
		[<strong>?</strong>]: Guess based on last staff in this ring. <BR>

	
		<em>
			<?=toggleClass('hiddenCheckStaffRow','(Show More Rows &#8595;)','(Show Less Rows &#8593;)')?>
		</em>

	<!-- Data entry fields -->
		<table>
			<?php foreach($matchStaffList as $member): 
				$msID = $member['matchStaffID'];
				$totalRowsShowing++;
				if($msID < 0){
					$msID = $msIDcounter;
					$msIDcounter--;
					$isAlreadySet = '?';
				} else {
					$alreadySet[$member['rosterID']] = true;
					$isAlreadySet = '&#x2714;';
				}
				?>
				<tr>
					<th>
						<?=$isAlreadySet?>
					</th>
					<td>
						<select name='updateMatchStaff[umsInfo][<?=$msID?>][rosterID]'>
							<option value='<?=$member['rosterID']?>'>
								<?=getFighterName($member['rosterID'])?>
							</option>
							<option value='0'>
								- Delete Staff Member -
							</option>
						</select>
					</td>
					<td>
						<select  name='updateMatchStaff[umsInfo][<?=$msID?>][logisticsRoleID]'>
							<?php foreach($rolesList as $role):?>
								<option <?=optionValue($role['logisticsRoleID'],$member['logisticsRoleID'])?> >
									<?=$role['roleName']?>
								</option>
							<?php endforeach ?>
						</select>
					</td>

			<?php endforeach ?>


		<!-- Add New Staff -->	
			<?php for($i=$msIDcounter;$i>=($msIDcounter-10);$i--): // Negative values for new staff
				$totalRowsShowing++;
				if($totalRowsShowing > 7){
					$hideRows = "class='hiddenCheckStaffRow hidden'";
				}
				?>
				<tr <?=$hideRows?> >
					<td>
						<!-- Always empty -->
					</td>
					<td>
						<select name='updateMatchStaff[umsInfo][<?=$i?>][rosterID]'>
							<option></option>
							<?php foreach($staffList as $person):
								if(isset($alreadySet[$person['rosterID']]) == true){
									continue;
								}?>

								<option <?=optionValue($person['rosterID'],null)?> >
									<?=getFighterName($person['rosterID'])?>
								</option>

							<?php endforeach ?>
						</select>
					</td>
					<td>
						<select  name='updateMatchStaff[umsInfo][<?=$i?>][logisticsRoleID]'>
							<?php foreach($rolesList as $role):?>
								<option <?=optionValue($role['logisticsRoleID'],null)?> >
									<?=$role['roleName']?>
								</option>
							<?php endforeach ?>
						</select>
					</td>
				</tr>
			<?php endfor ?>

		</table>




	<!-- Submit buttons -->
		<?php if(isset($alreadySet) == true){
			$bText = 'Update Staff';
			$bClass= 'hollow';
		} else {
			$bText = 'Check In Staff';
			$bClass = '';
		} ?>

		<div class='grid-x grid-margin-x'>

			<button class='button success small-6 cell <?=$bClass?>' name='formName' 
				value='updateMatchStaff'>
				<?=$bText?>
			</button>

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

function livestreamMatchSet($matchID){
	
	$livestreamInfo = getLivestreamInfo();
	if(ALLOW['EVENT_SCOREKEEP'] == false){ return; }
	if($livestreamInfo['isLive'] != 1 || $livestreamInfo['useOverlay'] != 1){
		return;
	}
	?>
	
	<?php if($matchID != $livestreamInfo['matchID']): ?>
		<form method='POST' onclick="this.submit()" class='pointer'>
		<input type='hidden' name='formName' value='setLivestreamMatch'>
		<input type='hidden' name='matchID' value='<?=$matchID?>'>
		
		<div class='callout alert text-center'>
			This match is currently not displayed on the livestream overlay<BR>
			<a>Change to Active Match</a>
		</div>
		</form>
	<?php endif ?>
	
<?php }

/******************************************************************************/

function askForFinalization($matchInfo){
/*	After the final match of a tournament has concluded this will prompt the 
	scorekeeper to finalize the tournament results */
	
	if(    ALLOW['EVENT_SCOREKEEP'] == false
		|| LOCK_TOURNAMENT != null){ return;}

	$tournamentID = (int)$matchInfo['tournamentID'];

	$finalize = false;
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
					Add Third Match
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
	?>
	
	<div class='grid-x align-middle grid-padding-x cell'>
	
	<div class='medium-shrink small-12 cell' style='margin-bottom: 10px;'>
	<?php if($_SESSION['bracketHelper'] == 'try'): ?>
		<a class='button no-bottom' href='finalsBracket.php'>
			Back To Bracket
		</a>
		<a class='button no-bottom' href='poolMatches.php#anchor{<?=$matchID?>'>
			Back To Match List
		</a>
		
	<?php elseif($matchInfo['matchType'] == 'pool'): ?>
		<a class='button expanded no-bottom' href='poolMatches.php#anchor<?=$matchID?>'>
			Back To Match List
		</a>
		
	<?php else: ?>
		
		<a class='button no-bottom' href='finalsBracket.php#anchor<?=$matchID?>'>
			Back To Bracket
		</a>

	<?php endif ?>
	</div>
	
	<!-- Tournament name -->
	<div class='auto text-center cell hide-for-small-only' >
		<h5><?=$name?></h5>
	</div>

	</div>
	
<?php }

/******************************************************************************/

function dataEntryBox($matchInfo){
/*	The main data entry box for the match. Contains boxes for each fighter
	and the scorekeepers box bellow. Scorekeepers box only visiable if logged in.*/

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
	
<!-- Score boxes for individual fighters -->
	<div class='grid-x grid-margin-x'>		
		<?php fighterDataEntryBox($matchInfo,1); ?>
		<?php fighterDataEntryBox($matchInfo,2); ?>
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
	
	<table>
		
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
	<?php if(isDoubleHits()): ?>
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
		<td>Clear Last Exchange</td>
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
	<!-- Penalty -->
	<tr>
		<td>Penalty</td>
		<td>
			<div class='switch no-bottom'>
			<input class='switch-input' type='radio' name='mod' 
				value='penalty' id='Penalty_Radio'
				onchange="modifiersRadioButtons()" >
			<label class='switch-paddle' for='Penalty_Radio'>
			</label>
			</div>
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
	<input type='hidden' name='restartTimer' value='0' id='restartTimerInput'>
	
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
		<a class='button alert hollow' onclick="editExchange('')">Cancel Editing</a>
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
	if($score == null){$score=0;};
	
	$doubleTypes = getDoubleTypes($tournamentID);
	$maxPoints = 8;
	if($doubleTypes['afterblowType'] != 'deductive'){
		$hideAfterblow = "class='hidden'";
	}

	if(isTeamLogic($tournamentID)){
		$teamID = getFighterTeam($id, $tournamentID);
		$teamName = getTeamName($teamID);
	} else {
		$teamName = "";
	}

	?>
	
<!-- Begin display -->
	<div class='small-6 cell fighter-score-box' style='background-color: <?=$colorCode?>;'>
		<div class='grid-x' style='height: 100%'>
			
			<!-- Fighter information -->
			<div class='align-self-top cell'>
				<span style='font-size:20px;'> <?=$fighterName?></span>
			</div>
			<div class='align-self-bottom cell'>
				<span style='font-size:15px;'><?=$fighterSchool?></span><BR>
				<?php if(isTeamLogic($tournamentID)): ?>
					<span style='font-size:15px;'>(<?=$teamName?>)</span><BR>
				<?php endif ?>
				<span style='font-size:30px;'><?=$colorName?></span><BR>
				<span style='font-size:60px;'><?=$score?></span><BR>
		
				<?php if($isFinished || ALLOW['EVENT_SCOREKEEP'] == false): ?>
					</div>
					</div>
					</div>
					<?php return; ?>
				<?php endif ?>
	
			<!-- Hit score select -->
				<div class='input-group grid-x'>
					<span class='input-group-label large-4 medium-6 small-12'>Hit</span>
					<?php scoreSelectDropDown($id, $pre, isReverseScore($tournamentID)); ?>
					
				</div>
			
			<!-- Afterblow score select -->
				<?php if($doubleTypes['afterblowType'] == 'deductive'): ?>
					
					<div class='input-group grid-x'>
						<span class='input-group-label large-4 medium-6 small-12'>
							Afterblow
						</span>
						
						<select class='input-group-field' disabled
							name='score[<?=$id?>][afterblow]' 
							id='<?=$pre?>_afterblow_dropdown' 
							onchange="scoreDropdownChange(this)">
							<option value=''></option>
							<option value='1'>1 Point</option>
							<?php for($i = 2; $i<=$maxPoints;$i++): ?>
								<option value='<?=$i?>'><?=$i?> Points</option>
							<?php endfor ?>
						</select>
					</div>
					
				<?php endif ?>

			<!-- Control point select -->	
				<?php $cVal = getControlPointValue();
					if($cVal != 0): ?>
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
				<?php endif ?>

				
			<!-- Penalty score select -->	
				<div id='<?=$pre?>_penalty_div' class='hidden'>
					<div class='input-group grid-x'>
						<span class='input-group-label large-4 medium-6 small-12'>
							Penalty
						</span>
						
						<select class='input-group-field'
							name='score[<?=$id?>][penalty]' 
							id='<?=$pre?>_penalty_dropdown' 
							onchange="penaltyDropDownChange(this)">
							<option value=''></option>
							<?php for($i = 1; $i <=$maxPoints;$i++): 
								if(isReverseScore($tournamentID) == REVERSE_SCORE_GOLF){
									$penaltyVal = $i;
								} else {
									$penaltyVal = -$i;
								}
								?>
								<option value='<?=$penaltyVal?>'><?=$penaltyVal?> Points</option>
							<?php endfor ?>
						</select>
					</div>
				</div>
			
			</div>
		</div>
	</div>

<?php }

/******************************************************************************/

function scoreSelectDropDown($id, $pre, $isReverseScore){
	
	$attacks = getTournamentAttacks();
	
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
<?php					
}

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

	$nextMatchInfo = getNextPoolMatch($matchInfo);
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
	

///////////////////////////////////////////////// ?> 
	
	
<!-- Staff Confirmation Mandatory Request -->
	<?php if(ALLOW['EVENT_SCOREKEEP'] == true && $staffConfirmRequired == true): ?>
		<div class='callout alert'>
			<strong>
				<a data-open='matchStaffConfirmBox'>
					Match Staff must be confirmed
				</a>
			</strong>
		</div>

	<?php endif ?>

	
<!-- Match winner management/display -->
	<?php if($endText1 != null || $endText2 != null): ?>
		<h4><?=$endText1?></h4>
		<div class='match-winner-name' style='background-color:<?=$endColor?>'>
		<h3 class='no-bottom'><?=$endText2?></h3>
		</div>
		
		
	<?php else: ?>

	<!-- Timer -->
		<?php if(IS_TIMER): ?>
			<input type='hidden' class='matchTime' id='matchTime' 
				name='matchTime' value='<?=$matchInfo['matchTime']?>'>
			<input type='hidden' id='timeLimit' value='<?=$matchInfo['timeLimit']?>'>
		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>	
			<script>
				window.onload = function(){

					updateTimerDisplay();

					<?php if($matchInfo['restartTimer'] == true): ?>
						startTimer();
					<? endif ?>

				};
			</script>
		
			Timer:
			<a class='button hollow expanded success no-bottom timer-input' 
				onclick="startTimer()" id='timerButton'>
			<h4 class='no-bottom' id='currentTime'>0:00</h4>
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

			<?php if($matchInfo['matchTime'] > 0){
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
		<?php endif ?>

	<!-- Match Winner -->
		<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>		
			<form method='POST'>
			<fieldset <?=$lockInputs?>>
			<input type='hidden' name='formName' value='matchWinner'>
			<input type='hidden' name='matchID' value='<?=$matchID?>'>
			<input type='hidden' class='matchTime' name='matchTime' value='<?=$matchInfo['matchTime']?>'>
		
			Winner:
			<div class='grid-x'>
			<div class='small-6 medium-12 large-6 cell match-winner-button-div'>
				<button class='button large success no-bottom expanded conclude-match-button' 
					style='background-color:<?=$colorCode1?>; '
					name='matchWinnerID' value='<?=$fighter1ID?>' <?=$lockInputs?> >
					<?=$name1?>
				</button>
			</div>
			<div class='small-6 medium-12 large-6 cell match-winner-button-div'>
				<button class='button large success no-bottom expanded conclude-match-button' 
				style='background-color:<?=$colorCode2?>;'
					name='matchWinnerID' value='<?=$fighter2ID?>' <?=$lockInputs?> >
					<?=$name2?>
				</button>
			</div>
		
		<!-- Tie -->	
			<?php if((int)$matchInfo['fighter1score'] == (int)$matchInfo['fighter2score'] 
						&& isTies($tournamentID)): ?>
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
	<?php if(isDoubleHits()): ?>
		<hr>

		<form method='POST'>
		<fieldset <?=$lockInputs?>>
		<input type='hidden' name='formName' value='matchWinner'>
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
	
<!-- Switch fighter colors -->	
	<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
		<HR>
		<form method='POST'>
		<button class='button warning hollow no-bottom' name='formName' 
			value='switchFighters' <?=$lockInputs?> >
			Switch Fighter Colors
		</button>
		</form>
	<?php endif ?>
	
<?php }

/******************************************************************************/

function doublesText($doubles, $matchInfo){
// adds smiley and frowny faces depending on the number of double hits
// adds button to declare match as a double out


	$reverseScore = isReverseScore($matchInfo['tournamentID']);
	$basePointValue = getBasePointValue($matchInfo['tournamentID'], $_SESSION['groupSet']);

	if(    (int)$matchInfo['maxDoubles'] != 0 
		&& (int)$doubles >= (int)$matchInfo['maxDoubles']){

		$doubleOut = true;

	} else if($reverseScore == REVERSE_SCORE_INJURY){

		if( 	(  $matchInfo['fighter1score'] <= 0 
				&& $matchInfo['fighter2score'] <= 0)
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
	
	$class=ifSet($doubleOut,"class='red-text'");
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

function unconcludedMatchWarning($matchInfo){

	$useWarning = isUnconcludedMatchWarning($matchInfo);

	if($useWarning && ALLOW['EVENT_SCOREKEEP'] == true){
		$string = "<strong>The last two matches have not been concluded.</strong><BR>
		Make sure to conclude the match (declare winner/double out/tie/etc..) when a match is finished.<BR>
		<i>If a match is not fought due to injury/disqualification, be sure that an event organizer removes them from the pool scoring calculations.</i>
		<form method='POST'>
			<input type='hidden' name='formName' value='ignorePastIncompletes'>
			<button class='button hollow no-bottom'>Don't Warn Me Again</button>
		</form>

		";
		displayAlert($string,'warning');

	}

	return $useWarning;

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
