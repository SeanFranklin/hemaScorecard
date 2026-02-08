<?php
/*******************************************************************************
	Finals Bracket 1

	Displays the main/winners bracket, and bracket management
	LOGIN:
		- ADMIN and above can create/delete brackets
		- STAFF and above can add/remove fighters from matches
		- STAFF and above can enable/disable the bracket helper

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Finals Bracket';
$includeTournamentName = true;
$lockedTournamentWarning = true;
$jsIncludes[] = "logistics_management_scripts.js";
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if($tournamentID == null){
	pageError('tournament');
} elseif($_SESSION['formatID'] != FORMAT_MATCH){
	displayAlert("There are no brackets for this tournament format");
} elseif (ALLOW['VIEW_MATCHES'] == false){
	displayAlert("Event is still upcoming<BR>Bracket not yet released");
} else {

// Load bracket information
	$allBracketInfo = getBracketInformation($tournamentID);
	$elimType = $allBracketInfo['elimType'];
	$ringsInfo = (array)logistics_getEventLocations($_SESSION['eventID'],'ring');

// Deal with bracket case and session
	if(isset($_SESSION['bracketView']) == false){
		$_SESSION['bracketView'] = BRACKET_PRIMARY;
	}

	if($_SESSION['bracketView'] == BRACKET_SECONDARY
		&& $elimType == ELIM_TYPE_SINGLE){

		$_SESSION['bracketView'] = BRACKET_PRIMARY;

	}

// Get eligible fighters
	if(isPools($tournamentID)){
		$finalists = getTournamentStandings($tournamentID, null, 'pool', 'advancements');
	} else {
		if(isEntriesByTeam($tournamentID) == false){
			$finalists = getTournamentFighters($tournamentID, 'rating');
		} else {
			$finalists = getTournamentTeams($tournamentID);
		}

	}


// Bracket Display
	if($elimType == null){

		displayAlert("No Brackets Created", 'CENTER');

		bracketManagement($tournamentID, false, $finalists);

	} else {


		if($_SESSION['bracketHelper'] != 'on'){
			$bracketAdvancements = [];
		} elseif($_SESSION['bracketView'] == BRACKET_PRIMARY){
			$bracketAdvancements = getPrimaryBracketAdvancements($allBracketInfo, $finalists);
		} else {
			$bracketAdvancements = getSecondaryBracketAdvancements($allBracketInfo, $finalists);
		}

		bracketControl($allBracketInfo,$ringsInfo);

		// Where the magic happens. Located in displayFunctions.php
		displayBracket($allBracketInfo[$_SESSION['bracketView']],
						$finalists,
						$_SESSION['bracketView'],
						$bracketAdvancements,
						$elimType);

		if($_SESSION['bracketCase'] = BRACKET_PRIMARY){
			bracketManagement($tournamentID, true, $finalists);
		}

	}



// Auto-refresh
	$time = autoRefreshTime(isInProgress($tournamentID, 'bracket'));
	echo "<script>window.onload = function(){autoRefresh({$time});}</script>";


}

include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function bracketControl($allBracketInfo, $ringsInfo){

	if(ALLOW['EVENT_SCOREKEEP'] == false && $allBracketInfo['elimType'] == ELIM_TYPE_SINGLE){
		return;
	}

?>

	<div class='callout grid-x grid-margin-x small'>

<!-- Switch brackets -->
	<?php if($allBracketInfo['elimType'] != ELIM_TYPE_SINGLE):
		if($_SESSION['bracketView'] == BRACKET_PRIMARY){
			$primary = '';
			$secondary = 'hollow';
		} else {
			$primary = 'hollow';
			$secondary = '';
		}
		?>

		<div class='cell shrink'>
		<form method="POST" style='display:inline-block;'>

			<input type='hidden' name='formName' value='changeBracketView'>

			<button class='button <?=$primary?> no-bottom'
				name='bracketView' value='<?=BRACKET_PRIMARY?>'>

				Main Bracket
			</button>

			<button class='button warning <?=$secondary?> no-bottom' name='bracketView' value='<?=BRACKET_SECONDARY?>'>
				Secondary Bracket
			</button>

		</form>
		</div>

	<?php endif ?>

<!-- Bracket helper button -->
	<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>
		<div class='cell shrink'>
			<?=bracketHelperToggleButton()?>
		</div>
	<?php endif ?>


<!-- Ring Assignment -->
	<?php if(ALLOW['EVENT_SCOREKEEP'] == true && $ringsInfo != null): ?>
		<div class='input-group shrink cell no-bottom'>
			<span class='input-group-label shrink'>
				Move to Ring:
				<?=tooltip("Moves all matches you have check-marked into this ring.")?>
			</span>
			<select class='input-group-field shrink' name='locationID' id='bracketLocationID'>
				<option value='0'></option>
				<?php foreach($ringsInfo as $ring): ?>
					<option value=<?=$ring['locationID']?> >
						<?=$ring['locationName']?>
					</option>
				<?php endforeach ?>
				<option value='0'>- Remove -</option>
			</select>
			<input type='submit' class='input-group-button button shrink success'
				onclick="submit_updateBracketRings()" value='Assign' >

			</input>
		</div>

		<?=assignMatchesToRingsBox($ringsInfo)?>
	<?php endif ?>

	</div>



<?php
}
/******************************************************************************/

function assignMatchesToRingsBox($ringsInfo){

	if(ALLOW['EVENT_SCOREKEEP'] == false){
		return;
	}

	$ringsToShowMatches = [];
	foreach($ringsInfo as $ring){
		if($ring['hasMatches'] ==  false){
			continue;
		}

		$tmp = [];
		$tmp['locationID'] = $ring['locationID'];
		$tmp['name'] = $ring['locationName'];
		$ringsToShowMatches[] = $tmp;
	}

?>
	<a class='button align-self-middle' data-open='match-locations-box' onclick="populateBracketMatchesToAssign(<?=$_SESSION['tournamentID']?>,<?=$_SESSION['eventID']?>)">
		Assign By Queue
	</a>



	<!----------------------------------------------------------------------->

	<div class='reveal medium' id='match-locations-box' data-reveal>

		<h3>Assign Matches To Rings</h3>

		<a class='cell large-12 button warning' onClick="window.location.reload();">
			<h4 class='no-bottom'>Refresh Page</h4>
		</a>

		<div class="tabs-content" data-tabs-content="assign-ring-tabs">
		<div class='tabs-panel is-active' id="panel-assign">
		<div class="grid-x grid-margin-x">


			<div class='cell large-12' id='assign-instructions'>
				<!-- Populated by JS -->
			</div>

			<HR class='cell large-12' >

			<div class='cell small-12 show-for-small-only top-border'>
				<h3>Rings:</h3>
			</div>

			<div class='cell medium-3'>
				<div class='grid-x grid-margin-x' id='rings-to-assign-div'>
					<!-- Populated by JS -->
				</div>
			</div>

			<div class='cell small-12 show-for-small-only top-border'>
				<h3>Matches:</h3>
			</div>

			<div class='cell medium-9'>
				<div class='grid-x grid-margin-x' id='matches-to-assign-div'>
					<!-- Populated by JS -->
				</div>

			</div>

		</div>
		</div>

		<div class="tabs-panel" id="panel-view">

			<?php foreach($ringsToShowMatches as $ring):?>
				<h3><?=$ring['name']?></h3>
				<div class='grid-x grid-margin-x' id='matches-assigned-div-<?=$ring['locationID']?>'>
				</div>
			<?php endforeach ?>

		</div>

		</div>

		<ul class="tabs" data-tabs id="assign-ring-tabs">
			<li class="tabs-title is-active"><a href="#panel-assign" aria-selected="true">Assign Matches</a></li>
			<li class="tabs-title"><a data-tabs-target="panel-view" href="#panel-view">View Queue</a></li>
		</ul>

	</div>

<?php
}


/******************************************************************************/

function bracketHelperToggleButton(){
// Creates a button to toggle the bracket helper on/off
// If the helper will go into a 'try' state if it is attempted to be enabled
// while there are incomplete pool matches, informing the user of the difficulty.

	$tournamentID = $_SESSION['tournamentID'];

	if($tournamentID == null){
		setAlert(SYSTEM,'No tournamentID in bracketHelperToggleButton()');
		return;
	}
	if(!isset($_SESSION['bracketHelper'])){
		$_SESSION['bracketHelper'] = '';
	}

// Checks if bracket helper is attempted to be turned on
// and turns on if no incomplete matches

	if($_SESSION['bracketHelper'] == 'try'){
		$_SESSION['incompletePoolMatches'] = getTournamentPoolIncompletes($tournamentID);
		if($_SESSION['incompletePoolMatches'] == null){
			$_SESSION['bracketHelper'] = 'on';
		}

	}

	// For the tooltip
	$descriptionText = "Automatically seeds based on pool placements,
			and suggests bracket advancements based on fight winners.";

	?>

<!-- Start display -->
	<?php if($_SESSION['bracketHelper'] == 'try'): ?>
		<div class='callout secondary'>
	<?php endif ?>

	<form method='POST' style='display:inline'>


<!-- Text for the toggle button -->
	<?php switch($_SESSION['bracketHelper']):
		case 'on': ?>
			<button class='button warning no-bottom' value='toggleBracketHelper'
				name='formName' <?=LOCK_TOURNAMENT?>>

				Disable Bracket Helper
			</button>
			<?php break;
		case 'try': ?>
			There are still incomplete pool matches.
			Finalize all matches before to ensure pool rankings are accurate.<BR>
			<input type='hidden' name='formName' value='toggleBracketHelper'>
			<button class='button secondary no-bottom' value='toggleBracketHelper'
				name='formName' <?=LOCK_TOURNAMENT?>>
				Cancel
			</button>
			<button class='button hollow warning no-bottom' value='true' name='bracketHelperOverride' <?=LOCK_TOURNAMENT?>>
				Override
			</button>

			<?php break;
		case 'off':
		default: ?>
			<button class='button hollow warning no-bottom' value='toggleBracketHelper' name='formName' <?=LOCK_TOURNAMENT?>>
				Enable Bracket Helper
			</button>
			<?php tooltip($descriptionText); ?>
			<?php break;
	endswitch; ?>

	</form>

<!-- Displays incomplete pool matches inhibiting bracket helper from enabling -->


	<?php
	if(isset($_SESSION['incompletePoolMatches'])){
		displayIncompleteMatches($_SESSION['incompletePoolMatches']);
	}
	?>

	<?php if($_SESSION['bracketHelper'] == 'try'): ?>
		</div>
	<?php endif ?>


<?php }

/******************************************************************************/

function bracketManagement($tournamentID, $doesBracketExist, $finalists){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

	?>

<!-- Bracket Management -->

	<fieldset class='fieldset'>
	<legend>Bracket Management</legend>

	<div class='grid-x grid-padding-x'>

	<!-- Create Bracket -->
		<?php if($doesBracketExist == false): ?>

			<div class='large-3 medium-4 cell'>
				<a class='button expanded no-bottom' data-open='createBracket' <?=LOCK_TOURNAMENT?>>
					Create Bracket
				</a>
			</div>

	<!-- Delete Bracket -->
		<?php else: ?>

			<div class='large-3 medium-4 cell'>
				<a class='button alert expanded no-bottom' data-open='deleteBracket' <?=LOCK_TOURNAMENT?>>
					Delete Bracket
				</a>
			</div>

		<?php endif ?>

		<div class='large-9 medium-8 cell text-right'>
			<a class='no-bottom' onclick="$('.seed-text').toggle()"><i>Show Seed Positions</i></a>
		</div>

	</div>
	</fieldset>


<!-- Create Bracket Box ------------------------------------------------------->

	<?php $maxBracketSize = 512; ?>
	<div class='reveal tiny grid-x grid-margin-x' id='createBracket' data-reveal>
	<form method='POST'>
	<fieldset <?=LOCK_TOURNAMENT?>>
		<h4>Create Bracket</h4>

		<form method='POST'>
		<input class='hidden' name='createBracket[tournamentID]' value='<?=$_SESSION['tournamentID']?>'>


		<div class='input-group'>
			<span class='input-group-label'>Bracket Size:</span>
			<input class='input-group-field' type='number' name='createBracket[sizePrimary]'
				min=2 max=<?=$maxBracketSize?> required >
		</div>


		<div class='input-group'>
			<span class='input-group-label'>
				Lower Bracket Size:&nbsp;
				<b><a onclick="$('#explain-double-elim-size').toggle()">(?)</a></b>
			</span>
			<input class='input-group-field' type='number' name='createBracket[sizeSecondary]'
				max=<?=$maxBracketSize?> placeholder='Single Elim'>
		</div>

		<div class='hidden callout' id='explain-double-elim-size'>
			<p><b>How The Lower Bracket Size Works</b><BR>
			The lower bracket size should be the "TopX" of people who will be able to go to the losers bracket. <u>In a standard double-elim make this the same as the Bracket Size</u>. The following examples use [Bracket Size | Lower Bracket Size].</p>
			<p><b>[32|32]</b> 32 people will go into the bracket, and all will go to the losers bracket after their first loss. (Normal way to do it.)</p>
			<p><b>[32|16]</b> 32 people will go into the bracket, but only the top16 go to the losers bracket. This means if you lose in the first round you are out, but if you make it to the top16 you will go to the losers bracket after your first loss.</p>
			<p><b>[16|32]</b> 16 people go to the upper bracket, and the rest of the top32 (#17-32) get seeded into the lower bracket as if they had lost their first round already.</p>

		</div>


		<div class='input-group'>
			<span class='input-group-label'>
				Double Elim Type:&nbsp;
				<b><a onclick="$('#explain-double-elim-type').toggle()">(?)</a></b>
			</span>
			<select class='input-group-field' name='createBracket[secondaryType]'>
				<option value='<?=ELIM_TYPE_CONSOLATION?>'>Consolation Bracket</option>
				<option value='<?=ELIM_TYPE_LOWER_BRACKET?>'>True Double Elim</option>
			</select>
		</div>

		<div class='hidden callout' id='explain-double-elim-type'>
			<p><b>Double Elim Type</b><BR>
			IGNORE THIS OPTION IF YOU ARE USING SINGLE ELIM</p>
			<p><u>Consolation Bracket</u>: If you lose once you go the the lower bracket to try to win bronze.</p>
			<p><u>True Double Elim</u>: The winner of the lower bracket can win gold if they defeat the upper bracket winner twice in a row.</p>
		</div>




	<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>
			<button class='button success small-6 cell' name='formName'
				value='createBracket' <?=LOCK_TOURNAMENT?>>
				Create
			</button>
			<button class='button secondary small-6 cell' data-close aria-label='Close modal'
				type='button' <?=LOCK_TOURNAMENT?>>
				Cancel
			</button>
		</div>

		</fieldset>
		</form>

	<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>


<!-- Delete Bracket Box ------------------------------------------------------->

	<div class='reveal' id='deleteBracket' data-reveal>
		<fieldset <?=LOCK_TOURNAMENT?>>
		<form method='POST'>
		<h4 class='text-center'>Warning!</h4>
		<p>You are about to erase all finals brackets for this tournament.<BR>
		This includes data from any matches in the Main and Secondary brackets.</p>

		<div class='grid-x grid-margin-x'>

			<button class='button alert small-6 cell' name='formName' value='deleteBracket'
				<?=LOCK_TOURNAMENT?>>
				Delete Bracket
			</button>
			<button class='button secondary small-6 cell' data-close aria-label='Close modal'
				type='button' <?=LOCK_TOURNAMENT?>>
				Cancel
			</button>

		</div>
		</fieldset>
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>

<?php }

/******************************************************************************/

function displayBracket($bracketInfo,
						$finalists,
						$bracketType,
						$bracketAdvancements,
						$elimType){
// Displays the bracket described by $bracket info

// Initialization
	if($bracketInfo == null){
		return;
	}

	$tournamentID = $bracketInfo['tournamentID'];
	$bracketLevels = $bracketInfo['bracketLevels'];
	$bracketID = $bracketInfo['groupID'];
	$teamEntry = isEntriesByTeam($tournamentID);
	$ringsInfo = (array)logistics_getEventLocations($_SESSION['eventID'],'ring');
	$bracketLevelsDisplayed = 0;
	$bracketLevelsToDisplay = $bracketLevels;

	$bracketMatches = getBracketMatchesByPosition($bracketID);

	if(    $bracketType == BRACKET_PRIMARY
		&& $elimType == ELIM_TYPE_SINGLE
		&& isset($bracketInfo['secondaryID']) == true){

		$bracketLevelsToDisplay++;

		$secondaryMatches = getBracketMatchesByPosition($bracketInfo['secondaryID']);
		$bracketMatches[1][2] = $bracketMatches[1][1];
		$bracketMatches[1][1] = $secondaryMatches[1][1];

	}

	if($elimType == ELIM_TYPE_TRUE_DOUBLE){
		$bracketLevelsToDisplay++;
		if($bracketType == BRACKET_PRIMARY && $bracketMatches[1][2]['winnerID'] != null){
			$bracketLevelsToDisplay++;
		}

	} elseif ($elimType == ELIM_TYPE_LOWER_BRACKET){
		$bracketLevelsToDisplay++;
	}

	// Warning if there are ties
	$numTies = getNumNoWinners($bracketID);
	if($numTies > 0 && $_SESSION['bracketHelper'] == 'on'){
		$_SESSION['bracketWarnings'][] = "
		<u>Warning:</u> One of the elimination bracket matches has ended with no winner.<BR>
		I hope you know what you are doing, because the bracket helper no longer does.
		";
	}


	// Change the background color if it's a secondary bracket.
	if($_SESSION['bracketView'] == BRACKET_SECONDARY){
		$bracketClass = "secondary-bracket";
	} else {
		$bracketClass = null;
	}


	// php to generate css based on bracket properties
	include('finalsCSS.php');
	?>

<!-- Buttons above bracket -->
	<form method='POST' id='bracketForm' style='display:inline;'>

	<input type='hidden' name='formName' value='updateBracket'>
	<input type='hidden' name='groupID' value='<?=$bracketID?>'>

	<?php if(ALLOW['EVENT_SCOREKEEP'] == true): ?>

		<button class= 'button success' name='updateBracket' value='newFighters' <?=LOCK_TOURNAMENT?>>
			Add Fighters
		</button>
		<button class= 'button alert' name='updateBracket' value='clearMatches' <?=LOCK_TOURNAMENT?>>
			Clear Selected
		</button>



		<?php if(isset($_SESSION['bracketWarnings'])):
			foreach($_SESSION['bracketWarnings'] as $type => $warning):

				if($bracketType == BRACKET_PRIMARY && $type == BRACKET_SECONDARY){
					continue;
				}
				?>

				<div class='callout warning' data-closable>
					<?=$warning?>
					<button class="close-button" aria-label="Dismiss secondary" type="button" data-close>
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			<?php endforeach ?>

		<?php unset($_SESSION['bracketWarnings']);
		endif?>

	<?php endif; ?>

<!-- Bracket display -->
	<div id='tournament_box' class='tournament_box <?=$bracketClass?>'>

	<?php
	for($currentLevel=$bracketLevels;$currentLevel >=1;$currentLevel--):

		$bracketLevelsDisplayed++;
		$extraLevelsNum = $bracketLevelsDisplayed - $bracketLevels;
		$seedList = @$bracketAdvancements[$currentLevel]; // may not exist, treat as null
		$tierName = getBracketLevelName($currentLevel, $bracketType, $elimType, $extraLevelsNum);


		echo"<div class='tier'>";

		echo "<h3 class='text-center'>{$tierName}</h3>";


		if($bracketType == BRACKET_PRIMARY){
			$maxMatchesAtLevel = pow(2,$currentLevel-1);
		} else {
			$maxMatchesAtLevel = getNumEntriesAtLevel_consolation($currentLevel,'matches');
		}

		for($bracketPosition = 1;$bracketPosition<=$maxMatchesAtLevel;$bracketPosition++):

			$secondaryMatch = false;

			$effectivePosition = $bracketPosition;
			if($currentLevel == 1){
				$effectivePosition += $bracketLevelsDisplayed - $bracketLevels;
			}

            // bracketPosition is the position of the match, this needs to be converted into
			// the position of the actual fighter in the bracket
			$seedText = getBracketSeedByPos(2*$bracketPosition-1,$currentLevel);
			$seedText .=" v ";
			$seedText .= getBracketSeedByPos(2*$bracketPosition,$currentLevel);

			if(isset($bracketMatches[$currentLevel][$effectivePosition]) == false){
				$matchInfo = null;
				$matchID = null;
				$isNotBlank = false;

				$seed1 = null;
				$seed2 = null;
			} else {


				$matchInfo = $bracketMatches[$currentLevel][$effectivePosition];
				$matchID = $matchInfo['matchID'];
				$isNotBlank = true;

				// may not exist, treat as null
				$seed1 = @$seedList[$effectivePosition][1]['rosterID'];
				$seed2 = @$seedList[$effectivePosition][2]['rosterID'];

			}

			$name = "depth{$currentLevel}";

			?>

<!--  Bracket level -->
		<div class='<?=$name?>'>
			<div class='centerCrap'>
			<div class='grid-x grid-padding-x text-center' style='width:<?=$boxWidth?>px;'>
				<div class='large-2 small-2 medium-2 align-self-middle text-center'>
					<?php if($isNotBlank){
						if($matchInfo['locationID'] != null){
							echo "<em>".logistics_getLocationName($matchInfo['locationID'], true)."</em>";
						}

						goToMatchButton($matchInfo);

						echo "<span style='font-size:0.85em; display:none' class='seed-text'>{$seedText}</span>";
					} ?>


				</div>
				<div class='large-10 small-10 medium-10'>

				<?php if($isNotBlank): ?>

					<?php bracket_finalistEntry(1,$matchInfo, $bracketInfo, $finalists,$seed1,$teamEntry); ?>
					<?php bracket_finalistEntry(2,$matchInfo, $bracketInfo, $finalists,$seed2,$teamEntry); ?>
					<a name='anchor<?=$matchID?>'></a>
				<?php endif ?>
				</div>
			</div>
			</div>

		<!-- Vertical Lines -->
			<?php if($currentLevel > 1 && $isNotBlank
				AND ($bracketType == BRACKET_PRIMARY || $currentLevel % 2 == 0)):

				if($bracketPosition % 2 != 0){
					$name .='_rightTop';
				} else {
					$name .= '_rightBottom';
				} ?>

				<div class='<?=$name?>'>
				</div>
			<?php endif ?>


			</div>


			<?php if($currentLevel == 1 && $bracketLevelsDisplayed < $bracketLevelsToDisplay){
					$currentLevel++;
			}?>

		<?php endfor ?>
		</div>

	<?php endfor ?>

	</div>

	</form>

<?php }

/******************************************************************************/

 function bracket_finalistDropDown($fighterNum,$matchID, $finalists, $seedID, $isTeams){
// Creates a drop down list of all the fighters provided
// Used in elimination brackets

	$rankedNames = $finalists;
	$tournamentID = $_SESSION['tournamentID'];
	?>

	<select name='newFinalists[<?=$matchID?>][<?=$fighterNum?>]' class='bracket-select' <?=LOCK_TOURNAMENT?>>
		<option value=''></option>
		<?php foreach($rankedNames as $fighter):
			if(isset($fighter['rank']) AND $_SESSION['bracketHelper'] != 'on'){
				$rank = "#{$fighter['rank']} - ";
			} else {
				$rank = '';
			}
			$selected = isSelected($fighter['rosterID'], $seedID);
			?>

			<option value='<?=$fighter['rosterID']?>' <?=$selected?>>
				<?=$rank?><?=getFighterName($fighter['rosterID'],null,null,$isTeams)?>
			</option>

		<?php endforeach ?>
	</select>
<?php }

/******************************************************************************/

function bracket_finalistEntry($fighterNum,$matchInfo, $bracketInfo, $finalists, $seedID, $teamEntry){
// Creates entry field or displays previously entered fighter
// for the bracket position specified

	$matchID = $matchInfo['matchID'];
	if($fighterNum == 1){
		$fighterID = 'fighter1ID';
		$color = COLOR_CODE_1;
	}else if($fighterNum == 2){
		$fighterID = 'fighter2ID';
		$color = COLOR_CODE_2;
	}else {
	 displayAlert("Error in 'finalistEntry' !!!!!!");
	}
	?>

<!-- If no data exists for the bracket position	-->
	<?php if($matchInfo[$fighterID] == 0 || $matchInfo[$fighterID] == null):

		$class = '';
		if($fighterNum == 1){
			$class = "bracket-top-slot";
		}
		?>

		<div class='<?=$class?>'>

		<?php
			if(ALLOW['EVENT_SCOREKEEP'] == true){
				// Staff and higher can add fighters
				bracket_finalistDropDown($fighterNum, $matchID, $finalists, $seedID,$teamEntry);
			} else {
				// Blank for guests
				echo "&nbsp;";
			}
		?>

		</div>


<!-- If data exists for the bracket position -->
	<?php else:
		$name = getFighterName($matchInfo['fighter'.$fighterNum.'ID'],null,null,$teamEntry);
		$score = $matchInfo['fighter'.$fighterNum.'Score'];
		$style = '';

		$teamFighters = "";
		if($teamEntry == true){
			$roster = getTeamRoster($matchInfo['fighter'.$fighterNum.'ID']);
			foreach($roster as $rosterID){
				$teamFighters .= "<li>".getFighterName($rosterID)."</li>";
			}
		}

		// If is match winner
		if ($matchInfo['fighter'.$fighterNum.'ID'] == $matchInfo['winnerID']){
			$style .= "font-weight: bold; ";
		}

		$class = '';
		if($fighterNum == 1){
			$class = "bracket-top-slot";
		}

		if(ALLOW['EVENT_SCOREKEEP'] == true){	// shows fighter color for staff only
			$style .= "background-color: {$color}; ";
		}
		?>

		<div class='<?=$class?>' style='<?=$style?> padding-left: 3px;'>
			<?=$score?> <?=$name?>
			<?php if($teamFighters != ""){
				tooltip($teamFighters);
			}?>
		</div>

	<?php endif ?>

<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
