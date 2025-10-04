<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Basko\FSM\StateInterface;
use Basko\FSM\StateMachine;
use Basko\FSM\Transition;

class Road2 {
    /**
     * @var non-empty-string
     */
    public $state = 'yellow';
}

class Intersection2 {
    /**
     * @var \Road2
     */
    public $northSouthRoad;

    /**
     * @var \Road2
     */
    public $eastWestRoad;

    public function __construct()
    {
        $this->northSouthRoad = new Road2();
        $this->eastWestRoad = new Road2();

        $this->northSouthRoad->state = 'green';
        $this->eastWestRoad->state = 'red';
    }
}

/**
 * @template-implements StateInterface<array{ns: non-empty-string, ew: non-empty-string}>
 */
class ComplexTrafficLightState implements StateInterface {
    /**
     * @var non-empty-string
     */
    private $name;

    /**
     * @var array{ns: non-empty-string, ew: non-empty-string}
     */
    private $payload;

    /**
     * @param non-empty-string $name
     * @param array{ns: non-empty-string, ew: non-empty-string} $payload
     */
    public function __construct($name, $payload = null)
    {
        $this->name = $name;
        if ($payload) {
            $this->payload = $payload;
        }
    }

    /**
     * @param StateInterface<array{ns: non-empty-string, ew: non-empty-string}> $other
     * @return bool
     */
    public function equals(StateInterface $other)
    {
        return $this->payload['ns'] === $other->getPayload()['ns'] && $this->payload['ew'] === $other->getPayload()['ew'];
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
            "%s|ns=%s|ew=%s",
            $this->name,
            $this->payload['ns'],
            $this->payload['ew']
        );
    }

    /**
     * @return array{ns: non-empty-string, ew: non-empty-string}
     */
    public function getPayload()
    {
        return $this->payload;
    }
}

try {
    $stateNSGreenEWRed = new ComplexTrafficLightState('0', ['ns' => 'green', 'ew' => 'red']);
    $stateNSYellowEWRed = new ComplexTrafficLightState('1', ['ns' => 'yellow', 'ew' => 'red']);
    $stateNSRedEWGreen = new ComplexTrafficLightState('2', ['ns' => 'red', 'ew' => 'green']);
    $stateNSRedEWYellow = new ComplexTrafficLightState('3', ['ns' => 'red', 'ew' => 'yellow']);

    /** @var Transition<\Intersection2, array{ns: non-empty-string, ew: non-empty-string}> $transitionNSGreenEWRed */
    $transitionNSGreenEWRed = new Transition('to_0', [$stateNSRedEWYellow], $stateNSGreenEWRed);
    /** @var Transition<\Intersection2, array{ns: non-empty-string, ew: non-empty-string}> $transitionNSYellowEWRed */
    $transitionNSYellowEWRed = new Transition('to_1', [$stateNSGreenEWRed], $stateNSYellowEWRed);
    /** @var Transition<\Intersection2, array{ns: non-empty-string, ew: non-empty-string}> $transitionNSRedEWGreen */
    $transitionNSRedEWGreen = new Transition('to_2', [$stateNSYellowEWRed], $stateNSRedEWGreen);
    /** @var Transition<\Intersection2, array{ns: non-empty-string, ew: non-empty-string}> $transitionNSRedEWYellow */
    $transitionNSRedEWYellow = new Transition('to_3', [$stateNSRedEWGreen], $stateNSRedEWYellow);

    /** @var StateMachine<\Intersection2, array{ns: non-empty-string, ew: non-empty-string}> $stateMachine */
    $stateMachine = new StateMachine(
        function (Intersection2 $intersection) {
            return new ComplexTrafficLightState(
                'doesnt_matter',
                ['ns' => $intersection->northSouthRoad->state, 'ew' => $intersection->eastWestRoad->state]
            );
        },
        function (Intersection2 $intersection, StateInterface $newState) {
            $intersection->northSouthRoad->state = $newState->getPayload()['ns'];
            $intersection->eastWestRoad->state = $newState->getPayload()['ew'];
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

    $intersection = new Intersection2();
    var_dump($intersection);

    $stateMachine->transition('to_1', $intersection);
    var_dump($intersection);

    $stateMachine->transition('to_2', $intersection);
    var_dump($intersection);

    $stateMachine->transition('to_3', $intersection);
    var_dump($intersection);

    $stateMachine->transition('to_0', $intersection);
    var_dump($intersection);
} catch (\Exception $exception) {
    echo $exception->getMessage();
}