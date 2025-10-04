<?php

namespace Basko\FSMTest\Entity;

class NotFinishedPost extends Post
{
    protected $map = [
        'hasTitle' => false,
        'hasBody' => true,
        'hasAuthor' => true,
        'isLocked' => false,
    ];
}