<?php 
/*******************************************************************************
	Import Participants
	
	Import participants from .csv files
	Login: 
		- ADMIN or above can  access
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Import Participants";
$hideEventNav = true;
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(USER_TYPE < USER_ADMIN){
	pageError('user');

	///////// This has been temporarialy disabled
} elseif(USER_TYPE < USER_SUPER_ADMIN) {
	displayAlert('This functionality has been disabled<BR>Sorry for any inconvenience');
	/////////

}else {
	$importData = $_SESSION['csvRosterAdditions'];
	unset($_SESSION['csvRosterAdditions']);
	$tournamentList = $_SESSION['csvTournamentList'];
	unset($_SESSION['csvTournamentList']);

	importFileBox();
	instructionBox();
	$schoolList = getSchoolList();
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>
	

	<span class='button ' data-open='CSVbox'>
		Upload File
	</span>
	<span class='button secondary hollow' data-open='instructionsBox'>
		Instructions
	</span>


	<?php if($importData != null): ?>
	
		<form method='POST' action='participantsEvent.php'>
			
		<button class='button success large' name='formName' value='addEventParticipants'>
			Add To Event Participants
		</button>
		
		<table>
			<tr>
				<th>Name</th>
				<th>School</th>
				<?php foreach((array)$tournamentList as $name): ?>
					<th><?=$name?></th>
				<?php endforeach ?>
				
			</tr>
		
		
		
		<?php foreach($importData as $k => $fighter): 
			$fName = htmlspecialchars($fighter['firstName'], ENT_QUOTES);
			$lName = htmlspecialchars($fighter['lastName'], ENT_QUOTES);
			?>
			<tr>
				<td>
					<div class='input-group no-margin shrink'>
					<?php if(NAME_MODE == 'firstName'): ?>
					<input type='text' name='newParticipants[new][<?=$k?>][firstName]' 
						class='input-group-field no-margin' value='<?=$fName?>'
						placeholder='First Name'>
					<?php endif ?>
					<input type='text' name='newParticipants[new][<?=$k?>][lastName]' 
						class='input-group-field no-margin' value='<?=$lName?>'
						placeholder='Last Name'>
					<?php if(NAME_MODE != 'firstName'): ?>
					<input type='text' name='newParticipants[new][<?=$k?>][firstName]' 
						class='input-group-field no-margin' value='<?=$fName?>'
						placeholder='First Name'>
					<?php endif ?>
					</div>
					
				</td>
				
				<td>
					<?php $schoolID = guessSchoolId($fighter['school']);?>
					
					<select class='shrink' name='newParticipants[new][<?=$k?>][schoolID]'>
		
					<option value='1' <?=$s1?>>*Unkonwn</option>
					<option value='2' <?=$s2?>>*Unafiliated</option>
					
					<?php foreach($schoolList as $school):
						if($school['schoolShortName'] == null || $school['schoolShortName'] == 'Unaffiliated'){continue;}
						$s = isSelected($school['schoolID'],$schoolID);
						?>
						
						<option value='<?=$school['schoolID']?>' <?=$s?>>
							<?=$school['schoolShortName']?>, <?=$school['schoolBranch']?>
						</option>
					<?php endforeach?>
					
					</select>
				
				</td>
				
				<?php foreach((array)$tournamentList as $tournamentID => $tName):
					if($fighter[$tournamentID] != ''){
						$checked  = 'checked';
					} else {
						$checked = '');
					}
					$i++;
					?>
					<td class='text-center'>
					<input type='checkbox' name='newParticipants[new][<?=$k?>][tournamentIDs][<?=$i?>]' 
					value='<?=$tournamentID?>' <?=$checked?>>
					</td>
				<?php endforeach?>
				
				
			</tr>
			
			
		<?php endforeach ?>
		</table>
		
		</form>
		
	<?php endif ?>


	
<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function instructionBox(){
	?>
	<div class='reveal large' id='instructionsBox' data-reveal>
	
	<h5>Instructions</h5>
	<ul>
	<li>The upload will only accept .csv files.</li>
	<li>The first row of the file must be a header row. The headers are:
		<ol>
			<li>firstName</li>
			<li>lastName</li>
			<li>school</li>
			<li>All the tournament names*</li>
			
		</ol>
		<i>Spelling on everything must be EXACTLY as listed here, including capitalization.
		Tournament names must be exactly as they appear in the software.</i>
	</li>
	<li>You can enter anything you want to confirm a fighter entry in a tournament (ie, 'X', '1', or anything else).
	The software looks for empty cells vs cells with something in them.</li>
	</ul>
	<HR>
	<h5>Input Validation</h5>
	<ul>
	<li>
		The software will compare the name of the school entered against names in the
		database and attempt to make a match. This algoritm is not that smart and you
		should make sure to double check everyone is assigned the correct school.
	</li>
	<li>
		The software will compare names with names in the system to determine if it is the same person. 
		If the combination of first and last name is not an <i>exact</i> match to what is already in the system
		the fighter will be entered in as a new person.
	</li>
	<li>
		<strong>If</strong> the system recognizes the fighter they will be entered as the same person.
		The software will also compare the school they are entered in against the school that is on record
		for the fighter. If there is a difference you will be prompted to chose which school to enter
		the fighter from.
	</li>
		
	
	
	
	
	
	</ul>
	<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>
	
<?php	
}

/******************************************************************************/

function guessSchoolId($givenName){
	

	$sql = "SELECT schoolID
			FROM systemSchools
			WHERE MATCH(schoolFullName) AGAINST (?)";
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $sql);
	// "s" means the database expects a string
	$stmt->bind_param("s", $givenName);
	$stmt->execute();
	$stmt->bind_result($schoolID);
	$stmt->fetch();
	
	$returnVal = $schoolID;

	mysqli_stmt_close($stmt);	
			
	return $returnVal;

	
}
/******************************************************************************/

function importFileBox(){
// Creates a box for users to upload a CSV file

	?>
	<div class='reveal tiny' id='CSVbox' data-reveal>
		
		<form method='POST' enctype='multipart/form-data'>
		Use only .csv files which are properly formated.<BR>
		I'm not liable for what happens if you don't!<HR>
		<input type='hidden' name='formName' value='importRosterCSV'>
		
	
		<input type='file' name='csv_file' accept='.csv'
			placeholder='Only .csv files are valid' onchange="this.form.submit()">
	
		</form>
		
		<!-- Close button -->
		<button class='close-button' data-close aria-label='Close modal' type='button'>
			<span aria-hidden='true'>&times;</span>
		</button>
	</div>
	
<?php
}

/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
