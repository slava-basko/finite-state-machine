<?php

namespace Basko\FSM\Exception;

use Basko\FSM\StateInterface;
use Basko\FSM\TransitionInterface;

final class InvalidArgumentException extends Exception
{
    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertNonEmptyString($value, $callee, $parameterPosition)
    {
        if (!\is_string($value) || \strlen($value) === 0) {
            throw new static(
                \sprintf(
                    "%s() expects parameter %d to be non-empty-string, '%s' given",
                    $callee,
                    $parameterPosition,
                    \is_object($value) ? \get_class($value) : \gettype($value)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws \Basko\FSM\Exception\InvalidArgumentException
     */
    public static function assertListOfStates($value, $callee, $parameterPosition)
    {
        static::assertListOfType($value, $callee, $parameterPosition, StateInterface::class);
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws \Basko\FSM\Exception\InvalidArgumentException
     */
    public static function assertListOfTransitions($value, $callee, $parameterPosition)
    {
        static::assertListOfType($value, $callee, $parameterPosition, TransitionInterface::class);
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws \Basko\FSM\Exception\InvalidArgumentException
     */
    public static function assertCallable($value, $callee, $parameterPosition)
    {
        if (\is_callable($value)) {
            return;
        }

        if (!\is_array($value) && !\is_string($value)) {
            throw new static(
                \sprintf(
                    '%s() expects parameter %d to be a valid callback, array, string or closure, %s given',
                    $callee,
                    $parameterPosition,
                    \is_object($value) ? \get_class($value) : \gettype($value)
                )
            );
        }

        if (\is_array($value)) {
            $type = 'method';
            $value = \array_values($value);

            $sep = '::';
            if (\is_object($value[0])) {
                $value[0] = \get_class($value[0]);
                $sep = '->';
            }

            $value = \array_map(static function ($item) {
                return trim(var_export($item, true), "'");
            }, $value);
            $value = \implode($sep, $value);
        } else {
            $type = 'function';
        }

        throw new static(
            \sprintf(
                "%s() expects parameter %d to be a valid callback, %s '%s' not found or invalid %s name",
                $callee,
                $parameterPosition,
                $type,
                $value,
                $type
            )
        );
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @param class-string $type
     * @return void
     * @throws \Basko\FSM\Exception\InvalidArgumentException
     */
    public static function assertListOfType($value, $callee, $parameterPosition, $type)
    {
        if (!\is_array($value)) {
            throw new static(
                \sprintf(
                    "%s() expects parameter %d to be array, '%s' given",
                    $callee,
                    $parameterPosition,
                    \is_object($value) ? \get_class($value) : \gettype($value)
                )
            );
        }

        foreach ($value as $index => $item) {
            if (!($item instanceof $type)) {
                throw new static(
                    \sprintf(
                        "%s() expects parameter %d to be array of %s, but element %s has type of '%s'",
                        $callee,
                        $parameterPosition,
                        $type,
                        $index,
                        \is_object($item) ? \get_class($item) : \gettype($item)
                    )
                );
            }
        }
    }
}
