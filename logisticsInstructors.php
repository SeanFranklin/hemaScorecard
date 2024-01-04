<?php
/*******************************************************************************
	Logistics Staff Assigments

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Event Instructors';
include('includes/header.php');


if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_ROSTER'] == false) {
	pageError('user');
} elseif($_SESSION['isMetaEvent'] == true){
	redirect('infoSummary.php');
} else {

	$eventRoster = getEventRoster();
	$instructorList = logistics_getEventInstructors($_SESSION['eventID']);

	$avaliableStaff = [];
	foreach($eventRoster as $roster){
		if(NAME_MODE == 'firstName'){
			$avaliableStaff[$roster['rosterID']]['name'] = $roster['firstName']." ".$roster['lastName'];
		} else {
			$avaliableStaff[$roster['rosterID']]['name'] = $roster['lastName'].", ".$roster['firstName'];
		}
		$avaliableStaff[$roster['rosterID']]['disabled'] = '';
	}

	foreach($instructorList as $i){
		$avaliableStaff[$i['rosterID']]['disabled'] = 'disabled';
	}


	instructorForm(0, $avaliableStaff);

	foreach($instructorList as $instructor){
		instructorForm($instructor, null);
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
/******************************************************************************/

/******************************************************************************/

function instructorForm($instructor, $avaliableStaff){

	if(ALLOW['EVENT_MANAGEMENT'] == false){
		displayInstructorBio($instructor);
		return;
	}

	if(ALLOW['SOFTWARE_ADMIN'] == true && isset($_SESSION['forcePlainText']) == true){
		$wysisygClass = '';
	} else {
		$wysisygClass = 'tiny-mce';
	}

	if($instructor != null){
		$name = $instructor['name'];
		$title = $name;
		$buttonText = 'Update';
		$bio = $instructor['instructorBio'];
		$calloutClass='primary';
		$rosterID = $instructor['rosterID'];
	} else {
		$name = null;
		$title = "New Instructor";
		$buttonText = 'Add';
		$bio = null;
		$calloutClass='warning';
		$rosterID = 0;
	}

?>
	<HR>
	<form method="POST">
	<input type="hidden" name="instructorBio[eventID]" value=<?=$_SESSION['eventID']?>>
	<div class='grid-x grid-margin-x' >

		<div class='cell large-4 medium-6'>
		<h4>
			<?=$title?>

			<button class='button success no-bottom' name='formName' value='instructorBio'>
					<?=$buttonText?>
			</button>

		</h4>
		</div>

		<?php if($rosterID == null): ?>



			<div class='cell large-4 medium-6'>
				<select name='instructorBio[rosterID]' required>
					<option selected disabled value=0>- select name -</option>
					<?php foreach($avaliableStaff as $rosterID => $r):?>
						<option value=<?=$rosterID?> <?=$r['disabled']?> ><?=$r['name']?></option>
					<?php endforeach ?>
				</select>
			</div>

			<div class='cell large-3 medium-12 text-center'>
				<i>Greyed out names are alread instructors. (Scroll down!)</i>
			</div>
		<?php else: ?>
			<input type="hidden" name="instructorBio[rosterID]" value=<?=$rosterID?>>

			<div class='cell large-8 medium-6 text-right'>
				<a class='button alert no-bottom hollow'
					onclick="$('#delete-instructor-button-<?=$rosterID?>').toggle()">
					Delete Instructor
				</a>
			</div>

		<?php endif ?>

		<div class='cell large-12'>
			<textarea rows=12 name='instructorBio[instructorBio]' class='<?=$wysisygClass?>' placeholder='Instructor Bio'><?=$bio?></textarea>
		</div>





		<div class='cell large-12 text-right hidden' id='delete-instructor-button-<?=$rosterID?>'>
			<button class='button alert no-bottom' name='formName' value='instructorDelete'>
				Yes, I'm sure I want to remove <?=$name?> as an instructor
			</button>
		</div>

	</div>
	</form>

	<?php if($instructor != 0): ?>
		<a onclick="$('#bio-for-<?=$rosterID?>').toggle()">Show Bio ↓</a>
		<div class='hidden' id='bio-for-<?=$rosterID?>'>
			<?=displayInstructorBio($instructor)?>
		</div>
	<?php endif ?>


<?php
}

/******************************************************************************/

function displayInstructorBio($instructor){

	if($instructor == null){
		return;
	}

// Find out if there is a saved image for the instructor
	$filePath = "./includes/images/instructors/".$instructor['systemRosterID'];

	if(file_exists($filePath.".png") == true){
		$image = "<img src='{$filePath}.png'>";
		$bioSize = "medium-9";
	} elseif(file_exists($filePath.".jpg") == true){
		$image = "<img src='{$filePath}.jpg'>";
		$bioSize = "medium-9";
	} elseif(file_exists($filePath.".jpeg") == true){
		$image = "<img src='{$filePath}.jpeg'>";
		$bioSize = "medium-9";
	} else {
		$image = null;
		$bioSize = "large-12";
	}

// Show their classes
	$personalSchedule = (array)logistics_getParticipantSchedule($instructor['rosterID'], $_SESSION['eventID']);
	$eventDays = getEventDays($_SESSION['eventID']);

	$classes = [];
	foreach((array)@$personalSchedule['scheduled'] as $block){

		if(@$block['logisticsRoleID'] == LOGISTICS_ROLE_INSTRUCTOR){
			$classes[] = logistics_getScheduleBlockInfo($block['blockID']);
		}
	}

?>

	<div class='grid-x grid-margin-x callout primary align-top image-box' >
	<!-- systemRosterID: <?=$instructor['systemRosterID']?> -->

	<div class='cell large-12'>
		<h3><?=($instructor['name'])?></h3>
	</div>

	<?php if($image != null): ?>
		<div class='cell medium-3'>
			<?=$image?>
		</div>
	<?php endif ?>

	<div class='cell <?=$bioSize?>'>
		<?=$instructor['instructorBio']?>
	</div>

	<div class='cell large-12'>
		<?php foreach($classes as $class):?>
			<hr><p><b><?=$class['blockTitle']?></b><BR>
			<i><?=$class['blockSubtitle']?></i></p>
			<p><?=$class['blockDescription']?></p>
		<?php endforeach ?>

	</div>

</div>

<?php
}

/******************************************************************************/
// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
