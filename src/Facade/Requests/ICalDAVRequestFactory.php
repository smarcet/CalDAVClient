<?php namespace CalDAVClient\Facade\Requests;
/**
 * Copyright 2019 OpenStack Foundation
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

/**
 * Interface ICalDAVRequestFactory
 * @package CalDAVClient\Facade\Requests
 */
interface ICalDAVRequestFactory {
    const PrincipalRequestType        = 'PRINCIPAL';
    const CalendarHomeRequestType     = 'CALENDAR_HOME';
    const CalendarsRequestType        = 'CALENDARS';
    const CalendarRequestType         = 'CALENDAR';
    const CalendarSyncRequestType     = 'CALENDAR_SYNC';
    const CalendarMultiGetRequestType = 'CALENDAR_MULTIGET';
    const CalendarQueryRequestType    = 'CALENDAR_QUERY';
    const CalendarCreateRequestType   = 'CREATE_CALENDAR';
    const EventCreateRequestType      = 'CREATE_EVENT';
    const EventUpdateRequestType      = 'UPDATE_EVENT';

    /**
     * Builds a request of a certain type.
     *
     * @param string $type
     * @param array $params
     * @return IAbstractWebDAVRequest|null
     * @throws \InvalidArgumentException
     */
    public function build($type, $params = []);
}
