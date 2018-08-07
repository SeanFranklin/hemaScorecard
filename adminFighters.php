<?php
/*******************************************************************************
	Fighter Management
	
	Withdraw fighters if they are injured and can no longer compete
	LOGIN:
		- ADMIN or higher required to access
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Manage Fighters';
$lockedTournamentWarning = true;
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];

if(USER_TYPE < USER_ADMIN){
	pageError('user');
} else if($tournamentID == null){
	pageError('tournament');
} else if(!isRounds($tournamentID) && !isPools($tournamentID)){
	displayAlert("No need to withdraw fighters from this tournament format");
} else {

	$roster = getTournamentRoster($tournamentID,'rosterID','full');

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>	
	
<!-- Navigate pool sets -->

	<div class='grid-x grid-padding-x'>
	<div class='large-7 medium-8 small-12'>
	
	<form method='POST'>
	<fieldset <?=LOCK_TOURNAMENT?>>
	<input type='hidden' name='formName' value='ignoreFightersInTournament'>
	<input type='hidden' name='tournamentID' value='<?=$tournamentID?>'>
	<table>
		<?php 
			if(isBrackets()){
				poolBracketRemoves($roster);
			} elseif(isRounds()){
				scoredRoundsRemoves($roster);
			} else {
				poolSetRemoves($roster);
			} 
		?>
	</table>
	<BR>
	<button class='button large success' name='updateTournament' 
		value='<?=$tournamentID?>' <?=LOCK_TOURNAMENT?>>
		Update List
	</button>
	</fieldset>
	</form>
		
	</div>
	</div>


<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function scoredRoundsRemoves($roster){
	$numGroupSets = getNumGroupSets();
	$stops = getStops();
	
	// Text for tooltips
	$text = "Fighter can no longer advance to subsequent rounds or stages";
	?>
	
	<input type='hidden' name='ignoreMode' value='groupSet'>
	<table>
		<tr>
			<th>
				Name
			</th>
			<th>
				Can not advance
				<?php tooltip($text); ?>
			</th>
		</tr>

	<?php foreach($roster as $rosterID => $fighter): 
		if($stops[$rosterID] > 0){
			$stopCheck = 'checked';
		} else {
			$stopCheck = '';
		}
		
		?>
		<tr>
			<td>
				<?=getFighterName($rosterID)?>
			</td>
				
	<!-- Keep fighter pool results but don't advance them to the bracket -->
		<td>
		<div class='switch text-center no-bottom'>
			<input type='hidden' name='finalsIgnores[<?=$rosterID?>]' value='0'>
			<input class='switch-input' type='checkbox' id='finalsIgnores[<?=$rosterID?>]' 
				name='finalsIgnores[<?=$rosterID?>]' value='1' <?=$stopCheck?>>
			<label class='switch-paddle' for='finalsIgnores[<?=$rosterID?>]'>
			</label>
		</div>
		</td>
		</tr>	
	<?php endforeach ?>
	
	</table>
	
<?php }

/******************************************************************************/

function poolSetRemoves($roster){
	$numGroupSets = getNumGroupSets();
	$ignores = getIgnores();
	$stops = getStops();
	
	// Text for tooltips
	$text = "Removes all matches involving the fighter from scoring calculations 
		starting in this pool set, leaving older sets unaffected.<BR><BR>
		Use if the fighter has been injured or disqualified before completing their pool.";
	$text2 = "Fighters have all matches and scores remain unchanged but will not
		advance to the next set. <BR><BR> Use if a fighter has completed their pool
		without incident but must withdraw from the tournament.";
	?>
	
	<input type='hidden' name='ignoreMode' value='groupSet'>
	<table>
		<tr>
			<th>
				Name
			</th>
			<th>
				Remove from set number
				<?php tooltip($text); ?>
			</th>
			<th>
				Can not advance
				<?php tooltip($text2); ?>
			</th>
		</tr>

	<?php foreach($roster as $rosterID => $fighter): 
		if(!isset($ignores[$rosterID])){
			$oldVal = 0;
		} else {
			$oldVal = $ignores[$rosterID];
		}
		if($stops[$rosterID] > 0){
			$stopCheck = 'checked';
		} else {
			$stopCheck = '';
		}
		
		?>
		<tr>
			<td>
				<?=getFighterName($rosterID)?>
			</td>
			
		<!-- Remove from scoring in a set -->
			<td>
				<input type='hidden' name='ignoreFightersOld[<?=$rosterID?>]' value='<?=$oldVal?>'>
				<select name = 'ignoreFightersNew[<?=$rosterID?>]'>
					<option value=0></option>
					<?php for($i=$numGroupSets;$i>0; $i--):
						$name = getSetName($i);
						$selected = isSelected($i,$oldVal);
						?>
					<option value=<?=$i?> <?=$selected?>><?=$name?></option>
					
				<?php endfor ?>
				</select>
			</td>
			
	<!-- Keep fighter pool results but don't advance them to the bracket -->
		<td>
		<div class='switch text-center no-bottom'>
			<input type='hidden' name='finalsIgnores[<?=$rosterID?>]' value='0'>
			<input class='switch-input' type='checkbox' id='finalsIgnores[<?=$rosterID?>]' 
				name='finalsIgnores[<?=$rosterID?>]' value='1' <?=$stopCheck?>>
			<label class='switch-paddle' for='finalsIgnores[<?=$rosterID?>]'>
			</label>
		</div>
		</td>
		</tr>	
	<?php endforeach ?>
	
	</table>
	
<?php }

/******************************************************************************/

function poolBracketRemoves($roster){
	$ignores = getIgnores();
	$stops = getStops();

	// Text for tooltips
	$text = "Removes all matches involving the fighter from scoring calculations. 
		Use if the fighter has been injured/disqualified before 
		they have completed all of their pool matches.";
	$text2 = htmlspecialchars("Selecting this will remove a fighter 
		from the list of fighters advancing to the finals, 
		but not affect their score or matches from the pools. 
		Use if a fighter withdraws between completion 
		of the pool and start of the finals.");
	
	?>
	
<!-- Header row -->
	<table>
		<th>
			Name
		</th>
		<th>
			Remove From Scoring
			<?php tooltip($text); ?>
		</th>
		<th>
			Don't Advance to Bracket
			<?php tooltip($text2); ?>
		</th>
		
	
<!-- Ignore all of a fighter's pool matches -->	
	<?php foreach($roster as $rosterID => $fighter): 

		if(isset($ignores[$rosterID]) && $ignores[$rosterID] > 0){
			$oldIgnore = 1;
			$ignoreCheck = 'checked';
		} else {
			$oldIgnore = 0;
			$ignoreCheck = '';
		}
		if($stops[$rosterID] > 0){
			$stopCheck = 'checked';
		} else {
			$stopCheck='';
		}
		?>
	
		<input type='hidden' name='ignoreMode' value='groupSet'>
		<tr>
			<td>
				<?=getFighterName($rosterID)?>
				</td>
			<td>
			<div class='switch text-center no-bottom'>
				<input type='hidden' name='ignoreFightersOld[<?=$rosterID?>]' value='<?=$oldIgnore?>'>
				<input class='switch-input' type='checkbox' id='poolIgnores[<?=$rosterID?>]' 
					name = 'ignoreFightersNew[<?=$rosterID?>]' value='1' <?=$ignoreCheck?>>
				<label class='switch-paddle' for='poolIgnores[<?=$rosterID?>]'>
				</label>
			</div>
			</td>
				
		<!-- Keep fighter pool results but don't advance them to the bracket -->
			<td>
			<div class='switch text-center no-bottom'>
				<input type='hidden' name='finalsIgnores[<?=$rosterID?>]' value='0'>
				<input class='switch-input' type='checkbox' id='finalsIgnores[<?=$rosterID?>]' 
					name='finalsIgnores[<?=$rosterID?>]' value='1' <?=$stopCheck?>>
				<label class='switch-paddle' for='finalsIgnores[<?=$rosterID?>]'>
				</label>
			</div>
			</td>
		</tr>
	<?php endforeach ?>
	
	</table>
	
	
<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
