<?php

use CalDAVClient\Facade\Responses\GetCalendarResponse;
use PHPUnit\Framework\TestCase;
use Sabre\VObject;

final class VAlarmParsingTest extends TestCase
{
    public function testParsePT0STrigger()
    {
        $ics = file_get_contents(__DIR__ . '/fixtures/events/event-with-pt0s-alarm.ics');
        $vcalendar = VObject\Reader::read($ics);
        $vevent = $vcalendar->VEVENT;

        $alarms = GetCalendarResponse::parseVAlarms($vevent);

        $this->assertCount(1, $alarms);
        $this->assertEquals(0, $alarms[0]['minutesBefore']);
    }

    public function testParseAbsoluteDateTimeTrigger()
    {
        $ics = file_get_contents(__DIR__ . '/fixtures/events/event-with-absolute-alarm.ics');
        $vcalendar = VObject\Reader::read($ics);
        $vevent = $vcalendar->VEVENT;

        $alarms = GetCalendarResponse::parseVAlarms($vevent);

        $this->assertCount(1, $alarms);
        // Event at 14:00, alarm at 13:30 = 30 minutes before
        $this->assertEquals(30, $alarms[0]['minutesBefore']);
    }

    public function testParseMixedUnitDuration()
    {
        $ics = file_get_contents(__DIR__ . '/fixtures/events/event-with-mixed-alarm.ics');
        $vcalendar = VObject\Reader::read($ics);
        $vevent = $vcalendar->VEVENT;

        $alarms = GetCalendarResponse::parseVAlarms($vevent);

        $this->assertCount(1, $alarms);
        // -PT1H30M = 90 minutes
        $this->assertEquals(90, $alarms[0]['minutesBefore']);
    }

    public function testParseAppleDefaultAlarm()
    {
        $ics = file_get_contents(__DIR__ . '/fixtures/events/event-with-default-alarm.ics');
        $vcalendar = VObject\Reader::read($ics);
        $vevent = $vcalendar->VEVENT;

        $alarms = GetCalendarResponse::parseVAlarms($vevent);

        $this->assertCount(1, $alarms);
        $this->assertEquals(15, $alarms[0]['minutesBefore']);
        $this->assertTrue(isset($alarms[0]['isDefault']));
        $this->assertTrue($alarms[0]['isDefault']);
    }

    public function testParseNoAlarms()
    {
        $ics = 'BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:TEST-NO-ALARM
DTSTART:20251113T140000Z
DTEND:20251113T150000Z
SUMMARY:No Alarms
END:VEVENT
END:VCALENDAR';
        $vcalendar = VObject\Reader::read($ics);
        $vevent = $vcalendar->VEVENT;

        $alarms = GetCalendarResponse::parseVAlarms($vevent);

        $this->assertCount(0, $alarms);
    }
}
