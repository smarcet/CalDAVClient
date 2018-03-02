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

    /**
     * @var string URL of the calendar that was created during the test
     */
    private static $calendar_created_by_phpunit = "";

    /**
     * @var \CalDAVClient\Facade\Responses\EventCreatedResponse
     */
    private static $event_created_by_phpunit    = null;

    private static $calendar_home = null;

    public function setUp() {
        parent::setUp();
    }

    public static function setUpBeforeClass()
    {
       self::$client = new CalDavClient(
            getenv('CALDAV_SERVER_HOST') . getenv('CALDAV_SERVER_PATH'),
            getenv('USER_LOGIN'),
            getenv('USER_PASSWORD'),
            getenv('AUTHTYPE')
        );
    }

    public static function tearDownAfterClass()
    {
        // do sth after the last test
    }

    private function getCalendarUrl() {
        return
            getenv('CALDAV_SERVER_HOST') .
            getenv('CALDAV_SERVER_PATH') .
            getenv('CALDAV_CALENDAR_HOME') .
            getenv('CALDAV_TEST_CALENDAR_PATH');
    }

    function testIsValidServer(){
        $this->assertTrue(self::$client->isValidServer());
    }

    function testPrincipal(){
        $principals = self::$client->getUserPrincipal();
        $responses  = $principals->getResponses();

        foreach ($responses as $res) {
            $url = $res->getPrincipalUrl();

            $this->assertTrue(!empty($url), "Principal URL is empty");
            return $url;
        }
    }

    function testCalendarHomes(){
        $caldav_host = getenv("CALDAV_SERVER_HOST");
        $caldav_path = getenv("CALDAV_SERVER_PATH");

        $principal_url = $this->testPrincipal();
        $res    = self::$client->getCalendarHome($caldav_host . $principal_url);
        $url    = $res->getCalendarHomeSetUrl();

        $this->assertTrue(!empty($url), "Calendar home URL is empty");
        // $host = $res->getRealCalDAVHost();
        // echo sprintf('calendar home is %s', $url).PHP_EOL;
        // echo sprintf('host is %s', $caldav_host).PHP_EOL;

        // first, ensures that the 'home' path is relative to the CalDav server
        // (this differs between servers)
        $path_without_prefix = $url;
        if (strpos($path_without_prefix, $caldav_host) === 0) {
            $path_without_prefix = substr($path_without_prefix, strlen($caldav_host));
        }
        if (strpos($path_without_prefix, $caldav_path) === 0) {
            $path_without_prefix = substr($path_without_prefix, strlen($caldav_path));
        }

        // then, turn the URL into an absolute URL so that we always know what to expect
        self::$calendar_home = $caldav_host . $caldav_path . $path_without_prefix;
    }

    function testGetCalendars(){
        $calendar_home = self::$calendar_home;

        $res   = self::$client->getCalendars($calendar_home);
        $this->assertTrue($res->isSuccessFull(), "GetCalendars request not successful");
        $this->assertTrue(count($res->getResponses()) > 0, "Request returned zero responses");
        return $res;
    }

    function testGetCalendar(){
        $res  = self::$client->getCalendar($this->getCalendarUrl());
        $this->assertTrue($res->isSuccessFull(), "Calendar request not successful");
        $this->assertTrue(!empty($res->getDisplayName()), "Display name not set");
        $this->assertTrue(!empty($res->getSyncToken()), "Sync-token empty");
        return $res;
    }

    function testSyncCalendar(){
        $cal = $this->testGetCalendar();

        $res    = self::$client->getCalendarSyncInfo(
            $this->getCalendarUrl(),
            $cal->getSyncToken());
        $this->assertTrue($res->isSuccessFull());
        $this->assertTrue(!empty($res->getSyncToken()));
    }

    function testCreateCalendar(){
        $home = self::$calendar_home;

        $link = self::$client->createCalendar(
            $home,
            new MakeCalendarRequestVO(
                null, // means: generate a unique name
                'OpenStack Sidney Summit Nov 2017',
                'Calendar to hold Summit Events',
                new DateTimeZone('Australia/Sydney')
            )
        );

        $this->assertTrue(!empty($link));
        self::$calendar_created_by_phpunit = $link . '/';
    }

    function testCreateEvent(){
        $res = self::$client->createEvent(
            self::$calendar_created_by_phpunit,
            new EventRequestVO(
                "test-event-" . md5(microtime(true)),
                'test event 4',
                'test event',
                'test event',
                new DateTime('2017-11-01 09:00:00'),
                new DateTime('2017-11-01 10:30:00'),
                new DateTimeZone('Australia/Sydney')
            )
        );

        $this->assertTrue($res->isSuccessFull());
        self::$event_created_by_phpunit = $res;
    }

    function testUpdateEvent(){
        $uid = self::$event_created_by_phpunit->getUid();
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
        $res = self::$client->updateEvent(self::$calendar_created_by_phpunit,
            $dto
        );

        $this->assertTrue($res->isSuccessFull());
    }

    function testGetEventByUrl(){

        $event_url = self::$event_created_by_phpunit->getResourceUrl();

        $v_card = self::$client->getEventVCardBy($event_url);

        $this->assertTrue(!empty($v_card));
    }

    function testGetEventsByUrl(){
        $event_url = self::$event_created_by_phpunit->getResourceUrl();

        $res = self::$client->getEventsBy(self::$calendar_created_by_phpunit,
            [$event_url]);

        $this->assertTrue($res->isSuccessFull());
    }

    function testDeleteEvent(){
        $uid  = self::$event_created_by_phpunit->getUid();

        $res = self::$client->deleteEvent(self::$calendar_created_by_phpunit,
            $uid
        );

        $this->assertTrue($res->isSuccessFull());
    }

    function testDeleteCalendar(){
        $host = getenv("CALDAV_SERVER_HOST");

        $calendar_url = $host . (str_replace($host, "", self::$calendar_created_by_phpunit));

        $res  = self::$client->getCalendar($calendar_url);

        $res = self::$client->deleteCalendar
        (
            $calendar_url,
            ""
        );

        $this->assertTrue(!empty($res));
    }
}