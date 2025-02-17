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

namespace qtism\data\content\interactions;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\content\BodyElement;
use qtism\data\QtiIdentifiable;
use qtism\data\QtiIdentifiableTrait;
use qtism\data\ShowHide;
use qtism\data\Shufflable;
use SplObjectStorage;

/**
 * From IMS QTI:
 *
 * Many of the interactions involve choosing one or more predefined choices.
 */
abstract class Choice extends BodyElement implements QtiIdentifiable, Shufflable
{
    use QtiIdentifiableTrait;

    /**
     * From IMS QTI:
     *
     * The identifier of the choice. This identifier must not be used by
     * any other choice or item variable.
     *
     * @var string
     * @qtism-bean-property
     */
    private $identifier;

    /**
     * From IMS QTI:
     *
     * If fixed is true for a choice then the position of this choice within
     * the interaction must not be changed by the delivery engine even if the
     * immediately enclosing interaction supports the shuffling of choices. If
     * no value is specified then the choice is free to be shuffled.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $fixed = false;

    /**
     * From IMS QTI:
     *
     * The identifier of a template variable that must have a base-type of identifier
     * and be either single of multiple cardinality. When the associated interaction is
     * part of an Item Template the value of the identified template variable is used to
     * control the visibility of the choice. When a choice is hidden it is not selectable
     * and its content is not visible to the candidate unless otherwise stated.
     *
     * @var string
     * @qtism-bean-property
     */
    private $templateIdentifier = '';

    /**
     * From IMS QTI:
     *
     * The showHide attribute determins how the visbility of the choice is controlled. If set to
     * show then the choice is hidden by default and shown only if the associated template variable
     * matches, or contains, the identifier of the choice. If set to hide then the choice is shown
     * by default and hidden if the associated template variable matches, or contains, the choice's
     * identifier.
     *
     * @var int
     * @qtism-bean-property
     */
    private $showHide;

    /**
     * Create a new Choice object.
     *
     * @param string $identifier The identifier of the choice.
     * @param string $id The identifier of the body element.
     * @param string $class The class of the body element.
     * @param string $lang The language of the body element.
     * @param string $label The label of the body element.
     * @throws InvalidArgumentException
     */
    public function __construct($identifier, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($id, $class, $lang, $label);
        $this->setIdentifier($identifier);
        $this->setFixed(false);
        $this->setTemplateIdentifier('');
        $this->setShowHide(ShowHide::SHOW);
        $this->setObservers(new SplObjectStorage());
    }

    /**
     * Get the identifier of the choice.
     *
     * @return string A QTI identifier.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Set the identifier of the choice.
     *
     * @param string $identifier A QTI identifier.
     * @throws InvalidArgumentException If the given $identifier is not valid.
     */
    public function setIdentifier($identifier): void
    {
        if (Format::isIdentifier($identifier, false) === true) {
            $this->identifier = $identifier;
        } else {
            $msg = "The 'identifier' argument must be a valid QTI identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Set whether the choice is fixed.
     *
     * @param bool $fixed
     * @throws InvalidArgumentException If $fixed is not a boolean value.
     */
    public function setFixed($fixed): void
    {
        if (is_bool($fixed)) {
            $this->fixed = $fixed;
        } else {
            $msg = "The 'fixed' argument must be a boolean value, '" . gettype($fixed) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether the choice is fixed.
     *
     * @return bool
     */
    public function isFixed(): bool
    {
        return $this->fixed;
    }

    /**
     * Set the template identifier of the choice.
     *
     * @param string $templateIdentifier An empty string if no identifier is provided or a QTI identifier.
     * @throws InvalidArgumentException If the given $templateIdentifier is not a valid QTI identifier.
     */
    public function setTemplateIdentifier($templateIdentifier): void
    {
        if (is_string($templateIdentifier) && (empty($templateIdentifier)) || Format::isIdentifier((string)$templateIdentifier, false) === true) {
            $this->templateIdentifier = $templateIdentifier;
        } else {
            $msg = "The 'templateIdentifier' must be an empty string or a valid QTI identifier, '" . gettype($templateIdentifier) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the template identifier of the choice.
     *
     * @return string A QTI identifier or an empty string if no identifier provided.
     */
    public function getTemplateIdentifier(): string
    {
        return $this->templateIdentifier;
    }

    /**
     * Whether a value is defined for the templateIdentifier attribute.
     *
     * @return bool
     */
    public function hasTemplateIdentifier(): bool
    {
        return $this->getTemplateIdentifier() !== '';
    }

    /**
     * Set the visibility of the choice.
     *
     * @param int $showHide A value from the ShowHide enumeration.
     * @throws InvalidArgumentException If $showHide is not a value from the ShowHide enumeration.
     */
    public function setShowHide($showHide): void
    {
        if (in_array($showHide, ShowHide::asArray())) {
            $this->showHide = $showHide;
        } else {
            $msg = "The 'showHide' argument must be a value from the ShowHide enumeration.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the visibility of the choice.
     *
     * @return int A value from the ShowHide enumeration.
     */
    public function getShowHide(): int
    {
        return $this->showHide;
    }

    public function __clone()
    {
        $this->setObservers(new SplObjectStorage());
    }
}
