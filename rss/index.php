<?php
/*******************************************************************************
	
		
*******************************************************************************/

// INITIALIZATION //////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////



include_once('../includes/config.php');

$sql = "SELECT eventID, eventName, eventYear, eventStartDate, eventCity, eventProvince, countryName, countryIso2
		FROM systemEvents
		INNER JOIN systemCountries USING(countryIso2)
		LEFT JOIN eventPublication USING(eventID)
		WHERE isArchived = 0
		AND (publishDescription = 1)
		ORDER BY eventStartDate ASC";
$eventList = mysqlQuery($sql, ASSOC);

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>


<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title>HEMA Scorecard</title>
<link>https://hemascorecard.com</link>
<description>The world's most popular HEMA tournament management software. (And it's also free.)</description>
<language>en-us</language>
<atom:link href="https://hemascorecard.com/rss/index.php" rel="self" type="application/rss+xml" />
<image>
	<url>https://hemascorecard.com/includes/images/logo_square.jpg</url>
	<title>HEMA Scorecard</title>
	<link>https://hemascorecard.com</link>
	<height>144</height>
  <width>144</width>
</image>

<?php foreach($eventList as $event): 
	$pubDate= date("D, d M Y H:i:s T", strtotime($event['eventStartDate']));
	?>

<item>
  <title><?=$event['eventName']?> <?=$event['eventYear']?></title>

  <guid>https://hemascorecard.com/infoSummary.php?e=<?=$event['eventID']?></guid>
  <link>https://hemascorecard.com/infoSummary.php?e=<?=$event['eventID']?></link>
  <description><?=$event['eventStartDate']?> in <?=$event['eventCity']?>, <?=$event['countryName']?></description>
</item>

<?php endforeach ?>

</channel>
</rss>

<?

/******************************************************************************/

// END OF DOCUMENT /////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
