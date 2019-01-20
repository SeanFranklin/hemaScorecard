<?php

namespace Scorecard\InfoWelcome;


use Scorecard\Infrastructure\AbstractPdoRepository;
use PDO;

class GetRecentEvents extends AbstractPdoRepository
{
    public function all()
    {
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
            JOIN eventTournaments using (eventID)
            WHERE CURRENT_DATE() > eventEndDate
            GROUP BY eventID
            ORDER BY eventEndDate DESC, eventStartDate DESC
            LIMIT 4";

        $statement = $this->handle->prepare($query);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}