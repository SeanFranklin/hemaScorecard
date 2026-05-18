-- Migration: Create eventRankings table and populate from existing data
-- This adds per-tournament ranking config, cloned from systemRankings templates.

CREATE TABLE IF NOT EXISTS `eventRankings` (
  `eventRankingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `eventID` int(10) UNSIGNED NOT NULL,
  `tournamentID` int(10) UNSIGNED NOT NULL,
  `systemRankingID` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `formatID` int(10) UNSIGNED NOT NULL,
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
  `displayField5` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`eventRankingID`),
  UNIQUE KEY `uq_tournament` (`tournamentID`),
  KEY `eventID` (`eventID`),
  KEY `systemRankingID` (`systemRankingID`),
  KEY `formatID` (`formatID`),
  CONSTRAINT `eventRankings_ibfk_event` FOREIGN KEY (`eventID`) REFERENCES `systemEvents` (`eventID`),
  CONSTRAINT `eventRankings_ibfk_tournament` FOREIGN KEY (`tournamentID`) REFERENCES `eventTournaments` (`tournamentID`),
  CONSTRAINT `eventRankings_ibfk_format` FOREIGN KEY (`formatID`) REFERENCES `systemFormats` (`formatID`),
  CONSTRAINT `eventRankings_ibfk_source` FOREIGN KEY (`systemRankingID`) REFERENCES `systemRankings` (`tournamentRankingID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Populate from existing tournament/ranking associations
INSERT INTO eventRankings (
  eventID, tournamentID, systemRankingID,
  name, formatID, description, displayFunction, scoringFunction, scoreFormula,
  orderByField1, orderBySort1, orderByField2, orderBySort2,
  orderByField3, orderBySort3, orderByField4, orderBySort4,
  displayTitle1, displayField1, displayTitle2, displayField2,
  displayTitle3, displayField3, displayTitle4, displayField4,
  displayTitle5, displayField5
)
SELECT
  eT.eventID, eT.tournamentID, eT.tournamentRankingID,
  sR.name, sR.formatID, sR.description, sR.displayFunction, sR.scoringFunction, sR.scoreFormula,
  sR.orderByField1, sR.orderBySort1, sR.orderByField2, sR.orderBySort2,
  sR.orderByField3, sR.orderBySort3, sR.orderByField4, sR.orderBySort4,
  sR.displayTitle1, sR.displayField1, sR.displayTitle2, sR.displayField2,
  sR.displayTitle3, sR.displayField3, sR.displayTitle4, sR.displayField4,
  sR.displayTitle5, sR.displayField5
FROM eventTournaments eT
INNER JOIN systemRankings sR ON eT.tournamentRankingID = sR.tournamentRankingID
WHERE eT.tournamentRankingID IS NOT NULL;
