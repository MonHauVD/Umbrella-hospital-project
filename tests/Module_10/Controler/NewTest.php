<?php
use PHPUnit\Framework\TestCase;

class NewTest extends TestCase
{
    public function testEqualString()
    {
        $string1 = "Hello, World!";
        $string2 = "Hello, World!";

        // Use assertEquals to compare the strings
        $this->assertEquals($string1, $string2, "The strings are not equal.");
    }
}