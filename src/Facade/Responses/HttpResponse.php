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
 * Class HttpResponse
 * @package CalDAVClient\Facade\Responses
 */
abstract class HttpResponse
{
    const HttpOKStatus        = 'HTTP/1.1 200 OK';
    const HttpNotFoundStatus  = 'HTTP/1.1 404 Not Found';
    const HttpForbiddenStatus = 'HTTP/1.1 403 Forbidden';

    const HttpCodeCreated       = 201;
    const HttpCodeNoContent     = 204;
    const HttpCodeOk            = 200;
    const HttpCodeMultiResponse = 207;
    /**
     * @var string
     */
    protected $body;

    /**
     * @var int
     */
    protected $code;

    /**
     * HttpResponse constructor.
     * @param string $body
     * @param int $code
     */
    public function __construct($body, $code)
    {
        $this->body = $body;
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return bool
     */
    abstract public function isSuccessFull();
}