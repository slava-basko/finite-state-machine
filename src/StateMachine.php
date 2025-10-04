<?php

namespace Basko\FSM;

use Basko\FSM\Exception\Exception;
use Basko\FSM\Exception\InvalidArgumentException;
use Basko\FSM\Exception\NonExistentTransitionException;
use Basko\FSM\Exception\NoSuitableTransitionException;

/**
 * @template TSubject
 * @template TPayload
 * @template-implements \Basko\FSM\StateMachineInterface<TSubject, TPayload>
 */
final class StateMachine implements StateMachineInterface
{
    /**
     * @var callable(TSubject):\Basko\FSM\StateInterface<TPayload>
     */
    private $getter;

    /**
     * @var callable(TSubject, \Basko\FSM\StateInterface<TPayload>):void
     */
    private $setter;

    /**
     * @var array<non-empty-string, \Basko\FSM\TransitionInterface<TSubject, TPayload>>
     */
    private $transitions = [];

    /**
     * @param callable(TSubject):\Basko\FSM\StateInterface<TPayload> $getter
     * @param callable(TSubject, \Basko\FSM\StateInterface<TPayload>):void $setter
     */
    public function __construct(callable $getter, callable $setter)
    {
        $this->getter = $getter;
        // @phpstan-ignore-next-line
        $this->setter = $setter;
    }

    /**
     * {@inheritdoc}
     */
    public function addTransitions(array $transitions)
    {
        InvalidArgumentException::assertListOfTransitions($transitions, __METHOD__, 1);

        foreach ($transitions as $transition) {
            if (isset($this->transitions[$transition->getName()])) {
                throw new Exception("Transition '{$transition->getName()}' is already added");
            }

            $this->transitions[$transition->getName()] = $transition;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * {@inheritdoc}
     */
    public function canTransition($transitionName, $subject)
    {
        $currentSubjectState = $this->getSubjectState($subject);

        $transition = $this->findTransition($transitionName, $currentSubjectState, $subject);
        if (!$transition) {
            return false;
        }

        return $transition->can($currentSubjectState, $subject);
    }

    /**
     * {@inheritdoc}
     */
    public function transition($transitionName, $subject)
    {
        $currentSubjectState = $this->getSubjectState($subject);

        if (!$this->canTransition($transitionName, $subject)) {
            throw NoSuitableTransitionException::create($transitionName, $currentSubjectState);
        }

        $transition = $this->findTransition($transitionName, $currentSubjectState, $subject);
        /** @var TransitionInterface<TSubject, TPayload> $transition */
        $newState = $transition->getToState();

        \call_user_func_array($this->setter, [$subject, $newState]);

        return $newState;
    }

    /**
     * @param TSubject $subject
     * @return \Basko\FSM\StateInterface<TPayload>
     * @throws \Basko\FSM\Exception\Exception
     */
    private function getSubjectState($subject)
    {
        $currentSubjectState = \call_user_func($this->getter, $subject);
        if (!($currentSubjectState instanceof StateInterface)) {
            throw new Exception('Getter return type mismatch');
        }

        return $currentSubjectState;
    }

    /**
     * @param non-empty-string $transitionName
     * @param \Basko\FSM\StateInterface<TPayload> $currentSubjectState
     * @param TSubject $subject
     * @return TransitionInterface<TSubject, TPayload>|null
     * @throws \Basko\FSM\Exception\Exception
     */
    private function findTransition($transitionName, StateInterface $currentSubjectState, $subject)
    {
        if (!isset($this->transitions[$transitionName])) {
            throw NonExistentTransitionException::create($transitionName);
        }

        $transition = $this->transitions[$transitionName];
        if ($transition->can($currentSubjectState, $subject)) {
            return $transition;
        }

        return null;
    }
}
