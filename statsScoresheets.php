<?php
/*******************************************************************************
	Match Scoresheets
	
	Permanent record of match scores
		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Tournament';
include('includes/header.php');

$eventID = $_SESSION['eventID'];

if($eventID == null){
	pageError('event');
else if(ALLOW['STATS_EVENT'] != false){
	pageError('user');
} else {

	$scoresheets = getScoresheets($eventID, $_SESSION['tournamentID']);
	$lastMatchID = 0;

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>
	<a class='scoresheet-explain' onclick="$('.scoresheet-explain').toggle()">What is going on here?</a>
	<div class='hidden scoresheet-explain'>
		<p>
			After a match is concluded a virtual 'scoresheet' is created. This scoresheet acts as a permanent record of the match, and is not connected to the main database. This means that the scoresheet will be preserved even if a tournament/pool/match was deleted from the software. Because these sheets aren't connected to the database, it also means that they aren't really search-able with the software, and you just have to look through a big list; like real scoresheets. This only exists for backup/recovery purposes if matches were inadvertently deleted. <BR>
			<b>Important:</b> Matches can, and will, appear multiple times if they are re-opened and closed. A new sheet is created every time a match is closed.
		</p>

		<p>
			Scoresheets for the currently selected tournament are shown. If you deleted a tournament it won't be in this list, but you will see it if you ask for every scoresheet in the event.<BR>
			<form method="POST">
				<input type='hidden' name='formName' value='changeTournament'>
				<button class='button' value='0' name='newTournament'>Whole Event (in a Huge List)</button>
			</form>
		</p>

		<p>
			<b>Understanding a scoresheet</b><BR>
			(#Internal MatchID)<BR>
			Tournament Name (#Internal TournamentID)<BR>
			Group Name (#Internal GroupID)<BR>
			Pool Set <em>OR</em> Bracket Info. Bracket level 1 is furthest left. Bracket Position, 1 is the topmost in the bracket level.<BR>
			Fighter 1 [Final Score]<BR>
			Fighter 2 [Final Score]<BR>

			Exchange Time in Seconds, Fighter, [Points|Deduction], Additional Information<BR>
			<em>In the case of a no exchange, double or equal score/afterblow for both the fighter the software has to randomly assign it to one of them. Don't read too much into who it got assigned to. </em>

		</p>

	</div>


	<?php foreach($scoresheets as $sheet): ?>
		<HR>
		<?php if($sheet['matchID'] == $lastMatchID):?>
			<div class='callout warning'>
				Warning: This match has the same MatchID listed as the previous match. It may be a duplicate.
			</div>

		<?php endif ?>


		<pre><?=$sheet['scoresheet']?></pre>
		<?php ($lastMatchID = $sheet['matchID'])?>
	<?php endforeach ?>

<?php

}
	
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/******************************************************************************/




/******************************************************************************/

/******************************************************************************/