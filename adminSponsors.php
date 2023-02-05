<?php 
/*******************************************************************************

Event Sponsors
	
*******************************************************************************/

// PAGE SETUP //////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Event Sponsors";

$tournamentPage 			= false;
$lockedTournamentWarning 	= false;

$jsIncludes = null;

include('includes/header.php');

// Get the event List
if((int)$_SESSION['eventID'] == 0){
	pageError('event');
} else if(ALLOW['SOFTWARE_ADMIN'] == false){
	pageError('user');
} else {

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

	$sponsorListGear = getSponsorListGear();
	$sponsorListEvent = getSponsorListEvent();
	$sponsorListLocal = getSponsorListLocal();
	$eventSponsors = getEventSponsors($_SESSION['eventID']);

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<div class='grid-x grid-margin-x'>
	<div class='large-6 medium-9 cell'>

	<form method='POST'>

	<a onclick="$('#gear-sponsor-list').toggle()"><h3>Equipment Sponsors</h3></a>
	<div id='gear-sponsor-list'>
		<?=displaySponsorList($sponsorListGear, $eventSponsors)?>
	</div>

	<a onclick="$('#event-sponsor-list').toggle()"><h3>Other Events as Sponsors</h3></a>
	<div class='hidden' id='event-sponsor-list'>
		<?=displaySponsorList($sponsorListEvent, $eventSponsors)?>
	</div>

	<a onclick="$('#local-sponsor-list').toggle()"><h3>Local Sponsors</h3></a>
	<div class='hidden' id='local-sponsor-list'>
		<?=displaySponsorList($sponsorListLocal, $eventSponsors)?>
	</div>

	<input type='hidden' name='sponsorList[eventID]' value="<?=$_SESSION['eventID']?>">

	<button class='button success no-bottom' name='formName' value='updateEventSponsors'>Update Sponsor List</button><BR>
	<i>(If your sponsor is not on the list, contact the HEMA Scorecard team)</i>

	</form>

	</div>
	</div>

<?
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displaySponsorList($sponsorList, $eventSponsors){
?>
	<table>
		<?php foreach($sponsorList as $sponsor): ?>
			<tr>
				<td><?=$sponsor['sponsorName']?></td>
				<td>
					<div class='switch text-center no-bottom'>
					<input class='switch-input' type='checkbox' <?=chk(@$eventSponsors[$sponsor['sponsorID']])?>
						id='sponsorList[sponsors][<?=$sponsor['sponsorID']?>][sponsorID]' 
						name='sponsorList[sponsors][<?=$sponsor['sponsorID']?>][sponsorID]' value='<?=$sponsor['sponsorID']?>'>
					<label class='switch-paddle' for='sponsorList[sponsors][<?=$sponsor['sponsorID']?>][sponsorID]'>
					</label>
					</div>
				</td>
				<td>
					<input type='number' name='sponsorList[sponsors][<?=$sponsor['sponsorID']?>][eventSponsorPercent]'
						value='<?=@$eventSponsors[$sponsor['sponsorID']]['eventSponsorPercent']?>' placeholder='100%'>
				</td>
				<input type='hidden' name='sponsorList[sponsors][<?=$sponsor['sponsorID']?>][eventSponsorID]' 
					value='<?=@$eventSponsors[$sponsor['sponsorID']]['eventSponsorID']?>'>
			</tr>
		<?php endforeach ?>
	</table>
<?
}


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
