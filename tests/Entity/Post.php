<?php

namespace Basko\FSMTest\Entity;

/**
 * @method bool hasTitle()
 * @method bool hasBody()
 * @method bool hasAuthor()
 * @method bool isLocked()
 */
class Post
{
    public $status = 'draft';

    protected $map = [
        'hasTitle' => true,
        'hasBody' => true,
        'hasAuthor' => true,
        'isLocked' => false,
    ];

    public function __call($name, $arguments)
    {
        return $this->map[$name];
    }

    public function getBody()
    {
        return "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.";
    }
}