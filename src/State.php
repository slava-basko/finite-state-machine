<?php

namespace Basko\FSM;

use Basko\FSM\Exception\InvalidArgumentException;

/**
 * @template TPayload
 * @template-implements \Basko\FSM\StateInterface<TPayload>
 */
final class State implements StateInterface
{
    /**
     * @var non-empty-string
     */
    private $name;

    /**
     * @var TPayload
     */
    private $payload;

    /**
     * @param non-empty-string $name
     * @param TPayload $payload
     * @throws \Basko\FSM\Exception\InvalidArgumentException
     */
    public function __construct($name, $payload = null)
    {
        InvalidArgumentException::assertNonEmptyString($name, __METHOD__, 1);

        $this->name = $name;
        $this->payload = $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(StateInterface $other)
    {
        return $this->name === $other->getName();
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
    public function getLabel()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
