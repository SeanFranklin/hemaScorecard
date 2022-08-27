<?php 
/*******************************************************************************
	Sales Page
	
	Information about the software and why people should use it
	LOGIN: N/A
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = "Why Choose HEMA Scorecard?";
$hideEventNav = true;
$hidePageTitle = true;
include('includes/header.php');

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>

<div class='grid-x grid-margin-x'>
	<div class='medium-9 cell'>
		<h3>Why choose HEMA Scorecard?</h3>

		You might be skeptical of switching from good ol' reliable pen and paper to tournament software. What can HEMA Scorecard do for you?

		<BR><BR><h5 style='display:inline;'><a href='#math'>Skip the math</a>:</h5>
		Scorecard calculates all of your pool scores and bracket advancements. 
		It can even do solo events like cutting! </li>

		<BR><BR><h5 style='display:inline;'><a href='#show'>Show the world</a>:</h5>  
		Results from HEMA Scorecard are live online as soon as the scorkeeper inputs them.
		Participants know how they are doing as soon as they can check their phone,
		and folks back home can follow a match blow for blow.


		<BR><BR><h5 style='display:inline;'><a href='#stats'>See the big picture</a>:</h5>
		HEMA Scorecard can give you detailed stats on how your tournaments ran, 
		how the fighters fought, and compare across multiple events.


		<BR><BR>Best of all, the price: <strong>Free</strong>. Sold yet? If you are interested in learning more contact
		<span style="unicode-bidi:bidi-override; direction: rtl; text-decoration:underline">
		ac.nilknarfnaes@ameh
		</span>
	</div>
	<div class='medium-3 small-6 cell'>
		<img src="includes/images/logo_square.jpg">
	</div>

</div>

<HR>
<a name='math'></a>
<h4>Skip the Math</h4>
<p>
	HEMA Scorecard supports all sorts of tournament formats. Deductive or Full Afterblows, Pools or Brackets, 
	or even crazy one hit tournaments with several rounds of pools. The program was designed to be as versitile
	as possible and run as many types of tournaments as possible.
</p>

<p>
	<img src='includes/images/promo_table_s.jpg' style="border: 1px solid black;">
</p>

<p>
	<i>Have something so crazy HEMA Scorecard can't even handle it?</i> Great! We want to
	hear about it and may make changes to allow you to realize your dream (or nightmare).
</p>

<HR>
<a name='show'></a>
<h4>Show the World</h4>
<p>
	HEMA Scorecard lives online, which means that your participants and spectators are no longer in the dark.
	<BR>
	This also means that you don't have to take the time to print pool results and make brackets for people to follow.
	Why? Because it's right there at their fingertips, with no effort on your part. <BR>
	<em>(Yes, <a href='http://hemaratings.com/'>HEMA Ratings</a> can also grab your results directly, without you needing to send them in.)</em>
</p>


<img src='includes/images/promo_phone_s.jpg' style="border: 1px solid black;">

<HR>
<a name='stats'></a>
<h4>See the Big Picture</h4>
<p>
	Maybe you've run the event, partied your heart out, and you are done until next year. Or maybe you want more?
	<BR>
	HEMA Scorecard keeps track of events exchange by exchange, allowing you to get
	a good look under the hood at your event.
</p>

<p>
	<strong>Event Summary</strong> - Have a quick look at how all of your tournaments stacked up:<BR>
	<img src='includes/images/tournamentSummary.jpg'  class='black-border'>
</p>


<p>
	<strong>Fighter Histories</strong> - Have a look at how fighters have performed over their careers:<BR>
	<img src='includes/images/fighterSummary.jpg' class='black-border'>
</p>


<?php include('includes/footer.php');

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
