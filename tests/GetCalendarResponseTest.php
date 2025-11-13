<?php
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

use CalDAVClient\Facade\Responses\GetCalendarResponse;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class GetCalendarResponseTest
 */
final class GetCalendarResponseTest extends TestCase
{
    private function setFoundProps($response, $props)
    {
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('found_props');
        $property->setAccessible(true);
        $property->setValue($response, $props);
    }

    public function testCanEditReturnsTrueWhenWritable()
    {
        $response = new GetCalendarResponse();
        $this->setFoundProps($response, [
            'current-user-privilege-set' => [
                ['privilege' => ['write' => []]]
            ]
        ]);

        $this->assertTrue($response->canEdit());
    }

    public function testCanEditReturnsTrueWhenPrivilegesUnknown()
    {
        $response = new GetCalendarResponse();
        $this->setFoundProps($response, []);

        $this->assertTrue($response->canEdit());
    }

    public function testCanEditReturnsFalseWhenReadOnly()
    {
        $response = new GetCalendarResponse();
        $this->setFoundProps($response, [
            'current-user-privilege-set' => [
                ['privilege' => ['read' => []]]
            ]
        ]);

        $this->assertFalse($response->canEdit());
    }
}
