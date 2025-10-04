<?php

namespace Basko\FSM;

/**
 * @template TPayload
 */
interface StateInterface
{
    /**
     * @param \Basko\FSM\StateInterface<TPayload> $other
     * @return bool
     */
    public function equals(StateInterface $other);

    /**
     * @return non-empty-string
     */
    public function getName();

    /**
     * Label uses in export process (in most cases name=label is ok)
     *
     * @return non-empty-string
     */
    public function getLabel();

    /**
     * Get state payload (use it when you need to handle complex state)
     *
     * @return TPayload
     */
    public function getPayload();
}
