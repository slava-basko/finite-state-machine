<?php

namespace Basko\FSM\Export;

use Basko\FSM\StateMachine;
use Basko\FSM\StateMachineInterface;

/**
 * @template TSubject
 * @template TPayload
 */
final class Graphviz implements ExportInterface
{
    /**
     * @var array<non-empty-string, array<string, string>>
     */
    private $options = [
        'graph' => [
            'ratio' => 'compress',
            'rankdir' => 'LR',
        ],
        'node' => [
            'fontsize' => '11',
            'fontname' => 'Arial',
        ],
        'edge' => [
            'fontsize' => '9',
            'fontname' => 'Arial',
            'color' => 'grey',
            'arrowhead' => 'open',
            'arrowsize' => '0.5',
        ],
        'node.state' => [
            'shape' => 'record',
            'style' => 'filled',
            'fillcolor' => '#eeeeee',
        ],
    ];

    /**
     * @var StateMachineInterface<TSubject, TPayload>
     */
    private $stateMachine;

    /**
     * @param StateMachineInterface<TSubject, TPayload> $stateMachine
     */
    public function __construct(StateMachineInterface $stateMachine)
    {
        $this->stateMachine = $stateMachine;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function build()
    {
        $transitions = $this->stateMachine->getTransitions();

        $nodes = [];
        foreach ($transitions as $transition) {
            foreach ($transition->getFromStates() as $fromState) {
                $nodes[$fromState->getLabel()] = [
                    'class' => $fromState->getLabel(),
                    'attributes' => $this->options['node.state'],
                ];
            }

            $nodes[$transition->getToState()->getLabel()] = [
                'class' => $transition->getToState()->getLabel(),
                'attributes' => \array_merge($this->options['node.state'], [
                    'label' => $transition->getToState()->getLabel(),
                ]),
            ];
        }

        $edges = [];
        foreach ($transitions as $transition) {
            foreach ($transition->getFromStates() as $fromState) {
                $edges[] = [
                    'from' => $fromState->getLabel(),
                    'to' => $transition->getToState()->getLabel(),
                    'attributes' => [
                        'label' => $transition->getName()
                    ],
                ];
            }
        }

        return $this->makeStart() . $this->makeNodes($nodes) . $this->makeEdges($edges) . $this->makeEnd();
    }

    /**
     * @param array<non-empty-string, array{
     *      class: string,
     *      attributes: array<string, string>
     *  }> $nodes
     * @return string
     */
    private function makeNodes(array $nodes)
    {
        $code = '';
        foreach ($nodes as $id => $node) {
            $code .= \sprintf(
                "  node_%s [label=\"%s\"%s];\n",
                $this->dotize($id),
                $node['class'],
                $this->addAttributes($node['attributes'])
            );
        }

        return $code . "\n";
    }

    /**
     * @param array<array{from: non-empty-string, to: non-empty-string, attributes: array<string, string>}> $edges
     * @return string
     */
    private function makeEdges(array $edges)
    {
        $code = '';
        foreach ($edges as $edge) {
            $code .= \sprintf(
                "  node_%s -> node_%s [style=\"filled\"%s];\n",
                $this->dotize($edge['from']),
                $this->dotize($edge['to']),
                $this->addAttributes($edge['attributes'])
            );
        }

        return $code;
    }

    /**
     * @return string
     */
    private function makeStart()
    {
        return \sprintf(
            "digraph fms {\n  %s\n  node [%s];\n  edge [%s];\n\n",
            $this->addOptions($this->options['graph']),
            $this->addOptions($this->options['node']),
            $this->addOptions($this->options['edge'])
        );
    }

    /**
     * @return string
     */
    private function makeEnd()
    {
        return "}\n";
    }

    /**
     * @param array<string, string> $attributes
     * @return string
     */
    private function addAttributes(array $attributes)
    {
        $code = [];
        foreach ($attributes as $k => $v) {
            $code[] = \sprintf('%s="%s"', $k, $v);
        }

        return $code ? ', ' . \implode(', ', $code) : '';
    }

    /**
     * @param array<string, string> $options
     * @return string
     */
    private function addOptions(array $options)
    {
        $code = [];
        foreach ($options as $k => $v) {
            $code[] = \sprintf('%s="%s"', $k, $v);
        }

        return \implode(' ', $code);
    }

    /**
     * @param string $id
     * @return string
     */
    private function dotize($id)
    {
        return (string)\preg_replace('/\W/i', '_', $id);
    }
}
