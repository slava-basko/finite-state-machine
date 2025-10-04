<?php

namespace Basko\FSM\Exception;

final class NonExistentTransitionException extends Exception
{
    /**
     * @param non-empty-string $transitionName
     * @return \Basko\FSM\Exception\NonExistentTransitionException
     */
    public static function create($transitionName)
    {
        return new NonExistentTransitionException("Transition '$transitionName' does not exist");
    }
}
