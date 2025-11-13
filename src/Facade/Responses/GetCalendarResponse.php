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
}
