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
 * Class GetCalendarRequest
 * @package CalDAVClient\Facade\Requests
 */
final class GetCalendarRequest extends AbstractPropFindWebDAVRequest
{

    /**
     * @see https://tools.ietf.org/html/rfc6578 for sync-token
     * GetCalendarRequest constructor.
     */
    public function __construct(){
        $this->properties = [
            '{DAV:}displayname',
            '{DAV:}resourcetype',
            '{DAV:}sync-token',
            '{DAV:}getetag',
            '{http://calendarserver.org/ns/}getctag',
        ];
    }
}