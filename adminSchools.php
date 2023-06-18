<?php
/*******************************************************************************
	Add/Edit Schools

	Page where administrators can add/edit schools in the database
	LOGIN
		- ADMIN or higher can add new schools
		- SUPER ADMIN can edit existing schools

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'School Management';
$jsIncludes[] = 'misc_scripts.js';
include('includes/header.php');

if(ALLOW['EVENT_MANAGEMENT'] == false
	&& ALLOW['SOFTWARE_ASSIST'] == false
	&& ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else {
	$schools = getSchoolListLong();

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<form method='POST' action='participantsEvent.php'>
	<input type='hidden' name='on' value='1'>
	<button class='button' value='addEventParticipantsMode' name='formName'>
		- Return To Add Participants -
	</button>
	<BR><BR>
	</form>

<!-- Add New School -->
	<?php
		if(isset($_SESSION['editSchoolID'])){
			editExistingSchool();
		} else {
			addNewSchoolInput();
		}
	?>

<!-- Display Existing Schools -->
	<HR><h5>Schools in Database</h5>

	<form method='POST'>
	<input type='hidden' name='formName' value='editExistingSchool'>
	<input type='hidden' name='enableEditing' value='true'>

	<table>
	<?php displaySchoolHeaders(); ?>

	<?php foreach($schools as $school): ?>
		<tr>
			<td>
			<?php if(ALLOW['SOFTWARE_ASSIST'] == true):
				$displayID = intToString($school['schoolID'],3); ?>
				<button class='button tiny hollow' name='schoolID' value='<?= $school['schoolID'] ?>'>
					Edit #<?= $displayID ?>
				</button>
			<?php endif ?>
			</td>
			<td><?= $school['schoolFullName'] ?></td>
			<td><?= $school['schoolShortName'] ?></td>
			<td><?= $school['schoolAbbreviation'] ?></td>
			<td><?= $school['schoolBranch'] ?></td>
			<td><?= $school['countryName'] ?></td>
			<td><?= $school['schoolProvince'] ?></td>
			<td><?= $school['schoolCity'] ?></td>
		</tr>


	<?php endforeach ?>

	</table>

<?php }


include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displaySchoolHeaders(){
	?>
	<tr>
		<td></td>
		<th>School Full Name</th>
		<th>School Short Name</th>
		<th>Abbreviation</th>
		<th>Branch</th>
		<th>Country</th>
		<th>State/Province</th>
		<th>City</th>
	</tr>
<?php }

/******************************************************************************/

function editExistingSchool(){

	if(ALLOW['SOFTWARE_ASSIST'] == false){return;}
	$schoolID = $_SESSION['editSchoolID'];
	unset($_SESSION['editSchoolID']);
	$schoolInfo = getSchoolInfo($schoolID); ?>

	<h5>Edit School (ID: <?= $schoolID ?>)</h5>

	<form method='POST'>
	<input type='hidden' name='formName' value='editExistingSchool'>
	<input type='hidden' name='schoolID' value='<?= $schoolID ?>'>

	<table>
	<tr>
		<td>School Full Name</td>
		<td>
			<input type='text' name='schoolFullName' required
			value='<?= $schoolInfo['schoolFullName'] ?>'>
		</td>
	</tr>
	<tr>
		<td>School Short Name </td>
		<td>
			<input type='text' name='schoolShortName'
			value='<?= $schoolInfo['schoolShortName'] ?>' size='10'>
		</td>
	</tr>
	<tr>
		<td>School Abbreviation </td>
		<td>
			<input type='text' name='schoolAbbreviation' required
			value='<?= $schoolInfo['schoolAbbreviation'] ?>' size='1'>
		</td>
	</tr>
	<tr>
		<td>School Branch </td>
		<td>
			<input type='text' name='schoolBranch'
			value='<?= $schoolInfo['schoolBranch'] ?>' size='10'>
		</td>
	</tr>
	<tr>
		<td>School Country </td>
		<td>
			<?=selectCountry("countryIso2", $schoolInfo['countryIso2']);?>
		</td>
	</tr>
	<tr>
		<td>School Province </td>
		<td>
			<input type='text' name='schoolProvince'
			value='<?= $schoolInfo['schoolProvince'] ?>'>
		</td>
	</tr>
	<tr>
		<td>School City </td>
		<td>
			<input type='text' name='schoolCity'
			value='<?= $schoolInfo['schoolCity'] ?>'>
		</td>
	</tr>
	</table>

	<div class='grid-x grid-padding-x row'>
		<div class='small-12 medium-3 large-2 cell'>
			<button class='button primary expanded'>Update School</button>
		</div>
		<div class='small-12 medium-3 large-2 cell'>
			<a class='button secondary expanded' href='adminSchools.php'>
				Cancel Update
			</a>
		</div>
	</div>
	</form>


<?php }



/******************************************************************************/

function addNewSchoolInput(){
?>
	<h5>Add School:</h5>

	<div class='grid-x grid-margin-x'>
	<div class='medium-6 cell'>

		<form method='POST'>
		<input type='hidden' name='formName' value='addNewSchool'>
		<div class='grid-x cell'>
		<div class='input-group grid-x cell'>
			<span class='input-group-label small-5'><strong>Full Name:</strong></span>
			<input class='input-group-field' type='text' name='schoolFullName' required
				 onkeyup="schoolInputPlaceholders()" id='schoolFull' placeholder='- Mandatory -'>
		</div>
		<div class='input-group grid-x cell'>
			<span class='input-group-label small-5'>School Short Name:</span>
			<input class='input-group-field' type='text' name='schoolShortName'
				id='schoolShort'>
		</div>
		<div class='input-group grid-x cell'>
			<span class='input-group-label small-5'><strong>School Abbreviation</strong>
				<?=tooltip("This is used as a shorthand for things like pool rosters, to help avoid people from the same school fighting.<BR>
				Please keep it as short as possible, 3-5 characters.<BR>
				(Max is 7)")?>:
			</span>
			<input class='input-group-field' type='text' name='schoolAbbreviation'
				id='schoolAbbreviation' maxlength='7' placeholder='(keep it short)' required>
		</div>
		<div class='input-group grid-x cell'>
			<span class='input-group-label small-5'>School Branch</span>
			<input class='input-group-field' type='text' name='schoolBranch'>
		</div>
		<div class='input-group grid-x cell'>
			<span class='input-group-label small-5'><strong>School Country</strong></span>
			<?=selectCountry("countryIso2", null, null, "input-group-field");?>
		</div>
		<div class='input-group grid-x cell'>
			<span class='input-group-label small-5'>School Province</span>
			<input class='input-group-field' type='text' name='schoolProvince'>
		</div>
		<div class='input-group grid-x cell'>
			<span class='input-group-label small-5'>School City</span>
			<input class='input-group-field' type='text' name='schoolCity'>
		</div>

		</div>
		<button class='button success'>Add New School</button>
		</form>
	</div>

	<div class='hide-for-small-only medium-6 cell'>
		The school's short name is the name that will mostly show up.<BR>
		The school's full name is the full title of the school.

		<ul>Example:
		<li>Full Name: <em>True Edge Academy of Swordsmanship</em>
		<li>Short Name: <em>True Edge</em>
		<li>Branch: <em>True Edge South</em></li>
		<li>Abbreviation: <em>TEA</em></ul>
		The school's branch will show up after the schools full name<BR>

		<em>True Edge Academy of Swordsmanship, <u>True Edge South</u></em><BR><BR>
		If you don't fill in the short name the software will use the full name.
		If you don't fill in an abbreviation the software will make one up
		of all the capital letters in the name.
	</div>

	</div>

<?php }

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
