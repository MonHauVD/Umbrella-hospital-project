<?php

namespace App\Tests;//Dung khi da ap dung autoload

use App\Cart;//Dung khi da ap dung autoload
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    public function testGetNetPrice()
    {
        // require 'function-to-test/Cart.php'; //chi dung khi chua ap dung autoload
        $cart = new Cart();
        $cart->price = 10;
        $netPrice = $cart->getNetPrice();
        $this->assertEquals(12, $netPrice);
    }
}