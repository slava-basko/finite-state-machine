<?php

namespace Basko\FSM;

/**
 * @template TSubject
 * @template TPayload
 */
interface TransitionInterface
{
    /**
     * @return non-empty-string
     */
    public function getName();

    /**
     * @return array<\Basko\FSM\StateInterface<TPayload>>
     */
    public function getFromStates();

    /**
     * @return \Basko\FSM\StateInterface<TPayload>
     */
    public function getToState();

    /**
     * @return callable(TSubject):bool|null
     */
    public function getGuard();

    /**
     * @param \Basko\FSM\StateInterface<TPayload> $currentSubjectState
     * @param TSubject $subject
     * @return bool
     * @throws \Basko\FSM\Exception\InvalidArgumentException
     */
    public function can(StateInterface $currentSubjectState, $subject);
}
