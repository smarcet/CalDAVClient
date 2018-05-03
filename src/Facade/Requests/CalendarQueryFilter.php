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

/**
 * Class CalendarQueryFilter
 * @package CalDAVClient\Facade\Requests
 */
final class CalendarQueryFilter
{
    /**
     * @var bool
     */
    private $get_etags;

    /**
     * @var bool
     */
    private $get_calendar_data;

    /**
     * @var DateTime
     */
    private $to;

    /**
     * @var DateTime
     */
    private $from;

    /**
     * CalendarQueryFilter constructor.
     * @param bool $get_etags
     * @param bool $get_calendar_data
     * @param DateTime $from
     * @param DateTime $to
     */
    public function __construct($get_etags = true, $get_calendar_data = false, DateTime $from = null,  DateTime $to = null)
    {
        $this->get_etags         = $get_etags;
        $this->get_calendar_data = $get_calendar_data;
        $this->from              = $from;
        $this->to                = $to;

        if(!is_null($this->from) && !is_null($this->to) && $this->from > $this->to)
            throw new \InvalidArgumentException("from should be lower than to param");
    }

    /**
     * @return bool
     */
    public function useGetETags()
    {
        return $this->get_etags;
    }

    /**
     * @return bool
     */
    public function useGetCalendarData()
    {
        return $this->get_calendar_data;
    }

    /**
     * @return DateTime
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return DateTime
     */
    public function getFrom()
    {
        return $this->from;
    }

}