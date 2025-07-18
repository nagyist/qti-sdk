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

namespace qtism\runtime\rendering\markup\xhtml;

use DOMDocumentFragment;
use qtism\data\content\interactions\Orientation;
use qtism\data\QtiComponent;
use qtism\data\ShufflableCollection;
use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;

/**
 * ChoiceInteraction renderer. Rendered components will be transformed as
 * 'div' elements with 'qti-choiceInteraction' and 'qti-blockInteraction' additional CSS class.
 *
 * The following data-X attributes will be rendered:
 *
 * * data-responseIdentifier = qti:interaction->responseIdentifier
 * * data-shuffle = qti:choiceInteraction->shuffle
 * * data-max-choices = qti:choiceInteraction->maxChoices
 * * data-min-choices = qti:choiceInteraction->minChoices
 * * data-orientation = qti:choiceInteraction->orientation
 */
class ChoiceInteractionRenderer extends InteractionRenderer
{
    /**
     * Create a new ChoiceInteractionRenderer object.
     *
     * @param AbstractMarkupRenderingEngine $renderingEngine
     */
    public function __construct(?AbstractMarkupRenderingEngine $renderingEngine = null)
    {
        parent::__construct($renderingEngine);
        $this->transform('div');
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     */
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component, $base = ''): void
    {
        parent::appendAttributes($fragment, $component, $base);
        $this->additionalClass('qti-blockInteraction');
        $this->additionalClass('qti-choiceInteraction');
        $this->additionalUserClass(($component->getOrientation() === Orientation::VERTICAL) ? 'qti-vertical' : 'qti-horizontal');

        $qtiCount = count($component->getSimpleChoices());
        $qtiCount = ($qtiCount % 2 === 1) ? $qtiCount + 1 : $qtiCount;
        $this->additionalUserClass("qti-count-{$qtiCount}");

        $fragment->firstChild->setAttribute('data-shuffle', ($component->mustShuffle() === true) ? 'true' : 'false');
        $fragment->firstChild->setAttribute('data-max-choices', (string)$component->getMaxChoices());
        $fragment->firstChild->setAttribute('data-min-choices', (string)$component->getMinChoices());
        $fragment->firstChild->setAttribute('data-orientation', ($component->getOrientation() === Orientation::VERTICAL) ? 'vertical' : 'horizontal');
    }

    /**
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     * @param string $base
     */
    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component, $base = ''): void
    {
        parent::appendChildren($fragment, $component, $base);

        if ($this->getRenderingEngine()->getShufflingPolicy() === AbstractMarkupRenderingEngine::CONTEXT_AWARE && $component->mustShuffle() === true) {
            Utils::shuffle($fragment->firstChild, new ShufflableCollection($component->getSimpleChoices()->getArrayCopy()));
        }

        // Put the choice elements into an unordered list.
        // Dev note: it seems we need a trick ... http://php.net/manual/en/domnode.removechild.php#90292
        $choiceElts = $fragment->firstChild->getElementsByTagName('li');
        $choiceQueue = [];
        $ulElt = $fragment->ownerDocument->createElement('ul');

        foreach ($choiceElts as $choiceElt) {
            $choiceQueue[] = $choiceElt;
        }

        foreach ($choiceQueue as $choiceElt) {
            $ifStatements = Utils::extractStatements($choiceElt, Utils::EXTRACT_IF);
            $incStatements = Utils::extractStatements($choiceElt, Utils::EXTRACT_INCLUDE);

            $fragment->firstChild->removeChild($choiceElt);
            $ulElt->appendChild($choiceElt);

            // Re-append qtism-include/qtism-endinclude.
            $statements = Utils::extractStatements($choiceElt, Utils::EXTRACT_INCLUDE);
            if (empty($incStatements) === false) {
                $choiceElt->parentNode->insertBefore($incStatements[0], $choiceElt);
                $choiceElt->parentNode->insertBefore($incStatements[1], $choiceElt->nextSibling);
            }

            // Re-append qtism-if/qtism-endif.
            if (empty($ifStatements) === false) {
                $choiceElt->parentNode->insertBefore($ifStatements[0], $choiceElt);
                $choiceElt->parentNode->insertBefore($ifStatements[1], $choiceElt->nextSibling);
            }
        }

        $fragment->firstChild->appendChild($ulElt);
    }
}
