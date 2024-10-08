<?php
/*******************************************************************************
	Add Tournament Types

	Page for adding new tournament meta types (weapons/classes/materials/etc...)
	LOGIN:
		- SUPER ADMIN required for access

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Add Tournament Types';
include('includes/header.php');

if(ALLOW['SOFTWARE_ASSIST'] == false){
	pageError('user');
} else {

	$sql = "SELECT tournamentTypeMeta, tournamentType
			FROM systemTournaments
			ORDER BY tournamentType ASC";
	$allTournaments = (array)mysqlQuery($sql, ASSOC);

	$tournamentsSorted = [];
	foreach($allTournaments as $t){
		$tournamentsSorted[$t['tournamentTypeMeta']][] = $t['tournamentType'];
	}


	$sql = "SELECT attackClass, attackText
			FROM systemAttacks
			ORDER BY attackText ASC";
	$allAttacks = (array)mysqlQuery($sql, ASSOC);

	$attacksSorted = [];
	foreach($allAttacks as $a){
		$attacksSorted[$a['attackClass']][] = $a['attackText'];
	}


// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>
<BR>

	<form method='POST'>
	<input type='hidden' name='formName' value='addTournamentType'>

	<a href='adminNewTournaments.php' class='button hollow'>Add New Tournaments</a>

	<div class='input-group'>
		<span class='input-group-label'>Type:</span>
		<select name='tournamentTypeMeta' class='input-group-field'>
			<option value='weapon'>Weapon</option>
			<option value='prefix'>Prefix</option>
			<option value='material'>Material</option>
			<option value='suffix'>Suffix</option>
			<option value='gender'>Gender</option>
		</select>

		<span class='input-group-label'>Name:</span>
		<input class='input-group-field' type='text' name='tournamentType'>

		<button class='button success input-group-button'>
			Add
		</button>
	</div>

	</form>

	<table><tr>
		<?php
			foreach($tournamentsSorted as $tournamentTypeMeta => $tournamentsInMeta){
				echo "<td style='vertical-align: top;'>";
				echo "<b>".$tournamentTypeMeta."</b>";
				foreach($tournamentsInMeta as $t){
					echo "<li>".$t."</li>";
				}
				echo "</td>";
			}
		?>
	</tr></table>


<!------------------------------------------------------------------------------------------>
<BR><HR><BR>
<!------------------------------------------------------------------------------------------>



	<form method='POST'>
	<input type='hidden' name='formName' value='addAttackType'>

	<a href='adminNewTournaments.php' class='button hollow'>Add New Attack</a>

	<div class='input-group'>
		<span class='input-group-label'>Type:</span>
		<select name='attackClass' class='input-group-field'>
			<option value='type'>Type</option>
			<option value='target'>Target</option>
			<option value='prefix'>Prefix</option>
			<option value='illedgalAction'>Illegal Action</option>
		</select>

		<input class='input-group-field' type='text' name='attackCode' placeholder='attackCode'>
		<input class='input-group-field' type='text' name='attackText' placeholder='Attack Name'>

		<button class='button success input-group-button'>
			Add
		</button>
	</div>

	</form>

	<table><tr>
		<?php
			foreach($attacksSorted as $attackClass => $attacksInClass){
				echo "<td style='vertical-align: top;'>";
				echo "<b>".$attackClass."</b>";
				foreach($attacksInClass as $a){
					echo "<li>".$a."</li>";
				}
				echo "</td>";
			}
		?>
	</tr></table>

<!------------------------------------------------------------------------------------------>
<BR><BR>

<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
