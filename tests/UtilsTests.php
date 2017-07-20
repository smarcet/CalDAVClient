<?php
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
use CalDAVClient\Facade\Utils\ICalTimeZoneBuilder;
/**
 * Class UtilsTests
 */
final class UtilsTests  extends PHPUnit_Framework_TestCase
{
     public function testBuildICalendarBuildChicagoTimeZone(){
         $calendar   = ICalTimeZoneBuilder::build(new DateTimeZone('America/Chicago'), '-//OpenStack//Boston 2017 Summit//EN');
         $string_cal = $calendar->render();
         $this->assertTrue(!empty($string_cal));
         $expected_val = <<<ICAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//OpenStack//Boston 2017 Summit//EN
X-WR-TIMEZONE:America/Chicago
X-PUBLISHED-TTL:P1W
BEGIN:VTIMEZONE
TZID:America/Chicago
X-LIC-LOCATION:America/Chicago
BEGIN:DAYLIGHT
TZNAME:CDT
TZOFFSETFROM:-0600
TZOFFSETTO:-0500
DTSTART:20170312T020000
RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH=3;BYDAY=2SU
END:DAYLIGHT
BEGIN:STANDARD
TZNAME:CST
TZOFFSETFROM:-0500
TZOFFSETTO:-0600
DTSTART:20171105T020000
RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH=11;BYDAY=1SU
END:STANDARD
END:VTIMEZONE
END:VCALENDAR
ICAL;

         $this->assertTrue(self::normalizeString($string_cal) == self::normalizeString($expected_val));
     }


    public function testBuildICalendarBuildSydneyTimeZone(){
        $calendar   = ICalTimeZoneBuilder::build(new DateTimeZone('Australia/Sydney'), '-//OpenStack//Boston 2017 Summit//EN');
        $string_cal = $calendar->render();
        $this->assertTrue(!empty($string_cal));
        $expected_val = <<<ICAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//OpenStack//Boston 2017 Summit//EN
X-WR-TIMEZONE:Australia/Sydney
X-PUBLISHED-TTL:P1W
BEGIN:VTIMEZONE
TZID:Australia/Sydney
X-LIC-LOCATION:Australia/Sydney
BEGIN:DAYLIGHT
TZNAME:AEDT
TZOFFSETFROM:+1000
TZOFFSETTO:+1100
DTSTART:20171001T020000
RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH=10;BYDAY=1SU
END:DAYLIGHT
BEGIN:STANDARD
TZNAME:AEST
TZOFFSETFROM:+1100
TZOFFSETTO:+1000
DTSTART:20170402T030000
RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH=4;BYDAY=1SU
END:STANDARD
END:VTIMEZONE
END:VCALENDAR
ICAL;

        $this->assertTrue(self::normalizeString($string_cal) == self::normalizeString($expected_val));
    }

    /**
     * @param string $str
     * @return string
     */
    private static function normalizeString($str){

        $str = trim($str);
        $str = str_replace("\n", '', $str);
        $str = str_replace("\r", '', $str);
        return $str;
    }
}

