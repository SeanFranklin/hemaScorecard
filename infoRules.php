<?php 
/*******************************************************************************
	Tournament Rules
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Tournament Rules";

include('includes/header.php');

// Get the event List
if((int)$_SESSION['eventID'] == 0){
	pageError('event');
} else if(ALLOW['VIEW_RULES'] == false){
	displayAlert("Rules for this event have not been published.");
} else {
	
	
	$rulesIDs = getEventRules($_SESSION['eventID']);

	if((int)$_SESSION['rulesID'] != 0){
		$rulesInfo = getRulesInfo($_SESSION['rulesID']);
	} else {
		$rulesInfo['rulesName'] = "";
		$rulesInfo['rulesText'] = "";
	}

	$baseUrl = "http://$_SERVER[HTTP_HOST]";
	$linkPath = $baseUrl."/infoRules.php?e=".$_SESSION['eventID']."&r=".$_SESSION['rulesID'];

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<form method="POST">
		<div class='grid-x grid-margin-x'>
		<div class='medium-8 large-6 cell input-group no-bottom'>
			<span class='input-group-label no-bottom'>Select Rules:</span>
			<select class='input-group-field no-bottom' name='changeRules[rulesID]'>
				<option value='0'>
					<?php if(ALLOW['EVENT_MANAGEMENT'] == true):?>
						-- Add New --
					<?php endif ?>
				</option>
				<?php foreach($rulesIDs as $rulesID):?>
					<option <?=optionValue($rulesID,$_SESSION['rulesID'])?>>
						<?=getRulesName($rulesID)?>
					</option>
				<?php endforeach ?>
			</select>
			<button class='input-group-button button no-bottom' name='formName' value='changeRulesID'>Change</button>
		</div>
		<div class='medium-4 large-6 cell input-group no-bottom'>
			<?php if((int)$_SESSION['rulesID'] != 0):?>
				<a href="<?=$linkPath?>">Permalink to <i><?=$rulesInfo['rulesName']?></i></a>
			<?php endif ?>
		</div>
		</div>
	</form>

	<hr>

	<?php 
		if(ALLOW['EVENT_MANAGEMENT'] == true){
			editRules($rulesInfo);
			echo "<HR>";
		}

		displayRules($rulesInfo);
	?>

<?
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayRules($rulesInfo){
?>


	<div class='documentation-div'>
		<a name='top-of-rules'></a>
		<div class='callout primary'>
			<span id='rules-title'><?=$rulesInfo['rulesName']?></span>
		</div>

		<?=$rulesInfo['rulesText']?>
	</div>

<?
}

/******************************************************************************/

function editRules($rulesInfo){

	$tournamentIDs = getEventTournaments($_SESSION['eventID']);

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

?>
	<form method="POST">
	
		<div class='grid-x grid-margin-x'>
		<div class='medium-8 large-6 cell input-group'>
			<span class='input-group-label'>Name of Rules:</span>
			<input class='input-group-field' type='text' 
				name='updateRules[rulesName]' value="<?=$rulesInfo['rulesName']?>"
				placeholder='Add new ruleset' required>
		</div>
		</div>

		<div class='grid-x grid-margin-x'>
		<div class='medium-8 large-6 cell'>
			<b>Applies To:</b>
			<?php if(count($tournamentIDs) == 0):?>
				<BR>&emsp;<i>Event has no tournaments created</i><BR><BR>
			<?php else: ?>
			<table class='cell'>

				<?php foreach($tournamentIDs as $tournamentID):?>

					<tr>
						<td><?=getTournamentName($tournamentID)?></td>
						<td>
							<div class='switch text-center no-bottom'>
							<input type='hidden' name='updateRules[tournamentIDs][<?=$tournamentID?>]' value='0'>
							<input class='switch-input' type='checkbox' <?=chk(@$rulesInfo['tournamentIDs'][$tournamentID])?>
								id='updateRules[tournamentIDs][<?=$tournamentID?>]' 
								name='updateRules[tournamentIDs][<?=$tournamentID?>]' value='1'>
							<label class='switch-paddle' for='updateRules[tournamentIDs][<?=$tournamentID?>]'>
							</label>
							</div>
						</td>
					</tr>

				<?php endforeach ?>
			<?php endif ?>
			</table>
		</div>
		</div>

		<textarea name='updateRules[rulesText]' required rows='20'><?=$rulesInfo['rulesText']?></textarea>

		<input type='hidden' name='updateRules[rulesID]' value='<?=$_SESSION['rulesID']?>'>
		<input type='hidden' name='updateRules[eventID]' value='<?=$_SESSION['eventID']?>'>
		<button class='button success' name="formName" value="updateRules">Update Rule-set</button>
		<a class='button alert' data-open='deleteRules'>
			Delete Rule-set
		</a>
		
	</form>


<!-- Delete Rules -->
	<div class='reveal tiny' id='deleteRules' data-reveal>
		<form method='POST'>
		<input type='hidden' name='deleteRules[rulesID]' value='<?=$_SESSION['rulesID']?>'>

		Are you sure you want to <b>permanently</b> delete these rules?
		<HR>

	<!-- Submit buttons -->
		<div class='grid-x grid-margin-x'>
			<button class='success button small-6 cell alert no-bottom' name='formName' value='deleteRules'>
				Yes. Bye bye!
			</button>
			<button class='secondary button small-6 cell no-bottom' data-close aria-label='Close modal' type='button'>
				No. They live.
			</button>
		</div>
		</form>
		
	<!-- Reveal close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	
	</div>

<?
}


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
