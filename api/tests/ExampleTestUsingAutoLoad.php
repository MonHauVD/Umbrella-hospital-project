<?php
// namespace App\Tests;
class ExampleTestUsingAutoLoad extends \PHPUnit\Framework\TestCase //vendor\phpunit\phpunit\src\Framework\TestCase.php
{
    public function tetProduct()
    {
        require 'function-to-test\example-funtions.php';
        $this->assertSame(6, product(2, 3));
        $this->assertSame(0, product(0, 3));
        $this->assertSame(0, product(2, 0));
        $this->assertSame(1, product(0, 0));
    }
}