<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures;

class SerializableObjectWithScalarData
{
    private $privateMember;

    public function __construct($privateMember)
    {
        $this->privateMember = $privateMember;
    }

    public function __serialize(): array
    {
        return [
            'privateMember' => $this->privateMember,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->privateMember = $data['privateMember'];
    }
}
