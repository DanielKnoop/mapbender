<?php


namespace Mapbender\WmtsBundle\Component;


abstract class AbstractTileServiceParser
{
    /** @var \DOMXPath */
    protected $xpath;

    public function __construct(\DOMXPath $xpath)
    {
        $this->xpath = $xpath;
    }

    protected function getValue($expression, \DOMNode $startNode = null)
    {
        try {
            $elm = $this->xpath->query($expression, $startNode)->item(0);
            if (!$elm) {
                return null;
            }
            if ($elm->nodeType == XML_ATTRIBUTE_NODE) {
                /** @var \DOMAttr $elm */
                return $elm->value;
            } elseif ($elm->nodeType == XML_TEXT_NODE) {
                /** @var \DOMText $elm */
                return $elm->wholeText;
            } elseif ($elm->nodeType == XML_ELEMENT_NODE) {
                return $elm;
            } else {
                return null;
            }
        } catch (\Exception $E) {
            return null;
        }
    }
}
