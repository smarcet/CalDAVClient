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

use DateTimeZone;

/**
 * Class MakeCalendarRequestVO
 * @package CalDAVClient\Facade\Requests
 */
final class MakeCalendarRequestVO
{
    /**
     * @var string
     */
    private $uid;

    /**
     * @var DateTimeZone
     */
    private $timezone;

    /**
     * @var string
     */
    private $resource_name;

    /**
     * @var null|string
     */
    private $display_name;

    /**
     * @var null|string
     */
    private $description;

    /**
     * MakeCalendarRequestDTO constructor.
     * @param string $resource_name
     * @param string|null $display_name
     * @param string|null $description
     * @param DateTimeZone|null $timezone
     */
    public function __construct($resource_name, $display_name = null, $description = null, DateTimeZone $timezone = null)
    {
        $this->resource_name = strtolower($resource_name);
        $this->display_name  = $display_name;
        $this->description   = $description;
        $this->timezone      = $timezone;
        $this->uid           = md5(uniqid(mt_rand(), true));

        if(is_null($this->timezone)){
            $this->timezone = new DateTimeZone('UTC');
        }
    }

    /**
     * @param string $uid
     */
    public function setUID($uid){
        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getUID()
    {
        return $this->uid;
    }

    /**
     * @return DateTimeZone
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resource_name;
    }

    /**
     * @return null|string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }
}