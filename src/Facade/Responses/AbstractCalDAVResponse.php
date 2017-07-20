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
use CalDAVClient\Facade\Exceptions\XMLResponseParseException;
/**
 * Class AbstractCalDAVResponse
 * @package CalDAVClient\Facade\Responses
 */
abstract class AbstractCalDAVResponse extends HttpResponse
{

    /**
     * @var string
     */
    protected $server_url;

    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    /**
     * @var array
     */
    protected $content;

    /**
     * AbstractCalDAVResponse constructor.
     * @param string|null $server_url
     * @param string|null $body
     * @param int $code
     */
    public function __construct($server_url = null, $body = null, $code = HttpResponse::HttpCodeOk )
    {
        parent::__construct($body, $code);
        $this->server_url = $server_url;
        if(!empty($this->body)) {
            $this->xml     = simplexml_load_string($this->body);
            if($this->xml === FALSE)
                throw new XMLResponseParseException();
            $this->content = $this->toAssocArray($this->xml);
            $this->parse();
        }
    }

    public function __destruct()
    {
    }

    protected function setContent($content){
        $this->content = $content;
    }

    protected abstract function parse();
    /**
     * @param $xml
     * @return array
     */
    protected function toAssocArray($xml) {
        $string = json_encode($xml);
        $array  = json_decode($string, true);
        return $array;
    }

    /**
     * @return bool
     */
    protected function isValid(){
        return isset($this->content['response']);
    }
}