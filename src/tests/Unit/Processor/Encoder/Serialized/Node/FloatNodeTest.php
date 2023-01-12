<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Node;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\FloatNode;

class FloatNodeTest extends TestCase
{
    public function testGetContentReturnsFloat(): void
    {
        $this->assertEquals(
            4.2,
            (new FloatNode(4.2))->getContent()
        );
    }

    public function testGetValueReturnsFloat(): void
    {
        $this->assertEquals(
            4.2,
            (new FloatNode(4.2))->getValue()
        );
    }
}
