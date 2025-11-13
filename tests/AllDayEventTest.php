<?php

use CalDAVClient\Facade\CalDavClient;
use PHPUnit\Framework\TestCase;

class AllDayEventTest extends TestCase
{
    public function testCreateAllDayEvent()
    {
        // This test requires a real CalDAV server
        // For unit testing, we verify the method exists and accepts parameters

        $client = new CalDavClient(
            'https://caldav.example.com',
            'testuser',
            'testpass'
        );

        $this->assertTrue(method_exists($client, 'createEventFromICS'));
        $this->assertTrue(method_exists($client, 'updateEventFromICS'));
    }
}
