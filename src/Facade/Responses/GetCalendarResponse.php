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

    /**
     * Get current user's privilege set for this calendar
     *
     * @return array|null Array of privilege names like ['read', 'write'] or null
     */
    public function getCurrentUserPrivileges()
    {
        if (!isset($this->found_props['current-user-privilege-set'])) {
            return null;
        }

        $privilegeSet = $this->found_props['current-user-privilege-set'];
        $privileges = [];

        // The property contains nested privilege elements
        // Each privilege element contains a privilege name like 'write', 'read', etc.
        if (is_array($privilegeSet)) {
            foreach ($privilegeSet as $privilege) {
                if (is_array($privilege) && isset($privilege['privilege'])) {
                    // Privilege can be array of privilege names or single value
                    if (is_array($privilege['privilege'])) {
                        $privileges = array_merge($privileges, array_keys($privilege['privilege']));
                    } else {
                        $privileges[] = $privilege['privilege'];
                    }
                }
            }
        }

        return empty($privileges) ? null : $privileges;
    }

    /**
     * Check if user has write permission to this calendar
     *
     * @return bool|null True if writable, false if read-only, null if unknown
     */
    public function isWritable()
    {
        $privileges = $this->getCurrentUserPrivileges();

        if ($privileges === null) {
            // If privileges not provided, assume writable (conservative default)
            return null;
        }

        // Check for write-related privileges
        // DAV spec defines: write, write-content, write-properties, bind, unbind
        $writePrivileges = ['write', 'write-content', 'bind'];

        foreach ($writePrivileges as $writePriv) {
            if (in_array($writePriv, $privileges)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can edit this calendar
     * Convenience method that wraps isWritable() with null handling
     * Returns true if writable or unknown (null), false if explicitly read-only
     *
     * @return bool
     */
    public function canEdit()
    {
        $isWritable = $this->isWritable();
        // Handle null (server doesn't expose privileges) as true
        // This maintains backward compatibility with CalDAV servers that don't provide privilege info
        return $isWritable !== false;
    }

    /**
     * Parse VALARM components from a VEVENT
     * Supports RFC 5545 trigger formats:
     * - Duration: "-PT15M", "PT0S", "-P1D", "-PT1H30M"
     * - Absolute: "19760401T005545Z" with VALUE=DATE-TIME
     *
     * @param \Sabre\VObject\Component\VEvent $vevent
     * @return array Array of alarms with minutesBefore and isDefault fields
     */
    public static function parseVAlarms($vevent)
    {
        $alarms = [];

        if (!isset($vevent->VALARM)) {
            return $alarms;
        }

        foreach ($vevent->VALARM as $valarm) {
            if (!isset($valarm->TRIGGER)) {
                continue;
            }

            $trigger = (string)$valarm->TRIGGER;
            $valueParam = isset($valarm->TRIGGER['VALUE']) ? (string)$valarm->TRIGGER['VALUE'] : null;
            $minutes = null;

            // Case 1: Absolute trigger (VALUE=DATE-TIME)
            if ($valueParam === 'DATE-TIME') {
                try {
                    $triggerTime = new \DateTime($trigger, new \DateTimeZone('UTC'));
                    $eventStartTime = new \DateTime($vevent->DTSTART->getValue(), new \DateTimeZone('UTC'));
                    $diff = $eventStartTime->getTimestamp() - $triggerTime->getTimestamp();
                    $minutes = (int)($diff / 60);
                    if ($minutes < 0) {
                        $minutes = 0;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            // Case 2: Duration-based trigger
            // RFC 5545 duration: "-PT15M", "PT0S", "-P1DT12H", "-PT1H30M"
            else if (preg_match('/^-?P(?:(\d+)W)?(?:(\d+)D)?(?:T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?)?$/', $trigger, $matches)) {
                $weeks = isset($matches[1]) && $matches[1] !== '' ? (int)$matches[1] : 0;
                $days = isset($matches[2]) && $matches[2] !== '' ? (int)$matches[2] : 0;
                $hours = isset($matches[3]) && $matches[3] !== '' ? (int)$matches[3] : 0;
                $mins = isset($matches[4]) && $matches[4] !== '' ? (int)$matches[4] : 0;
                $secs = isset($matches[5]) && $matches[5] !== '' ? (int)$matches[5] : 0;

                $minutes = ($weeks * 7 * 24 * 60) + ($days * 24 * 60) + ($hours * 60) + $mins + (int)ceil($secs / 60);
            }

            if ($minutes !== null) {
                $alarm = ['minutesBefore' => $minutes];

                // Check for X-APPLE-DEFAULT-ALARM property
                if (isset($valarm->{'X-APPLE-DEFAULT-ALARM'}) && (string)$valarm->{'X-APPLE-DEFAULT-ALARM'} === 'TRUE') {
                    $alarm['isDefault'] = true;
                }

                $alarms[] = $alarm;
            }
        }

        return $alarms;
    }
}
