<?php
/*******************************************************************************
	Tournament Components
	
	View and change the tournament components.
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Tournament Components';
$includeTournamentName = true;
$lockedTournamentWarning = true;
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];
if($tournamentID == null){
	pageError('tournament');
} elseif($_SESSION['formatID'] != FORMAT_COMPOSITE){
	redirect('participantsTournament.php');
} else {

	$tournamentList = [];
	$tournamentList = getTournamentsFull();
	$tournamentComponents = getTournamentComponents($tournamentID);

	foreach($tournamentList as $tournamentID => $info){

		// A tournament can't be it's own component.
		if($tournamentID == $_SESSION['tournamentID']){
			unset($tournamentList[$tournamentID]);
			continue;
		}

		$useResult[$tournamentID] = '';
		$useRoster[$tournamentID] = '';
	}

	$isExclusive = '';
	foreach($tournamentComponents as $component){
		$cTournamentID = $component['cTournamentID'];

		if($component['useResult'] != 0){
			$useResult[$cTournamentID] = 'checked';
		}

		if($component['useRoster'] != 0){
			$useRoster[$cTournamentID] = 'checked';
		}

		if($component['isExclusive'] != 0){
			$isExclusive = 'checked';
		}
	}


	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Page Structure -->
	<?php if(ALLOW['EVENT_MANAGEMENT'] == false): ?>
		<ul>
			<?php foreach($tournamentComponents as $component): ?>
				<li><?=getTournamentName($component['cTournamentID'])?></li>
			<?php endforeach ?>
		</ul>
	<?php else: ?>


	
		<form method='POST' id='tournamentComponentsForm'>
		<fieldset <?=LOCK_TOURNAMENT?>>
		
		<div class='grid-x grid-padding-x'>
		<div class='large-8 medium-10 cell'>
		<h4>Tournament Components</h4>					
		<input type='hidden' name='compositeTournament[tournamentID]' 
			value=<?= $_SESSION['tournamentID']?>>

		<div class='input-group medium-6 small-12 no-bottom'> 
			<span class='input-group-label'>
				Exclusive Roster &nbsp;
				<?php tooltip("<b>Non-Exclusive</b>:<BR>
							Enters <u>all</u> fighters who are entered in all the 
							tournaments with <i>Use Roster</i> selected.
							<BR><BR><b>Exclusive</b>:<BR>
							Enters fighters who are only entered in the tournaments with
							<i>Use Roster</i> selected. (Any other tournament entries invalidate them.)
							")?>:
			</span>
			<div class='switch input-group-button large no-bottom'>
				<input type='hidden' name='compositeTournament[isExclusive]' value='0'>
				<input class='switch-input' type='checkbox' id='compositeTournament[isExclusive]' 
					name='compositeTournament[isExclusive]' value='1' <?=$isExclusive?>>
				<label class='switch-paddle' for='compositeTournament[isExclusive]'>
				</label>
			</div>
		</div>


		<table>
			<tr>
				<th>Tournament</th>
				<th>
					Use Results?
					<?=tooltip("Scores from selected events will be used in the final placings for
								the composite event.")?>
				</th>
				<th>
					Use Roster?
					<?=tooltip("The roster for the composite tournament will be all individuals who have registered in <u>all</u> events with 'Use Roster' selected.")?>
				</th>
			</tr>

			<?php foreach($tournamentList as $tournamentID => $info):
				$fName = "compositeTournament[tournamentIDs][{$tournamentID}]";
				?>

				<tr>
					<td>
						<?=getTournamentName($tournamentID)?>
					</td>
					<td>
						<div class='switch no-bottom input-group-field'>
							<input class='switch-input' type='hidden'  
								name='<?=$fName?>[useResult]' value=0>
							<input class='switch-input polar-disables' type='checkbox' 
								id='<?=$fName?>[useResult]' name='<?=$fName?>[useResult]' 
								value=1 <?=$useResult[$tournamentID]?> >
							<label class='switch-paddle' for='<?=$fName?>[useResult]'>
							</label>
						</div>
					</td>

					<td>
						<div class='switch no-bottom input-group-field'>
							<input class='switch-input' type='hidden'  
								name='<?=$fName?>[useRoster]' value=0>
							<input class='switch-input polar-disables' type='checkbox' 
								id='<?=$fName?>[useRoster]' name='<?=$fName?>[useRoster]' 
								value=1 <?=$useRoster[$tournamentID]?> >
							<label class='switch-paddle' for='<?=$fName?>[useRoster]'>
							</label>
						</div>
					</td>
				</tr>
			<?php endforeach ?>
		</table>
		<button class='button success' name='formName' value='updateTournamentComponents'>
			Update
		</button>
			
		</fieldset>
		</form>	
	<?php endif ?>
		
<?php 		
	
}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
