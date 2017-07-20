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
 * Class CalendarMultigetRequest
 * @package CalDAVClient\Facade\Requests
 */
final class CalendarMultiGetRequest implements IAbstractWebDAVRequest
{
    /**
     * @var string[]
     */
    private $hrefs;

    /**
     * CalendarMultiGetRequest constructor.
     * @param array $hrefs
     */
    public function __construct(array $hrefs){
        $this->hrefs = $hrefs;
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
        $nodes =  [
            '{DAV:}prop' => [
                '{DAV:}getetag' => '',
                '{urn:ietf:params:xml:ns:caldav}calendar-data' => ''
            ],
        ];
        // set hrefs
        foreach ($this->hrefs as $href){
            $nodes[] = ['name' => '{DAV:}href', 'value' => $href];
        }
        return $service->write('{urn:ietf:params:xml:ns:caldav}calendar-multiget', $nodes);
    }
}