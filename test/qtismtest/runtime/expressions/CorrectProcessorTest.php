<?php

namespace qtismtest\runtime\expressions;

use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\expressions\CorrectProcessor;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class CorrectProcessorTest
 */
class CorrectProcessorTest extends QtiSmTestCase
{
    public function testMultipleCardinality(): void
    {
        $responseDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="directedPair" cardinality="multiple">
				<correctResponse>
					<value>A B</value>
					<value>C D</value>
				</correctResponse>
			</responseDeclaration>		
		');
        $expr = $this->createComponentFromXml('<correct identifier="response1"/>');

        $processor = new CorrectProcessor($expr);
        $variable = ResponseVariable::createFromDataModel($responseDeclaration);
        $processor->setState(new State([$variable]));

        $comparable = new MultipleContainer(BaseType::DIRECTED_PAIR);
        $comparable[] = new QtiDirectedPair('A', 'B');
        $comparable[] = new QtiDirectedPair('C', 'D');

        $result = $processor->process();
        $this::assertInstanceOf(MultipleContainer::class, $result);
        $this::assertTrue($result->equals($comparable));
        $this::assertTrue($comparable->equals($result));
    }

    public function testSingleCardinality(): void
    {
        $responseDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="integer" cardinality="single">
				<correctResponse interpretation="A single value!">
					<value>20</value>
				</correctResponse>
			</responseDeclaration>
		');
        $expr = $this->createComponentFromXml('<correct identifier="response1"/>');
        $variable = ResponseVariable::createFromDataModel($responseDeclaration);

        $processor = new CorrectProcessor($expr);
        $processor->setState(new State([$variable]));

        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(20, $result->getValue());
    }

    public function testNull(): void
    {
        $responseDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="integer" cardinality="single"/>
		');

        $expr = $this->createComponentFromXml('<correct identifier="response1"/>');
        $variable = ResponseVariable::createFromDataModel($responseDeclaration);

        $processor = new CorrectProcessor($expr);
        $result = $processor->process(); // No state set.
        $this::assertNull($result);

        $processor->setState(new State([$variable]));
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testException(): void
    {
        $variableDeclaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="outcome1" baseType="integer" cardinality="single"/>
		');
        $variable = OutcomeVariable::createFromDataModel($variableDeclaration);
        $expr = $this->createComponentFromXml('<correct identifier="outcome1"/>');

        $processor = new CorrectProcessor($expr);
        $processor->setState(new State([$variable]));
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }
}
