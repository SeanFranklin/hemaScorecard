<?php
namespace HemaScorecard\Api\Lib;

class TimeFormat {

    /**
     * Convert a minutes-since-midnight integer into a "HH:MM" 24-hour string.
     *
     * Clamps negatives to 0. Values >= 1440 are taken modulo 1440 — organizers
     * should use a higher dayNum for past-midnight blocks rather than relying
     * on wraparound (documented in the spec).
     */
    public static function minutesToHhmm(int $minutes): string {
        if ($minutes < 0) {
            $minutes = 0;
        }
        $minutes = $minutes % 1440;
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return sprintf('%02d:%02d', $h, $m);
    }
}
