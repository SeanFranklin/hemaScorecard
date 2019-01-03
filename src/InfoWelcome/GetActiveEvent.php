<?php

namespace Scorecard\InfoWelcome;


use Scorecard\Infrastructure\AbstractPdoRepository;
use PDO;

class GetActiveEvent extends AbstractPdoRepository
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
            WHERE CURRENT_DATE() BETWEEN eventStartDate AND eventEndDate
            GROUP BY eventID
            ORDER BY eventStartDate ASC, eventEndDate ASC";

        $statement = $this->handle->prepare($query);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}