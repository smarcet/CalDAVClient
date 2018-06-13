<?php namespace CalDAVClient\Facade\Responses;
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
 * Class GetCalendarResponse
 * @package CalDAVClient\Facade\Responses
 */
final class GetCalendarResponse extends GenericSinglePROPFINDCalDAVResponse
{
    const ResourceTypeCalendar = 'calendar';
    /**
     * @return string
     */
    public function getDisplayName(){
        return isset($this->found_props['displayname']) ? $this->found_props['displayname'] : null;
    }

    public function getResourceType(){
        return isset($this->found_props['resourcetype']) ? $this->found_props['resourcetype'] : null;
    }

    /**
     * @see https://tools.ietf.org/html/rfc6578
     * @return string
     */
    public function getSyncToken(){
        return isset($this->found_props['sync-token']) ? $this->found_props['sync-token'] : null;
    }

    /**
     * @return string
     */
    public function getCTag(){
        return isset($this->found_props['getctag']) ? $this->found_props['getctag'] : null;
    }

}