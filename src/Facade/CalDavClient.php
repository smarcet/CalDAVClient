<?php namespace CalDAVClient\Facade;

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

use CalDAVClient\Facade\Exceptions\ConflictException;
use CalDAVClient\Facade\Exceptions\ForbiddenException;
use CalDAVClient\Facade\Requests\CalDAVRequestFactory;
use CalDAVClient\Facade\Requests\CalendarQueryFilter;
use CalDAVClient\Facade\Requests\EventRequestVO;
use CalDAVClient\Facade\Requests\MakeCalendarRequestVO;
use CalDAVClient\Facade\Responses\CalendarDeletedResponse;
use CalDAVClient\Facade\Responses\CalendarHomesResponse;
use CalDAVClient\Facade\Responses\CalendarSyncInfoResponse;
use CalDAVClient\Facade\Responses\EventCreatedResponse;
use CalDAVClient\Facade\Responses\EventDeletedResponse;
use CalDAVClient\Facade\Responses\EventUpdatedResponse;
use CalDAVClient\Facade\Responses\GetCalendarMultiResponse;
use CalDAVClient\Facade\Responses\GetCalendarsResponse;
use CalDAVClient\Facade\Responses\ResourceCollectionResponse;
use CalDAVClient\Facade\Responses\UserPrincipalResponse;
use CalDAVClient\Facade\Utils\RequestFactory;
use CalDAVClient\ICalDavClient;
use CalDAVClient\Facade\Exceptions\NotFoundResourceException;
use CalDAVClient\Facade\Exceptions\ServerErrorException;
use CalDAVClient\Facade\Exceptions\UserUnAuthorizedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;

/**
 * Class CalDavClient
 * @package CalDAVClient\Facade
 */
final class CalDavClient implements ICalDavClient
{

    /**
     * As indicated in Section 3.10 of [RFC2445], the URL of calendar object
     * resources containing (an arbitrary set of) calendaring and scheduling
     * information may be suffixed by ".ics", and the URL of calendar object
     * resources containing free or busy time information may be suffixed by
     * ".ifb".
     */

    const SchedulingInformationSuffix   = '.ics';

    const FreeBusyTimeInformationSuffix = '.ics';

    const ETagHeader           = 'ETag';

    const DAVHeader            = 'DAV';

    const CalendarAccessOption = 'calendar-access';

    const DefaultAuthType      = 'basic';

    /**
     * @var string
     */
    private $server_url;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $authtype = self::DefaultAuthType;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $timeout = 60;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * CalDavClient constructor.
     * @param string $server_url
     * @param string|null $user
     * @param string|null $password
     * @param string $authtype
     * @param array $headers Additional headers to send with each request
     */
    public function __construct($server_url, $user = null, $password = null, $authtype = self::DefaultAuthType, $headers=[])
    {
        $this->server_url = $server_url;
        $this->user       = $user;
        $this->password   = $password;
        $this->authtype   = $authtype;
        $this->setHeaders($headers);

        $this->client     = new Client();
    }

    /**
     * @param string $server_url
     * @return void
     */
    public function setServerUrl($server_url)
    {
        $this->server_url = $server_url;
    }

    /**
     * @param string $username
     * @param string $password
     * @return void
     */
    public function setCredentials($username, $password)
    {
        $this->user     = $username;
        $this->password = $password;
    }

    public function setAuthenticationType($authtype) {
        $this->authtype = $authtype;
    }

    /**
     * Set headers that will be sent with each request
     *
     * @param array $headers
     */
    public function setHeaders($headers = []) {
        $this->headers = $headers;
    }
    
    /**
     * @param Request $http_request
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function makeRequest(Request $http_request){
        try{
            $options = [
                'timeout' => $this->timeout
            ];
            switch (strtolower(trim($this->authtype))) {
                case "basic":
                case "digest":
                case "ntlm":
                    $options['auth'] = [$this->user, $this->password, $this->authtype];
                    break;
            }

            if (!empty($this->headers)) {
                $options['headers'] = $this->headers;
            }

            return $this->client->send($http_request, $options);
        }
        catch (ClientException $ex){
            switch($ex->getCode()){
                case 401:
                    throw new UserUnAuthorizedException();
                    break;
                case 403:
                    throw new ForbiddenException();
                    break;
                case 404:
                    throw new NotFoundResourceException();
                    break;
                case 409:
                    throw new ConflictException($ex->getMessage(), $ex->getCode());
                    break;
                default:
                    throw new ServerErrorException($ex->getMessage(), $ex->getCode());
                    break;
            }
        }
    }

    /**
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function isValidServer()
    {

        $http_response = $this->makeRequest(
            RequestFactory::createOptionsRequest($this->server_url)
        );

        $res     = $http_response->hasHeader(self::DAVHeader);
        $options = [];
        if($res){
            $val = $http_response->getHeaderLine(self::DAVHeader);
            if(!empty($val)){
                $options = explode(', ', $val);
            }
        }

        return $res && count($options) > 0 && in_array(self::CalendarAccessOption, $options);
    }

    /**
     * @return UserPrincipalResponse
     */
    public function getUserPrincipal()
    {
        $http_response = $this->makeRequest(
            RequestFactory::createPropFindRequest
            (
                $this->server_url,
                CalDAVRequestFactory::getInstance()->build(CalDAVRequestFactory::PrincipalRequestType)->getContent(),
                0
            )
        );

        return new UserPrincipalResponse($this->server_url, (string)$http_response->getBody(), $http_response->getStatusCode());
    }

    /**
     * @param string $principal_url
     * @return CalendarHomesResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCalendarHome($principal_url)
    {
        $http_response = $this->makeRequest(
            RequestFactory::createPropFindRequest
            (
                $principal_url,
                CalDAVRequestFactory::getInstance()->build(CalDAVRequestFactory::CalendarHomeRequestType)->getContent(),
                0
            )
        );

        return new CalendarHomesResponse($this->server_url, (string)$http_response->getBody(), $http_response->getStatusCode());
    }

    /**
     * @param string $calendar_home_set
     * @param MakeCalendarRequestVO $vo
     * @see https://tools.ietf.org/html/rfc4791#section-5.3.1
     * @return string|boolean
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createCalendar($calendar_home_set, MakeCalendarRequestVO $vo)
    {
        $uid           = $vo->getUID();
        $resource_name = $vo->getResourceName();

        $resource_url  = $calendar_home_set . ($resource_name ? $resource_name : $uid);
        $http_response = $this->makeRequest(
            RequestFactory::createMakeCalendarRequest
            (
                $resource_url,
                CalDAVRequestFactory::getInstance()->build(CalDAVRequestFactory::CalendarCreateRequestType, [$vo])->getContent()
            )
        );

        return $http_response->getStatusCode() == 201 ? $resource_url : false;
    }

    /**
     * @param string $calendar_home_set_url
     * @return GetCalendarsResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCalendars($calendar_home_set_url)
    {
        $http_response = $this->makeRequest(
            RequestFactory::createPropFindRequest
            (
                $calendar_home_set_url,
                CalDAVRequestFactory::getInstance()->build(CalDAVRequestFactory::CalendarsRequestType)->getContent()
            )
        );

        return new GetCalendarsResponse($this->server_url, (string)$http_response->getBody(), $http_response->getStatusCode());
    }

    /**
     * @param string $calendar_url
     * @param int $depth Defaults to 0 to obtain calendar metadata. Set to 1 to obtain (all) calendar contents as well.
     * @return GetCalendarMultiResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCalendar($calendar_url, $depth = 0)
    {
        $http_response = $this->makeRequest(
            RequestFactory::createPropFindRequest
            (
                $calendar_url,
                CalDAVRequestFactory::getInstance()->build(CalDAVRequestFactory::CalendarRequestType)->getContent(),
                $depth
            )
        );

        return new GetCalendarMultiResponse($this->server_url, (string)$http_response->getBody(), $http_response->getStatusCode());
    }


    /**
     * @param string $calendar_url
     * @param string $sync_token
     * @return CalendarSyncInfoResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCalendarSyncInfo($calendar_url, $sync_token)
    {

        $http_response = $this->makeRequest(
            RequestFactory::createReportRequest
            (
                $calendar_url,
                CalDAVRequestFactory::getInstance()->build(CalDAVRequestFactory::CalendarSyncRequestType, [$sync_token])->getContent()
            )
        );

        return new CalendarSyncInfoResponse($this->server_url, (string)$http_response->getBody(), $http_response->getStatusCode());
    }

    /**
     * @param string $calendar_url
     * @param EventRequestVO $vo
     * @return EventCreatedResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createEvent($calendar_url, EventRequestVO $vo)
    {
        $uid           = $vo->getUID();
        $resource_url  = $calendar_url.$uid.self::SchedulingInformationSuffix;
        $http_response = $this->makeRequest(
            RequestFactory::createPutRequest
            (
                $resource_url,
                CalDAVRequestFactory::getInstance()->build(CalDAVRequestFactory::EventCreateRequestType, [$vo])->getContent()
            )
        );
        $etag = $http_response->hasHeader(self::ETagHeader) ? $http_response->getHeaderLine(self::ETagHeader) : null;
        return new EventCreatedResponse
        (
            $uid,
            $etag,
            $resource_url,
            (string)$http_response->getBody(),
            $http_response->getStatusCode()
        );
    }

    /**
     * @param string $calendar_url
     * @param EventRequestVO $vo
     * @param string $etag
     * @return EventUpdatedResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateEvent($calendar_url, EventRequestVO $vo, $etag = null)
    {
        $uid           = $vo->getUID();
        $resource_url  = $calendar_url.$uid.self::SchedulingInformationSuffix;
        $http_response = $this->makeRequest(
            RequestFactory::createPutRequest
            (
                $resource_url,
                CalDAVRequestFactory::getInstance()->build(CalDAVRequestFactory::EventUpdateRequestType, [$vo])->getContent(),
                $etag
            )
        );
        $etag = $http_response->hasHeader(self::ETagHeader) ? $http_response->getHeaderLine(self::ETagHeader) : null;
        return new EventUpdatedResponse
        (
            $uid,
            $etag,
            $resource_url,
            (string)$http_response->getBody(),
            $http_response->getStatusCode()
        );
    }

    /**
     * @param string $calendar_url
     * @param string $uid
     * @param string $etag
     * @return EventDeletedResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteEvent($calendar_url, $uid, $etag = null)
    {
        $http_response = $this->makeRequest(
            RequestFactory::createDeleteRequest
            (
                $calendar_url.$uid.self::SchedulingInformationSuffix,
                $etag
            )
        );

        return new EventDeletedResponse
        (
            (string)$http_response->getBody(), $http_response->getStatusCode()
        );
    }

    /**
     * @param string $event_url
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getEventVCardBy($event_url){
        $http_response = $this->makeRequest(
            RequestFactory::createGetRequest
            (
                $event_url
            )
        );

        $ical = (string)$http_response->getBody();
        return $ical;
    }

    /**
     * @param string $calendar_url
     * @param array $events_urls
     * @return ResourceCollectionResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getEventsBy($calendar_url, array $events_urls)
    {
        $http_response = $this->makeRequest(
            RequestFactory::createReportRequest
            (
                $calendar_url,
                CalDAVRequestFactory::getInstance()->build(CalDAVRequestFactory::CalendarMultiGetRequestType, [$events_urls])->getContent()
            )
        );

        return new ResourceCollectionResponse
        (
            $this->server_url,
            (string)$http_response->getBody(),
            $http_response->getStatusCode()
        );
    }

    /**
     * @param string $calendar_url
     * @param CalendarQueryFilter $filter
     * @return ResourceCollectionResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getEventsByQuery($calendar_url, CalendarQueryFilter $filter)
    {

        $http_response = $this->makeRequest(
            RequestFactory::createReportRequest
            (
                $calendar_url,
                CalDAVRequestFactory::getInstance()->build(CalDAVRequestFactory::CalendarQueryRequestType, [$filter])->getContent()
            )
        );

        return new ResourceCollectionResponse
        (
            $this->server_url,
            (string)$http_response->getBody(),
            $http_response->getStatusCode()
        );
    }

    /**
     * @param string $calendar_url
     * @param string|null $etag
     * @return CalendarDeletedResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteCalendar($calendar_url, $etag = null)
    {
        $http_response = $this->makeRequest(
            RequestFactory::createDeleteRequest
            (
                $calendar_url,
                $etag
            )
        );

        return new CalendarDeletedResponse
        (
            (string)$http_response->getBody(), $http_response->getStatusCode()
        );
    }
}
