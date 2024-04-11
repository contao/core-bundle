<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\String;

/**
 * @implements \IteratorAggregate<string, string>
 * @implements \ArrayAccess<string, string|int|bool|\Stringable|null>
 */
class HtmlAttributes implements \Stringable, \JsonSerializable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @var array<string, string>
     */
    private array $attributes = [];

    private bool $doubleEncoding = false;

    /**
     * @param iterable<string, string|int|bool|\Stringable|null>|string|self|null $attributes
     */
    public function __construct(self|iterable|string|null $attributes = null)
    {
        $this->mergeWith($attributes);
    }

    /**
     * Outputs the attributes as a string that is safe to be placed inside HTML tags.
     * The output will contain a leading space if there is at least one property set,
     * e.g. ' foo="bar" bar="42"'.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Merges these instance's attributes with those of another instance/string/array
     * of attributes.
     *
     * If a falsy $condition is specified, the method is a no-op.
     *
     * @param iterable<string, string|int|bool|\Stringable|null>|string|self|null $attributes
     */
    public function mergeWith(self|iterable|string|null $attributes = null, mixed $condition = true): self
    {
        if (empty($attributes) || !$this->test($condition)) {
            return $this;
        }

        // Merge values if possible, set them otherwise
        $mergeSet = function (string $name, \Stringable|bool|int|string|null $value): void {
            if ('class' === $name) {
                $this->addClass($value);
            } elseif ('style' === $name) {
                $this->addStyle($value);
            } else {
                $this->set($name, $value);
            }
        };

        if (\is_string($attributes)) {
            foreach ($this->parseString($attributes) as $name => $value) {
                try {
                    $mergeSet($name, $value);
                } catch (\InvalidArgumentException) {
                    // Skip invalid attributes
                }
            }

            return $this;
        }

        foreach ($attributes as $name => $value) {
            $mergeSet($name, $value);
        }

        return $this;
    }

    /**
     * Sets a property and validates the name. If the given $value is false the
     * property will be unset instead. All values will be coerced to strings, whereby
     * null and true will result in an empty string.
     *
     * If a falsy $condition is specified, the method is a no-op.
     */
    public function set(string $name, \Stringable|bool|int|string|null $value = true, mixed $condition = true): self
    {
        if (!$this->test($condition)) {
            return $this;
        }

        $name = strtolower($name);

        // Even though the HTML 5 parser supports attribute names starting with an equal
        // sign, we have to disallow that in order to support serializing boolean
        // attributes. Otherwise, serializing and parsing back something like `hidden=""
        // =attr="value"` would fail the roundtrip.
        if (1 !== preg_match('(^[^>\s/=]+$)', $name) || 1 !== preg_match('//u', $name)) {
            throw new \InvalidArgumentException(sprintf('An HTML attribute name must be valid UTF-8 and not contain the characters >, /, = or whitespace, got "%s".', $name));
        }

        // Unset if value is set to false
        if (false === $value) {
            unset($this->attributes[$name]);

            return $this;
        }

        $this->attributes[$name] = true === $value ? '' : (string) $value;

        // Normalize class names and style attributes
        if ('class' === $name) {
            $this->addClass('');
        } elseif ('style' === $name) {
            $this->addStyle([]);
        }

        return $this;
    }

    /**
     * Set the property $name to $value if the value is truthy.
     */
    public function setIfExists(string $name, \Stringable|bool|int|string|null $value): self
    {
        if ($this->test($value)) {
            $this->set($name, $value);
        }

        return $this;
    }

    /**
     * Unset the property $name.
     *
     * If a falsy $condition is specified, the method is a no-op.
     */
    public function unset(string $name, mixed $condition = true): self
    {
        if (!$this->test($condition)) {
            return $this;
        }

        unset($this->attributes[strtolower($name)]);

        return $this;
    }

    /**
     * Add a single class ("foo") or multiple from a class string ("foo bar baz").
     *
     * If a falsy $condition is specified, the method is a no-op.
     *
     * @param string|array<string> $classes
     */
    public function addClass(array|string $classes, mixed $condition = true): self
    {
        if (!$this->test($condition)) {
            return $this;
        }

        if (\is_array($classes)) {
            $classes = implode(' ', $classes);
        }

        $this->attributes['class'] = implode(
            ' ',
            array_unique($this->split(($this->attributes['class'] ?? '').' '.$classes)),
        );

        if (empty($this->attributes['class'])) {
            unset($this->attributes['class']);
        }

        return $this;
    }

    /**
     * Remove a single class ("foo") or multiple from a class string ("foo bar baz").
     *
     * If a falsy $condition is specified, the method is a no-op.
     *
     * @param string|array<string> $classes
     */
    public function removeClass(array|string $classes, mixed $condition = true): self
    {
        if (!$this->test($condition)) {
            return $this;
        }

        if (\is_array($classes)) {
            $classes = implode(' ', $classes);
        }

        $this->attributes['class'] = implode(
            ' ',
            array_diff(
                $this->split($this->attributes['class'] ?? ''),
                $this->split($classes),
            ),
        );

        if (empty($this->attributes['class'])) {
            unset($this->attributes['class']);
        }

        return $this;
    }

    /**
     * Adds
     * - a single style ("color: red")
     * - or multiple styles from a style string ("color: red; background: blue")
     * - or a style array (['color' => 'red', 'background' => 'blue']).
     *
     * If a falsy $condition is specified, the method is a no-op.
     *
     * @param string|array<int|string, string> $styles
     */
    public function addStyle(array|string $styles, mixed $condition = true): self
    {
        if (!$this->test($condition)) {
            return $this;
        }

        if (\is_array($styles)) {
            foreach ($styles as $prop => $value) {
                if (\is_string($prop)) {
                    $styles[$prop] = "$prop:$value";
                }
            }

            $styles = implode(';', $styles);
        }

        $mergedStyles = [...$this->parseStyles($this->attributes['style'] ?? ''), ...$this->parseStyles($styles)];

        $this->attributes['style'] = $this->serializeStyles($mergedStyles);

        if (empty($this->attributes['style'])) {
            unset($this->attributes['style']);
        }

        return $this;
    }

    /**
     * Removes
     * - a single style ("color")
     * - or multiple styles from a style string ("color: red; background: blue")
     * - or a style list (['color', 'background'])
     * - or a style array (['color' => 'red', 'background' => 'blue']).
     *
     * If a falsy $condition is specified, the method is a no-op.
     *
     * @param string|list<string>|array<string, string> $styles
     */
    public function removeStyle(array|string $styles, mixed $condition = true): self
    {
        if (!$this->test($condition)) {
            return $this;
        }

        $styles = (array) $styles;

        foreach ($styles as $prop => $value) {
            if (\is_string($prop)) {
                $styles[$prop] = "$prop:$value";
            } elseif (!str_contains($value, ':')) {
                $styles[$prop] = "$value:";
            }
        }

        $styles = implode(';', $styles);

        $mergedStyles = array_diff_key(
            $this->parseStyles($this->attributes['style'] ?? ''),
            $this->parseStyles($styles),
        );

        $this->attributes['style'] = $this->serializeStyles($mergedStyles);

        if (empty($this->attributes['style'])) {
            unset($this->attributes['style']);
        }

        return $this;
    }

    public function setDoubleEncoding(bool $doubleEncoding): self
    {
        $this->doubleEncoding = $doubleEncoding;

        return $this;
    }

    /**
     * Outputs the attributes as a string that is safe to be placed inside HTML tags.
     * The output will contain a leading space if $leadingSpace is set to true and
     * there is at least one property set, e.g. ' foo="bar" bar="42"'.
     */
    public function toString(bool $leadingSpace = true): string
    {
        $attributes = [];

        foreach ($this->attributes as $name => $value) {
            $attributes[] = '' !== $value ? sprintf('%s="%s"', $name, $this->escapeValue($name, $value)) : $name;
        }

        $string = implode(' ', $attributes);

        return $leadingSpace && $string ? " $string" : $string;
    }

    /**
     * @return \ArrayIterator<string, string>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->attributes);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException(sprintf('The attribute property "%s" does not exist.', $offset));
        }

        return $this->attributes[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    public function jsonSerialize(): array
    {
        return $this->attributes;
    }

    /**
     * Returns true if the argument is truthy.
     */
    private function test(mixed $condition): bool
    {
        if ($condition instanceof \Stringable) {
            $condition = (string) $condition;
        }

        return (bool) $condition;
    }

    /**
     * @return array<string>
     */
    private function split(string $value): array
    {
        return array_filter(preg_split('/\s+/', $value));
    }

    /**
     * Parse attributes from an attribute string like 'foo="bar" baz="42'.
     *
     * @return \Generator<string, string>
     */
    private function parseString(string $attributesString): \Generator
    {
        // Regular expression to match attributes according to
        // https://html.spec.whatwg.org/#before-attribute-name-state
        $attributeRegex = '(
            [\s/]*+                                    # Optional white space including slash
            ([^>\s/][^>\s/=]*+)                        # Attribute name
            \s*+                                       # Optional white space
            (?:
                =                                      # Assignment
                \s*+                                   # Optional white space
                (?|                                    # Value
                    "([^"]*)(?:"|$(*SKIP)(*FAIL))      # Double quoted value
                    |\'([^\']*)(?:\'|$(*SKIP)(*FAIL))  # Or single quoted value
                    |([^\s>]*+)                        # Or unquoted or missing value
                )                                      # Value end
            )?+                                        # Assignment is optional
        )ix';

        preg_match_all($attributeRegex, $attributesString, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);

        foreach ($matches as [1 => $name, 2 => $value]) {
            yield strtolower($name) => html_entity_decode($value ?? '', ENT_QUOTES | ENT_HTML5);
        }
    }

    private function escapeValue(string $name, string $value): string
    {
        if (!preg_match('//u', $value)) {
            throw new \RuntimeException(sprintf('The value of property "%s" is not a valid UTF-8 string.', $name));
        }

        $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, null, $this->doubleEncoding);

        return str_replace(['{{', '}}'], ['&#123;&#123;', '&#125;&#125;'], $value);
    }

    /**
     * @return array<string, list<string>>
     */
    private function parseStyles(string $styles): array
    {
        // Regular expression to match declarations according to
        // https://www.w3.org/TR/css-syntax-3/#declaration-list-diagram
        $declarationRegex = '/
            (?:
                \\\.                              # Escape
                |"(?:\\\.|[^"\n])*+(?:"|\n|$)     # String token double quotes
                |\'(?:\\\.|[^\'\n])*+(?:\'|\n|$)  # String token single quotes
                |\{(?:(?R)|[^}])*+(?:}|$)         # {}-block
                |\[(?:(?R)|[^]])*+(?:]|$)         # []-block
                |\((?:(?R)|[^)])*+(?:\)|$)        # ()-block
                |\/\*(?:(?!\*\/).)*+(?:\*\/|$)    # Comment block
                |[^;{}\[\]()"\']                  # Anything else
            )++
        /ixs';

        // Regular expression to match an <ident-token> according to
        // https://www.w3.org/TR/css-syntax-3/#ident-token-diagram
        $propertyRegex = '/
            ^
            (?:
                \/\*                                # Comment start
                (?:(?!\*\/).)*+                     # Anything but comment end
                (?:\*\/|$(*SKIP)(*FAIL))            # Comment end
                |\s                                 # Or whitespace
            )*+
            (                                       # Match property name
                (?!\d)                              # Must not start with a digit
                (?!-\d)                             # Must not start with a dash followed by a digit
                -?+                                 # Optional leading dash
                (?:
                    [a-z0-9\x80-\xFF_-]
                    |\\\(?:[0-9a-f]{1,6}\s?|[^\n])  # Escape
                )++
            )
            (?:
                \/\*                                # Comment start
                (?:(?!\*\/).)*+                     # Anything but comment end
                (?:\*\/|$(*SKIP)(*FAIL))            # Comment end
                |\s                                 # Or whitespace
            )*+
            :                                       # Colon
        /ixs';

        preg_match_all($declarationRegex, $styles, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);

        $result = [];

        foreach ($matches as [0 => $declaration]) {
            if (preg_match($propertyRegex, $declaration, $match)) {
                // Spacing according to https://www.w3.org/TR/cssom-1/#serialize-a-css-declaration
                $property = trim(substr($match[0], 0, -1), " \n\r\t\v\f");
                $value = trim(substr($declaration, \strlen($match[0])), " \n\r\t\v\f");
                $result[$this->decodeStyleProperty($match[1])][] = "$property: $value;";
            }
        }

        return $result;
    }

    private function decodeStyleProperty(string $property): string
    {
        // Decode an escaped code point according to
        // https://www.w3.org/TR/css-syntax-3/#consume-escaped-code-point
        $property = preg_replace_callback(
            '/\\\(?:([0-9a-f]{1,6}+\s?+)|([^\n]))/i',
            static fn ($match) => $match[2] ?? \IntlChar::chr(hexdec($match[1])),
            $property,
        );

        // Property names are case-insensitive except custom properties according to
        // https://www.w3.org/TR/css-syntax-3/#style-rule
        if (!str_starts_with($property, '--')) {
            $property = strtolower($property);
        }

        return $property;
    }

    /**
     * @param array<string, list<string>> $styles
     */
    private function serializeStyles(array $styles): string
    {
        // Serialize styles according to
        // https://www.w3.org/TR/cssom-1/#serialize-a-css-declaration-block
        return implode(' ', array_merge(...array_values($styles)));
    }
}
