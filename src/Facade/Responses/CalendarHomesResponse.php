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
 * Class CalendarHomesResponse
 * @package CalDAVClient\Facade\Responses
 */
final class CalendarHomesResponse extends GenericSinglePROPFINDCalDAVResponse
{
    /**
     * @return string
     */
    public function getCalendarHomeSetUrl(){
        return isset($this->found_props['calendar-home-set']) && isset($this->found_props['calendar-home-set']['href']) ?
            $this->found_props['calendar-home-set']['href'] : null;
    }

    /**
     * @return string|null
     */
    public function getRealCalDAVHost(){
        $url = $this->getCalendarHomeSetUrl();
        return !empty($url) ? parse_url($url,PHP_URL_HOST) : null;
    }
}