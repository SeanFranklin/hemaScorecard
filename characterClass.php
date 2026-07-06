<?php
/*******************************************************************************


*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Filter Matches By School';
$hidePageTitle = true;
include('includes/header.php');
{

	$systemRosterID = (int)$_SESSION['filterForSystemRosterID'];

// Stats ----------------------------------------------------------------
	$stats['stealth']['name'] = "Stealth";
	$stats['cunning']['name'] = "Cunning";
	$stats['toughness']['name'] = "Toughness";
	$stats['antagonism']['name'] = "Antagonism";
	$stats['wisdom']['name'] = "Wisdom";
	$stats['accuracy']['name'] = "Accuracy";
	$stats['versatility']['name'] = "Versatility";

	$stats['stealth']['text'] = "Stealthy fighters make their movements indiscernible, leaving judges with no choice but to call No Exchange.";
	$stats['cunning']['text'] = "Cunning fighters makes sure to always get the advantage, coming away with a point even when both fighters hit.";
	$stats['toughness']['text'] = "Tough fighters are always ready to duke it out, having lots of exchanges where both fighter hit each other.";
	$stats['antagonism']['text'] = "Antagonists sow the seeds of their own demise, engaging in exchanges where both hit, yet their opponents are the ones getting the points.";
	$stats['wisdom']['text'] = "Wise fighters are able to maximize their effectiveness, preferring exchanges where they receive multiple points.";
	$stats['accuracy']['text'] = "Accurate fighters are able to pinpoint their attacks, and land hits only when they won't be struck.";
	$stats['versatility']['text'] = "Versatile fighters are at home with every weapon, and diversify rather than specialize.";

	$stats['stealth']['breakpoints'] 		= [  3,  8, 13, 19];
	$stats['cunning']['breakpoints'] 		= [  0,  2,  4,  7];
	$stats['toughness']['breakpoints'] 		= [  1, 13, 20, 35];
	$stats['antagonism']['breakpoints'] 	= [  0,  2,  5,  7];
	$stats['wisdom']['breakpoints'] 		= [ 13, 20, 26, 36];
	$stats['accuracy']['breakpoints'] 		= [ 40, 47, 53, 62];
	$stats['versatility']['breakpoints'] 	= [  0,  5, 10, 15];


// Classes ----------------------------------------------------------------
	$class['Psionic']['attributes'] 	= ['stealth'=>2,'cunning'=>2,'toughness'=>1,'antagonism'=>1,'wisdom'=>4,'accuracy'=>1,'versatility'=>1];
	$class['Marine']['attributes'] 		= ['stealth'=>1,'cunning'=>1,'toughness'=>3,'antagonism'=>2,'wisdom'=>1,'accuracy'=>2,'versatility'=>2];
	$class['Engineer']['attributes']	= ['stealth'=>1,'cunning'=>2,'toughness'=>1,'antagonism'=>3,'wisdom'=>1,'accuracy'=>1,'versatility'=>4];
	$class['Sniper']['attributes'] 		= ['stealth'=>2,'cunning'=>1,'toughness'=>1,'antagonism'=>1,'wisdom'=>0,'accuracy'=>4,'versatility'=>1];
	$class['Knight']['attributes'] 		= ['stealth'=>1,'cunning'=>1,'toughness'=>5,'antagonism'=>1,'wisdom'=>1,'accuracy'=>1,'versatility'=>1];
	$class['Rogue']['attributes'] 		= ['stealth'=>1,'cunning'=>4,'toughness'=>1,'antagonism'=>3,'wisdom'=>1,'accuracy'=>1,'versatility'=>1];
	$class['Black Mage']['attributes'] 	= ['stealth'=>0,'cunning'=>1,'toughness'=>1,'antagonism'=>4,'wisdom'=>5,'accuracy'=>1,'versatility'=>1];
	$class['Alchemist']['attributes'] 	= ['stealth'=>1,'cunning'=>1,'toughness'=>1,'antagonism'=>0,'wisdom'=>5,'accuracy'=>1,'versatility'=>3];
	$class['Red Mage']['attributes'] 	= ['stealth'=>1,'cunning'=>1,'toughness'=>1,'antagonism'=>1,'wisdom'=>2,'accuracy'=>1,'versatility'=>5];
	$class['Berserker']['attributes'] 	= ['stealth'=>1,'cunning'=>1,'toughness'=>4,'antagonism'=>3,'wisdom'=>1,'accuracy'=>1,'versatility'=>1];
	$class['Ninja']['attributes'] 		= ['stealth'=>5,'cunning'=>2,'toughness'=>0,'antagonism'=>2,'wisdom'=>0,'accuracy'=>0,'versatility'=>1];
	$class['Archer']['attributes'] 		= ['stealth'=>1,'cunning'=>1,'toughness'=>1,'antagonism'=>1,'wisdom'=>1,'accuracy'=>5,'versatility'=>0];
	$class['Dragoon']['attributes'] 	= ['stealth'=>1,'cunning'=>1,'toughness'=>4,'antagonism'=>3,'wisdom'=>1,'accuracy'=>2,'versatility'=>1];
	$class['Changeling']['attributes'] 	= ['stealth'=>1,'cunning'=>2,'toughness'=>1,'antagonism'=>1,'wisdom'=>1,'accuracy'=>2,'versatility'=>3];
	$class['Bard']['attributes'] 		= ['stealth'=>2,'cunning'=>1,'toughness'=>0,'antagonism'=>2,'wisdom'=>2,'accuracy'=>1,'versatility'=>2];
	$class['Geomancer']['attributes'] 	= ['stealth'=>1,'cunning'=>1,'toughness'=>1,'antagonism'=>1,'wisdom'=>2,'accuracy'=>1,'versatility'=>4];
	$class['Monk']['attributes'] 		= ['stealth'=>1,'cunning'=>1,'toughness'=>4,'antagonism'=>1,'wisdom'=>2,'accuracy'=>1,'versatility'=>2];
	$class['Necromancer']['attributes'] = ['stealth'=>1,'cunning'=>2,'toughness'=>1,'antagonism'=>5,'wisdom'=>2,'accuracy'=>1,'versatility'=>1];
	$class['NPC']['attributes']         = ['stealth'=>1,'cunning'=>1,'toughness'=>1,'antagonism'=>1,'wisdom'=>1,'accuracy'=>1,'versatility'=>1];

	$class['Knight']['modifier'] = 3;
	$class['Changeling']['modifier'] = -1;
	$class['Bard']['modifier'] = -1.5;
	$class['Red Mage']['modifier'] = 2;

	$class['Psionic']['text'] 		= "Psionic are wise with an element of cunning. And can fit in both a fantasy and sci-fi setting.";
	$class['Marine']['text'] 		= "Marines are balanced, with some degree of accuracy, versatility, antagonism, and a healthy dose of toughness.";
	$class['Engineer']['text']		= "Very versatile and cunning, and engineers are also known for being abrasive.";
	$class['Sniper']['text'] 		= "Accurate, with a small dash of stealth. The 20th century version of the archer, making it actually cool.";
	$class['Knight']['text'] 		= "The knight prizes toughness above all else. Whether that is good or bad depends on your need for tank.";
	$class['Rogue']['text'] 		= "The rogue's is known far and wide for it's cunning, but these tales are not conveyed in a positive tone.";
	$class['Black Mage']['text']	= "Wise and powerful in the ways of the arcane, the black mage disdained rather than revered.";
	$class['Alchemist']['text']		= "While not seen as formidable as other wizards, the alchemist uses their wisdom to be able to solve a wide array of problems.";
	$class['Red Mage']['text'] 		= "The true jack of all trades, and either super overpowered or absolutely trash.";
	$class['Berserker']['text'] 	= "The berserker is tough enough to take the hits, and (to the consternation of their opponents) dishes them out in return.";
	$class['Ninja']['text'] 		= "The pinnacle of stealth, with only hushed whispers to speak of their guile.";
	$class['Archer']['text'] 		= "Bring an archer when you want to nail the target with pinpoint accuracy, but realize they are a specialized tool.";
	$class['Dragoon']['text'] 		= "Go play old-school Final Fantasy games to figure out what this class means.";
	$class['Changeling']['text'] 	= "Versatile , cunning, and wise. (Not the high-fantasy changelings. Think DS9.)";
	$class['Bard']['text'] 			= "The bard is middle of the road in almost all aspects, not acting as the standout in the party.";
	$class['Geomancer']['text'] 	= "At home in any location, and wise enough to bring out the best of the terrain at hand.";
	$class['Monk']['text'] 			= "The dude in the party who for some reason gets by just being tough and punching stuff. Has some other more versatile skills inspired from mysticism.";
	$class['Necromancer']['text'] 	= "The class everyone loves to hate. You are still cunning and wise, though not as much as you like to think.";
	$class['NPC']['text'] 			= "You haven't done a lot.";


// Bins ----------------------------------------------------------------
	$bins['total'] = 0;

	foreach($class as $n => $c){
		$bins['classes'][$n]['primary'] = 0;
		$bins['classes'][$n]['secondary'] = 0;
	}

	for($i = 0; $i <= 100; $i++){
		$bins['stats'][$i]['stealth'] = 0;
		$bins['stats'][$i]['cunning'] = 0;
		$bins['stats'][$i]['toughness'] = 0;
		$bins['stats'][$i]['antagonism'] = 0;
		$bins['stats'][$i]['wisdom']= 0;
		$bins['stats'][$i]['accuracy'] = 0;
		$bins['stats'][$i]['versatility'] = 0;
	}



// Loop ----------------------------------------------------------------

	$bins['show']['stats'] = false;
	$bins['show']['class'] = true;

	//for($systemRosterID = 1; $systemRosterID < 8297; $systemRosterID++)
	{

		$fighterStats = getFighterStats($systemRosterID);

		if($fighterStats != []){

			$stats['stealth']['raw'] = round(100 * @$fighterStats['noExchange'] / $fighterStats['totalExchange']);
			$stats['cunning']['raw'] = round(100 * @$fighterStats['abFor'] / $fighterStats['totalExchange']);
			$stats['toughness']['raw'] = round(100 * @$fighterStats['bilaterals'] / $fighterStats['totalExchange']);
			$stats['antagonism']['raw'] = round(100 * @$fighterStats['abAgainst'] / $fighterStats['totalExchange']);
			$stats['wisdom']['raw'] = round(100 * @$fighterStats['multiPointExch'] / $fighterStats['totalExchange']);
			$stats['accuracy']['raw'] = round(100 * @$fighterStats['cleanFor'] / $fighterStats['totalExchange']);
			$stats['versatility']['raw'] = round(@$fighterStats['weaponScore']);

			foreach($stats as $i => $s){
				$stats[$i]['stars'] = getStars($s['raw'], $s['breakpoints'] );
			}

			$charClass = calculateClass($stats, $class);

			$bins['stats'][$stats['stealth']['raw']]['stealth']++;
			$bins['stats'][$stats['cunning']['raw']]['cunning']++;
			$bins['stats'][$stats['toughness']['raw']]['toughness']++;
			$bins['stats'][$stats['antagonism']['raw']]['antagonism']++;
			$bins['stats'][$stats['wisdom']['raw']]['wisdom']++;
			$bins['stats'][$stats['accuracy']['raw']]['accuracy']++;
			$bins['stats'][$stats['versatility']['raw']]['versatility']++;

			$bins['classes'][$charClass['class'][0]]['primary']++;
			$bins['classes'][$charClass['class'][1]]['secondary']++;
			$bins['total']++;

		}

	}

	if($bins['total'] > 1 &&  $bins['show']['stats'] == true){
		showAllStats($bins, $stats);
	}
	if($bins['total']  > 1 && $bins['show']['class'] == true){
		showAllClasses($bins, $class);
	}

	$scoreSum = 0;
	foreach($charClass['data'] as $n => $score){
		$scoreSum += abs($score);
	}

	foreach($charClass['data'] as $n => $score){
		$charClass['data'][$n] *= 100/$scoreSum;
		$charClass['data'][$n] = pow($charClass['data'][$n], 0.7);
	}



// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?=changeRosterFilterDropdown()?>

	<?php if($_SESSION['filterForSystemRosterID'] != 0): ?>




		<div class='grid-x grid-margin-x'>

			<fieldset class='cell large-4 medium-6 callout fieldset'>
				<legend>
					<span style='font-size:1.5em'>Character</span>
				</legend>

				<div class='grid-x grid-margin-x'>
					<div class='small-8 cell'>

						<u>Name</u>:
						<BR><b><?=getFighterNameSystem($_SESSION['filterForSystemRosterID'])?></b>
						<BR><BR>
						<u>Faction</u>:<BR>
						<b><?=getFighterSchoolNameSystem($_SESSION['filterForSystemRosterID'])?></b>
						<BR><BR>
					</div>
					<div class='small-4 cell'>
						<span style='font-size:0.85em'>Powered by</span><BR>
						<img src='includes/images/logo_square.jpg' width='100px'>
						<BR><i><span style='font-size:0.6em; line-height: 1.0em; display:block'>(look, I was smart enough to include branding this year)</span></i>
					</div>
				</div>

				<u>Primary Class</u>: <b><?=$charClass['class'][0]?></b><BR>
				<i><?=$class[$charClass['class'][0]]['text']?></i>
				<BR><BR>
				<u>Secondary Class</u>: <b><?=$charClass['class'][1]?></b><BR>
				<i><?=$class[$charClass['class'][1]]['text']?></i>

			</fieldset>

			<fieldset class='cell large-4  medium-6 callout fieldset'>
				<legend>
					<span style='font-size:1.5em'>
						Base Stats
						<?=tooltip('Personal stats are determined relative to the average character attributes in the database.')?>
					</span>
				</legend>
				<table>
					<?php foreach($stats as $s):?>
						<tr>
							<td  class='text-right' style='border-right: solid 1px black'><?=$s['name']?>  </td>
							<td><b><?=showStars($s['stars'])?></b></td>
							<td><?=tooltip($s['text'])?></td>
						</tr>
					<?php endforeach ?>

				</table>

			</fieldset>

			<span class='cell large-4  medium-6 text align-self-middle text-center' >

			<a class='aptitudes' style='color:white' onclick="$('.aptitudes').toggleClass('hidden')">Hidden Link</a>

			<fieldset class='callout fieldset hidden aptitudes'>
				<legend>
					<span style='font-size:1.5em'>Aptitudes</span>
				</legend>
				<table class='table-compact'>
					<?php foreach($charClass['data'] as $n => $score ):



						?>
						<tr>
							<td class='text-right'  style='border-right: solid 1px black'>
								<?=$n?>
							</td>
							<td class='text-left'>
								<?=showStars($score)?>
							</td>
						</tr>

					<?php endforeach ?>
				</table>
			</fieldset>
		</span>


		</div>




	<?php endif ?>


<?php

}

include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////



/******************************************************************************/

function getFighterStats($systemRosterID,  $minExchanges = 0){

	$fighterStats = [];

	$weaponScore = 0;
	$sql = "SELECT COUNT(*) AS numEntries
			FROM eventTournamentRoster
			INNER JOIN eventTournaments USING(tournamentID)
			INNER JOIN eventRoster USING(rosterID)
			WHERE systemRosterID = {$systemRosterID}
			GROUP BY tournamentWeaponID";
	$tournamentWeapons = (array)mysqlQuery($sql, SINGLES, 'numEntries');

	$weaponScore = 0;
	if($tournamentWeapons != []){

		$numWeapons = count($tournamentWeapons);
		$sumOfSquares = 0;
		$highestWeapon = 0;

		foreach($tournamentWeapons as $w){

			$weaponScore += min($w, 3);
			$sumOfSquares += ($w * $w);
			if($w > $highestWeapon){
				$highestWeapon = $w;
			}
		}


		$weaponScore /= $highestWeapon;
		$weaponScore *= 5;

		$weaponScore = min($weaponScore, 100);
	}


	$fighterStats['weaponScore'] = $weaponScore;

	$sql = "SELECT exchangeType, scoreValue, scoreDeduction, 1 AS isFighter
			FROM eventExchanges AS eE
			INNER JOIN eventRoster AS eR ON eE.scoringID = eR.rosterID
			WHERE systemRosterID = {$systemRosterID}
			AND exchangeType IN('noExchange','clean','afterblow','double')";
	$exchFor = (array)mysqlQuery($sql, ASSOC);

	if(count($exchFor) < $minExchanges/2){
		return [];
	}

	$sql = "SELECT exchangeType, scoreValue, scoreDeduction, 0 AS isFighter
			FROM eventExchanges AS eE
			INNER JOIN eventRoster AS eR ON eE.receivingID = eR.rosterID
			WHERE systemRosterID = {$systemRosterID}
			AND exchangeType IN('noExchange','clean','afterblow','double')";
	$exchAgainst = (array)mysqlQuery($sql, ASSOC);

	$allExchanges = array_merge(@(array)$exchFor, @(array)$exchAgainst);



	foreach($allExchanges as $e){

		switch($e['exchangeType']){

			case 'noExchange': {
				@$fighterStats['noExchange']++;
				@$fighterStats['totalExchange']++;
				break;
			}

			case 'afterblow':
			{

				if($e['scoreValue'] > $e['scoreDeduction'] && $e['isFighter'] == 1){
					@$fighterStats['abFor']++;

					if($e['scoreValue'] - $e['scoreDeduction'] > 1){
						@$fighterStats['multiPointExch']++;
					}
				}

				if($e['scoreValue'] > $e['scoreDeduction'] && $e['isFighter'] == 0){
					@$fighterStats['abAgainst']++;
				}

				@$fighterStats['bilaterals']++;

				@$fighterStats['totalExchange']++;
				break;
			}

			case 'clean':
			{

				if($e['isFighter'] == 1){
					if($e['scoreValue'] > 1){
						@$fighterStats['multiPointExch']++;
					}
					@$fighterStats['cleanFor']++;
					@$fighterStats['totalExchange']++;
				}

				break;
			}

			case 'double':
			{
				@$fighterStats['Bilaterals']++;
				@$fighterStats['totalExchange']++;
				break;
			}
		}

	}

	if(@$fighterStats['totalExchange'] == 0){
		$fighterStats['totalExchange'] = 1;
	}

	return $fighterStats;
}

/******************************************************************************/

function calculateClass($stats, $class, $showClass = false){

	$best['score'] = [-9999,9999];
	$best['class'] = ["",""];
	$best['lowScore'] = 9999;

	foreach($class as $n => $c){


		$score = 0;
		foreach($stats as $stat => $s){
			$penalty = abs($s['stars'] - $c['attributes'][$stat]);
			$penalty *= $penalty;

			$score -= $penalty;
			if($s['stars'] == 5 && $c['attributes'][$stat] == 5){
				$score += 5;
			}

			$score += 8;

		}

		$score += @$c['modifier'];
		$class[$n]['score'] = $score;



		if($score > $best['score'][0]){

			$best['class'][1] = $best['class'][0];
			$best['class'][0] = $n;

			$best['score'][1] = $best['score'][0];
			$best['score'][0] = $score;


		} else if($score > $best['score'][1]){

			$best['class'][1] = $n;

			$best['score'][1] = $score;

		} else {
			// Do nothing
		}

		if($score < $best['lowScore']){
			$best['lowScore'] = $score;
		}

		$best['data'][$n] = $score;

	}

	return $best;


}


/******************************************************************************/

function showStars($value){

	for($i = 0; $i < $value; $i++){
		echo "★ ";
	}

}

/******************************************************************************/


function getStars($value, $breakpoints){

	$numStars = 5;
	foreach($breakpoints as $i => $point){
		if($value <= $point){
			$numStars = $i + 1;
			break;
		}
	}


	return $numStars;
}

/******************************************************************************/

function showAllClasses($bins, $class){
?>

	<table>
	<?php foreach($class as $n =>$c): ?>
		<tr>
			<td><?=$n?></td>
			<td><?=$bins['classes'][$n]['primary']?></td>
			<td><?=$bins['classes'][$n]['secondary']?></td>
			<td><?=$bins['classes'][$n]['primary'] + $bins['classes'][$n]['secondary']?></td>
		</tr>
	<?php endforeach ?>
	</table>

<?php
}


/******************************************************************************/

function showAllStats($bins, $stats){
?>

	<table>

		<tr>
			<th></th>
			<?php foreach($stats as $s): ?>
				<th>
					<?=$s['name']?>
				</th>
			<?php endforeach ?>
		</tr>

		<?php foreach($bins['stats'] as $i => $b): ?>

			<tr>
				<td><?=$i?></td>

				<?php foreach($b as $s): ?>

					<td><?=$s?></th>

				<?php endforeach ?>

			</tr>
		<?php endforeach ?>

	</table>

<?php

}

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
