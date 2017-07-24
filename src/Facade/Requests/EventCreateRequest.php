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

use CalDAVClient\Facade\Utils\ICalTimeZoneBuilder;
use Eluceo\iCal\Component\Event;
use DateTime;

/**
 * Class EventCreateRequest
 * @package CalDAVClient\Facade\Requests
 */
class EventCreateRequest implements IAbstractWebDAVRequest
{
    /**
     * @var EventRequestVO
     */
    private $vo;

    /**
     * EventCreateRequest constructor.
     * @param EventRequestVO $vo
     */
    public function __construct(EventRequestVO $vo)
    {
        $this->vo = $vo;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $time_zone        = $this->vo->getTimeZone();
        $calendar         = ICalTimeZoneBuilder::build($time_zone, $this->vo->getProdId());
        $local_start_time = new DateTime($this->vo->getStartTime()->format('Y-m-d H:i:s'), $time_zone);
        $local_end_time   = new DateTime($this->vo->getEndTime()->format('Y-m-d H:i:s'), $time_zone);
        $event            = new Event($this->vo->getUID());

        $event
            ->setCreated(new DateTime())
            ->setDtStart($local_start_time)
            ->setDtEnd($local_end_time)
            ->setNoTime(false)
            ->setSummary($this->vo->getTitle())
            ->setDescription(strip_tags($this->vo->getDescription()))
            ->setDescriptionHTML($this->vo->getDescription());

        if($time_zone->getName() == 'UTC'){
            $event->setUseUtc(true)
                  ->setUseTimezone(false);
        }
        else{
            $event->setUseUtc(false)
                ->setUseTimezone(true);
        }

        if(!empty($this->vo->getLocationTitle())){
            $geo = sprintf("%s;%s", $this->vo->getLocationLat(), $this->vo->getLocationLng());
            $event->setLocation($this->vo->getLocationTitle(), $this->vo->getLocationTitle(), $geo);
        }

        $calendar->addComponent($event);

        return $calendar->render();
    }
}