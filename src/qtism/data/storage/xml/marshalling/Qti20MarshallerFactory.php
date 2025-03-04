<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\common\utils\Reflection;
use ReflectionClass;

/**
 * A MarshallerFactory focusing on instantiating and configuring
 * Marshallers for QTI 2.0.
 */
class Qti20MarshallerFactory extends MarshallerFactory
{
    /**
     * Create a new Qti20MarshallerFactory object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->removeMappingEntry('mediaInteraction');
        $this->removeMappingEntry('infoControl');
        $this->removeMappingEntry('interpolationTable');
        $this->removeMappingEntry('interpolationTableEntry');
        $this->removeMappingEntry('matchTable');
        $this->removeMappingEntry('matchTableEntry');
        $this->removeMappingEntry('assessmentTest');
        $this->removeMappingEntry('testPart');
        $this->removeMappingEntry('assessmentSection');
        $this->removeMappingEntry('assessmentSectionRef');
        $this->removeMappingEntry('assessmentItemRef');
        $this->removeMappingEntry('sectionPart');
        $this->removeMappingEntry('selection');
        $this->removeMappingEntry('ordering');
        $this->removeMappingEntry('weight');
        $this->removeMappingEntry('variableMapping');
        $this->removeMappingEntry('templateDefault');
        $this->removeMappingEntry('timeLimits');
        $this->removeMappingEntry('testFeedback');
        $this->removeMappingEntry('branchRule');
        $this->removeMappingEntry('preCondition');
        $this->removeMappingEntry('outcomeProcessing');
        $this->removeMappingEntry('outcomeCondition');
        $this->removeMappingEntry('outcomeIf');
        $this->removeMappingEntry('outcomeElseIf');
        $this->removeMappingEntry('outcomeElse');
        $this->removeMappingEntry('exitTest');
        $this->removeMappingEntry('itemSubset');
        $this->removeMappingEntry('testVariables');
        $this->removeMappingEntry('outcomeMaximum');
        $this->removeMappingEntry('outcomeMinimum');
        $this->removeMappingEntry('numberCorrect');
        $this->removeMappingEntry('numberIncorrect');
        $this->removeMappingEntry('numberResponded');
        $this->removeMappingEntry('numberPresented');
        $this->removeMappingEntry('numberSelected');
        $this->removeMappingEntry('repeat');
        $this->removeMappingEntry('roundTo');
        $this->removeMappingEntry('mathOperator');
        $this->removeMappingEntry('mathConstant');
        $this->removeMappingEntry('min');
        $this->removeMappingEntry('max');
        $this->removeMappingEntry('statsOperator');
        $this->removeMappingEntry('gcd');
        $this->removeMappingEntry('lcd');
        $this->removeMappingEntry('templateConstraint');
    }

    /**
     * @param ReflectionClass $class
     * @param array $args
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    protected function instantiateMarshaller(ReflectionClass $class, array $args)
    {
        array_unshift($args, '2.0.0');
        return Reflection::newInstance($class, $args);
    }
}
