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
/**
 * Class ICalTimeZoneBuilder
 * @package CalDAVClient\Facade\Utils
 */
final class ICalTimeZoneBuilder
{

    /**
     * @param DateTimeZone $time_zone
     * @param string $calendar_prod_id
     * @param bool $with_calendar_envelope
     * @return Calendar|Timezone
     */
    public static function build(DateTimeZone $time_zone, $calendar_prod_id, $with_calendar_envelope = true){

        $now           = new  DateTime('now', $time_zone);
        $year          = $now->format('Y');
        $startOfYear   = new \DateTime('1/1/'.$year, $time_zone);
        $startOfNext   = new \DateTime('1/1/'.($year + 1), $time_zone);
        // get all transitions for one current year and next
        $transitions   = $time_zone->getTransitions($startOfYear->getTimestamp(), $startOfNext->getTimestamp());
        $vTimezone     = new Timezone($time_zone->getName());
        $std           = null;
        $dst           = null;
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
                $dst =  new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_DAYLIGHT);
                $current_time_zone_rule = $dst;
            }
            // standard time definition
            else {
                $std = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_STANDARD);
                $current_time_zone_rule = $std;
            }

            if ($current_time_zone_rule) {
                $dt     = new DateTime($trans['time'], new DateTimeZone('UTC'));
                $offset = $trans['offset'] / 3600;
                // DATETIME S
                $hours  = abs($former_offset);
                if($former_offset >= 0 )
                    $dt->add(new DateInterval("PT{$hours}H"));
                else
                    $dt->sub(new DateInterval("PT{$hours}H"));
                $current_time_zone_rule->setDtStart($dt);
                $current_time_zone_rule->setTzOffsetFrom(sprintf('%s%02d%02d', $former_offset >= 0 ? '+' : '-', abs($former_offset), abs(($former_offset - floor($former_offset)) * 60)));
                $current_time_zone_rule->setTzOffsetTo(sprintf('%s%02d%02d', $offset >= 0 ? '+' : '-', abs($offset), abs(($offset - floor($offset)) * 60)));

                // add abbreviated timezone name if available
                if (!empty($trans['abbr'])) {
                    $current_time_zone_rule->setTzName($trans['abbr']);
                }

                $former_offset = $offset;
                $r_rule = new \Eluceo\iCal\Property\Event\RecurrenceRule();
                $r_rule->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY);
                $r_rule->setByMonth(intval($dt->format('m')));
                $r_rule->setByDay
                (
                    self::translate2ByDay($dt)
                );
                $current_time_zone_rule->setRecurrenceRule($r_rule);
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