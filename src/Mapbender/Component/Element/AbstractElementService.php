<?php


namespace Mapbender\Component\Element;


use Mapbender\CoreBundle\Component\ElementBase\EditableInterface;
use Mapbender\CoreBundle\Component\ElementBase\MinimalInterface;
use Mapbender\CoreBundle\Entity\Element;

abstract class AbstractElementService implements MinimalInterface, EditableInterface
{
    /**
     * @param Element $element
     * @return ElementView
     */
    abstract public function getView(Element $element);

    /**
     * @param Element $element
     * @return array
     */
    public function getClientConfiguration(Element $element)
    {
        return $element->getConfiguration() ?: array();
    }
}
