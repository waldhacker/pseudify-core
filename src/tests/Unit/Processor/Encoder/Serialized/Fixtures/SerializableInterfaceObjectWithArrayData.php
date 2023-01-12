<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures;

class SerializableInterfaceObjectWithArrayData implements \Serializable
{
    private $privateMember;
    protected $protectedMember;
    public $publicMember;

    public function __construct($privateMember, $protectedMember, $publicMember)
    {
        $this->privateMember = $privateMember;
        $this->protectedMember = $protectedMember;
        $this->publicMember = $publicMember;
    }

    public function serialize()
    {
        return serialize([
            $this->privateMember,
            $this->protectedMember,
            $this->publicMember,
        ]);
    }

    public function unserialize($data)
    {
        [
            $this->privateMember,
            $this->protectedMember,
            $this->publicMember
        ] = unserialize($data);
    }
}
