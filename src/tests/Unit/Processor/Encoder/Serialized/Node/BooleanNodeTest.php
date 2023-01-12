<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Node;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\BooleanNode;

class BooleanNodeTest extends TestCase
{
    public function testGetContentReturnsTrue(): void
    {
        $this->assertEquals(
            true,
            (new BooleanNode(true))->getContent()
        );
    }

    public function testGetValueReturnsTrue(): void
    {
        $this->assertEquals(
            true,
            (new BooleanNode(true))->getValue()
        );
    }

    public function testGetContentReturnsFalse(): void
    {
        $this->assertEquals(
            false,
            (new BooleanNode(false))->getContent()
        );
    }

    public function testGetValueReturnsFalse(): void
    {
        $this->assertEquals(
            false,
            (new BooleanNode(false))->getValue()
        );
    }
}
