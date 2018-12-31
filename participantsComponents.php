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
} else{

	$tournamentList = getTournamentsFull();
	$tournamentComponents = getTournamentComponents($tournamentID);
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Page Structure -->
	<?php if(ALLOW['EVENT_MANAGEMENT'] == false): ?>
		<ul>
			<?php foreach($tournamentComponents['fullList'] as $tournamentID): ?>
				<li><?=getTournamentName($tournamentID)?></li>
			<?php endforeach ?>
		</ul>
	<?php else: ?>


	
		<form method='POST' id='tournamentComponentsForm'>
		<fieldset <?=LOCK_TOURNAMENT?>>
		
		<div class='grid-x grid-padding-x'>
		<div class='large-6 medium-8 cell'>
		<h4>Tournament Components</h4>					
		<input type='hidden' name='compositeTournament[tournamentID]' 
			value=<?= $_SESSION['tournamentID']?>>

		<table>
			<tr>
				<th>Tournament</th>
				<th>Used?</th>
			</tr>

			<?php foreach($tournamentList as $tournamentID => $info):
				if($tournamentID == $_SESSION['tournamentID']){ continue;}
				if(isset($tournamentComponents[$tournamentID])){
					$isComponent = 'checked';
				} else {
					$isComponent = '';
				}	
				?>
				<tr>
					<td>
						<?=getTournamentName($tournamentID)?>
					</td>
					<td>

						<input type='hidden' value='0'
						name='compositeTournament[components][<?=$tournamentID?>]'>

						<input type='checkbox' 
						name='compositeTournament[components][<?=$tournamentID?>]' 
						value='1' <?=$isComponent?> >

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
