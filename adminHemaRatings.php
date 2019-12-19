<?php 
/*******************************************************************************
	HEMA Ratings Export Information
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "HEMA Ratings";

include('includes/header.php');


if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else {

	$hemaRatingInfo = hemaRatings_GetEventInfo($_SESSION['eventID']);
	$tournamentsFinalized = areAllTournamentsFinalized($_SESSION['eventID']);
	$incompletes = getEventIncompletes($_SESSION['eventID']);

	$incompleteWarning = listIncompleteMatches($incompletes);
	$dataComplete = hemaRatings_isEventInfoComplete($_SESSION['eventID']);

//field type | display text | required | only enterable once tournaments are complete

	$dataEntries[] 						= ['header','Event Information'];
	$dataEntries['eventName'] 			= ['locked',''];
	$dataEntries['eventStartDate'] 		= ['locked',''];
	$dataEntries['eventCountry'] 		= ['locked',''];
	$dataEntries['eventProvince'] 		= ['locked',''];
	$dataEntries['eventCity'] 			= ['locked',''];
	$dataEntries['organizingSchool'] 	= ['select',''];
	$dataEntries['socialMediaLink'] 	= ['text',"One or more links to info about the event on Facebook, VK, Eventbrite, etc. This makes it easier to tag, find photos, etc. If this is missing, please write an explanation why."];
	$dataEntries['photoLink'] 			= ['text',"Links to photos/albums we can use when announcing that we’ve added the results to the ratings. These can also be submitted to us directly via email or Facebook message. If this is missing, please write an explanation why."];

	$dataEntries[] 						= ['header','Submitter Info'];
	$dataEntries['submitterEmail'] 		= ['text',''];
	$dataEntries['submitterName'] 		= ['text',''];
	$dataEntries['organizerName'] 		= ['text',''];
	$dataEntries["organizerEmail"] 		= ['locked',''];

	$dataEntries[] 						= ['header','Data Integrity'];
	$dataEntries['eventConform'] 		= ['check',"I have read and understood the criteria laid out on HEMA Ratings 'About' page, and the event meets these requirements"];
	$dataEntries['allMatchesFought'] 	= ['check',"If someone withdrew due to injury or for some other reason didn't fight all their fights, those fight should not be included. We want to rate people's fighting ability, not their injuries"];
	$dataEntries['missingMatches'] 		= ['check',"Sometimes there are fights missing for various reasons. If there are any fights missing, please let us know why and we'll find out how to proceed."];
	$dataEntries['notes'] 		= ['textField',''];
	
	foreach($dataEntries as $field => $entry){
		unset($tmp);
		$tmp['type'] = $entry[0];
		$tmp['extraInfo'] = $entry[1];
		$tmp['text'] = hemaRatings_getFieldDisplayName($field);
		$tmp['required'] = hemaRatings_isEventInfoRequired($field);

		if($tournamentsFinalized == false){
			$tmp['lock'] = hemaRatings_lockFieldUntilComplete($field);
		} else {
			$tmp['lock'] = false;
		}

		$dataItems[$field] = $tmp;

	}

	$errorStr = "";
	if($tournamentsFinalized == false){
		$errorStr .= "";
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<?php if($dataComplete == false || $tournamentsFinalized == false):?>

	<div class='callout alert'>
		<h3>Form Not Complete</h3>

		<?php if($tournamentsFinalized == false): ?>
			<li>
				All tournaments must be finalized before form can be completed. 
					<a href='infoSummary.php'>Finalize Tournaments</a>
			</li>
		<?php endif ?>

		<?php if($dataComplete == false): ?>
			<li>Fields in <strong class='red-text'>RED</strong> are mandatory.</li>
		<?php endif ?>

		<?php if($incompleteWarning != null): ?>
			<li>There are incomplete matches. 
				<em><a onclick="$('#incomplete-matches').toggle()">Show/Hide Matches</a></em>
				<div class='callout hidden' id='incomplete-matches'>
					<ul >
						The following matches are incomplete:
						<?=$incompleteWarning?>
					</ul>
				</div>


			</li>
		<?php endif ?>

	</div>
<?php endif ?>


<form method="POST">

<input type='hidden' name='eventHemaRatings[eventID]' value="<?=$_SESSION['eventID']?>">

<div class='grid-x grid-margin-x'>
<div class='large-8 medium-10'>


	<button class='button success' name='formName' value='hemaRatings_UpdateEventInfo'>
		Update
	</button>

	<em>Values in grey are event information and can only be changed on the <a href='adminEvent.php'>Event Settings</a> page.</em>

	<table>

		<?php foreach($dataItems as $field => $item): ?>
			<?=formEntry($item, $field, $hemaRatingInfo)?>
		<?php endforeach ?>
		
	</table>

	<button class='button success' name='formName' value='hemaRatings_UpdateEventInfo'>
		Update
	</button>

</form>

</div>
</div>


<?
}
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/**********************************************************************/

function formEntry($item, $field, $hemaRatingInfo){

	if($item['required'] == false){
		$class='grey-text';
	} elseif(@$hemaRatingInfo[$field] == ''){
		$class = 'alert-text';
	} else {
		$class = '';
	}
?>
	<tr>
	
	<?php if($item['type'] == 'header'): ?>

		<th class='warning-text' colspan='100%'>
			<?=$item['extraInfo']?>
		</th>
		
	<?php else: ?>

		<td class="<?=$class?>">
			<?=$item['text']?>
			<?php if($item['extraInfo'] != null): ?>
				<?=tooltip($item['extraInfo'])?>
			<?php endif ?>
		</td>

		<td>

			<?php if($item['lock'] == true): ?>

				<em>Disabled until all tournaments are finalized</em>
				<input type='hidden' value=""
					name='eventHemaRatings[<?=$field?>]'>
					
			<?php elseif($item['type'] == 'check'): ?>

				<?=confirmPaddles($field, $hemaRatingInfo[$field])?>
		
			<?php elseif($item['type'] == 'locked'): ?>

				<input type='text' class='no-bottom' disabled 
					value="<?=$hemaRatingInfo[$field]?>">

			<?php elseif($item['type'] == 'text'): ?>

				<input type='text' class='no-bottom'
					name='eventHemaRatings[<?=$field?>]'
					value="<?=$hemaRatingInfo[$field]?>">

			<?php elseif($item['type'] == 'textField'): ?>

				<textarea class='no-bottom'
					 rows="4" cols="50"
					name='eventHemaRatings[<?=$field?>]'><?=$hemaRatingInfo[$field]?></textarea>
				
			<?php elseif($item['type'] == 'select'): ?>

				<select class='no-bottom'
					name='eventHemaRatings[<?=$field?>]'>
					<?=schoolSelectDropDown($hemaRatingInfo[$field])?>
				</select>
	
			<?php endif ?>
		</td>
	<?php endif ?>

	</tr>


<?
}

/**********************************************************************/

function confirmPaddles($field, $value){

	if($value == null){
		$value = "";
	}

	$text[""] = '(unchecked)';
	$text["0"] = 'No';
	$text["1"] = 'Yes';

	foreach($text as $i => $str){

		if(strcmp($i,$value) == 0){
			$checked = "checked";
		} else {
			$checked = "";
		}
	?>
		<div class='switch no-bottom inline'>
						
			<input class='switch-input no-bottom' type='radio' 
				name='eventHemaRatings[<?=$field?>]'
				value='<?=$i?>' <?=$checked?>
				id='<?=$field?><?=$str?>'>
			<label class='switch-paddle' for='<?=$field?><?=$str?>'>
			</label>
			<span class='grey-text'><?=$str?></span>
		</div>

	<?
	}
}

/**********************************************************************/

function schoolSelectDropDown($selectedID = null){
		$schoolList = getSchoolList();

?>
	<option <?=optionValue(1, $selectedID)?> >*Unknown</option>
	<option <?=optionValue(2, $selectedID)?> >*Unaffiliated</option>
	
	<?php foreach($schoolList as $school):
		if($school['schoolShortName'] == null || $school['schoolShortName'] == 'Unaffiliated'){continue;}
		?>
		
		<option <?=optionValue($school['schoolID'], $selectedID)?> >
			<?=$school['schoolShortName']?>, <?=$school['schoolBranch']?>
		</option>
	<?php endforeach?>

<?
}

/******************************************************************************/

function listIncompleteMatches($incompletes){
	
	if($incompletes == null){
		return null;
	}
	
	$incompleteWarning = "";
	foreach($incompletes as $match){
		if($match['fighter1ID'] != null){
			$name1 = getFighterName($match['fighter1ID']);
		} else {
			$name1 = "{empty}";
		}

		if($match['fighter2ID'] != null){
			$name2 = getFighterName($match['fighter2ID']);
		} else {
			$name2 = "{empty}";
		}

		$incompleteWarning .= "<li><strong>[ ";
		$incompleteWarning .= getTournamentName($match['tournamentID'])." ]</strong> <em>";
		if($match['groupType'] =='elim'){
			if($match['groupName'] == 'winner'){

				$incompleteWarning .= "Primary Bracket";
			} else {
				$incompleteWarning .= "Secondary Bracket";
			}
		} else {
			$incompleteWarning .= $match['groupName'];
		}


		$incompleteWarning .= "</em> | {$name1} vs {$name2} </li>";
	}

	return $incompleteWarning;

}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
