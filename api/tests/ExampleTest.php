<?php

class ExampleTest extends \PHPUnit\Framework\TestCase //vendor\phpunit\phpunit\src\Framework\TestCase.php
{
    // public function testTrue()
    // {
    //     $this->assertTrue(true);
    // }
    public function testTwoString()
    {
        $string1 = 'vietdung';
        $string2 = 'vietdung';
        // $this->assertSame($string1, $string2);
        $this->assertTrue($string1 == $string2);
    }
    public function testTwoString2()
    {
        $string1 = 'vietdung';
        $string2 = 'vietdung';
        $this->assertTrue($string1 != $string2);
    }

    public function tetProduct()
    {
        require 'function-to-test\example-funtions.php';
        $this->assertSame(6, product(2, 3));
        $this->assertSame(0, product(0, 3));
        $this->assertSame(0, product(2, 0));
        $this->assertSame(1, product(0, 0));
    }
}