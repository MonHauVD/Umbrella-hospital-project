<?php

declare(strict_types=1);

namespace Doctrine\Deprecations;

use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;
use PHPUnit\Framework\TestCase;

use function restore_error_handler;
use function set_error_handler;

class VerifyDeprecationsTest extends TestCase
{
    use VerifyDeprecations;

    public function setUp(): void
    {
        set_error_handler(static function (): bool {
            return false;
        });
    }

    public function tearDown(): void
    {
        restore_error_handler();
    }

    public function testExpectDeprecationWithIdentifier(): void
    {
        $this->expectDeprecationWithIdentifier('http://example.com');

        Deprecation::trigger('doctrine/dbal', 'http://example.com', 'message');
    }

    public function testExpectNoDeprecationWithIdentifier(): void
    {
        $this->expectNoDeprecationWithIdentifier('http://example.com');

        Deprecation::trigger('doctrine/dbal', 'http://otherexample.com', 'message');
    }
}
