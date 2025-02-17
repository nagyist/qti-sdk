<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\expressions\operators;

use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiScalar;
use qtism\data\expressions\operators\Gcd;

/**
 * The GcdProcessor class aims at processing Gcd operators.
 *
 * From IMS QTI:
 *
 * The gcd operator takes 1 or more sub-expressions which all have base-type
 * integer and may have single, multiple or ordered cardinality. The result is a
 * single integer equal in value to the greatest common divisor (gcd) of the
 * argument values. If all the arguments are zero, the result is 0, gcd(0,0)=0;
 * authors should beware of this in calculations which require division by the
 * gcd of random values. If some, but not all, of the arguments are zero, the
 * result is the gcd of the non-zero arguments, gcd(0,n)=n if n<>0. If any of
 * the sub-expressions is NULL, the result is NULL. If any of the sub-expressions
 * is not a numerical value, then the result is NULL.
 */
class GcdProcessor extends OperatorProcessor
{
    /**
     * Process the Gcd operator.
     *
     * @return QtiInteger|null The integer value equal in value to the greatest common divisor of the sub-expressions. If any of the sub-expressions is NULL, the result is NULL.
     * @throws OperatorProcessingException
     */
    public function process(): ?QtiInteger
    {
        $operands = $this->getOperands();

        if ($operands->containsNull() === true) {
            return null;
        }

        if ($operands->anythingButRecord() === false) {
            $msg = 'The Gcd operator only accepts operands with a cardinality of single, multiple or ordered.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_CARDINALITY);
        }

        if ($operands->exclusivelyInteger() === false) {
            $msg = 'The Gcd operator only accepts operands with an integer baseType.';
            throw new OperatorProcessingException($msg, $this, OperatorProcessingException::WRONG_BASETYPE);
        }

        // Make a flat collection first.
        $flatCollection = new OperandsCollection();
        $zeroCount = 0;
        $valueCount = 0;
        foreach ($operands as $operand) {
            if ($operand instanceof QtiScalar) {
                $valueCount++;

                if ($operand->getValue() !== 0) {
                    $flatCollection[] = $operand;
                } else {
                    $zeroCount++;
                }
            } elseif ($operand->contains(null)) {
                // Container with at least one null value inside.
                // -> If any of the sub-expressions is null or not numeric, returns null.
                return null;
            } else {
                // Container with no null values.
                foreach ($operand as $o) {
                    $valueCount++;

                    if ($o->getValue() !== 0) {
                        $flatCollection[] = $o;
                    } else {
                        $zeroCount++;
                    }
                }
            }
        }

        if ($zeroCount === $valueCount) {
            // All arguments of gcd() are 0.
            return new QtiInteger(0);
        } else {
            $g = $flatCollection[0];
            $loopLimit = count($flatCollection) - 1;
            $i = 0;

            while ($i < $loopLimit) {
                $g = new QtiInteger(Utils::gcd($g->getValue(), $flatCollection[$i + 1]->getValue()));
                $i++;
            }

            return $g;
        }
    }

    /**
     * @return string
     */
    protected function getExpressionType(): string
    {
        return Gcd::class;
    }
}
