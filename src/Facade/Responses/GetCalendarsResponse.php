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
 * Class GetCalendarsResponse
 * @package CalDAVClient\Facade\Responses
 */
final class GetCalendarsResponse extends GenericMultiCalDAVResponse
{
    /**
     * @return GenericSinglePROPFINDCalDAVResponse
     */
    protected function buildSingleResponse()
    {
        return new GetCalendarResponse();
    }

    /**
     * @param string $type
     * @return array
     */
    public function getResponseByType($type){
        $responses = [];

        foreach ($this->getResponses() as $response){
            if(!$response instanceof GetCalendarResponse) continue;
            $resource_types = $response->getResourceType();
            if(in_array($type, array_keys($resource_types))) $responses[] = $response;
        }

        return $responses;
    }
}

