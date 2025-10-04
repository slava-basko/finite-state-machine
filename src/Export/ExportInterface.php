<?php

namespace Basko\FSM\Export;

interface ExportInterface
{
    /**
     * @return string
     */
    public function build();
}
