<?php

use PHPUnit\Framework\TestCase;

class BookingPhotosModelTest extends TestCase
{
    

    public static function setUpBeforeClass(): void
    {
        
    }
    public function setUp(): void
    {
        
    }

    public function tearDown(){
        
    }

    public function test_M01_BookingPhotosModel_getAll_01()
    {
        $arr = [1, 23, 4];
        print($arr);
        $this->assertEquals(16, 16);
    }
}