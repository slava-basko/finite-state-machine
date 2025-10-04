<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Basko\FSM\StateInterface;
use Basko\FSM\StateMachine;
use Basko\FSM\Transition;

class TrafficLight {
    /**
     * Bit 1: Rns
     * Bit 2: Yns
     * Bit 3: Gns
     * Bit 4: Rew
     * Bit 5: Yew
     * Bit 6: Gew
     *
     * @var int
     */
    public $bitmask = 0b010010;

    public function __construct()
    {
        $this->bitmask = 0b001100;
    }

    /**
     * @return void
     */
    public function showStatus() {
        $isBitSet = function($bitPosition) {
            return ($this->bitmask & (1 << ($bitPosition - 1))) !== 0;
        };

        echo "Current bitmask: " . sprintf('%06b', $this->bitmask) . " (dec: {$this->bitmask})\n";
        echo "Bits state:\n";
        echo "  Bit 1 (Rns): " . ($isBitSet(1) ? 'ON' : 'OFF') . "\n";
        echo "  Bit 2 (Yns): " . ($isBitSet(2) ? 'ON' : 'OFF') . "\n";
        echo "  Bit 3 (Gns): " . ($isBitSet(3) ? 'ON' : 'OFF') . "\n";
        echo "  Bit 4 (Rew): " . ($isBitSet(4) ? 'ON' : 'OFF') . "\n";
        echo "  Bit 5 (Yew): " . ($isBitSet(5) ? 'ON' : 'OFF') . "\n";
        echo "  Bit 6 (Gew): " . ($isBitSet(6) ? 'ON' : 'OFF') . "\n";
        echo "\n";
    }
}

/**
 * @template-implements StateInterface<int>
 */
class TrafficLightState implements StateInterface {
    /**
     * @var non-empty-string
     */
    private $name;

    /**
     * @var int
     */
    private $payload;

    /**
     * @param non-empty-string $name
     * @param int $payload
     */
    public function __construct($name, $payload = null)
    {
        $this->name = $name;
        if (!\is_int($payload)) {
            throw new \InvalidArgumentException('$payload must be an integer');
        }
        $this->payload = $payload;
    }

    /**
     * @param \TrafficLightState $other
     * @return bool
     */
    public function equals(StateInterface $other)
    {
        return $this->payload === $other->getPayload();
    }

    /**
     * @return non-empty-string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return non-empty-string
     */
    public function getLabel()
    {
        return \sprintf(
            "%s|%s",
            $this->name,
            sprintf('%06b', $this->payload)
        );
    }

    /**
     * @return int
     */
    public function getPayload()
    {
        return $this->payload;
    }
}

try {
    $stateNSGreenEWRed =    new TrafficLightState('0', 0b001100);
    $stateNSYellowEWRed =   new TrafficLightState('1', 0b010100);
    $stateNSRedEWGreen =    new TrafficLightState('2', 0b100001);
    $stateNSRedEWYellow =   new TrafficLightState('3', 0b100010);

    /** @var Transition<\TrafficLight, int> $transitionNSGreenEWRed */
    $transitionNSGreenEWRed = new Transition('to_0', [$stateNSRedEWYellow], $stateNSGreenEWRed);
    /** @var Transition<\TrafficLight, int> $transitionNSYellowEWRed */
    $transitionNSYellowEWRed = new Transition('to_1', [$stateNSGreenEWRed], $stateNSYellowEWRed);
    /** @var Transition<\TrafficLight, int> $transitionNSRedEWGreen */
    $transitionNSRedEWGreen = new Transition('to_2', [$stateNSYellowEWRed], $stateNSRedEWGreen);
    /** @var Transition<\TrafficLight, int> $transitionNSRedEWYellow */
    $transitionNSRedEWYellow = new Transition('to_3', [$stateNSRedEWGreen], $stateNSRedEWYellow);

    $stateMachine = new StateMachine(
        function (TrafficLight $intersection) {
            return new TrafficLightState('doesnt_matter', $intersection->bitmask);
        },
        function (TrafficLight $intersection, StateInterface $newState) {
            $intersection->bitmask = $newState->getPayload();
        }
    );

    $stateMachine->addTransitions([
        $transitionNSGreenEWRed,
        $transitionNSYellowEWRed,
        $transitionNSRedEWGreen,
        $transitionNSRedEWYellow,
    ]);

    //$exporter = new \Basko\FSM\Export\Graphviz($stateMachine);
    //echo $exporter->build() . "\n";exit();

    $intersection = new TrafficLight();
    $intersection->showStatus();

    $stateMachine->transition('to_1', $intersection);
    $intersection->showStatus();

    $stateMachine->transition('to_2', $intersection);
    $intersection->showStatus();

    $stateMachine->transition('to_3', $intersection);
    $intersection->showStatus();

    $stateMachine->transition('to_0', $intersection);
    $intersection->showStatus();
} catch (\Exception $exception) {
    echo $exception->getMessage();
}