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
 * Class UserPrincipalSingleResponse
 * @package CalDAVClient\Facade\Responses
 */
final class UserPrincipalSingleResponse extends GenericSinglePROPFINDCalDAVResponse
{
    /**
     * @return string
     */
    public function getPrincipalUrl() {
        $url = isset($this->found_props['current-user-principal']) &&  isset($this->found_props['current-user-principal']['href']) ?
            $this->server_url.$this->found_props['current-user-principal']['href'] : null;
        // check on not found one ( issue on caldav icloud imp)
        if(empty($url))
            $url =  isset($this->not_found_props['current-user-principal']) &&  isset($this->not_found_props['current-user-principal']['href']) ?
                $this->server_url.$this->not_found_props['current-user-principal']['href'] : null;
        return $url;
    }
}