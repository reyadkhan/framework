<?php

/**
 * This file is part of Attributes package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Instantiator;

use JetBrains\PhpStorm\Pure;
use Doctrine\Common\Annotations\DocParser;
use Spiral\Attributes\Exception\AttributeException;

/**
 * @internal DoctrineInstantiator is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class DoctrineInstantiator extends Instantiator
{
    /**
     * An error message that occurs when the attribute has no public field in
     * a format compatible with doctrine/annotations.
     *
     * @see DocParser::Annotation()
     *
     * @var string
     */
    private const ERROR_INVALID_PROPERTY =
        'The attribute #[%s] declared on %s does not have a property named "%s".' . "\n" .
        'Available properties: %s'
    ;

    /**
     * An error that occurs when specifying invalid arguments for an attribute
     * in a format compatible with doctrine/annotations.
     *
     * @see DocParser::syntaxError()
     *
     * @var string
     */
    private const ERROR_INVALID_ARGUMENT = 'Expected %s, got %s';

    /**
     * @var string
     */
    private const DEFAULT_PROPERTY_NAME = 'value';

    /**
     * @param \ReflectionClass $attr
     * @param array $arguments
     * @param string $context
     * @return object
     * @throws \ReflectionException
     */
    public function instantiate(\ReflectionClass $attr, array $arguments, string $context): object
    {
        $arguments = $this->formatArguments($arguments);

        // Using constructor
        if ($this->getConstructor($attr)) {
            return $attr->newInstance($arguments);
        }

        // Using direct insert
        $instance = $attr->newInstanceWithoutConstructor();

        foreach ($arguments as $name => $value) {
            try {
                $property = $attr->getProperty($name);

                if (!$property->isPublic()) {
                    throw $this->propertyNotFound($attr, $name, $context);
                }

                $instance->$name = $value;
            } catch (\Throwable $e) {
                throw $this->propertyNotFound($attr, $name, $context);
            }
        }

        return $instance;
    }

    /**
     * @param iterable $arguments
     * @return array
     */
    private function formatArguments(iterable $arguments): array
    {
        $result = [];

        foreach ($arguments as $name => $value) {
            if (\is_int($name)) {
                $this->validateArgumentPosition($name, $value);

                $name = self::DEFAULT_PROPERTY_NAME;
            }

            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * @param int $index
     * @param mixed $value
     */
    private function validateArgumentPosition(int $index, $value): void
    {
        if ($index === 0) {
            return;
        }

        $value = \is_scalar($value) ? \var_export($value, true) : \get_debug_type($value);
        $message = \sprintf(self::ERROR_INVALID_ARGUMENT, self::DEFAULT_PROPERTY_NAME, $value);

        throw AttributeException::syntaxError($message);
    }

    /**
     * @param \ReflectionClass $attr
     * @param string $name
     * @param string $context
     * @return AttributeException
     */
    private function propertyNotFound(\ReflectionClass $attr, string $name, string $context): AttributeException
    {
        $available = $this->getAvailablePropertiesString($attr);
        $message = \sprintf(self::ERROR_INVALID_PROPERTY, $attr->getName(), $context, $name, $available);

        return AttributeException::creationError($message);
    }

    /**
     * @param \ReflectionClass $class
     * @return string
     */
    #[Pure]
    private function getAvailablePropertiesString(\ReflectionClass $class): string
    {
        return \implode(', ', \get_class_vars($class->getName()));
    }
}
