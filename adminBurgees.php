<?php
/*******************************************************************************


*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'School Standings';

include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else {

	$tournamentIDs = getEventTournaments();
	$rankingIDs = getBurgeeRankings();
	$burgeeIDs = getEventBurgees($_SESSION['eventID']);

	$numBurgees = count($burgeeIDs);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

	rankingExplainReveal($rankingIDs);

	echo "<ul class='accordion' data-accordion data-allow-all-closed='true'>";

	foreach($burgeeIDs as $burgeeID){
		burgeeInput($burgeeID, $tournamentIDs, $rankingIDs, $numBurgees);
	}

	burgeeInput(0, $tournamentIDs, $rankingIDs, $numBurgees);

	echo "</ul>";

}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function rankingExplainReveal($rankingIDs){

	$rankingsToDisplay = [];
	foreach($rankingIDs as $r){

		$rankingsToDisplay[$r['burgeeRankingID']]['params'] = getBurgeeRankingParameters($r['burgeeRankingID']);
		$rankingsToDisplay[$r['burgeeRankingID']]['name'] = $r['rankingName'];
	}


?>
	<div class='reveal large' id='burgee-ranking-explain-box' data-reveal>

		<h4>Ranking Choices:</h4>

		<div class='callout primary'>
			This feature is still under development, and ranking choices are limited. If you are wanting something specific please contact the HEMA Scorecard team and we'll see if we can prioritize that mode for you.
		</div>

		<p><b class='red-text'>Note:</b> Each club member is counted only once with their best result. (No matter how many tournaments they win.)</p>


		<?php foreach($rankingsToDisplay as $ranking){

			echo "<ul><u><b>{$ranking['name']}</b></u>";
			displayBurgeeRankingExplanation($ranking['params']);
			echo "</ul>";
		}?>


		<!-- Reveal close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>
<?php
}

/******************************************************************************/

function burgeeInput($burgeeID, $tournamentIDs, $rankingIDs, $numBurgees){

	$hiddenClass = "hidden";
	if($burgeeID != 0){
		$info = getBurgeeInfo($burgeeID);
		$title = $info['burgeeName'];
		$buttonText = "Update";
		if($numBurgees == 1){
			$hiddenClass = "";
		}
	} else {
		$info['burgeeName'] = '';
		$info['burgeeRankingID'] = 0;
		$info['hideBurgee'] = 0;
		$title = "** Add New **";
		$buttonText = "Add";
		if($numBurgees == 0){
			$hiddenClass = "";
		}
	}

	$nameStart = "burgeeInfo[".$burgeeID."]";

?>

	<li class="accordion-item" data-accordion-item>

		<a href="#" class="accordion-title">
			<h4 >
				<?=$title?>
				<?php if((int)$info['hideBurgee'] == 1): ?>
					<i style='font-size:0.6em'>(hidden)</i>
				<?php endif ?>
			</h4>

		</a>

		<div class="accordion-content" data-tab-content>
		<form method="POST">
		<div class='grid-x grid-margin-x'>

			<input class='hidden' name='<?=$nameStart?>[eventID]' value=<?=$_SESSION['eventID']?>>
			<input class='hidden' name='<?=$nameStart?>[burgeeID]' value=<?=$burgeeID?>>

			<div class='large-5 cell'>
				Name:
				<input type='text' name='<?=$nameStart?>[burgeeName]' required
					value='<?=$info['burgeeName']?>'
					placeholder="eg: 'Overall School Points'">
			</div>

			<div class='large-4 cell'>

				Ranking:
				<a data-open="burgee-ranking-explain-box" >
					(?)
				</a>

				<select name='<?=$nameStart?>[burgeeRankingID]' required>
					<option disabled selected></option>
					<?php foreach($rankingIDs as $ranking):?>
						<option <?=optionValue($ranking['burgeeRankingID'],$info['burgeeRankingID'])?>>
							<?=$ranking['rankingName']?>
						</option>
					<?php endforeach ?>
				</select>
			</div>

			<div class='large-3 cell'>
				Name:
				<div class='input-group'>
					<span class='input-group-label'>
						Visibility <?=tooltip("Your participants will not be able to see this if you chose to hide it.")?>
					</span>
					<select name='<?=$nameStart?>[hideBurgee]' class='input-group-field'>
						<option <?=optionValue(0, $info['hideBurgee'])?>>Public</option>
						<option <?=optionValue(1, $info['hideBurgee'])?>>Hidden</option>
					</select>
				</div>
			</div>

			<div class='large-12 cell'>
				Tournaments Included In Calculation:
			</div>
			<?php foreach($tournamentIDs as $tID):
				if(isset($info['components'][$tID]) == true){
					$set = 1;
				} else {
					$set = 0;
				}
				$paddleName = "{$nameStart}[components][{$tID}]";

				?>
				<div class='large-3 medium-4 cell text-center'>
					<BR>
					<?=getTournamentName($tID)?>
					<?=checkboxPaddle($paddleName,1,$set,0)?>
				</div>
			<?php endforeach ?>

			<div class='large-12 cell'>
				<HR>
			</div>

			<div class='large-3 medium-6 cell'>
				<button class='button success expanded' name='formName' value='burgeeInfo'>
					<?=$buttonText?>
				</button>
			</div >

			<?php if($burgeeID != 0):?>
				<div class='large-3 medium-6 cell'>
					<a class='button alert no-bottom expanded' data-open="delete-burgee-<?=$burgeeID?>-box">
						Delete
					</a>
				</div >

				<div class='large-2 medium-6 cell'>
				</div>

				<div class='large-4 medium-6 cell'>
					<a class='button hollow no-bottom expanded' onclick="$('.extra-burgee-<?=$burgeeID?>').toggle()">
						Show Me The Current Standings ↓
					</a>
				</div >
			<?php endif ?>

		</div>
		</form>


		<div class='hidden extra-burgee-<?=$burgeeID?>'>
			<?=burgeeDisplay($burgeeID)?>
		</div>

		</div>

	</li>


	<?=deleteBurgeeRevealBox($burgeeID, $title)?>

<?php
}

/******************************************************************************/

function deleteBurgeeRevealBox($burgeeID, $title){

	if($burgeeID == 0){
		return;
	}

?>
	<div class='reveal medium' id='delete-burgee-<?=$burgeeID?>-box' data-reveal>

		<h4>Do you really want to delete <b><?=$title?></b>?</h4>

		<form method="POST">
		<div class='grid-x grid-margin-x'>

			<input type='hidden' name='deleteBurgee[eventID]' value=<?=$_SESSION['eventID']?>>
			<input type='hidden' name='deleteBurgee[burgeeID]' value=<?=$burgeeID?>>

			<button class='button alert cell large-6' name='formName' value='deleteBurgee'>
				Delete
			</button>

			<a class='button cell large-6 secondary align-middle' data-close>
				Cancel
			</a>

		</div>
		</form>

		<!-- Reveal close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>
<?php
}

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
