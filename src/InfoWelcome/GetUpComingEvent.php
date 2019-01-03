<?php

namespace Scorecard\InfoWelcome;


use Scorecard\Infrastructure\AbstractPdoRepository;
use PDO;

class GetUpComingEvent extends AbstractPdoRepository
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
            JOIN eventTournaments using (eventID)
            WHERE eventStartDate > CURRENT_DATE()
            GROUP BY eventID
            ORDER BY eventStartDate ASC, eventEndDate ASC";

        $statement = $this->handle->prepare($query);
        $statement->bindValue(':date_limit', EVENT_UPCOMING_LIMIT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}