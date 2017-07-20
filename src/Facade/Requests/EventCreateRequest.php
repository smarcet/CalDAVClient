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
     * @var EventRequestDTO
     */
    private $dto;

    /**
     * EventCreateRequest constructor.
     * @param EventRequestDTO $dto
     */
    public function __construct(EventRequestDTO $dto)
    {
        $this->dto = $dto;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $time_zone        = $this->dto->getTimeZone();
        $calendar         = ICalTimeZoneBuilder::build($time_zone, $this->dto->getProdId());
        $local_start_time = new DateTime($this->dto->getStartTime()->format('Y-m-d H:i:s'), $time_zone);
        $local_end_time   = new DateTime($this->dto->getEndTime()->format('Y-m-d H:i:s'), $time_zone);
        $event            = new Event($this->dto->getUID());

        $event
            ->setCreated(new DateTime())
            ->setDtStart($local_start_time)
            ->setDtEnd($local_end_time)
            ->setNoTime(false)
            ->setSummary($this->dto->getTitle())
            ->setDescription(strip_tags($this->dto->getDescription()))
            ->setDescriptionHTML($this->dto->getDescription());

        if($time_zone->getName() == 'UTC'){
            $event->setUseUtc(true)
                  ->setUseTimezone(false);
        }
        else{
            $event->setUseUtc(false)
                ->setUseTimezone(true);
        }

        if(!empty($this->dto->getLocationTitle())){
            $geo = sprintf("%s;%s", $this->dto->getLocationLat(), $this->dto->getLocationLng());
            $event->setLocation($this->dto->getLocationTitle(), $this->dto->getLocationTitle(), $geo);
        }

        $calendar->addComponent($event);

        return $calendar->render();
    }
}