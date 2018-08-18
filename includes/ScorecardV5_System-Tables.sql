-- phpMyAdmin SQL Dump
-- version 4.4.15.9
-- https://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2018 at 04:35 PM
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
-- Table structure for table `systemAttacks`
--

CREATE TABLE IF NOT EXISTS `systemAttacks` (
  `attackID` int(10) unsigned NOT NULL,
  `attackClass` varchar(255) NOT NULL,
  `attackCode` varchar(255) NOT NULL,
  `attackText` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

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
(13, 'prefix', 'afterblow', 'Afterblow');

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
  `groupWinnersFirst` int(11) NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemRankings`
--

INSERT INTO `systemRankings` (`tournamentRankingID`, `name`, `numberOfInstances`, `description`, `Pool_Bracket`, `Pool_Sets`, `Scored_Event`, `displayFunction`, `scoringFunction`, `rankingFunction`, `advancementFunction`, `scoreFormula`, `groupWinnersFirst`, `orderByField1`, `orderBySort1`, `orderByField2`, `orderBySort2`, `orderByField3`, `orderBySort3`, `orderByField4`, `orderBySort4`, `displayTitle1`, `displayField1`, `displayTitle2`, `displayField2`, `displayTitle3`, `displayField3`, `displayTitle4`, `displayField4`, `displayTitle5`, `displayField5`) VALUES
(1, 'Franklin 2014', 82, 'Calculation:\n +[Points For]\n +(5 * [Wins])\n -[Points Against]\n -(Doubles Penalty)\n\nDoubles Penalty\n1 Double -> 1 = 1\n2 Doubles -> 1+2 = 3\n3 Doubles -> 1+2+3 = 6 etc...\n\nRanking:\n1) Pool winners first\n2) Score\n3) Wins\n4) Doubles', 1, 0, 0, NULL, NULL, NULL, NULL, '(5*wins) + pointsFor - pointsAgainst - ((doubles * (doubles+1))/2)', 1, 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(2, 'RSS Cutting', 11, 'Root Sum Square Cutting\n\nScoring\nTotal Deduction = sqrt([Cut Deduction]^2 + [Form Deduction]^2)\nScore = 20 - Cut Deduction\n\nRanking\n1) By Score\n2) Least deductions', 0, 0, 1, 'RSScutting', 'RSScutting', NULL, NULL, NULL, 0, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Results Only', 12, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Deduction Based', 2, 'Scoring\r\n100 point base score\r\nDeductions from the base score', 0, 0, 1, 'DeductionBased', 'DeductionBased', NULL, NULL, 'pointsFor', 0, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'FNY 2017', 2, 'Fechtshule New York 2017\r\n\r\nScoring:\r\nOne exchange matches\r\n+ 1*Wins\r\n- 2*[Losses]\r\n- 2*[Doubles]\r\n\r\nRanking:\r\nCumulative across multiple pools', 0, 1, 0, NULL, NULL, NULL, 'FNY2017', 'pointsFor - 2 * (losses + doubles)', 0, 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Pushes', 'matches - hitsFor - losses - doubles', 'Losses', 'losses', 'Doubles', 'doubles', 'Score', 'score'),
(7, 'Total Points Scored', 6, 'Scoring\r\n +[Points For]\r\n\r\nRanking\r\n1) Wins\r\n2) Score\r\n3) Doubles', 1, 0, 0, NULL, NULL, NULL, NULL, 'pointsFor', 0, 'wins', 'DESC', 'score', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Hit Ratio', 2, 'Score\r\n[Points For] / [Total Times Hit]\r\n\r\nRanking\r\n1) Score\r\n2) Wins', 1, 0, 0, NULL, NULL, NULL, NULL, 'case \n	when (hitsAgainst + afterblowsAgainst + doubles) > 0 then\n		pointsFor /  (hitsAgainst + afterblowsAgainst + doubles)\n	else\n		9001\nend', 0, 'score', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', NULL, NULL, 'Points For', 'pointsFor', 'Total Times Hit', 'hitsAgainst + afterblowsAgainst + doubles', 'Score', 'score', NULL, NULL, NULL, NULL),
(9, 'Sandstorm 2017', 2, 'Scoring:\r\n3 Points - Controlled Win/Artful Exchange\r\n2 Points - Win\r\n1 Point - Win w/ Afterblow\r\n\r\nRanking:\r\nBy Score\r\n', 1, 0, 0, NULL, NULL, NULL, NULL, 'pointsFor - doubles', 0, 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Control Wins', 'score + doubles - (2*wins) - (3*afterblowsAgainst)', 'Wins', '(3 * wins) - (2 * afterblowsAgainst) - score + doubles', 'Afterblow Wins', 'afterblowsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(10, '2 Point Cumulative', 1, 'Used for Singlestick in Helsinki Open 2018\r\n\r\nScoring:\r\n2 Points for Win\r\n1 Point for Tie\r\n\r\nRanking:\r\nBy Score', 0, 1, 0, NULL, NULL, NULL, 'FNY2017', '(2 * wins) + ties', 0, 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Ties', 'ties', 'Losses', 'losses', 'Score', 'score', NULL, NULL),
(11, 'Flowerpoint', 2, 'Score\r\n-1 Point for every time hit\r\n(Scoring action or double)\r\n\r\nRanking\r\nBy score', 0, 1, 0, NULL, NULL, NULL, 'Flowerpoint', '0 - hitsAgainst - doubles', 0, 'score', 'DESC', 'doubles', 'ASC', 'wins', 'DESC', NULL, NULL, 'Number of Times Hit', 'hitsAgainst', 'Doubles', 'doubles', 'Score', 'score', NULL, NULL, NULL, NULL),
(13, 'Thokk Continuous', 1, NULL, 0, 1, 0, NULL, NULL, NULL, 'SimpleRankOrder', '0 - AbsPointsAgainst', 0, 'hitsAgainst', 'ASC', 'hitsFor', 'DESC', 'score', 'DESC', NULL, NULL, 'Bouts Won', 'hitsFor', 'Bouts Lost', 'hitsAgainst', 'Points Against', 'pointsAgainst', NULL, NULL, NULL, NULL),
(14, 'Alls Fair', 2, 'Ranking:\r\n1) Wins\r\n2) Doubles\r\n3) Points +/-', 0, 1, 0, NULL, NULL, NULL, 'SimpleRankOrder', 'pointsFor - pointsAgainst', 0, 'wins', 'DESC', 'doubles', 'ASC', 'score', 'DESC', NULL, NULL, 'Wins', 'wins', 'Doubles', 'doubles', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Points +/-', 'score'),
(15, 'JNCR', 1, 'Julian''s Nameless Cutting Rules\r\n\r\nCuts are assigned scored as follows:\r\n8 points cut quality\r\n4 points upper body form\r\n4 points lower body form\r\n\r\n0 in cut quality or 0 in combined form is 0 for the entire cut.\r\n\r\nA negative score in any of the three becomes the final score.\r\n\r\nA cut with perfect scores earns an additional +4 points.', 0, 0, 1, 'JNCR', 'JNCR', NULL, NULL, NULL, 0, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Score', 'score', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Aussie Reversed', 8, '<u>This score mode is meant to be used with reverse scores!</u>\r\n\r\nPoints are assigned to the fighter who was hit.\r\n\r\nRanking:\r\n1) Wins\r\n2) Least points hit with (this is the points you give to the fighter!)', 1, 1, 0, NULL, NULL, NULL, NULL, 'AbsPointsAgainst', 0, 'wins', 'DESC', 'AbsPointsAgainst', 'ASC', NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Points Against', 'score', NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'AHWG 2018', 1, 'Austin Historical Weapons Guild\r\n\r\nFor use with single hit matches\r\nScore = Wins - Losses - Double Outs', 1, 1, 0, NULL, NULL, NULL, NULL, 'wins - losses - doubleOuts', 0, 'score', 'DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Wins', 'wins', 'Losses', 'losses', 'Doubles', 'doubles', 'Score', 'score', NULL, NULL),
(18, 'MART', 0, 'Mid Atlantic Rookie Tournament: Fighty McFightface', 1, 1, 0, NULL, NULL, NULL, NULL, '0', 0, 'wins', 'DESC', 'doubles', 'ASC', 'AbsPointsAgainst', 'ASC', 'AbsPointsFor', 'DESC', 'Wins', 'wins', 'Doubles', 'doubles', 'Points Against', 'AbsPointsAgainst', 'Points For', 'AbsPointsFor', NULL, NULL),
(19, 'Franklin 2014 (x25)', 7, 'Franklin 2014 with even stronger doubles penalty\r\n\r\nCalculation:\r\n +[Points For]\r\n +(5 * [Wins])\r\n -[Points Against]\r\n -(Doubles Penalty) * 1.25\r\n\r\nDoubles Penalty\r\n1 Double -> 1 = 1\r\n2 Doubles -> 1+2 = 3\r\n3 Doubles -> 1+2+3 = 6 etc...\r\n\r\nRanking:\r\n1) Pool winners first\r\n2) Score\r\n3) Wins\r\n4) Doubles', 1, 1, 0, NULL, NULL, NULL, NULL, '(5*wins) + pointsFor - pointsAgainst - (1.25*(doubles * (doubles+1))/2)', 1, 'score', 'DESC', 'wins', 'DESC', 'doubles', 'ASC', 'hitsAgainst', 'ASC', 'Wins', 'wins', 'Points For', 'pointsFor', 'Points Against', 'pointsAgainst', 'Doubles', 'doubles', 'Score', 'score'),
(20, 'Baer Score', 1, 'Sort By:\r\n1) Wins\r\n2) Points Against\r\n3) Doubles', 1, 1, 0, NULL, NULL, NULL, NULL, '0', 0, 'wins', 'DESC', 'AbsPointsAgainst', 'ASC', 'doubles', 'ASC', NULL, NULL, 'Wins', 'wins', 'Points Against', 'AbsPointsAgainst', 'Doubles', 'doubles', NULL, NULL, NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=1044 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemRoster`
--

INSERT INTO `systemRoster` (`systemRosterID`, `firstName`, `middleName`, `lastName`, `nickname`, `gender`, `schoolID`, `HemaRatingsID`, `birthdate`, `rosterCountry`, `rosterProvince`, `rosterCity`, `eMail`, `publicNotes`, `privateNotes`) VALUES
(2, 'Alaa', NULL, 'Bartley', NULL, 'Male', 5, 1586, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Ben', NULL, 'Blythe', NULL, 'Male', 5, 846, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Douglas', NULL, 'Bostic', NULL, 'Male', 28, 849, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Shane', NULL, 'Gibson', NULL, 'Male', 5, 786, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'James Harvey', NULL, 'Grant', NULL, 'Male', 5, 4624, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Randy', NULL, 'Reyes', NULL, 'Male', 5, 860, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'Jay', NULL, 'Voris', NULL, 'Male', 28, 1230, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'Robert', NULL, 'Kuciver', NULL, 'Male', 28, 861, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'Alexander', NULL, 'Plummer', NULL, 'Male', 5, 810, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'Benjamin', NULL, 'Winnick', NULL, 'Male', 5, 1253, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'Damien', NULL, 'Allen', NULL, 'Male', 5, 1232, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'Steven', NULL, 'Gotcher', NULL, 'Male', 6, 805, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'Clifford', NULL, 'Curry', NULL, 'Male', 6, 776, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Cameron', NULL, 'Maurin', NULL, 'Male', 6, 1233, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'Michael', NULL, 'Woodford', NULL, 'Male', 5, 814, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'Jay', NULL, 'Simpson', NULL, 'Male', 5, 1234, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'Micah', NULL, 'Palacio', NULL, 'Male', 5, 1238, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'Hassan', NULL, 'Masood', NULL, 'Male', 5, 1235, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'John', NULL, 'Matchete', NULL, 'Male', 5, 1236, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'Jesus', NULL, 'Valdez', NULL, 'Male', 5, 1237, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'Dimitrios', NULL, 'Stephanoy', NULL, 'Male', 3, 754, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 'Meena', NULL, 'Lidder', NULL, 'Female', 3, 1273, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 'Kevin', NULL, 'Campbell', NULL, 'Male', 3, 1250, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 'Stephen', NULL, 'Kime', NULL, 'Male', 3, 638, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'Amanda', NULL, 'Clark', NULL, 'Female', 3, 1259, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 'Sean', NULL, 'Franklin', NULL, 'Male', 2, 637, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 'Lee', NULL, 'Smith', NULL, 'Male', 3, 634, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 'Nicole', NULL, 'Smith', NULL, 'Female', 3, 652, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 'Tim', NULL, 'Magnuson', NULL, 'Male', 7, 842, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 'RJ', NULL, 'McKeehan', NULL, 'Male', 7, 671, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 'Gregory', NULL, 'Burke', NULL, 'Male', 21, 1278, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 'William', NULL, 'Cryts II', NULL, 'Male', 20, 1251, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 'Joshua', NULL, 'Gilbrech', NULL, 'Male', 8, 798, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(37, 'Nikolas', NULL, 'Miller', NULL, 'Male', 2, 1239, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'Christopher', NULL, 'Green', NULL, 'Male', 12, 816, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, 'John', NULL, 'Patterson', NULL, 'Male', 5, 625, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 'Phillip', NULL, 'Martin', NULL, 'Male', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 'Nathan', NULL, 'Clough', NULL, 'Male', 2, 406, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(43, 'Karl', NULL, 'Bolle', NULL, 'Male', 35, 379, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, 'Dayna', NULL, 'Rowden', NULL, 'Female', 35, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(45, 'Leyla', NULL, 'Azizova', NULL, 'Female', 10, 801, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(46, 'Skye', NULL, 'Hilton', NULL, 'Female', 10, 1280, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(47, 'Jack', NULL, 'McKeane', NULL, 'Male', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 'Po Tsen', NULL, 'Kuo', NULL, 'Male', 32, 1240, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 'Brian', NULL, 'Stewart', NULL, 'Male', 10, 812, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(50, 'Nathan', NULL, 'Wheatley', NULL, 'Male', 2, 1260, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(51, 'Baer', NULL, 'Kenney', NULL, 'Male', 36, 1241, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 'Matthew', NULL, 'Roop-Kharasch', NULL, 'Male', 38, 1976, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(53, 'Robert Earl', NULL, 'Barnhill', NULL, 'Male', 36, 1242, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(54, 'Dean', NULL, 'Flagg', NULL, 'Male', 38, 1243, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(55, 'Kanstantsin', NULL, 'Mianzhynski', NULL, 'Male', 3, 1252, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 'Christopher', NULL, 'Yang', NULL, 'Male', 14, 790, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, 'Matthew', NULL, 'Roche', NULL, 'Male', 39, 770, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(59, 'Gary', NULL, 'Gibson', NULL, 'Male', 34, 1254, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, 'Russell', NULL, 'Mackler', NULL, 'Male', 5, 1255, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(61, 'Robert', NULL, 'Howard', NULL, 'Male', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(62, 'Arthur', NULL, 'Agdeppa', NULL, 'Male', 1, 1244, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(63, 'Jim', NULL, 'Barrows', NULL, 'Male', 1, 364, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(65, 'Jeremy', NULL, 'Halliday', NULL, 'Male', 40, 487, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(66, 'Jesse', NULL, 'Franco', NULL, 'Male', 12, 852, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(67, 'John', NULL, 'Rechtoris', NULL, 'Male', 8, 1256, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(68, 'Herry', NULL, 'Chen', NULL, 'Male', 2, 795, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(69, 'Charles', NULL, 'Buschmann', NULL, 'Male', 5, 815, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(70, 'David', NULL, 'Suh', NULL, 'Male', 7, 1257, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(71, 'Donovan', NULL, 'Malet', NULL, 'Male', 13, 1270, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(72, 'Leanna', NULL, 'Beauchamp', NULL, 'Female', 28, 1258, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(73, 'Alex', NULL, 'Laslavic', NULL, 'Male', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(74, 'Thomas', NULL, 'Morin', NULL, 'Male', 28, 865, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(75, 'Erik', NULL, 'Bailes', NULL, 'Male', 4, 728, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(76, 'Synde', NULL, 'Tarasenko', NULL, 'Female', 4, 1281, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(77, 'Lianna', NULL, 'Teeter', NULL, 'Female', 4, 1274, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(78, 'Jesse', NULL, 'Tucker', NULL, 'Male', 3, 630, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(79, 'Allan', NULL, 'Sherlock', NULL, 'Male', 7, 811, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(80, 'Tracy', NULL, 'Mellow', NULL, 'Male', 12, 843, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(81, 'Roberto', NULL, 'Martinez-Loyo', NULL, 'Male', 11, 648, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(82, 'Jacob', NULL, 'Plumb', NULL, 'Male', 12, 820, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(83, 'Charlton', NULL, 'Jackson', NULL, 'Male', 34, 1261, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(84, 'Eric', NULL, 'Hardeman', NULL, 'Male', 7, 1422, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(85, 'Christopher', NULL, 'Nelson', NULL, 'Male', 5, 775, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(87, 'Alec', NULL, 'Plumb', NULL, 'Male', 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(88, 'Tony', NULL, 'Huang', NULL, 'Male', 3, 658, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(91, 'Jane', NULL, 'Johnston', NULL, 'Female', 3, 1275, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(92, 'Meghan', NULL, 'Citra', NULL, 'Female', 3, 768, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(93, 'Alex', NULL, 'Yang', NULL, 'Male', 3, 3618, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(94, 'Joe', NULL, 'Gundersen', NULL, 'Male', 3, 1399, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(95, 'Reese', NULL, 'Pollock', NULL, 'Male', 3, 1394, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(96, 'Jen', NULL, 'Bowles', NULL, 'Female', 4, 1396, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(97, 'Tyler', NULL, 'Corston-Oliver', NULL, 'Male', 3, 1406, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(98, 'David', NULL, 'Laudenslager', NULL, 'Male', 10, 1276, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(99, 'Jonathan', NULL, 'Ying', NULL, 'Male', 7, 779, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(100, 'Alex', NULL, 'Munro', NULL, 'Male', 28, 1245, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(101, 'Rachel', NULL, 'Van Dyke', NULL, 'Female', 7, 803, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(102, 'Omar', NULL, 'Macias', NULL, 'Male', 37, 5120, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(103, 'Roman', NULL, 'Sadorf', NULL, 'Male', 28, 1271, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(104, 'Exiel', NULL, 'Li', NULL, 'Male', 32, 1247, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(105, 'Albert', NULL, 'Toberer', NULL, 'Male', 33, 1262, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(107, 'Shane', NULL, 'Hinckley', NULL, 'Male', 1, 1272, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(108, 'Danny', NULL, 'Miller', NULL, 'Male', 3, 727, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(109, 'Nolan', NULL, 'Lindberg', NULL, 'Male', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(110, 'Geoff', NULL, 'Lowrey', NULL, 'Male', 39, 1277, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(111, 'Ramon', NULL, 'Santos', NULL, 'Male', 12, 1248, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(112, 'Kyle', NULL, 'Griswold', NULL, 'Male', 6, 626, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(113, 'Ian', NULL, 'McLean', NULL, 'Male', 12, 819, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(114, 'Steve', NULL, 'Frostrom', NULL, 'Male', 34, 1264, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(115, 'Justinder', NULL, 'Singh', NULL, 'Male', 3, 1265, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(116, 'Charlie', NULL, 'Kallberg', NULL, 'Male', 21, 789, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(117, 'Casey', NULL, 'Por', NULL, 'Male', 3, 741, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(118, 'Harbell', NULL, 'ILustre', NULL, 'Male', 14, 1266, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(119, 'Nicholas', NULL, 'Craft', NULL, 'Male', 14, 1267, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(120, 'Shaun', NULL, 'Butler', NULL, 'Male', 38, 1268, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(121, 'Victoria', NULL, 'Andrade Mckeehan', NULL, 'Female', 7, 732, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(122, 'Myles', NULL, 'Cupp', NULL, 'Male', 7, 405, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(123, 'Christopher', NULL, 'Ponzillo', NULL, 'Male', 7, 1269, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(124, 'Mishael Lopes', NULL, 'Cardozo', NULL, 'Male', 9, 663, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(125, 'Keith', NULL, 'Cotter-Reilly', NULL, 'Male', 18, 218, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(126, 'Brittany', NULL, 'Reeves', NULL, 'Female', 6, 773, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(127, 'Don', NULL, 'Madlung', NULL, 'Male', 3, 756, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(128, 'Kevin', NULL, 'de Ridder', NULL, 'Male', 3, 763, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(129, 'Ryan', NULL, 'Ward-Hall', NULL, 'Male', 4, 804, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(130, 'Kenneth', NULL, 'Morlock', NULL, 'Male', 10, 800, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(131, 'Thomas', NULL, 'Levine', NULL, 'Male', 10, 787, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(132, 'Christoper', NULL, 'Nava Delgado', NULL, 'Male', 11, 774, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(133, 'Meg', NULL, 'Floyd', NULL, 'Female', 13, 264, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(134, 'Aaron', NULL, 'Karnuta', NULL, 'Male', 101, 274, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(135, 'Adam', NULL, 'Simmons', NULL, 'Male', 5, 3094, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(136, 'RJ', NULL, 'Hebner', NULL, 'Male', 5, 784, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(137, 'Richard', NULL, 'Marsden', NULL, 'Male', 5, 627, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(138, 'Martin', NULL, 'Niggemeier', NULL, 'Male', 23, 782, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(139, 'Jordan', NULL, 'Hinckley', NULL, 'Male', 27, 797, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(140, 'Sasha', NULL, 'Pinegar', NULL, 'Male', 27, 785, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(144, 'David', NULL, 'Cullan', NULL, 'Male', 1, 791, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(145, 'Douglas', NULL, 'Mitchell', NULL, 'Male', 21, 777, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(146, 'Dylan', NULL, 'Smith', NULL, 'Male', 136, 792, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(147, 'Eddie', NULL, 'Slezak', NULL, 'Male', 1, 793, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(148, 'Elizabeth', NULL, 'Atkinson', NULL, 'Female', 8, 809, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(149, 'Eric', NULL, 'Holland', NULL, 'Male', 1, 794, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(150, 'Jack', NULL, 'Stewart', NULL, 'Male', 27, 796, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(151, 'James', NULL, 'Auger', NULL, 'Male', 1, 349, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(153, 'John', NULL, 'Sullins', NULL, 'Male', 157, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(155, 'Kari', NULL, 'Baker', NULL, 'Female', 1, 799, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(156, 'Luke', NULL, 'LaFontaine', NULL, 'Male', 1, 781, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(158, 'Patrick', NULL, 'Dean', NULL, 'Male', 1, 783, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(159, 'Patrick', NULL, 'Tarzi', NULL, 'Male', 1, 802, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(160, 'Robin', NULL, 'Price', NULL, 'Male', 7, 423, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(161, 'John', NULL, 'Knoch', NULL, 'Male', 14, 778, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(163, 'Gene', NULL, 'Tausk', NULL, 'Male', 1, 1279, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(164, 'Jonathan', NULL, 'Mayshar', NULL, 'Male', 7, 646, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(165, 'Julian', NULL, 'Schuetze', NULL, 'Male', 3, 749, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(166, 'Gregory', NULL, 'Temorcioglu', NULL, 'Male', 4, 736, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(167, 'Corrigan', NULL, 'Cassidy', NULL, 'Male', 3, 1395, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(168, 'Frank', NULL, 'Curwood', NULL, 'Male', 3, 737, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(169, 'Greg', NULL, 'Elworthy', NULL, 'Male', 4, 1425, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(170, 'Ksenia', NULL, 'Kozhevnikova', NULL, 'Male', 3, 743, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(171, 'Par', NULL, 'Parmar', NULL, 'Male', 3, 1397, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(172, 'Chris', NULL, 'Bruce', NULL, 'Male', 4, 1398, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(173, 'Anthony', NULL, 'Buonomo', NULL, 'Male', 43, 477, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(174, 'Carmon', NULL, 'Heye', NULL, 'Male', 3, 1400, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(175, 'Dashiell', NULL, 'Harrison', NULL, 'Male', 2, 1415, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(176, 'Morgan', NULL, 'Blackmore', NULL, 'Male', 102, 1416, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(177, 'Morgan', NULL, 'Garrett', NULL, 'Male', 44, 1401, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(178, 'Elliot', NULL, 'Robinson', NULL, 'Male', 45, 1417, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(179, 'Samuel', NULL, 'Zavaletta', NULL, 'Male', 42, 1418, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(181, 'Mark', NULL, 'Smead', NULL, 'Male', 2, 1402, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(182, 'Shawn', NULL, 'Holt', NULL, 'Male', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(183, 'Dave', NULL, 'Gill', NULL, 'Male', 3, 1403, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(184, 'Tryon', NULL, 'Thompson', NULL, 'Male', 45, 1419, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(185, 'Cory', NULL, 'Conner', NULL, 'Male', 42, 1404, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(186, 'Siavash', NULL, 'Rezvani', NULL, 'Male', 42, 1405, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(188, 'Preston', NULL, 'Weller', NULL, 'Male', 102, 1420, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(189, 'Stewart', NULL, 'Keenan', NULL, 'Male', 4, 1426, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(190, 'Steven', NULL, 'Will', NULL, 'Male', 42, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(191, 'Nicholas', NULL, 'Chase', NULL, 'Male', 69, 1423, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(192, 'Shawn', NULL, 'Fackler', NULL, 'Male', 69, 1421, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(193, 'Zachary', NULL, 'Amsden', NULL, 'Male', 45, 730, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(194, 'Callie', NULL, 'Jones', NULL, 'Male', 46, 1407, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(195, 'Lucas', NULL, 'Servera', NULL, 'Male', 45, 764, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(196, 'Logan', NULL, 'Martens', NULL, 'Male', 3, 1408, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(197, 'Adrian', NULL, 'Hrytzak', NULL, 'Male', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(198, 'Brian', NULL, 'Antonick', NULL, 'Male', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(199, 'Kevin', NULL, 'Monkhouse', NULL, 'Male', 3, 735, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(200, 'Miles', NULL, 'Ripley', NULL, 'Male', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(202, 'Simon', NULL, 'MacGillvray', NULL, 'Male', 4, 755, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(203, 'Kit', NULL, 'Smith', NULL, 'Male', 1, 258, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(204, 'Ben', NULL, 'Hawkins', NULL, 'Male', 49, 286, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(205, 'Katrina', NULL, 'Rempel', NULL, 'Male', 3, 750, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(206, 'Robert', NULL, 'Balchunas', NULL, 'Male', 3, 733, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(207, 'Bart', NULL, 'Konings', NULL, 'Male', 4, 753, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(208, 'Seth', NULL, 'Fleming-Alho', NULL, 'Male', 4, 747, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(209, 'Nik', NULL, 'Lamont', NULL, 'Male', 4, 769, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(210, 'Brent', NULL, 'Lambell', NULL, 'Male', 48, 463, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(211, 'Douglas', NULL, 'Mayovsky', NULL, 'Male', 46, 757, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(212, 'Philip', NULL, 'Mueller', NULL, 'Male', 46, 758, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(213, 'Tim', NULL, 'Duefrane', NULL, 'Male', 102, 739, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(214, 'Amanda', NULL, 'Trail', NULL, 'Male', 50, 131, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(215, 'Andrew', NULL, 'Mendez', NULL, 'Male', 50, 765, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(216, 'Morgan', NULL, 'Ferry', NULL, 'Male', 7, 759, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(217, 'Albert', NULL, 'Smalls', NULL, 'Male', 39, 761, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(218, 'Erik', NULL, 'Von Essen', NULL, 'Male', 39, 766, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(219, 'Haley', NULL, 'Horton-Loup', NULL, 'Male', 39, 762, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(220, 'Jordan', NULL, 'Thompson', NULL, 'Male', 39, 488, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(221, 'Michael', NULL, 'Edelson', NULL, 'Male', 19, 403, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(222, 'Matthew', NULL, 'Fiebig', NULL, 'Male', 51, 767, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(223, 'Paul', NULL, 'Miller', NULL, 'Male', 42, 771, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(224, 'Ben', NULL, 'Strickling', NULL, 'Male', 52, 290, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(226, 'Hugh', NULL, 'Owens', NULL, 'Male', 42, 1409, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(227, 'David', NULL, 'Attwell', NULL, 'Male', 3, 1410, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(228, 'Jason', NULL, 'Brown', NULL, 'Male', 42, 1424, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(229, 'Louis', NULL, 'Gaty', NULL, 'Male', 42, 1411, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(230, 'Sam', NULL, 'Gaty', NULL, 'Male', 42, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(231, 'Andrew', NULL, 'Campell', NULL, 'Male', 53, 1412, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(232, 'Eric', NULL, 'Artzt', NULL, 'Male', 39, 729, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(233, 'Kaz', NULL, 'Hale', NULL, 'Male', 42, 1413, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(234, 'Monica', NULL, 'Garcia', NULL, 'Male', 42, 1414, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(235, 'Devin', NULL, 'McCarthy', NULL, 'Male', 38, 1973, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(236, 'Daniel', NULL, 'Zwart', NULL, 'Male', 54, 324, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(237, 'Robert', NULL, 'Childs', NULL, 'Male', 55, 969, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(238, 'Alex', NULL, 'Jackson', NULL, 'Male', 14, 1974, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(239, 'Joseph', NULL, 'Campbell', NULL, 'Male', 15, 833, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(240, 'Wesley', NULL, 'Koswara', NULL, 'Male', 15, 1967, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(241, 'Michael', NULL, 'Metz', NULL, 'Male', 136, 1966, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(242, 'Michael', NULL, 'Brockelhurst', NULL, 'Male', 15, 857, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(243, 'Joshua', NULL, 'Richau', NULL, 'Male', 1, 1968, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(244, 'Ted', NULL, 'Elsner', NULL, 'Male', 57, 2143, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(245, 'Nolan', NULL, 'Duino', NULL, 'Male', 7, 1975, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(246, 'Norman', NULL, 'Lao', NULL, 'Male', 7, 1969, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(247, 'Richard', NULL, 'Vang', NULL, 'Male', 19, 890, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(248, 'Caleb', NULL, 'Hallgren', NULL, 'Male', 24, 1989, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(249, 'Michael', NULL, 'Huang', NULL, 'Male', 25, 858, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(253, 'Tony', NULL, 'Nava', NULL, 'Male', 38, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(254, 'Alexander', NULL, 'Flores', NULL, 'Male', 9, 2068, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(255, 'Axel', NULL, 'Pettersson', NULL, 'Male', 59, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(257, 'Dustin', NULL, 'Thelen', NULL, 'Male', 55, 1977, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(258, 'Ben', NULL, 'Ablin', NULL, 'Male', 12, 1978, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(259, 'Jon', NULL, 'Breshears', NULL, 'Male', 15, 831, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(260, 'Justin', NULL, 'Weeks', NULL, 'Male', 34, 1979, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(261, 'Darien', NULL, 'Miles', NULL, 'Male', 7, 1980, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(262, 'Will', NULL, 'Chang', NULL, 'Male', 7, 825, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(263, 'Angel', NULL, 'Uribe', NULL, 'Male', 7, 1981, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(264, 'Robert', NULL, 'Morgan', NULL, 'Male', 7, 1982, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(265, 'Kristofer', NULL, 'Kody', NULL, 'Male', 7, 1983, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(266, 'Justin', NULL, 'Beck', NULL, 'Male', 7, 780, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(267, 'Monica', NULL, 'Lissette', NULL, 'Male', 42, 1984, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(268, 'Ryan', NULL, 'Shapiro', NULL, 'Male', 24, 874, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(269, 'Jeff', NULL, 'Jacobson', NULL, 'Male', 24, 871, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(270, 'Michael', NULL, 'Schachtner', NULL, 'Male', 24, 1985, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(271, 'Charles', NULL, 'Boling', NULL, 'Male', 12, 847, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(272, 'Jay', NULL, 'Fonacier', NULL, 'Male', 14, 1986, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(273, 'Jonathan', NULL, 'Hervas', NULL, 'Male', 1, 1990, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(274, 'JP', NULL, 'Masters', NULL, 'Male', 14, 835, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(275, 'Daniel', NULL, 'Masters', NULL, 'Male', 14, 817, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(276, 'Paul', NULL, 'Barolet', NULL, 'Male', 38, 3156, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(277, 'Paula', NULL, 'Butler', NULL, 'Male', 38, 3155, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(278, 'Xander', NULL, 'Sobecki', NULL, 'Male', 56, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(279, 'Kate', NULL, 'Jeffreys', NULL, 'Male', 10, 1755, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(280, 'James', NULL, 'White', NULL, 'Male', 14, 1987, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(281, 'John', NULL, 'Zemanek', NULL, 'Male', 14, 5142, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(282, 'Mathew', NULL, 'Meigniot', NULL, 'Male', 14, 822, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(283, 'Peter', NULL, 'Hoff', NULL, 'Male', 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(284, 'Tabitha', NULL, 'Halverson', NULL, 'Female', 7, 3177, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(285, 'Timothy', NULL, 'Leonard', NULL, 'Male', 14, 5143, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(286, 'Nathan', NULL, 'Rowe', NULL, 'Male', 15, 1970, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(287, 'Shane', NULL, 'Becker', NULL, 'Male', 5, 1971, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(288, 'Allison', NULL, 'Weeks', NULL, 'Male', 34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(289, 'Anna', NULL, 'Castiglioni', NULL, 'Male', 60, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(291, 'Matthew', NULL, 'Lawrence', NULL, 'Male', 58, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(292, 'Ashton', NULL, 'Warren', NULL, 'Male', 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(293, 'Brian', NULL, 'Frick', NULL, 'Male', 7, 808, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(294, 'Caleb', NULL, 'Nye', NULL, 'Male', 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(295, 'James', NULL, 'Griffin', NULL, 'Male', 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(296, 'Nathan', NULL, 'Class', NULL, 'Male', 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(297, 'Tyler', NULL, 'Smith', NULL, 'Male', 136, 824, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(298, 'Gary', NULL, 'Chelak', NULL, 'Male', 24, 869, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(299, 'Jesus', NULL, 'Mendez', NULL, 'Male', 14, 1988, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(300, 'Philip', NULL, 'Wheeler', NULL, 'Male', 15, 823, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(301, 'Dylan', NULL, 'Labbie', NULL, 'Male', 14, 1972, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(302, 'Dimitri', NULL, 'Sanborn', NULL, 'Male', 61, 848, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(303, 'David', NULL, 'Coblentz', NULL, 'Male', 62, 867, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(304, 'Dori', NULL, 'Coblentz', NULL, 'Male', 62, 868, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(305, 'Anthony', NULL, 'Robinson', NULL, 'Male', 63, 478, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(306, 'Thomas', NULL, 'Carrillo', NULL, 'Male', 64, 2629, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(307, 'Joseph', NULL, 'Brassey', NULL, 'Male', 46, 731, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(308, 'Hunter', NULL, 'Smith', NULL, 'Male', 65, 829, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(309, 'Sam', NULL, 'Ross', NULL, 'Male', 13, 863, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(310, 'Andrew', NULL, 'Weems', NULL, 'Male', 13, 476, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(311, 'Ben', NULL, 'Floyd', NULL, 'Male', 13, 285, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(312, 'Jayson', NULL, 'Barrons', NULL, 'Male', 101, 359, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(313, 'Colin', NULL, 'Farabee', NULL, 'Male', 14, 876, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(314, 'Hung Kai', NULL, 'Tai', NULL, 'Male', 14, 818, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(315, 'Sarah', NULL, 'Trott', NULL, 'Male', 14, 840, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(316, 'Saul', NULL, 'Wyner', NULL, 'Male', 14, 841, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(317, 'Jonny', NULL, 'Cadman', NULL, 'Male', 7, 832, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(318, 'John', NULL, 'Eiler', NULL, 'Male', 15, 853, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(319, 'Mark', NULL, 'Peterson', NULL, 'Male', 15, 856, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(320, 'Sigmund', NULL, 'Werndorf', NULL, 'Male', 15, 499, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(321, 'Vincent', NULL, 'Stoy', NULL, 'Male', 15, 443, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(322, 'Beth', NULL, 'Hammer', NULL, 'Male', 39, 254, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(323, 'Joe', NULL, 'Ceirante', NULL, 'Male', 19, 467, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(324, 'Rebecca', NULL, 'Glass', NULL, 'Female', 19, 267, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(325, 'Jesse', NULL, 'Eaton', NULL, 'Male', 67, 877, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(326, 'Ian', NULL, 'Rodgers', NULL, 'Male', 5, 851, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(327, 'Jared', NULL, 'Lambert', NULL, 'Male', 5, 870, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(328, 'Jessica', NULL, 'Hubbard', NULL, 'Male', 5, 830, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(329, 'Kyle', NULL, 'Gawn', NULL, 'Male', 5, 855, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(330, 'Reginald', NULL, 'Braun', NULL, 'Male', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(331, 'Kevin', NULL, 'Murakoshi', NULL, 'Male', 30, 837, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(334, 'Aaron', NULL, 'Shober', NULL, 'Male', 23, 662, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(335, 'Ayhana', NULL, 'Clark', NULL, 'Male', 24, 866, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(336, 'Patrick', NULL, 'Murray', NULL, 'Male', 24, 872, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(337, 'Richard', NULL, 'Aldrich', NULL, 'Male', 24, 873, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(338, 'Vicktor', NULL, 'Hung', NULL, 'Male', 24, 875, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(339, 'Jan', NULL, 'Deneke', NULL, 'Male', 25, 356, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(340, 'Paul', NULL, 'Abrams', NULL, 'Male', 25, 859, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(341, 'Rudraigh', NULL, 'Quattrin', NULL, 'Male', 25, 862, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(342, 'Sierra', NULL, 'Cirimelli-Low', NULL, 'Male', 25, 878, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(343, 'Dylan', NULL, 'Cheasty', NULL, 'Male', 2, 828, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(344, 'Eric', NULL, 'Smith', NULL, 'Male', 2, 850, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(345, 'Andrew', NULL, 'Hall', NULL, 'Male', 15, 845, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(347, 'Paul', NULL, 'Suda', NULL, 'Male', 1, 838, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(349, 'Aaron', NULL, 'Harmon', NULL, 'Male', 1, 826, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(350, 'Kevin', NULL, 'Moran', NULL, 'Male', 38, 836, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(351, 'Matthew', NULL, 'Cruit-Kitts', NULL, 'Male', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(352, 'Paul', NULL, 'Mysliwiec', NULL, 'Male', 8, 3157, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(353, 'Tyler', NULL, 'Siska', NULL, 'Male', 8, 3091, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(354, 'Matther', NULL, 'Clyker', NULL, 'Male', 71, 3145, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(355, 'Nero', NULL, 'Lynx', NULL, 'Male', 70, 3170, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(356, 'Aindreas', NULL, 'Dounyng', NULL, 'Male', 71, 1585, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(357, 'Isaac', NULL, 'Gary', NULL, 'Male', 101, 1590, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(358, 'Jeremy', NULL, 'Hansen', NULL, 'Male', 71, 3146, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(359, 'Jynell', NULL, 'Veverka', NULL, 'Male', 71, 2318, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(360, 'Philip', NULL, 'Kuberski', NULL, 'Male', 71, 3158, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(361, 'Zachary', NULL, 'Austin', NULL, 'Male', 71, 1598, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(363, 'Michael', NULL, 'Konrad', NULL, 'Male', 28, 3148, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(364, 'John', NULL, 'Dowdle', NULL, 'Male', 72, 2770, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(365, 'Anthony', NULL, 'Mayoh', NULL, 'Male', 3, 3159, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(366, 'Benjamin', NULL, 'Smith', NULL, 'Male', 73, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(367, 'Michael', NULL, 'Salvia', NULL, 'Male', 70, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(368, 'Howard', NULL, 'Nenno', NULL, 'Male', 10, 3179, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(379, 'Oli', NULL, 'Balasa', NULL, 'Male', 19, 408, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(380, 'Peter', NULL, 'Haas', NULL, 'Male', 19, 1954, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(381, 'Liliana', NULL, 'Klein', NULL, 'Male', 19, 1654, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(382, 'Jay', NULL, 'Tsulis', NULL, 'Male', 19, 693, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(383, 'Andre', NULL, 'Troche', NULL, 'Male', 1, 5340, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(384, 'Meghan', NULL, 'O''Connell', NULL, 'Female', 19, 397, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(385, 'Timothy', NULL, 'Stys', NULL, 'Male', 19, 3022, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(386, 'Leanne', NULL, 'Gonzalez-Singer', NULL, 'Female', 19, 2865, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(387, 'Kenny', NULL, 'Mai', NULL, 'Male', 19, 1958, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(388, 'Justin', NULL, 'Mingo', NULL, 'Male', 19, 2870, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(390, 'Patrick', NULL, 'McDonald', NULL, 'Male', 19, 1957, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(391, 'Marcus', NULL, 'Johnson', NULL, 'Male', 19, 669, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(392, 'Patrick', NULL, 'Paglen', NULL, 'Male', 19, 2871, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(393, 'Javier', NULL, 'Cabrera Ferrero', NULL, 'Male', 3, 5134, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(397, 'Kai', NULL, 'Fast', NULL, 'Male', 4, 3599, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(398, 'Gisele', NULL, 'Plourde', NULL, 'Male', 4, 5744, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(399, 'Torben', NULL, 'Schau', NULL, 'Male', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(400, 'Clay', NULL, 'Swanlund', NULL, 'Male', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(401, 'Oni', NULL, 'Prower', NULL, 'Male', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(404, 'Alfredo', NULL, 'Velez', NULL, 'Male', 69, 3171, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(405, 'Tristan', NULL, 'Fackler', NULL, 'Male', 69, 3172, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(406, 'Robert', NULL, 'Fletcher', NULL, 'Male', 40, 3149, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(410, 'Niklas', NULL, 'Montonen', NULL, 'Male', 3, 5743, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(411, 'Jake', NULL, 'Norwood', NULL, 'Male', 35, 346, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(412, 'Tim', NULL, 'Kaufman', NULL, 'Male', 19, 185, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(413, 'Chris', NULL, 'Hobbs', NULL, 'Male', 131, 311, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(414, 'Travis', NULL, 'Mayott', NULL, 'Male', 47, 440, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(416, 'Eric', NULL, 'White', NULL, 'Male', 75, 1607, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(418, 'James', NULL, 'Clark', NULL, 'Male', 35, 350, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(419, 'Jeremy', NULL, 'Steflik', NULL, 'Male', 76, 361, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(420, 'Dave', NULL, 'Kaufman', NULL, 'Male', 19, 325, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(421, 'Luis', NULL, 'Torres', NULL, 'Male', 19, 386, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(422, 'Kiana', NULL, 'Shurkin', NULL, 'Female', 35, 257, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(423, 'Douglas', NULL, 'Perritt', NULL, 'Male', 72, 327, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(424, 'Tristan', NULL, 'Zukowski', NULL, 'Male', 19, 187, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(425, 'Charles', NULL, 'Murdock', NULL, 'Male', 78, 309, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(426, 'David', NULL, 'Von Bargen', NULL, 'Male', 35, 706, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(427, 'Alexander', NULL, 'Kotarakos', NULL, 'Male', 79, 2025, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(428, 'Steven', NULL, 'Viani', NULL, 'Male', 19, 432, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(429, 'Thomas', NULL, 'Schratwieser', NULL, 'Male', 35, 1946, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(430, 'Scott', NULL, 'Barb', NULL, 'Male', 74, 1664, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(431, 'Jim', NULL, 'Brooks', NULL, 'Male', 74, 365, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(432, 'Benjamin', NULL, 'Jarashow', NULL, 'Male', 47, 287, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(433, 'Tyler', NULL, 'Sullivan', NULL, 'Male', 19, 441, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(434, 'Jacob', NULL, 'Kelly', NULL, 'Male', 80, 691, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(435, 'Nathan', NULL, 'Wallace', NULL, 'Male', 81, 707, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(436, 'Lisa', NULL, 'Losito', NULL, 'Female', 47, 262, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(437, 'Alex', NULL, 'Meloi', NULL, 'Male', 82, 688, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(438, 'Fedor', NULL, 'Syagin', NULL, 'Male', 19, 1951, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(439, 'Kristopher', NULL, 'Micozzi', NULL, 'Male', 83, 1825, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(440, 'Christian', NULL, 'O''Connell', NULL, 'Male', 82, 1962, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(441, 'Joseph', NULL, 'Yeager', NULL, 'Male', 35, 376, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(442, 'Patrick', NULL, 'McCaffrey', NULL, 'Male', 84, 698, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(443, 'Richard', NULL, 'Tomasso', NULL, 'Male', 79, 2031, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(444, 'Andrew', NULL, 'Kilgore', NULL, 'Male', 77, 578, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(445, 'Lauren', NULL, 'Hanson', NULL, 'Female', 19, 260, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(446, 'Paul', NULL, 'Kartage', NULL, 'Male', 35, 411, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(447, 'Robert', NULL, 'Smith', NULL, 'Male', 35, 700, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(448, 'Brandon', NULL, 'Shuler', NULL, 'Male', 74, 2168, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(449, 'Ryan', NULL, 'Rusek', NULL, 'Male', 2, 2170, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(450, 'Ashleigh', NULL, 'Hobbs', NULL, 'Female', 131, 471, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(451, 'Joseph', NULL, 'Michel', NULL, 'Male', 82, 695, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(452, 'Michael', NULL, 'Authement', NULL, 'Male', 35, 2765, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(453, 'Lissa', NULL, 'Harris', NULL, 'Female', 19, 263, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(454, 'David', NULL, 'Kalinowski', NULL, 'Male', 35, 2772, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(455, 'Matthew', NULL, 'Daitsman', NULL, 'Male', 82, 2169, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(456, 'Deena', NULL, 'Sadek', NULL, 'Female', 19, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(457, 'John', NULL, 'Durish', NULL, 'Male', 35, 369, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(458, 'Thomas', NULL, 'Moeller', NULL, 'Male', 19, 2013, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(459, 'Peter', NULL, 'Als Nerving', NULL, 'Male', 86, 60, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(460, 'Samantha', NULL, 'Miller', NULL, 'Female', 35, 427, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(461, 'Nicholas', NULL, 'Allen', NULL, 'Male', 87, 2858, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(462, 'James', NULL, 'Allred', NULL, 'Male', 75, 2859, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(463, 'Fanni', NULL, 'Baranyi', NULL, 'Female', 86, 2860, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(464, 'Daniel', NULL, 'Barreto', NULL, 'Male', 19, 2861, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(465, 'Donald', NULL, 'Black', NULL, 'Male', 77, 932, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(466, 'Joseph', NULL, 'Conlon', NULL, 'Male', 81, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(467, 'Sherrie', NULL, 'Dion', NULL, 'Female', 82, 2176, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(468, 'Nicolas', NULL, 'Engst Matthews', NULL, 'Male', 19, 2864, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(469, 'Jim', NULL, 'Howard', NULL, 'Male', 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(470, 'Mariana', NULL, 'Lopez Rodriguez', NULL, 'Female', 88, 140, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(471, 'Kaitlyn', NULL, 'Meade', NULL, 'Female', 19, 2866, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(472, 'Jose', NULL, 'Neves', NULL, 'Male', 19, 2867, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(473, 'Stelian', NULL, 'Serban', NULL, 'Male', 19, 2868, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(474, 'Tyler', NULL, 'Jachetta', NULL, 'Male', 35, 2869, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(475, 'Jeremy', NULL, 'Wolf', NULL, 'Male', 19, 2872, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(476, 'Ashanti', NULL, 'Ziths', NULL, 'Male', 19, 2873, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(477, 'Katie', NULL, 'Zold', NULL, 'Female', 89, 2874, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(478, 'Toby', NULL, 'Hall', NULL, 'Male', 19, 437, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(479, 'Nathan', NULL, 'Weston', NULL, 'Male', 77, 911, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(480, 'Till', NULL, 'Schultz', NULL, 'Male', 90, 3150, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(483, 'Valerie', NULL, 'Keys', NULL, 'Female', 4, 3176, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(484, 'Robbey', NULL, 'Hoy', NULL, 'Male', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(486, 'Long', NULL, 'Quang Dang', NULL, 'Male', 3, 3163, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(487, 'Ken', NULL, 'Lee', NULL, 'Male', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(488, 'Ian', NULL, 'Hollier', NULL, 'Male', 40, 3175, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(489, 'Justin', NULL, 'Cragin', NULL, 'Male', 1, 3173, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(490, 'Michael', NULL, 'Graves', NULL, 'Male', 1, 3161, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(491, 'Stephanie', NULL, 'Williams', NULL, 'Female', 1, 3174, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(492, 'Sean', NULL, 'Connolly', NULL, 'Male', 8, 3162, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(493, 'Connor', NULL, 'Chamberlain', NULL, 'Male', 101, 3151, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(494, 'Tim', NULL, 'Meade', NULL, 'Male', 71, 3152, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(495, 'Anthony', NULL, 'Zucca', NULL, 'Male', 10, 3164, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(496, 'Tony', NULL, 'Huynh', NULL, 'Male', 15, 3165, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(497, 'Jeff', NULL, 'Kim', NULL, 'Male', 7, 3153, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(498, 'Kristen', NULL, 'Argyle', NULL, 'Female', 40, 472, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(499, 'Nathan', NULL, 'King', NULL, 'Male', 40, 496, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(500, 'James', NULL, 'Cruzan', NULL, 'Male', 28, 3154, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(501, 'Jacob', NULL, 'Ulmer', NULL, 'Male', 2, 3166, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(502, 'Anthony', NULL, 'Balisacan', NULL, 'Male', 91, 3178, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(503, 'Alexander', NULL, 'Craddock', NULL, 'Male', 92, 3167, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(504, 'Seth', NULL, 'Wuertz', NULL, 'Male', 92, 3168, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(505, 'Arturo', NULL, 'Camargo', NULL, 'Male', 93, 1657, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(506, 'Jennifer', NULL, 'Benitez', NULL, 'Male', 93, 2077, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(507, 'Ivan', NULL, 'Rodriguez', NULL, 'Male', 94, 461, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(508, 'Robert', NULL, 'Koening', NULL, 'Male', 96, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(509, 'Josh', NULL, 'Rogers', NULL, 'Male', 95, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(510, 'Lamont', NULL, 'Glass', NULL, 'Male', 103, 3621, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(511, 'Sean', NULL, 'Shewey', NULL, 'Male', 95, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(512, 'Eli', NULL, 'Hood', NULL, 'Male', 42, 2363, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(513, 'Jesse', NULL, 'Brittle', NULL, 'Male', 42, 3601, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(514, 'Chad', NULL, 'Alderman', NULL, 'Male', 158, 3169, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(515, 'Steve', NULL, 'Wittmann', NULL, 'Male', 3, 684, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(516, 'Samantha', NULL, 'Campbell', NULL, 'Female', 3, 655, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(517, 'Christine', NULL, 'Nombrado', NULL, 'Male', 3, 752, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(518, 'Allen', NULL, 'Mudrovcic', NULL, 'Male', 3, 745, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(519, 'Anton', NULL, 'Stefanac', NULL, 'Male', 4, 746, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(520, 'Sam', NULL, 'Street', NULL, 'Male', 97, 426, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(521, 'Adam', NULL, 'Ritz', NULL, 'Male', 53, 738, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(522, 'Ilkka', NULL, 'Hartikainen', NULL, 'Male', 99, 606, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(523, 'Annina', NULL, 'Roukonen', NULL, 'Female', 99, 108, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(524, 'Keith', NULL, 'Farrell', NULL, 'Male', 49, 744, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(525, 'Matthys', NULL, 'Kool', NULL, 'Male', 31, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(526, 'Robert', NULL, 'Martin', NULL, 'Male', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(527, 'Jimmy', NULL, 'Norris', NULL, 'Male', 1, 740, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(528, 'Brian', NULL, 'Hughes', NULL, 'Male', 1, 742, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(529, 'Rebecca', NULL, 'Boyd', NULL, 'Female', 1, 751, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(530, 'Stew', NULL, 'Feil', NULL, 'Male', 1, 748, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(532, 'Gary', NULL, 'Ledford', NULL, 'Male', 7, 813, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(533, 'Brian', NULL, 'Batronis', NULL, 'Male', 5, 3510, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(534, 'David', NULL, 'Ballard', NULL, 'Male', 5, 3511, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(535, 'Rachel', NULL, 'Livingston', NULL, 'Male', 5, 3512, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(536, 'Nick', NULL, 'Wixom', NULL, 'Male', 5, 3513, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(537, 'Jeffery', NULL, 'Fisher', NULL, 'Male', 28, 3514, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(540, 'Kevin', NULL, 'Davis', NULL, 'Male', 28, 3097, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(541, 'Philip', NULL, 'Athey', NULL, 'Male', 5, 3516, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(543, 'William', NULL, 'Wilder', NULL, 'Male', 28, 3517, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(544, 'Virgil', NULL, 'Mathes', NULL, 'Male', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(546, 'Sylvia', NULL, 'Lee', NULL, 'Male', 14, 3518, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(547, 'Jordan', NULL, 'Archuleta', NULL, 'Male', 28, 3519, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(548, 'Liam', NULL, 'Friedman', NULL, 'Male', 100, 3520, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(549, 'Connor', NULL, 'Lavery', NULL, 'Male', 100, 3521, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(550, 'Sara', NULL, 'Lewis', NULL, 'Female', 5, 3522, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(551, 'Placeholder', NULL, '#4', NULL, 'Male', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(552, 'Placeholder', NULL, '#3', NULL, 'Male', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(553, 'Placeholder', NULL, '#2', NULL, 'Male', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(554, 'Placeholder', NULL, '#1', NULL, 'Male', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(556, 'Alastair', NULL, 'Sew Hoy', NULL, 'Male', 3, 3619, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(557, 'Shawn', NULL, 'Mracek', NULL, 'Male', 4, 3600, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(558, 'Adam', NULL, 'Triplett', NULL, 'Male', 69, 3606, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(559, 'Charles', NULL, 'Moreland', NULL, 'Male', 69, 3611, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(560, 'Jacob', NULL, 'Flaherty', NULL, 'Male', 42, 3602, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(561, 'Kristin', NULL, 'Brown', NULL, 'Female', 42, 3609, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(562, 'Sam', NULL, 'Lary', NULL, 'Male', 42, 3620, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(563, 'Matthew', NULL, 'Wright', NULL, 'Male', 42, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(564, 'Wayne', NULL, 'Heinz', NULL, 'Male', 50, 965, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(565, 'Caleb', NULL, 'Switzer', NULL, 'Male', 45, 2008, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(566, 'Anthony', NULL, 'Zavin', NULL, 'Male', 45, 2369, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(567, 'Joseph', NULL, 'Colistro', NULL, 'Male', 45, 2001, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(568, 'Chad', NULL, 'Herbert', NULL, 'Male', 53, 5742, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(569, 'Cole', NULL, 'Cyre', NULL, 'Male', 53, 5751, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(570, 'Joseph', NULL, 'Marsden', NULL, 'Male', 46, 2364, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(571, 'Mitchell', NULL, 'Allen', NULL, 'Male', 39, 2009, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(572, 'Zachary', NULL, 'Morand', NULL, 'Male', 102, 3607, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(573, 'Doric', NULL, 'Olson', NULL, 'Male', 102, 3612, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(574, 'Jason', NULL, 'Prendergast', NULL, 'Male', 55, 3613, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(575, 'Matt', NULL, 'Cecil', NULL, 'Male', 55, 3614, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(576, 'Jeremy', NULL, 'Hennig', NULL, 'Male', 42, 3608, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(577, 'Sam', NULL, 'Yaskovic', NULL, 'Male', 55, 3615, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `systemRoster` (`systemRosterID`, `firstName`, `middleName`, `lastName`, `nickname`, `gender`, `schoolID`, `HemaRatingsID`, `birthdate`, `rosterCountry`, `rosterProvince`, `rosterCity`, `eMail`, `publicNotes`, `privateNotes`) VALUES
(578, 'Vaughn', NULL, 'Bechtol', NULL, 'Male', 55, 3616, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(579, 'John', NULL, 'Slaughter', NULL, 'Male', 55, 3617, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(580, 'James', NULL, 'Wolfe', NULL, 'Male', 104, 2362, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(581, 'Miranda', NULL, 'Freeman', NULL, 'Female', 42, 3610, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(582, 'Pavel', NULL, 'Donchenko', NULL, 'Male', 42, 3603, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(583, 'Dwight', NULL, 'Craig', NULL, 'Male', 42, 3604, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(584, 'Klaus', NULL, 'McGlinchey', NULL, 'Male', 46, 3605, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(585, 'Luis Eduardo', NULL, 'Preciado Gomez', NULL, 'Male', 11, 2078, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(586, 'Anyinzan', NULL, 'Cahuich', NULL, 'Male', 11, 2088, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(587, 'Gaute', NULL, 'Raigorodsky', NULL, 'Male', 109, 2087, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(588, 'Miguel Esteban', NULL, 'Kadwrytte Dossetti', NULL, 'Male', 109, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(589, 'Carlos Arturo', NULL, 'Tello Rebolledo', NULL, 'Male', 109, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(590, 'Manuel Guillermo', NULL, 'Gonzalez Fernandez', NULL, 'Male', 109, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(591, 'Santiago', NULL, 'Pedrayes Gonzalez', NULL, 'Male', 109, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(592, 'Maria Andrea', NULL, 'Garza Vela', NULL, 'Male', 109, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(593, 'Jose Raimundo', NULL, 'Jimenez Lopez', NULL, 'Male', 109, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(594, 'Mario Eduardo', NULL, 'GarcÃ­a Torres', NULL, 'Male', 11, 5384, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(595, 'Eduardo', NULL, 'san german nuÃ±ez', NULL, 'Male', 105, 5573, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(596, 'HÃ©ctor Arturo', NULL, 'Torres Torres', NULL, 'Male', 16, 2080, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(597, 'Pablo', NULL, 'Barrera', NULL, 'Male', 16, 2079, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(598, 'Carlos', NULL, 'Chavez', NULL, 'Male', 16, 2072, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(600, 'Luis Francisco', NULL, 'RodrÃ­guez Blake', NULL, 'Male', 93, 494, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(601, 'Rodrigo ', NULL, 'Cueto', NULL, 'Male', 93, 2076, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(602, 'JosÃ© Fernando', NULL, 'PÃ©rez GonzÃ¡lez', NULL, 'Male', 107, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(603, 'Luis Felipe', NULL, 'Del Castillo Toro', NULL, 'Male', 107, 2082, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(604, 'Daniel Esteban', NULL, 'Ochoa solis', NULL, 'Male', 108, 5393, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(605, 'José Luis', NULL, 'Zamarripa Soltero', NULL, 'Male', 108, 2084, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(606, 'Mario', NULL, 'Castro', NULL, 'Male', 106, 5394, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(607, 'Omar JosÃ©', NULL, 'HernÃ¡ndez AlcalÃ¡', NULL, 'Male', 106, 2075, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(608, 'Alan Daniel', NULL, 'Varela Markakis', NULL, 'Male', 106, 5389, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(609, 'JosÃ© AndrÃ©s', NULL, 'Guerrero SÃ¡nchez', NULL, 'Male', 106, 5388, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(610, 'Abraham', NULL, 'GarcÃ­a', NULL, 'Male', 11, 2069, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(611, 'Omar', NULL, 'Rodriguez', NULL, 'Male', 11, 574, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(612, 'Eduardo', NULL, 'de la Rosa Erosa', NULL, 'Male', 110, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(613, 'RubÃ©n Tadeo', NULL, 'Aguilar Aguilar', NULL, 'Male', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(614, 'Jorge Manuel', NULL, 'Herrera Tovar', NULL, 'Male', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(615, 'Guillermo', NULL, 'HernÃ¡ndez', NULL, 'Male', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(616, 'Raquel', NULL, 'Mancera SÃ¡nchez', NULL, 'Male', 111, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(617, 'Manuel', NULL, 'ESquivel Alva', NULL, 'Male', 111, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(618, 'Luis Ignacio', NULL, 'Vazquez', NULL, 'Male', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(619, 'Marisol', NULL, 'Vazquez', NULL, 'Male', 16, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(620, 'Raquel Alma', NULL, 'Melendez', NULL, 'Male', 16, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(621, 'Francisco', NULL, 'HernÃ¡ndez Guarneros', NULL, 'Male', 93, 5385, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(622, 'Roberto Perez', NULL, 'Verdia De la Torre', NULL, 'Male', 108, 5387, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(623, 'Angel', NULL, 'Giron Morales', NULL, 'Male', 93, 2065, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(624, 'Eetu', NULL, 'RÃ¶pelinen', NULL, NULL, 112, 102, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(625, 'Mikko', NULL, 'Lehto', NULL, NULL, 112, 1564, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(626, 'Peik', NULL, 'BackstrÃ¶m', NULL, NULL, 112, 105, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(627, 'Miro', NULL, 'Lahtela', NULL, NULL, 112, 1060, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(628, 'Christoffer', NULL, 'Warelius', NULL, NULL, 112, 192, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(629, 'Tapio', NULL, 'Pellinen', NULL, NULL, 112, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(630, 'Markus', NULL, 'Koivisto', NULL, NULL, 112, 42, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(631, 'Mats', NULL, 'BergstrÃ¶m', NULL, NULL, 112, 601, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(632, 'Bart', NULL, 'Jongsma', NULL, NULL, 31, 1363, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(633, 'Stefan', NULL, 'Brunner', NULL, NULL, 31, 1554, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(634, 'Tim', NULL, 'Beerens', NULL, NULL, 31, 3206, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(635, 'Henric', NULL, 'Jansen', NULL, NULL, 31, 1573, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(636, 'Alexey', NULL, 'Borisov', NULL, NULL, 113, 1125, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(637, 'Andrey', NULL, 'Muzurin', NULL, NULL, 113, 190, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(638, 'Elena', NULL, 'Muzurina', NULL, NULL, 113, 135, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(639, 'Alexey', NULL, 'Perkov', NULL, NULL, 113, 1132, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(640, 'Kirill', NULL, 'Glushchenko', NULL, NULL, 113, 2918, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(641, 'Majken', NULL, 'Roelfszema', NULL, NULL, 114, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(642, 'HÃ¥vard', NULL, 'Eidheim', NULL, NULL, 125, 244, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(643, 'Sigrid', NULL, 'Hogendorp', NULL, NULL, 116, 1374, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(644, 'Sergey', NULL, 'Kultaev', NULL, NULL, 117, 1865, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(645, 'Vadim', NULL, 'Komissarov', NULL, NULL, 117, 1842, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(646, 'Olga', NULL, 'Odintsova', NULL, NULL, 117, 1897, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(647, 'Sedlova', NULL, 'Tatyana', NULL, NULL, 117, 5107, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(648, 'Joakim', NULL, 'Linde', NULL, NULL, 118, 231, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(649, 'Kristofer', NULL, 'Stanson', NULL, NULL, 119, 197, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(650, 'Eetu', NULL, 'SipilÃ¤', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(651, 'Simo-Pekka', NULL, 'LeppÃ¤nen', NULL, NULL, 112, 1583, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(652, 'Thomas', NULL, 'Nyzell', NULL, NULL, 120, 95, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(653, 'Kalle', NULL, 'KylmÃ¤nen', NULL, NULL, 112, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(654, 'Mark', NULL, 'Wilkie', NULL, NULL, 122, 171, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(655, 'Sebastian', NULL, 'Broman', NULL, NULL, 123, 5020, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(656, 'Jonas', NULL, 'Eriksson', NULL, NULL, 112, 234, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(657, 'Tatu-Matti', NULL, 'Pekkarinen', NULL, NULL, 124, 2260, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(658, 'Oskar', NULL, 'Spjuth', NULL, NULL, 120, 3230, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(659, 'James', NULL, 'MacGilp', NULL, NULL, 122, 1195, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(660, 'Jussi', NULL, 'HytÃ¶nen', NULL, NULL, 112, 1067, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(661, 'Jouni', NULL, 'Jokelainen', NULL, NULL, 126, 129, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(662, 'Mattias', NULL, 'BrÃ¤nnstrÃ¶m', NULL, NULL, 127, 1574, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(664, 'Ian', NULL, 'Westfall', NULL, NULL, 35, 5058, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(665, 'Brent', NULL, 'Wang', NULL, NULL, 35, 2777, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(666, 'Henry', NULL, 'Kenyon', NULL, NULL, 35, 340, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(667, 'Kyle', NULL, 'Crandall', NULL, NULL, 35, 5063, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(668, 'Thomas', NULL, 'Pachura', NULL, NULL, 35, 5152, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(669, 'Charles', NULL, 'Lin', NULL, NULL, 35, 308, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(670, 'Alexander', NULL, 'Bowe', NULL, NULL, 35, 2768, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(671, 'Justin', NULL, 'Garey', NULL, NULL, 35, 5151, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(672, 'Joseph', NULL, 'Lilly', NULL, NULL, 35, 2163, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(673, 'Chris', NULL, 'Choban', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(674, 'Trent', NULL, 'McCartney', NULL, NULL, 35, 5066, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(675, 'Philip', NULL, 'Becnel', NULL, NULL, 35, 2767, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(677, 'Heather', NULL, 'O''Sullivan', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(679, 'Michael', NULL, 'Croke', NULL, NULL, 128, 399, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(682, 'Sarah', NULL, 'Heatwole', NULL, NULL, 128, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(683, 'Janet', NULL, 'Ismail', NULL, NULL, 128, 5643, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(684, 'Josh', NULL, 'Raines', NULL, NULL, 128, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(685, 'Zachary', NULL, 'White', NULL, NULL, 128, 5632, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(686, 'Phillip', NULL, 'Burgstahler', NULL, NULL, 128, 2181, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(687, 'Zachary', NULL, 'Showalter', NULL, NULL, 128, 1831, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(688, 'John', NULL, 'Stansfield', NULL, NULL, 128, 2184, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(689, 'Kym', NULL, 'Young', NULL, NULL, 74, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(690, 'William', NULL, 'Buschur', NULL, NULL, 35, 1820, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(691, 'Piotr', NULL, 'Przanowski', NULL, NULL, 132, 1819, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(692, 'Lane', NULL, 'D''Alessandro', NULL, NULL, 84, 3553, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(693, 'Jessica', NULL, 'Rozek', NULL, NULL, 47, 624, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(694, 'Stephen', NULL, 'Cheney', NULL, NULL, 129, 711, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(696, 'Connor', NULL, 'Richardson', NULL, NULL, 129, 3030, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(697, 'Austin', NULL, 'Straub', NULL, NULL, 129, 4789, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(698, 'Ashley', NULL, 'Vogt', NULL, NULL, 129, 3032, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(699, 'Hiram', NULL, 'Troche', NULL, NULL, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(700, 'Connor', NULL, 'Kemp-Cowell', NULL, NULL, 134, 3555, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(701, 'Terry', NULL, 'Sae-Jaew', NULL, NULL, 128, 5146, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(702, 'Tanya', NULL, 'Smith', NULL, NULL, 133, 143, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(703, 'Wesley', NULL, 'Halstead', NULL, NULL, 131, 2386, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(704, 'JW', NULL, 'Pugnetti', NULL, NULL, 131, 2388, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(705, 'Chris', NULL, 'Shelton', NULL, NULL, 131, 2385, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(706, 'Jason', NULL, 'Pajski', NULL, NULL, 52, 358, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(707, 'Megan', NULL, 'Pajski', NULL, NULL, 52, 265, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(708, 'Hanji', NULL, 'Bae', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(709, 'Michael', NULL, 'Murphy', NULL, NULL, 87, 5148, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(711, 'Luke', NULL, 'Hendrix', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(712, 'Elena', NULL, 'Hutchinson', NULL, NULL, 35, 5076, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(713, 'Justyn', NULL, 'Loss', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(714, 'Jens', NULL, 'Nawitzki', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(716, 'Jacob', NULL, 'Schaffer', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(717, 'Marlene', NULL, 'Hurst', NULL, NULL, 35, 5064, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(718, 'Kai', NULL, 'Filippucci', NULL, NULL, 132, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(719, 'Brandi', NULL, 'Florence', NULL, NULL, 130, 251, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(721, 'Anthony', NULL, 'Laurence', NULL, NULL, 84, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(722, 'Joseph', NULL, 'Ingrao', NULL, NULL, 47, 5351, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(723, 'Bryan', NULL, 'Lindung', NULL, NULL, 47, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(724, 'Laurie', NULL, 'Murphy', NULL, NULL, 129, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(726, 'Andrew', NULL, 'Le', NULL, NULL, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(727, 'Charles', NULL, 'Demore', NULL, NULL, 131, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(728, 'Phillip', NULL, 'Evankovich', NULL, NULL, 131, 5633, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(729, 'Evangeline', NULL, 'O''Keefe', NULL, NULL, 131, 335, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(730, 'Allison', NULL, 'Shafer', NULL, NULL, 131, 5355, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(731, 'Steven', NULL, 'Thomas', NULL, NULL, 131, 5365, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(732, 'Harald', NULL, 'Dresler', NULL, NULL, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(734, 'David', NULL, 'Hessler', NULL, NULL, 87, 5149, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(735, 'Derek-Paul', NULL, 'Carll', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(736, 'Theo', NULL, 'Coelho', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(737, 'Rachel', NULL, 'Dorn', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(738, 'Josh', NULL, 'Geen', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(739, 'Noelle', NULL, 'Hepworth', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(740, 'Benjamin', NULL, 'LeDoux', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(741, 'Kenny', NULL, 'Tyler', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(742, 'Ryan', NULL, 'Assi', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(743, 'Morgan', NULL, 'Livesay', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(744, 'Alan', NULL, 'West', NULL, NULL, 160, 5126, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(745, 'Alfred', NULL, 'Tumandao', NULL, NULL, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(746, 'Brian', NULL, 'Stokes', NULL, NULL, 34, 5144, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(747, 'Carl', NULL, 'Peterson', NULL, NULL, 24, 5127, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(748, 'Chandler', NULL, 'Ryder', NULL, NULL, 15, 5128, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(750, 'Clare', NULL, 'Lahey', NULL, NULL, 15, 5130, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(751, 'Elizabeth', NULL, 'Wheeler', NULL, NULL, 15, 5131, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(752, 'Francisco Javier', NULL, 'Gil Sandoval', NULL, NULL, 37, 5132, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(754, 'Haesung', NULL, 'Kim', NULL, NULL, 14, 5133, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(756, 'Jarrett', NULL, 'Ebersberger', NULL, NULL, 13, 3090, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(760, 'Kevyn', NULL, 'Beltran', NULL, NULL, 136, 5135, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(761, 'Lacey', NULL, 'Cupp', NULL, NULL, 7, 5124, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(762, 'Matthew', NULL, 'Cowan', NULL, NULL, 38, 5136, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(763, 'Michael', NULL, 'Badulak', NULL, NULL, 7, 5137, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(764, 'Mick', NULL, 'Yanko', NULL, NULL, 14, 5138, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(766, 'Peter', NULL, 'Irving', NULL, NULL, 14, 5139, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(767, 'Rick', NULL, 'Silver', NULL, NULL, 158, 5140, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(768, 'Ronan', NULL, 'Relosa', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(769, 'Tristan', NULL, 'Barks', NULL, NULL, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(770, 'Victor', NULL, 'Cobian', NULL, NULL, 154, 5141, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(772, 'Stephen', NULL, 'Dougherty', NULL, NULL, 129, 4802, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(773, 'Daniel', NULL, 'Halliday', NULL, NULL, 54, 4791, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(775, 'Kathryn', NULL, 'Johnson', NULL, NULL, 128, 5153, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(776, 'Jamie', NULL, 'Kikilidis', NULL, NULL, 74, 353, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(777, 'Nicholas', NULL, 'Omichinski', NULL, NULL, 54, 2312, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(779, 'Nicholas', NULL, 'Schneider', NULL, NULL, 47, 1826, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(780, 'Daniel', NULL, 'Thomas', NULL, NULL, 87, 5154, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(781, 'Benjamin', NULL, 'Allen', NULL, NULL, 134, 5358, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(782, 'David', NULL, 'Burriss', NULL, NULL, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(783, 'Mike', NULL, 'Burriss', NULL, NULL, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(784, 'Cole', NULL, 'Chlebowski', NULL, NULL, 128, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(785, 'Phillip', NULL, 'Dresler', NULL, NULL, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(787, 'Jake', NULL, 'Lake', NULL, NULL, 128, 5287, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(788, 'Loretta', NULL, 'Murphy', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(789, 'Jonathan', NULL, 'O''Sullivan', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(790, 'Timothy', NULL, 'Patterson', NULL, NULL, 84, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(791, 'Alexander', NULL, 'Phan', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(792, 'Tim', NULL, 'Snyder', NULL, NULL, 131, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(793, 'Stephen', NULL, 'Tyler', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(794, 'Thomas', NULL, 'Wagamon', NULL, NULL, 87, 5155, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(795, 'James', NULL, 'Lewis', NULL, NULL, 87, 5156, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(796, 'Abbye', NULL, 'Palmer', NULL, NULL, 131, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(797, 'Kenny', NULL, 'Milleker', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(798, 'Erin', NULL, 'Phillips', NULL, NULL, 87, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(799, 'Lynne', NULL, 'Hackert', NULL, NULL, 55, 5145, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(803, 'Brendan', NULL, 'Sturges', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(804, 'Arya', NULL, 'Popescu', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(805, 'Shaun', NULL, 'Watson', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(806, 'Eric', NULL, 'Avila', NULL, NULL, 35, 2766, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(807, 'Wesley', NULL, 'Higginbotham', NULL, NULL, 35, 446, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(808, 'Kenneth', NULL, 'Gudel', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(809, 'Patrick', NULL, 'Shannahan', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(810, 'Michael', NULL, 'Nathan', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(811, 'Jeffrey', NULL, 'Sisson', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(812, 'Jeffrey', NULL, 'Steinbach', NULL, NULL, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(816, 'Bear', NULL, 'Kronisch', NULL, NULL, 4, 5731, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(817, 'Benjamin', NULL, 'Grunert', NULL, NULL, 53, 5732, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(818, 'Connor', NULL, 'Wiebe', NULL, NULL, 4, 5746, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(819, 'Damien', NULL, 'Howkins', NULL, NULL, 53, 5733, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(821, 'Emma', NULL, 'Federly', NULL, NULL, 4, 5734, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(822, 'George', NULL, 'Primrose', NULL, NULL, 3, 5749, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(824, 'Heather', NULL, 'Treadgold', NULL, NULL, 53, 5750, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(826, 'Josh', NULL, 'Furrate', NULL, NULL, 137, 489, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(827, 'Nathan', NULL, 'Morrison', NULL, NULL, 3, 5735, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(828, 'Nikolaos', NULL, 'Lalopoulos', NULL, NULL, 138, 5736, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(829, 'Owen', NULL, 'Seidman', NULL, NULL, 53, 5737, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(830, 'Rudi', NULL, 'Lin', NULL, NULL, 3, 5738, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(831, 'Sheena', NULL, 'Haug', NULL, NULL, 138, 5739, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(832, 'Simon', NULL, 'Perry', NULL, NULL, 4, 5740, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(833, 'Tristan', NULL, 'Meager', NULL, NULL, 4, 5741, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(834, 'Vincent', NULL, 'Galano', NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(835, 'Johanus', NULL, 'Haidner', NULL, NULL, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(836, 'Tony', NULL, 'Nava Delgado', NULL, NULL, 139, 2073, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(837, 'Thomas', NULL, 'Del Motte', NULL, NULL, 4, 5747, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(838, 'Nadia', NULL, 'Sadouski', NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(839, 'Madeline', NULL, 'Grant', NULL, NULL, 137, 5518, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(840, 'Steven', NULL, 'Liu', NULL, NULL, 4, 5748, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(841, 'Kelsey', NULL, 'Lore', NULL, NULL, 4, 5745, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(842, 'Dario Alberto', NULL, 'Magnani1', NULL, NULL, 140, 1012, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(843, 'Federico', NULL, 'Marangoni', NULL, NULL, 140, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(844, 'Giancarlo', NULL, 'Ranally', NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(845, 'Megan', NULL, 'Kan', NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(846, 'Griffin', NULL, 'Wolf', NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(847, 'Mark', NULL, 'Kolodii', NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(848, 'Aman', NULL, 'Bazayev', NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(849, 'Stephen', NULL, 'Loch', NULL, NULL, 2, 2374, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(850, 'Mateo', NULL, 'Lopez-Espejo', NULL, NULL, 2, 6003, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(851, 'Nathanial', NULL, 'Stewart', NULL, NULL, 55, 6006, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(852, 'Dean', NULL, 'Gunnison', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(853, 'Conor', NULL, 'Gunnison', NULL, NULL, 2, 6004, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(854, 'Pete', NULL, 'Siefer', NULL, NULL, 2, 6005, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(855, 'Devon', NULL, 'Garden', NULL, NULL, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(856, 'Harald', NULL, 'Parlee', NULL, NULL, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(857, 'Roman', NULL, 'Frolov', NULL, NULL, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(858, 'Stephen', NULL, 'Briggs', NULL, NULL, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(859, 'Joseph', NULL, 'Paches', NULL, NULL, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(860, 'Michael', NULL, 'Miller', NULL, NULL, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(861, 'Clinton', NULL, 'Sime', NULL, NULL, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(862, 'August', NULL, 'Sieben', NULL, NULL, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(863, 'Colton', NULL, 'Strohschein', NULL, NULL, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(864, 'Nick', NULL, 'Morrish', NULL, NULL, 138, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(865, 'Jessica', NULL, 'Kaprowski', NULL, NULL, 141, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(866, 'Connor', NULL, 'Burns', NULL, NULL, 141, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(867, 'Calan', NULL, 'Lovstrom', NULL, NULL, 142, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(868, 'Lexa', NULL, 'Davidson', NULL, NULL, 142, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(869, 'Steven', NULL, 'DallaVicenza', NULL, NULL, 142, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(870, 'Silken', NULL, 'Kleer', NULL, NULL, 142, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(871, 'Mark', NULL, 'Winkelman', NULL, NULL, 142, 635, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(872, 'Thomas', NULL, 'Poda', NULL, NULL, 142, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(873, 'Thora', NULL, 'Jensdottir', NULL, NULL, 142, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(874, 'Greg', NULL, 'Watkins', NULL, NULL, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(875, 'Randy', NULL, 'Bayuk', NULL, NULL, 103, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(876, 'Ferd', NULL, 'Terado', NULL, NULL, 143, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(877, 'Leonard', NULL, 'L''Ã©vÃªque', NULL, NULL, 95, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(878, 'Damon', NULL, 'Stith', NULL, NULL, 145, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(879, 'James', NULL, 'Epperly', NULL, NULL, 103, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(880, 'Steve', NULL, 'Oler', NULL, NULL, 146, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(881, 'Drew', NULL, 'Pace', NULL, NULL, 147, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(882, 'Jordan', NULL, 'Parris', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(883, 'Brett', NULL, 'Wang', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(884, 'Harriet', NULL, 'Coates', NULL, NULL, 38, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(885, 'RaÃºl', NULL, 'Barrera-Barraza', NULL, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(886, 'Jake', NULL, 'Ulmer', NULL, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(887, 'Michael', NULL, 'Roth', NULL, NULL, 169, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(888, 'Carlos', NULL, 'Garcia', NULL, NULL, 56, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(889, 'Thomas', NULL, 'Amoroso', NULL, NULL, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(890, 'Eric', NULL, 'King', NULL, NULL, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(891, 'Julie', NULL, 'Olson', NULL, NULL, 149, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(892, 'Robyn', NULL, 'Alman', NULL, NULL, 149, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(893, 'Sue', NULL, 'Buzzard', NULL, NULL, 149, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(894, 'Kirsten', NULL, 'Meredith', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(895, 'Thomas', NULL, 'Appiah', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(896, 'Timo', NULL, 'Elliott', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(897, 'Joshua', NULL, 'Gardner', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(898, 'Gabriel', NULL, 'Echeverria', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(899, 'Ethan', NULL, 'Scoffield', NULL, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(900, 'Lloyd', NULL, 'McKenzie', NULL, NULL, 101, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(901, 'Gryphon', NULL, 'Nayman', NULL, NULL, 101, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(902, 'Matthew', NULL, 'McQuillan', NULL, NULL, 101, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(903, 'Julia', NULL, 'Deyanova', NULL, NULL, 101, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(904, 'William', NULL, 'Lewis', NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(905, 'Tzu Ping', NULL, 'Fang', NULL, NULL, 32, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(906, 'Ren Jun', NULL, 'Wang', NULL, NULL, 32, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(907, 'Zheng', NULL, 'Liu', NULL, NULL, 32, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(908, 'Tom', NULL, 'Karnuta', NULL, NULL, 151, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(909, 'Joseph', NULL, 'Thibodaux', NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(910, 'Mike', NULL, 'O''Laskey', NULL, NULL, 114, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(911, 'Forrest', NULL, 'Martinez', NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(912, 'John', NULL, 'Dickens', NULL, NULL, 152, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(913, 'Evan', NULL, 'Avery', NULL, NULL, 152, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(914, 'Robert', NULL, 'Roy', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(915, 'Logan', NULL, 'Black', NULL, NULL, 153, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(916, 'Robin', NULL, 'Black', NULL, NULL, 153, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(917, 'Omar', NULL, 'Alonso', NULL, NULL, 154, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(918, 'Alejandro', NULL, 'Jaquez', NULL, NULL, 154, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(919, 'Robert', NULL, 'Brooks', NULL, NULL, 155, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(920, 'Kris', NULL, 'Pruett', NULL, NULL, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(921, 'An', NULL, 'Hoang', NULL, NULL, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(922, 'Hannah', NULL, 'Gammack', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(923, 'John', NULL, 'Stochl', NULL, NULL, 156, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(924, 'Russell', NULL, 'Bradley', NULL, NULL, 156, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(925, 'Don', NULL, 'Synstelien', NULL, NULL, 156, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(926, 'Peder', NULL, 'Fash', NULL, NULL, 158, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(927, 'William', NULL, 'Byrne', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(928, 'Jacob', NULL, 'Carlson', NULL, NULL, 24, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(929, 'Anthony', NULL, 'Julin', NULL, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(930, 'Samuel', NULL, 'Paskewitz', NULL, NULL, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(931, 'Joseph', NULL, 'McKeehan-Davis', NULL, NULL, 92, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(932, 'Steve', NULL, 'Mattsen', NULL, NULL, 159, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(933, 'Bacchus', NULL, 'Davis', NULL, NULL, 161, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(934, 'Alex', NULL, 'Roberson', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(935, 'Jake', NULL, 'Richards', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(936, 'Liam', NULL, 'Richards', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(937, 'Emma', NULL, 'Fowler', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(938, 'Warren', NULL, 'Finch', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(939, 'Dylan', NULL, 'Butler', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(940, 'Ben', NULL, 'Spratt', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(941, 'James', NULL, 'Harris', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(942, 'Carter', NULL, 'Walshe', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(943, 'Paul', NULL, 'Roberson', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(944, 'Jeff', NULL, 'Alvrez', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(945, 'Jonathan', NULL, 'Mclean', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(946, 'Francisco', NULL, 'Sandoval', NULL, NULL, 154, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(947, 'Melissa', NULL, 'Jones', NULL, NULL, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(948, 'Jonathan', NULL, 'Magno', NULL, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(949, 'Kevin', NULL, 'Franklin', NULL, NULL, 163, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(950, 'Warren', NULL, 'Digman', NULL, NULL, 156, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(951, 'Hai', NULL, 'Zhu', NULL, NULL, 164, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(952, 'Norman', NULL, 'Butterfield', NULL, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(953, 'Zac', NULL, 'Makepeace', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(954, 'Ruben', NULL, 'Campbell', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(955, 'Edward', NULL, 'Grant', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(956, 'Jack', NULL, 'Cheong', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(957, 'Jeff', NULL, 'Li', NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(958, 'Aaron', NULL, 'Labertew', NULL, NULL, 165, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(959, 'Travis', NULL, 'Price', NULL, NULL, 182, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(960, 'Tyler', NULL, 'Larson', NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(961, 'Joe', NULL, 'Loder', NULL, NULL, 166, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(962, 'Tyler', NULL, 'Klocke', NULL, NULL, 167, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(963, 'Rick', NULL, 'Long', NULL, NULL, 168, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(964, 'Ian', NULL, 'Pelnar', NULL, NULL, 168, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(965, 'Justin', NULL, 'Pelnar', NULL, NULL, 168, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(966, 'Miguel Angel', NULL, 'Reina Espinosa', NULL, NULL, 168, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(967, 'Tyler', NULL, 'Sigman', NULL, NULL, 168, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(968, 'Don', NULL, 'Doumakes', NULL, NULL, 168, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(969, 'Kenneth', NULL, 'Jones', NULL, NULL, 169, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(970, 'Sam', NULL, 'Brian', NULL, NULL, 182, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(971, 'Ijsbrand', NULL, 'Smid', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(972, 'Bede', NULL, 'Curnow', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(973, 'Felix', NULL, 'Smith', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(974, 'Kip', NULL, 'Freeman', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(975, 'Ash', NULL, 'Marriott', NULL, NULL, 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(976, 'Bryant', NULL, 'Coston', NULL, NULL, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(977, 'Stephen Anthony', NULL, 'Heacock', NULL, NULL, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(978, 'James', NULL, 'Irizarry', NULL, NULL, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(979, 'Erin', NULL, 'Burt', NULL, NULL, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(980, 'Andrew', NULL, 'de la Fuente', NULL, NULL, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(981, 'Noah', NULL, 'Klempner', NULL, NULL, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(982, 'Mark', NULL, 'Holgate', NULL, NULL, 170, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(983, 'Rhys', NULL, 'Kinlough', NULL, NULL, 170, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(984, 'Ben', NULL, 'Hill', NULL, NULL, 171, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(985, 'Chanara', NULL, 'Gettons', NULL, NULL, 171, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(986, 'Christopher', NULL, 'Godwin', NULL, NULL, 171, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(987, 'Jessica', NULL, 'Silvallana', NULL, NULL, 171, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(988, 'Phil', NULL, 'Frost', NULL, NULL, 171, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(989, 'John', NULL, 'Wilson', NULL, NULL, 172, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(990, 'Ryan', NULL, 'Tanzer', NULL, NULL, 173, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(991, 'Samantha', NULL, 'Travis', NULL, NULL, 173, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(992, 'Sean', NULL, 'Reichman', NULL, NULL, 173, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(993, 'Christopher', NULL, 'Denby', NULL, NULL, 174, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(994, 'Christopher', NULL, 'Ray', NULL, NULL, 174, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(995, 'Daniel', NULL, 'Arnold', NULL, NULL, 174, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(996, 'Martin', NULL, 'Soderstrom', NULL, NULL, 174, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(997, 'Rodney', NULL, 'Alchin', NULL, NULL, 174, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(998, 'William', NULL, 'Carew', NULL, NULL, 174, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(999, 'Stuart', NULL, 'Manahan', NULL, NULL, 175, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1000, 'James', NULL, 'Spottiswoode', NULL, NULL, 176, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1001, 'Matthew', NULL, 'Pihodnya', NULL, NULL, 176, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1002, 'Ryan', NULL, 'Kelly', NULL, NULL, 176, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1003, 'Samuel', NULL, 'Lewis', NULL, NULL, 176, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1004, 'Chris', NULL, 'Smith', NULL, NULL, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1005, 'David', NULL, 'Critchley', NULL, NULL, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1006, 'Mark', NULL, 'Arnold', NULL, NULL, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1007, 'Shane', NULL, 'Stapleton', NULL, NULL, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1008, 'Sky', NULL, 'Chen', NULL, NULL, 178, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1009, 'Brett', NULL, 'Kagan', NULL, NULL, 179, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1010, 'Ross', NULL, 'Davies', NULL, NULL, 181, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1011, 'Bob', NULL, 'Dobson', NULL, NULL, 180, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1012, 'Chris', NULL, 'Slee', NULL, NULL, 180, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1013, 'Daniel', NULL, 'Green', NULL, NULL, 180, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1014, 'Kristian', NULL, 'Guivarra', NULL, NULL, 180, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1015, 'Lois', NULL, 'Spangler', NULL, NULL, 173, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1016, 'Alex', NULL, 'Palmer', NULL, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1017, 'Nicholas', NULL, 'Clopton', NULL, NULL, 158, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1018, 'William', NULL, 'Bui', NULL, NULL, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1019, 'Bruce', NULL, 'Rawitch', NULL, NULL, 167, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1020, 'Richard', NULL, 'Goode', NULL, NULL, 167, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1021, 'Daniel', NULL, 'Cadenbach', NULL, NULL, 166, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1022, 'Aaron', NULL, 'Ziska', NULL, NULL, 166, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1023, 'Christopher', NULL, 'Preyer', NULL, NULL, 36, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1024, 'Nick', NULL, 'Pestello', NULL, NULL, 168, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1025, 'William', NULL, 'Fischer', NULL, NULL, 156, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1026, 'Woody', NULL, 'Riehl', NULL, NULL, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1027, 'Matthew', NULL, 'Heavin', NULL, NULL, 169, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1028, 'Mason', NULL, 'Hays', NULL, NULL, 165, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1029, 'Matt', NULL, 'Heavin', NULL, NULL, 169, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1030, 'Matt', NULL, 'Oleson', NULL, NULL, 159, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1031, 'Mitchell', NULL, 'Schinstock', NULL, NULL, 156, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1032, 'Harrison', NULL, 'Cratty', NULL, NULL, 156, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1033, 'Wayne', NULL, 'Brekke', NULL, NULL, 168, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1034, 'Bryan', NULL, 'Howard', NULL, NULL, 182, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1035, 'Levi', NULL, 'Fontaine', NULL, NULL, 183, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1036, 'Chris', NULL, 'Preyer', NULL, NULL, 182, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1037, 'Dustin', NULL, 'Whittaker', NULL, NULL, 184, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1038, 'Benjamin', NULL, 'Boyd', NULL, NULL, 184, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1039, 'William', NULL, 'Venolia', NULL, NULL, 184, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1040, 'Bryce', NULL, 'Lowman', NULL, NULL, 182, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1041, 'Eric', NULL, 'Siley', NULL, NULL, 182, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1042, 'Weston', NULL, 'Price', NULL, NULL, 182, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1043, 'Ian', NULL, 'Stochl', NULL, NULL, 156, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `systemRosterNotDuplicate`
--

CREATE TABLE IF NOT EXISTS `systemRosterNotDuplicate` (
  `tableID` int(10) unsigned NOT NULL,
  `rosterID1` int(10) unsigned NOT NULL,
  `rosterID2` int(10) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemRosterNotDuplicate`
--

INSERT INTO `systemRosterNotDuplicate` (`tableID`, `rosterID1`, `rosterID2`) VALUES
(1, 304, 303);

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
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `systemSchools`
--

INSERT INTO `systemSchools` (`schoolID`, `schoolFullName`, `schoolShortName`, `schoolBranch`, `schoolAbreviation`, `schoolCity`, `schoolProvince`, `schoolCountry`, `schoolAddress`) VALUES
(1, '', '', '', '*', '', '', '', NULL),
(2, 'Unaffiliated', 'Unaffiliated', '', '*', '', '', '', NULL),
(3, 'Blood and Iron Martial Arts', 'Blood and Iron', 'Burnaby', 'BnI', 'Burnaby', 'British Columbia', 'Canada', NULL),
(4, 'Blood and Iron Martial Arts', 'Blood and Iron', 'Victoria', 'BnI', 'Victoria', 'British Columbia', 'Canada', NULL),
(5, 'Phoenix Society of Historical Swordsmanship', 'Phoenix Society', '', 'PSHS', 'Phoenix', 'Arizona', 'USA', NULL),
(6, 'Mordhau Historical Combatives', 'Mordhau', '', 'Mord', 'Phoenix', 'Arizona', 'USA', NULL),
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
(56, 'Chicago Sword Guild', 'Chicago Sword Guild', '', 'CSG', 'Chicago', 'Illinois', 'USA', NULL),
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
(152, 'Lexington HEMA', 'Lexington', '', 'LHEMA', '', '', 'USA', NULL),
(153, 'War Sword Historical Fencing', 'War Sword', '', 'WSHF', '', '', 'USA', NULL),
(154, 'Rittershaft HEMA', 'Rittershaft', '', 'RHEMA', '', '', 'Mexico', NULL),
(155, 'Husaria Academy of Sabre Fencing', 'Husaria Academy of Sabre Fencing', '', 'HASF', '', '', 'UK', NULL),
(156, 'Musketeer Fencing Club', 'Musketeer Fencing Club', '', 'MFC', '', '', 'USA', NULL),
(157, 'En Garde Fencing', 'En Garde Fencing', '', 'EGF', '', '', 'USA', NULL),
(158, 'Sterling Mercenaries', 'Sterling Mercenaries', '', 'SM', '', '', 'USA', NULL),
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
(175, 'The Historical School of fencing', 'The Historical School of fencing', '', 'HSF', 'Melbourne', 'Victoria', 'Australia', NULL),
(176, 'Ironclad Academy Of The Sword', 'Ironclad', '', 'IAOTS', 'Adelaide', 'South Australia', 'Australia', NULL),
(177, 'Prima Spada School of Fence', 'Prima Spada', '', 'PSSF', 'Brisbane', 'Queensland', 'Australia', NULL),
(178, 'Scholar Victoria', 'Scholar Victoria', '', 'SV', 'Melbourne', 'Victoria', 'Australia', NULL),
(179, 'The School of Historical Fencing', 'School of Historical Fencing', '', 'SHF', 'Melbourne', 'Victoria ', 'Australia', NULL),
(180, 'Vanguard Swordsmanship Acadamy', 'Vanguard ', '', 'VSA', 'Brisbane', 'Queensland', 'Australia', NULL),
(181, 'Spada di Bolognese', 'Spada di Bolognese', '', 'SDB', 'Brisbane', 'Queensland', 'Australia', NULL),
(182, 'Baer Swords School of Western Martial Arts', 'Baer Swords', '', 'BSWORDS', 'Kansas City', 'Missouri', 'USA', NULL),
(183, 'Blackhearts Fencing Club', 'Blackhearts', '', 'BFC', 'Witchita', 'Kansas', 'USA', NULL),
(184, 'Springfield Historical Fencing Guild', 'Springfield Fencing', '', 'SHFG', 'Springfield', 'Missouri', 'USA', NULL);

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
(1, 'weapon', 'Longsword', 1, 1, 1, 57, NULL, NULL),
(2, 'weapon', 'Messer', 1, 1, 1, 2, NULL, NULL),
(3, 'weapon', 'Sword and Buckler', 1, 1, 1, 12, NULL, NULL),
(4, 'weapon', 'Rapier', 1, 1, 1, 14, NULL, NULL),
(5, 'weapon', 'Singlestick', 1, 1, 1, 19, NULL, NULL),
(6, 'weapon', 'Dagger', 1, 1, 1, 1, NULL, NULL),
(7, 'weapon', 'Saber', 1, 1, 1, 2, NULL, NULL),
(8, 'weapon', 'Smallsword', 1, 1, 1, 0, NULL, NULL),
(9, 'weapon', 'Grappling', 1, 1, 1, 0, NULL, NULL),
(10, 'weapon', 'Multiple Weapon', 1, 1, 1, 3, NULL, NULL),
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
-- Indexes for table `cuttingStandards`
--
ALTER TABLE `cuttingStandards`
  ADD PRIMARY KEY (`standardID`);

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
-- AUTO_INCREMENT for table `cuttingStandards`
--
ALTER TABLE `cuttingStandards`
  MODIFY `standardID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `systemAttacks`
--
ALTER TABLE `systemAttacks`
  MODIFY `attackID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
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
-- AUTO_INCREMENT for table `systemMatchOrder`
--
ALTER TABLE `systemMatchOrder`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=166;
--
-- AUTO_INCREMENT for table `systemRankings`
--
ALTER TABLE `systemRankings`
  MODIFY `tournamentRankingID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table `systemRoster`
--
ALTER TABLE `systemRoster`
  MODIFY `systemRosterID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1044;
--
-- AUTO_INCREMENT for table `systemRosterNotDuplicate`
--
ALTER TABLE `systemRosterNotDuplicate`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `systemSchools`
--
ALTER TABLE `systemSchools`
  MODIFY `schoolID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=185;
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
