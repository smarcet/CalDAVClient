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
 * Class AbstractPropFindWebDAVRequest
 * @package CalDAVClient\Facade\Requests
 * @see https://tools.ietf.org/html/rfc2518#section-8.1
 */
abstract class AbstractPropFindWebDAVRequest implements IAbstractWebDAVRequest
{
    protected $properties = [];

    /**
     * @return string
     */
    public function getContent()
    {
        $service = new Service();

        $service->namespaceMap = [
            'DAV:'                           => 'D',
            'urn:ietf:params:xml:ns:caldav'  => 'C',
            'http://calendarserver.org/ns/'  => 'CS',
        ];

        $elements = [];
        foreach( $this->properties as $val ) {
            $elements[] = [  $val => "" ];
        }
        return $service->write('{DAV:}propfind',
            [
                '{DAV:}prop' => $elements
            ]);

    }
}