<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Basko\FSM\State;
use Basko\FSM\StateInterface;
use Basko\FSM\StateMachine;
use Basko\FSM\Transition;

$stateDraft = new State('draft');
$stateReview = new State('review');
$stateApproved = new State('approved');
$stateRejected = new State('rejected');
$stateEditing = new State('editing');
$statePublished = new State('published');

/** @var Transition<\Post, null> $transitionToReview */
$transitionToReview = new Transition('to_review', [$stateDraft, $stateEditing], $stateReview);
/** @var Transition<\Post, null> $transitionToApproved */
$transitionToApproved = new Transition('to_approved', [$stateReview], $stateApproved);
/** @var Transition<\Post, null> $transitionToRejected */
$transitionToRejected = new Transition('to_rejected', [$stateReview], $stateRejected);
/** @var Transition<\Post, null> $transitionToEditing */
$transitionToEditing = new Transition('to_editing', [$stateReview], $stateEditing);
/** @var Transition<\Post, null> $transitionToPublished */
$transitionToPublished = new Transition('to_published', [$stateApproved], $statePublished);

class Post
{
    /**
     * @var non-empty-string
     */
    public $state = 'draft';
}

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

//$exporter = new \Basko\FSM\Export\Graphviz($stateMachine);
//echo $exporter->build() . "\n";exit();

$post = new Post();

$stateMachine->transition('to_review', $post);
var_dump($post);

$stateMachine->transition('to_approved', $post);
var_dump($post);