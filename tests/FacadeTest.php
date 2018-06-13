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
use CalDAVClient\Facade\CalDavClient;
use CalDAVClient\ICalDavClient;
use CalDAVClient\Facade\Requests\EventRequestVO;
use CalDAVClient\Facade\Requests\MakeCalendarRequestVO;
/**
 * Class FacadeTest
 */
final class FacadeTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ICalDavClient
     */
    private static $client;

    public static function setUpBeforeClass()
    {
       self::$client = new CalDavClient(
            getenv('CALDAV_SERVER_URL'),
            getenv('USER_EMAIL'),
            getenv('USER_PASSWORD')
        );
    }

    public static function tearDownAfterClass()
    {
        // do sth after the last test
    }

    function testIsValidServer(){
        $this->assertTrue(self::$client ->isValidServer());
    }

    function testPrincipal(){

        $res    = self::$client ->getUserPrincipal();
        $url    = $res->getPrincipalUrl();

        $this->assertTrue(!empty($url));
        echo sprintf('principal url is %s', $url).PHP_EOL;
        return $url;
    }

    function testCalendarHomes(){
        $principal_url = $this->testPrincipal();
        $res    = self::$client->getCalendarHome($principal_url);
        $url    = $res->getCalendarHomeSetUrl();

        $this->assertTrue(!empty($url));
        $host = $res->getRealCalDAVHost();
        echo sprintf('calendar home is %s', $url).PHP_EOL;
        echo sprintf('host is %s', $host).PHP_EOL;
        return $url;
    }

    function testGetCalendars(){
        $calendar_home = $this->testCalendarHomes();
        $res   = self::$client->getCalendars($calendar_home);
        $this->assertTrue($res->isSuccessFull());
        $this->assertTrue(count($res->getResponses()) > 0);
        return $res;
    }

    function testGetCalendar(){
        $responses = self::$client->getCalendar($this->getCalendarUrl())->getResponses();

        foreach ($responses as $res) {
            $this->assertTrue($res->isSuccessFull(), "Calendar request not successful");
            $this->assertTrue(!empty($res->getDisplayName()), "Display name not set");
            $this->assertTrue(!empty($res->getSyncToken()), "Sync-token empty");
        }
        return $responses[0];
    }

    function testSyncCalendar(){

        $res    = self::$client->getCalendarSyncInfo(
            getenv('CALDAV_SERVER_URL').'/8244464267/calendars/openstack-summit-sidney-2017/',
            "FT=-@RU=8546e45e-a9f6-4f20-b6a2-7637f4783d8f@S=169");
        $this->assertTrue($res->isSuccessFull());
        $this->assertTrue(!empty($res->getSyncToken()));
    }

    function testCreateCalendar(){

        $res = self::$client->createCalendar(
            getenv('CALDAV_SERVER_URL').'/8244464267/calendars/',
            new MakeCalendarRequestVO(
                'openstack-summit-sidney-2017',
                'OpenStack Sidney Summit Nov 2017',
                'Calendar to hold Summit Events',
                new DateTimeZone('Australia/Sydney')
            )
        );

        $this->assertTrue(!empty($res));
    }

    function testDeleteCalendar(){

        $calendar_url =  'https://p01-caldav.icloud.com:443/8244464267/calendars/openstack-summit-sidney-2017';
        $res  = self::$client->getCalendar($calendar_url);

        $res = self::$client->deleteCalendar
        (
            $calendar_url,
            ""
        );

        $this->assertTrue(!empty($res));
    }

    function testCreateEvent(){
        $res = self::$client->createEvent(
            getenv('CALDAV_SERVER_URL').'/8244464267/calendars/0f9ba5e9072576c6fab990b8f813b4e0/',
            new EventRequestVO(
                'openstack-summit-sidney-2017',
                'test event 4',
            'test event',
                'test event',
                new DateTime('2017-11-01 09:00:00'),
                new DateTime('2017-11-01 10:30:00'),
                new DateTimeZone('Australia/Sydney')
            )
        );

        $this->assertTrue($res->isSuccessFull());
    }

    function testUpdateEvent(){
        $uid = 'ad82cdfe9da1d7b8c840c3acfa65db18';
        //$etag = "C=150@U=8546e45e-a9f6-4f20-b6a2-7637f4783d8f";

        $dto =  new EventRequestVO(
            '0f9ba5e9072576c6fab990b8f813b4e0',
            'test event 4 updated 2!!!!',
            'test event updated' ,
            'test event update',
            new DateTime('2017-11-01 09:00:00'),
            new DateTime('2017-11-01 10:50:00'),
            new DateTimeZone('Australia/Sydney')
        );
        $dto->setUID($uid);
        $res = self::$client->updateEvent(getenv('CALDAV_SERVER_URL').'/8244464267/calendars/0f9ba5e9072576c6fab990b8f813b4e0/',
            $dto
        );

        $this->assertTrue($res->isSuccessFull());
    }

    function testDeleteEvent(){
        $uid  = 'd738bd4a675f4b60cf664b88ce7f0659';

        $res = self::$client->deleteEvent(getenv('CALDAV_SERVER_URL').'/8244464267/calendars/0f9ba5e9072576c6fab990b8f813b4e0/',
            $uid
        );

        $this->assertTrue($res->isSuccessFull());
    }

    function testGetEventByUrl(){

        $event_url = 'https://p01-caldav.icloud.com:443/8244464267/calendars/openstack-summit-sidney-2017/d7a2387264bfa1a619c37a593e94204a.ics';

        $v_card = self::$client->getEventVCardBy($event_url);

        $this->assertTrue(!empty($v_card));
    }

    function testGetEventsByUrl(){
        $event_url  = '/8244464267/calendars/openstack-summit-sidney-2017/0df083912b476631bf677c140ad4740b.ics';

        $res = self::$client->getEventsBy('https://p01-caldav.icloud.com:443/8244464267/calendars/openstack-summit-sidney-2017/',
            [$event_url]);

        $this->assertTrue($res->isSuccessFull());
    }
}