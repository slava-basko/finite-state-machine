<?php

namespace Basko\FSMTest\TestCase;

use Basko\FSM\State;
use Basko\FSM\Transition;
use Basko\FSMTest\Entity\Post;
use PHPUnit\Framework\TestCase;

class TransitionTest extends TestCase
{
    public function testTransition()
    {
        $toReviewTransition = new Transition(
            'to_review',
            [new State('draft')],
            new State('review')
        );
        $this->assertTrue($toReviewTransition->can(new State('draft'), new Post()));
        $this->assertFalse($toReviewTransition->can(new State('publish'), new Post()));
    }

    public function testTransition2()
    {
        $toReviewTransition = new Transition(
            'to_review',
            [new State('draft'), new State('another')],
            new State('review')
        );
        $this->assertTrue($toReviewTransition->can(new State('draft'), new Post()));
        $this->assertFalse($toReviewTransition->can(new State('publish'), new Post()));
    }

    public function testTransition3()
    {
        $toReviewTransition = new Transition(
            'to_review',
            [new State('another'), new State('draft')],
            new State('review')
        );
        $this->assertTrue($toReviewTransition->can(new State('draft'), new Post()));
        $this->assertFalse($toReviewTransition->can(new State('publish'), new Post()));
    }

    public function testTransitionWithGuard()
    {
        $toReviewTransition = new Transition(
            'to_review',
            [new State('draft')],
            new State('review'),
            function (Post $post) {
                return false;
            }
        );
        $this->assertFalse($toReviewTransition->can(new State('draft'), new Post()));
    }

    public function testgetFromStates()
    {
        $transition = new Transition(
            't1',
            [new State('a'), new State('b')],
            new State('c')
        );

        $this->assertEquals(
            [new State('a'), new State('b')],
            $transition->getFromStates()
        );
    }

    public function testGetGuard()
    {
        $t1 = new Transition('t1', [new State('a')], new State('b'));
        $this->assertEquals(null, $t1->getGuard());

        $t2 = new Transition('t2', [new State('a')], new State('b'), function ($subject) {
            return true;
        });
        $this->assertInstanceOf(\Closure::class, $t2->getGuard());
    }
}