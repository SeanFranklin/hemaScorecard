<?php 
/*******************************************************************************
	Info Summary
	
	Displays all the tournament medalists
	Login: 
		- ADMIN or above can add/remove final medalists 
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Livestream Management";
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');;
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ALLOW['VIEW_SETTINGS'] == false){
	pageError('user');
} else {
	
	$info = getLivestreamInfo($_SESSION['eventID']);
	$incompleteMatches = getEventIncompletes($_SESSION['eventID']);
	$numEventIncompletes = count($incompleteMatches);
	$matchOrder = getLivestreamMatchOrder($_SESSION['eventID']);

	foreach($incompleteMatches as $match){
		$matchName = getTournamentName($match['tournamentID']);
		$matchName .= " : ";
		
		if($match['groupType'] == 'pool'){
			$matchName .= "[Pool Match] ";
		} elseif ($match['groupType'] == 'elim'){
			if($match['bracketLevel'] == 1){
				if($match['groupName'] == 'winner'){
					$matchName .= "[1st Place Match] ";
				} if($match['groupName'] == 'loser') {
					$matchName .= "[3rd Place Match] ";
				}
			} else {
				$matchName .= "[Bracket Match] ";
			}
		}
		
		if($match['fighter1ID'] != null){ $f1Name = getFighterName($match['fighter1ID']); }
		if($match['fighter2ID'] != null){ $f2Name = getFighterName($match['fighter2ID']); }
		
		if($f1Name != null && $f2Name != null){
			$matchName .= "-> {$f1Name} vs {$f2Name}";
		} else {
			$matchName .= "-> Unknown";
		}
		
		
		
		$tmp['matchName'] = $matchName;
		$tmp['matchID'] = $match['matchID'];
		
		$matchList[] = $tmp;
		
	}
	
	
	switch(isLivestreamValid()){
		case 'live':
			$class= 'alert';
			$statusText = 'LIVE';
			$enableButton = 'deactivate';
			break;
		case 'ready';
			$class = 'success';
			$statusText = 'Ready';
			$enableButton = 'activate';
			break;
		default:
			$cass = '';
			$statusText = 'Disabled';
			break;
	}
	
// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

<!-- Status bar -->
	<div class='callout text-center <?=$class?> '>
		<form method='POST'>
		<input type='hidden' name='formName' value='activateLivestream'>
		<div class='grid-x grid-margin-x'>
			<div class='medium-auto small-12 cell align-self-middle'>
				<h4>
					Livestream status:
					<strong><?=$statusText?></strong>
				</h4>
			</div>
			
			<!-- Start stream button -->
			<?php if($enableButton == 'activate'): ?>
			<div class='medium-2 small-12 cell'>
				<button class='button expanded success no-bottom' name='livestreamStatus' value=1>
					<strong>Go Live!</strong>
				</button>
			</div>
			<?php endif ?>
			
			<!-- End stream button -->
			<?php if($enableButton == 'deactivate'): ?>
			<div class='medium-2 small-12 cell'>
				<button class='button expanded alert no-bottom' name='livestreamStatus' value=0>
					End Stream
				</button>
			</div>
			<?php endif ?>
			
		</div>
		</form>
	</div>
	
<!-- Go to page -->
	<a class='button' href='livestream.php'>Go to Livestream</a>

<!-- Configuration -->
	<fieldset class='fieldset'>
		<legend><h5>Configuration</h5></legend>
		
		<form method='POST'>
		<div class='grid-x grid-margin-x'>
			
		<!-- Chanel name -->
		<div class='large-4 medium-6 small-12 cell'>
			<div class='input-group'>
				<span class='input-group-label'>
					Chanel Name:
				</span>
				<input class='input-group-field' type='text' name='chanelName'
					value='<?=$info['chanelName']?>'>
			</div>
		</div>
		
		<!-- Chanel name -->
		<div class='large-4 medium-6 small-12 cell'>
			<div class='input-group'>
				<span class='input-group-label'>
					Platform:
				</span>
				<select class='input-group-field' name='inputPlatform'>
					<option selected disabled hidden></option>
					
					<?php $selected = isSelected('twitch', $info['platform']); ?>
					<option value='twitch' <?=$selected?>>Twitch.tv</option>
					
					<?php $selected = isSelected('youtube', $info['platform']); ?>
					<option value='youtube' <?=$selected?>>YouTube</option>
					
					<?php $selected = isSelected('link', $info['platform']); ?>
					<option value='link' <?=$selected?>>Direct Link</option>
					
				</select>
			</div>
		</div>
			
		<!-- Use score overlay -->
		<div class='large-4 medium-6 small-12 cell'>
			<div class='input-group'>
				<span class='input-group-label'>
					Use match score overlay:
				</span>
				<?php $checked = isSelected(1 == $info['useOverlay'], 'checked'); ?>
				<div class='switch input-group-button large no-bottom'>
					<input type='hidden' name='useOverlay' value='0'>
					<input class='switch-input' type='checkbox' id='useOverlay' 
						name='useOverlay' value='1' <?=$checked?> >
					<label class='switch-paddle' for='useOverlay'>
					</label>
				</div>
			</div>
		</div>
		
		<!-- Submit button -->
		<div class='large-2 medium-3 small-12 cell'>
			<button class='button success expanded' name='formName' value='livestreamInfo'>
				Update
			</button>
		</div>
		
		</div>
		
		</form>
	</fieldset>
	
<!-- Match Order -->
	<h4>Livestream Matches</h4>
	<form method='POST'>
	The following matches will be displayed on your livestream:
	<button class='button success' name='formName' value='livestreamOrder'>
		Update
	</button>
	
	<?php for($num = 1; $num <= $numEventIncompletes; $num++): ?>
		<div class='input-group'>
			<span class='input-group-label'># <?=$num?></span>
			<select name=matchIDs[<?=$num?>]>
				<option></option>
				<?php foreach($matchList as $match): 
					$selected = isSelected($match['matchID'] == $matchOrder[$num]);
					?>
					<option value='<?=$match['matchID']?>' <?=$selected?>>
						<?=$match['matchName']?>
					</option>
				
				<?php endforeach ?>
			</select>
		</div>
	
	<?php endfor ?>
	
	</form>
	
	
<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/


/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
