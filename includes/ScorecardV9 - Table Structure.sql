-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 09, 2023 at 12:45 AM
-- Server version: 5.7.33-0ubuntu0.16.04.1
-- PHP Version: 7.0.33-0ubuntu0.16.04.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ScorecardV5`
--

-- --------------------------------------------------------

--
-- Table structure for table `eventAttacks`
--

CREATE TABLE `eventAttacks` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `attackPrefix` int(10) UNSIGNED DEFAULT NULL,
  `attackTarget` int(10) UNSIGNED DEFAULT NULL,
  `attackType` int(10) UNSIGNED DEFAULT NULL,
  `attackPoints` float NOT NULL DEFAULT '0',
  `attackNumber` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventAttributes`
--

CREATE TABLE `eventAttributes` (
  `attributeID` int(10) UNSIGNED NOT NULL,
  `attributeBool` tinyint(1) DEFAULT NULL,
  `attributeText` text,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `attributeType` varchar(255) NOT NULL,
  `attributeValue` float DEFAULT NULL,
  `attributeGroupSet` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventBurgeeComponents`
--

CREATE TABLE `eventBurgeeComponents` (
  `burgeeComponentID` int(10) UNSIGNED NOT NULL,
  `burgeeID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventBurgeePlacings`
--

CREATE TABLE `eventBurgeePlacings` (
  `burgeePlaceID` int(10) UNSIGNED NOT NULL,
  `burgeeID` int(10) UNSIGNED NOT NULL,
  `schoolID` int(10) UNSIGNED NOT NULL,
  `rosterID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(11) UNSIGNED NOT NULL,
  `burgeePoints` int(11) DEFAULT '0',
  `placingName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventBurgees`
--

CREATE TABLE `eventBurgees` (
  `burgeeID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `burgeeRankingID` int(10) UNSIGNED NOT NULL,
  `burgeeName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventCutStandards`
--

CREATE TABLE `eventCutStandards` (
  `qualID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `standardID` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `qualValue` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventDefaults`
--

CREATE TABLE `eventDefaults` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `color1ID` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `color2ID` int(10) UNSIGNED NOT NULL DEFAULT '2',
  `maxPoolSize` int(10) UNSIGNED NOT NULL DEFAULT '5',
  `maxDoubleHits` int(10) UNSIGNED NOT NULL DEFAULT '3',
  `normalizePoolSize` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `allowTies` tinyint(1) NOT NULL DEFAULT '0',
  `nameDisplay` varchar(255) NOT NULL DEFAULT 'firstName',
  `tournamentDisplay` varchar(255) NOT NULL DEFAULT 'weapon',
  `tournamentSorting` varchar(255) NOT NULL DEFAULT 'numGrouped',
  `useControlPoint` int(11) NOT NULL DEFAULT '0',
  `staffCompetency` int(11) NOT NULL DEFAULT '0',
  `addStaff` tinyint(1) NOT NULL DEFAULT '0',
  `staffHoursTarget` int(11) NOT NULL DEFAULT '0',
  `limitStaffConflicts` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventDescriptions`
--

CREATE TABLE `eventDescriptions` (
  `eventDescriptionID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventExchanges`
--

CREATE TABLE `eventExchanges` (
  `exchangeID` int(10) UNSIGNED NOT NULL,
  `matchID` int(10) UNSIGNED DEFAULT NULL,
  `exchangeType` varchar(255) NOT NULL,
  `scoringID` int(10) UNSIGNED DEFAULT NULL,
  `receivingID` int(10) UNSIGNED DEFAULT NULL,
  `scoreValue` float DEFAULT NULL,
  `scoreDeduction` float DEFAULT NULL,
  `exchangeNumber` int(11) NOT NULL DEFAULT '0',
  `exchangeTime` int(11) DEFAULT NULL,
  `refPrefix` int(10) UNSIGNED DEFAULT NULL,
  `refTarget` int(10) UNSIGNED DEFAULT NULL,
  `refType` int(10) UNSIGNED DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventGroupRankings`
--

CREATE TABLE `eventGroupRankings` (
  `groupRankingID` int(10) UNSIGNED NOT NULL,
  `groupID` int(10) UNSIGNED NOT NULL,
  `groupRank` int(11) NOT NULL,
  `overlapSize` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventGroupRoster`
--

CREATE TABLE `eventGroupRoster` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `groupID` int(10) UNSIGNED DEFAULT NULL,
  `rosterID` int(10) UNSIGNED DEFAULT NULL,
  `poolPosition` int(10) UNSIGNED DEFAULT NULL,
  `participantStatus` varchar(255) DEFAULT 'normal',
  `tournamentTableID` int(10) UNSIGNED DEFAULT NULL,
  `groupCheckIn` tinyint(1) NOT NULL DEFAULT '0',
  `groupGearCheck` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventGroups`
--

CREATE TABLE `eventGroups` (
  `groupID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `groupType` varchar(255) NOT NULL,
  `groupNumber` int(11) DEFAULT NULL,
  `groupName` varchar(255) DEFAULT NULL,
  `groupSet` int(11) NOT NULL DEFAULT '1',
  `bracketLevels` tinyint(4) DEFAULT NULL,
  `numFighters` int(10) UNSIGNED DEFAULT NULL,
  `groupStatus` varchar(255) DEFAULT NULL,
  `groupComplete` tinyint(1) NOT NULL DEFAULT '0',
  `locationID` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventHemaRatingsInfo`
--

CREATE TABLE `eventHemaRatingsInfo` (
  `hemaRatingInfoID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `organizingSchool` int(10) UNSIGNED DEFAULT NULL,
  `socialMediaLink` text,
  `photoLink` text,
  `submitterName` varchar(255) DEFAULT NULL,
  `submitterEmail` varchar(255) DEFAULT NULL,
  `organizerName` varchar(255) DEFAULT NULL,
  `eventConform` tinyint(1) DEFAULT NULL,
  `allMatchesFought` tinyint(1) DEFAULT NULL,
  `missingMatches` tinyint(1) DEFAULT NULL,
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventIgnores`
--

CREATE TABLE `eventIgnores` (
  `ignoreID` int(11) NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `rosterID` int(10) UNSIGNED NOT NULL,
  `ignoreAtSet` int(11) NOT NULL DEFAULT '0',
  `stopAtSet` int(11) NOT NULL DEFAULT '0',
  `soloAtSet` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventMatches`
--

CREATE TABLE `eventMatches` (
  `matchID` int(10) UNSIGNED NOT NULL,
  `groupID` int(10) UNSIGNED DEFAULT NULL,
  `matchNumber` int(10) UNSIGNED DEFAULT NULL,
  `fighter1ID` int(10) UNSIGNED DEFAULT NULL,
  `fighter2ID` int(10) UNSIGNED DEFAULT NULL,
  `winnerID` int(10) UNSIGNED DEFAULT NULL,
  `fighter1Score` float DEFAULT NULL,
  `fighter2Score` float DEFAULT NULL,
  `bracketPosition` int(10) UNSIGNED DEFAULT NULL,
  `bracketLevel` int(10) UNSIGNED DEFAULT NULL,
  `matchComplete` tinyint(1) DEFAULT '0',
  `signOff1` tinyint(1) NOT NULL DEFAULT '0',
  `signOff2` tinyint(1) NOT NULL DEFAULT '0',
  `ignoreMatch` tinyint(1) DEFAULT '0',
  `reversedColors` tinyint(1) NOT NULL DEFAULT '0',
  `matchTime` int(11) DEFAULT NULL,
  `isPlaceholder` tinyint(1) NOT NULL DEFAULT '0',
  `placeholderMatchID` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventMatchOptions`
--

CREATE TABLE `eventMatchOptions` (
  `matchOptionID` int(10) UNSIGNED NOT NULL,
  `matchID` int(10) UNSIGNED NOT NULL,
  `optionID` int(10) UNSIGNED NOT NULL,
  `optionValue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventPlacings`
--

CREATE TABLE `eventPlacings` (
  `placeID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `rosterID` int(10) UNSIGNED NOT NULL,
  `placing` int(11) NOT NULL,
  `highBound` int(11) DEFAULT NULL,
  `lowBound` int(11) DEFAULT NULL,
  `placeType` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventPublication`
--

CREATE TABLE `eventPublication` (
  `publicationID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `publishDescription` tinyint(1) NOT NULL DEFAULT '0',
  `publishRoster` tinyint(1) NOT NULL DEFAULT '0',
  `publishSchedule` tinyint(1) NOT NULL DEFAULT '0',
  `publishMatches` tinyint(1) NOT NULL DEFAULT '0',
  `publishRules` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventRatings`
--

CREATE TABLE `eventRatings` (
  `ratingID` int(10) UNSIGNED NOT NULL,
  `tournamentRosterID` int(10) UNSIGNED NOT NULL,
  `rating` int(11) NOT NULL DEFAULT '0',
  `subGroupNum` int(11) NOT NULL DEFAULT '0',
  `rating2` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventRoster`
--

CREATE TABLE `eventRoster` (
  `rosterID` int(10) UNSIGNED NOT NULL,
  `systemRosterID` int(10) UNSIGNED DEFAULT NULL,
  `eventID` int(10) UNSIGNED DEFAULT NULL,
  `schoolID` int(10) UNSIGNED DEFAULT NULL,
  `publicNotes` text,
  `privateNotes` text,
  `isTeam` tinyint(1) NOT NULL DEFAULT '0',
  `eventCheckIn` tinyint(1) NOT NULL DEFAULT '0',
  `eventWaiver` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventRosterAdditional`
--

CREATE TABLE `eventRosterAdditional` (
  `additionalRosterID` int(10) UNSIGNED NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `registrationType` int(11) NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `eventWaiver` tinyint(1) NOT NULL DEFAULT '0',
  `eventCheckIn` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventRules`
--

CREATE TABLE `eventRules` (
  `rulesID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `rulesName` varchar(255) DEFAULT NULL,
  `rulesOrder` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `rulesText` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventRulesLinks`
--

CREATE TABLE `eventRulesLinks` (
  `rulesLinkID` int(10) UNSIGNED NOT NULL,
  `rulesID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventScoresheets`
--

CREATE TABLE `eventScoresheets` (
  `scoresheetID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentID` int(10) UNSIGNED DEFAULT NULL,
  `matchID` int(10) UNSIGNED DEFAULT NULL,
  `scoresheet` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventSettings`
--

CREATE TABLE `eventSettings` (
  `eventSettingID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `organizerEmail` varchar(255) DEFAULT NULL,
  `termsOfUseAccepted` tinyint(1) NOT NULL DEFAULT '0',
  `staffPassword` varchar(255) DEFAULT NULL,
  `organizerPassword` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventSponsors`
--

CREATE TABLE `eventSponsors` (
  `eventSponsorID` int(10) UNSIGNED NOT NULL,
  `sponsorID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `eventSponsorPercent` int(11) NOT NULL DEFAULT '100'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventStandings`
--

CREATE TABLE `eventStandings` (
  `standingID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED DEFAULT NULL,
  `groupID` int(10) UNSIGNED DEFAULT NULL,
  `rosterID` int(10) UNSIGNED DEFAULT NULL,
  `groupType` varchar(255) NOT NULL,
  `groupSet` int(11) NOT NULL DEFAULT '1',
  `normalized` tinyint(1) DEFAULT '0',
  `rank` int(11) DEFAULT NULL,
  `score` float DEFAULT '0',
  `matches` float DEFAULT '0',
  `wins` float DEFAULT '0',
  `losses` float DEFAULT '0',
  `ties` float NOT NULL DEFAULT '0',
  `pointsFor` float DEFAULT '0',
  `pointsAgainst` float DEFAULT '0',
  `hitsFor` float DEFAULT '0',
  `hitsAgainst` float DEFAULT '0',
  `afterblowsFor` float DEFAULT '0',
  `afterblowsAgainst` float DEFAULT '0',
  `doubles` float DEFAULT '0',
  `noExchanges` float DEFAULT '0',
  `AbsPointsFor` float DEFAULT '0',
  `AbsPointsAgainst` float DEFAULT '0',
  `AbsPointsAwarded` float NOT NULL DEFAULT '0',
  `numPenalties` float DEFAULT '0',
  `penaltiesAgainstOpponents` float DEFAULT '0',
  `penaltiesAgainst` float DEFAULT '0',
  `doubleOuts` float DEFAULT '0',
  `ignoreForBracket` tinyint(1) NOT NULL DEFAULT '0',
  `basePointValue` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTeamRoster`
--

CREATE TABLE `eventTeamRoster` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `teamID` int(10) UNSIGNED DEFAULT NULL,
  `rosterID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentRosterID` int(10) UNSIGNED DEFAULT NULL,
  `memberRole` varchar(255) NOT NULL DEFAULT 'member',
  `memberName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournamentCompGroupItems`
--

CREATE TABLE `eventTournamentCompGroupItems` (
  `componentGroupItemID` int(10) UNSIGNED NOT NULL,
  `componentGroupID` int(10) UNSIGNED NOT NULL,
  `tournamentComponentID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournamentCompGroups`
--

CREATE TABLE `eventTournamentCompGroups` (
  `componentGroupID` int(10) UNSIGNED NOT NULL,
  `metaTournamentID` int(10) UNSIGNED NOT NULL,
  `usedComponents` int(11) NOT NULL DEFAULT '0',
  `numComponents` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournamentComponents`
--

CREATE TABLE `eventTournamentComponents` (
  `tournamentComponentID` int(10) UNSIGNED NOT NULL,
  `metaTournamentID` int(10) UNSIGNED NOT NULL,
  `componentTournamentID` int(10) UNSIGNED NOT NULL,
  `useResult` tinyint(1) NOT NULL DEFAULT '0',
  `useRoster` tinyint(1) NOT NULL DEFAULT '0',
  `ignoreRoster` tinyint(1) NOT NULL DEFAULT '0',
  `resultsCalculated` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournamentOptions`
--

CREATE TABLE `eventTournamentOptions` (
  `tournamentOptionID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `optionID` int(10) UNSIGNED NOT NULL,
  `optionValue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournamentOrder`
--

CREATE TABLE `eventTournamentOrder` (
  `tournamentOrderID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `sortOrder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournamentRoster`
--

CREATE TABLE `eventTournamentRoster` (
  `tournamentRosterID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED DEFAULT NULL,
  `rosterID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentCheckIn` tinyint(1) NOT NULL DEFAULT '0',
  `tournamentGearCheck` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournaments`
--

CREATE TABLE `eventTournaments` (
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `tournamentWeaponID` int(10) UNSIGNED NOT NULL,
  `tournamentPrefixID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentGenderID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentMaterialID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentSuffixID` int(10) UNSIGNED DEFAULT NULL,
  `tournamentRankingID` int(10) UNSIGNED DEFAULT NULL,
  `doubleTypeID` int(10) UNSIGNED DEFAULT '2',
  `formatID` int(10) UNSIGNED DEFAULT '2',
  `numGroupSets` int(11) NOT NULL DEFAULT '1',
  `numParticipants` int(10) UNSIGNED DEFAULT '0',
  `normalizePoolSize` int(11) DEFAULT '0',
  `color1ID` int(10) UNSIGNED DEFAULT '1',
  `color2ID` int(10) UNSIGNED DEFAULT '2',
  `maxPoolSize` int(10) UNSIGNED NOT NULL DEFAULT '5',
  `maxDoubleHits` int(10) UNSIGNED NOT NULL DEFAULT '3',
  `maximumExchanges` int(11) DEFAULT NULL,
  `maximumPoints` int(11) DEFAULT NULL,
  `maxPointSpread` int(11) NOT NULL DEFAULT '0',
  `basePointValue` int(11) NOT NULL DEFAULT '0',
  `allowTies` tinyint(1) NOT NULL DEFAULT '0',
  `timerCountdown` tinyint(1) NOT NULL DEFAULT '0',
  `isCuttingQual` tinyint(1) NOT NULL DEFAULT '0',
  `isFinalized` tinyint(1) NOT NULL DEFAULT '0',
  `timeLimit` int(11) NOT NULL DEFAULT '0',
  `isNotNetScore` tinyint(1) NOT NULL DEFAULT '0',
  `isReverseScore` int(11) NOT NULL DEFAULT '0',
  `overrideDoubleType` tinyint(1) NOT NULL DEFAULT '0',
  `isPrivate` tinyint(1) NOT NULL DEFAULT '0',
  `isTeams` tinyint(1) NOT NULL DEFAULT '0',
  `logicMode` varchar(255) DEFAULT NULL,
  `poolWinnersFirst` int(11) NOT NULL DEFAULT '0',
  `limitPoolMatches` int(11) NOT NULL DEFAULT '0',
  `checkInStaff` int(11) NOT NULL DEFAULT '0',
  `hideFinalResults` tinyint(1) NOT NULL DEFAULT '0',
  `numSubMatches` int(11) NOT NULL DEFAULT '0',
  `subMatchMode` int(11) NOT NULL DEFAULT '0',
  `requireSignOff` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventVideo`
--

CREATE TABLE `eventVideo` (
  `videoID` int(10) UNSIGNED NOT NULL,
  `videoType` int(11) NOT NULL,
  `sourceType` int(11) NOT NULL,
  `sourceLink` varchar(255) DEFAULT NULL,
  `matchID` int(11) UNSIGNED DEFAULT NULL,
  `synchTime` int(11) DEFAULT NULL,
  `synchTime2` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventVideoStreams`
--

CREATE TABLE `eventVideoStreams` (
  `streamID` int(10) UNSIGNED NOT NULL,
  `videoID` int(10) UNSIGNED DEFAULT NULL,
  `locationID` int(10) UNSIGNED DEFAULT NULL,
  `isLive` tinyint(1) NOT NULL DEFAULT '0',
  `overlayEnabled` tinyint(1) NOT NULL DEFAULT '1',
  `overlayOpacity` int(11) NOT NULL DEFAULT '70'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsAnnouncements`
--

CREATE TABLE `logisticsAnnouncements` (
  `announcementID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED DEFAULT NULL,
  `message` text NOT NULL,
  `displayUntil` int(32) UNSIGNED NOT NULL,
  `visibility` varchar(20) NOT NULL DEFAULT 'all'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsBlockAttributes`
--

CREATE TABLE `logisticsBlockAttributes` (
  `blockAttributeID` int(10) UNSIGNED NOT NULL,
  `blockID` int(10) UNSIGNED NOT NULL,
  `blockAttributeType` varchar(255) DEFAULT NULL,
  `blockAttributeText` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsLocations`
--

CREATE TABLE `logisticsLocations` (
  `locationID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `locationName` varchar(255) NOT NULL,
  `hasMatches` tinyint(1) NOT NULL DEFAULT '1',
  `hasClasses` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsLocationsBlocks`
--

CREATE TABLE `logisticsLocationsBlocks` (
  `blockLocationID` int(10) UNSIGNED NOT NULL,
  `blockID` int(10) UNSIGNED NOT NULL,
  `locationID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsLocationsMatches`
--

CREATE TABLE `logisticsLocationsMatches` (
  `matchLocationID` int(10) UNSIGNED NOT NULL,
  `locationID` int(10) UNSIGNED DEFAULT NULL,
  `matchID` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsRoleCompetency`
--

CREATE TABLE `logisticsRoleCompetency` (
  `roleCompetencyID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `logisticsRoleID` int(10) UNSIGNED NOT NULL,
  `roleCompetency` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsScheduleBlocks`
--

CREATE TABLE `logisticsScheduleBlocks` (
  `blockID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `dayNum` int(11) NOT NULL,
  `startTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL,
  `blockTypeID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED DEFAULT NULL,
  `blockTitle` varchar(255) DEFAULT NULL,
  `blockSubtitle` varchar(255) DEFAULT NULL,
  `blockDescription` text,
  `blockLink` text,
  `blockLinkDescription` varchar(255) DEFAULT NULL,
  `suppressConflicts` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsScheduleShifts`
--

CREATE TABLE `logisticsScheduleShifts` (
  `shiftID` int(10) UNSIGNED NOT NULL,
  `blockID` int(10) UNSIGNED NOT NULL,
  `locationID` int(10) UNSIGNED NOT NULL,
  `startTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffCompetency`
--

CREATE TABLE `logisticsStaffCompetency` (
  `staffCompetencyID` int(10) UNSIGNED NOT NULL,
  `rosterID` int(10) UNSIGNED NOT NULL,
  `staffCompetency` int(11) NOT NULL DEFAULT '0',
  `staffHoursTarget` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffMatches`
--

CREATE TABLE `logisticsStaffMatches` (
  `matchStaffID` int(10) UNSIGNED NOT NULL,
  `matchID` int(10) UNSIGNED NOT NULL,
  `rosterID` int(10) UNSIGNED NOT NULL,
  `logisticsRoleID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffMatchMultipliers`
--

CREATE TABLE `logisticsStaffMatchMultipliers` (
  `matchMultiplierID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL,
  `logisticsRoleID` int(10) UNSIGNED NOT NULL,
  `matchMultiplier` float NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffShifts`
--

CREATE TABLE `logisticsStaffShifts` (
  `staffShiftID` int(10) UNSIGNED NOT NULL,
  `shiftID` int(10) UNSIGNED NOT NULL,
  `rosterID` int(10) UNSIGNED NOT NULL,
  `logisticsRoleID` int(10) UNSIGNED DEFAULT NULL,
  `checkedIn` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logisticsStaffTemplates`
--

CREATE TABLE `logisticsStaffTemplates` (
  `staffTemplateID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `logisticsRoleID` int(10) UNSIGNED NOT NULL,
  `numStaff` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemAttacks`
--

CREATE TABLE `systemAttacks` (
  `attackID` int(10) UNSIGNED NOT NULL,
  `attackClass` varchar(255) NOT NULL,
  `attackCode` varchar(255) NOT NULL,
  `attackText` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemAttacks`
--

INSERT INTO `systemAttacks` (`attackID`, `attackClass`, `attackCode`, `attackText`) VALUES
(1, 'target', 'head', 'Head'),
(2, 'target', 'torso', 'Torso'),
(3, 'target', 'arm', 'Arm'),
(4, 'target', 'leg', 'Leg'),
(5, 'type', 'cut', 'Cut'),
(6, 'type', 'thrust', 'Thrust'),
(7, 'type', 'throw', 'Throw'),
(8, 'type', 'slice', 'Slice'),
(9, 'prefix', 'control', 'Controlled'),
(10, 'type', 'throw', 'Throw'),
(11, 'type', 'pommel', 'Pommel'),
(12, 'prefix', 'wDouble', 'Weighted Double'),
(13, 'prefix', 'afterblow', 'Afterblow'),
(14, 'type', 'ringOut', 'Ring Out'),
(15, 'type', 'buckler', 'Buckler Punch'),
(16, 'prefix', 'open', 'Open'),
(17, 'prefix', 'closed', 'Closed'),
(18, 'type', 'double', 'Double'),
(19, 'target', 'hand', 'Hand'),
(20, 'type', 'disarm', 'Disarm'),
(21, 'target', 'unarmored', 'Unarmored'),
(22, 'target', 'lightArmor', 'Light Armor'),
(23, 'type', 'mord', 'Mordschlag'),
(24, 'type', 'touchThrow', 'Touch-Throw'),
(25, 'type', 'groundControl', 'Ground Control'),
(26, 'type', 'standingThrow', 'Standing Throw'),
(27, 'type', 'advantage', 'Advantage'),
(28, 'type', 'shallowChain', 'Shallow Chain'),
(29, 'type', 'deepChain', 'Deep Chain'),
(30, 'type', 'pommelChain', 'Pommel Chain'),
(31, 'type', 'autoWin', 'Auto-Win'),
(32, 'target', 'deep', 'Deep'),
(33, 'target', 'shallow', 'Shallow'),
(34, 'penalty', 'yellowCard', 'Yellow Card'),
(35, 'penalty', 'redCard', 'Red Card'),
(36, 'illegalAction', 'backOfHead', 'Strike to the back of the head'),
(37, 'illegalAction', 'excessiveForce', 'Excessive force / Lack of control'),
(38, 'penalty', 'blackCard', 'Black Card'),
(39, 'illegalAction', 'offTarget', 'Off target attack'),
(40, 'illegalAction', 'unsportsmanlike ', 'Unsportsmanlike like conduct'),
(41, 'illegalAction', 'noHold', 'Continuing after hold'),
(42, 'illegalAction', 'weaponDisparity', 'Weapon disparity'),
(43, 'illegalAction', 'ringOut', 'Ring Out'),
(44, 'prefix', 'attack', 'Attack'),
(45, 'prefix', 'counterattack', 'Counterattack'),
(46, 'prefix', 'parry_riposte', 'Parry-Riposte'),
(47, 'prefix', 'renewal', 'Renewal'),
(48, 'prefix', '1h', 'One Handed'),
(49, 'illegalAction', 'floor', 'Hitting the Floor'),
(50, 'target', 'deepHigh', 'Deep & High'),
(51, 'prefix', 'offhan', 'Off Hand'),
(52, 'illegalAction', 'exposeBack', 'Expose Back'),
(53, 'illegalAction', 'unspecified', 'Unspecified'),
(56, 'illegalAction', 'score', 'Score Reduction'),
(57, 'target', 'limb', 'Limb'),
(58, 'illegalAction', 'slow', 'Unprepared When Match Is Called');

-- --------------------------------------------------------

--
-- Table structure for table `systemBlockTypes`
--

CREATE TABLE `systemBlockTypes` (
  `blockTypeID` int(10) UNSIGNED NOT NULL,
  `typeName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemBlockTypes`
--

INSERT INTO `systemBlockTypes` (`blockTypeID`, `typeName`) VALUES
(1, 'Tournament'),
(2, 'Class'),
(3, 'Staffing'),
(4, 'Misc');

-- --------------------------------------------------------

--
-- Table structure for table `systemBurgees`
--

CREATE TABLE `systemBurgees` (
  `burgeeRankingID` int(10) UNSIGNED NOT NULL,
  `rankingName` varchar(255) NOT NULL,
  `functionName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemBurgees`
--

INSERT INTO `systemBurgees` (`burgeeRankingID`, `rankingName`, `functionName`) VALUES
(1, 'Num In Top 4', 'NumInTop4'),
(2, 'Finalists And Bracket', 'rankingPerEvent'),
(3, 'Finalist Points', 'FinalistPoints');

-- --------------------------------------------------------

--
-- Table structure for table `systemColors`
--

CREATE TABLE `systemColors` (
  `colorID` int(10) UNSIGNED NOT NULL,
  `colorName` varchar(255) NOT NULL,
  `colorCode` varchar(255) NOT NULL,
  `contrastCode` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemColors`
--

INSERT INTO `systemColors` (`colorID`, `colorName`, `colorCode`, `contrastCode`) VALUES
(1, 'BLACK', '#778899', '#FFFFFF'),
(2, 'GOLD', '#E7B923', '#000000'),
(3, 'RED', '#EB5757', '#FFFFFF'),
(4, 'BLUE', '#1C6CD8', '#FFFFFF'),
(5, 'WHITE', '#FFF', '#000000'),
(6, 'GREEN', '#3CB371', '#FFFFFF'),
(7, 'PURPLE', '#593F98', '#FFFFFF');

-- --------------------------------------------------------

--
-- Table structure for table `systemCountries`
--

CREATE TABLE `systemCountries` (
  `countryIso2` varchar(2) NOT NULL,
  `countryTitle` varchar(80) NOT NULL,
  `countryName` varchar(80) NOT NULL,
  `countryIso3` char(3) DEFAULT NULL,
  `countryNumCode` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemCountries`
--

INSERT INTO `systemCountries` (`countryIso2`, `countryTitle`, `countryName`, `countryIso3`, `countryNumCode`) VALUES
('AD', 'ANDORRA', 'Andorra', 'AND', 20),
('AE', 'UNITED ARAB EMIRATES', 'United Arab Emirates', 'ARE', 784),
('AF', 'AFGHANISTAN', 'Afghanistan', 'AFG', 4),
('AG', 'ANTIGUA AND BARBUDA', 'Antigua and Barbuda', 'ATG', 28),
('AI', 'ANGUILLA', 'Anguilla', 'AIA', 660),
('AL', 'ALBANIA', 'Albania', 'ALB', 8),
('AM', 'ARMENIA', 'Armenia', 'ARM', 51),
('AN', 'NETHERLANDS ANTILLES', 'Netherlands Antilles', 'ANT', 530),
('AO', 'ANGOLA', 'Angola', 'AGO', 24),
('AQ', 'ANTARCTICA', 'Antarctica', NULL, NULL),
('AR', 'ARGENTINA', 'Argentina', 'ARG', 32),
('AS', 'AMERICAN SAMOA', 'American Samoa', 'ASM', 16),
('AT', 'AUSTRIA', 'Austria', 'AUT', 40),
('AU', 'AUSTRALIA', 'Australia', 'AUS', 36),
('AW', 'ARUBA', 'Aruba', 'ABW', 533),
('AZ', 'AZERBAIJAN', 'Azerbaijan', 'AZE', 31),
('BA', 'BOSNIA AND HERZEGOVINA', 'Bosnia and Herzegovina', 'BIH', 70),
('BB', 'BARBADOS', 'Barbados', 'BRB', 52),
('BD', 'BANGLADESH', 'Bangladesh', 'BGD', 50),
('BE', 'BELGIUM', 'Belgium', 'BEL', 56),
('BF', 'BURKINA FASO', 'Burkina Faso', 'BFA', 854),
('BG', 'BULGARIA', 'Bulgaria', 'BGR', 100),
('BH', 'BAHRAIN', 'Bahrain', 'BHR', 48),
('BI', 'BURUNDI', 'Burundi', 'BDI', 108),
('BJ', 'BENIN', 'Benin', 'BEN', 204),
('BM', 'BERMUDA', 'Bermuda', 'BMU', 60),
('BN', 'BRUNEI DARUSSALAM', 'Brunei Darussalam', 'BRN', 96),
('BO', 'BOLIVIA', 'Bolivia', 'BOL', 68),
('BR', 'BRAZIL', 'Brazil', 'BRA', 76),
('BS', 'BAHAMAS', 'Bahamas', 'BHS', 44),
('BT', 'BHUTAN', 'Bhutan', 'BTN', 64),
('BV', 'BOUVET ISLAND', 'Bouvet Island', NULL, NULL),
('BW', 'BOTSWANA', 'Botswana', 'BWA', 72),
('BY', 'BELARUS', 'Belarus', 'BLR', 112),
('BZ', 'BELIZE', 'Belize', 'BLZ', 84),
('CA', 'CANADA', 'Canada', 'CAN', 124),
('CC', 'COCOS (KEELING) ISLANDS', 'Cocos (Keeling) Islands', NULL, NULL),
('CD', 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'Congo, the Democratic Republic of the', 'COD', 180),
('CF', 'CENTRAL AFRICAN REPUBLIC', 'Central African Republic', 'CAF', 140),
('CG', 'CONGO', 'Congo', 'COG', 178),
('CH', 'SWITZERLAND', 'Switzerland', 'CHE', 756),
('CI', 'COTE D\'IVOIRE', 'Cote D\'Ivoire', 'CIV', 384),
('CK', 'COOK ISLANDS', 'Cook Islands', 'COK', 184),
('CL', 'CHILE', 'Chile', 'CHL', 152),
('CM', 'CAMEROON', 'Cameroon', 'CMR', 120),
('CN', 'CHINA', 'China', 'CHN', 156),
('CO', 'COLOMBIA', 'Colombia', 'COL', 170),
('CR', 'COSTA RICA', 'Costa Rica', 'CRI', 188),
('CS', 'SERBIA AND MONTENEGRO', 'Serbia and Montenegro', NULL, NULL),
('CU', 'CUBA', 'Cuba', 'CUB', 192),
('CV', 'CAPE VERDE', 'Cape Verde', 'CPV', 132),
('CX', 'CHRISTMAS ISLAND', 'Christmas Island', NULL, NULL),
('CY', 'CYPRUS', 'Cyprus', 'CYP', 196),
('CZ', 'CZECH REPUBLIC', 'Czech Republic', 'CZE', 203),
('DE', 'GERMANY', 'Germany', 'DEU', 276),
('DJ', 'DJIBOUTI', 'Djibouti', 'DJI', 262),
('DK', 'DENMARK', 'Denmark', 'DNK', 208),
('DM', 'DOMINICA', 'Dominica', 'DMA', 212),
('DO', 'DOMINICAN REPUBLIC', 'Dominican Republic', 'DOM', 214),
('DZ', 'ALGERIA', 'Algeria', 'DZA', 12),
('EC', 'ECUADOR', 'Ecuador', 'ECU', 218),
('EE', 'ESTONIA', 'Estonia', 'EST', 233),
('EG', 'EGYPT', 'Egypt', 'EGY', 818),
('EH', 'WESTERN SAHARA', 'Western Sahara', 'ESH', 732),
('ER', 'ERITREA', 'Eritrea', 'ERI', 232),
('ES', 'SPAIN', 'Spain', 'ESP', 724),
('ET', 'ETHIOPIA', 'Ethiopia', 'ETH', 231),
('FI', 'FINLAND', 'Finland', 'FIN', 246),
('FJ', 'FIJI', 'Fiji', 'FJI', 242),
('FK', 'FALKLAND ISLANDS (MALVINAS)', 'Falkland Islands (Malvinas)', 'FLK', 238),
('FM', 'MICRONESIA, FEDERATED STATES OF', 'Micronesia, Federated States of', 'FSM', 583),
('FO', 'FAROE ISLANDS', 'Faroe Islands', 'FRO', 234),
('FR', 'FRANCE', 'France', 'FRA', 250),
('GA', 'GABON', 'Gabon', 'GAB', 266),
('GB', 'UNITED KINGDOM', 'United Kingdom', 'GBR', 826),
('GD', 'GRENADA', 'Grenada', 'GRD', 308),
('GE', 'GEORGIA', 'Georgia', 'GEO', 268),
('GF', 'FRENCH GUIANA', 'French Guiana', 'GUF', 254),
('GH', 'GHANA', 'Ghana', 'GHA', 288),
('GI', 'GIBRALTAR', 'Gibraltar', 'GIB', 292),
('GL', 'GREENLAND', 'Greenland', 'GRL', 304),
('GM', 'GAMBIA', 'Gambia', 'GMB', 270),
('GN', 'GUINEA', 'Guinea', 'GIN', 324),
('GP', 'GUADELOUPE', 'Guadeloupe', 'GLP', 312),
('GQ', 'EQUATORIAL GUINEA', 'Equatorial Guinea', 'GNQ', 226),
('GR', 'GREECE', 'Greece', 'GRC', 300),
('GS', 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 'South Georgia and the South Sandwich Islands', NULL, NULL),
('GT', 'GUATEMALA', 'Guatemala', 'GTM', 320),
('GU', 'GUAM', 'Guam', 'GUM', 316),
('GW', 'GUINEA-BISSAU', 'Guinea-Bissau', 'GNB', 624),
('GY', 'GUYANA', 'Guyana', 'GUY', 328),
('HK', 'HONG KONG', 'Hong Kong', 'HKG', 344),
('HM', 'HEARD ISLAND AND MCDONALD ISLANDS', 'Heard Island and Mcdonald Islands', NULL, NULL),
('HN', 'HONDURAS', 'Honduras', 'HND', 340),
('HR', 'CROATIA', 'Croatia', 'HRV', 191),
('HT', 'HAITI', 'Haiti', 'HTI', 332),
('HU', 'HUNGARY', 'Hungary', 'HUN', 348),
('ID', 'INDONESIA', 'Indonesia', 'IDN', 360),
('IE', 'IRELAND', 'Ireland', 'IRL', 372),
('IL', 'ISRAEL', 'Israel', 'ISR', 376),
('IN', 'INDIA', 'India', 'IND', 356),
('IO', 'BRITISH INDIAN OCEAN TERRITORY', 'British Indian Ocean Territory', NULL, NULL),
('IQ', 'IRAQ', 'Iraq', 'IRQ', 368),
('IR', 'IRAN, ISLAMIC REPUBLIC OF', 'Iran, Islamic Republic of', 'IRN', 364),
('IS', 'ICELAND', 'Iceland', 'ISL', 352),
('IT', 'ITALY', 'Italy', 'ITA', 380),
('JM', 'JAMAICA', 'Jamaica', 'JAM', 388),
('JO', 'JORDAN', 'Jordan', 'JOR', 400),
('JP', 'JAPAN', 'Japan', 'JPN', 392),
('KE', 'KENYA', 'Kenya', 'KEN', 404),
('KG', 'KYRGYZSTAN', 'Kyrgyzstan', 'KGZ', 417),
('KH', 'CAMBODIA', 'Cambodia', 'KHM', 116),
('KI', 'KIRIBATI', 'Kiribati', 'KIR', 296),
('KM', 'COMOROS', 'Comoros', 'COM', 174),
('KN', 'SAINT KITTS AND NEVIS', 'Saint Kitts and Nevis', 'KNA', 659),
('KP', 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF', 'Korea, Democratic People\'s Republic of', 'PRK', 408),
('KR', 'KOREA, REPUBLIC OF', 'Korea, Republic of', 'KOR', 410),
('KW', 'KUWAIT', 'Kuwait', 'KWT', 414),
('KY', 'CAYMAN ISLANDS', 'Cayman Islands', 'CYM', 136),
('KZ', 'KAZAKHSTAN', 'Kazakhstan', 'KAZ', 398),
('LA', 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC', 'Lao People\'s Democratic Republic', 'LAO', 418),
('LB', 'LEBANON', 'Lebanon', 'LBN', 422),
('LC', 'SAINT LUCIA', 'Saint Lucia', 'LCA', 662),
('LI', 'LIECHTENSTEIN', 'Liechtenstein', 'LIE', 438),
('LK', 'SRI LANKA', 'Sri Lanka', 'LKA', 144),
('LR', 'LIBERIA', 'Liberia', 'LBR', 430),
('LS', 'LESOTHO', 'Lesotho', 'LSO', 426),
('LT', 'LITHUANIA', 'Lithuania', 'LTU', 440),
('LU', 'LUXEMBOURG', 'Luxembourg', 'LUX', 442),
('LV', 'LATVIA', 'Latvia', 'LVA', 428),
('LY', 'LIBYAN ARAB JAMAHIRIYA', 'Libyan Arab Jamahiriya', 'LBY', 434),
('MA', 'MOROCCO', 'Morocco', 'MAR', 504),
('MC', 'MONACO', 'Monaco', 'MCO', 492),
('MD', 'MOLDOVA, REPUBLIC OF', 'Moldova, Republic of', 'MDA', 498),
('MG', 'MADAGASCAR', 'Madagascar', 'MDG', 450),
('MH', 'MARSHALL ISLANDS', 'Marshall Islands', 'MHL', 584),
('MK', 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'Macedonia, the Former Yugoslav Republic of', 'MKD', 807),
('ML', 'MALI', 'Mali', 'MLI', 466),
('MM', 'MYANMAR', 'Myanmar', 'MMR', 104),
('MN', 'MONGOLIA', 'Mongolia', 'MNG', 496),
('MO', 'MACAO', 'Macao', 'MAC', 446),
('MP', 'NORTHERN MARIANA ISLANDS', 'Northern Mariana Islands', 'MNP', 580),
('MQ', 'MARTINIQUE', 'Martinique', 'MTQ', 474),
('MR', 'MAURITANIA', 'Mauritania', 'MRT', 478),
('MS', 'MONTSERRAT', 'Montserrat', 'MSR', 500),
('MT', 'MALTA', 'Malta', 'MLT', 470),
('MU', 'MAURITIUS', 'Mauritius', 'MUS', 480),
('MV', 'MALDIVES', 'Maldives', 'MDV', 462),
('MW', 'MALAWI', 'Malawi', 'MWI', 454),
('MX', 'MEXICO', 'Mexico', 'MEX', 484),
('MY', 'MALAYSIA', 'Malaysia', 'MYS', 458),
('MZ', 'MOZAMBIQUE', 'Mozambique', 'MOZ', 508),
('NA', 'NAMIBIA', 'Namibia', 'NAM', 516),
('NC', 'NEW CALEDONIA', 'New Caledonia', 'NCL', 540),
('NE', 'NIGER', 'Niger', 'NER', 562),
('NF', 'NORFOLK ISLAND', 'Norfolk Island', 'NFK', 574),
('NG', 'NIGERIA', 'Nigeria', 'NGA', 566),
('NI', 'NICARAGUA', 'Nicaragua', 'NIC', 558),
('NL', 'NETHERLANDS', 'Netherlands', 'NLD', 528),
('NO', 'NORWAY', 'Norway', 'NOR', 578),
('NP', 'NEPAL', 'Nepal', 'NPL', 524),
('NR', 'NAURU', 'Nauru', 'NRU', 520),
('NU', 'NIUE', 'Niue', 'NIU', 570),
('NZ', 'NEW ZEALAND', 'New Zealand', 'NZL', 554),
('OM', 'OMAN', 'Oman', 'OMN', 512),
('PA', 'PANAMA', 'Panama', 'PAN', 591),
('PE', 'PERU', 'Peru', 'PER', 604),
('PF', 'FRENCH POLYNESIA', 'French Polynesia', 'PYF', 258),
('PG', 'PAPUA NEW GUINEA', 'Papua New Guinea', 'PNG', 598),
('PH', 'PHILIPPINES', 'Philippines', 'PHL', 608),
('PK', 'PAKISTAN', 'Pakistan', 'PAK', 586),
('PL', 'POLAND', 'Poland', 'POL', 616),
('PM', 'SAINT PIERRE AND MIQUELON', 'Saint Pierre and Miquelon', 'SPM', 666),
('PN', 'PITCAIRN', 'Pitcairn', 'PCN', 612),
('PR', 'PUERTO RICO', 'Puerto Rico', 'PRI', 630),
('PS', 'PALESTINIAN TERRITORY', 'Palestinian Territory', NULL, NULL),
('PT', 'PORTUGAL', 'Portugal', 'PRT', 620),
('PW', 'PALAU', 'Palau', 'PLW', 585),
('PY', 'PARAGUAY', 'Paraguay', 'PRY', 600),
('QA', 'QATAR', 'Qatar', 'QAT', 634),
('RE', 'REUNION', 'Reunion', 'REU', 638),
('RO', 'ROMANIA', 'Romania', 'ROM', 642),
('RU', 'RUSSIAN FEDERATION', 'Russian Federation', 'RUS', 643),
('RW', 'RWANDA', 'Rwanda', 'RWA', 646),
('SA', 'SAUDI ARABIA', 'Saudi Arabia', 'SAU', 682),
('SB', 'SOLOMON ISLANDS', 'Solomon Islands', 'SLB', 90),
('SC', 'SEYCHELLES', 'Seychelles', 'SYC', 690),
('SD', 'SUDAN', 'Sudan', 'SDN', 736),
('SE', 'SWEDEN', 'Sweden', 'SWE', 752),
('SG', 'SINGAPORE', 'Singapore', 'SGP', 702),
('SH', 'SAINT HELENA', 'Saint Helena', 'SHN', 654),
('SI', 'SLOVENIA', 'Slovenia', 'SVN', 705),
('SJ', 'SVALBARD AND JAN MAYEN', 'Svalbard and Jan Mayen', 'SJM', 744),
('SK', 'SLOVAKIA', 'Slovakia', 'SVK', 703),
('SL', 'SIERRA LEONE', 'Sierra Leone', 'SLE', 694),
('SM', 'SAN MARINO', 'San Marino', 'SMR', 674),
('SN', 'SENEGAL', 'Senegal', 'SEN', 686),
('SO', 'SOMALIA', 'Somalia', 'SOM', 706),
('SR', 'SURINAME', 'Suriname', 'SUR', 740),
('ST', 'SAO TOME AND PRINCIPE', 'Sao Tome and Principe', 'STP', 678),
('SV', 'EL SALVADOR', 'El Salvador', 'SLV', 222),
('SY', 'SYRIAN ARAB REPUBLIC', 'Syrian Arab Republic', 'SYR', 760),
('SZ', 'SWAZILAND', 'Swaziland', 'SWZ', 748),
('TC', 'TURKS AND CAICOS ISLANDS', 'Turks and Caicos Islands', 'TCA', 796),
('TD', 'CHAD', 'Chad', 'TCD', 148),
('TF', 'FRENCH SOUTHERN TERRITORIES', 'French Southern Territories', NULL, NULL),
('TG', 'TOGO', 'Togo', 'TGO', 768),
('TH', 'THAILAND', 'Thailand', 'THA', 764),
('TJ', 'TAJIKISTAN', 'Tajikistan', 'TJK', 762),
('TK', 'TOKELAU', 'Tokelau', 'TKL', 772),
('TL', 'TIMOR-LESTE', 'Timor-Leste', NULL, NULL),
('TM', 'TURKMENISTAN', 'Turkmenistan', 'TKM', 795),
('TN', 'TUNISIA', 'Tunisia', 'TUN', 788),
('TO', 'TONGA', 'Tonga', 'TON', 776),
('TR', 'TURKEY', 'Turkey', 'TUR', 792),
('TT', 'TRINIDAD AND TOBAGO', 'Trinidad and Tobago', 'TTO', 780),
('TV', 'TUVALU', 'Tuvalu', 'TUV', 798),
('TW', 'TAIWAN', 'Taiwan', 'TWN', 158),
('TZ', 'TANZANIA, UNITED REPUBLIC OF', 'Tanzania, United Republic of', 'TZA', 834),
('UA', 'UKRAINE', 'Ukraine', 'UKR', 804),
('UG', 'UGANDA', 'Uganda', 'UGA', 800),
('UM', 'UNITED STATES MINOR OUTLYING ISLANDS', 'United States Minor Outlying Islands', NULL, NULL),
('US', 'UNITED STATES', 'United States', 'USA', 840),
('UY', 'URUGUAY', 'Uruguay', 'URY', 858),
('UZ', 'UZBEKISTAN', 'Uzbekistan', 'UZB', 860),
('VA', 'HOLY SEE (VATICAN CITY STATE)', 'Holy See (Vatican City State)', 'VAT', 336),
('VC', 'SAINT VINCENT AND THE GRENADINES', 'Saint Vincent and the Grenadines', 'VCT', 670),
('VE', 'VENEZUELA', 'Venezuela', 'VEN', 862),
('VG', 'VIRGIN ISLANDS, BRITISH', 'Virgin Islands, British', 'VGB', 92),
('VI', 'VIRGIN ISLANDS, U.S.', 'Virgin Islands, U.s.', 'VIR', 850),
('VN', 'VIET NAM', 'Viet Nam', 'VNM', 704),
('VU', 'VANUATU', 'Vanuatu', 'VUT', 548),
('WF', 'WALLIS AND FUTUNA', 'Wallis and Futuna', 'WLF', 876),
('WS', 'SAMOA', 'Samoa', 'WSM', 882),
('YE', 'YEMEN', 'Yemen', 'YEM', 887),
('YT', 'MAYOTTE', 'Mayotte', NULL, NULL),
('ZA', 'SOUTH AFRICA', 'South Africa', 'ZAF', 710),
('ZM', 'ZAMBIA', 'Zambia', 'ZMB', 894),
('ZW', 'ZIMBABWE', 'Zimbabwe', 'ZWE', 716);

-- --------------------------------------------------------

--
-- Table structure for table `systemCutQualifications`
--

CREATE TABLE `systemCutQualifications` (
  `qualID` int(10) UNSIGNED NOT NULL,
  `systemRosterID` int(10) UNSIGNED DEFAULT NULL,
  `standardID` int(10) UNSIGNED DEFAULT NULL,
  `date` date DEFAULT NULL,
  `qualValue` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemCutStandards`
--

CREATE TABLE `systemCutStandards` (
  `standardID` int(10) UNSIGNED NOT NULL,
  `standardName` varchar(255) NOT NULL,
  `standardCode` varchar(255) NOT NULL,
  `standardText` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemCutStandards`
--

INSERT INTO `systemCutStandards` (`standardID`, `standardName`, `standardCode`, `standardText`) VALUES
(1, 'West Coast Qualification', 'westCoast', 'Either:\r\n4 total cuts, 2 on each side of the mat\r\n\r\nor\r\n\r\n3 unique cuts performed on the mat\r\n\r\nTime Limit: 40 seconds.'),
(2, 'Longpoint HFL', 'LHFL', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `systemDoubleTypes`
--

CREATE TABLE `systemDoubleTypes` (
  `doubleTypeID` int(10) UNSIGNED NOT NULL,
  `doubleTypeName` varchar(255) NOT NULL,
  `doublesDisabled` tinyint(1) NOT NULL,
  `afterblowDisabled` tinyint(1) NOT NULL,
  `afterblowType` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemDoubleTypes`
--

INSERT INTO `systemDoubleTypes` (`doubleTypeID`, `doubleTypeName`, `doublesDisabled`, `afterblowDisabled`, `afterblowType`) VALUES
(1, 'No Afterblow', 0, 1, 'none'),
(2, 'Deductive Afterblow', 0, 0, 'deductive'),
(3, 'Full Afterblow', 0, 0, 'full');

-- --------------------------------------------------------

--
-- Table structure for table `systemEvents`
--

CREATE TABLE `systemEvents` (
  `eventID` int(10) UNSIGNED NOT NULL,
  `eventName` varchar(255) NOT NULL,
  `eventAbbreviation` varchar(255) DEFAULT NULL,
  `eventYear` smallint(6) DEFAULT NULL,
  `eventStartDate` date DEFAULT NULL,
  `eventEndDate` date DEFAULT NULL,
  `regionCode` int(11) DEFAULT NULL,
  `countryIso2` varchar(2) NOT NULL DEFAULT 'AQ',
  `eventProvince` varchar(255) DEFAULT NULL,
  `eventCity` varchar(255) DEFAULT NULL,
  `eventStatus` varchar(255) NOT NULL DEFAULT 'active',
  `isArchived` tinyint(1) NOT NULL DEFAULT '0',
  `limitStaffConflicts` int(11) NOT NULL DEFAULT '0',
  `isMetaEvent` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemFormats`
--

CREATE TABLE `systemFormats` (
  `formatID` int(10) UNSIGNED NOT NULL,
  `formatName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemFormats`
--

INSERT INTO `systemFormats` (`formatID`, `formatName`) VALUES
(1, 'Results Only'),
(2, 'Sparring Matches'),
(3, 'Solo Scored'),
(4, 'Composite Event');

-- --------------------------------------------------------

--
-- Table structure for table `systemLogisticsRoles`
--

CREATE TABLE `systemLogisticsRoles` (
  `logisticsRoleID` int(10) UNSIGNED NOT NULL,
  `roleName` varchar(255) NOT NULL,
  `roleSortImportance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemLogisticsRoles`
--

INSERT INTO `systemLogisticsRoles` (`logisticsRoleID`, `roleName`, `roleSortImportance`) VALUES
(1, 'Director', 30),
(2, 'Judge', 20),
(3, 'Table', 10),
(4, '*Unspecified*', 0),
(5, 'Instructor', 60),
(6, 'General Staff', 5),
(7, 'Participant', 1),
(8, 'Ring Boss', 25),
(9, 'Director - Assistant', 29);

-- --------------------------------------------------------

--
-- Table structure for table `systemMatchOrder`
--

CREATE TABLE `systemMatchOrder` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `numberOfFighters` tinyint(4) DEFAULT NULL,
  `matchNumber` tinyint(4) DEFAULT NULL,
  `fighter1` tinyint(4) DEFAULT NULL,
  `fighter2` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemMatchOrder`
--

INSERT INTO `systemMatchOrder` (`tableID`, `numberOfFighters`, `matchNumber`, `fighter1`, `fighter2`) VALUES
(1, 2, 1, 1, 2),
(2, 3, 1, 1, 2),
(3, 3, 2, 1, 3),
(4, 3, 3, 2, 3),
(5, 4, 1, 1, 2),
(6, 4, 2, 3, 4),
(7, 4, 3, 3, 2),
(8, 4, 4, 4, 2),
(9, 4, 5, 4, 1),
(10, 4, 6, 3, 1),
(11, 5, 1, 2, 1),
(12, 5, 2, 4, 3),
(13, 5, 3, 5, 1),
(14, 5, 4, 2, 3),
(15, 5, 5, 5, 4),
(16, 5, 6, 1, 3),
(17, 5, 7, 5, 2),
(18, 5, 8, 1, 4),
(19, 5, 9, 5, 3),
(20, 5, 10, 2, 4),
(21, 6, 1, 2, 1),
(22, 6, 2, 3, 6),
(23, 6, 3, 4, 5),
(24, 6, 4, 6, 1),
(25, 6, 5, 2, 5),
(26, 6, 6, 3, 4),
(27, 6, 7, 1, 5),
(28, 6, 8, 6, 4),
(29, 6, 9, 2, 3),
(30, 6, 10, 4, 1),
(31, 6, 11, 5, 3),
(32, 6, 12, 6, 2),
(33, 6, 13, 1, 3),
(34, 6, 14, 4, 2),
(35, 6, 15, 5, 6),
(36, 7, 1, 7, 6),
(37, 7, 2, 1, 5),
(38, 7, 3, 2, 4),
(39, 7, 4, 6, 5),
(40, 7, 5, 7, 4),
(41, 7, 6, 1, 3),
(42, 7, 7, 5, 4),
(43, 7, 8, 6, 3),
(44, 7, 9, 7, 2),
(45, 7, 10, 4, 3),
(46, 7, 11, 5, 2),
(47, 7, 12, 6, 1),
(48, 7, 13, 3, 2),
(49, 7, 14, 4, 1),
(50, 7, 15, 5, 7),
(51, 7, 16, 2, 1),
(52, 7, 17, 3, 7),
(53, 7, 18, 4, 6),
(54, 7, 19, 1, 7),
(55, 7, 20, 2, 6),
(56, 7, 21, 3, 5),
(57, 8, 1, 7, 6),
(58, 8, 2, 1, 5),
(59, 8, 3, 2, 4),
(60, 8, 4, 3, 8),
(61, 8, 5, 6, 5),
(62, 8, 6, 7, 4),
(63, 8, 7, 1, 3),
(64, 8, 8, 2, 8),
(65, 8, 9, 5, 4),
(66, 8, 10, 6, 3),
(67, 8, 11, 7, 2),
(68, 8, 12, 1, 8),
(69, 8, 13, 4, 3),
(70, 8, 14, 5, 2),
(71, 8, 15, 6, 1),
(72, 8, 16, 7, 8),
(73, 8, 17, 3, 2),
(74, 8, 18, 4, 1),
(75, 8, 19, 5, 7),
(76, 8, 20, 6, 8),
(77, 8, 21, 2, 1),
(78, 8, 22, 3, 7),
(79, 8, 23, 4, 6),
(80, 8, 24, 5, 8),
(81, 8, 25, 1, 7),
(82, 8, 26, 2, 6),
(83, 8, 27, 3, 5),
(84, 8, 28, 4, 8),
(85, 10, 1, 2, 1),
(86, 10, 2, 3, 10),
(87, 10, 3, 4, 9),
(88, 10, 4, 5, 8),
(89, 10, 5, 6, 7),
(90, 10, 6, 2, 3),
(91, 10, 7, 1, 7),
(92, 10, 8, 8, 6),
(93, 10, 9, 9, 5),
(94, 10, 10, 10, 4),
(95, 10, 11, 6, 9),
(96, 10, 12, 7, 8),
(97, 10, 13, 3, 1),
(98, 10, 14, 4, 2),
(99, 10, 15, 5, 10),
(100, 10, 16, 10, 6),
(101, 10, 17, 2, 5),
(102, 10, 18, 3, 4),
(103, 10, 19, 1, 8),
(104, 10, 20, 9, 7),
(105, 10, 21, 5, 3),
(106, 10, 22, 6, 2),
(107, 10, 23, 7, 10),
(108, 10, 24, 8, 9),
(109, 10, 25, 4, 1),
(110, 10, 26, 1, 9),
(111, 10, 27, 10, 8),
(112, 10, 28, 2, 7),
(113, 10, 29, 3, 6),
(114, 10, 30, 4, 5),
(115, 10, 31, 5, 1),
(116, 10, 32, 6, 4),
(117, 10, 33, 7, 3),
(118, 10, 34, 8, 2),
(119, 10, 35, 9, 10),
(120, 10, 36, 5, 6),
(121, 10, 37, 1, 10),
(122, 10, 38, 2, 9),
(123, 10, 39, 3, 8),
(124, 10, 40, 4, 7),
(125, 10, 41, 9, 3),
(126, 10, 42, 10, 2),
(127, 10, 43, 6, 1),
(128, 10, 44, 7, 5),
(129, 10, 45, 8, 4),
(130, 9, 1, 2, 1),
(131, 9, 2, 4, 9),
(132, 9, 3, 5, 8),
(133, 9, 4, 6, 7),
(134, 9, 5, 2, 3),
(135, 9, 6, 1, 7),
(136, 9, 7, 8, 6),
(137, 9, 8, 9, 5),
(138, 9, 9, 9, 6),
(139, 9, 10, 7, 8),
(140, 9, 11, 3, 1),
(141, 9, 12, 4, 2),
(142, 9, 13, 5, 2),
(143, 9, 14, 4, 3),
(144, 9, 15, 1, 8),
(145, 9, 16, 9, 7),
(146, 9, 17, 5, 3),
(147, 9, 18, 6, 2),
(148, 9, 19, 8, 9),
(149, 9, 20, 1, 4),
(150, 9, 21, 1, 9),
(151, 9, 22, 2, 7),
(152, 9, 23, 3, 6),
(153, 9, 24, 4, 5),
(154, 9, 25, 1, 5),
(155, 9, 26, 4, 6),
(156, 9, 27, 7, 3),
(157, 9, 28, 8, 2),
(158, 9, 29, 5, 6),
(159, 9, 30, 9, 2),
(160, 9, 31, 3, 8),
(161, 9, 32, 4, 7),
(162, 9, 33, 9, 3),
(163, 9, 34, 6, 1),
(164, 9, 35, 7, 5),
(165, 9, 36, 8, 4),
(166, 11, 1, 1, 10),
(167, 11, 2, 6, 4),
(168, 11, 3, 2, 11),
(169, 11, 4, 7, 5),
(170, 11, 5, 3, 1),
(171, 11, 6, 8, 6),
(172, 11, 7, 4, 2),
(173, 11, 8, 9, 7),
(174, 11, 9, 5, 3),
(175, 11, 10, 10, 8),
(176, 11, 11, 11, 9),
(177, 11, 12, 2, 9),
(178, 11, 13, 7, 3),
(179, 11, 14, 3, 10),
(180, 11, 15, 8, 4),
(181, 11, 16, 4, 11),
(182, 11, 17, 9, 5),
(183, 11, 18, 5, 1),
(184, 11, 19, 10, 6),
(185, 11, 20, 6, 2),
(186, 11, 21, 11, 7),
(187, 11, 22, 1, 8),
(188, 11, 23, 3, 8),
(189, 11, 24, 8, 2),
(190, 11, 25, 4, 9),
(191, 11, 26, 9, 3),
(192, 11, 27, 5, 10),
(193, 11, 28, 10, 4),
(194, 11, 29, 6, 11),
(195, 11, 30, 11, 5),
(196, 11, 31, 7, 1),
(197, 11, 32, 1, 6),
(198, 11, 33, 2, 7),
(199, 11, 34, 4, 7),
(200, 11, 35, 9, 1),
(201, 11, 36, 5, 8),
(202, 11, 37, 10, 2),
(203, 11, 38, 6, 9),
(204, 11, 39, 11, 3),
(205, 11, 40, 7, 10),
(206, 11, 41, 1, 4),
(207, 11, 42, 8, 11),
(208, 11, 43, 2, 5),
(209, 11, 44, 3, 6),
(210, 11, 45, 5, 6),
(211, 11, 46, 10, 11),
(212, 11, 47, 6, 7),
(213, 11, 48, 11, 1),
(214, 11, 49, 7, 8),
(215, 11, 50, 1, 2),
(216, 11, 51, 8, 9),
(217, 11, 52, 2, 3),
(218, 11, 53, 9, 10),
(219, 11, 54, 3, 4),
(220, 11, 55, 4, 5),
(221, 12, 1, 2, 1),
(222, 12, 2, 12, 2),
(223, 12, 3, 5, 8),
(224, 12, 4, 8, 2),
(225, 12, 5, 12, 8),
(226, 12, 6, 4, 2),
(227, 12, 7, 8, 1),
(228, 12, 8, 11, 12),
(229, 12, 9, 4, 7),
(230, 12, 10, 7, 12),
(231, 12, 11, 11, 7),
(232, 12, 12, 3, 12),
(233, 12, 13, 1, 7),
(234, 12, 14, 6, 7),
(235, 12, 15, 9, 12),
(236, 12, 16, 2, 7),
(237, 12, 17, 5, 12),
(238, 12, 18, 9, 7),
(239, 12, 19, 6, 1),
(240, 12, 20, 5, 6),
(241, 12, 21, 8, 11),
(242, 12, 22, 12, 6),
(243, 12, 23, 4, 11),
(244, 12, 24, 8, 6),
(245, 12, 25, 12, 1),
(246, 12, 26, 10, 11),
(247, 12, 27, 3, 6),
(248, 12, 28, 6, 11),
(249, 12, 29, 10, 6),
(250, 12, 30, 7, 5),
(251, 12, 31, 1, 11),
(252, 12, 32, 9, 10),
(253, 12, 33, 2, 5),
(254, 12, 34, 5, 10),
(255, 12, 35, 9, 5),
(256, 12, 36, 2, 11),
(257, 12, 37, 1, 5),
(258, 12, 38, 4, 5),
(259, 12, 39, 7, 10),
(260, 12, 40, 11, 5),
(261, 12, 41, 8, 4),
(262, 12, 42, 12, 10),
(263, 12, 43, 4, 1),
(264, 12, 44, 3, 4),
(265, 12, 45, 6, 9),
(266, 12, 46, 10, 4),
(267, 12, 47, 3, 10),
(268, 12, 48, 6, 4),
(269, 12, 49, 10, 1),
(270, 12, 50, 8, 9),
(271, 12, 51, 12, 4),
(272, 12, 52, 9, 3),
(273, 12, 53, 2, 9),
(274, 12, 54, 5, 3),
(275, 12, 55, 1, 9),
(276, 12, 56, 7, 8),
(277, 12, 57, 11, 3),
(278, 12, 58, 4, 9),
(279, 12, 59, 7, 3),
(280, 12, 60, 11, 9),
(281, 12, 61, 1, 3),
(282, 12, 62, 2, 3),
(283, 12, 63, 10, 2),
(284, 12, 64, 3, 8),
(285, 12, 65, 6, 2),
(286, 12, 66, 10, 8),
(287, 13, 1, 1, 2),
(288, 13, 2, 8, 9),
(289, 13, 3, 2, 3),
(290, 13, 4, 9, 10),
(291, 13, 5, 3, 4),
(292, 13, 6, 10, 11),
(293, 13, 7, 4, 5),
(294, 13, 8, 11, 12),
(295, 13, 9, 5, 6),
(296, 13, 10, 12, 13),
(297, 13, 11, 6, 7),
(298, 13, 12, 1, 13),
(299, 13, 13, 7, 8),
(300, 13, 14, 7, 9),
(301, 13, 15, 1, 3),
(302, 13, 16, 8, 10),
(303, 13, 17, 2, 4),
(304, 13, 18, 9, 11),
(305, 13, 19, 3, 5),
(306, 13, 20, 10, 12),
(307, 13, 21, 4, 6),
(308, 13, 22, 11, 13),
(309, 13, 23, 5, 7),
(310, 13, 24, 1, 12),
(311, 13, 25, 6, 8),
(312, 13, 26, 2, 13),
(313, 13, 27, 3, 13),
(314, 13, 28, 7, 10),
(315, 13, 29, 1, 4),
(316, 13, 30, 8, 11),
(317, 13, 31, 2, 5),
(318, 13, 32, 9, 12),
(319, 13, 33, 3, 6),
(320, 13, 34, 10, 13),
(321, 13, 35, 4, 7),
(322, 13, 36, 1, 11),
(323, 13, 37, 5, 8),
(324, 13, 38, 2, 12),
(325, 13, 39, 6, 9),
(326, 13, 40, 6, 10),
(327, 13, 41, 4, 13),
(328, 13, 42, 7, 11),
(329, 13, 43, 1, 5),
(330, 13, 44, 8, 12),
(331, 13, 45, 2, 6),
(332, 13, 46, 9, 13),
(333, 13, 47, 3, 7),
(334, 13, 48, 1, 10),
(335, 13, 49, 4, 8),
(336, 13, 50, 2, 11),
(337, 13, 51, 5, 9),
(338, 13, 52, 3, 12),
(339, 13, 53, 4, 12),
(340, 13, 54, 6, 11),
(341, 13, 55, 5, 13),
(342, 13, 56, 7, 12),
(343, 13, 57, 1, 6),
(344, 13, 58, 8, 13),
(345, 13, 59, 2, 7),
(346, 13, 60, 1, 9),
(347, 13, 61, 3, 8),
(348, 13, 62, 2, 10),
(349, 13, 63, 4, 9),
(350, 13, 64, 3, 11),
(351, 13, 65, 5, 10),
(352, 13, 66, 5, 11),
(353, 13, 67, 5, 12),
(354, 13, 68, 6, 12),
(355, 13, 69, 6, 13),
(356, 13, 70, 7, 13),
(357, 13, 71, 1, 7),
(358, 13, 72, 1, 8),
(359, 13, 73, 2, 8),
(360, 13, 74, 2, 9),
(361, 13, 75, 3, 9),
(362, 13, 76, 3, 10),
(363, 13, 77, 4, 10),
(364, 13, 78, 4, 11);

-- --------------------------------------------------------

--
-- Table structure for table `systemOptionsList`
--

CREATE TABLE `systemOptionsList` (
  `optionID` int(10) UNSIGNED NOT NULL,
  `optionEnum` varchar(255) NOT NULL,
  `optionName` varchar(255) NOT NULL,
  `optionType` varchar(255) NOT NULL,
  `optionDescription` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemOptionsList`
--

INSERT INTO `systemOptionsList` (`optionID`, `optionEnum`, `optionName`, `optionType`, `optionDescription`) VALUES
(1, 'META_ROSTER_MODE', 'Roster Mode', 'tournament', 'The way the roster for a meta-tournament is managed/constructed.'),
(2, 'MATCH_NUM_SUB_MATCHES', 'Number of Sub-Matches', 'match', 'Number of sub-matches fought as a part of this match.'),
(3, 'SWAP_FIGHTERS', 'Switch fighter colors', 'match', 'Swap the colors of the fighters in a match'),
(4, 'ATTACK_DISPLAY_MODE', 'Attack Display Mode', 'tournament', 'Defines how user-defined attack types are displayed/edited'),
(5, 'AFTERBLOW_POINT_VALUE', 'Afterblow Point Value', 'tournament', 'The point deduction for an afterblow. (Even if afterblow > score, the final score will never be negative.)'),
(6, 'MATCH_TIE_MODE', 'Tie Mode', 'tournament', 'When to allow a match to end on a tie'),
(7, 'TEAM_SWITCH_POINTS', 'Team Switch Points', 'tournament', 'In a Team vs Team< tournament the table will be instructed to change fighters whenever one team\'s score reaches a multiple of this value. (And also before the match starts.)'),
(8, 'DOUBLES_ARE_NOT_SCORING_EXCH', 'Doubles Are Not Scoring Exchanges', 'tournament', 'If you don\'t want doubles to count as a scoring exchange\r\n(When calculating to end the match based of maximum number of exchanges.)'),
(9, 'CONTROL_POINT_VALUE', 'Control Point Value', 'tournament', 'The value of control point.');

-- --------------------------------------------------------

--
-- Table structure for table `systemRankings`
--

CREATE TABLE `systemRankings` (
  `tournamentRankingID` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `formatID` int(10) UNSIGNED NOT NULL,
  `numberOfInstances` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `description` text,
  `displayFunction` varchar(255) DEFAULT NULL,
  `scoringFunction` varchar(255) DEFAULT NULL,
  `scoreFormula` text,
  `orderByField1` varchar(255) NOT NULL DEFAULT 'score',
  `orderBySort1` varchar(255) NOT NULL DEFAULT 'DESC',
  `orderByField2` varchar(255) DEFAULT NULL,
  `orderBySort2` varchar(255) DEFAULT NULL,
  `orderByField3` varchar(255) DEFAULT NULL,
  `orderBySort3` varchar(255) DEFAULT NULL,
  `orderByField4` varchar(255) DEFAULT NULL,
  `orderBySort4` varchar(255) DEFAULT NULL,
  `displayTitle1` varchar(255) DEFAULT 'Score',
  `displayField1` varchar(255) DEFAULT 'score',
  `displayTitle2` varchar(255) DEFAULT NULL,
  `displayField2` varchar(255) DEFAULT NULL,
  `displayTitle3` varchar(255) DEFAULT NULL,
  `displayField3` varchar(255) DEFAULT NULL,
  `displayTitle4` varchar(255) DEFAULT NULL,
  `displayField4` varchar(255) DEFAULT NULL,
  `displayTitle5` varchar(255) DEFAULT NULL,
  `displayField5` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemRankings`
--

INSERT INTO `systemRankings` (`tournamentRankingID`, `name`, `formatID`, `numberOfInstances`, `description`, `displayFunction`, `scoringFunction`, `scoreFormula`, `orderByField1`, `orderBySort1`, `orderByField2`, `orderBySort2`, `orderByField3`, `orderBySort3`, `orderByField4`, `orderBySort4`, `displayTitle1`, `displayField1`, `displayTitle2`, `displayField2`, `displayTitle3`, `displayField3`, `displayTitle4`, `displayField4`, `displayTitle5`, `displayField5`) VALUES
(1, 'Franklin 2014', 2, 143, '== Ranking ====\nIndicator Score\n1st Tiebreaker: Wins [Highest]\n2nd Tiebreaker: Doubles [Lowest]\n3rd Tiebreaker: Hits Against [Lowest]\n\n==Indicator Score ====\n +[Points For]\n +(5 * [Wins])\n -[Points Against]\n -(Doubles Penalty)\n\nDoubles Penalty:\n1 Double -> 1 = 1\n2 Doubles -> 1+2 = 3\n3 Doubles -> 1+2+3 = 6 etc...', NULL, NULL, '(5*wins) + pointsFor - pointsAgainst - ((doubles * (doubles+1))/2)', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(2, 'RSS Cutting', 3, 11, '(Root Sum Square Cutting)\n\n== Ranking ====\nIndicator Score\n1st Tiebreaker: Least deductions\n\n== Indicator Score Score ====\n\nTotal Deduction = sqrt([Cut Deduction]^2 + [Form Deduction]^2)\n\nScore = 20 - Total Deduction\n\n', 'RSScutting', 'RSScutting', NULL, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Results Only', 1, 19, NULL, NULL, NULL, NULL, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Deduction Based', 3, 7, '== Ranking ====\nIndicator Score\n\n== Indicator Score ====\n100 point base score\nDeductions are applied against the base score', 'DeductionBased', 'DeductionBased', 'pointsFor', 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'FNY 2017', 2, 5, '(Fechtshule New York 2017)\n\n== Ranking ====\nIndicator Score\n\n== Indicator Score ====\n+ 1*Wins\n- 2*[Losses]\n- 2*[Doubles]', NULL, NULL, 'pointsFor - 2 * (losses + doubles)', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Pushes', 'matches - hitsFor - losses - doubles', 'Losses', 'losses', 'Doubles', 'doubles', 'Score', 'score'),
(7, 'Total Points Scored', 2, 75, 'Ranking\nNet Points For, after removing deductions due to afterblows.\n1st Tiebreaker: Doubles\n2nd Tiebreaker: Wins\n', NULL, NULL, 'pointsFor', 'score', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', NULL, NULL, 'Wins', 'wins', 'Doubles', 'doubles', 'Points Scored', 'score', NULL, NULL, NULL, NULL),
(8, 'Hit Ratio', 2, 3, '== Ranking ====\nIndicator Score\n1st Tiebreaker: Wins\n\n== Indicator Score ====\n[Points For] / [Total Times Hit]\n\n', NULL, NULL, 'case \n	when (hitsAgainst + afterblowsAgainst + doubles) > 0 then\n		pointsFor /  (hitsAgainst + afterblowsAgainst + doubles)\n	else\n		9001\nend', 'score', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', NULL, NULL, 'Points For', 'pointsFor', 'Total Times Hit', 'hitsAgainst + afterblowsAgainst + doubles', 'Score', 'score', NULL, NULL, NULL, NULL),
(9, 'Sandstorm 2017', 2, 2, '== Ranking ====\nIndicator Score\n\n== Indicator Score ====\n3 Points - Controlled Win/Artful Exchange\n2 Points - Win\n1 Point - Win w/ Afterblow\n', NULL, NULL, 'pointsFor - doubles', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Control Wins', 'score + doubles - (2*wins) - (3*afterblowsAgainst)', 'Wins', '(3 * wins) - (2 * afterblowsAgainst) - score + doubles', 'Afterblow Wins', 'afterblowsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(10, '2 Point Cumulative', 2, 2, '== Ranking ====\nIndicator Score\n\n== Indicator Score ====\n2 Points for Win\n1 Point for Tie', NULL, NULL, '(2 * wins) + ties', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Ties', 'ties', 'Losses', 'losses', 'Score', 'score', NULL, NULL),
(11, 'Flowerpoint', 2, 20, '== Ranking ====\nIndicator Score\n\n== Indicator Score ====\n-1 Point for every time hit\n(Scoring action or double)\n\n', NULL, NULL, '0 - hitsAgainst - doubles', 'score', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', NULL, NULL, 'Number of Times Hit', 'hitsAgainst', 'Doubles', 'doubles', 'Score', 'score', NULL, NULL, NULL, NULL),
(13, 'Thokk Continuous', 2, 1, '== Ranking ====\nNumber of Time Hit [Ascending]\n1st Tiebreaker: Number of hits delivered [Descending]\n2nd Tiebreaker: Indicator Score [Descending]\n\n== Indicator Score ====\n(0 - Points Against*)\n*Points Against is the absolute value before afterblow deduction is applied.', NULL, NULL, '0 - AbsPointsAgainst', 'hitsAgainst', 'ASC', 'hitsFor', 'DESC', 'score', 'DESC', NULL, NULL, 'Bouts Won', 'hitsFor', 'Bouts Lost', 'hitsAgainst', 'Points Against', 'pointsAgainst', NULL, NULL, NULL, NULL),
(14, 'Alls Fair', 2, 7, '== Ranking =====\nWins\n1st Tiebreaker: Doubles\n2nd Tiebreaker: Points +/-', NULL, NULL, 'pointsFor - pointsAgainst', 'wins', 'DESC', 'doubles', 'ASC', 'score', 'DESC', NULL, NULL, 'Wins', 'wins', 'Doubles', 'doubles', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Points +/-', 'score'),
(15, 'JNCR', 3, 7, '(Julian\'s Nameless Cutting Rules)\n\nCuts are assigned scored as follows:\n8 points cut quality\n4 points upper body form\n4 points lower body form\n\n0 in cut quality or 0 in combined form is 0 for the entire cut.\n\nA negative score in any of the three becomes the final score.\n\nA cut with perfect scores earns an additional +4 points.', 'JNCR', 'JNCR', NULL, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Aussie Reversed', 2, 82, '<u>This score mode is meant to be used with reverse scores!</u>\nPoints are assigned to the fighter who was hit.\n\n== Ranking ====\nWins\n1st Tiebreaker: Least points hit with (this is the points you give to the fighter!)\n2nd Tiebreaker: Most points hit against opponents\n\nThese are the absolute values of points, without the afterblow deduction.', NULL, NULL, 'AbsPointsAgainst', 'wins', 'DESC', 'AbsPointsAgainst', 'ASC', 'AbsPointsFor', 'DESC', NULL, NULL, 'Wins', 'wins', 'Points Against', 'score', 'Points For', 'AbsPointsFor', 'Mutual Hits', 'doubles + afterblowsFor + afterblowsAgainst', NULL, NULL),
(17, 'AHWG 2018', 2, 1, '== Ranking ====\nIndicator Score\n\n== Indicator Score ====\nWins - Losses - Double Outs', NULL, NULL, 'wins - losses - doubleOuts', 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Losses', 'losses', 'Doubles', 'doubles', 'Score', 'score', NULL, NULL),
(18, 'MART', 2, 1, '(Mid Atlantic Rookie Tournament: Fighty McFightface)\n\n== Ranking ====\nIndicator Score\n1st Tiebreaker: Doubles\n2nd Tiebreaker: Points against\n3rs Tiebreaker: Points for\n\n== Indicator Score ====\n2 * Wins + Ties\n\n\n\n\n', NULL, NULL, '(2 * Wins) + Ties', 'score', 'DESC', '(doubles + afterblowsFor + afterblowsAgainst)', 'ASC', 'AbsPointsAgainst', 'ASC', 'AbsPointsFor', 'DESC', 'Wins', 'wins', 'Ties', 'ties', 'Doubles', '(doubles + afterblowsFor + afterblowsAgainst)', 'Points Against', 'AbsPointsAgainst', 'Points For', 'AbsPointsFor'),
(19, 'Franklin 2014 (x25)', 2, 23, '== Ranking ====\nIndicator Score\n1st Tiebreaker: Wins [Highest]\n2nd Tiebreaker: Doubles [Lowest]\n3rd Tiebreaker: Hits Against [Lowest]\n\n==Indicator Score ====\n +[Points For]\n +(5 * [Wins])\n -[Points Against]\n -(Doubles Penalty) * 1.25\n\nDoubles Penalty:\n1 Double -> 1 = 1\n2 Doubles -> 1+2 = 3\n3 Doubles -> 1+2+3 = 6 etc...', NULL, NULL, '(5*wins) + pointsFor - pointsAgainst - (1.25*(doubles * (doubles+1))/2)', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(20, 'Baer Score', 2, 12, '== Ranking ====\nWins\n1st Tiebreaker: Points Against\n2nd Tiebreaker: Doubles', NULL, NULL, '0', 'wins', 'DESC', 'AbsPointsAgainst', 'ASC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Points Against', 'AbsPointsAgainst', 'Doubles', 'doubles', NULL, NULL, NULL, NULL),
(21, 'Wins | Plus/Minus', 2, 229, '== Ranking ====\nWins\n1st Tiebreaker: Indicator Score\n\n== Indicator Score ====\npointsFor - pointsAgainst\n\n', NULL, NULL, 'pointsFor - pointsAgainst', 'wins', 'DESC', 'score', 'DESC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Points +/-', 'score', NULL, NULL),
(22, 'Ram Rules', 2, 6, '== Ranking ====\r\nIndicator Score\r\n\r\n== Indicator Score ====\r\nPoints For - (2 * Doubles)', NULL, NULL, 'pointsFor - (2 * Doubles)', 'score', 'DESC', 'doubles', 'ASC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points For', 'pointsFor', 'Doubles', 'doubles', 'Score', 'score', NULL, NULL),
(23, 'Swiss League', 2, 5, '==Ranking ====\r\nIndicator Score\r\n1st Tiebreaker: Points scored\r\n\r\n== Indicator Score ====\r\nMatch Score for Winner = (Winner Pts - Loser Pts) / Winner Pts\r\nMatch Score for Lower = 0\r\nPool Indicator Score = Sum of Match Indicator Scores', NULL, NULL, '#SwissScore', 'score', 'DESC', 'AbsPointsFor', 'DESC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points For', 'AbsPointsFor', 'Points Against', 'AbsPointsAgainst', 'Indicator Score', 'score', NULL, NULL),
(24, 'Wins & Aggregate Score', 2, 22, '== Ranking ====\r\nWins\r\n1st Tiebreaker: Total Points Scored\r\n\r\n*points scored before afterblow deduction is applied', NULL, NULL, 'AbsPointsFor', 'wins', 'DESC', 'pointsFor', 'DESC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points Scored', 'pointsFor', 'Points Against', 'pointsAgainst', 'Bilateral Hits', 'doubles + afterblowsFor + afterblowsAgainst', NULL, NULL),
(25, 'Wessex League', 2, 53, 'Ranking ====\r\nIndicator Score\r\n1st Tiebreaker: (Hits For - Hits Against)\r\n2nd Tiebreaker: Doubles\r\n\r\n== Indicator Score ====\r\n+ 3 * Wins\r\n+ 1 * Ties\r\n- Doubles Penalty\r\n\r\nDoubles Penalty:\r\nEvery second double per match -1\r\n(ie: First double every match is free)', NULL, NULL, '#Wessex', 'score', 'DESC', 'hitsFor - hitsAgainst', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Losses', 'losses', 'Draws', 'ties', 'Doubles', 'doubles', 'Score', 'score'),
(26, 'WEIRD 2019', 2, 17, '== Ranking ====\r\nIndicator Score\r\n1st Tiebreaker: Points Against\r\n\r\n== Indicator Score ====\r\n+ (10 * Wins)\r\n- (10 * Losses)\r\n- (10 * Double Outs)\r\n+ pointsFor\r\n\r\n', NULL, NULL, '(10 * wins) - (10 * losses) - (10 * doubleOuts) + pointsFor', 'score', 'DESC', 'pointsAgainst', 'ASC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Losses', 'losses', 'Doubles', 'doubleOuts', 'Points For', 'pointsFor', 'Score', 'score'),
(27, 'Cut & Deduction', 3, 28, 'Each cut is input with a score and deduction', 'PureScore', 'PureScore', 'pointsFor', 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'Flat Score', 3, 2, 'Only a score value is input for each cut', 'PureScore', 'PureScore', NULL, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 'Longpoint Deviation', 4, 5, '== Ranking ====\r\nIndicator Score\r\n\r\n== Indicator Score ====\r\nScore = Sum([Tournament Scores]) - Standard Deviation([Tournament Scores])\r\n\r\nComponent Tournament Scores:\r\n[Tournament Score] = [Base Point Value] * ([Number of Entries] - (place -1))/[Number of Entries]\r\n\r\n', NULL, NULL, '#LpDeviation', 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Component Scores', 'pointsFor', 'Inconsistency Penalty', '-pointsAgainst', 'Score', 'score', NULL, NULL, NULL, NULL),
(30, 'LP Hit Ratio', 2, 7, '== Ranking ====\r\nIndicator Score\r\n1st Tiebreaker: Doubles [Lowest]\r\n2nd Tiebreaker: High Wins [Highest]\r\n3rd Tiebreaker: Time Hit [Lowest]\r\n\r\n== Indicator Score ====\r\n[Absolute Points For + Win Bonus]/[Total Times Hit]\r\n\r\nAbsolute Points For\r\nPoints scored *before* the afterblow is deducted.\r\n\r\nWin Bonus\r\n2 Points for every win\r\n\r\nTotal Times Hit\r\n[# Clean Hits Against] + [# Doubles] + [# Afterblows Hit With]\r\n\r\n\r\n', NULL, NULL, 'case \n	when (hitsAgainst + afterblowsAgainst + doubles) > 0 then\n		(AbsPointsAwarded + 2 * wins) /  (hitsAgainst + afterblowsAgainst + doubles)\n	else\n		9001\nend', 'score', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', NULL, NULL, 'Wins', 'wins', 'Target Points', 'absPointsAwarded', 'Total Times Hit', 'hitsAgainst + afterblowsAgainst + doubles', 'Score', 'score', NULL, NULL),
(31, 'OSS', 2, 3, '== Ranking ====\r\nScore\r\n1st Tiebreaker: Points Against\r\n\r\n== Indicator Score ====\r\nwins*2 - ties - losses\r\n', NULL, NULL, '(wins*2) - ties - losses', 'score', 'DESC', 'AbsPointsAgainst', 'ASC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Losses', 'losses', 'Ties', 'ties', 'Points Against', 'AbsPointsAgainst', 'Score', 'score'),
(32, 'Points Remaining', 2, 7, '== Ranking ====\r\nIndicator Score\r\n1st Tiebreaker: Points For\r\n2nd Tiebreaker: Number of Doubles\r\n\r\n== Indicator Score ====\r\nSum of remaining points from each match (dependent on what the base point value is set at)', NULL, '', '(basePointValue * matches) - AbsPointsAgainst - penaltiesAgainst', 'score', 'DESC', 'pointsFor + penaltiesAgainst', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Points Remaining', 'score', 'Points For', 'pointsFor + penaltiesAgainst', 'Doubles', 'doubles', NULL, NULL, NULL, NULL),
(33, 'Fairfax', 2, 1, '== Ranking ====\r\nPoints For\r\n1st Tiebreaker: Wins\r\n2nd Tiebreaker: Doubles\r\n3rd Tiebreaker: Points Against', NULL, NULL, '0', 'pointsFor', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', 'pointsAgainst', 'ASC', 'Points For', 'pointsFor', 'Wins', 'wins', 'Doubles', 'doubles', 'Points Against', 'pointsAgainst', NULL, NULL),
(34, 'FoB Dagger', 2, 1, '== Ranking ====\r\nWins [Highest] \r\n1st Tiebreaker: # Control Points [Highest]\r\n2nd Tiebreaker: Points Against [Lowest]\r\n3rd Tiebreaker: Points For [Highest]\r\n\r\n*this is points after the net points for afterblow is taken into account.', NULL, NULL, '#FobDagger', 'wins', 'DESC', 'score', 'DESC', 'pointsAgainst', 'ASC', 'pointsFor', 'DESC', 'Wins', 'wins', '# Control Points', 'score', 'Points Against', 'pointsAgainst', 'Points For', 'pointsFor', NULL, NULL),
(35, 'Wins and Points', 2, 44, '== Ranking ====\r\nWins\r\n1st Tiebreaker: Points For\r\n2nd Tiebreaker: Points Against\r\n3rd Tiebreaker: Doubles', NULL, NULL, '0', 'wins', 'DESC', 'pointsFor', 'DESC', 'pointsAgainst', 'DESC', 'doubles', 'ASC', 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Doubles', 'doubles', NULL, NULL),
(36, 'Placing Countdown', 4, 1, '== Ranking ==\r\nIndicator Score\r\n\r\n== Indicator Score ====\r\nGo through each component tournament and award points in descending order, starting from the specified Base Point Value.\r\n\r\nExample:\r\nBase Point Value = 20 points.\r\n1st Place: 20 pts\r\n2nd Place: 19 pts\r\n3rd Place: 18 pts\r\netc...', NULL, NULL, '#PlacingCountdown', 'score', 'DESC', 'pointsFor', 'DESC', NULL, NULL, NULL, NULL, '# of Tournaments', 'round((pointsFor/basePointValue),2)', 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL),
(37, 'Placing Percentage', 4, 5, '== Ranking ====\r\nIndicator Score\r\n\r\n== Indicator Score ====\r\nScore = Sum([Tournament Scores])\r\n\r\nComponent Tournament Scores:\r\n[Tournament Score] = [Base Point Value] * ([Number of Entries] - (place -1))/[Number of Entries]\r\n\r\n', NULL, NULL, '#PlacingPercent', 'score', 'DESC', 'pointsFor', 'ASC', NULL, NULL, NULL, NULL, '# of Tournaments', 'pointsFor', 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'Hit and Don\'t Double', 2, 15, '== Ranking ====\r\nMost Hits\r\n1st Tiebreaker: Doubles [Lowest]\r\n2nd Tiebreaker: Most Wins [Highest]\r\n3rd Tiebreaker: Hits Against [Lowest]', NULL, NULL, '0', 'hitsFor', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', 'hitsAgainst', 'ASC', 'Hits For', 'hitsFor', 'Doubles', 'doubles', 'Wins', 'wins', 'Hits Against', 'hitsAgainst', NULL, NULL),
(39, 'Franklin 2014 - Rev Score', 2, 1, 'Modified Franklin 2014 to work with reverse score matches.\r\n\r\n== Ranking ====\r\n1) Indicator Score [Highest]\r\n2) Wins [Highest]\r\n3) Doubles [Lowest]\r\n4) Hits Against [Lowest]\r\n\r\n== Indicator Score ====\r\n + (5 * [Wins])\r\n +[Points Remaining]\r\n -[Opponent\'s Points Remaining]\r\n -(Doubles Penalty)\r\n\r\nDoubles Penalty\r\n1 Double -> 1 = 1\r\n2 Doubles -> 1+2 = 3\r\n3 Doubles -> 1+2+3 = 6 etc...\r\n', NULL, NULL, '(5*wins) + pointsFor - pointsAgainst + penaltiesAgainstOpponents - ((doubles * (doubles+1))/2)', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Points Remaining', '(basePointValue * matches) - pointsAgainst - penaltiesAgainst', 'Opponent Points Remaining', '(basePointValue * matches) - pointsFor - penaltiesAgainstOpponents - penaltiesAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(40, 'Donnybrook', 2, 12, '== Ranking ====\r\nWins\r\n1st Tiebreaker: Doubles [lowest]\r\n2nd Tiebreaker: Points Against [lowest]\r\n3rd Tiebreaker: Points For [highest]\r\n\r\n*points awarded after the afterblow deduction is taken into account', NULL, NULL, '0', 'wins', 'DESC', 'doubles', 'ASC', 'pointsAgainst', 'ASC', 'pointsFor', 'DESC', 'Wins', 'wins', 'Doubles', 'doubles', 'Points Against', 'pointsAgainst', 'Points For', 'pointsFor', NULL, NULL),
(41, 'CSEN Nazionale - Scherma Storica', 2, 4, '== Ranking ====\r\nNumber of Wins [Highest]\r\n1st Tiebreaker: # Double Outs [Lowest]\r\n2nd Tiebreaker: Indicator Score [Highest]\r\n3rd Tiebreaker: Total Hits Received [Lowest]\r\n\r\n== Indicator Score ====\r\npointsFor - pointsAgainst', NULL, NULL, 'pointsFor - pointsAgainst', 'wins', 'DESC', 'doubleOuts', 'DESC', 'score', 'DESC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Double Outs', 'doubleOuts', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', NULL, NULL),
(42, 'SingleHit', 2, 2, '== Ranking =====\nIndicator Score \n1st Tiebreaker: Lowest total-times-hit \n2nd Tiebreaker: Lowest doubles\n\n== Indicator Score ====\nPoints For / (Points Against + Doubles) ', NULL, NULL, 'IF((pointsAgainst + doubles) != 0, pointsFor / (pointsAgainst + doubles), 9001)', 'score', 'DESC', 'pointsAgainst + doubles', 'ASC', 'doubles', 'ASC', NULL, NULL, 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Doubles', 'doubles', 'Score', 'score', NULL, NULL),
(43, 'PHO Match Points', 2, 4, '(aka Purpleheart Match Points)\r\n\r\n== Ranking ====\r\nIndicator Score\r\n1st Tiebreaker: Wins\r\n2nd Tiebreaker: Points Against\r\n3rd Tiebreaker: Points For\r\n\r\n*absolute value of points awarded without afterblow deductions\r\n\r\n== Indicator Score ==== \r\n(9 * wins) + (6 * ties) + (3 * losses) - [Doubles Penalty]\r\n\r\nDoubles Penalty: 1 point for every double hit in a match after the first (first double of a match does not impact score).\r\nThe number of doubles on the standings page is NOT the doubles penalty, it is the total number. Some of these are not factored into the Match Points.\r\n\r\n', NULL, NULL, '#PhoMatchPoints', 'score', 'DESC', 'wins', 'DESC', 'AbsPointsAgainst', 'ASC', 'AbsPointsFor', 'DESC', 'Wins', 'wins', 'Ties', 'ties', 'Losses', 'losses', 'Doubles', 'doubles', 'Match Points', 'score'),
(44, 'Schnegel Score', 2, 6, '== Ranking ====\r\nIndicator Score\r\n1st Tiebreaker: Wins\r\n2nd Tiebreaker: Points For\r\n\r\n== Indicator Score ====\r\nIf WIN: Score +10\r\nIf TIE: Score +pointsAwarded\r\n\r\n', NULL, NULL, '#Schnegel', 'score', 'DESC', 'wins', 'DESC', 'pointsFor', 'DESC', NULL, NULL, 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Score', 'score', NULL, NULL),
(45, 'AG Internal', 2, 0, '== Ranking ====\r\n1) Indicator Score\r\n1st Tiebreaker: Least times hit\r\n2nd Tiebreaker: Most hits landed\r\n\r\n== Indicator Score ====\r\n+1 for every deep target hit\r\n-1 for every time hit (on any target)\r\n\r\n', NULL, NULL, 'pointsFor - hitsFor - hitsAgainst', 'score', 'DESC', 'hitsAgainst', 'ASC', 'hitsFor', 'DESC', NULL, NULL, 'Deep Target Hits', 'pointsFor - hitsFor', 'Times Hit', 'hitsAgainst', 'Score', 'score', NULL, NULL, NULL, NULL),
(46, 'Franklin 2014.3', 2, 33, '== Ranking ====\r\n1) Indicator Score\r\n2) Wins\r\n3) Doubles\r\n4) Hits Against\r\n\r\n== Indicator Score ====\r\n +[Points For]\r\n +(3 * [Wins])\r\n -[Points Against]\r\n -(Doubles Penalty)\r\n\r\nDoubles Penalty\r\n1 Double -> 0 = 0\r\n2 Doubles -> 0+1 = 1\r\n3 Doubles -> 0+1+2 = 3 etc...', NULL, NULL, '(3*wins) + pointsFor - pointsAgainst - ((doubles * (doubles-1))/2)', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(47, 'Wins - Hits Against', 2, 7, '== Ranking ====\r\nWins\r\n1st Tiebreaker: Points Against\r\n2nd Tiebreaker: Points For\r\n\r\n*absolute value of points before afterblow deduction is applied.', NULL, NULL, 'wins', 'wins', 'DESC', 'AbsPointsAgainst', 'ASC', 'AbsPointsFor', 'desc', NULL, NULL, 'Wins', 'wins', 'Points Against', 'AbsPointsAgainst', 'Points For', 'AbsPointsFor', NULL, NULL, NULL, NULL),
(48, 'Dutch Match Points', 2, 2, '== Ranking ====\r\nIndicator Score\r\n1st Tiebreaker: # of times hit\r\n2nd Tiebreaker: # hits delivered\r\n3rd Tiebreaker: # of penalties\r\n\r\n== Indicator Score ==== \r\n(9 * wins) + (6 * ties) + (3 * losses) - [Doubles Penalty]\r\n\r\nDoubles Penalty: 1 point for every double hit in a match after the first (first double of a match does not impact score).\r\nThe number of doubles on the standings page is NOT the doubles penalty, it is the total number. Some of these are not factored into the Match Points.\r\n\r\n', NULL, NULL, '#PhoMatchPoints', 'score', 'DESC', 'pointsAgainst', 'ASC', 'pointsFor', 'DESC', 'numPenalties', 'ASC', 'Wins', 'wins', 'Ties', 'ties', 'Losses', 'losses', 'Doubles', 'doubles', 'Match Points', 'score'),
(49, 'Sofia', 2, 3, '== Ranking ====\r\nWins\r\n1st Tiebreaker: Indicator Score\r\n2nd Tiebreaker: Doubles\r\n\r\n== Indicator Score ====\r\npointsFor - pointsAgainst', NULL, NULL, 'pointsFor - pointsAgainst', 'wins', 'DESC', 'score', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', '+/-', 'score', 'Doubles', 'doubles'),
(50, 'Plus/Minus | Wins', 2, 5, '== Ranking ====\r\nIndicator Score\r\n1st Tiebreaker: Wins\r\n\r\n== Indicator Score ====\r\npointsFor - pointsAgainst\r\n\r\n', NULL, NULL, 'pointsFor - pointsAgainst', 'score', 'DESC', 'wins', 'DESC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Points +/-', 'score', NULL, NULL),
(51, 'Wessex League Standings', 4, 12, '== Ranking ====\r\nIndicator Score\r\n\r\n== Indicator Score ====\r\nGain points based on every tournament placing.\r\n1st = 22 pts\r\n2nd = 18 pts\r\n3rd = 14 pts\r\n4th = 10 pts\r\n5th-8th = 6 pts\r\n9th-16th = 3 pts\r\n17th+ = 1 pts\r\n\r\n', NULL, NULL, '#WessexLeagueStandings', 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Events Attended', 'pointsFor', 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL),
(52, 'Midwinter', 2, 3, 'Midwinter ====\nIndicator Score\n1st Tiebreaker: Doubles\n2nd Tiebreaker: Hits For\n3rd Tiebreaker: Hits Against\n\n== Indicator Score ====\n+ 2 * Wins\n+ # of matches w/ First Hit\n+ # of matches blanking opponent\n- # of matches w/ Doubles', NULL, NULL, '#MidWinter', 'score', 'DESC', 'doubles', 'ASC', 'hitsFor', 'DESC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Losses', 'losses', 'Hits For', 'hitsFor', 'Doubles', 'doubles', 'Score', 'score'),
(53, 'AAA (Don\'t Get Hit!)', 2, 2, 'Ranking:\r\nFewest Points Against\r\n\r\n1st Tiebreaker: Points Scored\r\n2nd Tiebreaker: Wins', NULL, NULL, 'pointsAgainst', 'pointsAgainst', 'ASC', 'pointsFor', 'DESC', 'wins', 'DESC', NULL, NULL, 'Points Against', 'pointsAgainst', 'Points For', 'pointsFor', 'Wins', 'wins', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `systemRoster`
--

CREATE TABLE `systemRoster` (
  `systemRosterID` int(10) UNSIGNED NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `middleName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `schoolID` int(10) UNSIGNED DEFAULT NULL,
  `HemaRatingsID` int(10) UNSIGNED DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `rosterCountry` varchar(255) DEFAULT NULL,
  `rosterProvince` varchar(255) DEFAULT NULL,
  `rosterCity` varchar(255) DEFAULT NULL,
  `eMail` varchar(255) DEFAULT NULL,
  `publicNotes` text,
  `privateNotes` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemRosterNotDuplicate`
--

CREATE TABLE `systemRosterNotDuplicate` (
  `tableID` int(10) UNSIGNED NOT NULL,
  `rosterID1` int(10) UNSIGNED NOT NULL,
  `rosterID2` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemSchools`
--

CREATE TABLE `systemSchools` (
  `schoolID` int(10) UNSIGNED NOT NULL,
  `schoolFullName` varchar(255) NOT NULL,
  `schoolShortName` varchar(255) DEFAULT NULL,
  `schoolBranch` varchar(255) DEFAULT NULL,
  `schoolAbbreviation` varchar(255) DEFAULT NULL,
  `schoolCity` varchar(255) DEFAULT NULL,
  `schoolProvince` varchar(255) DEFAULT NULL,
  `countryIso2` varchar(2) DEFAULT NULL,
  `schoolAddress` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemSponsors`
--

CREATE TABLE `systemSponsors` (
  `sponsorID` int(10) UNSIGNED NOT NULL,
  `sponsorName` varchar(255) NOT NULL,
  `sponsorType` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemTournaments`
--

CREATE TABLE `systemTournaments` (
  `tournamentTypeID` int(10) UNSIGNED NOT NULL,
  `tournamentTypeMeta` varchar(255) DEFAULT NULL,
  `tournamentType` varchar(255) DEFAULT NULL,
  `Pool_Bracket` tinyint(1) NOT NULL DEFAULT '1',
  `Pool_Sets` tinyint(1) NOT NULL DEFAULT '1',
  `Scored_Event` tinyint(1) NOT NULL DEFAULT '1',
  `numberOfInstances` int(10) UNSIGNED DEFAULT NULL,
  `description` text,
  `functionName` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemTournaments`
--

INSERT INTO `systemTournaments` (`tournamentTypeID`, `tournamentTypeMeta`, `tournamentType`, `Pool_Bracket`, `Pool_Sets`, `Scored_Event`, `numberOfInstances`, `description`, `functionName`) VALUES
(1, 'weapon', 'Longsword', 1, 1, 1, 386, NULL, NULL),
(2, 'weapon', 'Messer', 1, 1, 1, 11, NULL, NULL),
(3, 'weapon', 'Sword and Buckler', 1, 1, 1, 83, NULL, NULL),
(5, 'weapon', 'Singlestick', 1, 1, 1, 39, NULL, NULL),
(6, 'weapon', 'Dagger', 1, 1, 1, 21, NULL, NULL),
(7, 'weapon', 'Saber', 1, 1, 1, 89, NULL, NULL),
(8, 'weapon', 'Smallsword', 1, 1, 1, 17, NULL, NULL),
(9, 'weapon', 'Grappling', 1, 1, 1, 1, NULL, NULL),
(10, 'weapon', 'Multiple Weapon', 1, 1, 1, 35, NULL, NULL),
(11, 'prefix', NULL, 1, 1, 1, 0, NULL, NULL),
(12, 'prefix', 'Advanced', 1, 1, 1, 0, NULL, NULL),
(13, 'prefix', 'Intermediate', 1, 1, 1, 0, NULL, NULL),
(14, 'prefix', 'Beginners', 1, 1, 1, 0, NULL, NULL),
(15, 'prefix', 'Invitational', 1, 1, 1, 0, NULL, NULL),
(16, 'prefix', 'Novice', 1, 1, 1, 0, NULL, NULL),
(17, 'ranking', 'Franklin 2014', 1, 0, 0, 0, NULL, NULL),
(18, 'ranking', 'N / A', 0, 0, 0, 0, NULL, NULL),
(19, 'gender', NULL, 1, 1, 1, 0, NULL, NULL),
(20, 'gender', 'Open', 1, 1, 1, 0, NULL, NULL),
(21, 'gender', 'Women\'s', 1, 1, 1, 0, NULL, NULL),
(22, 'gender', 'Men\'s', 1, 1, 1, 0, NULL, NULL),
(23, 'material', NULL, 1, 1, 1, 0, NULL, NULL),
(24, 'material', 'Steel', 1, 1, 1, 0, NULL, NULL),
(25, 'material', 'Synthetic', 1, 1, 1, 0, NULL, NULL),
(26, 'material', 'Rattan', 1, 1, 1, 0, NULL, NULL),
(27, 'material', 'Mixed', 1, 1, 1, 0, NULL, NULL),
(28, 'ranking', 'Franklin 2016', 1, 0, 0, 0, NULL, NULL),
(29, 'ranking', '2 Pool Winners', 1, 0, 0, 0, NULL, NULL),
(30, 'ranking', 'Total Points Scored', 1, 0, 0, 0, NULL, NULL),
(31, 'ranking', 'CC Invitation 2016', 1, 0, 0, 0, NULL, NULL),
(32, 'weapon', 'Longsword Cutting', 1, 1, 1, 43, NULL, NULL),
(33, 'ranking', 'Results Only', 0, 0, 0, 0, NULL, NULL),
(34, 'weapon', 'Glima', 1, 1, 1, 7, NULL, NULL),
(35, 'weapon', 'Rotella', 1, 1, 1, 2, NULL, NULL),
(36, 'prefix', 'Lightweight', 1, 1, 1, 0, NULL, NULL),
(37, 'prefix', 'Middleweight', 1, 1, 1, 0, NULL, NULL),
(38, 'prefix', 'Heavyweight', 1, 1, 1, 0, NULL, NULL),
(39, 'weapon', 'Staff', 1, 1, 1, 1, NULL, NULL),
(44, 'ranking', 'FNY 2017', 0, 1, 0, NULL, NULL, NULL),
(45, 'ranking', 'Eurofest 2017', 1, 0, 0, NULL, NULL, NULL),
(46, 'ranking', 'RMS Cutting', 0, 0, 1, NULL, NULL, 'RMScutting'),
(47, 'weapon', 'Cutting Quallification', 1, 1, 1, 1, NULL, NULL),
(48, 'weapon', 'Mixed Short Sword', 1, 1, 1, 6, NULL, NULL),
(49, 'weapon', 'Mixed Knife', 1, 1, 1, 1, NULL, NULL),
(50, 'ranking', 'Deduction Based', 0, 0, 1, NULL, NULL, 'DeductionBased'),
(51, 'weapon', 'Backsword', 1, 1, 1, 1, NULL, NULL),
(52, 'weapon', 'Broadsword', 1, 1, 1, 4, NULL, NULL),
(53, 'weapon', 'Single Handed Cutting', 1, 1, 1, 4, NULL, NULL),
(54, 'weapon', 'Dane Axe', 1, 1, 1, 1, NULL, NULL),
(55, 'weapon', 'Bowie Knife', 1, 1, 1, 2, NULL, NULL),
(56, 'weapon', 'Sidesword', 1, 1, 1, 23, NULL, NULL),
(57, 'material', 'Gekkenschwert', 1, 1, 1, NULL, NULL, NULL),
(58, 'weapon', 'Two Handed Sword', 1, 1, 1, 2, NULL, NULL),
(59, 'weapon', 'Single Sword', 1, 1, 1, 11, NULL, NULL),
(60, 'weapon', 'Trifecta', 1, 1, 1, 1, NULL, NULL),
(61, 'weapon', 'Cutting', 1, 1, 1, 14, NULL, NULL),
(62, 'weapon', 'Forms', 1, 1, 1, 2, NULL, NULL),
(63, 'prefix', 'Finals', 1, 1, 1, NULL, NULL, NULL),
(64, 'prefix', 'Pools', 1, 1, 1, NULL, NULL, NULL),
(65, 'prefix', 'DO NOT TOUCH', 1, 1, 1, NULL, NULL, NULL),
(66, 'material', 'DO NOT TOUCH', 1, 1, 1, NULL, NULL, NULL),
(67, 'weapon', 'Spear', 1, 1, 1, 6, NULL, NULL),
(68, 'weapon', 'Armored', 1, 1, 1, 8, NULL, NULL),
(69, 'weapon', 'Passage At Arms', 1, 1, 1, 1, NULL, NULL),
(70, 'weapon', 'Ringen', 1, 1, 1, 12, NULL, NULL),
(71, 'weapon', 'Longsword Triathlon', 1, 1, 1, 1, NULL, NULL),
(72, 'weapon', 'Messer Triathlon', 1, 1, 1, 1, NULL, NULL),
(73, 'weapon', 'Man-At-Armsâ€™ Triathlon', 1, 1, 1, 1, NULL, NULL),
(74, 'weapon', 'Liechtenauerâ€™s Pentathlon', 1, 1, 1, 2, NULL, NULL),
(75, 'prefix', 'Light', 1, 1, 1, NULL, NULL, NULL),
(76, 'prefix', 'Heavy', 1, 1, 1, NULL, NULL, NULL),
(77, 'weapon', 'Dussack', 1, 1, 1, 7, NULL, NULL),
(78, 'prefix', 'Light-Heavyweight', 1, 1, 1, NULL, NULL, NULL),
(79, 'prefix', 'Openweight', 1, 1, 1, NULL, NULL, NULL),
(80, 'prefix', 'Tier 2', 1, 1, 1, NULL, NULL, NULL),
(81, 'weapon', 'Rapier & Dagger', 1, 1, 1, 49, NULL, NULL),
(82, 'prefix', 'U35', 1, 1, 1, NULL, NULL, NULL),
(83, 'prefix', 'Staff Training', 1, 1, 1, NULL, NULL, NULL),
(84, 'weapon', 'Tetrathlon', 1, 1, 1, 1, NULL, NULL),
(85, 'prefix', 'Blue', 1, 1, 1, NULL, NULL, NULL),
(86, 'prefix', 'Red', 1, 1, 1, NULL, NULL, NULL),
(87, 'weapon', 'Sidesword & Dagger', 1, 1, 1, 3, NULL, NULL),
(88, 'weapon', 'Sidesword & Rotella', 1, 1, 1, 2, NULL, NULL),
(89, 'material', 'Padded', 1, 1, 1, NULL, NULL, NULL),
(90, 'weapon', 'Courtsword', 1, 1, 1, NULL, NULL, NULL),
(91, 'weapon', 'Rapier (Single)', 1, 1, 1, 42, NULL, NULL),
(92, 'weapon', 'Rapier (Optional Dagger)', 1, 1, 1, 15, NULL, NULL),
(93, 'weapon', 'Rapier (Hybrid Offhand)', 1, 1, 1, 12, NULL, NULL),
(94, 'weapon', 'Rapier (Offhand Unknown)', 1, 1, 1, 10, NULL, NULL),
(95, 'prefix', 'Tier A', 1, 1, 1, NULL, NULL, NULL),
(96, 'prefix', 'Tier B', 1, 1, 1, NULL, NULL, NULL),
(97, 'weapon', '1-H Medieval Sword', 1, 1, 1, 4, NULL, NULL),
(98, 'prefix', 'Team', 1, 1, 1, NULL, NULL, NULL),
(99, 'prefix', 'Over 45', 1, 1, 1, NULL, NULL, NULL),
(100, 'prefix', 'Senior', 1, 1, 1, NULL, NULL, NULL),
(101, 'weapon', 'Aggregate School Award', 1, 1, 1, 1, NULL, NULL),
(102, 'prefix', 'Franco Belgian', 1, 1, 1, NULL, NULL, NULL),
(103, 'prefix', 'Conventional', 1, 1, 1, NULL, NULL, NULL),
(104, 'weapon', 'Kriegsmesser ', 1, 1, 1, 1, NULL, NULL),
(105, 'prefix', 'Tier 1', 1, 1, 1, NULL, NULL, NULL),
(106, 'prefix', 'Tier 3', 1, 1, 1, NULL, NULL, NULL),
(107, 'prefix', 'Tier C', 1, 1, 1, NULL, NULL, NULL),
(108, 'weapon', 'Sidesword and Buckler', 1, 1, 1, 1, NULL, NULL),
(109, 'gender', 'Women\'s+', 1, 1, 1, NULL, NULL, NULL),
(110, 'prefix', 'Youth', 1, 1, 1, NULL, NULL, NULL),
(111, 'prefix', 'U18', 1, 1, 1, NULL, NULL, NULL),
(112, 'weapon', 'Experimental (TBD)', 1, 1, 1, 1, NULL, NULL),
(113, 'weapon', 'Relay', 1, 1, 1, NULL, NULL, NULL),
(114, 'prefix', 'Team Relay', 1, 1, 1, NULL, NULL, NULL),
(115, 'weapon', 'Arming Sword Cutting', 1, 1, 1, 2, NULL, NULL),
(116, 'weapon', 'Mixed Weapon', 1, 1, 1, 2, NULL, NULL),
(117, 'prefix', 'Relay', 1, 1, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `systemUserEvents`
--

CREATE TABLE `systemUserEvents` (
  `userTournamentID` int(11) NOT NULL,
  `userID` int(10) UNSIGNED NOT NULL,
  `eventID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemUsers`
--

CREATE TABLE `systemUsers` (
  `userID` int(10) UNSIGNED NOT NULL,
  `userName` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `userEmail` varchar(255) NOT NULL,
  `EVENT_VIDEO` tinyint(1) NOT NULL DEFAULT '0',
  `EVENT_SCOREKEEP` tinyint(1) NOT NULL DEFAULT '0',
  `EVENT_MANAGEMENT` tinyint(1) NOT NULL DEFAULT '0',
  `SOFTWARE_EVENT_SWITCHING` tinyint(1) NOT NULL DEFAULT '0',
  `SOFTWARE_ASSIST` tinyint(1) NOT NULL DEFAULT '0',
  `SOFTWARE_ADMIN` tinyint(1) NOT NULL DEFAULT '0',
  `STATS_EVENT` tinyint(1) NOT NULL DEFAULT '0',
  `STATS_ALL` tinyint(1) NOT NULL DEFAULT '0',
  `VIEW_HIDDEN` tinyint(1) NOT NULL DEFAULT '0',
  `VIEW_SETTINGS` tinyint(1) NOT NULL DEFAULT '0',
  `VIEW_EMAIL` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `eventAttacks`
--
ALTER TABLE `eventAttacks`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `tournamentID` (`tournamentID`,`attackTarget`,`attackType`),
  ADD KEY `attackTarget` (`attackTarget`),
  ADD KEY `attackType` (`attackType`),
  ADD KEY `attackPrefix` (`attackPrefix`);

--
-- Indexes for table `eventAttributes`
--
ALTER TABLE `eventAttributes`
  ADD PRIMARY KEY (`attributeID`),
  ADD KEY `tournamentID` (`tournamentID`);

--
-- Indexes for table `eventBurgeeComponents`
--
ALTER TABLE `eventBurgeeComponents`
  ADD PRIMARY KEY (`burgeeComponentID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `burgeeID` (`burgeeID`);

--
-- Indexes for table `eventBurgeePlacings`
--
ALTER TABLE `eventBurgeePlacings`
  ADD PRIMARY KEY (`burgeePlaceID`),
  ADD KEY `tournamentID` (`burgeeID`),
  ADD KEY `schoolID` (`schoolID`),
  ADD KEY `rosterID` (`rosterID`),
  ADD KEY `tournamentID_2` (`tournamentID`);

--
-- Indexes for table `eventBurgees`
--
ALTER TABLE `eventBurgees`
  ADD PRIMARY KEY (`burgeeID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `schoolTournamentRankingID` (`burgeeRankingID`);

--
-- Indexes for table `eventCutStandards`
--
ALTER TABLE `eventCutStandards`
  ADD PRIMARY KEY (`qualID`),
  ADD KEY `tournamentID` (`tournamentID`,`standardID`);

--
-- Indexes for table `eventDefaults`
--
ALTER TABLE `eventDefaults`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `color1ID` (`color1ID`),
  ADD KEY `color2ID` (`color2ID`);

--
-- Indexes for table `eventDescriptions`
--
ALTER TABLE `eventDescriptions`
  ADD PRIMARY KEY (`eventDescriptionID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `eventExchanges`
--
ALTER TABLE `eventExchanges`
  ADD PRIMARY KEY (`exchangeID`),
  ADD KEY `matchID` (`matchID`),
  ADD KEY `scorringID` (`scoringID`),
  ADD KEY `recievingID` (`receivingID`),
  ADD KEY `refPrefix` (`refPrefix`),
  ADD KEY `refTarget` (`refTarget`),
  ADD KEY `refType` (`refType`);

--
-- Indexes for table `eventGroupRankings`
--
ALTER TABLE `eventGroupRankings`
  ADD PRIMARY KEY (`groupRankingID`),
  ADD KEY `groupID` (`groupID`);

--
-- Indexes for table `eventGroupRoster`
--
ALTER TABLE `eventGroupRoster`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `rosterID` (`rosterID`),
  ADD KEY `groupID` (`groupID`),
  ADD KEY `tournamentTableID` (`tournamentTableID`);

--
-- Indexes for table `eventGroups`
--
ALTER TABLE `eventGroups`
  ADD PRIMARY KEY (`groupID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `locationID` (`locationID`);

--
-- Indexes for table `eventHemaRatingsInfo`
--
ALTER TABLE `eventHemaRatingsInfo`
  ADD PRIMARY KEY (`hemaRatingInfoID`),
  ADD KEY `hemaRatingInfoID` (`hemaRatingInfoID`);

--
-- Indexes for table `eventIgnores`
--
ALTER TABLE `eventIgnores`
  ADD PRIMARY KEY (`ignoreID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `rosterID` (`rosterID`);

--
-- Indexes for table `eventMatches`
--
ALTER TABLE `eventMatches`
  ADD PRIMARY KEY (`matchID`),
  ADD KEY `groupID` (`groupID`),
  ADD KEY `fighter1ID` (`fighter1ID`),
  ADD KEY `fighter2ID` (`fighter2ID`),
  ADD KEY `winnerID` (`winnerID`),
  ADD KEY `placeholderMatchID` (`placeholderMatchID`);

--
-- Indexes for table `eventMatchOptions`
--
ALTER TABLE `eventMatchOptions`
  ADD PRIMARY KEY (`matchOptionID`),
  ADD KEY `matchID` (`matchID`),
  ADD KEY `optionID` (`optionID`);

--
-- Indexes for table `eventPlacings`
--
ALTER TABLE `eventPlacings`
  ADD PRIMARY KEY (`placeID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `rosterID` (`rosterID`);

--
-- Indexes for table `eventPublication`
--
ALTER TABLE `eventPublication`
  ADD PRIMARY KEY (`publicationID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `eventRatings`
--
ALTER TABLE `eventRatings`
  ADD PRIMARY KEY (`ratingID`),
  ADD KEY `tournamentRosterID` (`tournamentRosterID`);

--
-- Indexes for table `eventRoster`
--
ALTER TABLE `eventRoster`
  ADD PRIMARY KEY (`rosterID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `schoolID` (`schoolID`),
  ADD KEY `rosterID` (`systemRosterID`);

--
-- Indexes for table `eventRosterAdditional`
--
ALTER TABLE `eventRosterAdditional`
  ADD PRIMARY KEY (`additionalRosterID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `eventRules`
--
ALTER TABLE `eventRules`
  ADD PRIMARY KEY (`rulesID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `eventRulesLinks`
--
ALTER TABLE `eventRulesLinks`
  ADD PRIMARY KEY (`rulesLinkID`),
  ADD KEY `rulesID` (`rulesID`),
  ADD KEY `tournamentID` (`tournamentID`);

--
-- Indexes for table `eventScoresheets`
--
ALTER TABLE `eventScoresheets`
  ADD PRIMARY KEY (`scoresheetID`);

--
-- Indexes for table `eventSettings`
--
ALTER TABLE `eventSettings`
  ADD PRIMARY KEY (`eventSettingID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `eventSponsors`
--
ALTER TABLE `eventSponsors`
  ADD PRIMARY KEY (`eventSponsorID`),
  ADD KEY `sponsorID` (`sponsorID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `eventStandings`
--
ALTER TABLE `eventStandings`
  ADD PRIMARY KEY (`standingID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `groupID` (`groupID`),
  ADD KEY `rosterID` (`rosterID`);

--
-- Indexes for table `eventTeamRoster`
--
ALTER TABLE `eventTeamRoster`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `teamID` (`teamID`,`rosterID`),
  ADD KEY `rosterID` (`rosterID`),
  ADD KEY `tournamentRosterID` (`tournamentRosterID`);

--
-- Indexes for table `eventTournamentCompGroupItems`
--
ALTER TABLE `eventTournamentCompGroupItems`
  ADD PRIMARY KEY (`componentGroupItemID`),
  ADD UNIQUE KEY `componentGroupID_2` (`componentGroupID`,`tournamentComponentID`),
  ADD KEY `componentGroupID` (`componentGroupID`),
  ADD KEY `tournamentComponentID` (`tournamentComponentID`);

--
-- Indexes for table `eventTournamentCompGroups`
--
ALTER TABLE `eventTournamentCompGroups`
  ADD PRIMARY KEY (`componentGroupID`),
  ADD KEY `metaTournamentID` (`metaTournamentID`);

--
-- Indexes for table `eventTournamentComponents`
--
ALTER TABLE `eventTournamentComponents`
  ADD PRIMARY KEY (`tournamentComponentID`),
  ADD KEY `tournamentID` (`metaTournamentID`),
  ADD KEY `componentTournamentID` (`componentTournamentID`);

--
-- Indexes for table `eventTournamentOptions`
--
ALTER TABLE `eventTournamentOptions`
  ADD PRIMARY KEY (`tournamentOptionID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `optionID` (`optionID`);

--
-- Indexes for table `eventTournamentOrder`
--
ALTER TABLE `eventTournamentOrder`
  ADD PRIMARY KEY (`tournamentOrderID`),
  ADD KEY `tournamentID` (`tournamentID`);

--
-- Indexes for table `eventTournamentRoster`
--
ALTER TABLE `eventTournamentRoster`
  ADD PRIMARY KEY (`tournamentRosterID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `eventRosterID` (`rosterID`);

--
-- Indexes for table `eventTournaments`
--
ALTER TABLE `eventTournaments`
  ADD PRIMARY KEY (`tournamentID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `tournamentWeaponID` (`tournamentWeaponID`),
  ADD KEY `tournamentPrefixID` (`tournamentPrefixID`),
  ADD KEY `tournamentSuffixID` (`tournamentSuffixID`),
  ADD KEY `tournamentGenderID` (`tournamentGenderID`),
  ADD KEY `tournamentMaterialID` (`tournamentMaterialID`),
  ADD KEY `tournamentRankingID` (`tournamentRankingID`),
  ADD KEY `doubleTypeID` (`doubleTypeID`),
  ADD KEY `elimTypeID` (`formatID`),
  ADD KEY `color1ID` (`color1ID`),
  ADD KEY `color2ID` (`color2ID`),
  ADD KEY `tournamentElimID` (`formatID`);

--
-- Indexes for table `eventVideo`
--
ALTER TABLE `eventVideo`
  ADD PRIMARY KEY (`videoID`),
  ADD KEY `matchID` (`matchID`);

--
-- Indexes for table `eventVideoStreams`
--
ALTER TABLE `eventVideoStreams`
  ADD PRIMARY KEY (`streamID`),
  ADD KEY `videoID` (`videoID`),
  ADD KEY `locationID` (`locationID`);

--
-- Indexes for table `logisticsAnnouncements`
--
ALTER TABLE `logisticsAnnouncements`
  ADD PRIMARY KEY (`announcementID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `logisticsBlockAttributes`
--
ALTER TABLE `logisticsBlockAttributes`
  ADD PRIMARY KEY (`blockAttributeID`),
  ADD KEY `blockID` (`blockID`);

--
-- Indexes for table `logisticsLocations`
--
ALTER TABLE `logisticsLocations`
  ADD PRIMARY KEY (`locationID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `logisticsLocationsBlocks`
--
ALTER TABLE `logisticsLocationsBlocks`
  ADD PRIMARY KEY (`blockLocationID`),
  ADD KEY `scheduleID` (`blockID`),
  ADD KEY `locationID` (`locationID`);

--
-- Indexes for table `logisticsLocationsMatches`
--
ALTER TABLE `logisticsLocationsMatches`
  ADD PRIMARY KEY (`matchLocationID`),
  ADD UNIQUE KEY `matchID_2` (`matchID`),
  ADD KEY `placeID` (`locationID`),
  ADD KEY `matchID` (`matchID`);

--
-- Indexes for table `logisticsRoleCompetency`
--
ALTER TABLE `logisticsRoleCompetency`
  ADD PRIMARY KEY (`roleCompetencyID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `logisticsRoleID` (`logisticsRoleID`);

--
-- Indexes for table `logisticsScheduleBlocks`
--
ALTER TABLE `logisticsScheduleBlocks`
  ADD PRIMARY KEY (`blockID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `blockTypeID` (`blockTypeID`);

--
-- Indexes for table `logisticsScheduleShifts`
--
ALTER TABLE `logisticsScheduleShifts`
  ADD PRIMARY KEY (`shiftID`),
  ADD KEY `blockID` (`blockID`),
  ADD KEY `shiftLocationID` (`locationID`);

--
-- Indexes for table `logisticsStaffCompetency`
--
ALTER TABLE `logisticsStaffCompetency`
  ADD PRIMARY KEY (`staffCompetencyID`),
  ADD KEY `rosterID` (`rosterID`);

--
-- Indexes for table `logisticsStaffMatches`
--
ALTER TABLE `logisticsStaffMatches`
  ADD PRIMARY KEY (`matchStaffID`),
  ADD UNIQUE KEY `matchStaffID` (`rosterID`,`matchID`) USING BTREE,
  ADD KEY `matchID` (`matchID`),
  ADD KEY `rosterID` (`rosterID`),
  ADD KEY `logisticsRoleID` (`logisticsRoleID`);

--
-- Indexes for table `logisticsStaffMatchMultipliers`
--
ALTER TABLE `logisticsStaffMatchMultipliers`
  ADD PRIMARY KEY (`matchMultiplierID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `logisticsRoleID` (`logisticsRoleID`);

--
-- Indexes for table `logisticsStaffShifts`
--
ALTER TABLE `logisticsStaffShifts`
  ADD PRIMARY KEY (`staffShiftID`),
  ADD KEY `rosterID` (`rosterID`),
  ADD KEY `logisticsRoleID` (`logisticsRoleID`),
  ADD KEY `schedultID` (`shiftID`);

--
-- Indexes for table `logisticsStaffTemplates`
--
ALTER TABLE `logisticsStaffTemplates`
  ADD PRIMARY KEY (`staffTemplateID`),
  ADD KEY `logisticsRoleID` (`logisticsRoleID`),
  ADD KEY `tournamentID` (`tournamentID`);

--
-- Indexes for table `systemAttacks`
--
ALTER TABLE `systemAttacks`
  ADD PRIMARY KEY (`attackID`);

--
-- Indexes for table `systemBlockTypes`
--
ALTER TABLE `systemBlockTypes`
  ADD PRIMARY KEY (`blockTypeID`);

--
-- Indexes for table `systemBurgees`
--
ALTER TABLE `systemBurgees`
  ADD PRIMARY KEY (`burgeeRankingID`);

--
-- Indexes for table `systemColors`
--
ALTER TABLE `systemColors`
  ADD PRIMARY KEY (`colorID`);

--
-- Indexes for table `systemCountries`
--
ALTER TABLE `systemCountries`
  ADD PRIMARY KEY (`countryIso2`);

--
-- Indexes for table `systemCutQualifications`
--
ALTER TABLE `systemCutQualifications`
  ADD PRIMARY KEY (`qualID`),
  ADD KEY `systemRosterID` (`systemRosterID`),
  ADD KEY `standardID` (`standardID`);

--
-- Indexes for table `systemCutStandards`
--
ALTER TABLE `systemCutStandards`
  ADD PRIMARY KEY (`standardID`);

--
-- Indexes for table `systemDoubleTypes`
--
ALTER TABLE `systemDoubleTypes`
  ADD PRIMARY KEY (`doubleTypeID`);

--
-- Indexes for table `systemEvents`
--
ALTER TABLE `systemEvents`
  ADD PRIMARY KEY (`eventID`),
  ADD KEY `countryIso2` (`countryIso2`),
  ADD KEY `countryIso2_2` (`countryIso2`);

--
-- Indexes for table `systemFormats`
--
ALTER TABLE `systemFormats`
  ADD PRIMARY KEY (`formatID`);

--
-- Indexes for table `systemLogisticsRoles`
--
ALTER TABLE `systemLogisticsRoles`
  ADD PRIMARY KEY (`logisticsRoleID`);

--
-- Indexes for table `systemMatchOrder`
--
ALTER TABLE `systemMatchOrder`
  ADD PRIMARY KEY (`tableID`);

--
-- Indexes for table `systemOptionsList`
--
ALTER TABLE `systemOptionsList`
  ADD PRIMARY KEY (`optionID`);

--
-- Indexes for table `systemRankings`
--
ALTER TABLE `systemRankings`
  ADD PRIMARY KEY (`tournamentRankingID`),
  ADD KEY `formatID` (`formatID`);

--
-- Indexes for table `systemRoster`
--
ALTER TABLE `systemRoster`
  ADD PRIMARY KEY (`systemRosterID`),
  ADD UNIQUE KEY `HemaRatingsID` (`HemaRatingsID`),
  ADD KEY `schoolID` (`schoolID`);

--
-- Indexes for table `systemRosterNotDuplicate`
--
ALTER TABLE `systemRosterNotDuplicate`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `rosterID1` (`rosterID1`),
  ADD KEY `rosterID2` (`rosterID2`);

--
-- Indexes for table `systemSchools`
--
ALTER TABLE `systemSchools`
  ADD PRIMARY KEY (`schoolID`),
  ADD KEY `countryIso2` (`countryIso2`);
ALTER TABLE `systemSchools` ADD FULLTEXT KEY `schoolFullName` (`schoolFullName`);

--
-- Indexes for table `systemSponsors`
--
ALTER TABLE `systemSponsors`
  ADD PRIMARY KEY (`sponsorID`);

--
-- Indexes for table `systemTournaments`
--
ALTER TABLE `systemTournaments`
  ADD PRIMARY KEY (`tournamentTypeID`);

--
-- Indexes for table `systemUserEvents`
--
ALTER TABLE `systemUserEvents`
  ADD PRIMARY KEY (`userTournamentID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `tournamentID` (`eventID`);

--
-- Indexes for table `systemUsers`
--
ALTER TABLE `systemUsers`
  ADD PRIMARY KEY (`userID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `eventAttacks`
--
ALTER TABLE `eventAttacks`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7940;
--
-- AUTO_INCREMENT for table `eventAttributes`
--
ALTER TABLE `eventAttributes`
  MODIFY `attributeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2679;
--
-- AUTO_INCREMENT for table `eventBurgeeComponents`
--
ALTER TABLE `eventBurgeeComponents`
  MODIFY `burgeeComponentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=230;
--
-- AUTO_INCREMENT for table `eventBurgeePlacings`
--
ALTER TABLE `eventBurgeePlacings`
  MODIFY `burgeePlaceID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=703;
--
-- AUTO_INCREMENT for table `eventBurgees`
--
ALTER TABLE `eventBurgees`
  MODIFY `burgeeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
--
-- AUTO_INCREMENT for table `eventCutStandards`
--
ALTER TABLE `eventCutStandards`
  MODIFY `qualID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT for table `eventDefaults`
--
ALTER TABLE `eventDefaults`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=651;
--
-- AUTO_INCREMENT for table `eventDescriptions`
--
ALTER TABLE `eventDescriptions`
  MODIFY `eventDescriptionID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;
--
-- AUTO_INCREMENT for table `eventExchanges`
--
ALTER TABLE `eventExchanges`
  MODIFY `exchangeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=381997;
--
-- AUTO_INCREMENT for table `eventGroupRankings`
--
ALTER TABLE `eventGroupRankings`
  MODIFY `groupRankingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;
--
-- AUTO_INCREMENT for table `eventGroupRoster`
--
ALTER TABLE `eventGroupRoster`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40641;
--
-- AUTO_INCREMENT for table `eventGroups`
--
ALTER TABLE `eventGroups`
  MODIFY `groupID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11328;
--
-- AUTO_INCREMENT for table `eventHemaRatingsInfo`
--
ALTER TABLE `eventHemaRatingsInfo`
  MODIFY `hemaRatingInfoID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;
--
-- AUTO_INCREMENT for table `eventIgnores`
--
ALTER TABLE `eventIgnores`
  MODIFY `ignoreID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=434;
--
-- AUTO_INCREMENT for table `eventMatches`
--
ALTER TABLE `eventMatches`
  MODIFY `matchID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110189;
--
-- AUTO_INCREMENT for table `eventMatchOptions`
--
ALTER TABLE `eventMatchOptions`
  MODIFY `matchOptionID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=481;
--
-- AUTO_INCREMENT for table `eventPlacings`
--
ALTER TABLE `eventPlacings`
  MODIFY `placeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20537;
--
-- AUTO_INCREMENT for table `eventPublication`
--
ALTER TABLE `eventPublication`
  MODIFY `publicationID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;
--
-- AUTO_INCREMENT for table `eventRatings`
--
ALTER TABLE `eventRatings`
  MODIFY `ratingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18087;
--
-- AUTO_INCREMENT for table `eventRoster`
--
ALTER TABLE `eventRoster`
  MODIFY `rosterID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14001;
--
-- AUTO_INCREMENT for table `eventRosterAdditional`
--
ALTER TABLE `eventRosterAdditional`
  MODIFY `additionalRosterID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;
--
-- AUTO_INCREMENT for table `eventRules`
--
ALTER TABLE `eventRules`
  MODIFY `rulesID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;
--
-- AUTO_INCREMENT for table `eventRulesLinks`
--
ALTER TABLE `eventRulesLinks`
  MODIFY `rulesLinkID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=230;
--
-- AUTO_INCREMENT for table `eventScoresheets`
--
ALTER TABLE `eventScoresheets`
  MODIFY `scoresheetID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22701;
--
-- AUTO_INCREMENT for table `eventSettings`
--
ALTER TABLE `eventSettings`
  MODIFY `eventSettingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=340;
--
-- AUTO_INCREMENT for table `eventSponsors`
--
ALTER TABLE `eventSponsors`
  MODIFY `eventSponsorID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;
--
-- AUTO_INCREMENT for table `eventStandings`
--
ALTER TABLE `eventStandings`
  MODIFY `standingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143425;
--
-- AUTO_INCREMENT for table `eventTeamRoster`
--
ALTER TABLE `eventTeamRoster`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=484;
--
-- AUTO_INCREMENT for table `eventTournamentCompGroupItems`
--
ALTER TABLE `eventTournamentCompGroupItems`
  MODIFY `componentGroupItemID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `eventTournamentCompGroups`
--
ALTER TABLE `eventTournamentCompGroups`
  MODIFY `componentGroupID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `eventTournamentComponents`
--
ALTER TABLE `eventTournamentComponents`
  MODIFY `tournamentComponentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;
--
-- AUTO_INCREMENT for table `eventTournamentOptions`
--
ALTER TABLE `eventTournamentOptions`
  MODIFY `tournamentOptionID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=658;
--
-- AUTO_INCREMENT for table `eventTournamentOrder`
--
ALTER TABLE `eventTournamentOrder`
  MODIFY `tournamentOrderID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table `eventTournamentRoster`
--
ALTER TABLE `eventTournamentRoster`
  MODIFY `tournamentRosterID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26979;
--
-- AUTO_INCREMENT for table `eventTournaments`
--
ALTER TABLE `eventTournaments`
  MODIFY `tournamentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1362;
--
-- AUTO_INCREMENT for table `eventVideo`
--
ALTER TABLE `eventVideo`
  MODIFY `videoID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1331;
--
-- AUTO_INCREMENT for table `eventVideoStreams`
--
ALTER TABLE `eventVideoStreams`
  MODIFY `streamID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table `logisticsAnnouncements`
--
ALTER TABLE `logisticsAnnouncements`
  MODIFY `announcementID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
--
-- AUTO_INCREMENT for table `logisticsBlockAttributes`
--
ALTER TABLE `logisticsBlockAttributes`
  MODIFY `blockAttributeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=291;
--
-- AUTO_INCREMENT for table `logisticsLocations`
--
ALTER TABLE `logisticsLocations`
  MODIFY `locationID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=454;
--
-- AUTO_INCREMENT for table `logisticsLocationsBlocks`
--
ALTER TABLE `logisticsLocationsBlocks`
  MODIFY `blockLocationID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3640;
--
-- AUTO_INCREMENT for table `logisticsLocationsMatches`
--
ALTER TABLE `logisticsLocationsMatches`
  MODIFY `matchLocationID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11032;
--
-- AUTO_INCREMENT for table `logisticsRoleCompetency`
--
ALTER TABLE `logisticsRoleCompetency`
  MODIFY `roleCompetencyID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
--
-- AUTO_INCREMENT for table `logisticsScheduleBlocks`
--
ALTER TABLE `logisticsScheduleBlocks`
  MODIFY `blockID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1836;
--
-- AUTO_INCREMENT for table `logisticsScheduleShifts`
--
ALTER TABLE `logisticsScheduleShifts`
  MODIFY `shiftID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1875;
--
-- AUTO_INCREMENT for table `logisticsStaffCompetency`
--
ALTER TABLE `logisticsStaffCompetency`
  MODIFY `staffCompetencyID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2635;
--
-- AUTO_INCREMENT for table `logisticsStaffMatches`
--
ALTER TABLE `logisticsStaffMatches`
  MODIFY `matchStaffID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9263;
--
-- AUTO_INCREMENT for table `logisticsStaffMatchMultipliers`
--
ALTER TABLE `logisticsStaffMatchMultipliers`
  MODIFY `matchMultiplierID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `logisticsStaffShifts`
--
ALTER TABLE `logisticsStaffShifts`
  MODIFY `staffShiftID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3606;
--
-- AUTO_INCREMENT for table `logisticsStaffTemplates`
--
ALTER TABLE `logisticsStaffTemplates`
  MODIFY `staffTemplateID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;
--
-- AUTO_INCREMENT for table `systemAttacks`
--
ALTER TABLE `systemAttacks`
  MODIFY `attackID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;
--
-- AUTO_INCREMENT for table `systemBlockTypes`
--
ALTER TABLE `systemBlockTypes`
  MODIFY `blockTypeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `systemBurgees`
--
ALTER TABLE `systemBurgees`
  MODIFY `burgeeRankingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `systemColors`
--
ALTER TABLE `systemColors`
  MODIFY `colorID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `systemCutQualifications`
--
ALTER TABLE `systemCutQualifications`
  MODIFY `qualID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=426;
--
-- AUTO_INCREMENT for table `systemCutStandards`
--
ALTER TABLE `systemCutStandards`
  MODIFY `standardID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `systemDoubleTypes`
--
ALTER TABLE `systemDoubleTypes`
  MODIFY `doubleTypeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `systemEvents`
--
ALTER TABLE `systemEvents`
  MODIFY `eventID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=310;
--
-- AUTO_INCREMENT for table `systemFormats`
--
ALTER TABLE `systemFormats`
  MODIFY `formatID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `systemLogisticsRoles`
--
ALTER TABLE `systemLogisticsRoles`
  MODIFY `logisticsRoleID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `systemMatchOrder`
--
ALTER TABLE `systemMatchOrder`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=365;
--
-- AUTO_INCREMENT for table `systemOptionsList`
--
ALTER TABLE `systemOptionsList`
  MODIFY `optionID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `systemRankings`
--
ALTER TABLE `systemRankings`
  MODIFY `tournamentRankingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;
--
-- AUTO_INCREMENT for table `systemRoster`
--
ALTER TABLE `systemRoster`
  MODIFY `systemRosterID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5603;
--
-- AUTO_INCREMENT for table `systemRosterNotDuplicate`
--
ALTER TABLE `systemRosterNotDuplicate`
  MODIFY `tableID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT for table `systemSchools`
--
ALTER TABLE `systemSchools`
  MODIFY `schoolID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=873;
--
-- AUTO_INCREMENT for table `systemSponsors`
--
ALTER TABLE `systemSponsors`
  MODIFY `sponsorID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `systemTournaments`
--
ALTER TABLE `systemTournaments`
  MODIFY `tournamentTypeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;
--
-- AUTO_INCREMENT for table `systemUserEvents`
--
ALTER TABLE `systemUserEvents`
  MODIFY `userTournamentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `systemUsers`
--
ALTER TABLE `systemUsers`
  MODIFY `userID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `eventAttacks`
--
ALTER TABLE `eventAttacks`
  ADD CONSTRAINT `eventAttacks_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventAttacks_ibfk_2` FOREIGN KEY (`attackTarget`) REFERENCES `systemAttacks` (`attackID`),
  ADD CONSTRAINT `eventAttacks_ibfk_3` FOREIGN KEY (`attackType`) REFERENCES `systemAttacks` (`attackID`);

--
-- Constraints for table `eventAttributes`
--
ALTER TABLE `eventAttributes`
  ADD CONSTRAINT `eventAttributes_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventBurgeeComponents`
--
ALTER TABLE `eventBurgeeComponents`
  ADD CONSTRAINT `eventburgeecomponents_ibfk_2` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventburgeecomponents_ibfk_3` FOREIGN KEY (`burgeeID`) REFERENCES `eventBurgees` (`burgeeID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventBurgeePlacings`
--
ALTER TABLE `eventBurgeePlacings`
  ADD CONSTRAINT `eventburgeeplacings_ibfk_1` FOREIGN KEY (`burgeeID`) REFERENCES `eventBurgees` (`burgeeID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventburgeeplacings_ibfk_2` FOREIGN KEY (`schoolID`) REFERENCES `systemSchools` (`schoolID`),
  ADD CONSTRAINT `eventburgeeplacings_ibfk_3` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`),
  ADD CONSTRAINT `eventburgeeplacings_ibfk_4` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`);

--
-- Constraints for table `eventBurgees`
--
ALTER TABLE `eventBurgees`
  ADD CONSTRAINT `eventburgees_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventburgees_ibfk_2` FOREIGN KEY (`burgeeRankingID`) REFERENCES `systemBurgees` (`burgeeRankingID`);

--
-- Constraints for table `eventCutStandards`
--
ALTER TABLE `eventCutStandards`
  ADD CONSTRAINT `eventCutStandards_ibfk_2` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventDefaults`
--
ALTER TABLE `eventDefaults`
  ADD CONSTRAINT `eventdefaults_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventdefaults_ibfk_2` FOREIGN KEY (`color1ID`) REFERENCES `systemColors` (`colorID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `eventdefaults_ibfk_3` FOREIGN KEY (`color2ID`) REFERENCES `systemColors` (`colorID`) ON UPDATE CASCADE;

--
-- Constraints for table `eventDescriptions`
--
ALTER TABLE `eventDescriptions`
  ADD CONSTRAINT `eventDescriptions_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventExchanges`
--
ALTER TABLE `eventExchanges`
  ADD CONSTRAINT `eventExchanges_ibfk_1` FOREIGN KEY (`matchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventExchanges_ibfk_2` FOREIGN KEY (`scoringID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventExchanges_ibfk_3` FOREIGN KEY (`receivingID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventExchanges_ibfk_4` FOREIGN KEY (`refPrefix`) REFERENCES `systemAttacks` (`attackID`),
  ADD CONSTRAINT `eventExchanges_ibfk_5` FOREIGN KEY (`refTarget`) REFERENCES `systemAttacks` (`attackID`),
  ADD CONSTRAINT `eventExchanges_ibfk_6` FOREIGN KEY (`refType`) REFERENCES `systemAttacks` (`attackID`);

--
-- Constraints for table `eventGroupRankings`
--
ALTER TABLE `eventGroupRankings`
  ADD CONSTRAINT `eventGroupRankings_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `eventGroups` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventGroupRoster`
--
ALTER TABLE `eventGroupRoster`
  ADD CONSTRAINT `eventGroupRoster_ibfk_2` FOREIGN KEY (`groupID`) REFERENCES `eventGroups` (`groupID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventGroupRoster_ibfk_3` FOREIGN KEY (`tournamentTableID`) REFERENCES `eventTournamentRoster` (`tournamentRosterID`) ON DELETE CASCADE;

--
-- Constraints for table `eventGroups`
--
ALTER TABLE `eventGroups`
  ADD CONSTRAINT `eventGroups_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE;

--
-- Constraints for table `eventIgnores`
--
ALTER TABLE `eventIgnores`
  ADD CONSTRAINT `eventIgnores_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventIgnores_ibfk_2` FOREIGN KEY (`rosterID`) REFERENCES `eventTournamentRoster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventMatches`
--
ALTER TABLE `eventMatches`
  ADD CONSTRAINT `eventMatches_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `eventGroups` (`groupID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventMatches_ibfk_2` FOREIGN KEY (`fighter1ID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventMatches_ibfk_3` FOREIGN KEY (`fighter2ID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventMatches_ibfk_4` FOREIGN KEY (`winnerID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventMatches_ibfk_5` FOREIGN KEY (`placeholderMatchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventMatchOptions`
--
ALTER TABLE `eventMatchOptions`
  ADD CONSTRAINT `eventMatchOptions_ibfk_1` FOREIGN KEY (`matchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventMatchOptions_ibfk_2` FOREIGN KEY (`optionID`) REFERENCES `systemOptionsList` (`optionID`);

--
-- Constraints for table `eventPlacings`
--
ALTER TABLE `eventPlacings`
  ADD CONSTRAINT `eventPlacings_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`);

--
-- Constraints for table `eventPublication`
--
ALTER TABLE `eventPublication`
  ADD CONSTRAINT `eventPublication_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventRatings`
--
ALTER TABLE `eventRatings`
  ADD CONSTRAINT `eventRatings_ibfk_1` FOREIGN KEY (`tournamentRosterID`) REFERENCES `eventTournamentRoster` (`tournamentRosterID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventRoster`
--
ALTER TABLE `eventRoster`
  ADD CONSTRAINT `eventRoster_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventRoster_ibfk_2` FOREIGN KEY (`schoolID`) REFERENCES `systemSchools` (`schoolID`),
  ADD CONSTRAINT `eventRoster_ibfk_3` FOREIGN KEY (`systemRosterID`) REFERENCES `systemRoster` (`systemRosterID`);

--
-- Constraints for table `eventRosterAdditional`
--
ALTER TABLE `eventRosterAdditional`
  ADD CONSTRAINT `eventRosterAdditional_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventRules`
--
ALTER TABLE `eventRules`
  ADD CONSTRAINT `eventRules_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventRulesLinks`
--
ALTER TABLE `eventRulesLinks`
  ADD CONSTRAINT `eventRulesLinks_ibfk_1` FOREIGN KEY (`rulesID`) REFERENCES `eventRules` (`rulesID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventRulesLinks_ibfk_2` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventSettings`
--
ALTER TABLE `eventSettings`
  ADD CONSTRAINT `eventSettings_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventSponsors`
--
ALTER TABLE `eventSponsors`
  ADD CONSTRAINT `eventSponsors_ibfk_1` FOREIGN KEY (`sponsorID`) REFERENCES `systemSponsors` (`sponsorID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventSponsors_ibfk_2` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventStandings`
--
ALTER TABLE `eventStandings`
  ADD CONSTRAINT `eventStandings_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventStandings_ibfk_2` FOREIGN KEY (`groupID`) REFERENCES `eventGroups` (`groupID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventStandings_ibfk_3` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE;

--
-- Constraints for table `eventTeamRoster`
--
ALTER TABLE `eventTeamRoster`
  ADD CONSTRAINT `eventTeamRoster_ibfk_1` FOREIGN KEY (`teamID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventTeamRoster_ibfk_2` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventTeamRoster_ibfk_3` FOREIGN KEY (`tournamentRosterID`) REFERENCES `eventTournamentRoster` (`tournamentRosterID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventTournamentCompGroupItems`
--
ALTER TABLE `eventTournamentCompGroupItems`
  ADD CONSTRAINT `eventTournamentCompGroupItems_ibfk_1` FOREIGN KEY (`componentGroupID`) REFERENCES `eventTournamentCompGroups` (`componentGroupID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventTournamentCompGroupItems_ibfk_2` FOREIGN KEY (`tournamentComponentID`) REFERENCES `eventTournamentComponents` (`tournamentComponentID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventTournamentCompGroups`
--
ALTER TABLE `eventTournamentCompGroups`
  ADD CONSTRAINT `eventTournamentCompGroups_ibfk_1` FOREIGN KEY (`metaTournamentID`) REFERENCES `eventTournaments` (`tournamentID`);

--
-- Constraints for table `eventTournamentComponents`
--
ALTER TABLE `eventTournamentComponents`
  ADD CONSTRAINT `eventTournamentComponents_ibfk_1` FOREIGN KEY (`metaTournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventTournamentComponents_ibfk_2` FOREIGN KEY (`componentTournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON UPDATE CASCADE;

--
-- Constraints for table `eventTournamentOptions`
--
ALTER TABLE `eventTournamentOptions`
  ADD CONSTRAINT `eventTournamentOptions_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventTournamentOptions_ibfk_2` FOREIGN KEY (`optionID`) REFERENCES `systemOptionsList` (`optionID`);

--
-- Constraints for table `eventTournamentOrder`
--
ALTER TABLE `eventTournamentOrder`
  ADD CONSTRAINT `eventTournamentOrder_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventTournamentRoster`
--
ALTER TABLE `eventTournamentRoster`
  ADD CONSTRAINT `eventTournamentRoster_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventTournamentRoster_ibfk_2` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE;

--
-- Constraints for table `eventTournaments`
--
ALTER TABLE `eventTournaments`
  ADD CONSTRAINT `eventTournaments_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventTournaments_ibfk_10` FOREIGN KEY (`color1ID`) REFERENCES `systemColors` (`colorID`),
  ADD CONSTRAINT `eventTournaments_ibfk_11` FOREIGN KEY (`color2ID`) REFERENCES `systemColors` (`colorID`),
  ADD CONSTRAINT `eventTournaments_ibfk_14` FOREIGN KEY (`formatID`) REFERENCES `systemFormats` (`formatID`),
  ADD CONSTRAINT `eventTournaments_ibfk_15` FOREIGN KEY (`tournamentRankingID`) REFERENCES `systemRankings` (`tournamentRankingID`),
  ADD CONSTRAINT `eventTournaments_ibfk_2` FOREIGN KEY (`tournamentWeaponID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_3` FOREIGN KEY (`tournamentPrefixID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_4` FOREIGN KEY (`tournamentSuffixID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_5` FOREIGN KEY (`tournamentGenderID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_6` FOREIGN KEY (`tournamentMaterialID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_8` FOREIGN KEY (`doubleTypeID`) REFERENCES `systemDoubleTypes` (`doubleTypeID`);

--
-- Constraints for table `eventVideo`
--
ALTER TABLE `eventVideo`
  ADD CONSTRAINT `eventVideo_ibfk_1` FOREIGN KEY (`matchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsAnnouncements`
--
ALTER TABLE `logisticsAnnouncements`
  ADD CONSTRAINT `logisticsAnnouncements_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsBlockAttributes`
--
ALTER TABLE `logisticsBlockAttributes`
  ADD CONSTRAINT `logisticsBlockAttributes_ibfk_1` FOREIGN KEY (`blockID`) REFERENCES `logisticsScheduleBlocks` (`blockID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsLocations`
--
ALTER TABLE `logisticsLocations`
  ADD CONSTRAINT `logisticsLocations_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsLocationsBlocks`
--
ALTER TABLE `logisticsLocationsBlocks`
  ADD CONSTRAINT `logisticsLocationsBlocks_ibfk_1` FOREIGN KEY (`blockID`) REFERENCES `logisticsScheduleBlocks` (`blockID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsLocationsBlocks_ibfk_2` FOREIGN KEY (`locationID`) REFERENCES `logisticsLocations` (`locationID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsLocationsMatches`
--
ALTER TABLE `logisticsLocationsMatches`
  ADD CONSTRAINT `logisticsLocationsMatches_ibfk_1` FOREIGN KEY (`locationID`) REFERENCES `logisticsLocations` (`locationID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsLocationsMatches_ibfk_4` FOREIGN KEY (`matchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsRoleCompetency`
--
ALTER TABLE `logisticsRoleCompetency`
  ADD CONSTRAINT `logisticsRoleCompetency_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsRoleCompetency_ibfk_2` FOREIGN KEY (`logisticsRoleID`) REFERENCES `systemLogisticsRoles` (`logisticsRoleID`);

--
-- Constraints for table `logisticsScheduleBlocks`
--
ALTER TABLE `logisticsScheduleBlocks`
  ADD CONSTRAINT `logisticsScheduleBlocks_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsScheduleBlocks_ibfk_2` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsScheduleBlocks_ibfk_3` FOREIGN KEY (`blockTypeID`) REFERENCES `systemBlockTypes` (`blockTypeID`);

--
-- Constraints for table `logisticsScheduleShifts`
--
ALTER TABLE `logisticsScheduleShifts`
  ADD CONSTRAINT `logisticsScheduleShifts_ibfk_1` FOREIGN KEY (`blockID`) REFERENCES `logisticsScheduleBlocks` (`blockID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsScheduleShifts_ibfk_2` FOREIGN KEY (`locationID`) REFERENCES `logisticsLocations` (`locationID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsStaffCompetency`
--
ALTER TABLE `logisticsStaffCompetency`
  ADD CONSTRAINT `logisticsStaffCompetency_ibfk_1` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsStaffMatches`
--
ALTER TABLE `logisticsStaffMatches`
  ADD CONSTRAINT `logisticsStaffMatches_ibfk_1` FOREIGN KEY (`matchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsStaffMatches_ibfk_2` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsStaffMatches_ibfk_3` FOREIGN KEY (`logisticsRoleID`) REFERENCES `systemLogisticsRoles` (`logisticsRoleID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsStaffMatchMultipliers`
--
ALTER TABLE `logisticsStaffMatchMultipliers`
  ADD CONSTRAINT `logisticsStaffMatchMultipliers_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsStaffMatchMultipliers_ibfk_2` FOREIGN KEY (`logisticsRoleID`) REFERENCES `systemLogisticsRoles` (`logisticsRoleID`);

--
-- Constraints for table `logisticsStaffShifts`
--
ALTER TABLE `logisticsStaffShifts`
  ADD CONSTRAINT `logisticsStaffShifts_ibfk_2` FOREIGN KEY (`rosterID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logisticsStaffShifts_ibfk_3` FOREIGN KEY (`logisticsRoleID`) REFERENCES `systemLogisticsRoles` (`logisticsRoleID`),
  ADD CONSTRAINT `logisticsStaffShifts_ibfk_4` FOREIGN KEY (`shiftID`) REFERENCES `logisticsScheduleShifts` (`shiftID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logisticsStaffTemplates`
--
ALTER TABLE `logisticsStaffTemplates`
  ADD CONSTRAINT `logisticsStaffTemplates_ibfk_1` FOREIGN KEY (`logisticsRoleID`) REFERENCES `systemLogisticsRoles` (`logisticsRoleID`);

--
-- Constraints for table `systemCutQualifications`
--
ALTER TABLE `systemCutQualifications`
  ADD CONSTRAINT `cuttingqualifications_ibfk_1` FOREIGN KEY (`systemRosterID`) REFERENCES `systemRoster` (`systemRosterID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `cuttingqualifications_ibfk_2` FOREIGN KEY (`standardID`) REFERENCES `systemCutStandards` (`standardID`) ON UPDATE CASCADE;

--
-- Constraints for table `systemEvents`
--
ALTER TABLE `systemEvents`
  ADD CONSTRAINT `systemEvents_ibfk_1` FOREIGN KEY (`countryIso2`) REFERENCES `systemCountries` (`countryIso2`);

--
-- Constraints for table `systemRankings`
--
ALTER TABLE `systemRankings`
  ADD CONSTRAINT `systemRankings_ibfk_1` FOREIGN KEY (`formatID`) REFERENCES `systemFormats` (`formatID`) ON UPDATE CASCADE;

--
-- Constraints for table `systemRoster`
--
ALTER TABLE `systemRoster`
  ADD CONSTRAINT `systemRoster_ibfk_1` FOREIGN KEY (`schoolID`) REFERENCES `systemSchools` (`schoolID`);

--
-- Constraints for table `systemRosterNotDuplicate`
--
ALTER TABLE `systemRosterNotDuplicate`
  ADD CONSTRAINT `systemRosterNotDuplicate_ibfk_1` FOREIGN KEY (`rosterID1`) REFERENCES `systemRoster` (`systemRosterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `systemRosterNotDuplicate_ibfk_2` FOREIGN KEY (`rosterID2`) REFERENCES `systemRoster` (`systemRosterID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `systemSchools`
--
ALTER TABLE `systemSchools`
  ADD CONSTRAINT `systemSchools_ibfk_1` FOREIGN KEY (`countryIso2`) REFERENCES `systemCountries` (`countryIso2`);

--
-- Constraints for table `systemUserEvents`
--
ALTER TABLE `systemUserEvents`
  ADD CONSTRAINT `systemUserEvents_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `systemUsers` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `systemUserEvents_ibfk_2` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
