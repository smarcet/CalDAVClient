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

use GuzzleHttp\Psr7\Request;

/**
 * Class RequestFactory
 * @package CalDAVClient\Facade\Utils
 */
final class RequestFactory
{

    /**
     * @param string $http_method
     * @param array $params
     * @return array
     */
    private static function createHeadersFor($http_method, array $params = []){
        switch ($http_method){
            case HttpMethods::PropFind:
            case HttpMethods::Options:
            case HttpMethods::Report:
                return  [
                    Headers::Depth        => $params[0],
                    Headers::Prefer       => "return-minimal",
                    Headers::ContentType  => ContentTypes::Xml
                ];
            case HttpMethods::Delete:
                $etag = $params[0];
                if(!empty($etag)) {
                    return [
                        Headers::IfMatch => $etag,
                    ];
                }
                return [];
            case HttpMethods::MakeCalendar:
                return [
                    Headers::ContentType  => ContentTypes::Xml
                ];
            case HttpMethods::Put:

               $len  = $params[0];
               $etag = $params[1];

               $headers = [
                   Headers::ContentLength => intval($len),
                   Headers::ContentType   => ContentTypes::Calendar,
               ];

               if(!empty($etag)){
                   $headers[Headers::IfMatch] = $etag;
               }

               return $headers;
        }
        return [];
    }
    /**
    * @param string $url
    * @param string $body
    * @param int $depth
    * @return Request
    */
    public static function createPropFindRequest($url , $body, $depth = 1){
        return new Request
        (
            HttpMethods::PropFind,
            $url ,
            self::createHeadersFor(HttpMethods::PropFind, [$depth]),
            $body
        );
    }

    /**
     * @param string $url
     * @param string $body
     * @return Request
     */
    public static function createMakeCalendarRequest($url , $body){
        return new Request
        (
            HttpMethods::MakeCalendar,
            $url,
            self::createHeadersFor(HttpMethods::MakeCalendar),
            $body
        );
    }

    /**
     * @param string $url
     * @param int $depth
     * @return Request
     */
    public static function createOptionsRequest($url, $depth = 1){
        return new Request
        (
            HttpMethods::Options,
            $url,
            self::createHeadersFor(HttpMethods::Options, [$depth])
        );
    }

    /**
     * @param string $url
     * @param string $body
     * @param int $depth
     * @return Request
     */
    public static function createReportRequest($url , $body, $depth = 1){
        return new Request
        (
            HttpMethods::Report,
            $url,
            self::createHeadersFor(HttpMethods::Report, [$depth]),
            $body
        );
    }

    /**
     * @param string $url
     * @param string $etag
     * @return Request
     */
    public static function createDeleteRequest($url , $etag){
        return new Request
        (
            HttpMethods::Delete,
            $url,
            self::createHeadersFor(HttpMethods::Delete, [$etag])
        );
    }

    /**
     * @param string $url
     * @return Request
     */
    public static function createGetRequest($url){
        return new Request
        (
            HttpMethods::Get,
            $url,
            self::createHeadersFor(HttpMethods::Get)
        );
    }

    /**
     * @param string $url
     * @param string $body
     * @param string $etag
     * @return Request
     */
     public static function createPutRequest($url, $body, $etag = null){

        return new Request
        (
            HttpMethods::Put,
            $url,
            self::createHeadersFor(HttpMethods::Put, [strlen($body), $etag]),
            $body
        );
    }

    /**
     * @param string $url
     * @param string $body
     * @param string $etag
     * @return Request
     */
    public static function createPostRequest($url, $body, $etag = null){
        return new Request
        (
            HttpMethods::Post,
            $url,
            self::createHeadersFor(HttpMethods::Post, [$etag]),
            $body
        );
    }

}