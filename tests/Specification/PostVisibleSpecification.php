<?php

namespace Basko\FSMTest\Specification;

use Basko\Specification\AbstractSpecification;

class PostVisibleSpecification extends AbstractSpecification
{
    /**
     * @param \Basko\FSMTest\Entity\Post $candidate
     * @return bool
     */
    public function isSatisfiedBy($candidate)
    {
        return $candidate->hasAuthor() && !$candidate->isLocked();
    }
}