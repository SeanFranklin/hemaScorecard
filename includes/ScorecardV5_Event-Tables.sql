-- phpMyAdmin SQL Dump
-- version 4.4.15.9
-- https://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2018 at 04:32 PM
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `ignoreFighter` tinyint(1) NOT NULL DEFAULT '0',
  `tournamentTableID` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `privateNotes` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `AbsPointsFor` int(11) DEFAULT NULL,
  `AbsPointsAgainst` int(11) DEFAULT NULL,
  `numPenalties` float DEFAULT '0',
  `penaltiesAgainstOpponents` float DEFAULT '0',
  `penaltiesAgainst` float DEFAULT '0',
  `doubleOuts` float DEFAULT '0',
  `ignoreForBracket` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eventTournamentRoster`
--

CREATE TABLE IF NOT EXISTS `eventTournamentRoster` (
  `tableID` int(10) unsigned NOT NULL,
  `tournamentID` int(10) unsigned DEFAULT NULL,
  `rosterID` int(10) unsigned DEFAULT NULL,
  `ignorePoolMatches` tinyint(1) DEFAULT '0',
  `removeFromFinals` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `isPrivate` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
-- Indexes for table `systemEvents`
--
ALTER TABLE `systemEvents`
  ADD PRIMARY KEY (`eventID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cuttingQualifications`
--
ALTER TABLE `cuttingQualifications`
  MODIFY `qualID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventAttacks`
--
ALTER TABLE `eventAttacks`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventAttributes`
--
ALTER TABLE `eventAttributes`
  MODIFY `attributeID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventCuttingStandards`
--
ALTER TABLE `eventCuttingStandards`
  MODIFY `qualID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventDefaults`
--
ALTER TABLE `eventDefaults`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventExchanges`
--
ALTER TABLE `eventExchanges`
  MODIFY `exchangeID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventGroupRoster`
--
ALTER TABLE `eventGroupRoster`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventGroups`
--
ALTER TABLE `eventGroups`
  MODIFY `groupID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventLivestreamMatches`
--
ALTER TABLE `eventLivestreamMatches`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventLivestreams`
--
ALTER TABLE `eventLivestreams`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventMatches`
--
ALTER TABLE `eventMatches`
  MODIFY `matchID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventPlacings`
--
ALTER TABLE `eventPlacings`
  MODIFY `placeID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventRoster`
--
ALTER TABLE `eventRoster`
  MODIFY `rosterID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventStandings`
--
ALTER TABLE `eventStandings`
  MODIFY `standingID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventTournamentRoster`
--
ALTER TABLE `eventTournamentRoster`
  MODIFY `tableID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventTournaments`
--
ALTER TABLE `eventTournaments`
  MODIFY `tournamentID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `systemEvents`
--
ALTER TABLE `systemEvents`
  MODIFY `eventID` int(10) unsigned NOT NULL AUTO_INCREMENT;
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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
