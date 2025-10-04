<?php

namespace Basko\FSMTest\TestCase;

use Basko\FSM\State;
use Basko\FSM\StateInterface;
use Basko\FSM\StateMachine;
use Basko\FSM\Transition;
use Basko\FSMTest\Entity\Post;
use PHPUnit\Framework\TestCase;

class GraphvizTest extends TestCase {
    public function testGraphviz()
    {
        $stateDraft = new State('draft');
        $stateReview = new State('review');
        $stateApproved = new State('approved');
        $stateRejected = new State('rejected');
        $stateEditing = new State('editing');
        $statePublished = new State('published');

        $transitionToReview = new Transition('to_review', [$stateDraft, $stateEditing], $stateReview);
        $transitionToApproved = new Transition('to_approved', [$stateReview], $stateApproved);
        $transitionToRejected = new Transition('to_rejected', [$stateReview], $stateRejected);
        $transitionToEditing = new Transition('to_editing', [$stateReview], $stateEditing);
        $transitionToPublished = new Transition('to_published', [$stateApproved], $statePublished);

        $stateMachine = new StateMachine(
            function (Post $post) {
                return new State($post->state);
            },
            function (Post $post, StateInterface $newState) {
                $post->state = $newState->getName();
            }
        );

        $stateMachine->addTransitions([
            $transitionToReview,
            $transitionToApproved,
            $transitionToRejected,
            $transitionToEditing,
            $transitionToPublished,
        ]);

        $exporter = new \Basko\FSM\Export\Graphviz($stateMachine);

        $expectedGraph = <<<TEXT
digraph fms {
  ratio="compress" rankdir="LR"
  node [fontsize="11" fontname="Arial"];
  edge [fontsize="9" fontname="Arial" color="grey" arrowhead="open" arrowsize="0.5"];

  node_draft [label="draft", shape="record", style="filled", fillcolor="#eeeeee"];
  node_editing [label="editing", shape="record", style="filled", fillcolor="#eeeeee", label="editing"];
  node_review [label="review", shape="record", style="filled", fillcolor="#eeeeee"];
  node_approved [label="approved", shape="record", style="filled", fillcolor="#eeeeee"];
  node_rejected [label="rejected", shape="record", style="filled", fillcolor="#eeeeee", label="rejected"];
  node_published [label="published", shape="record", style="filled", fillcolor="#eeeeee", label="published"];

  node_draft -> node_review [style="filled", label="to_review"];
  node_editing -> node_review [style="filled", label="to_review"];
  node_review -> node_approved [style="filled", label="to_approved"];
  node_review -> node_rejected [style="filled", label="to_rejected"];
  node_review -> node_editing [style="filled", label="to_editing"];
  node_approved -> node_published [style="filled", label="to_published"];
}

TEXT;

        $this->assertEquals($expectedGraph, $exporter->build());
    }
}