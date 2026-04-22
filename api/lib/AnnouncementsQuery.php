<?php
namespace HemaScorecard\Api\Lib;

class AnnouncementsQuery {

    /**
     * Return current, publicly-visible announcements for an event.
     * Includes global (eventID IS NULL) announcements too.
     * Filters out expired rows and rows with visibility != 'all'.
     */
    public static function listForEvent(int $eventID): array {
        $eventID = (int)$eventID;
        $sql = "SELECT announcementID, eventID, message, displayUntil
                FROM logisticsAnnouncements
                WHERE (eventID = {$eventID} OR eventID IS NULL)
                AND visibility = 'all'
                AND displayUntil >= UNIX_TIMESTAMP(NOW())
                ORDER BY displayUntil DESC, announcementID DESC";
        return mysqlQuery($sql, ASSOC);
    }
}
