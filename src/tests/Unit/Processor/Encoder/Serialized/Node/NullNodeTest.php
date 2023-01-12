<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Node;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\NullNode;

class NullNodeTest extends TestCase
{
    public function testGetContentReturnsNull(): void
    {
        $this->assertEquals(
            null,
            (new NullNode())->getContent()
        );
    }

    public function testGetValueReturnsNull(): void
    {
        $this->assertEquals(
            null,
            (new NullNode())->getValue()
        );
    }
}
