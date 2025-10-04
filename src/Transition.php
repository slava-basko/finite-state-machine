<?php

namespace Basko\FSM;

use Basko\FSM\Exception\InvalidArgumentException;

/**
 * @template TSubject
 * @template TPayload
 * @template-implements \Basko\FSM\TransitionInterface<TSubject, TPayload>
 */
final class Transition implements TransitionInterface
{
    /**
     * @var non-empty-string
     */
    private $name;

    /**
     * @var array<\Basko\FSM\StateInterface<TPayload>>
     */
    private $fromStates;

    /**
     * @var \Basko\FSM\StateInterface<TPayload>
     */
    private $toState;

    /**
     * @var callable(TSubject):bool|null
     */
    private $guard;

    /**
     * @param non-empty-string $name
     * @param array<\Basko\FSM\StateInterface<TPayload>> $from
     * @param \Basko\FSM\StateInterface<TPayload> $to
     * @param callable(TSubject):bool|null $guard
     * @throws \Basko\FSM\Exception\InvalidArgumentException
     */
    public function __construct($name, $from, StateInterface $to, $guard = null)
    {
        InvalidArgumentException::assertNonEmptyString($name, __METHOD__, 1);
        InvalidArgumentException::assertListOfStates($from, __METHOD__, 2);
        if (!\is_null($guard)) {
            InvalidArgumentException::assertCallable($guard, __METHOD__, 1);
        }

        $this->name = $name;
        $this->fromStates = $from;
        $this->toState = $to;
        $this->guard = $guard;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromStates()
    {
        return $this->fromStates;
    }

    /**
     * {@inheritdoc}
     */
    public function getToState()
    {
        return $this->toState;
    }

    /**
     * {@inheritdoc}
     */
    public function getGuard()
    {
        return $this->guard;
    }

    /**
     * {@inheritdoc}
     */
    public function can(StateInterface $currentSubjectState, $subject)
    {
        foreach ($this->fromStates as $fromState) {
            if ($fromState->equals($currentSubjectState)) {
                if (\is_callable($this->guard)) {
                    return (bool)\call_user_func($this->guard, $subject);
                }

                return true;
            }
        }

        return false;
    }
}
