<?php 
/*******************************************************************************
	Info Summary
	
	Displays all the tournament medalists
	Login: 
		- ADMIN or above can add/remove final medalists 
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Livestream";
$jsIncludes[] = 'livestream_scripts.js';
include('includes/header.php');

if($_SESSION['eventID'] == null){
	pageError('event');
} else {
	$info = getLivestreamInfo();
	
	if($info['platform'] == 'link' && $info['isLive'] == 1){
		redirect("{$info['chanelName']}");
	}

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
?>
	<input type='hidden' id='eventID' value='<?=$_SESSION['eventID']?>'>
	<?php if(ALLOW['EVENT_MANAGEMENT'] == true || ALLOW['VIEW_SETTINGS'] == true): ?>
		<a class='button' href='livestreamManagement.php'>Go to Livestream Management</a>
	<?php endif ?>


	<?php if($info['isLive'] == 1): 
		switch($info['platform']){
			case 'twitch':
				displayUsingTwitch($info);
				break;
			case 'youtube':
				displayUsingYoutube($info);
				break;
			default:
		}

	?>
		
	<?php else: ?>
		<?php displayAlert("Sorry, there is no active livestream for this event"); ?>
	<?php endif ?>

	
<?php }
include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function displayUsingYoutube($info){
	if($info['useOverlay'] == 1){
		$fullscreen = null;
	} else {
		$fullscreen = 'allowfullscreen';
	}
	
	?>
	
	<script>
		// Find all YouTube videos
		var $allVideos = $("iframe[src^='//www.youtube.com']");

		// The element that is fluid width
		$fluidEl = $("body");

		// Figure out and save aspect ratio for each video
		$allVideos.each(function() {

		  $(this)
			.data('aspectRatio', this.height / this.width)

			// and remove the hard coded width/height
			.removeAttr('height')
			.removeAttr('width');

		});

		// When the window is resized
		$(window).resize(function() {

		  var newWidth = $fluidEl.width();

		  // Resize all videos according to their own aspect ratio
		  $allVideos.each(function() {

			var $el = $(this);
			$el
			  .width(newWidth)
			  .height(newWidth * $el.data('aspectRatio'));

		  });

		// Kick off one resize to fix all videos on page load
		}).resize();
	</script>
	
	<style>
	.video-container {
		position: relative;
		padding-bottom: 56.25%; /* 16:9 */
		padding-top: 30px; /* size of chrome */
		height: 0; 
		overflow: hidden;
	}
	 
	.video-container iframe,
	.video-container object,
	.video-container embed {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
	}
	

	.video-container #overlay {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
	}
	
	.video-container #overlay2 {
		position: absolute;
		bottom: 0px;
		left: 0;
		width: 100%;
	}
	
	.video-container #screenCover {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
	}

	#overlay, #overlay2 {
		background:#000;
		opacity:0.5;
		/*background:rgba(255,255,255,0.8); or just this*/
		z-index:50;
		color:#fff;
		pointer-events: none;
	}
	
	#video-holder.fullscreen{
		z-index: 9999; 
		width: 100%; 
		position: fixed; 
		top: 0; 
		left: 0; 
	 }
	
	</style>
	
	<?php if($info['useOverlay'] == 1): ?>
		<script>
			window.onload = function(){ setInterval(updateLivestream,1000); };
		</script>
	<?php endif ?>

	<div class="holder" id='video-holder'>
	<div class="video-container">

	
	<iframe width="560" height="349" src="https://www.youtube.com/embed/5KXu4AQ9NkM?wmode=opaque&autoplay=1&loop=1" 
	frameborder="0" gesture="media" allow="encrypted-media" <?=$fullscreen?>>
	</iframe>
	
	
	

<!-- Overlay ---------->
	<?php if($info['useOverlay'] == 1): ?>
	
	<!-- Covers the screen and prevents interaction with youtube controls.
		Is also a clickable div which toggles fake fullscreen -->
	<div id='screenCover' onclick="$('#video-holder').toggleClass('fullscreen');" >
	</div>
	
	<!-- Upper overlay. Scores and match information -->
	<div id="overlay">
	<div class='grid-x align-middle overlay'>
		<div class='shrink cell' id='color1Div'>
			<div class='grid-x align-middle'>
				<div class='shrink cell'>
					<span class='overlay-score overlay-left' id='fighter1Score'>-</span>
				</div>
				<div class='shrink cell overlay-div'>
					<span class='overlay-name' id='fighter1Name'>----</span><BR>
					<span class='overlay-school hide-for-small-only' id='fighter1School'>----</span>
				</div>
			</div>
			
			
		</div>
		<div class='auto text-center cell'>

			<div class='hide-for-small-only'><span class='overlay-school' id='tournamentName'>------</span><BR></div>
			<span class='overlay-name' id='matchName'>----</span>
	
		</div>
		
		<div class='shrink cell' id='color2Div'>
			<div class='grid-x align-middle'>
				
				<div class='shrink cell'>
					<span class='overlay-name overlay-left' id='fighter2Name'>----</span><BR>
					<span class='overlay-school overlay-left hide-for-small-only' id='fighter2School'>----</span>
				</div>
				<div class='shrink cell overlay-div'>
					<span class='overlay-score' id='fighter2Score'>-</span>
				</div>
			</div>
		</div>
	</div>
	</div>
	
	<!-- Lower overlay. Last exchange, match time, and doubles -->
	<div id="overlay2">
	<div class='grid-x align-middle overlay'>
		<div class='small-5'>
			<div class='grid-x align-middle overlay'>
			<div class='shrink'>
				<span class='overlay-school overlay-left hide-for-small-only'>Last Exchange: </span>
				<span class='overlay-name overlay-left show-for-small-only'>Last: </span>
			</div>
			<div class='shrink' style='background:#555;' id='lastExchange'>
				<span class='overlay-name overlay-left' id='exchangeType'>
				&nbsp;
				</span><BR>
				<span class='overlay-name overlay-left' id='exchangePoints'>
				&nbsp;
				</span>
			</div>
			</div>
			
		</div>
		<div class='small-2 text-center'>
			<span class='overlay-score' id='timeDiv'>0:00</span>
		</div>
		
		<div class='small-5 text-right'>
			<span class='overlay-name' id='doublesDiv'></span>
		</div>
	</div>
	</div>
	
	<?php endif ?>
	
	</div>
	</div>

	
<?php }

/******************************************************************************/

function displayUsingTwitch($info){
	?>
	<!-- Add a placeholder for the Twitch embed -->
	
	<style>
	.video {
		position: relative;
		display: inline-block;

		/* ie7 inline-block support */
		*display: inline;
		*zoom: 1;
	}	
		
		
	#over {
		position: absolute;
		top: 10px;
		left: 100px;
		background-color: #ff0000;
	}

	</style>
	
	
	<div id="twitch-embed">
		<div class='video'>
	<div id='over'>
	ASDF
	</div>
	</div>
	</div>
	
	<!-- Load the Twitch embed script -->
	<script src="https://embed.twitch.tv/embed/v1.js"></script>

	<!-- Create a Twitch.Embed object that will render within the "twitch-embed" root element. -->
	<script type="text/javascript">
	  new Twitch.Embed("twitch-embed", {
		width: '100%',
		height: 467,
		channel: "<?=$info['chanelName']?>"
	  });
	</script>
	
<?php }

/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
