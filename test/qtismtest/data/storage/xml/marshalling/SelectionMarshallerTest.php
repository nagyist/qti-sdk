<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\rules\Selection;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class SelectionMarshallerTest
 */
class SelectionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $select = 2;
        $withReplacement = true;

        $component = new Selection($select);
        $component->setWithReplacement($withReplacement);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('selection', $element->nodeName);
        $this::assertSame($select . '', $element->getAttribute('select'));
        $this::assertEquals('true', $element->getAttribute('withReplacement'));

        $this::assertEquals(0, $element->childNodes->length);
    }

    public function testMarshallWithExternalData(): void
    {
        $select = 2;
        $withReplacement = true;
        $xmlString = '
            <selection xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" select="2" ><som:adaptiveItemSelection xmlns:som="http://www.my-namespace.com"/></selection>
        ';

        $component = new Selection($select, $withReplacement, $xmlString);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('selection', $element->nodeName);
        $this::assertSame($select . '', $element->getAttribute('select'));
        $this::assertEquals('true', $element->getAttribute('withReplacement'));

        $this::assertEquals('<selection select="2" withReplacement="true"><som:adaptiveItemSelection xmlns:som="http://www.my-namespace.com"/></selection>', $element->ownerDocument->saveXML($element));
    }

    public function testUnmarshallValid(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<selection xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" select="2" withReplacement="true"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(Selection::class, $component);
        $this::assertEquals(2, $component->getSelect());
        $this::assertTrue($component->isWithReplacement());
    }

    public function testUnmarshallValidTwo(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<selection xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" select="2"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(Selection::class, $component);
        $this::assertEquals(2, $component->getSelect());
        $this::assertFalse($component->isWithReplacement());
    }

    public function testUnmarshallValidWithExtension(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
            <selection xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" select="2" >
                <ais:adaptiveItemSelection xmlns:ais="http://www.taotesting.com/xsd/ais_v1p0p0">
                    <ais:adaptiveEngineRef identifier="engine" href="http://www.my-cat-engine/cat/api/"/>
                    <ais:adaptiveSettingsRef identifier="settings" href="settings.xml"/>
                    <ais:qtiUsagedataRef identifier="usagedata" href="usagedata.xml"/>
                    <ais:qtiMetadataRef identifier="metadata" href="metadata.xml"/>
                </ais:adaptiveItemSelection>
            </selection>
        ');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(Selection::class, $component);
        $this::assertEquals(2, $component->getSelect());
        $this::assertFalse($component->isWithReplacement());

        $this::assertEquals(1, $component->getXml()->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveItemSelection')->length);
        $this::assertEquals(1, $component->getXml()->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveEngineRef')->length);
        $this::assertEquals(1, $component->getXml()->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'qtiUsagedataRef')->length);
        $this::assertEquals(1, $component->getXml()->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'qtiMetadataRef')->length);
    }

    public function testUnmarshallInvalid(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        // the mandatory 'select' attribute is missing in the following test.
        $dom->loadXML('<selection xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" withReplacement="true"/>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->expectException(UnmarshallingException::class);
        $component = $marshaller->unmarshall($element);
    }
}
