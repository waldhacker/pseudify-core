<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures;

class SerializableInterfaceObjectWithScalarData implements \Serializable
{
    private $privateMember;

    public function __construct($privateMember)
    {
        $this->privateMember = $privateMember;
    }

    public function serialize()
    {
        return serialize($this->privateMember);
    }

    public function unserialize($data)
    {
        $this->privateMember = unserialize($data);
    }
}
