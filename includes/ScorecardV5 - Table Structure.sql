-- phpMyAdmin SQL Dump
-- version 4.4.15.9
-- https://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2018 at 09:49 PM
-- Server version: 5.6.37
-- PHP Version: 7.1.8

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
-- Table structure for table `cuttingQualifications`
--

CREATE TABLE IF NOT EXISTS `cuttingQualifications` (
  `qualID` int(10) unsigned NOT NULL,
  `systemRosterID` int(10) unsigned DEFAULT NULL,
  `standardID` int(10) unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `qualValue` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cuttingStandards`
--

CREATE TABLE IF NOT EXISTS `cuttingStandards` (
  `standardID` int(10) unsigned NOT NULL,
  `standardName` varchar(255) NOT NULL,
  `standardCode` varchar(255) NOT NULL,
  `standardText` text
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cuttingStandards`
--

INSERT INTO `cuttingStandards` (`standardID`, `standardName`, `standardCode`, `standardText`) VALUES
(1, 'West Coast Qualification', 'westCoast', 'Either:\r\n4 total cuts, 2 on each side of the mat\r\n\r\nor\r\n\r\n3 unique cuts performed on the mat\r\n\r\nTime Limit: 40 seconds.'),
(2, 'Longpoint HFL', 'LHFL', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `eventAttacks`
--

CREATE TABLE IF NOT EXISTS `eventAttacks` (
  `tableID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `attackPrefix` int(10) unsigned DEFAULT NULL,
  `attackTarget` int(10) unsigned DEFAULT NULL,
  `attackType` int(10) unsigned DEFAULT NULL,
  `attackPoints` int(11) NOT NULL DEFAULT '0',
  `attackNumber` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=667 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventAttributes`
--

CREATE TABLE IF NOT EXISTS `eventAttributes` (
  `attributeID` int(10) unsigned NOT NULL,
  `attributeBool` tinyint(1) DEFAULT NULL,
  `attributeText` text,
  `tournamentID` int(10) unsigned NOT NULL,
  `attributeType` varchar(255) NOT NULL,
  `attributeValue` float DEFAULT NULL,
  `attributeGroupSet` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=409 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventCuttingStandards`
--

CREATE TABLE IF NOT EXISTS `eventCuttingStandards` (
  `qualID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `standardID` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `qualValue` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventDefaults`
--

CREATE TABLE IF NOT EXISTS `eventDefaults` (
  `tableID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `color1ID` int(10) unsigned NOT NULL DEFAULT '1',
  `color2ID` int(10) unsigned NOT NULL DEFAULT '2',
  `maxPoolSize` int(10) unsigned NOT NULL DEFAULT '5',
  `maxDoubleHits` int(10) unsigned NOT NULL DEFAULT '3',
  `normalizePoolSize` int(10) unsigned NOT NULL DEFAULT '0',
  `allowTies` tinyint(1) NOT NULL DEFAULT '0',
  `nameDisplay` varchar(255) NOT NULL DEFAULT 'firstName',
  `tournamentDisplay` varchar(255) NOT NULL DEFAULT 'weapon',
  `tournamentSorting` varchar(255) NOT NULL DEFAULT 'numGrouped',
  `useTimer` tinyint(1) NOT NULL DEFAULT '0',
  `useControlPoint` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventExchanges`
--

CREATE TABLE IF NOT EXISTS `eventExchanges` (
  `exchangeID` int(10) unsigned NOT NULL,
  `matchID` int(10) unsigned DEFAULT NULL,
  `exchangeType` varchar(255) NOT NULL,
  `scoringID` int(10) unsigned DEFAULT NULL,
  `recievingID` int(10) unsigned DEFAULT NULL,
  `scoreValue` float DEFAULT NULL,
  `scoreDeduction` float DEFAULT NULL,
  `exchangeNumber` int(11) NOT NULL DEFAULT '0',
  `exchangeTime` int(11) DEFAULT NULL,
  `refPrefix` int(10) unsigned DEFAULT NULL,
  `refTarget` int(10) unsigned DEFAULT NULL,
  `refType` int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=55390 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventGroupRoster`
--

CREATE TABLE IF NOT EXISTS `eventGroupRoster` (
  `tableID` int(10) unsigned NOT NULL,
  `groupID` int(10) unsigned DEFAULT NULL,
  `rosterID` int(10) unsigned DEFAULT NULL,
  `poolPosition` int(10) unsigned DEFAULT NULL,
  `participantStatus` varchar(255) DEFAULT 'normal',
  `tournamentTableID` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6692 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventGroups`
--

CREATE TABLE IF NOT EXISTS `eventGroups` (
  `groupID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `groupType` varchar(255) NOT NULL,
  `groupNumber` int(11) DEFAULT NULL,
  `groupName` varchar(255) DEFAULT NULL,
  `groupSet` int(11) NOT NULL DEFAULT '1',
  `bracketLevels` tinyint(4) DEFAULT NULL,
  `numFighters` int(10) unsigned DEFAULT NULL,
  `groupStatus` varchar(255) DEFAULT NULL,
  `groupComplete` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=1876 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventIgnores`
--

CREATE TABLE IF NOT EXISTS `eventIgnores` (
  `ignoreID` int(11) NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `rosterID` int(10) unsigned NOT NULL,
  `ignoreAtSet` int(11) NOT NULL DEFAULT '0',
  `stopAtSet` int(11) NOT NULL DEFAULT '0',
  `soloAtSet` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventLivestreamMatches`
--

CREATE TABLE IF NOT EXISTS `eventLivestreamMatches` (
  `tableID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `matchNumber` int(11) NOT NULL,
  `matchID` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventLivestreams`
--

CREATE TABLE IF NOT EXISTS `eventLivestreams` (
  `tableID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `isLive` tinyint(1) NOT NULL DEFAULT '0',
  `chanelName` varchar(255) NOT NULL,
  `platform` varchar(255) NOT NULL,
  `useOverlay` tinyint(1) NOT NULL DEFAULT '0',
  `matchID` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventMatches`
--

CREATE TABLE IF NOT EXISTS `eventMatches` (
  `matchID` int(10) unsigned NOT NULL,
  `groupID` int(10) unsigned DEFAULT NULL,
  `matchNumber` int(10) unsigned DEFAULT NULL,
  `fighter1ID` int(10) unsigned DEFAULT NULL,
  `fighter2ID` int(10) unsigned DEFAULT NULL,
  `winnerID` int(10) unsigned DEFAULT NULL,
  `fighter1Score` float DEFAULT NULL,
  `fighter2Score` float DEFAULT NULL,
  `bracketPosition` int(10) unsigned DEFAULT NULL,
  `bracketLevel` int(10) unsigned DEFAULT NULL,
  `matchComplete` tinyint(1) DEFAULT '0',
  `ignoreMatch` tinyint(1) DEFAULT '0',
  `YouTubeLink` text,
  `reversedColors` tinyint(1) NOT NULL DEFAULT '0',
  `matchTime` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=15046 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventPlacings`
--

CREATE TABLE IF NOT EXISTS `eventPlacings` (
  `placeID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned NOT NULL,
  `rosterID` int(10) unsigned NOT NULL,
  `placing` int(11) NOT NULL,
  `highBound` int(11) DEFAULT NULL,
  `lowBound` int(11) DEFAULT NULL,
  `placeType` varchar(255) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2752 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventRoster`
--

CREATE TABLE IF NOT EXISTS `eventRoster` (
  `rosterID` int(10) unsigned NOT NULL,
  `systemRosterID` int(10) unsigned DEFAULT NULL,
  `eventID` int(10) unsigned DEFAULT NULL,
  `schoolID` int(10) unsigned DEFAULT NULL,
  `publicNotes` text,
  `privateNotes` text,
  `isTeam` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=2242 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventStandings`
--

CREATE TABLE IF NOT EXISTS `eventStandings` (
  `standingID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned DEFAULT NULL,
  `groupID` int(10) unsigned DEFAULT NULL,
  `rosterID` int(10) unsigned DEFAULT NULL,
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
  `AbsPointsFor` int(11) DEFAULT '0',
  `AbsPointsAgainst` int(11) DEFAULT '0',
  `numPenalties` float DEFAULT '0',
  `penaltiesAgainstOpponents` float DEFAULT '0',
  `penaltiesAgainst` float DEFAULT '0',
  `doubleOuts` float DEFAULT '0',
  `ignoreForBracket` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=123255 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTeamRoster`
--

CREATE TABLE IF NOT EXISTS `eventTeamRoster` (
  `tableID` int(10) unsigned NOT NULL,
  `teamID` int(10) unsigned DEFAULT NULL,
  `rosterID` int(10) unsigned DEFAULT NULL,
  `tournamentRosterID` int(10) unsigned DEFAULT NULL,
  `memberRole` varchar(255) NOT NULL DEFAULT 'member',
  `memberName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournamentRoster`
--

CREATE TABLE IF NOT EXISTS `eventTournamentRoster` (
  `tableID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned DEFAULT NULL,
  `rosterID` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4387 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournaments`
--

CREATE TABLE IF NOT EXISTS `eventTournaments` (
  `tournamentID` int(10) unsigned NOT NULL,
  `eventID` int(10) unsigned NOT NULL,
  `tournamentWeaponID` int(10) unsigned NOT NULL,
  `tournamentPrefixID` int(10) unsigned DEFAULT NULL,
  `tournamentGenderID` int(10) unsigned DEFAULT NULL,
  `tournamentMaterialID` int(10) unsigned DEFAULT NULL,
  `tournamentSuffixID` int(10) unsigned DEFAULT NULL,
  `tournamentRankingID` int(10) unsigned DEFAULT NULL,
  `doubleTypeID` int(10) unsigned DEFAULT '2',
  `tournamentElimID` int(10) unsigned DEFAULT '2',
  `numGroupSets` int(11) NOT NULL DEFAULT '1',
  `numParticipants` int(10) unsigned DEFAULT '0',
  `normalizePoolSize` int(11) DEFAULT '0',
  `color1ID` int(10) unsigned DEFAULT '1',
  `color2ID` int(10) unsigned DEFAULT '2',
  `maxPoolSize` int(10) unsigned NOT NULL DEFAULT '5',
  `maxDoubleHits` int(10) unsigned NOT NULL DEFAULT '3',
  `maximumExchanges` int(11) DEFAULT NULL,
  `basePointValue` int(11) NOT NULL DEFAULT '0',
  `allowTies` tinyint(1) NOT NULL DEFAULT '0',
  `tournamentStatus` varchar(255) DEFAULT NULL,
  `isCuttingQual` tinyint(1) NOT NULL DEFAULT '0',
  `isFinalized` tinyint(1) NOT NULL DEFAULT '0',
  `useTimer` tinyint(1) NOT NULL DEFAULT '0',
  `useControlPoint` int(11) NOT NULL DEFAULT '0',
  `isNotNetScore` tinyint(1) NOT NULL DEFAULT '0',
  `isReverseScore` int(11) NOT NULL DEFAULT '0',
  `overrideDoubleType` tinyint(1) NOT NULL DEFAULT '0',
  `isPrivate` tinyint(1) NOT NULL DEFAULT '0',
  `isTeams` tinyint(1) NOT NULL DEFAULT '0',
  `logicMode` varchar(255) DEFAULT NULL,
  `poolWinnersFirst` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=255 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemAttacks`
--

CREATE TABLE IF NOT EXISTS `systemAttacks` (
  `attackID` int(10) unsigned NOT NULL,
  `attackClass` varchar(255) NOT NULL,
  `attackCode` varchar(255) NOT NULL,
  `attackText` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

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
(12, 'prefix', 'double', 'Weighted Double'),
(13, 'prefix', 'afterblow', 'Afterblow'),
(14, 'type', 'ringOut', 'Ring Out');

-- --------------------------------------------------------

--
-- Table structure for table `systemColors`
--

CREATE TABLE IF NOT EXISTS `systemColors` (
  `colorID` int(10) unsigned NOT NULL,
  `colorName` varchar(255) NOT NULL,
  `colorCode` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemColors`
--

INSERT INTO `systemColors` (`colorID`, `colorName`, `colorCode`) VALUES
(1, 'BLACK', '#778899'),
(2, 'GOLD', '#E7B923'),
(3, 'RED', '#EB5757'),
(4, 'BLUE', '#1C6CD8'),
(5, 'WHITE', '#FFF');

-- --------------------------------------------------------

--
-- Table structure for table `systemDoubleTypes`
--

CREATE TABLE IF NOT EXISTS `systemDoubleTypes` (
  `doubleTypeID` int(10) unsigned NOT NULL,
  `doubleTypeName` varchar(255) NOT NULL,
  `doublesDisabled` tinyint(1) NOT NULL,
  `afterblowDisabled` tinyint(1) NOT NULL,
  `afterblowType` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemDoubleTypes`
--

INSERT INTO `systemDoubleTypes` (`doubleTypeID`, `doubleTypeName`, `doublesDisabled`, `afterblowDisabled`, `afterblowType`) VALUES
(1, 'No Afterblow', 0, 1, 'none'),
(2, 'Deductive Afterblow', 0, 0, 'deductive'),
(3, 'Full Afterblow', 0, 0, 'full');

-- --------------------------------------------------------

--
-- Table structure for table `systemElimTypes`
--

CREATE TABLE IF NOT EXISTS `systemElimTypes` (
  `elimTypeID` int(10) unsigned NOT NULL,
  `elimTypeName` varchar(255) NOT NULL,
  `isPools` tinyint(1) NOT NULL,
  `isBrackets` tinyint(1) NOT NULL,
  `isRounds` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemElimTypes`
--

INSERT INTO `systemElimTypes` (`elimTypeID`, `elimTypeName`, `isPools`, `isBrackets`, `isRounds`) VALUES
(1, 'Results Only', 0, 0, 0),
(2, 'Pool & Bracket', 1, 1, 0),
(3, 'Direct Bracket', 0, 1, 0),
(4, 'Pool Sets', 1, 0, 0),
(5, 'Scored Event', 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `systemEvents`
--

CREATE TABLE IF NOT EXISTS `systemEvents` (
  `eventID` int(10) unsigned NOT NULL,
  `eventName` varchar(255) NOT NULL,
  `eventAbreviation` varchar(255) DEFAULT NULL,
  `eventLeague` varchar(255) DEFAULT NULL,
  `eventYear` smallint(6) DEFAULT NULL,
  `eventStartDate` date DEFAULT NULL,
  `eventEndDate` date DEFAULT NULL,
  `regionCode` int(11) DEFAULT NULL,
  `eventCountry` varchar(255) DEFAULT NULL,
  `eventProvince` varchar(255) DEFAULT NULL,
  `eventCity` varchar(255) DEFAULT NULL,
  `USER_STAFF` varchar(255) DEFAULT NULL,
  `USER_ADMIN` varchar(255) DEFAULT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `eventStatus` varchar(255) NOT NULL DEFAULT 'active',
  `organizerEmail` varchar(255) DEFAULT NULL,
  `termsOfUseAccepted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemMatchOrder`
--

CREATE TABLE IF NOT EXISTS `systemMatchOrder` (
  `tableID` int(10) unsigned NOT NULL,
  `numberOfFighters` tinyint(4) DEFAULT NULL,
  `matchNumber` tinyint(4) DEFAULT NULL,
  `fighter1` tinyint(4) DEFAULT NULL,
  `fighter2` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=166 DEFAULT CHARSET=latin1;

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
(165, 9, 36, 8, 4);

-- --------------------------------------------------------

--
-- Table structure for table `systemRankings`
--

CREATE TABLE IF NOT EXISTS `systemRankings` (
  `tournamentRankingID` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `numberOfInstances` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text,
  `Pool_Bracket` tinyint(1) NOT NULL DEFAULT '0',
  `Pool_Sets` tinyint(1) NOT NULL DEFAULT '0',
  `Scored_Event` tinyint(1) NOT NULL DEFAULT '0',
  `displayFunction` varchar(255) DEFAULT NULL,
  `scoringFunction` varchar(255) DEFAULT NULL,
  `rankingFunction` varchar(255) DEFAULT NULL,
  `advancementFunction` varchar(255) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemRankings`
--

INSERT INTO `systemRankings` (`tournamentRankingID`, `name`, `numberOfInstances`, `description`, `Pool_Bracket`, `Pool_Sets`, `Scored_Event`, `displayFunction`, `scoringFunction`, `rankingFunction`, `advancementFunction`, `scoreFormula`, `orderByField1`, `orderBySort1`, `orderByField2`, `orderBySort2`, `orderByField3`, `orderBySort3`, `orderByField4`, `orderBySort4`, `displayTitle1`, `displayField1`, `displayTitle2`, `displayField2`, `displayTitle3`, `displayField3`, `displayTitle4`, `displayField4`, `displayTitle5`, `displayField5`) VALUES
(1, 'Franklin 2014', 82, 'Calculation:\n +[Points For]\n +(5 * [Wins])\n -[Points Against]\n -(Doubles Penalty)\n\nDoubles Penalty\n1 Double -> 1 = 1\n2 Doubles -> 1+2 = 3\n3 Doubles -> 1+2+3 = 6 etc...\n\nRanking:\n1) Pool winners first\n2) Score\n3) Wins\n4) Doubles', 1, 0, 0, NULL, NULL, NULL, NULL, '(5*wins) + pointsFor - pointsAgainst - ((doubles * (doubles+1))/2)', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(2, 'RSS Cutting', 11, 'Root Sum Square Cutting\n\nScoring\nTotal Deduction = sqrt([Cut Deduction]^2 + [Form Deduction]^2)\nScore = 20 - Cut Deduction\n\nRanking\n1) By Score\n2) Least deductions', 0, 0, 1, 'RSScutting', 'RSScutting', NULL, NULL, NULL, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Results Only', 12, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Deduction Based', 2, 'Scoring\r\n100 point base score\r\nDeductions from the base score', 0, 0, 1, 'DeductionBased', 'DeductionBased', NULL, NULL, 'pointsFor', 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'FNY 2017', 2, 'Fechtshule New York 2017\r\n\r\nScoring:\r\nOne exchange matches\r\n+ 1*Wins\r\n- 2*[Losses]\r\n- 2*[Doubles]\r\n\r\nRanking:\r\nCumulative across multiple pools', 0, 1, 0, NULL, NULL, NULL, 'FNY2017', 'pointsFor - 2 * (losses + doubles)', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Pushes', 'matches - hitsFor - losses - doubles', 'Losses', 'losses', 'Doubles', 'doubles', 'Score', 'score'),
(7, 'Total Points Scored', 8, 'Scoring\r\n +[Points For]\r\n\r\nRanking\r\nTop 2 in each pool first\r\n1) Wins\r\n2) Score\r\n3) Doubles', 1, 0, 0, NULL, NULL, NULL, NULL, 'pointsFor', 'wins', 'DESC', 'score', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Points Scored', 'score', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Hit Ratio', 2, 'Score\r\n[Points For] / [Total Times Hit]\r\n\r\nRanking\r\n1) Score\r\n2) Wins', 1, 0, 0, NULL, NULL, NULL, NULL, 'case \n	when (hitsAgainst + afterblowsAgainst + doubles) > 0 then\n		pointsFor /  (hitsAgainst + afterblowsAgainst + doubles)\n	else\n		9001\nend', 'score', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', NULL, NULL, 'Points For', 'pointsFor', 'Total Times Hit', 'hitsAgainst + afterblowsAgainst + doubles', 'Score', 'score', NULL, NULL, NULL, NULL),
(9, 'Sandstorm 2017', 2, 'Scoring:\r\n3 Points - Controlled Win/Artful Exchange\r\n2 Points - Win\r\n1 Point - Win w/ Afterblow\r\n\r\nRanking:\r\nBy Score\r\n', 1, 0, 0, NULL, NULL, NULL, NULL, 'pointsFor - doubles', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Control Wins', 'score + doubles - (2*wins) - (3*afterblowsAgainst)', 'Wins', '(3 * wins) - (2 * afterblowsAgainst) - score + doubles', 'Afterblow Wins', 'afterblowsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(10, '2 Point Cumulative', 1, 'Used for Singlestick in Helsinki Open 2018\r\n\r\nScoring:\r\n2 Points for Win\r\n1 Point for Tie\r\n\r\nRanking:\r\nBy Score', 0, 1, 0, NULL, NULL, NULL, 'FNY2017', '(2 * wins) + ties', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Ties', 'ties', 'Losses', 'losses', 'Score', 'score', NULL, NULL),
(11, 'Flowerpoint', 4, 'Score\r\n-1 Point for every time hit\r\n(Scoring action or double)\r\n\r\nRanking\r\nBy score', 0, 1, 0, NULL, NULL, NULL, 'Flowerpoint', '0 - hitsAgainst - doubles', 'score', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', NULL, NULL, 'Number of Times Hit', 'hitsAgainst', 'Doubles', 'doubles', 'Score', 'score', NULL, NULL, NULL, NULL),
(13, 'Thokk Continuous', 1, NULL, 0, 1, 0, NULL, NULL, NULL, 'SimpleRankOrder', '0 - AbsPointsAgainst', 'hitsAgainst', 'ASC', 'hitsFor', 'DESC', 'score', 'DESC', NULL, NULL, 'Bouts Won', 'hitsFor', 'Bouts Lost', 'hitsAgainst', 'Points Against', 'pointsAgainst', NULL, NULL, NULL, NULL),
(14, 'Alls Fair', 2, 'Ranking:\r\n1) Wins\r\n2) Doubles\r\n3) Points +/-', 0, 1, 0, NULL, NULL, NULL, 'SimpleRankOrder', 'pointsFor - pointsAgainst', 'wins', 'DESC', 'doubles', 'ASC', 'score', 'DESC', NULL, NULL, 'Wins', 'wins', 'Doubles', 'doubles', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Points +/-', 'score'),
(15, 'JNCR', 1, 'Julian''s Nameless Cutting Rules\r\n\r\nCuts are assigned scored as follows:\r\n8 points cut quality\r\n4 points upper body form\r\n4 points lower body form\r\n\r\n0 in cut quality or 0 in combined form is 0 for the entire cut.\r\n\r\nA negative score in any of the three becomes the final score.\r\n\r\nA cut with perfect scores earns an additional +4 points.', 0, 0, 1, 'JNCR', 'JNCR', NULL, NULL, NULL, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Aussie Reversed', 21, '<u>This score mode is meant to be used with reverse scores!</u>\n\nPoints are assigned to the fighter who was hit.\n\nRanking:\nTop 2 from each pool ranked ahead of 3rd place and lower\n1) Wins\n2) Least points hit with (this is the points you give to the fighter!)', 1, 1, 0, NULL, NULL, NULL, NULL, 'AbsPointsAgainst', 'wins', 'DESC', 'AbsPointsAgainst', 'ASC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points Against', 'score', NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'AHWG 2018', 2, 'Austin Historical Weapons Guild\r\n\r\nFor use with single hit matches\r\nScore = Wins - Losses - Double Outs', 1, 1, 0, NULL, NULL, NULL, NULL, 'wins - losses - doubleOuts', 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Losses', 'losses', 'Doubles', 'doubles', 'Score', 'score', NULL, NULL),
(18, 'MART', 1, 'Mid Atlantic Rookie Tournament: Fighty McFightface\r\n\r\nScore:\r\n2 * Wins + Ties\r\n\r\nRanking:\r\n1) Score\r\n2) Doubles (fewest outranks most)\r\n3) Points allowed (Fewest outranks most)\r\n4) Points scored (Most outranks fewest)\r\n\r\n\r\n', 1, 1, 0, NULL, NULL, NULL, NULL, '(2 * Wins) + Ties', 'score', 'DESC', '(doubles + afterblowsFor + afterblowsAgainst)', 'ASC', 'AbsPointsAgainst', 'ASC', 'AbsPointsFor', 'DESC', 'Wins', 'wins', 'Ties', 'ties', 'Doubles', '(doubles + afterblowsFor + afterblowsAgainst)', 'Points Against', 'AbsPointsAgainst', 'Points For', 'AbsPointsFor'),
(19, 'Franklin 2014 (x25)', 7, 'Franklin 2014 with even stronger doubles penalty\r\n\r\nCalculation:\r\n +[Points For]\r\n +(5 * [Wins])\r\n -[Points Against]\r\n -(Doubles Penalty) * 1.25\r\n\r\nDoubles Penalty\r\n1 Double -> 1 = 1\r\n2 Doubles -> 1+2 = 3\r\n3 Doubles -> 1+2+3 = 6 etc...\r\n\r\nRanking:\r\n1) Pool winners first\r\n2) Score\r\n3) Wins\r\n4) Doubles', 1, 1, 0, NULL, NULL, NULL, NULL, '(5*wins) + pointsFor - pointsAgainst - (1.25*(doubles * (doubles+1))/2)', 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(20, 'Baer Score', 2, 'Sort By:\r\n1) Wins\r\n2) Points Against\r\n3) Doubles', 1, 1, 0, NULL, NULL, NULL, NULL, '0', 'wins', 'DESC', 'AbsPointsAgainst', 'ASC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Points Against', 'AbsPointsAgainst', 'Doubles', 'doubles', NULL, NULL, NULL, NULL),
(21, 'Wins | Plus/Minus', 1, 'Score:\r\npointsFor - pointsAgainst\r\n\r\nRanking\r\n1) Wins\r\n2) Score', 1, 1, 0, NULL, NULL, NULL, NULL, 'pointsFor - pointsAgainst', 'wins', 'DESC', 'score', 'DESC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Points +/-', 'score', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `systemRoster`
--

CREATE TABLE IF NOT EXISTS `systemRoster` (
  `systemRosterID` int(10) unsigned NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `middleName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `schoolID` int(10) unsigned DEFAULT NULL,
  `HemaRatingsID` int(10) unsigned DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `rosterCountry` varchar(255) DEFAULT NULL,
  `rosterProvince` varchar(255) DEFAULT NULL,
  `rosterCity` varchar(255) DEFAULT NULL,
  `eMail` varchar(255) DEFAULT NULL,
  `publicNotes` text,
  `privateNotes` text
) ENGINE=InnoDB AUTO_INCREMENT=1185 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemRosterNotDuplicate`
--

CREATE TABLE IF NOT EXISTS `systemRosterNotDuplicate` (
  `tableID` int(10) unsigned NOT NULL,
  `rosterID1` int(10) unsigned NOT NULL,
  `rosterID2` int(10) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `systemSchools`
--

CREATE TABLE IF NOT EXISTS `systemSchools` (
  `schoolID` int(10) unsigned NOT NULL,
  `schoolFullName` varchar(255) NOT NULL,
  `schoolShortName` varchar(255) DEFAULT NULL,
  `schoolBranch` varchar(255) DEFAULT NULL,
  `schoolAbreviation` varchar(255) DEFAULT NULL,
  `schoolCity` varchar(255) DEFAULT NULL,
  `schoolProvince` varchar(255) DEFAULT NULL,
  `schoolCountry` varchar(255) DEFAULT NULL,
  `schoolAddress` varchar(255) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=205 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemSchools`
--

INSERT INTO `systemSchools` (`schoolID`, `schoolFullName`, `schoolShortName`, `schoolBranch`, `schoolAbreviation`, `schoolCity`, `schoolProvince`, `schoolCountry`, `schoolAddress`) VALUES
(1, '', '', '', '*', '', '', '', NULL),
(2, 'Unaffiliated', 'Unaffiliated', '', '*', '', '', '', NULL),
(3, 'Blood and Iron Martial Arts', 'Blood and Iron', 'Burnaby', 'BnI', 'Burnaby', 'British Columbia', 'Canada', NULL),
(4, 'Blood and Iron Martial Arts', 'Blood and Iron', 'Victoria', 'BnI', 'Victoria', 'British Columbia', 'Canada', NULL),
(5, 'Phoenix Society of Historical Swordsmanship', 'Phoenix Society', '', 'PSHS', 'Phoenix', 'Arizona', 'USA', NULL),
(6, 'Mordhau Historical Combative', 'Mordhau', '', 'Mord', 'Phoenix', 'Arizona', 'USA', NULL),
(7, 'South Coast Swords', 'South Coast', '', 'SCS', 'Fullerton', 'California', 'USA', NULL),
(8, 'Albuquerque Sword Labs', 'Albuquerque', '', 'ASL', 'Albuqerque', 'New Mexico', 'USA', NULL),
(9, 'Academie voor Middeleeuwse Europese Krijgskunsten', 'AMEK', '', 'AMEK', 'Harlem', '', 'Netherlands', NULL),
(10, 'Davenriche European Martial Arts School', 'Davenriche', '', 'DEMAS', '', 'California', 'USA', NULL),
(11, 'Elite Fencing Club', 'EFC', '', 'EFC', 'Mexico City', 'CDMX', 'Mexico', NULL),
(12, 'Iron Gate Swordfighting', 'Iron Gate', '', 'IGS', 'Visalia', 'California', 'USA', NULL),
(13, 'Krieg School', 'Krieg School', 'Denver', 'KS', 'Denver', 'Colorado', 'USA', NULL),
(14, 'Kron Martial Arts', 'Kron', 'Fullerton', 'KRON', 'Fullerton', 'California', 'USA', NULL),
(15, 'Kron Martial Arts', 'Kron', 'Los Angeles', 'KRON', 'Los Angeles', 'California', 'USA', NULL),
(16, 'Maza de Plata', 'Maza de Plata', '', 'MdP', '', 'CDMX', 'Mexico', NULL),
(17, 'Esgrima Gratuza', 'Esgrima Gratuza', '', 'EG', '', 'CDMX', 'Mexico', NULL),
(18, 'Atlanta Freifechter', 'Atlanta Freifechter', '', 'AFF', 'Atlanta', 'Georgia', 'USA', NULL),
(19, 'New York Historical Fencing Association', 'New York', '', 'NYHFA', '', 'New York', 'USA', NULL),
(20, 'Nobel Science Academy', 'Nobel Science Academy', '', 'NSA', '', '', 'USA', NULL),
(21, 'Ochs America', 'Ochs America', '', 'OA', '', '', 'USA', NULL),
(22, 'Orden de Pendragon', 'Pendragon', '', 'OdP', '', 'CDMX', 'Mexico', NULL),
(23, 'Sword Carolina', 'Sword Carolina', '', 'SC', '', 'South Carolina', 'USA', NULL),
(24, 'Tattershall School of Defense', 'Tattershall', '', 'TSD', '', 'California', 'USA', NULL),
(25, 'Tosetti Institute of MMA and Fitness', 'Tosetti Institute', '', 'TIMAF', '', 'California', 'USA', NULL),
(27, 'UCSA', 'UCSA', '', 'UCSA', '', '', 'USA', NULL),
(28, 'Tucson Historic Fencing Club', 'Tucson Historic Fencing', '', 'THF', 'Tucson', 'Arizona', 'USA', NULL),
(29, 'Verde Valley Historical Swordsmanship', 'Verde Valley', '', 'VVHS', 'Prescott', 'Arizona', 'USA', NULL),
(30, 'Sacramento Sword School', 'Sacramento', '', 'SSS', 'Sacramento', 'California', 'USA', NULL),
(31, 'Historisch Vrijvechten Nederland', 'Historisch Vrijvechten Nederland', '', 'HVN', 'Harlem', '', 'Netherlands', NULL),
(32, 'Vor & Nach Society of Historical European Martial Arts', 'Vor & Nach', '', 'VnN', '', '', '', NULL),
(33, 'Blackfriar''s School of Fence', 'Blackfriar''s', '', 'BSF', '', '', '', NULL),
(34, 'Schola San Marco', 'Schola San Marco', '', 'SSM', '', '', 'USA', NULL),
(35, 'Capital Kunst Des Fechtens', 'Capital KDF', '', 'cKDF', 'Washington', 'DC', 'USA', NULL),
(36, 'BaerSwords School of Western Martial Arts', 'BaerSwords', '', 'BSSWMA', 'Kansas City', 'Missouri', 'USA', NULL),
(37, 'Tijuana Freifechter', 'Tijuana Freifechter', '', 'TJFF', '', '', '', NULL),
(38, 'Academy of Arms', 'Academy of Arms', 'Los Angeles', 'AoA', 'Los Angeles', 'California', 'USA', NULL),
(39, 'Lonin League', 'Lonin', '', 'LL', 'Seattle', 'Washington', 'USA', NULL),
(40, 'True Edge Academy', 'True Edge Academy', '', 'TEA', '', '', '', NULL),
(41, 'Kron Martial Arts', 'Kron', 'Inland Empire', 'Kron', '', 'California', 'USA', NULL),
(42, 'Swordguild Portland', 'Swordguild Portland', '', 'SGP', 'Portland', 'Oregon', 'USA', NULL),
(43, 'Austin Historical Weapons Guild', 'Austin HWG', '', 'AHWG', 'Austin', 'Texas', 'USA', NULL),
(44, 'Citadel Combat Arena', 'Citadel', '', 'CCA', '', '', '', NULL),
(45, 'Indes Western Martial Arts', 'Indes WMA', '', 'Indes', 'Portland', 'Oregon', 'USA', NULL),
(46, 'Grit City Historical European Martial Arts', 'Grit City HEMA', '', 'Grit', 'Tacoma', 'Washington', 'USA', NULL),
(47, 'Maryland Kuns des Fechten', 'Maryland KDF', '', 'MKdF', 'Columbia', 'Maryland', 'USA', NULL),
(48, 'Drei Wunder', 'Drei Wunder', '', 'DreiW', 'Portland', 'Oregon', 'USA', NULL),
(49, 'Academy of Historical Arts', 'Academy of Historical Arts', '', 'AoHA', 'Glasgow', '', 'Scotland', NULL),
(50, 'Iron Crown KDF', 'Iron Crown', '', 'ICKdF', 'Spokane', 'Washington', 'USA', NULL),
(51, 'Seven Swords Academy', 'Seven Swords', '', 'SSA', 'Tacoma', 'Washington', 'USA', NULL),
(52, 'Triangle Sword Guild', 'Triangle SG', '', 'TSG', 'Raleigh', 'North Carolina', 'USA', NULL),
(53, 'Okanogan Combat Guild', 'Okanogan CG', '', 'OCG', 'Kelowna', 'British Columbia', 'Canada', NULL),
(54, 'Ann Arbor Sword Club', 'Ann Arbor', '', 'AASC', 'Ann Arbor', 'Michigan', 'USA', NULL),
(55, 'Black Tigers', 'Black Tigers', '', 'BT', '', '', 'USA', NULL),
(56, 'Chicago Swordplay Guild', 'Chicago Swordplay Guild', '', 'CSG', 'Chicago', 'Illinois', 'USA', NULL),
(57, 'Sacramento Freifechter', 'Sacramento FF', '', 'SFF', 'Sacramento', 'California', 'USA', NULL),
(58, 'South Bay FMA Club', 'South Bay', '', 'SBFMA', '', 'California', 'USA', NULL),
(59, 'Göteborgs Historiska Fäktskola', 'Göteborgs', '', 'GHFS', 'Göteborgs', 'Västergötland', 'Sween', NULL),
(60, 'Scholars of Alcala', 'Scholars of Alcala', '', 'SoA', '', '', 'USA', NULL),
(61, 'Bay Area Freifechter', 'Bay Area FF', '', 'BAFF', '', '', 'USA', NULL),
(62, 'Decatur School of Arms', 'Decatur', '', 'DSA', '', '', 'USA', NULL),
(63, 'Emerald Coast HEMA Guild', 'Emerald Coast', '', 'ECHG', '', '', 'USA', NULL),
(64, 'Fresno Freifechter', 'Fresno Freifechter', '', 'FF', '', '', 'USA', NULL),
(65, 'Iron Shield', 'Iron Shield', '', 'IS', '', '', 'USA', NULL),
(66, 'Kali Dynamics', 'Kali Dynamics', '', 'KD', '', '', 'USA', NULL),
(67, 'Orange County HEMA', 'OC HEMA', '', 'OCH', '', '', 'USA', NULL),
(68, 'Sacto HEMA', 'Sacto', '', 'SH', '', '', 'USA', NULL),
(69, 'Loyal Order of the Sword', 'Loyal Order of the Sword', '', 'LOtS', '', '', 'USA', NULL),
(70, 'The Court of Swords', 'Court of Swords', '', 'CoS', '', '', 'USA', NULL),
(71, 'The Guild Academy', 'Guild Academy', '', 'GA', '', '', 'USA', NULL),
(72, 'Virginia Academy of Fencing', 'Virginia Academy', '', 'VA', '', '', 'USA', NULL),
(73, 'Hilt & Cross', 'Hilt & Cross', '', 'H&C', '', '', 'USA', NULL),
(74, 'Broken Plow', 'Plow', '', 'BP', 'Pittsburgh', 'PA', 'USA', NULL),
(75, 'New Jersey Historical Fencing Association', 'NJHFA', '', 'NJHFA', '', 'NJ', 'USA', NULL),
(76, 'WSTR', 'WSTR', '', 'WSTR', '', '', 'USA', NULL),
(77, 'Athena School of Arms', 'Athena', '', 'ASA', '', 'MA', 'USA', NULL),
(78, 'Gruberg Freifiechter', 'Gruberg', '', 'GF', '', '', 'USA', NULL),
(79, 'New Hampshire KDF', 'NHKDF', '', 'NHKDF', '', 'New Hampshire', 'USA', NULL),
(80, 'Pennsylvania Klopffechters', 'PA Klop', '', 'PK', '', 'PA', 'USA', NULL),
(81, 'Ohio Historical Fencing', 'OHF', '', 'OHF', '', 'OH', 'USA', NULL),
(82, 'Tri-State Historical Fencing', 'Tri-State', '', 'TSHF', '', 'NJ', 'USA', NULL),
(83, 'Long Island Historical Fencing', 'Long Island', '', 'LIHF', '', 'NY', 'USA', NULL),
(84, 'L''Arte Della Bellica', 'L''Arte', '', 'LADB', '', 'PA', 'USA', NULL),
(85, 'Los Angeles Historical Fencing Association', 'LAHFA', '', 'LAHFA', '', 'CA', 'USA', NULL),
(86, 'AHFS', 'AHFS', '', 'AHFS', '', '', 'Denmark', NULL),
(87, 'VCU HEMA Club', 'VCU HEMA', '', 'VCUHEMAC', '', '', 'USA', NULL),
(88, 'Virginia Academy of Fencing', 'VAF', '', 'VAF', '', 'VA', 'USA', NULL),
(89, 'Laurel City Historical Fencing ', 'Laurel City', '', 'LCHF', '', '', 'USA', NULL),
(90, 'PSV Karlsruhe', 'PSV Karlsruhe', '', 'PSV K', 'Karlsruhe', 'Baden-Württemberg', 'Germany', NULL),
(91, 'Burton Richardson''s Battlefield Kali', 'Battlefield Kali', '', 'BRBK', '', '', 'USA', NULL),
(92, 'Black Flag HEMA', 'Black Flag', '', 'BFH', '', '', '', NULL),
(93, 'Krigerskole', 'Krigerskole', '', 'KS', '', '', 'Mexico', NULL),
(94, 'Tulsa School of Defense', 'Tulsa', '', 'TSoD', '', '', 'USA', NULL),
(95, 'Pekiti-Tirsia Kali', 'Pekiti-Tirsia Kali', '', 'PTK', '', '', 'USA', NULL),
(96, 'Natural Spirit International', 'Natural Spirit International', '', 'NSI', '', '', 'USA', NULL),
(97, 'Sword to Sword', 'Sword to Sword', '', 'StS', '', 'Texas', 'USA', NULL),
(99, 'Espoo Association for Historical Fencing', 'EHMS', '', 'EHMS', '', '', 'Finland', NULL),
(100, 'Twin Moons', 'Twin Moons', '', 'Twin Moons', 'Mesa', 'Arizona', 'USA', NULL),
(101, 'Denver Historical Fencing Academy', 'Denver Academy', '', 'DHFA', 'Denver', 'Colorado', 'USA', NULL),
(102, 'Herzstich Duelling Guild', 'Herzstich', '', 'HDG', 'Tacoma', 'Washington', 'USA', NULL),
(103, 'Blackbird Training Group', 'Blackbird', '', 'BTG', '', 'Washington', 'USA', NULL),
(104, 'Academy of European Arms', 'Academy of European Arms', '', 'AEA', 'Portland', 'Oregon', 'USA', NULL),
(105, 'Sangi', 'Sangi', '', 'S', '', '', '', NULL),
(106, 'Wolfskopf Academy', 'Wolfskopf Academy', '', 'WA', '', '', '', NULL),
(107, 'Caballeros de ebano', 'Caballeros de ebano', '', 'C', 'Guadalajara', 'Guadalajara', 'MÃ©xico', NULL),
(108, 'Phoebus Ferratus', 'Phoebus Ferratus', '', 'PF', '', '', '', NULL),
(109, 'Arcant', 'Arcant', '', 'A', '', '', '', NULL),
(110, 'Oghma', 'Oghma', '', 'O', '', '', '', NULL),
(111, 'AEHCM HÃ¡bito de Santiago', 'AEHCM HÃ¡bito de Santiago', '', 'AEHCMHS', '', '', '', NULL),
(112, 'Espoon Historiallisen Miekkailun Seura', 'EHMS ry', '', 'EHMS', '', '', 'finland', NULL),
(113, 'Tramazzone', 'Tramazzone', '', 'T', '', '', 'Russia', NULL),
(114, 'MARS â€“ Vechtschool voor middeleeuwse krijgskunsten', 'MARS', '', 'MARSV', '', '', 'The Netherlands', NULL),
(115, 'fekteklubben frie duellister', 'FFKD', '', '', '', '', 'Norway', NULL),
(116, 'Zwaard & Steen', 'Zwaard & Steen', '', 'ZS', '', '', 'The Netherlands', NULL),
(117, 'FreiFechterGilde', 'FFG', '', 'FFG', '', '', 'Russia', NULL),
(118, 'GÃ¶teborgs Historiska FÃ¤ktskola', 'GHFS', '', 'GHF', '', '', 'Sweden', NULL),
(119, 'Stockholms StigmÃ¤n ', 'StigmÃ¤n', '', 'SS', '', '', 'Sweden', NULL),
(120, 'Uppsala Historiska FÃ¤tkskola', 'UHFS', '', 'UHF', '', '', 'Sewe', NULL),
(121, 'Uppsala Historiska FÃ¤tkskola', 'UHFS', '', 'UHF', '', '', 'Sweden', NULL),
(122, 'The institute for historical arts', 'The IHA', '', 'T', '', '', 'Scotland', NULL),
(123, 'Vaasan Miekkailija', 'VAMI ry', '', 'VM', '', '', 'Finland', NULL),
(124, 'MalmÃ¶ Historiska FÃ¤ktskola', 'MHFS', '', 'MHF', '', '', 'Sweden', NULL),
(125, 'fekteklubben frie duellister', 'FKFD', '', '', '', '', 'Norway', NULL),
(126, 'Joensuun Historiallisen Miekkailun Seura', 'JoHMS', '', 'JHMS', '', '', 'Finland', NULL),
(127, 'Ã–rebro Hema', 'Ã–HEMA', '', 'H', '', '', 'Sweden', NULL),
(128, 'Richmond Kunst des Fechtens', 'Richmond KDF', '', 'RKDF', 'Richmond', 'Virginia', 'USA', NULL),
(129, 'Medieval European Martial Arts Guild', 'MEMAG', '', 'MEMAG', '', '', 'USA', NULL),
(130, 'Fenris Kunst des Fechtens', 'Fenris Kunst des Fechtens', '', 'FKDF', 'West Virginia', '', 'USA', NULL),
(131, 'Steel City Historical Fencing', 'Steel City', '', 'SCHF', 'Pittsburgh', 'Pennsylvania', 'USA', NULL),
(132, 'Charlottesville HEMA', 'Charlottesville HEMA', '', 'CHEMA', 'Charlottesville', 'Virginia', 'USA', NULL),
(133, 'Rogue Fencing', 'Rogue Fencing', '', 'RF', 'New York', 'New York', 'USA', NULL),
(134, 'Philadelphia Common Fencers Guild', 'Philadelphia Common Fencers Guild', '', 'PCFG', 'Philadelphia', 'Pennsylvania', 'USA', NULL),
(135, 'Broken Plow Western Martial Arts', 'Broken Plow', '', 'BP', 'Pittsburgh', 'Pennsylvania', 'USA', NULL),
(136, 'Fighters Guild', 'Fighters Guild', '', 'FG', '', 'California', 'United States', NULL),
(137, 'Ordo Procinctus', 'Ordo', '', 'OP', 'Baton Rouge', 'Louisiana', 'USA', NULL),
(138, 'Academy of European Swordsmanship', 'Academy of European Swordsmanship', '', 'AES', 'Edmonton', 'Alberta', 'Canada', NULL),
(139, 'Esgrima HistÃ³rica Marte', 'Esgrima HistÃ³rica Marte', '', 'EHM', '', '', 'Mexico', NULL),
(140, 'SocietÃ  dei Vai', 'SocietÃ  dei Vai', '', 'SdV', '', '', 'Italy', NULL),
(141, 'The Academy of European Swordsmanship', 'The AES', 'Rimbey', 'AES', 'Rimbey', 'Alberta', 'Canada', NULL),
(142, 'The Forge Western Martial Arts', 'The Forge', '', 'FWMA', 'Canada', 'Alberta', 'Canada', NULL),
(143, 'PTK-SMF Peninsula', 'PTK-SMF Peninsula', '', 'PTKSMFP', 'Foster City', 'California', 'USA', NULL),
(144, 'Austin Warrior Arts', 'Austin Warrior Arts', '', 'AWA', 'Austin', 'Texas', 'USA', NULL),
(145, 'Sefe Dekote. Guild of the Silent School', 'Sefe Dekote. Guild of the Silent School', '', 'SDGSS', 'Austin', 'Texas', 'USA', NULL),
(146, 'Five Rings Fencing - Arkansas', 'Five Rings - Arkansas', 'Arkansas', '5Rings', 'Springdale', 'Arkansas', 'United States', NULL),
(147, 'Joplin HEMA: Blossfechten Academy', 'Joplin Hema', 'Five Rings Fencing - Arkansas', 'Joplin HEMA B.A.', 'Joplin', 'Missouri', 'United States', NULL),
(148, 'Columbus Saber Academy', 'Columbus Saber Academy', '', 'CSA', '', '', 'USA', NULL),
(149, 'Athena School of Arms', 'Athena School of Arms', '', 'ASA', '', '', 'USA', NULL),
(150, 'Martial Arts Research Systems', 'Martial Arts Research Systems', '', 'MARS', '', '', 'USA', NULL),
(151, 'Blue Wave Martial Arts', 'Blue Wave', '', 'BWMA', '', '', 'USA', NULL),
(152, 'HEMA Lexington', 'Lexington', '', 'LHEMA', '', '', 'USA', NULL),
(153, 'War Sword Historical Fencing', 'War Sword', '', 'WSHF', '', '', 'USA', NULL),
(154, 'Ritterschaft HEMA', 'Ritterschaft', '', 'RHEMA', '', '', 'Mexico', NULL),
(155, 'Husaria Academy of Sabre Fencing', 'Husaria Academy of Sabre Fencing', '', 'HASF', '', '', 'UK', NULL),
(156, 'Musketeer Fencing Club', 'Musketeer Fencing Club', '', 'MFC', '', '', 'USA', NULL),
(157, 'En Garde Fencing', 'En Garde Fencing', '', 'EGF', '', '', 'USA', NULL),
(158, 'Stirling Mercenaries', 'Stirling Mercenaries', '', 'SM', '', '', 'USA', NULL),
(159, 'Center for Blade Arts', 'Center for Blade Arts', '', 'CBA', '', '', 'USA', NULL),
(160, 'San Diego Renaissance Sword Arts', 'San Diego Renaissance Sword Arts', '', 'SDRSA', '', '', 'USA', NULL),
(161, 'Salle Saint-Georges', 'Salle Saint-Georges', '', 'SSG', '', '', 'USA', NULL),
(162, 'Sword Fighter', 'Sword Fighter', '', 'SF', 'Gold Coast', 'Queensland', 'Australia', NULL),
(163, 'Tattershall Tulsa', 'Tattershall Tulsa', '', 'TT', '', '', 'Usa', NULL),
(164, 'Mustang Sword Club', 'Mustang Sword Club', '', 'MSC', '', '', 'Canada', NULL),
(165, 'Central Iowa Historical Fencing Guild', 'Central Iowa Historical Fencing Guild', '', 'CIHFG', '', 'Iowa', 'USA', NULL),
(166, 'Nebraska Swordfighters Guild', 'Nebraska Swordfighters Guild', '', 'NSG', 'Lincoln', 'Nebraska', 'USA', NULL),
(167, 'Medieval Sword Guild of Kansas City', 'Medieval Sword Guild of Kansas City', '', 'MSGKC', '', '', 'USA', NULL),
(168, 'Omaha KDF', 'Omaha KDF', '', 'OKDF', 'Omaha', 'Nebraska', 'USA', NULL),
(169, 'Heartland HEMA', 'Heartland HEMA', '', 'HHEMA', '', '', 'USA', NULL),
(170, 'Adelaide Sword Academy', 'Adelaide Sword Academy', '', 'ASA', 'Adelaide', 'South Australia', 'Australia', NULL),
(171, 'Australis Scherma Scuola di Spada e Sciabola', 'Australis Scherma Scuola di Spada e Sciabola', '', 'ASSSS', 'Brisbane', 'Queensland', 'Australia', NULL),
(172, 'Brisbane Swords', 'Brisbane Swords', '', 'BS', 'Brisbane', 'Queensland', 'Australia', NULL),
(173, 'Brisbane School of Iberian Swordsmanship', 'Brisbane School of Iberian Swordsmanship', '', 'BSIS', 'Brisbane', 'Queensland', 'Australia', NULL),
(174, 'Collegium in Armis', 'Collegium in Armis', '', 'CIA', 'Brisbane', 'Queensland', 'Australia', NULL),
(175, 'The School of Historical Fencing', 'The School of Historical Fencing', '', 'SHF', 'Melbourne', 'Victoria', 'Australia', NULL),
(176, 'Ironclad Academy Of The Sword', 'Ironclad', '', 'IAOTS', 'Adelaide', 'South Australia', 'Australia', NULL),
(177, 'Prima Spada School of Fence', 'Prima Spada', '', 'PSSF', 'Brisbane', 'Queensland', 'Australia', NULL),
(178, 'Scholar Victoria', 'Scholar Victoria', '', 'SV', 'Melbourne', 'Victoria', 'Australia', NULL),
(179, 'The School of Historical Fencing', 'School of Historical Fencing', '', 'SHF', 'Melbourne', 'Victoria ', 'Australia', NULL),
(180, 'Vanguard Swordsmanship Academy', 'Vanguard ', '', 'VSA', 'Brisbane', 'Queensland', 'Australia', NULL),
(181, 'Spada di Bolognese', 'Spada di Bolognese', '', 'SDB', 'Brisbane', 'Queensland', 'Australia', NULL),
(182, 'Baer Swords School of Western Martial Arts', 'Baer Swords', '', 'BSWORDS', 'Kansas City', 'Missouri', 'USA', NULL),
(183, 'Blackhearts Fencing Club', 'Blackhearts', '', 'BFC', 'Witchita', 'Kansas', 'USA', NULL),
(184, 'Springfield Historical Fencing Guild', 'Springfield Fencing', '', 'SHFG', 'Springfield', 'Missouri', 'USA', NULL),
(185, 'Men at Arms Martial Arts', 'Men at Arms', '', 'MAA', 'Harrisburg', 'PA', 'United States', NULL),
(186, 'Big Tree Combat Club', 'Big Tree Combat Club', '', 'BTCC', '', '', 'Australia', NULL),
(187, 'GLECA', 'GLECA', '', 'GLECA', '', '', 'Australia', NULL),
(188, 'Historical Europe Rapier Academy', 'Historical Europe Rapier Academy', '', 'HERA', '', '', 'Australia', NULL),
(189, 'Elevator', 'Elevator', '', 'E', '', '', 'Australia', NULL),
(190, 'School of Historical Defence Arts', 'SHDA', '', 'SHDA', 'Brisbane', 'Queensland', 'Australia', NULL),
(191, 'Fechtschule Victoria', 'Fechtschule Victoria', '', 'FSV', '', 'Victoria', 'Australia', NULL),
(192, 'Leongatha Medieval Society', 'Leongatha Medieval Society', '', 'LMS', '', '', 'Australia', NULL),
(193, 'House Darksun', 'Darksun', '', 'HD', 'Perth', 'Western Australia', 'Australia', NULL),
(194, 'KDF Australia', 'KDF Australia', '', 'KDFA', '', '', 'Australia', NULL),
(195, 'Auckland Sword and Shield Society', 'Auckland Sword and Shield Society', '', 'ASSS', 'Auckland', '', 'New Zealand', NULL),
(196, 'Spada di Bolognese', 'Spada di Bolognese', '', 'SB', 'Brisbane', 'Queensland', 'Australia', NULL),
(197, 'Gem City Duelists Society', 'Gem City', '', 'GCDS', '', 'Ohio', 'United States', NULL),
(198, 'Halfmoon Hema', 'Halfmoon', '', 'HH', '', 'New York', 'United States', NULL),
(199, 'Errant Historical Fencing', 'Errant', '', 'EHF', '', 'New York', 'United States', NULL),
(200, 'The Evangelista School of Fencing', 'Evangelista School', '', 'Evangelista ', '', '', 'USA', NULL),
(201, 'Medieval Military Arts Academy:  HEMA of Springfield', 'Medieval Military Arts Academy', '', 'MMAA', '', '', 'USA', NULL),
(202, 'Black Wolf Historical Fencing', 'Black Wolf', '', 'BWHF', 'Danville', 'Pennsylvania', 'USA', NULL),
(203, 'New York Historical Fencing Association', 'New York Historical', 'North', 'NYHFA', 'Saugerties', 'New York', 'United States', NULL),
(204, 'New York Historical Fencing Association', 'New York RPI', 'Rensselaer', 'NYHFA Rensselaer', 'Troy', 'New York', 'USA', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `systemTournaments`
--

CREATE TABLE IF NOT EXISTS `systemTournaments` (
  `tournamentTypeID` int(10) unsigned NOT NULL,
  `tournamentTypeMeta` varchar(255) DEFAULT NULL,
  `tournamentType` varchar(255) DEFAULT NULL,
  `Pool_Bracket` tinyint(1) NOT NULL DEFAULT '1',
  `Pool_Sets` tinyint(1) NOT NULL DEFAULT '1',
  `Scored_Event` tinyint(1) NOT NULL DEFAULT '1',
  `numberOfInstances` int(10) unsigned DEFAULT NULL,
  `description` text,
  `functionName` text
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemTournaments`
--

INSERT INTO `systemTournaments` (`tournamentTypeID`, `tournamentTypeMeta`, `tournamentType`, `Pool_Bracket`, `Pool_Sets`, `Scored_Event`, `numberOfInstances`, `description`, `functionName`) VALUES
(1, 'weapon', 'Longsword', 1, 1, 1, 67, NULL, NULL),
(2, 'weapon', 'Messer', 1, 1, 1, 2, NULL, NULL),
(3, 'weapon', 'Sword and Buckler', 1, 1, 1, 14, NULL, NULL),
(4, 'weapon', 'Rapier', 1, 1, 1, 19, NULL, NULL),
(5, 'weapon', 'Singlestick', 1, 1, 1, 19, NULL, NULL),
(6, 'weapon', 'Dagger', 1, 1, 1, 1, NULL, NULL),
(7, 'weapon', 'Saber', 1, 1, 1, 4, NULL, NULL),
(8, 'weapon', 'Smallsword', 1, 1, 1, 0, NULL, NULL),
(9, 'weapon', 'Grappling', 1, 1, 1, 0, NULL, NULL),
(10, 'weapon', 'Multiple Weapon', 1, 1, 1, 5, NULL, NULL),
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
(21, 'gender', 'Women''s', 1, 1, 1, 0, NULL, NULL),
(22, 'gender', 'Men''s', 1, 1, 1, 0, NULL, NULL),
(23, 'material', NULL, 1, 1, 1, 0, NULL, NULL),
(24, 'material', 'Steel', 1, 1, 1, 0, NULL, NULL),
(25, 'material', 'Synthetic', 1, 1, 1, 0, NULL, NULL),
(26, 'material', 'Rattan', 1, 1, 1, 0, NULL, NULL),
(27, 'material', 'Mixed', 1, 1, 1, 0, NULL, NULL),
(28, 'ranking', 'Franklin 2016', 1, 0, 0, 0, NULL, NULL),
(29, 'ranking', '2 Pool Winners', 1, 0, 0, 0, NULL, NULL),
(30, 'ranking', 'Total Points Scored', 1, 0, 0, 0, NULL, NULL),
(31, 'ranking', 'CC Invitation 2016', 1, 0, 0, 0, NULL, NULL),
(32, 'weapon', 'Longsword Cutting', 1, 1, 1, 19, NULL, NULL),
(33, 'ranking', 'Results Only', 0, 0, 0, 0, NULL, NULL),
(34, 'weapon', 'Glima', 1, 1, 1, 7, NULL, NULL),
(35, 'weapon', 'Rotella', 1, 1, 1, 1, NULL, NULL),
(36, 'prefix', 'Lightweight', 1, 1, 1, 0, NULL, NULL),
(37, 'prefix', 'Middleweight', 1, 1, 1, 0, NULL, NULL),
(38, 'prefix', 'Heavyweight', 1, 1, 1, 0, NULL, NULL),
(39, 'weapon', 'Staff', 1, 1, 1, NULL, NULL, NULL),
(44, 'ranking', 'FNY 2017', 0, 1, 0, NULL, NULL, NULL),
(45, 'ranking', 'Eurofest 2017', 1, 0, 0, NULL, NULL, NULL),
(46, 'ranking', 'RMS Cutting', 0, 0, 1, NULL, NULL, 'RMScutting'),
(47, 'weapon', 'Cutting Quallification', 1, 1, 1, 1, NULL, NULL),
(48, 'weapon', 'Mixed Short Sword', 1, 1, 1, 2, NULL, NULL),
(49, 'weapon', 'Mixed Knife', 1, 1, 1, 1, NULL, NULL),
(50, 'ranking', 'Deduction Based', 0, 0, 1, NULL, NULL, 'DeductionBased'),
(51, 'weapon', 'Backsword', 1, 1, 1, 1, NULL, NULL),
(52, 'weapon', 'Broadsword', 1, 1, 1, 1, NULL, NULL),
(53, 'weapon', 'Single Handed Cutting', 1, 1, 1, 2, NULL, NULL),
(54, 'weapon', 'Dane Axe', 1, 1, 1, NULL, NULL, NULL),
(55, 'weapon', 'Bowie Knife', 1, 1, 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `systemUsers`
--

CREATE TABLE IF NOT EXISTS `systemUsers` (
  `logInID` int(10) unsigned NOT NULL,
  `logInName` varchar(255) NOT NULL,
  `logInType` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemUsers`
--

INSERT INTO `systemUsers` (`logInID`, `logInName`, `logInType`, `password`) VALUES
(1, 'Software Administrator', 'USER_SUPER_ADMIN', '$2y$10$7J.zzpe7De0HfQhI1wqP3OVH5aEZ2uwcGZZzS2PeehEL7vHwjypmi'),
(2, 'Video User', 'USER_VIDEO', '$2y$10$tfcwP0VThYHz1UGJvLduZu3ONy2fNSGsB1V372SX0wOajkCK85eSq'),
(3, 'Analytics User', 'USER_STATS', '$2y$10$XCsx/IOJsizHSkSvVxzEOOFanKb7E8NpPQQSD7ILqpp4HlfVw2s1e');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cuttingQualifications`
--
ALTER TABLE `cuttingQualifications`
  ADD PRIMARY KEY (`qualID`),
  ADD KEY `systemRosterID` (`systemRosterID`),
  ADD KEY `standardID` (`standardID`);

--
-- Indexes for table `cuttingStandards`
--
ALTER TABLE `cuttingStandards`
  ADD PRIMARY KEY (`standardID`);

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
-- Indexes for table `eventCuttingStandards`
--
ALTER TABLE `eventCuttingStandards`
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
-- Indexes for table `eventExchanges`
--
ALTER TABLE `eventExchanges`
  ADD PRIMARY KEY (`exchangeID`),
  ADD KEY `matchID` (`matchID`),
  ADD KEY `scorringID` (`scoringID`),
  ADD KEY `recievingID` (`recievingID`),
  ADD KEY `refPrefix` (`refPrefix`),
  ADD KEY `refTarget` (`refTarget`),
  ADD KEY `refType` (`refType`);

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
  ADD KEY `tournamentID` (`tournamentID`);

--
-- Indexes for table `eventIgnores`
--
ALTER TABLE `eventIgnores`
  ADD PRIMARY KEY (`ignoreID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `rosterID` (`rosterID`);

--
-- Indexes for table `eventLivestreamMatches`
--
ALTER TABLE `eventLivestreamMatches`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `eventID` (`eventID`,`matchID`),
  ADD KEY `matchID` (`matchID`);

--
-- Indexes for table `eventLivestreams`
--
ALTER TABLE `eventLivestreams`
  ADD PRIMARY KEY (`tableID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `matchID` (`matchID`);

--
-- Indexes for table `eventMatches`
--
ALTER TABLE `eventMatches`
  ADD PRIMARY KEY (`matchID`),
  ADD KEY `groupID` (`groupID`),
  ADD KEY `fighter1ID` (`fighter1ID`),
  ADD KEY `fighter2ID` (`fighter2ID`),
  ADD KEY `winnerID` (`winnerID`);

--
-- Indexes for table `eventPlacings`
--
ALTER TABLE `eventPlacings`
  ADD PRIMARY KEY (`placeID`),
  ADD KEY `tournamentID` (`tournamentID`),
  ADD KEY `rosterID` (`rosterID`);

--
-- Indexes for table `eventRoster`
--
ALTER TABLE `eventRoster`
  ADD PRIMARY KEY (`rosterID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `schoolID` (`schoolID`),
  ADD KEY `rosterID` (`systemRosterID`);

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
-- Indexes for table `eventTournamentRoster`
--
ALTER TABLE `eventTournamentRoster`
  ADD PRIMARY KEY (`tableID`),
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
  ADD KEY `elimTypeID` (`tournamentElimID`),
  ADD KEY `color1ID` (`color1ID`),
  ADD KEY `color2ID` (`color2ID`),
  ADD KEY `tournamentElimID` (`tournamentElimID`);

--
-- Indexes for table `systemAttacks`
--
ALTER TABLE `systemAttacks`
  ADD PRIMARY KEY (`attackID`);

--
-- Indexes for table `systemColors`
--
ALTER TABLE `systemColors`
  ADD PRIMARY KEY (`colorID`);

--
-- Indexes for table `systemDoubleTypes`
--
ALTER TABLE `systemDoubleTypes`
  ADD PRIMARY KEY (`doubleTypeID`);

--
-- Indexes for table `systemElimTypes`
--
ALTER TABLE `systemElimTypes`
  ADD PRIMARY KEY (`elimTypeID`);

--
-- Indexes for table `systemEvents`
--
ALTER TABLE `systemEvents`
  ADD PRIMARY KEY (`eventID`);

--
-- Indexes for table `systemMatchOrder`
--
ALTER TABLE `systemMatchOrder`
  ADD PRIMARY KEY (`tableID`);

--
-- Indexes for table `systemRankings`
--
ALTER TABLE `systemRankings`
  ADD PRIMARY KEY (`tournamentRankingID`);

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
  ADD FULLTEXT KEY `schoolFullName` (`schoolFullName`);

--
-- Indexes for table `systemTournaments`
--
ALTER TABLE `systemTournaments`
  ADD PRIMARY KEY (`tournamentTypeID`);

--
-- Indexes for table `systemUsers`
--
ALTER TABLE `systemUsers`
  ADD PRIMARY KEY (`logInID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cuttingQualifications`
--
ALTER TABLE `cuttingQualifications`
  MODIFY `qualID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=195;
--
-- AUTO_INCREMENT for table `cuttingStandards`
--
ALTER TABLE `cuttingStandards`
  MODIFY `standardID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `eventAttacks`
--
ALTER TABLE `eventAttacks`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=667;
--
-- AUTO_INCREMENT for table `eventAttributes`
--
ALTER TABLE `eventAttributes`
  MODIFY `attributeID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=409;
--
-- AUTO_INCREMENT for table `eventCuttingStandards`
--
ALTER TABLE `eventCuttingStandards`
  MODIFY `qualID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `eventDefaults`
--
ALTER TABLE `eventDefaults`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=90;
--
-- AUTO_INCREMENT for table `eventExchanges`
--
ALTER TABLE `eventExchanges`
  MODIFY `exchangeID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=55390;
--
-- AUTO_INCREMENT for table `eventGroupRoster`
--
ALTER TABLE `eventGroupRoster`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6692;
--
-- AUTO_INCREMENT for table `eventGroups`
--
ALTER TABLE `eventGroups`
  MODIFY `groupID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1876;
--
-- AUTO_INCREMENT for table `eventIgnores`
--
ALTER TABLE `eventIgnores`
  MODIFY `ignoreID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=114;
--
-- AUTO_INCREMENT for table `eventLivestreamMatches`
--
ALTER TABLE `eventLivestreamMatches`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventLivestreams`
--
ALTER TABLE `eventLivestreams`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `eventMatches`
--
ALTER TABLE `eventMatches`
  MODIFY `matchID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15046;
--
-- AUTO_INCREMENT for table `eventPlacings`
--
ALTER TABLE `eventPlacings`
  MODIFY `placeID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2752;
--
-- AUTO_INCREMENT for table `eventRoster`
--
ALTER TABLE `eventRoster`
  MODIFY `rosterID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2242;
--
-- AUTO_INCREMENT for table `eventStandings`
--
ALTER TABLE `eventStandings`
  MODIFY `standingID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=123255;
--
-- AUTO_INCREMENT for table `eventTeamRoster`
--
ALTER TABLE `eventTeamRoster`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventTournamentRoster`
--
ALTER TABLE `eventTournamentRoster`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4387;
--
-- AUTO_INCREMENT for table `eventTournaments`
--
ALTER TABLE `eventTournaments`
  MODIFY `tournamentID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=255;
--
-- AUTO_INCREMENT for table `systemAttacks`
--
ALTER TABLE `systemAttacks`
  MODIFY `attackID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `systemColors`
--
ALTER TABLE `systemColors`
  MODIFY `colorID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `systemDoubleTypes`
--
ALTER TABLE `systemDoubleTypes`
  MODIFY `doubleTypeID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `systemElimTypes`
--
ALTER TABLE `systemElimTypes`
  MODIFY `elimTypeID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `systemEvents`
--
ALTER TABLE `systemEvents`
  MODIFY `eventID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=47;
--
-- AUTO_INCREMENT for table `systemMatchOrder`
--
ALTER TABLE `systemMatchOrder`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=166;
--
-- AUTO_INCREMENT for table `systemRankings`
--
ALTER TABLE `systemRankings`
  MODIFY `tournamentRankingID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `systemRoster`
--
ALTER TABLE `systemRoster`
  MODIFY `systemRosterID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1185;
--
-- AUTO_INCREMENT for table `systemRosterNotDuplicate`
--
ALTER TABLE `systemRosterNotDuplicate`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `systemSchools`
--
ALTER TABLE `systemSchools`
  MODIFY `schoolID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=205;
--
-- AUTO_INCREMENT for table `systemTournaments`
--
ALTER TABLE `systemTournaments`
  MODIFY `tournamentTypeID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=56;
--
-- AUTO_INCREMENT for table `systemUsers`
--
ALTER TABLE `systemUsers`
  MODIFY `logInID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `cuttingQualifications`
--
ALTER TABLE `cuttingQualifications`
  ADD CONSTRAINT `cuttingqualifications_ibfk_1` FOREIGN KEY (`systemRosterID`) REFERENCES `systemRoster` (`systemRosterID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `cuttingqualifications_ibfk_2` FOREIGN KEY (`standardID`) REFERENCES `cuttingStandards` (`standardID`) ON UPDATE CASCADE;

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
-- Constraints for table `eventCuttingStandards`
--
ALTER TABLE `eventCuttingStandards`
  ADD CONSTRAINT `eventCuttingStandards_ibfk_2` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventDefaults`
--
ALTER TABLE `eventDefaults`
  ADD CONSTRAINT `eventdefaults_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventdefaults_ibfk_2` FOREIGN KEY (`color1ID`) REFERENCES `systemColors` (`colorID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `eventdefaults_ibfk_3` FOREIGN KEY (`color2ID`) REFERENCES `systemColors` (`colorID`) ON UPDATE CASCADE;

--
-- Constraints for table `eventExchanges`
--
ALTER TABLE `eventExchanges`
  ADD CONSTRAINT `eventExchanges_ibfk_1` FOREIGN KEY (`matchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventExchanges_ibfk_2` FOREIGN KEY (`scoringID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventExchanges_ibfk_3` FOREIGN KEY (`recievingID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventExchanges_ibfk_4` FOREIGN KEY (`refPrefix`) REFERENCES `systemAttacks` (`attackID`),
  ADD CONSTRAINT `eventExchanges_ibfk_5` FOREIGN KEY (`refTarget`) REFERENCES `systemAttacks` (`attackID`),
  ADD CONSTRAINT `eventExchanges_ibfk_6` FOREIGN KEY (`refType`) REFERENCES `systemAttacks` (`attackID`);

--
-- Constraints for table `eventGroupRoster`
--
ALTER TABLE `eventGroupRoster`
  ADD CONSTRAINT `eventGroupRoster_ibfk_2` FOREIGN KEY (`groupID`) REFERENCES `eventGroups` (`groupID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventGroupRoster_ibfk_3` FOREIGN KEY (`tournamentTableID`) REFERENCES `eventTournamentRoster` (`tableID`) ON DELETE CASCADE;

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
-- Constraints for table `eventLivestreamMatches`
--
ALTER TABLE `eventLivestreamMatches`
  ADD CONSTRAINT `eventLivestreamMatches_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventLivestreamMatches_ibfk_2` FOREIGN KEY (`matchID`) REFERENCES `eventMatches` (`matchID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventLivestreams`
--
ALTER TABLE `eventLivestreams`
  ADD CONSTRAINT `eventLivestreams_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eventMatches`
--
ALTER TABLE `eventMatches`
  ADD CONSTRAINT `eventMatches_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `eventGroups` (`groupID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventMatches_ibfk_2` FOREIGN KEY (`fighter1ID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventMatches_ibfk_3` FOREIGN KEY (`fighter2ID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventMatches_ibfk_4` FOREIGN KEY (`winnerID`) REFERENCES `eventRoster` (`rosterID`) ON DELETE CASCADE;

--
-- Constraints for table `eventPlacings`
--
ALTER TABLE `eventPlacings`
  ADD CONSTRAINT `eventPlacings_ibfk_1` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`);

--
-- Constraints for table `eventRoster`
--
ALTER TABLE `eventRoster`
  ADD CONSTRAINT `eventRoster_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventRoster_ibfk_2` FOREIGN KEY (`schoolID`) REFERENCES `systemSchools` (`schoolID`),
  ADD CONSTRAINT `eventRoster_ibfk_3` FOREIGN KEY (`systemRosterID`) REFERENCES `systemRoster` (`systemRosterID`);

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
  ADD CONSTRAINT `eventTeamRoster_ibfk_3` FOREIGN KEY (`tournamentRosterID`) REFERENCES `eventTournamentRoster` (`tableID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `eventTournaments_ibfk_14` FOREIGN KEY (`tournamentElimID`) REFERENCES `systemElimTypes` (`elimTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_15` FOREIGN KEY (`tournamentRankingID`) REFERENCES `systemRankings` (`tournamentRankingID`),
  ADD CONSTRAINT `eventTournaments_ibfk_2` FOREIGN KEY (`tournamentWeaponID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_3` FOREIGN KEY (`tournamentPrefixID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_4` FOREIGN KEY (`tournamentSuffixID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_5` FOREIGN KEY (`tournamentGenderID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_6` FOREIGN KEY (`tournamentMaterialID`) REFERENCES `systemTournaments` (`tournamentTypeID`),
  ADD CONSTRAINT `eventTournaments_ibfk_8` FOREIGN KEY (`doubleTypeID`) REFERENCES `systemDoubleTypes` (`doubleTypeID`);

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
