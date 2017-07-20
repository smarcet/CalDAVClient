<?php namespace CalDAVClient\Facade\Requests;
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

/**
 * Class CalDAVRequestFactory
 * @package CalDAVClient\Facade\Requests
 */
final class CalDAVRequestFactory
{
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

    private function __construct(){}

    /**
     * @var CalDAVRequestFactory
     */
    private static $instance;

    /**
     * @return CalDAVRequestFactory
     */
    public static function getInstance(){
        if(is_null(self::$instance)) self::$instance = new CalDAVRequestFactory();
        return self::$instance;
    }

    /**
     * @param string $type
     * @param array $params
     * @return IAbstractWebDAVRequest|null
     * @throws \InvalidArgumentException
     */
    public function build($type, $params = []){
        switch(strtoupper($type)){
            case self::PrincipalRequestType:
                return new UserPrincipalRequest();
            case self::CalendarHomeRequestType:
                return new CalendarHomeRequest();
            case self::CalendarsRequestType:
                return new GetCalendarsRequest();
            case self::CalendarRequestType:
                return new GetCalendarRequest();
            case self::CalendarSyncRequestType:
                if(count($params) == 0 )
                    throw new \InvalidArgumentException();
                return new CalendarSyncRequest($params[0]);
            case self::CalendarMultiGetRequestType:
                if(count($params) == 0 )
                    throw new \InvalidArgumentException();
                return new CalendarMultiGetRequest($params[0]);
            case self::CalendarQueryRequestType:
                if(count($params) == 0 )
                    throw new \InvalidArgumentException();
                return new CalendarQueryRequest($params[0]);
            case self::CalendarCreateRequestType:
                if(count($params) == 0 )
                    throw new \InvalidArgumentException();
                return new CalendarCreateRequest($params[0]);
            case self::EventCreateRequestType:
                if(count($params) == 0 )
                    throw new \InvalidArgumentException();
                return new EventCreateRequest($params[0]);
            case self::EventUpdateRequestType:
                if(count($params) == 0 )
                    throw new \InvalidArgumentException();
                return new EventUpdateRequest($params[0]);
        }
        return null;
    }
}