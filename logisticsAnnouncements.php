<?php
/*******************************************************************************
	Logistics Announcements
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Logistics Announcements';
include('includes/header.php');

$eventID = (int)$_SESSION['eventID'];

if(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else if($eventID == 0){
	pageError('event');
} else {

	$announcements = logistics_getEventAnnouncments($_SESSION['eventID']);

	$blank["announcementID"] = 0;
	$blank["message"] = "";
	$blank["displayUntil"] = time() + 10 * 60;
	$blank["visibility"] = "all";

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>	
	

	<p>This allows you to set an announcement that will appear on the page for all users, 
		until they confirm they have seen it. The specified time shows how long the announcment 
		will be displayed after you add/update it. If you select <b>Staff</b> as the announcment 
		type it will only display if they are logged into the event as staff or organizer. <BR>
		<i>When doing staff announcements don't put anything sensitive you don't want to be public. 
		People could still see this announcment if there is a database pull in the future.</i>
	</p>

<!-- Add ------------------------------------------------>

	<HR>
	<h4>Add Announcement</h4>
	<?=editAnnouncementForm($blank)?>

<!-- Edit ------------------------------------------------>
	<HR>
	<h4>Edit Announcements</h4>
	<i>If you want to hide an announcement set the remaining time to 0.</i>

	<?php 
		foreach($announcements as $announcement){
			editAnnouncementForm($announcement);
		}
	?>
	
		



<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function editAnnouncementForm($announcement){

	$announcementID = (int)$announcement['announcementID'];
	$displayTime = round(($announcement['displayUntil'] - time())/60);

	if($displayTime < 0){
		$displayTime = 0;
	}

	if($announcementID == 0){
		$minTime = 10; // 10 minutes;
		$timeText = "Display For";
	} else {
		$minTime = 0;
		$timeText = "Time Remaining";
	}

	if(ALLOW['SOFTWARE_ADMIN'] == false){
		$maxTime = "max=3000";
	} else {
		$maxTime = "";
	}

?>
	
	<div class='grid-x grid-margin-x'>
	<div class='large-8 medium-10 cell'>

	<form method="POST">
	<fieldset class='fieldset'>
		<input type='hidden' name='announcement[eventID]' value='<?=$_SESSION['eventID']?>'>
		<input type='hidden' name='announcement[announcementID]' value='<?=$announcement['announcementID']?>'>

		<textarea required rows='6' name='announcement[message]'><?=$announcement['message']?></textarea>

		<div class='input-group'>
			<span class='input-group-label'><?=$timeText ?> (min): </span>
			<input type=number class='input-group-field' min=<?=$minTime?>  <?=$maxTime?>
				name='announcement[displayTime]' value='<?=$displayTime?>'>


			<span class='input-group-label'>Visibility: </span>
			<select type=number class='input-group-field' name='announcement[visibility]'>
				<option <?=optionValue('all',$announcement['visibility'])?>>All</option>
				<option <?=optionValue('staff',$announcement['visibility'])?>>Staff</option>
			</select>

		</div>

		<?php if($announcementID == 0): ?>
			<button class='button success no-bottom' name='formName' value='updateAnnouncement'>Add Announcement</button>
		<?php else: ?>
			<button class='button no-bottom' name='formName' value='updateAnnouncement'>Edit Announcement</button>
		<?php endif ?>

	</fieldset>
	</form>

	</div>
	</div>
<?
}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
