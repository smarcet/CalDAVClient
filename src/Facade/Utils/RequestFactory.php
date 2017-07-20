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
    * @param string $url
    * @param string $body
    * @param int $depth
    * @return Request
    */
    static function createPropFindRequest($url , $body, $depth = 1){
        return new Request('PROPFIND',  $url ,  [
            'Depth'         => $depth,
            "Prefer"        => "return-minimal",
            "Content-Type"  => ContentTypes::ContentTypeXml
        ],
            $body
        );
    }

    /**
     * @param string $url
     * @param string $body
     * @return Request
     */
    static function createMakeCalendarRequest($url , $body){
        return new Request('MKCALENDAR',  $url ,  [
            "Content-Type"  => ContentTypes::ContentTypeXml
        ],
            $body
        );
    }

    /**
     * @param string $url
     * @param int $depth
     * @return Request
     */
    static function createOptionsRequest($url, $depth = 1){
        return new Request('OPTIONS',  $url ,  [
            'Depth'         => $depth,
            "Prefer"        => "return-minimal",
            "Content-Type"  => ContentTypes::ContentTypeXml
        ]);
    }

    /**
     * @param string $url
     * @param string $body
     * @param int $depth
     * @return Request
     */
    static function createReportRequest($url , $body, $depth = 1){
        return new Request('REPORT',  $url ,  [
            'Depth'         => $depth,
            "Prefer"        => "return-minimal",
            "Content-Type"  => ContentTypes::ContentTypeXml
        ],
            $body
        );
    }

    /**
     * @param string $url
     * @param string $etag
     * @return Request
     */
    static function createDeleteRequest($url , $etag){
        return new Request('DELETE',  $url ,  [
            "If-Match"  => $etag
        ]);
    }

    /**
     * @param string $url
     * @return Request
     */
    static function createGetRequest($url){
        return new Request('GET',  $url ,  [
        ]);
    }

    /**
     * @param string $url
     * @param string $body
     * @param string $etag
     * @return Request
     */
    static function createPutRequest($url, $body, $etag = null){
        $headers = [
            "Content-Type"  => ContentTypes::ContentTypeCalendar,
        ];

        if(empty($etag)){
            $headers["If-None-Match"] = "*";
        }
        else{
            $headers["If-Match"] = $etag;
        }

        return new Request('PUT',  $url, $headers, $body);
    }

}