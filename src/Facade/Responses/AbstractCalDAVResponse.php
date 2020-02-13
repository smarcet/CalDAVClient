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
use CalDAVClient\Facade\Exceptions\XMLResponseParseException;
/**
 * Class AbstractCalDAVResponse
 * @package CalDAVClient\Facade\Responses
 */
abstract class AbstractCalDAVResponse extends HttpResponse
{

    /**
     * @var string
     */
    protected $server_url;

    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    /**
     * @var array
     */
    protected $content;

    private $stripped;

    /**
     * AbstractCalDAVResponse constructor.
     * @param string|null $server_url
     * @param string|null $body
     * @param int $code
     */
    public function __construct($server_url = null, $body = null, $code = HttpResponse::HttpCodeOk)
    {
        parent::__construct($body, $code);
        $this->server_url = $server_url;
        if (!empty($this->body)) {
            $this->stripped = $this->stripNamespacesFromTags($this->body);
            // Merge CDATA as text nodes
            $this->xml = simplexml_load_string($this->stripped, null, LIBXML_NOCDATA);
            if ($this->xml === FALSE)
                throw new XMLResponseParseException();
            $this->content = $this->toAssocArray($this->xml);

            $this->parse();
        }
    }

    public function __destruct()
    {
    }

    protected function setContent($content) {
        $this->content = $content;
    }

    abstract protected function parse();

    /**
     * Strip namespaces from the XML, because otherwise we can't always properly convert
     * the XML to an associative JSON array, and some CalDAV servers (such as SabreDAV)
     * return only namespaced XML.
     *
     * @param $xml
     * @return string
     */
    private function stripNamespacesFromTags($xml) {
        // `simplexml_load_string` treats namespaced XML differently than non-namespaced XML, and
        // calling `json_encode` on the results of a parsed namespaced XML string will only
        // include the non-namespaced tags. Therefore, we remove the namespaces.
        //
        // Almost literally taken from
        // https://laracasts.com/discuss/channels/general-discussion/converting-xml-to-jsonarray/replies/112561


        // We retrieve the namespaces from the XML code so we can check for
        // them to remove them
        $obj = simplexml_load_string($xml);
        $namespaces = $obj->getNamespaces(true);
        $toRemove = array_keys($namespaces);

        // This is part of a regex I will use to remove the namespace declaration from string
        $nameSpaceDefRegEx = '(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?';

        // Cycle through each namespace and remove it from the XML string
        foreach ($toRemove as $remove) {
            // First remove the namespace from the opening of the tag
            $xml = str_replace('<'.$remove.':', '<', $xml);
            // Now remove the namespace from the closing of the tag
            $xml = str_replace('</'.$remove.':', '</', $xml);
            // This XML uses the name space with CommentText, so remove that too
            $xml = str_replace($remove.':commentText', 'commentText', $xml);
            // Complete the pattern for RegEx to remove this namespace declaration
            $pattern = "/xmlns:{$remove}{$nameSpaceDefRegEx}/";
            // Remove the actual namespace declaration using the Pattern
            $xml = preg_replace($pattern, '', $xml, 1);
        }

        // Return sanitized and cleaned up XML with no namespaces
        return $xml;
    }

    /**
     * @param $xml
     * @return array
     */
    protected function toAssocArray($xml) {
        $string = json_encode($xml);
        $array  = json_decode($string, true);
        return $array;
    }

    /**
     * @return bool
     */
    protected function isValid() {
        return isset($this->content['response']);
    }
}