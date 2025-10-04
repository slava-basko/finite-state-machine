<?php

namespace Basko\FSMTest\TestCase;

use Basko\FSM\Exception\Exception;
use Basko\FSM\Exception\InvalidArgumentException;
use Basko\FSM\Exception\NoSuitableTransitionException;
use Basko\FSM\State;
use Basko\FSM\StateInterface;
use Basko\FSM\StateMachine;
use Basko\FSM\Transition;
use Basko\FSMTest\Entity\Post;
use PHPUnit\Framework\TestCase;

class StateMachineTest extends TestCase
{
    public function testStateMachine()
    {
        $stateDraft = new State('draft');
        $stateReview = new State('review');
        $statePublish = new State('publish');

        $toReviewTransition = new Transition('to_review', [$stateDraft], $stateReview);
        $toPublishTransition = new Transition('to_publish', [$stateReview], $statePublish);

        $stateMachine = new StateMachine(
            function (Post $post) {
                return new State($post->status);
            },
            function (Post $post, StateInterface $newState) {
                $post->status = $newState->getName();
                return $newState;
            }
        );
        $stateMachine->addTransitions([$toReviewTransition, $toPublishTransition]);

        $this->assertTrue($stateMachine->canTransition('to_review', new Post()));
        $this->assertFalse($stateMachine->canTransition('to_publish', new Post()));

        try {
            $stateMachine->transition('to_publish', new Post());
        } catch (NoSuitableTransitionException $exception) {
            $this->assertEquals(
                "Can't execute transition 'to_publish', no suitable transition found for 'draft' states",
                $exception->getMessage()
            );
        }
    }

    public function testTransition()
    {
        $stateDraft = new State('draft');
        $stateReview = new State('review');

        $toReviewTransition = new Transition('to_review', [$stateDraft], $stateReview);

        $stateMachine = new StateMachine(
            function (Post $post) {
                return new State($post->status);
            },
            function (Post $post, State $newState) {
                $post->status = $newState->getName();
            }
        );
        $stateMachine->addTransitions([$toReviewTransition]);

        $this->assertEquals(
            new State('review'),
            $stateMachine->transition('to_review', new Post())
        );
    }

    public function testTransitionFailure()
    {
        $stateDraft = new State('draft');
        $stateReview = new State('review');

        $toReviewTransition = new Transition('to_review', [$stateDraft], $stateReview);

        $stateMachine = new StateMachine(
            function (Post $post) {
                return new State($post->status);
            },
            function (Post $post, State $newState) {
                $post->status = $newState;
            }
        );
        $stateMachine->addTransitions([$toReviewTransition]);

        try {
            $stateMachine->transition('to_publish', new Post());
        } catch (Exception $exception) {
            $this->assertEquals("Transition 'to_publish' does not exist", $exception->getMessage());
        }
    }

    public function testStateMachineMiddle()
    {
        $stateDraft = new State('draft');
        $stateReview = new State('review');
        $statePublish = new State('publish');

        $toReviewTransition =  new Transition('to_review', [$stateDraft], $stateReview);
        $toPublishTransition = new Transition('to_publish', [$stateReview], $statePublish);

        $stateMachine = new StateMachine(
            function (Post $post) {
                return new State($post->status);
            },
            function (Post $post, State $newState) {
                $post->status = $newState->getName();
            }
        );
        $stateMachine->addTransitions([$toReviewTransition, $toPublishTransition]);

        $post = new Post();
        $post->status = 'review';

        $stateMachine->transition('to_publish', $post);

        $this->assertEquals('publish', $post->status);
    }

    public function testAddSomeNonTransition()
    {
        $sm = new StateMachine(
            function () {},
            function () {}
        );

        try {
            $sm->addTransitions([
                new Transition('t1', [new State('a')], new State('b')),
                new Transition('t2', [new State('b')], new State('c')),
                new \stdClass(),
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals(
                "Basko\FSM\StateMachine::addTransitions() expects parameter 1 to be array of Basko\FSM\TransitionInterface, but element 2 has type of 'stdClass'",
                $exception->getMessage()
            );
        }
    }

    public function testAddSomeDuplicateTransition()
    {
        $sm = new StateMachine(
            function () {},
            function () {}
        );

        try {
            $sm->addTransitions([
                new Transition('t1', [new State('a')], new State('b')),
                new Transition('t1', [new State('a')], new State('b')),
            ]);
        } catch (Exception $exception) {
            $this->assertEquals(
                "Transition 't1' is already added",
                $exception->getMessage()
            );
        }
    }
}