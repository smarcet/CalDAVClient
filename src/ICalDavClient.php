<?php namespace CalDAVClient;
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

use CalDAVClient\Facade\Requests\CalendarQueryFilter;
use CalDAVClient\Facade\Requests\EventRequestDTO;
use CalDAVClient\Facade\Requests\MakeCalendarRequestDTO;
use CalDAVClient\Facade\Responses\CalendarHomesResponse;
use CalDAVClient\Facade\Responses\CalendarSyncInfoResponse;
use CalDAVClient\Facade\Responses\EventCreatedResponse;
use CalDAVClient\Facade\Responses\EventDeletedResponse;
use CalDAVClient\Facade\Responses\EventUpdatedResponse;
use CalDAVClient\Facade\Responses\GetCalendarResponse;
use CalDAVClient\Facade\Responses\GetCalendarsResponse;
use CalDAVClient\Facade\Responses\ResourceCollectionResponse;
use CalDAVClient\Facade\Responses\UserPrincipalResponse;

/**
 * Interface ICalDavClient
 * @package CalDAVClient
 * @see https://tools.ietf.org/html/rfc479
 */
interface ICalDavClient
{

    /**
     * @param string $server_url
     * @return void
     */
    function setServerUrl($server_url);

    /**
     * @param string $username
     * @param string $password
     * @return void
     */
    function setCredentials($username, $password);

    /**
     * @return bool
     */
    function isValidServer();

    /**
     * @return UserPrincipalResponse
     */
    function getUserPrincipal();

    /**
     * @param string $principal_url
     * @return CalendarHomesResponse
     */
    function getCalendarHome($principal_url);

    /**
     * @param string $calendar_home_set
     * @param MakeCalendarRequestDTO $dto
     * @see https://tools.ietf.org/html/rfc4791#section-5.3.1
     * @return string|boolean
     */
    function createCalendar($calendar_home_set, MakeCalendarRequestDTO $dto);

    /**
     * @param string $calendar_home_set_url
     * @return GetCalendarsResponse
     */
    function getCalendars($calendar_home_set_url);

    /**
     * @param string $calendar_url
     * @return GetCalendarResponse
     */
    function getCalendar($calendar_url);

    /**
     * @see https://tools.ietf.org/html/rfc6578
     * @param string $calendar_url
     * @param string $sync_token
     * @return CalendarSyncInfoResponse
     */
    function getCalendarSyncInfo($calendar_url, $sync_token);

    /**
     * @param string $calendar_url
     * @param EventRequestDTO $dto
     * @return EventCreatedResponse
     */
    function createEvent($calendar_url, EventRequestDTO $dto);

    /**
     * @param string $calendar_url
     * @param EventRequestDTO $dto
     * @param string $etag
     * @return EventUpdatedResponse
     */
    function updateEvent($calendar_url, EventRequestDTO $dto, $etag);

    /**
     * @param string $calendar_url
     * @param string $uid
     * @param string $etag
     * @return EventDeletedResponse
     */
    function deleteEvent($calendar_url, $uid, $etag);

    /**
     * @param string $event_url
     * @return string
     */
    function getEventVCardBy($event_url);

    /**
     * @param string $calendar_url
     * @param array $events_urls
     * @return ResourceCollectionResponse
     */
    function getEventsBy($calendar_url, array $events_urls);

    /**
     * @param string $calendar_url
     * @param CalendarQueryFilter $filter
     * @return ResourceCollectionResponse
     */
    function getEventsByQuery($calendar_url, CalendarQueryFilter $filter);

}