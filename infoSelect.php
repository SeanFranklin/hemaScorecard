<?php
/*******************************************************************************
 * Event Selection
 *
 * Select which event to use
 * Login:
 * - SUPER ADMIN can see hidden events
 *******************************************************************************/

namespace Scorecard\InfoSelect;

use Scorecard\Query\GetActiveEvent;
use Scorecard\Query\GetHiddenEvents;
use Scorecard\Query\GetUpComingEvent;

$pageName = "Tournament Selection";
$hideEventNav = true;
$hidePageTitle = true;
include('includes/header.php');

$activeEventsRepo = new GetActiveEvent();
$upcomingEventsRepo = new GetUpComingEvent();
$hiddenEventsRepo = new GetHiddenEvents();
$archivedEventsRepo = new GetArchivedEvents();

// Get the event List
$activeEvents = $activeEventsRepo->all();
$upcomingEvents = $upcomingEventsRepo->all();
if (ALLOW['VIEW_HIDDEN']) {
    $hiddenEvents = $hiddenEventsRepo->all();
} else {
    $hiddenEvents = array();
}
$archivedEvents = $archivedEventsRepo->all();

?>

    <div class='grid-x grid-padding-x'>
        <div class='large-7 medium-10 small-12 cell' id='eventListContainer'>

            <h4 class='text-center'>Change Event</h4>

            <form method='POST'>
                <input type='hidden' name='formName' value='selectEvent'>
                <ul class='accordion' data-accordion data-allow-all-closed='true'>
                    <li class='accordion-item is-active' data-accordion-item>
                        <a class='accordion-title'>
                            <h4>Active Events</h4>
                        </a>
                        <div class='accordion-content' data-tab-content>

                            <!-- Hidden Events -->
                            <?php if (ALLOW['VIEW_HIDDEN'] && count($hiddenEvents) > 0): ?>
                                <h5>Hidden Events</h5>
                                <?php displayEventsInCategory($hiddenEvents); ?>
                            <?php endif ?>

                            <!-- Active Events -->
                            <?php if (count($activeEvents) > 0): ?>
                                <h5>Active Events</h5>
                                <?php displayEventsInCategory($activeEvents); ?>
                            <?php endif ?>

                            <!-- Upcoming Events -->
                            <?php if (count($upcomingEvents) > 0): ?>
                                <h5>Upcoming Events</h5>
                                <?php displayEventsInCategory($upcomingEvents); ?>
                            <?php endif ?>
                        </div>
                    </li>

                    <!-- Old Events -->
                    <?php displayArchivedEvents($archivedEvents); ?>

                </ul>
            </form>
        </div>
    </div>

    <?
include('includes/footer.php');

function displayArchivedEvents($year_buckets)
{
    foreach ($year_buckets as $year => $eventList) {
        echo "<li class='accordion-item' data-accordion-item>
            <a class='accordion-title' style='padding-top:10px; padding-bottom:1px'><h4>{$year} Events</h4></a>
            <div class='accordion-content' data-tab-content>";

        foreach ($eventList as $eventInfo) {
            $eventID = $eventInfo['eventID'];
            displayEventButton($eventID, $eventInfo);
        }
        echo "</div></li>";
    }
    echo "</div></li>";
}

/**********************************************************************/

function displayEventsInCategory($eventList)
{
    foreach ($eventList as $eventInfo) {
        $eventID = $eventInfo['eventID'];
        displayEventButton($eventID, $eventInfo);
    }
}
