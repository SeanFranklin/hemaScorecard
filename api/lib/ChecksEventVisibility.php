<?php
namespace HemaScorecard\Api\Lib;

trait ChecksEventVisibility {

    /**
     * Load the gate struct for $eventID via EventsQuery::findVisibleForGate.
     * If the event is missing or hidden, throw a 404 ApiException.
     */
    protected function findVisibleEventOrThrow(int $eventID): array {
        $gate = EventsQuery::findVisibleForGate($eventID);
        if ($gate === null) {
            throw new ApiException('not_found', 404, "Event {$eventID} not found");
        }
        return $gate;
    }

    /**
     * Check whether a resource governed by $flagKey (one of 'publishRoster',
     * 'publishRules', 'publishSchedule', 'publishMatches') should be visible
     * to API consumers at this event. Archived events override all flags.
     */
    protected function isResourceVisible(array $gate, string $flagKey): bool {
        if (!array_key_exists($flagKey, $gate)) {
            throw new \InvalidArgumentException("Unknown gate flag key: {$flagKey}");
        }
        return $gate['isArchived'] || $gate[$flagKey];
    }
}
