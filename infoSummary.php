<?php 
/*******************************************************************************
	Info Summary
	
	Displays all the tournament medalists
	Login: 
		- ADMIN or above can add/remove final medalists 
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Final Results";
include('includes/header.php');

if($_SESSION['eventID'] == null){
	displayAnyErrors('No Event Selected');
} else {

	$tournamentList = getTournamentsFull();

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<div class='grid-x'>
			
	<?php foreach((array)$tournamentList as $tournamentID => $data):
		
		$placings = getTournamentPlacings($tournamentID);
		$name = getTournamentName($tournamentID); 
		?>
		
		<fieldset class='large-7 medium-10 small-12 fieldset'>
			<a name='anchor<?=$tournamentID?>'></a>
			<legend><h4><?= $name ?></h4></legend>
		
		<!-- If no tournament results -->	
			 <?php if(!isFinalized($tournamentID)): ?>
				<?php if(USER_TYPE >= USER_STAFF):
					if($_SESSION['manualTournamentPlacing'] == $tournamentID):
						manualTournamentPlacing($tournamentID);
						unset($_SESSION['manualTournamentPlacing']);
					else: ?>
						<?php if(isset($_SESSION['manualPlacingMessage'][$tournamentID])): ?>
							<div class='callout secondary text-center'>
								<?=$_SESSION['manualPlacingMessage'][$tournamentID]?>
							</div>					
						<?php endif ?>
					
						Results Not Finalized
						<form method='POST'>
						<input type='hidden' name='formName' value='finalizeTournament'>
						<input type='hidden' name='tournamentID' value='<?=$tournamentID ?>'>
						<button class='button'>
							Auto Finalize Tournament
						</button>
						<button class='button secondary' name='enableManualTournamentPlacing' value='x'>
							Manually Finalize Tournament
						</button>
						</form>
					<?php endif ?>
				<?php endif ?>
				</fieldset>
				<?php continue; ?>
			<?php endif ?>
			
		
			
		<!-- Display tournament placings -->
			<table>
				<?php for($i=0;$i<=3;$i++):
					$rosterID = $placings[$i]['rosterID'];
					if($rosterID == null){continue;}
					$name = getFighterName($rosterID);
					
					$school = $placings[$i]['schoolFullName'];
					if($placings[$i]['schoolBranch'] != null){
						$school .= ", ".$placings[$i]['schoolBranch'];
					}
					$num = $i+1; ?>
					
					<tr>
						<td class='text-center'><?= $num ?></td>
						<td><strong><?=$name?></strong></div></td>
						<td><em><?= $school ?></em></td>
					</tr>
					
				<?php endfor ?>
			</table>
			
		<!-- Button to remove results -->
			<?php if(USER_TYPE >= USER_ADMIN): ?>
				<form method='POST'>
				<input type='hidden' name='formName' value='finalizeTournament'>
				<input type='hidden' name='finalizeTournament' value='revoke'>
				<button class='button hollow' name='tournamentID' value='<?= $tournamentID ?>'>
					Remove Final Results
				</button>
				</form>
			<?php endif ?>
			
		</fieldset>
			
	<?php endforeach ?>
	</div>

<?php }

unset($_SESSION['manualPlacingMessage']);

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function manualTournamentPlacing($tournamentID){

	$roster = getTournamentRoster($tournamentID);
	$max = sizeof($roster);
	?>
	
	<?php if(isset($_SESSION['manualPlacingMessage'][$tournamentID])): ?>
		<div class='callout secondary text-center'>
			<?=$_SESSION['manualPlacingMessage'][$tournamentID]?>
		</div>					
	<?php endif ?>
	
	<form method='POST'>
	<input type='hidden' name='formName' value='finalizeTournament'>
	<input type='hidden' name='tournamentID' value='<?= $tournamentID ?>'>
	<input type='hidden' name='manualTournamentPlacing' value='1'>
		
	<?php for($i=1;$i<=$max;$i++): 
		if($isTie != true){
			$tieStartsAt = $i;
		}
		$isTie = isset($_SESSION['ties'][$i]);
		$extraClass = isSelected($isTie,'alert callout');
		
	?>
	
		<!-- Select field -->
		<div class='input-group <?=$extraClass?>'>
			<span class='input-group-label'><?= $i ?></span>
			<select class='input-group-field' name='placing[<?= $i ?>]'>
				<option></option>";
				<?php foreach($roster as $person):
					$rosterID = $person['rosterID'];
					$name = getFighterName($rosterID);
					$selected = isSelected($rosterID, $_SESSION['overallScores'][$i-1]);
					 ?>
					<option value='<?= $rosterID ?>' <?=$selected?>><?= $name ?></option>";
				<?php endforeach ?>
			</select>
		</div>
		
		<!-- If it is done displaying a block of tied fighters -->
		<?php if($_SESSION['ties'][$i] === 'end'): ?>
			<div class='input-group'>
				<span class='input-group-label'>Enter above fighters as a tie?</span>
				<div class='switch no-bottom input-group-field'>
					<input class='switch-input' type='checkbox' id='ties<?=$i?>' 
						name='ties[<?=$tieStartsAt?>]' value='<?=$i?>' checked>
					<label class='switch-paddle' for='ties<?=$i?>'>
					</label>
				</div>
			</div>
			<BR>
			
			<?php unset($isTie); ?>
			
		<?php endif ?>
		
	<?php endfor ?>
	<button class='button' name='tournamentID' value='<?= $tournamentID ?>'>
		Finalize Tournament
	</button>
	<button class='button secondary' name='formName' value=''>
		Cancel
	</button>
	
	</form>
	
<?php 
	unset($_SESSION['overallScores']);
	unset($_SESSION['ties']);
}

/******************************************************************************

function displayFullPlacings(){			---- Depreciated

	$placings = getSchoolPoints();
	$headers = array();
	$headers1 = array();
	$headers2 = array();
	
	// Generate Headers for beyond 4th place
	foreach($placings as $place => $results){
		foreach($results as $index => $placing){
			switch($index){
				case 'score': case 'schoolID': case 'schoolName':
				case 1: case 2: case 3: case 4: continue;
				default:
						if(!is_int($index)){
							$k = (int)substr($index,4);
							$headers2[$k] = true;
						} else {$headers1[$index] = true;}

			}
		}
	}
	
	ksort($headers2); ksort($headers1);

	foreach($headers1 as $k => $b){$headers[] = $k;}
	foreach($headers2 as $k => $b){$headers[] = "Top ".$k;}
	
	

	// Data Table
	echo "<table class='data_table'>
	<tr>
		<th>Rank</th>
		<th>School</th>
		<th>Score</th>
		<th>1st</th>
		<th>2nd</th>
		<th>3rd</th>
		<th>4th</th>";
	foreach($headers as $header){
		echo "<th>{$header}</th>";
	}
echo "
	</tr>";
	
	$i=0;

	foreach($placings as $place => $results){

		$name = getSchoolName($results['schoolID'], 'full', 'yes');
		$score = $results['score'];
		$i++;
		echo"<tr>
			<td>{$i}</td>
			<td>{$name}</td>
			<td>{$score}</td>
			<td>{$results[1]}</td>
			<td>{$results[2]}</td>
			<td>{$results[3]}</td>
			<td>{$results[4]}</td>";
			foreach($headers as $index){
				echo "<td>{$results[$index]}</td>";

			}
		echo "</tr>";
		
	}
	echo "</table>";
}

/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
