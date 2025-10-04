<?php

namespace Basko\FSMTest\Specification;

use Basko\Specification\AbstractSpecification;

class PostCompleteSpecification extends AbstractSpecification
{
    /**
     * @param \Basko\FSMTest\Entity\Post $candidate
     * @return bool
     */
    public function isSatisfiedBy($candidate)
    {
        return $candidate->hasTitle() && $candidate->hasBody();
    }
}