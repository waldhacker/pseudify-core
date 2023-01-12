<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Fixtures;

class InvalidDataProcessing
{
    protected $identifier;
    protected $processor;

    public function __construct(string $identifier, callable $processor)
    {
        $this->identifier = $identifier;
        $this->processor = $processor;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getProcessor(): callable
    {
        return $this->processor;
    }
}
