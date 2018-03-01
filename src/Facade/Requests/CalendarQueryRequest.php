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
use Sabre\Xml\Service;

/**
 * Class CalendarQueryRequest
 * @package CalDAVClient\Facade\Requests
 */
class CalendarQueryRequest implements IAbstractWebDAVRequest
{

    /**
     * CalendarQueryRequest constructor.
     * @param CalendarQueryFilter $filter
     */
    public function __construct(CalendarQueryFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @var CalendarQueryFilter
     */
    protected $filter;

    protected function formatTimestamp($datetime) {
        // Make a copy of the date, and convert it to GMT to accommodate
        // CalDAV's date & time formatting requirements
        $clone = clone $datetime;
        $clone->setTimezone(new \DateTimeZone("GMT"));

        return $clone->format('Ymd\THis\Z'); // 'Z' means: GMT time
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $service = new Service();

        $service->namespaceMap = [
            'DAV:'                          => 'D',
            'urn:ietf:params:xml:ns:caldav' => 'C',
        ];

        $filter = [];
        $props  = [];

        if($this->filter->useGetETags()){
            $props['{DAV:}getetag'] = '';
        }

        if($this->filter->useGetCalendarData()){
            $props['{urn:ietf:params:xml:ns:caldav}calendar-data'] = '';
        }

        if ($this->filter->getFrom() || $this->filter->getTo()) {
            $date_range = [];
            if ($this->filter->getFrom()) {
                $date_range['start'] = $this->formatTimestamp($this->filter->getFrom());
            }
            if ($this->filter->getTo()) {
                $date_range['end'] = $this->formatTimestamp($this->filter->getTo());
            }

            $filter[] = [
                'name'       => '{urn:ietf:params:xml:ns:caldav}comp-filter',
                'attributes' => ['name' => 'VCALENDAR'],
                'value'      => [
                    'name'       => '{urn:ietf:params:xml:ns:caldav}comp-filter',
                    'attributes' => ['name' => 'VEVENT'],
                    'value'      => [
                        'name'       => '{urn:ietf:params:xml:ns:caldav}time-range',
                        'attributes' => $date_range
                    ]
                ]
            ];
        }

        $nodes =  [
            '{DAV:}prop' => [
                $props
            ],
            '{urn:ietf:params:xml:ns:caldav}filter' => $filter
        ];
        return $service->write('{urn:ietf:params:xml:ns:caldav}calendar-query', $nodes);
    }
}
