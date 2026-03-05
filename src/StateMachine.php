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
        InvalidArgumentException::assertNonEmptyString($transitionName, __METHOD__, 1);

        $currentState = $this->getSubjectState($subject);
        $transition = $this->getTransitionByName($transitionName);

        return $transition->can($currentState, $subject);
    }

    /**
     * {@inheritdoc}
     */
    public function transition($transitionName, $subject)
    {
        InvalidArgumentException::assertNonEmptyString($transitionName, __METHOD__, 1);

        $currentState = $this->getSubjectState($subject);
        $transition = $this->getTransitionByName($transitionName);

        if (!$transition->can($currentState, $subject)) {
            throw NoSuitableTransitionException::create($transitionName, $currentState);
        }

        $newState = $transition->getToState();
        \call_user_func($this->setter, $subject, $newState);

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
     * @return \Basko\FSM\TransitionInterface<TSubject, TPayload>
     * @throws \Basko\FSM\Exception\NonExistentTransitionException
     */
    private function getTransitionByName($transitionName)
    {
        if (!isset($this->transitions[$transitionName])) {
            throw NonExistentTransitionException::create($transitionName);
        }

        return $this->transitions[$transitionName];
    }
}
