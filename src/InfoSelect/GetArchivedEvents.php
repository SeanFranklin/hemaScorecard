<?php

namespace Scorecard\InfoSelect;


use Scorecard\Infrastructure\AbstractPdoRepository;
use PDO;

class GetArchivedEvents extends AbstractPdoRepository
{
    public function all() {
        $query =
            "SELECT eventYear, 
              eventID, 
              eventName, 
              eventYear, 
              eventStartDate, 
              eventEndDate, 
              eventCountry, 
              eventProvince, 
              eventCity, 
              eventStatus
            FROM systemEvents
            WHERE CURRENT_DATE() > eventEndDate
            ORDER BY eventStartDate DESC, eventEndDate DESC";

        $statement = $this->handle->prepare($query);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_GROUP);
    }
}