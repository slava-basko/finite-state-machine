<?php

namespace Basko\FSMTest\TestCase;

use Basko\FSM\Exception\InvalidArgumentException;
use Basko\FSM\State;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    public function testState()
    {
        $state = new State('a');

        $this->assertEquals('a', $state->getName());
    }

    public function testEqual()
    {
        $a = new State('a');
        $b = new State('b');
        $c = new State('a');

        $this->assertFalse($a->equals($b));
        $this->assertTrue($a->equals($c));
    }

    public function testStateEmptyString()
    {
        try {
            new State('');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals(
                "Basko\FSM\State::__construct() expects parameter 1 to be non-empty-string, 'string' given",
                $exception->getMessage()
            );
        }
    }

    public function testStateLabel()
    {
        $state = new State('a');
        $this->assertEquals('a', $state->getLabel());
    }

    public function testStatePayload()
    {
        $state = new State('a', 'payload');
        $this->assertEquals('payload', $state->getPayload());

        $state2 = new State('b', ['k1' => 'v1', 'k2' => 'v2']);
        $this->assertEquals(['k1' => 'v1', 'k2' => 'v2'], $state2->getPayload());
    }
}