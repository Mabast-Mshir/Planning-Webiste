<?php 

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testProducerFirst(): string
    {
        $this->assertTrue(true);

        return 'first';
    }

    public function testProducerSecond(): string
    {
        $this->assertTrue(true);

        return 'second';
    }
    public function user(string $a, string $b): void
    {
        $this->assertSame('first', $a);
        $this->assertSame('second', $b);
    }
}