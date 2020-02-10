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

use CalDAVClient\Facade\Exceptions\ForbiddenQueryException;
use CalDAVClient\Facade\Exceptions\NotValidGenericSingleCalDAVResponseException;

/**
 * Class GenericSinglePROPFINDCalDAVResponse
 * @package CalDAVClient\Facade\Responses
 * @see https://tools.ietf.org/html/rfc2518#section-11
 */
class GenericSinglePROPFINDCalDAVResponse extends AbstractCalDAVResponse
{
    /**
     * @var array
     */
    protected $found_props = [];

    /**
     * @var array
     */
    protected $not_found_props = [];

    /**
     * @return $this
     * @throws ForbiddenQueryException
     * @throw NotValidGenericSingleCalDAVResponseException
     */
    protected function parse()
    {

        if (!$this->isValid()) throw new NotValidGenericSingleCalDAVResponseException();
        if (!isset($this->content['response']['propstat'])) return $this;
        if (isset($this->content['response']['propstat']['prop']) && isset($this->content['response']['propstat']['status'])) {
            // all props found
            $status = $this->content['response']['propstat']['status'];
            if ($this->statusMatches($status, AbstractCalDAVResponse::HttpOKStatus)) {
                $this->found_props = $this->content['response']['propstat']['prop'];
                $this->not_found_props = null;
            }
            if ($this->statusMatches($status, AbstractCalDAVResponse::HttpNotFoundStatus)) {
                $this->not_found_props = $this->content['response']['propstat']['prop'];
                $this->found_props = null;
            }
            if ($this->statusMatches($status, AbstractCalDAVResponse::HttpForbiddenStatus)) {
                throw new ForbiddenQueryException();
            }
            return $this;
        }
        // multi props ( found or not found)
        foreach ($this->content['response']['propstat'] as $propstat) {

            if (!isset($propstat['status']) || !isset($propstat['prop'])) continue;

            if ($this->statusMatches($propstat['status'], AbstractCalDAVResponse::HttpOKStatus))
                $this->found_props = $propstat['prop'];

            if ($this->statusMatches($propstat['status'], AbstractCalDAVResponse::HttpNotFoundStatus))
                $this->not_found_props = $propstat['prop'];
        }
        return $this;
    }

    /**
     * @param string $responseStatus
     * @param string $desiredStatus
     * @return bool
     */
    protected function statusMatches($responseStatus, $desiredStatus)
    {
      return strtoupper($responseStatus) == $desiredStatus;
    }

    /**
     * @return bool
     */
    protected function isValid()
    {
        return parent::isValid() ;
    }

    /**
     * @return string|null
     */
    public function getHRef()
    {
        return isset($this->content['response']['href']) ? $this->content['response']['href'] : null;
    }

    /**
     * @return bool
     */
    public function isSuccessFull()
    {
        return $this->code == HttpResponse::HttpCodeMultiResponse;
    }
}
