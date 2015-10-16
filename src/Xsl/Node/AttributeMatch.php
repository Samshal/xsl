<?php
namespace Genkgo\Xsl\Xsl\Node;

use DOMElement;
use Genkgo\Xsl\Context;
use Genkgo\Xsl\Xpath\Compiler;
use Genkgo\Xsl\Xsl\ElementTransformerInterface;

/**
 * Class AttributeMatch
 * @package Genkgo\Xsl\Xsl\Element
 */
class AttributeMatch implements ElementTransformerInterface {

    /**
     * @var Compiler
     */
    private $xpathCompiler;

    /**
     * @param Compiler $compiler
     */
    public function __construct (Compiler $compiler) {
        $this->xpathCompiler = $compiler;
    }

    /**
     * @param DOMElement $element
     * @param Context $context
     */
    public function transform(DOMElement $element, Context $context)
    {
        if ($element->hasAttribute('match')) {
            $element->setAttribute(
                'match',
                $this->xpathCompiler->compile(
                    $element->getAttribute('match'),
                    $context
                )
            );
        }
    }

}