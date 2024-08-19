<?php
/*******************************************************************************
	Logistics Locations

*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Event FAQ';
$includeTournamentName = false;
$jsIncludes[] = "sortable_scripts.js";
include('includes/header.php');

$tournamentID = $_SESSION['tournamentID'];
$faq = getEventFaq($_SESSION['eventID']);

if($_SESSION['eventID'] == null){
	pageError('event');
} elseif(ALLOW['EVENT_MANAGEMENT'] == false && ($faq == [] || ALLOW['VIEW_RULES'] == false)) {
	pageError('user');
} else {

	if(isAdminOptionSet('forcePlainText') == true){
		$wysisygClass = '';
	} else {
		$wysisygClass = ''; ////'tiny-mce';
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>

	<?php foreach($faq as $question):?>
		<?=faqDisplay($question, $wysisygClass)?>
	<?php endforeach ?>

	<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
		<?=reOrderFaq($faq)?>
		<?=faqDisplay([], $wysisygClass)?>
	<?php endif ?>

<?php }
include('includes/footer.php');


// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function reOrderFaq($faq){

	if(ALLOW['EVENT_MANAGEMENT'] == false || sizeof((array)$faq) < 2){
		return;
	}

?>
	<HR>

	<div class='grid-x grid-margin-x'>
	<div class='large-12 cell'>
		<a class='re-order-faq' onclick="$('.re-order-faq').toggle()">Re-Order Questions ↓</a>
		<h5 class='re-order-faq hidden'><BR><a  onclick="$('.re-order-faq').toggle()">Re-Order Questions ↑</a></h5>
	</div>

	<div class='large-6 cell re-order-faq hidden callout text-right'>
	<form method="POST">

		<div  id='sort-faq-order'>
			<?php foreach($faq as $q): ?>
				<div class='callout primary text-left sortable-item' value=<?=$q['faqID']?> >
					<?=$q['faqQuestion']?>
				</div>
			<?php endforeach ?>
		</div>

		<?php foreach($faq as $index => $q): ?>
			<input class='hidden' name='orderFaq[faq][<?=$q['faqID']?>]'
				id='faq-order-for-<?=$q['faqID']?>' value=<?=$index?>>
		<?php endforeach ?>

		<button class='button success no-bottom' name='formName' value='orderFaq'>
			Update Question Order
		</button>

	</form>
	</div>
	</div>

<?php
}

/******************************************************************************/

function faqDisplay($faq, $wysisygClass){

	if($faq == []){
		$faq['faqQuestion'] = '';
		$faq['faqAnswer'] = ' ';
		$faq['faqID'] = 0;
		$buttonText = 'Add Question';
		$hidden = '';
		$new = true;
	} else {
		$buttonText = 'Update Question';
		$hidden = 'hidden';
		$new = false;
	}

?>

	<HR>

	<?php if($new == false): ?>
	<div>
		<b><?=$faq['faqQuestion']?></b>
		<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
			<i>	<a onclick="$('.question-<?=$faq['faqID']?>').toggleClass('hidden')">(Edit)</a></i>
		<?php endif ?>
		<BR><?=$faq['faqAnswer']?>
	</div>
	<?php elseif(ALLOW['EVENT_MANAGEMENT'] == true): ?>
		<b>Add New Question:</b>
	<?php endif ?>


	<?php if(ALLOW['EVENT_MANAGEMENT'] == true): ?>
	<div class='<?=$hidden?> question-<?=$faq['faqID']?>'>
		<form method="POST">

			<input type='hidden' name='updateFaq[faqID]' value=<?=$faq['faqID']?>>
			<input type='text' name='updateFaq[faqQuestion]' rows='1' placeholder='Question' value='<?=$faq['faqQuestion']?>'>
			<textarea name='updateFaq[faqAnswer]' class='<?=$wysisygClass?>' rows='5' placeholder='Answer'><?=$faq['faqAnswer']?></textarea>

			<button class='button success' name='formName' value='updateFaq'>
				<?=$buttonText?>
			</button>
			<?php if($new == false):?>
				<i>(Remove all text from question and answer to delete the whole question.)</i>
			<?php endif ?>
		</form>
	</div>
	<?php endif ?>



<?php
}


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
