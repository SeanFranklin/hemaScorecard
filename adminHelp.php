<?php
/*******************************************************************************
	Help
	
	Instructions on how to use the software
	LOGIN: N/A
	
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$pageName = 'Help';
include('includes/header.php');

// PAGE DISPLAY ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////	
?>
<a name='topOfPage'></a>

<?=helpDisplayAbout()?>

<ul class="tabs" data-tabs id="help-tabs">
	<li class="tabs-title is-active"><a href="#panel-user" aria-selected="true">Software Users</a></li>
	<li class="tabs-title"><a data-tabs-target="panel-organizer" href="#panel-user">Event Organizers</a></li>
</ul>



<div class="tabs-content" data-tabs-content="help-tabs">
	<div class="tabs-panel is-active" id="panel-user">
		<?=helpDisplayPerticipant()?>
	</div>
	<div class="tabs-panel" id="panel-organizer">
		<?=helpDisplayOrganizer()?>
	</div>
</div>




<?php include('includes/footer.php');

// FUNCTIONS ///////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/******************************************************************************/

function helpDisplayPerticipant(){
?>

<div class='documentation-div'>

<p>Thank you for checking out HEMA Scorecord. You’ve found your way to the Participant User Guide. Here you will get a how-to on how to navigate through the most important features of HEMA Scorecard. If you are an event organizer looking for help, please click on the "Event Organizers" tab above this paragraph.</p>



<h1>Events</h1>

<h3>Choosing An Event</h3>

<img src="includes/images/help_01.png">

<p>The landing page shows recently-completed, active, and upcoming events in chronological order <b class='red-text'>(1)</b>. If an upcoming or active event doesn’t appear here, it is either hidden by the event organizer or is not being hosted by HEMA Scorecard. If the event you are interested in has already occurred, you may need to navigate to “All Events” <b class='red-text'>(2)</b>, which is sorted by event name as a default.</p>

<h3>Event Information</h3>

<img src="includes/images/help_02.png">

<p>When you choose an event from the landing page, you will see all the different tournaments offered at the event <b class='red-text'>(3)</b>. If the event has concluded, you will also see the top competitors in each category <b class='red-text'>(4)</b>. The event organizer may have also provided information about the event on this page <b class='red-text'>(5)</b>.</p>

<img src="includes/images/help_03.png">

<p>Underneath the Event Information tab (which is underneath the “Menu” tab if you are on a mobile device), you will have more options for event information. All events will have an Event Roster <b class='red-text'>(6)</b> which will show all individuals registered for the event. The event organizer may also choose to use features like Schedule <b class='red-text'>(7)</b> and Tournament Rules <b class='red-text'>(8)</b>. If the Schedule feature is used, you will also have the ability to look at Individual Schedules <b class='red-text'>(9)</b>.</p>

<h3>Event Roster</h3>

<img src="includes/images/help_04.png">

<p>The Event Roster <b class='red-text'>(6)</b> will have basic statistics on those who have registered for the event. It will contain a full list of all the participants as well as their school, and will be sorted in alphabetical order by first name as a default.</p>

<h3>Schedule</h3>

<img src="includes/images/help_05.png">

<p>The Schedule <b class='red-text'>(7)</b>, if an event organizer chooses to use this feature, will contain information about which events, classes, workshops, etc are occurring during which time slots and at which location(s).</p>

<img src="includes/images/help_06.png">

<p>If the Schedule is populated, you can also view Individual Schedules <b class='red-text'>(9)</b>. These will show you all commitments you have made for a tournament– tournament entry, judging, instructor roles, etc– in chronological order. You can select your name from a dropdown list of event participants <b class='red-text'>(10)</b> to view your schedule.</p>

<a href='#topOfPage'>Back to Top</a>

<!---------------------------------------------------------------------->

<h1>Tournaments</h1>

<img src="includes/images/help_07.png">

<p>You may view an event’s tournaments by either clicking on them on the landing page <b class='red-text'>(3)</b> or by navigating to them underneath the “Select Tournament” menu <b class='red-text'>(11)</b>.</p>

<img src="includes/images/help_08.png">

<p>Selecting a tournament will bring you to the Tournament Roster page where you can view everyone in the tournament, in alphabetical order by first name. You are also able to navigate to Pool Rosters <b class='red-text'>(12)</b>, Pool Matches <b class='red-text'>(13)</b>, Pool Standings <b class='red-text'>(14)</b>, and Finals Bracket <b class='red-text'>(15)</b>.</p>

<h3>Pools</h3>

<img src="includes/images/help_09.png">

<p>Clicking on Pool Rosters <b class='red-text'>(12)</b> will bring up a list of the pools and their participants. The pool number and location can also be found here <b class='red-text'>(16)</b>. Additionally, at the bottom of the pools list is an option to create a filter for specific schools <b class='red-text'>(17)</b>. Applying this filter will show only those pools that contain members of the school that’s being filtered on.</p>

<img src="includes/images/help_10.png">

<p>Navigating to Pool Matches <b class='red-text'>(13)</b> will bring up a list of the matches for each pool. Typically tournaments will run through the matches in the order they appear in this list. Scores for the matches will be updated in real time on this page, and once a fight has concluded the winner will be bolded. You are able to click on the specific match to get more details for that match <b class='red-text'>(18)</b>. Note that there is also an option to create a school filter <b class='red-text'>(17)</b> at the bottom of the page.</p>

<img src="includes/images/help_11.png">

<p>Once pools have concluded, Pool Standings <b class='red-text'>(14)</b> will be finalized. This will show the standings of competitors after coming out of pools. The individuals who came first in their pools will be italicized. There will be a solid black line in the table which indicates the cutoff for finals. At the bottom of the table is a link that can be clicked which will explain the way the standings were calculated <b class='red-text'>(19)</b>; different tournaments will have different algorithms for calculating their standings.</p>

<h3>Finals</h3>

<img src="includes/images/help_12.png">

<p>Clicking on Finals Bracket <b class='red-text'>(15)</b> once pools are over will show a bracket that is populated in real time with the progression of finals. Competitors will see which ring they need to report to to the left of their names in the bracket <b class='red-text'>(20)</b>. Information about individual matches can be seen by pressing the “Go” button <b class='red-text'>(21)</b>.</p>


<h3>Matches</h3>

<img src="includes/images/help_13.png">

<p>Pool Matches <b class='red-text'>(13)</b> can be seen in greater detail by clicking on the match number <b class='red-text'>(18)</b>. Finals Matches <b class='red-text'>(15)</b> can similarly be viewed by pressing the “Go” button (21) on the bracket. Once on this page, you can see a running score <b class='red-text'>(23)</b> with the time elapsed <b class='red-text'>(22)</b> for the match updated in real time after each exchange is recorded. This page may also contain video for the match if a link has been provided to one.</p>

<a href='#topOfPage'>Back to Top</a>

</div>

<?php
}

/******************************************************************************/

function helpDisplayAbout(){
?>

<a class='about-scorecard button hollow' onclick="$('.about-scorecard').toggle()">
<h4 class='no-bottom'>About HEMA Scorecard</h4>
</a>

<fieldset class='fieldset hidden about-scorecard'>
<legend>
	<a class='button' onclick="$('.about-scorecard').toggle()">
<h4 class='no-bottom'>About HEMA Scorecard</h4>
</a>
</legend>

Developed by: Sean Franklin<BR>
<em>'HEMA is filled with software guys, so why did I 
have to teach myself how to code and do this?'</em>

<BR><BR>

<strong>About</strong><BR>
HEMA Scorecard is intended to make information about <em>Historical European Martial Arts</em> tournaments publicly accessible.<BR>
All* information captured by the software is public ably viewable on line, or can be requested as part of a statistical data dump.
<BR><em>*Naturally we aren't sharing event organizer's contact info.</em>

<BR><BR>

<strong>Terms of Use</strong><BR>
This software is free to use by all event organizers provided that:
<ul>
<li>You are running a HEMA tournament. (If you are doing re-enactment or sport fencing flash me some money and we can talk. :p )</li>
<li>Record all exchanges as they happen. This means recording all the No Exchanges, 
and if the score is 3 points minus 1 you record it as that, not as 2 points.</li>
<li>You haven't done anything egregious to piss me off.</li>
</ul>

<strong>Thanks To</strong>
<ul>
<li>The HEMA Alliance for hosting and advertising.</li>
<li>Jason Barrons for layout design and consultation.</li>
<li>Kari Baker for writing documentation.</li>
<li>Any event organizers who gave feedback and feature requests.</li>
</ul>

<a href='#topOfPage'>Back to Top</a>
</fieldset>

<?php
}

/******************************************************************************/

function helpDisplayOrganizer(){
?>


<div class='grid-x  secondary callout'>
<div class='large-3 cell align-self-middle' style='margin-bottom: 15px;'>
	<div class='grid-x'>
		<div class='large-12 medium-6 small-12'>
			<h4>Table of Contents:</h4>
		</div>
		<div class='large-12 medium-6 small-12'>
			<li><a href='#FAQ'>Frequently Asked Questions</a></li>
		</div>
	</div>
	
</div>	

<div class='large-9 medium-12'>
<div class='grid-x'>

	<div class='large-4 medium-4' style='margin-bottom: 15px;'>
		<li><a href='#gettingStarted'>Getting Started</a></li>
		<li><a href='#setUpEvent'>Setting Up An Event</a></li>
		<li><a href='#endUpEvent'>Concluding An Event</a></li>
		<li><a href='#eventStatus'>Event Status</a></li>
	</div>
	<div class='large-4 medium-4 cell' style='margin-bottom: 15px;'>
		<li><a href='#createTournaments'>Creating Tournaments</a></li>
		<li><a href='#addParticipants'>Adding Event Participants</a></li>
		<li><a href='#setupTournaments'>Setting Up Tournaments</a></li>
		<li><a href='#withdrawingFighters'>Injuries / Disqualifications</a></li>
	</div>
	<div class='large-4 medium-4 cell'>
		<ul>
		<li><a href='#runningPools'>Running Pools</a></li>
		<li><a href='#runningBracket'>Running Elimination Brackets</a></li>
		<li><a href='#runningMatch'>Scoring Matches</a></li>
		<li><a href='#runningRounds'>Running Scored Events</a></li>
		</ul>
	</div>
</div>
</div>
</div>





<!-- FAQ ------------------------------------------------------------>
<a name='FAQ'></a>
<fieldset class='fieldset'>
<legend><h4>Frequently Asked Questions</h4></legend>

<ul class='faq-list'>

<li>
	<em>How do I get my event set up?</em>
	Send the following information to
	<span style="unicode-bidi:bidi-override; direction: rtl; text-decoration: italic">
		<i>ac.nilknarfnaes@ameh</i>
	</span>
	<BR>&emsp;‣ Event Name
	<BR>&emsp;‣ Start Date
	<BR>&emsp;‣ End Date
	<BR>&emsp;‣ City
	<BR>&emsp;‣ State/Province
	<BR>&emsp;‣ Country
</li>

<li>
	<em>Why should I use HEMA Scorecard for my event?</em>
	<a href='infoWhy.php'>What a great question!</a>
</li>
	
<li>
	<em>Why can't I use any of the menu options?</em>
	Most likely you are not logged in. If you leave your computer unattended
	for too long your session may expire and you will be logged out. At this point
	you can navigate to pages and view results, but not modify anything.
	Log in again using the button in the upper right.
</li>

<li>
	<em>Does this work with HEMA Ratings?</em>
	Yes! Just let them know you have run your tournament using HEMA Scorecard and they
	are able to grab all the results. You don't have to worry about anything.
</li>

<li>
	<em>Can I use a different type of ranking?</em>
	Probably. All ranking algorithms need to be coded into the system prior to the
	start of the event. It is best to give the HEMA Scorecard team as much notice as posisble
	about the specific details of how you wish to score your event.
</li>

<li>
	<em>Why don't you have <i>&lt;this&gt;</i> feature?</em>
	There is a good chance no one has asked for it yet. Let the team know exactly what you
	want and it changes may be able to be made in a very short period of time.
</li>

<li>
	<em>I want more detailed stats on my event.</em>
	That's not a question, but I like where you are heading. Get in contact,
	we are happy to get you the information you need.
</li>
</ul>

<a href='#topOfPage'>Back to Top</a>
</fieldset>


<!-- Getting Started -------------------------------------------------->
<a name='gettingStarted'></a>
<fieldset class='fieldset'>
<legend><h4>Getting Started</h4></legend>

<h5>Selecting an Event</h5>
Once arriving on the main page you will be prompted to select an event. To switch to another event select ‘Change Event’ from the top navigational bar.
<li>Note: If you have logged in as event staff or and event organizer you will be logged out once you switch events. </li>
<BR>
<h5>Logging In</h5>
The <strong>Log In</strong> button is located in the upper right hand corner of the menu. 
This will take you to the log in screen where you can log into your even as staff or event organizer.
To log out click the <strong>Log Out</strong> button, located where the log in button used to be.

<BR><BR><h5>Switching Tournaments</h5>
Once you have selected an event you should select which tournament you wish to view. 
This is a drop down option in the upper left corner of the menu bar. The current tournament
will be displayed. Click on the tournament name to display a list of all the tournaments
in the event.
<li>Note: If no tournaments have been created yet this option will not appear.</li>

<BR><a href='#topOfPage'>Back to Top</a>
</fieldset>


<!-- Setting Up An Event ---------------------------------------------->
<a name='setUpEvent'></a>
<fieldset class='fieldset'>
<legend><h4>Setting Up Your Event</h4></legend>

The tools to set up and customize your event are available through the drop down menu <strong>Manage Event</strong>.

<BR><BR>
<h5>Setting Passwords</h5>
The first step to setting up your event is changing the password from the one your were
given. This is available through <strong><a href='adminEvent.php'>Manage Event > Event Settings</a></strong>, located on the bottom of the page.
<BR>
There are two different types of log in, Staff and Event Organizer. Staff have limited
access, and are only able to score matches but not perform tasks such as adding/removing fighters, 
changing event information and creating pools.

<BR><BR>
<h5>Setting Defaults</h5>
The <strong><a href='adminEvent.php'>Manage Event > Event Settings</a></strong> 
page also allows you to configure the default settings for your event.
This only affects the settings on any newly created events. If, for instance, your tournament will be using the colors 
red and gold you can change the default so that all new tournaments use these colors, 
rather than having to change each tournament individually.
<BR>These settings can be individually changed for any tournaments you create, 
this gives you the option to save time if you are creating multiple tournaments with the same settings.

<BR><BR><a href='#topOfPage'>Back to Top</a>
</fieldset>


<!-- Ending An Event ---------------------------------------------->
<a name='endUpEvent'></a>
<fieldset class='fieldset'>
<legend><h4>Concluding Your Event</h4></legend>

Your event is over. You've made it through! But it's not quite time to collapse
and forget about everything until next year. You need to finalize the tournament 
results so the people will be able to quickly see the event placings for all tournaments.
(And the HEMA Scorecard staff stop bugging you to make sure it is completed)

<BR><BR>Go to <strong><a href='infoSummary.php'>Event Status > Final Results</a></strong>
in the upper navigation bar. This will display a list of all events, and their final results.
Because you haven't done anything you will see a lot of 'Results Not Finalized'. Naturally
you should click on the <strong>Finalize Tournament</strong> button to capture the final results.

<BR><BR>If there is an elimination bracket the software will then automatically assign the 
final results. For other tournament types a list of positions will appear, for you to select fighters 
in ranked order. It is only necessary to specify the top 4 for display purposes. Populating the list further
is only necessary if you wish to have more detailed final placings for things like team point calculations.

<BR><BR><a href='#topOfPage'>Back to Top</a>
</fieldset>


<!-- Event Status --------------------------------------------->
<a name='eventStatus'></a>
<fieldset class='fieldset'>
<legend><h4>Event Status</h4></legend>

<h5>Participants / Schools</h5>
To view the registration summary of your event, go to 
<strong><a href='statsEvent.php'>Event Status > Participants/Schools</a></strong>
in the upper navigation menu. 
<BR>This will display a summary of:
<ol>
<li>Total Event Participants</li>
<li>Tournament Registrations</li>
<li>Attendance by School</li>
</ol>

<BR><h5>Tournament Stats</h5>
To view the registration summary of your event, go to 
<strong><a href='statsTournaments.php'>Event Status > Tournament Stats</a></strong>
in the upper navigation menu. 
<BR>This will display a summary of:
<ol>
<li>Total tournament exchanges</li>
<li>Percentage of exchanges by tournament. (Clean hits, doubles, etc...)</li>
<li>Point values awarded by tournament (Number of exchanges to each target)</li>
</ol>
<BR><a href='#topOfPage'>Back to Top</a>
</fieldset>


<!-- Creating Tournaments --------------------------------------------->
<a name='createTournaments'></a>
<fieldset class='fieldset'>
<legend><h4>Creating Tournaments</h4></legend>

<h5>New Tournaments</h5>
The next step is to create one or more tournaments for your event. This is done through the 
<strong><a href='adminNewTournaments.php'>Manage Event > Add New Tournaments</a></strong> page. 
<BR><em>Remember: you can't do this unless logged in as an Event Organizer.</em>
<BR>When adding a tournament you will have several options to specify. First is the tournament prefix, 
gender, material, and weapon. Prefix, gender, and material are all optional fields, and may be 
added to further differentiate events (ie. Beginners Synthetic Longsword, Women's Steel Longsword, 
Invitational Longsword).
<BR><BR>
<strong>Tournament Type</strong> defines what type of event your are running. The options are:
<li><u>Results Only</u> - Only list the participants, and the final placings of the tournament. 
Useful if you have an event you don't wish to use HEMA Scorecard for, but would like the 
event participants to see their registrations in the software.</li>
<li><u>Sparring Matches</u> - Most HEMA tournaments. This is any tournament where people (or teams) face each other in matches. <em>(This includes the old Pool & Bracket, Direct Bracket & Pool Sets modes)</em></li>
<li><u>Solo Scored</u> - An event where the participants compete for individual score, such as a cutting tournament.</li>
<li><u>Meta Event</u> - A tournament which doesn't have any results of it's own, but calculates based on the placings in other events.</li>

<BR>
<strong>Doubles/Afterblows</strong> specifies the afterblow model that will be used in scoring. 
<ul>
<li><u>No Afterblow</u> allows for only points to be awarded to one fighter, or a double hit to be awarded. </li>
<li><u>Deductive Afterblow</u> allows for afterblows that deduct points from an initial attack. Both the original </li>
score and the value deducted by the afterblow are entered by the scorekeeper.
<li><u>Full Afterblow</u> is for rule sets that score the points from the afterblow and original attack equally.</li>
</ul>

<strong>Ranking Algorithm</strong> determines how fighters will be ranked based on the performance in their pools. Should you wish 
to use a algorithm not listed please contact us to code it in for you.
<BR>*note* When calculating pool results all pools will be normalized to the same size. By default this size 
is 4, but can be changed in <strong><a href='adminTournaments.php'>Manage Event > Tournament Settings</a></strong>.

<a name='editTournaments'></a><BR><BR><h5>Editing Tournaments</h5>
To edit the details of a tournament you have created you go to
<strong><a href='adminTournaments.php'>Manage Event > Tournament Settings</a></strong>. This shows you a list of all tournaments
you have created. Click on <strong>Edit Details</strong> to show the tournament details, and make any changes.
<BR><u>Normalize Pool Size</u> is the 'average' pool size you plan on having. 
HEMA Scorecard will automatically normalize pool sizes so that there is no 
unfairness between fighters fighting in pools of different sizes. The normalization size changes the
pool size that participants are scaled to. A size of '4' would scale the results of all fighters in 
5 person pools down to the equivalent of a 4 person pool. A size of '5' would scale up all the results of
fighters in 4 person pools.


<BR><BR><a href='#topOfPage'>Back to Top</a>
</fieldset>


<!-- Adding Participants --------------------------------------------->
<a name='addParticipants'></a>
<fieldset class='fieldset'>
<legend><h4>Adding Event Participants</h4></legend>

Participants are added to the event in <strong><a href='participantsEvent.php'>Manage Fighters > Event Participants</a></strong>. 
<em>Remember, you can't do this unless logged in as an Event Organizer</em>
<BR><BR>
On the Event Roster page there is a button labeled <strong>Add Event Participants</strong>.
This will open a form to add new participants. 
<ol>
<li>First select the school/club from the drop down menu, or <em>Unknown</em> if the school/club is unknown.</li>
<li>Select fighter(s) from the drop down menu
<ul><li>If a fighter is not in the drop down menu it means they have not participated in a HEMA Scorecard event before. 
Enter their first and last name in the text entry fields provided.<BR>
HEMA Scorecard uses the same fighter database across multiple events and it is extremely important that fighter information
is entered accurately. Please use a fighter's proper name and ensure the spelling is accurate
(nicknames are fun, but this isn't the place).</li></ul></li>
<li>Select which tournaments the participant is competing in. This can always be changed at a later date.</li>
<li>Click <strong>Add New Participants</strong> to add the participants to the system</li>
<li>Once you are done, click <strong>Done Adding Participants</strong> to hide the form.</li>
</ol>
<h5>Adding New Schools</h5>
To add a new school click on the <strong>Add Schools</strong> button next to <strong>Add/Remove Event Participants</strong>. 
Please enter the information as accurately as possible, as future events will use the information you enter for this school.
<ul><li><em>Currently event organizers can not edit a school's information. 
If there are changes which need to be made please bring them to the attention of the HEMA Scorecard team.</em></li></ul>

<BR><a href='#topOfPage'>Back to Top</a>
</fieldset>


<!-- Setting Up Tournaments --------------------------------------------->
<a name='setupTournaments'></a>
<fieldset class='fieldset'>
<legend><h4>Setting Up Your Tournaments</h4></legend>

Once a tournament is created and fighters have been added to an event it is necessary to set up each tournament.
<p>Remember to make sure that you are entering information for the correct tournament!</p>
<h5>Enter Fighters In The Tournament</h5>
There are three ways to enter a fighter in the tournament. 

<li>Enter the fighter in the tournament when you enter them in the event.</li>
<li>Enter fighters in a tournament by using <strong><a href='participantsTournament.php'>Tournament Roster</a></strong> 
in the lower navigational bar.
This allows you to enter/remove multiple fighters at the same time.
<em>Note: This option will not appear unless you have selected a tournament.</em></li>
<li>Manually change the tournaments a fighter is assigned to by clicking <strong>Edit</strong> on the 
<strong><a href='participantsEvent.php'>Event Participants</a></strong> page. A form will open to change a fighters name, school, or tournament
registrations.</li>

<a name='createPools'></a>
<BR>
<h5>Create Pools</h5>
If your tournament has pools, the next step is to create them. This is 
performed under <strong><a href='poolRosters.php'>Pool Rosters</a></strong> in the lower navigational bar. This is also where you can create multiple pool sets, or successive stages of pools.
<BR>
You will be greeted with 'No Pools Created', and the option to create more pools located at the bottom of the screen. 
Create as many pools as required. At this point the pools will be empty, 
and you will have the option to populate them with fighters entered in the tournament. 
As you enter fighters they will be removed from the list of eligible fighters.
<BR><BR>
<u>Larger Pools:</u> If you wish to create a pool size larger than what is shown you will need to increase
the maximum pool size. <a href='#editTournaments'>Read More</a><BR>
<u>Change Pool Order:</u> To re-order pool number click <strong>Re-Order Pools</strong>. This will temporarily 
lock pool rosters from being changed, and give the option to change the pool numbers. 
Click <strong>Done</strong> to save these changes, or <strong>Cancel</strong> to discard.

<BR><a name='createPoolSets'></a>
<BR>
<h5>Pool Sets</h5>
If your tournament has Pool Sets (people fighting in multiple rounds of pools) you also create these in <strong><a href='poolRosters.php'>Pool Rosters</a></strong>. In the bottom right of the Pool Management box you will see an option to <strong>Manage Pool Sets</strong>. You can then increase the number of Pool Sets your tournament uses.


<BR><a name='createBrackets'></a><BR>
<h5>Creating/Deleting Brackets</h5>
Click on <strong><a href='finalsBracket.php'>Finals Bracket</a></strong> in
the lower navigational bar. This will give you the option to create a new
bracket. Enter in the desired bracket size in the field <strong>Number of Fighters</strong>

<BR><BR><strong>Double Elimination</strong> - To make a double elimination bracket enter a number greater 
than 4 in the lower text field. This will create a winners bracket and a consolation bracket.
The navigation options to the bracket will also change to <strong>Winners Bracket</strong> and
<strong>Consolation Bracket</strong>. 
<ul><li><u>Example:</u> Double Elimination bracket for 16 fighters. Enter 16 in both fields.</li>
<li><u>Example:</u> 16 person bracket with single elimination for first round and double elimination after that. 
Enter 16 for number of fighters and top 8 for the double elim size.</li></ul>
<i>(Technically the bronze medal match of a regular tournament is a double elimination bracket 
for only the top 4.)</i>

<BR><strong>Deleting a Bracket</strong> - To delete a bracket use the 
delete bracket button at the bottom of the winners bracket page.


<BR><a name='createRounds'></a><BR>
<h5>Create Rounds</h5>

For scored events, such as cutting tournaments, you will need to create rounds.
This is done using <strong><a href='roundRosters.php'>Round Rosters</a></strong> in the lower navigational menu.
<BR>
You will be greeted with 'No Rounds Created', and the option to create more rounds.
Create as many rounds as your tournament requires, and use the selection boxes to
add all fighters to the round.<BR>
After a fighter has completed their round they will be eligible to advance to the next round and appear in the selection boxes. 
Additionally fighters in the drop down list will be sorted by score in the previous rounds.

<BR><BR><a href='#topOfPage'>Back to Top</a>
</fieldset>


<!-- Withdraw Fighters --------------------------------------------->
<a name='withdrawingFighters'></a>
<fieldset class='fieldset'>
<legend><h4>Injury / Disqualification</h4></legend>

<h5>Withdrawing Fighters</h5>
Fighters can be added/removed from a tournament at any time prior to their matches.
However if a fighter has already begun participating they <i>should not</i> be removed
from the tournament. If a fighter can not continue their pool or bracket
(eg. Injury or Disqualification) they need to be withdrawn.
To do this, go to 
<strong><a href='adminFighters.php'>Manage Fighters > Withdraw Fighters</a></strong>
in the upper navigation menu. <BR>This will display a list of all competitors with
toggle switches next to their names. Select the desired options and click 
<strong>Update List</strong> at the bottom of the page

<BR><BR><h5>Remove From Scoring</h5>
Use this option if a fighter has not completed all of their matches. This will make the 
matches 'not exist' for scoring calculations, but the results will still be viewable in
the match list.
<strong>Pool Sets</strong> - If running a Pool Sets based tournament make sure you are currently in the 
pool set from which they are being removed. This will remove them from the scoring for this set,
but not affect the scoring of previous sets.

<BR><BR><h5>Remove From Finals</h5>
If a fighter has completed all of their pool matches but can not participate in the elimination bracket
(such as being injured in another tournament) use the <strong>Remove From Finals</strong> toggle.
This will keep all pool matches, and standings, unaffected. The fighter will simply be removed from
the list of fighters in the bracket menu, and the seedings of the other fighters will be ajusted to reflect this.

<BR><BR><a href='#topOfPage'>Back to Top</a>
</fieldset>


<!-- Running Pools --------------------------------------------->
<a name='runningPools'></a>
<fieldset class='fieldset'>
<legend><h4>Running Pools</h4></legend>
If you didn't create your pools when you set up your tournament, 
you need to do so before you can fight any matches. <a href='#createPools'>More Info</a>

<BR><BR><h5>Select Matches</h5>
To see pool matches select <strong><a href='poolMatches.php'>Pool Matches</a></strong>
on the lower navigational bar. This will display all matches currently scheduled for the pools. Matches not yet completed are
displayed in a lighter color, and completed matches are fully shaded. Click on the match number 
to go to the match scoring page.

<BR><BR><h5>Pool Standings</h5>
Pool standings are viewed by selecting <strong><a href='poolStandings.php'>Pool Standings</a></strong>
from the lower navigational bar. This displays a list of fighters ranked by your chosen
ranking algorithm. Note that if not all matches have been completed the software will 
automatically scale the results to the <i>Normalize Pool Size</i>. The pool standings
are updated on the completion of a match.

<BR><BR><h5>Pool Sets</h5>
If running an event using pool sets (fighters fighting in multiple rounds of pools)
the available  sets will appear directly under the lower navigation bar. Information
for whichever pool is selected will be displayed.

<BR><BR><a href='#topOfPage'>Back to Top</a>
</fieldset>


<!-- Elimination Brackets --------------------------------------------->
<a name='runningBracket'></a>
<fieldset class='fieldset'>
<legend><h4>Running Elimination Brackets</h4></legend>
If you didn't create your brackets when you set up your tournament, 
you need to do so before you can fight any matches. <a href='#createBrackets'>More Info</a>

<BR><BR><h5>Populating a Bracket</h5>
If you haven't added any fighters the bracket will appear as an empty structure, 
with drop down menus of tournament fighters. If the event has had pools the fighters will be 
ranked in order of pool performance. From here create the desired bracket match ups and click 
<strong>Add Fighters</strong> at the top of the screen, or <strong>Add</strong> directly beside the match.
<ul><li><i>Note: If fighters have been pre-advanced and have not had their opponents determined it is also possible to 
add only one fighter to a match and add their opponent at a later time. </i></li></ul>

<strong>Removing Fighters</strong> - To remove fighters from a match
select the check box next to the match and click <strong>Clear Selected</strong>
at the top of the page.

<BR><BR><h5>Bracket Helper</h5>
A handy feature of HEMA Scorecard is the Bracket Helper. If the bracket helper is
enabled it will automatically make suggestions about pool seeding and advancements.
To enable click on <strong>Enable Bracket Helper</strong> at the top of the page.
If a bracket is empty it will suggest seeding based on pool results. (Don't try
this if you have a direct bracket tournament. It will seed based on the fighter's names!)
As the matches progress the Bracket Helper will then suggest the advancements 
though the bracket based on match winners.
<ul><li><u>Note:</u> The bracket helper will only make suggestions, it will never
add or remove fighters from the bracket. The tournament staff
must click the appropriate button to actually add fighters.</li></ul>


<BR><a href='#topOfPage'>Back to Top</a>
</fieldset>





<!-- Scoring Matches --------------------------------------------->
<a name='runningMatch'></a>
<fieldset class='fieldset'>
<legend><h4>Scoring Matches</h4></legend>

In scoring a match you are presented with the ability to enter values for the score of each fighter, and if
the deductive afterblow model is used to specify the value of the afterblow. The history of all exchanges in
the match are displayed at the bottom of the page.

<BR><BR><h5>Exchanges</h5>
Each exchange can be one of the following:
<ul>
<li><u>No Exchange:</u> If no item is selected the exchange will be 'No Exchange' and no score is assigned to either fighter.</li>
<li><u>Clean Hit:</u> Select a score for one of the fighters.</li>
<li><u>Afterblow:</u> When using deductive afterblows select a score for a fighter and select the afterblow value. 
	For full afterblow rules select scores for each of the fighters.</li>
<li><u>Double Hit:</u> Select the double hit switch if the exchange is double.</li>
<li><u>Penalty:</u> Selecting the penalty switch will change the scores to negative values to asses a fighter a score penalty.</li>
<li><u>Clear Last Exchange:</u> Removes the last exchange inputted.</li>
</ul>

<div class='callout alert'>
<h5 class='text-center'>ENTER ALL DATA IN THE SOFTWARE</h5>
Make sure to enter all no-scoring exchanges and no quality hits. 
If there is a hit with a value of 2 and an afterblow deduction of 1 
<u>do not</u> enter a clean hit of 1 point.<BR>
<i>You may not think this is important, but I do. 
Having good quality tournament data is the reason I put so much time 
into developing free software for you to use. :)</i>
</div>

<BR><h5>Concluding Matches</h5>
The buttons to conclude a match are located in the upper right corner of the page. 
Once a winner has been determined for the match select the appropriate button. 
If the fight has reached the maximum number of double hits a red <strong>Double Out</strong>
 button will appear, to conclude the match as a double loss.
<BR>Selecting <strong>Re-Open Match</strong> after a match has been concluded will re-open the match to the last recorded exchange.
<ul><li><u>Important:</u> You must make sure table staff know to conclude all matches. 
If a match is not concluded properly the scoring calculations will not 
function properly, and the Bracket Helper will not know which fighters to advance.</li></ul>

<BR><a href='#topOfPage'>Back to Top</a>
</fieldset>

<!-- Scored Events --------------------------------------------->
<a name='runningRounds'></a>
<fieldset class='fieldset'>
<legend><h4>Scored Events</h4></legend>
If you didn't create your rounds when you set up your tournament, 
you need to do so before you can fight any matches. <a href='#createRounds'>More Info</a>

<BR><BR><h5>Scoring Competitors</h5>
To begin to enter scores for competitors click on <strong><a href='roundMatches.php'>Round Matches</a></strong>
in the lower navigation menu. This will show all created rounds, and the competitors in each.
To score a competitor click on the button labeled <strong>Go</strong> next to their name.
<BR>This will open the scoring page. To begin with you must add cuts to the competitor. 
Use the <strong>Add More Cuts</strong> menu option to add as many cuts as desired. (More can be added at any time)

<BR><BR>Once the cuts have been added you must assign score deductions before the score is calculated. 
Enter the desired values, or leave the field blank for a no deduction. Then click <strong>Update</strong> to add the deductions.
<ul><li><u>Note:</u> The score is not calculated until deductions have been assigned. 
Adding cuts does not affect the score, and the round is still incomplete.</li></ul>

<strong>Modifying Deductions</strong> - To change a deduction simply change the score value and click <strong>Update</strong>.
Any cuts which have no new information entered will retain their original value.

<BR><BR><h5>Round Standings</h5>
To view the round standings click on <strong><a href='roundStandings.php'>Round Standings</a></strong> 
in the lower navigation menu. This will display a ranked list of the competitors from each round, 
and the competitors which have not yet completed the round.

<BR><BR><h5>Round Advancements</h5>
To add competitors to the next round click on <strong><a href='roundRosters.php'>Round Standings</a></strong> 
in the lower navigation menu. As a competitor completes the previous round they will appear in
the selection options to be added to the next round, sorted by score.

<BR><BR><a href='#topOfPage'>Back to Top</a>
</fieldset>

<?php
}


/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
