<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures;

class SimpleObject
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
}
