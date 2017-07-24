<?php namespace CalDAVClient\Facade\Utils;
/**
 * Copyright 2017 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

use DateTimeZone;
use DateTime;
use DateInterval;
use Eluceo\iCal\Component\Timezone;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\TimezoneRule;
use Eluceo\iCal\Property\Event\RecurrenceRule;

/**
 * Class ICalTimeZoneBuilder
 * @package CalDAVClient\Facade\Utils
 */
final class ICalTimeZoneBuilder
{

    /**
     * @param DateTimeZone $time_zone
     * @return array
     */
    private static function calculateTimeRangeForTransitions(DateTimeZone $time_zone){

        $now           = new  DateTime('now', $time_zone);
        $year          = $now->format('Y');
        return [new DateTime('1/1/'.$year, $time_zone),  new DateTime('1/1/'.($year + 1), $time_zone)];
    }

    /**
     * @param array $trans
     * @param int $former_offset
     * @return DateTime
     */
    private static function convertStartDateFromUTC2Local(array $trans, $former_offset){
        $dt     = new DateTime($trans['time'], new DateTimeZone('UTC'));
        $hours  = abs($former_offset);
        // START TIME IS ON UTC and should be converted to local using former offset
        if($former_offset >= 0 )
            $dt->add(new DateInterval("PT{$hours}H"));
        else
            $dt->sub(new DateInterval("PT{$hours}H"));

        return $dt;
    }

    /**
     * @param DateTime $dt
     * @return RecurrenceRule
     */
    private static function calculateRecurrenceRule(DateTime $dt){
        $r_rule        = new RecurrenceRule();
        $r_rule->setFreq(RecurrenceRule::FREQ_YEARLY);
        $r_rule->setByMonth(intval($dt->format('m')));
        $r_rule->setByDay
        (
            self::translate2ByDay($dt)
        );
        return $r_rule;
    }

    /**
     * @param $offset
     * @return string
     */
    private static function calculateOffsetFrom($offset){
        return sprintf('%s%02d%02d', $offset >= 0 ? '+' : '-', abs($offset), abs(($offset - floor($offset)) * 60));
    }

    /**
     * @param  $offset
     * @return string
     */
    private static function calculateOffsetTo($offset){
        return sprintf('%s%02d%02d', $offset >= 0 ? '+' : '-', abs($offset), abs(($offset - floor($offset)) * 60));
    }
    /**
     * @param DateTimeZone $time_zone
     * @param string $calendar_prod_id
     * @param bool $with_calendar_envelope
     * @return Calendar|Timezone
     */
    public static function build(DateTimeZone $time_zone, $calendar_prod_id, $with_calendar_envelope = true){

        // get all transitions for one current year and next
        list($start_range, $end_range) = self::calculateTimeRangeForTransitions($time_zone);
        $transitions   = $time_zone->getTransitions($start_range->getTimestamp(), $end_range->getTimestamp());
        $vTimezone     = new Timezone($time_zone->getName());
        $former_offset = null;

        foreach ($transitions as $i => $trans) {
            $current_time_zone_rule = null;

            // skip the first entry...
            if ($i == 0) {
                // ... but remember the offset for the next TZOFFSETFROM value
                $former_offset = $trans['offset'] / 3600;
                continue;
            }

            // daylight saving time definition
            if ($trans['isdst']) {
                $current_time_zone_rule = new TimezoneRule(TimezoneRule::TYPE_DAYLIGHT);;
            }
            // standard time definition
            else {
                $current_time_zone_rule = new TimezoneRule(TimezoneRule::TYPE_STANDARD);;
            }

            if ($current_time_zone_rule) {
                $offset = $trans['offset'] / 3600;
                $dt     = self::convertStartDateFromUTC2Local($trans, $former_offset);
                $current_time_zone_rule->setDtStart($dt);
                $current_time_zone_rule->setTzOffsetFrom(self::calculateOffsetFrom($former_offset));
                $current_time_zone_rule->setTzOffsetTo(self::calculateOffsetTo($offset));

                // add abbreviated timezone name if available
                if (!empty($trans['abbr'])) {
                    $current_time_zone_rule->setTzName($trans['abbr']);
                }

                $former_offset = $offset;
                $current_time_zone_rule->setRecurrenceRule(self::calculateRecurrenceRule($dt));
                $vTimezone->addComponent($current_time_zone_rule);
            }

        }
        if($with_calendar_envelope) {
            $vCalendar = new Calendar(sprintf("'-//%s//EN'", $calendar_prod_id));
            $vCalendar->setTimezone($vTimezone);
            return $vCalendar;
        };
        return $vTimezone;
    }

    /**
     * The BYDAY rule part specifies a COMMA-separated list of days of
     * the week; SU indicates Sunday; MO indicates Monday; TU indicates
     * Tuesday; WE indicates Wednesday; TH indicates Thursday; FR
     * indicates Friday; and SA indicates Saturday.
     * Each BYDAY value can also be preceded by a positive (+n) or
     * negative (-n) integer.
     * @see https://tools.ietf.org/html/rfc5545#section-3.3.10 (BYDAY)
     * @see http://php.net/manual/en/datetime.formats.relative.php
     * @param DateTime $dt)
     * @return string
     */
    private static function translate2ByDay(DateTime $dt){
        $day_name = substr(strtoupper($dt->format('D')), 0,2);
        $ordinals = ['first', 'second', 'third', 'fourth', 'last'];
        $day_nbr  = 0;
        $is_last  = false;

        foreach($ordinals as $idx => $ord){
            $dt_n = self::buildOrdinalDateTime($ord, $dt);
            if($dt_n->format('Y-m-d') == $dt->format('Y-m-d')){
                $day_nbr = $idx  + 1;
                if($ord == 'last'){
                    $is_last = true;
                    $day_nbr  = 1;
                }
                break;
            }
        }

        return sprintf('%s%s%s', $is_last? '-':'', $day_nbr, $day_name);
    }

    /**
     * @param string $ord
     * @param DateTime $dt
     * @return DateTime
     */
    private static function buildOrdinalDateTime($ord, DateTime $dt){
        return new DateTime
        (
            date
            ("Y-m-d",
                strtotime
                (
                    sprintf
                    (
                        self::GetOrdinalDayQuery,
                        $ord,
                        $dt->format('l'),
                        $dt->format('F'),
                        $dt->format('Y')
                    )
                )
            )
        );
    }

    const GetOrdinalDayQuery = '%s %s of %s %s';
}