<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures;

class SerializableObjectWithArrayData
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

    public function __serialize(): array
    {
        return [
            'privateMember' => $this->privateMember,
            'protectedMember' => $this->protectedMember,
            'publicMember' => $this->publicMember,
        ];
    }

    public function unserialize(array $data): void
    {
        $this->privateMember = $data['privateMember'];
        $this->protectedMember = $data['protectedMember'];
        $this->publicMember = $data['publicMember'];
    }
}
