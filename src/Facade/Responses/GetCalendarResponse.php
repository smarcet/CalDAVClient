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

/**
 * Class GetCalendarResponse
 * @package CalDAVClient\Facade\Responses
 */
final class GetCalendarResponse extends ETagEntityResponse
{
    const ResourceTypeCalendar = 'calendar';
    /**
     * @return string
     */
    public function getDisplayName(){
        return isset($this->found_props['displayname']) ? $this->found_props['displayname'] : null;
    }

    public function getResourceType(){
        return isset($this->found_props['resourcetype']) ? $this->found_props['resourcetype'] : null;
    }

    /**
     * @see https://tools.ietf.org/html/rfc6578
     * @return string
     */
    public function getSyncToken(){
        return isset($this->found_props['sync-token']) ? $this->found_props['sync-token'] : null;
    }

    /**
     * @return string
     */
    public function getETag(){
        return isset($this->found_props['getetag']) ? $this->found_props['getetag'] : null;
    }

    /**
     * @return string
     */
    public function getCTag(){
        return isset($this->found_props['getctag']) ? $this->found_props['getctag'] : null;
    }

    /**
     * @return string
     */
    public function getCalendarColor(){
        return isset($this->found_props['calendar-color']) ? $this->found_props['calendar-color'] : null;
    }

    /**
     * Get supported calendar component types (VEVENT, VTODO, VJOURNAL, etc.)
     * Returns array of component names that this calendar supports
     *
     * @return array|null Array of component names like ['VEVENT', 'VTODO'] or null
     */
    public function getSupportedComponents()
    {
        if (!isset($this->found_props['supported-calendar-component-set'])) {
            return null;
        }

        $componentSet = $this->found_props['supported-calendar-component-set'];
        $components = [];

        // Handle single component (most common case)
        // Structure: {"comp": {"@attributes": {"name": "VEVENT"}}}
        if (isset($componentSet['comp']['@attributes']['name'])) {
            $components[] = $componentSet['comp']['@attributes']['name'];
        }
        // Handle array of components (less common)
        // Structure: [{"@attributes": {"name": "VEVENT"}}, {"@attributes": {"name": "VTODO"}}]
        elseif (is_array($componentSet)) {
            foreach ($componentSet as $key => $comp) {
                // Check for @attributes.name pattern
                if (isset($comp['@attributes']['name'])) {
                    $components[] = $comp['@attributes']['name'];
                }
                // Also check direct name property as fallback
                elseif (isset($comp['name'])) {
                    $components[] = $comp['name'];
                }
            }
        }

        return empty($components) ? null : $components;
    }

    /**
     * Check if calendar supports VEVENT components (calendar events)
     *
     * @return bool
     */
    public function supportsEvents()
    {
        $components = $this->getSupportedComponents();
        return $components !== null && in_array('VEVENT', $components);
    }
}
