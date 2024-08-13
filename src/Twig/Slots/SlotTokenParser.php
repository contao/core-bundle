<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Twig\Slots;

use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\Filter\RawFilter;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\GetAttrExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Twig\Node\PrintNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * @experimental
 */
final class SlotTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();

        // Parse opening tag: {% slot %}
        $nameToken = $stream->expect(Token::NAME_TYPE, null, '');
        $stream->expect(Token::BLOCK_END_TYPE);

        // Parse body: we inject a macro symbol with a marker expression to support the
        // virtual slot() function when parsing the following tokens. Then the marker
        // expression gets replaced again with a SlotContentNode that outputs the slot
        // content at runtime.
        $markerExpression = new ConstantExpression('', 0);
        $this->parser->addImportedSymbol('function', 'slot', '', $markerExpression);
        $body = $this->parser->subparse($this->decideForFork(...));

        if ($body->count()) {
            $this->traverseAndReplaceMarkerExpression($markerExpression, $nameToken->getValue(), $body);
        } else {
            $line = $stream->getCurrent()->getLine();
            $body->setNode('body', new PrintNode($this->getSlotReferenceExpression($nameToken->getValue(), $line), $line));
        }

        // Parse optional {% else %} tag with fallback content
        if ('else' === $stream->next()->getValue()) {
            $stream->expect(Token::BLOCK_END_TYPE);
            $fallback = $this->parser->subparse($this->decideAddEnd(...), true);
        } else {
            $fallback = null;
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return new SlotNode($nameToken->getValue(), $body, $fallback, $token->getLine());
    }

    public function decideForFork(Token $token): bool
    {
        return $token->test(['else', 'endslot']);
    }

    public function decideAddEnd(Token $token): bool
    {
        return $token->test('endslot');
    }

    public function getTag(): string
    {
        return 'slot';
    }

    private function traverseAndReplaceMarkerExpression(AbstractExpression $markerExpression, string $name, Node $node, array $parents = []): void
    {
        if ($node === $markerExpression) {
            /** @var Node $target */
            $target = $parents[1];

            foreach (array_keys(iterator_to_array($target)) as $key) {
                $target->removeNode((string) $key);
            }

            $target->setNode('expr', $this->getSlotReferenceExpression($name, $target->getTemplateLine()));

            return;
        }

        foreach ($node as $child) {
            $this->traverseAndReplaceMarkerExpression($markerExpression, $name, $child, [$node, ...$parents]);
        }
    }

    /**
     * Builds an expression that is equivalent to "_slots.<name>|raw".
     */
    private function getSlotReferenceExpression(string $name, int $line): AbstractExpression
    {
        $node = new GetAttrExpression(
            new NameExpression('_slots', $line),
            new ConstantExpression($name, $line),
            null,
            'array',
            $line,
        );

        if (class_exists(RawFilter::class)) {
            return new RawFilter($node);
        }

        return new FilterExpression(
            $node,
            new ConstantExpression('raw', $line),
            new Node(),
            $line,
        );
    }
}
