<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

abstract class Reader implements ReaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function firstClassMetadata(\ReflectionClass $class, string $name): ?object
    {
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->getClassMetadata($class, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function firstFunctionMetadata(\ReflectionFunctionAbstract $function, string $name): ?object
    {
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->getFunctionMetadata($function, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function firstPropertyMetadata(\ReflectionProperty $property, string $name): ?object
    {
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->getPropertyMetadata($property, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function firstConstantMetadata(\ReflectionClassConstant $constant, string $name): ?object
    {
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->getConstantMetadata($constant, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function firstParameterMetadata(\ReflectionParameter $parameter, string $name): ?object
    {
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->getParameterMetadata($parameter, $name) as $attribute) {
            return $attribute;
        }

        return null;
    }
}
