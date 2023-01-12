<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Node;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\RecursionByReferenceNode;

class RecursionByReferenceNodeTest extends TestCase
{
    public function testGetContentReturnsInteger(): void
    {
        $this->assertEquals(
            2,
            (new RecursionByReferenceNode(2))->getContent()
        );
    }

    public function testGetValueReturnsInteger(): void
    {
        $this->assertEquals(
            2,
            (new RecursionByReferenceNode(2))->getValue()
        );
    }
}
