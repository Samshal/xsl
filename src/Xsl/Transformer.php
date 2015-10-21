<?php
namespace Genkgo\Xsl\Xsl;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Genkgo\Xsl\Schema\XmlSchema;
use Genkgo\Xsl\TransformationContext;
use Genkgo\Xsl\TransformerInterface;
use Genkgo\Xsl\Xpath\Compiler;
use Genkgo\Xsl\Xsl\Node\AttributeBraces;
use Genkgo\Xsl\Xsl\Node\AttributeMatch;
use Genkgo\Xsl\Xsl\Node\AttributeSelect;
use Genkgo\Xsl\Xsl\Node\AttributeTest;
use Genkgo\Xsl\Xsl\Node\ElementForEachGroup;
use Genkgo\Xsl\Xsl\Node\ElementValueOf;

/**
 * Class Transformer
 * @package Genkgo\Xsl\Xsl
 */
class Transformer implements TransformerInterface
{
    /**
     * @var ElementTransformerInterface[]
     */
    private $elementTransformers = [];

    /**
     * @var AttributeTransformerInterface[]
     */
    private $attributeTransformers = [];

    /**
     * Transformer constructor.
     * @param Compiler $xpathCompiler
     */
    public function __construct(Compiler $xpathCompiler)
    {
        $this->elementTransformers = [
            new AttributeMatch($xpathCompiler),
            new AttributeSelect($xpathCompiler),
            new AttributeTest($xpathCompiler),
            new ElementValueOf(),
            new ElementForEachGroup($xpathCompiler),
        ];

        $this->attributeTransformers = [
            new AttributeBraces($xpathCompiler)
        ];
    }


    /**
     * @param DOMDocument $document
     */
    public function transform(DOMDocument $document)
    {
        $root = $document->documentElement;

        $root->setAttribute('xmlns:php', 'http://php.net/xsl');
        $root->setAttribute('xmlns:xs', XmlSchema::URI);
        $root->setAttribute('exclude-result-prefixes', 'php xs');

        $this->transformElements($document);
        $this->transformAttributes($document);
    }

    /**
     * @param DOMDocument $document
     */
    private function transformElements (DOMDocument $document) {
        $matchAndSelectElements = new DOMXPath($document);
        /** @var DOMNodeList|DOMElement[] $list */
        $list = $matchAndSelectElements->query('//xsl:*[@match|@select]');
        foreach ($list as $element) {
            foreach ($this->elementTransformers as $elementTransformer) {
                if ($elementTransformer->supports($document)) {
                    $elementTransformer->transform($element);
                }
            }
        }
    }

    /**
     * @param DOMDocument $document
     */
    private function transformAttributes (DOMDocument $document) {
        $matchAndSelectElements = new DOMXPath($document);
        /** @var DOMNodeList|DOMElement[] $list */
        $expression = '//@*[substring(., 1, 1) = "{" and substring(., 1, 2) != "{{" and substring(., string-length(.)) = "}"]';

        $list = $matchAndSelectElements->query($expression);
        foreach ($list as $attribute) {
            foreach ($this->attributeTransformers as $attributeTransformer) {
                if ($attributeTransformer->supports($document)) {
                    $attributeTransformer->transform($attribute);
                }
            }
        }
    }
}
