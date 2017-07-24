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

use DateTime;
use DateTimeZone;

/**
 * Class EventRequestVO
 * @package CalDAVClient\Facade\Requests
 */
final class EventRequestVO
{
    /**
     * @var string
     */
    private $prod_id;
    /**
     * @var string
     */
    private $uid;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $summary;

    /**
     * @var DateTime
     */
    private $start_time;

    /**
     * @var DateTime
     */
    private $end_time;

    /**
     * @var string
     */
    private $location_name;

    /**
     * @var string
     */
    private $location_title;

    /**
     * @var string
     */
    private $location_lat;

    /**
     * @var string
     */
    private $location_lng;

    /**
     * @var DateTimeZone
     */
    private $time_zone;

    /**
     * EventRequestDTO constructor.
     * @param string $prod_id
     * @param string $title
     * @param string $description
     * @param string $summary
     * @param DateTime $start_time
     * @param DateTime $end_time
     * @param DateTimeZone $time_zone
     * @param string $location_name
     * @param string $location_title
     * @param string $location_lat
     * @param string $location_lng
     */
    public function __construct
    (
        $prod_id,
        $title,
        $description,
        $summary,
        DateTime $start_time,
        DateTime $end_time,
        DateTimeZone $time_zone = null,
        $location_name = null,
        $location_title = null,
        $location_lat = null,
        $location_lng = null
    )
    {
        $this->prod_id         = $prod_id;
        $this->uid             = md5(uniqid(mt_rand(), true));
        $this->title           = $title;
        $this->description     = $description;
        $this->summary         = $summary;
        $this->start_time      = $start_time;
        $this->end_time        = $end_time;
        $this->location_name   = $location_name;
        $this->location_title  = $location_title;
        $this->location_lat    = $location_lat;
        $this->location_lng    = $location_lng;
        $this->time_zone       = $time_zone;
        if(is_null($this->time_zone)){
            $this->time_zone = new DateTimeZone('UTC');
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @return DateTime
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @return DateTime
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * @return string
     */
    public function getLocationName()
    {
        return $this->location_name;
    }

    /**
     * @return string
     */
    public function getLocationTitle()
    {
        return $this->location_title;
    }

    /**
     * @return string
     */
    public function getLocationLat()
    {
        return $this->location_lat;
    }

    /**
     * @return string
     */
    public function getLocationLng()
    {
        return $this->location_lng;
    }

    /**
     * @return DateTimeZone
     */
    public function getTimeZone(){
        return $this->time_zone;
    }

    /**
     * @return string
     */
    public function getProdId()
    {
        return $this->prod_id;
    }

}