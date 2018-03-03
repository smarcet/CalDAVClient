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
 * Class CalendarSyncInfoResponse
 * @package CalDAVClient\Facade\Responses
 */
final class CalendarSyncInfoResponse extends ResourceCollectionResponse
{
    /**
     * @return string|null
     */
    public function getSyncToken(){
        return isset($this->content['sync-token'])? $this->content['sync-token'] : null;
    }

    /**
     * @return bool
     */
    public function hasAvailableChanges(){
        return count($this->responses) > 0;
    }

    /**
     * @return ETagEntityResponse[]
     */
    public function getUpdates(){
        $res = [];
        foreach ($this->responses as $entity){
            if($entity instanceof ETagEntityResponse && $entity->getStatus() != HttpResponse::HttpOKStatus) continue;
                $res[] = $entity;
        }
        return $res;
    }

    /**
     * @return ETagEntityResponse[]
     */
    public function getDeletes(){
        $res = [];
        foreach ($this->responses as $entity){
            if($entity instanceof ETagEntityResponse && $entity->getStatus() != HttpResponse::HttpNotFoundStatus) continue;
                $res[] = $entity;
        }
        return $res;
    }

}