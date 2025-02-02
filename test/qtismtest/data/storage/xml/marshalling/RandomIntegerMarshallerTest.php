<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\expressions\RandomInteger;
use qtismtest\QtiSmTestCase;

/**
 * Class RandomIntegerMarshallerTest
 */
class RandomIntegerMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $min = 3;
        $max = '{tplVariable1}';
        $step = 2;
        $component = new RandomInteger($min, $max, $step);
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals($min . '', $element->getAttribute('min'));
        $this::assertEquals($max, $element->getAttribute('max'));
        $this::assertEquals($step . '', $element->getAttribute('step'));
    }

    public function testUnmarshall(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<randomInteger xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" min="3" max="{tplVariable1}" step="2"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(RandomInteger::class, $component);
        $this::assertEquals(3, $component->getMin());
        $this::assertEquals('{tplVariable1}', $component->getMax());
        $this::assertEquals(2, $component->getStep());
    }
}
