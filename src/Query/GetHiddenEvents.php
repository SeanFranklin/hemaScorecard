<?php

namespace Scorecard\Query;


use Scorecard\Infrastructure\AbstractPdoRepository;
use PDO;

class GetHiddenEvents extends AbstractPdoRepository
{
    public function all() {
        $query =
            "SELECT eventID, 
                eventName, 
                eventYear, 
                eventStartDate, 
                eventEndDate, 
                eventCountry, 
                eventProvince, 
                eventCity, 
                eventStatus
            FROM systemEvents
            LEFT JOIN eventTournaments using (eventID)
            WHERE eventTournaments.eventID IS NULL
            ORDER BY eventEndDate DESC, eventStartDate DESC";

        $statement = $this->handle->prepare($query);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}