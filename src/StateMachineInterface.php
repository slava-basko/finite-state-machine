<?php

namespace Basko\FSM;

/**
 * @template TSubject
 * @template TPayload
 */
interface StateMachineInterface
{
    /**
     * @param array<\Basko\FSM\TransitionInterface<TSubject, TPayload>> $transitions
     * @return void
     * @throws \Basko\FSM\Exception\Exception|\Basko\FSM\Exception\InvalidArgumentException
     */
    public function addTransitions(array $transitions);

    /**
     * @return array<\Basko\FSM\TransitionInterface<TSubject, TPayload>>
     */
    public function getTransitions();

    /**
     * @param non-empty-string $transitionName
     * @param TSubject $subject
     * @return bool
     * @throws \Basko\FSM\Exception\Exception
     */
    public function canTransition($transitionName, $subject);

    /**
     * @param non-empty-string $transitionName
     * @param TSubject $subject
     * @return \Basko\FSM\StateInterface<TPayload>
     * @throws \Basko\FSM\Exception\Exception
     */
    public function transition($transitionName, $subject);
}
