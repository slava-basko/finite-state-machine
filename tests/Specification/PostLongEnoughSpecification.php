<?php

namespace Basko\FSMTest\Specification;

use Basko\Specification\AbstractSpecification;

class PostLongEnoughSpecification extends AbstractSpecification
{
    /**
     * @param \Basko\FSMTest\Entity\Post $candidate
     * @return bool
     */
    public function isSatisfiedBy($candidate)
    {
        return \strlen($candidate->getBody()) > 200;
    }
}