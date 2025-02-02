<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\MediaInteraction;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class MediaInteractionMarshallerTest
 */
class MediaInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall(): void
    {
        $object = new ObjectElement('my-video.mp4', 'video/mp4');
        $object->setWidth('400');
        $object->setHeight('300');

        $mediaInteraction = new MediaInteraction('RESPONSE', false, $object, 'my-media');
        $mediaInteraction->setMinPlays(1);
        $mediaInteraction->setMaxPlays(2);
        $mediaInteraction->setLoop(true);
        $mediaInteraction->setXmlBase('/home/jerome');

        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection([new TextRun('Prompt...')]));
        $mediaInteraction->setPrompt($prompt);

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($mediaInteraction)->marshall($mediaInteraction);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this::assertEquals(
            '<mediaInteraction id="my-media" responseIdentifier="RESPONSE" autostart="false" minPlays="1" maxPlays="2" loop="true" xml:base="/home/jerome"><prompt>Prompt...</prompt><object data="my-video.mp4" type="video/mp4" width="400" height="300"/></mediaInteraction>',
            $dom->saveXML($element)
        );
    }

    public function testUnmarshall(): void
    {
        $element = $this->createDOMElement(
            '<mediaInteraction id="my-media" responseIdentifier="RESPONSE" autostart="false" minPlays="1" maxPlays="2" loop="true" xml:base="/home/jerome">
                <prompt>Prompt...</prompt>
                <object data="my-video.mp4" type="video/mp4" width="400" height="300"/>
            </mediaInteraction>'
        );

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this::assertInstanceOf(MediaInteraction::class, $component);
        $this::assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this::assertEquals('my-media', $component->getId());
        $this::assertFalse($component->mustAutostart());
        $this::assertEquals(1, $component->getMinPlays());
        $this::assertTrue($component->mustLoop());
        $this::assertEquals('/home/jerome', $component->getXmlBase());

        $object = $component->getObject();
        $this::assertEquals('my-video.mp4', $object->getData());
        $this::assertEquals('video/mp4', $object->getType());
        $this::assertEquals(400, $object->getWidth());
        $this::assertEquals(300, $object->getHeight());

        $this::assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        $this::assertEquals('Prompt...', $promptContent[0]->getContent());
    }

    public function testUnmarshallNoObject(): void
    {
        $element = $this->createDOMElement('
            <mediaInteraction id="my-media" responseIdentifier="RESPONSE" autostart="false" minPlays="1" maxPlays="2" loop="true" xml:base="/home/jerome"><prompt>Prompt...</prompt></mediaInteraction>        
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("A 'mediaInteraction' element must contain exactly one 'object' element, none given.");

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallMissingAutoStart(): void
    {
        $element = $this->createDOMElement('
            <mediaInteraction id="my-media" responseIdentifier="RESPONSE" minPlays="1" maxPlays="2" loop="true" xml:base="/home/jerome"><prompt>Prompt...</prompt><object data="my-video.mp4" type="video/mp4" width="400" height="300"/></mediaInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'autostart' attribute is missing from the 'mediaInteraction' element.");

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }

    public function testUnmarshallMissingResponseIdentifier(): void
    {
        $element = $this->createDOMElement('
            <mediaInteraction id="my-media" autostart="true" minPlays="1" maxPlays="2" loop="true" xml:base="/home/jerome"><prompt>Prompt...</prompt><object data="my-video.mp4" type="video/mp4" width="400" height="300"/></mediaInteraction>
        ');

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory 'responseIdentifier' attribute is missing from the 'mediaInteraction' element.");

        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
}
