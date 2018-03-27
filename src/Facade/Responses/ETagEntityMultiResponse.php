<?php namespace CalDAVClient\Facade\Responses;

class ETagEntityMultiResponse extends GenericMultiCalDAVResponse
{
    /**
     * @return ETagEntityResponse
     */
    protected function buildSingleResponse()
    {
        return new ETagEntityResponse();
    }
}