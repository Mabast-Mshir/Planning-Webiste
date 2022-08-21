<?php
use PHPUnit\Framework\TestCase;

class DependTest extends TestCase
{
    public function testEmpty()
    {
        $value = [];
        $this->assertEmpty($value);
        return $value;
    }
    public function testPush(array $value)
    {
        array_push($value, 'first');
        $this->assertEquals('first', $value[count($value) - 1]);
        $this->assertNotEmpty($value);
        return $value;
    }
}

?>