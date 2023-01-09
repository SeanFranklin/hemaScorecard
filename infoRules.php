<?php 
/*******************************************************************************
	Tournament Rules
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Tournament Rules";
$jsIncludes[] = "sortable_scripts.js";

include('includes/header.php');

// Get the event List
if((int)$_SESSION['eventID'] == 0){
	pageError('event');
} else if(ALLOW['VIEW_RULES'] == false){
	displayAlert("Rules for this event have not been published.");
} else {
	
	
	$rulesIDs = (array)getEventRules($_SESSION['eventID']);

	if((int)$_SESSION['rulesID'] != 0){
		$rulesInfo = getRulesInfo($_SESSION['rulesID']);
	} else {
		$rulesInfo['rulesName'] = "";
		$rulesInfo['rulesText'] = "";

		// If we went to this page with no rules loaded, refresh page with the first one.
		if($rulesIDs != [] && ALLOW['EVENT_MANAGEMENT'] == false){
		?>
			<script type="text/javascript">
				window.onload = function(){
					var input = document.getElementById('change-rules-select');
					input.value = <?=$rulesIDs[0]?>;
					submitForm('change-active-rules','changeRulesID');
				}
			</script>
		<?php
		}

	}

	$baseUrl = "http://$_SERVER[HTTP_HOST]";
	$linkPath = $baseUrl."/infoRules.php?e=".$_SESSION['eventID']."&r=".$_SESSION['rulesID'];

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Navigate Rules -------------------------------------->

	<form method="POST" id='change-active-rules'>
		<div class='grid-x grid-margin-x'>
		<div class='medium-8 large-6 cell input-group no-bottom'>
			<span class='input-group-label no-bottom hide-for-small-only'>Select Rules:</span>
			<span class='input-group-label no-bottom show-for-small-only'>Rules:</span>
			<select class='input-group-field no-bottom' name='changeRules[rulesID]' 
				id='change-rules-select' onchange="submitForm('change-active-rules','changeRulesID')">

				<?php if(ALLOW['EVENT_MANAGEMENT'] == true):?>
					<option value='0'>-- Add New --</option>
				<?php endif ?>

				<?php foreach($rulesIDs as $rulesID):?>
					<option <?=optionValue($rulesID,$_SESSION['rulesID'])?>>
						<?=getRulesName($rulesID)?>
					</option>
				<?php endforeach ?>

			</select>
		</div>
		<div class='medium-4 large-6 cell input-group no-bottom align-self-middle'>
			<?php if((int)$_SESSION['rulesID'] != 0):?>
				<a href="<?=$linkPath?>">Permalink to <i><?=$rulesInfo['rulesName']?></i></a>
			<?php endif ?>
		</div>
		</div>
	</form>

	

<!-- Show Rules -------------------------------------->

	<?=reOrderRules($rulesIDs)?>
		
	<?=editRules($rulesInfo)?>
		
	<?=displayRules($rulesInfo)?>


<?
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function reOrderRules($rulesIDs){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

?>
	<div class='grid-x grid-margin-x'>
	<div class='large-12 cell'>
		<a class='re-order-rules' onclick="$('.re-order-rules').toggle()">Re-Order of Rules ↓</a>
		<h5 class='re-order-rules hidden'><BR><a  onclick="$('.re-order-rules').toggle()">Re-Order of Rules ↑</a></h5>
	</div>

	<div class='large-6 cell re-order-rules hidden callout text-right'>
	<form method="POST">

		<div  id='sort-rules-order'>
			<?php foreach($rulesIDs as $rulesID):?>
				<div class='callout primary text-left' value=<?=$rulesID?>>
					<?=getRulesName($rulesID)?>
				</div>
			<?php endforeach ?>
		</div>

		<?php foreach($rulesIDs as $index => $rulesID): ?>
			<input class='hidden' name='orderRules[rulesIDs][<?=$rulesID?>]' 
				id='rules-order-for-<?=$rulesID?>' value=<?=$index?>>
		<?php endforeach ?>

		<button class='button success no-bottom' name='formName' value='orderRules'>
			Update Rules Order
		</button>

	</form>
	</div>
</div>

<?php
}

/******************************************************************************/

function displayRules($rulesInfo){
?>

	<HR>

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

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		return;
	}

	$tournamentIDs = getEventTournaments($_SESSION['eventID']);

	if(isset($rulesInfo['tournamentIDs']) == false || $rulesInfo['tournamentIDs'] == []){
		$hideToggle = "hidden";
		$hideInput = "";
	} else {
		$hideToggle = "";
		$hideInput = "hidden";
	}

?>

	<HR>

	<form method="POST">
	
		<div class='grid-x grid-margin-x'>
		<div class='medium-8 large-6 cell input-group'>
			<h4 class='input-group-label hide-for-small-only'>Name of Rules:</h4>
			<h4 class='input-group-label show-for-small-only'>Name:</h4>
			<input class='input-group-field' type='text' 
				name='updateRules[rulesName]' value="<?=$rulesInfo['rulesName']?>"
				placeholder='Add new ruleset' required>
		</div>

		<div class='large-12 cell'>
			<a class=" <?=$hideToggle?>" onclick="$('.attach-rules').toggle()"><p>Attach Rules To Tournaments ↓</p></a>
		</div>


		<div class='medium-8 large-6 cell <?=$hideInput?> attach-rules'>
			<i>The rules <b><?=$rulesInfo['rulesName']?></b> apply to the following tournaments:</i>
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
