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
 * Class EventCreatedResponse
 * @package CalDAVClient\Facade\Responses
 */
class EventCreatedResponse extends HttpResponse
{
    /**
     * @var string
     */
    protected $uid;
    /**
     * @var string
     */
    protected $etag;

    /**
     * @var string
     */
    protected $resource_url;

    /**
     * EventCreatedResponse constructor.
     * @param string $uid
     * @param string $etag
     * @param string $resource_url
     * @param string $body
     * @param int $code
     */
    public function __construct($uid, $etag, $resource_url, $body, $code)
    {
        parent::__construct($body, $code);
        $this->uid          = $uid;
        $this->etag         = $etag;
        $this->resource_url = $resource_url;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getETag()
    {
        return $this->etag;
    }

    /**
     * @return string
     */
    public function getResourceUrl(){
        return $this->resource_url;
    }

    /**
     * @return bool
     */
    public function isSuccessFull(){
        return $this->code == HttpResponse::HttpCodeCreated;
    }
}