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
 * Class GenericMultiCalDAVResponse
 * @package CalDAVClient\Facade\Responses
 */
class GenericMultiCalDAVResponse extends AbstractCalDAVResponse
{
    /**
     * @var GenericSinglePROPFINDCalDAVResponse[]
     */
    protected $responses = [];

    protected function parse()
    {
        if(isset($this->content['response'])){

            if(isset($this->content['response']['propstat'])) {
                // its a collection with one single element
                $single_resource = $this->buildSingleResponse();
                $single_resource->setContent(['response' => $this->content['response']]);
                $single_resource->parse();
                $this->responses[] = $single_resource;
                return;
            }

            foreach ($this->content['response'] as $val) {
                $single_resource = $this->buildSingleResponse();
                $single_resource->setContent(['response' => $val]);
                $single_resource->parse();
                $this->responses[] = $single_resource;
            }
        }
    }

    /**
     * @return GenericSinglePROPFINDCalDAVResponse[]
     */
    public function getResponses(){
        return $this->responses;
    }

    /**
     * @return GenericSinglePROPFINDCalDAVResponse
     */
    protected function buildSingleResponse()
    {
        return new GenericSinglePROPFINDCalDAVResponse();
    }

    /**
     * @return bool
     */
    public function isSuccessFull()
    {
        return $this->code == HttpResponse::HttpCodeMultiResponse;
    }
}