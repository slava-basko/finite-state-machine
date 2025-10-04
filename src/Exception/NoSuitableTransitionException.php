<?php

namespace Basko\FSM\Exception;

use Basko\FSM\StateInterface;

final class NoSuitableTransitionException extends Exception
{
    /**
     * @param non-empty-string $searchedTransitionName
     * @param \Basko\FSM\StateInterface<mixed> $state
     * @return \Basko\FSM\Exception\NoSuitableTransitionException
     */
    public static function create($searchedTransitionName, StateInterface $state)
    {
        return new NoSuitableTransitionException(\sprintf(
            "Can't execute transition '%s', no suitable transition found for '%s' states",
            $searchedTransitionName,
            $state->getName()
        ));
    }
}
