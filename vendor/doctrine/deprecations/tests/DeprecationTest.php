<?php

declare(strict_types=1);

namespace Doctrine\Deprecations;

use DeprecationTests\ConstructorDeprecation;
use DeprecationTests\Foo;
use DeprecationTests\RootDeprecation;
use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;
use Doctrine\Foo\Baz;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionProperty;

use function restore_error_handler;
use function set_error_handler;

class DeprecationTest extends TestCase
{
    use VerifyDeprecations;

    public function setUp(): void
    {
        // reset the global state of Deprecation class across tests
        $reflectionProperty = new ReflectionProperty(Deprecation::class, 'ignoredPackages');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, []);

        $reflectionProperty = new ReflectionProperty(Deprecation::class, 'triggeredDeprecations');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, []);

        Deprecation::disable();

        Deprecation::enableTrackingDeprecations();
    }

    public function expectErrorHandler(string $expectedMessage, string $identifier, int $times = 1): void
    {
        set_error_handler(function ($type, $message) use ($expectedMessage, $identifier, $times): bool {
            $this->assertStringMatchesFormat(
                $expectedMessage,
                $message
            );
            $this->assertEquals([$identifier => $times], Deprecation::getTriggeredDeprecations());

            return false;
        });
    }

    public function testDeprecation(): void
    {
        Deprecation::enableWithTriggerError();

        $this->expectDeprecationWithIdentifier('https://github.com/doctrine/deprecations/1234');

        try {
            $this->expectErrorHandler(
                'this is deprecated foo 1234 (DeprecationTest.php:%d called by TestCase.php:%d, https://github.com/doctrine/deprecations/1234, package doctrine/orm)',
                'https://github.com/doctrine/deprecations/1234'
            );

            Deprecation::trigger(
                'doctrine/orm',
                'https://github.com/doctrine/deprecations/1234',
                'this is deprecated %s %d',
                'foo',
                1234
            );

            $this->assertEquals(1, Deprecation::getUniqueTriggeredDeprecationsCount());

            Deprecation::trigger(
                'doctrine/orm',
                'https://github.com/doctrine/deprecations/1234',
                'this is deprecated %s %d',
                'foo',
                1234
            );

            $this->assertEquals(2, Deprecation::getUniqueTriggeredDeprecationsCount());
        } finally {
            restore_error_handler();
        }
    }

    public function testDeprecationWithoutDeduplication(): void
    {
        Deprecation::enableWithTriggerError();
        Deprecation::withoutDeduplication();

        try {
            $this->expectErrorHandler(
                'this is deprecated foo 2222 (DeprecationTest.php:%d called by TestCase.php:%d, https://github.com/doctrine/deprecations/2222, package doctrine/orm)',
                'https://github.com/doctrine/deprecations/2222'
            );

            Deprecation::trigger(
                'doctrine/orm',
                'https://github.com/doctrine/deprecations/2222',
                'this is deprecated %s %d',
                'foo',
                2222
            );

            $this->assertEquals(1, Deprecation::getUniqueTriggeredDeprecationsCount());
            restore_error_handler();

            $this->expectErrorHandler(
                'this is deprecated foo 2222 (DeprecationTest.php:%d called by TestCase.php:%d, https://github.com/doctrine/deprecations/2222, package doctrine/orm)',
                'https://github.com/doctrine/deprecations/2222',
                2
            );

            Deprecation::trigger(
                'doctrine/orm',
                'https://github.com/doctrine/deprecations/2222',
                'this is deprecated %s %d',
                'foo',
                2222
            );

            $this->assertEquals(2, Deprecation::getUniqueTriggeredDeprecationsCount());
        } finally {
            restore_error_handler();
        }
    }

    public function testDisableResetsCounts(): void
    {
        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/deprecations/1234',
            'this is deprecated %s %d',
            'foo',
            1234
        );
        Deprecation::disable();

        $this->assertEquals(0, Deprecation::getUniqueTriggeredDeprecationsCount());
        $this->assertEquals(['https://github.com/doctrine/deprecations/1234' => 0], Deprecation::getTriggeredDeprecations());
    }

    public function expectDeprecationMock(string $message, string $identifier, string $package): LoggerInterface
    {
        $mock = $this->createMock(LoggerInterface::class);
        $mock->method('notice')->with($message, $this->callback(function (array $context) use ($identifier, $package) {
            $this->assertEquals($package, $context['package']);
            $this->assertEquals($identifier, $context['link']);

            return true;
        }));

        return $mock;
    }

    public function testDeprecationWithPsrLogger(): void
    {
        $this->expectDeprecationWithIdentifier('https://github.com/doctrine/deprecations/2222');

        $mock = $this->expectDeprecationMock(
            'this is deprecated foo 1234',
            'https://github.com/doctrine/deprecations/2222',
            'doctrine/orm'
        );
        Deprecation::enableWithPsrLogger($mock);

        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/deprecations/2222',
            'this is deprecated %s %d',
            'foo',
            1234
        );
    }

    public function testDeprecationWithIgnoredPackage(): void
    {
        Deprecation::enableWithTriggerError();
        Deprecation::ignorePackage('doctrine/orm');

        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/orm/issue/1234',
            'this is deprecated %s %d',
            'foo',
            1234
        );

        $this->assertEquals(1, Deprecation::getUniqueTriggeredDeprecationsCount());
        $this->assertEquals(['https://github.com/doctrine/orm/issue/1234' => 1], Deprecation::getTriggeredDeprecations());
    }

    public function testDeprecationWithIgnoredLink(): void
    {
        Deprecation::enableWithTriggerError();
        Deprecation::ignoreDeprecations('https://github.com/doctrine/orm/issue/1234');

        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/orm/issue/1234',
            'this is deprecated %s %d',
            'foo',
            1234
        );

        $this->assertEquals(0, Deprecation::getUniqueTriggeredDeprecationsCount());
        $this->assertEquals([], Deprecation::getTriggeredDeprecations());
    }

    public function testDeprecationIfCalledFromOutside(): void
    {
            Deprecation::enableWithTriggerError();

        try {
            $this->expectErrorHandler(
                'Bar::oldFunc() is deprecated, use Bar::newFunc() instead. (Bar.php:%d called by Foo.php:14, https://github.com/doctrine/foo, package doctrine/foo)',
                'https://github.com/doctrine/foo'
            );

            Foo::triggerDependencyWithDeprecation();

            $this->assertEquals(1, Deprecation::getUniqueTriggeredDeprecationsCount());
        } finally {
            restore_error_handler();
        }
    }

    public function testDeprecationIfCalledFromOutsideNotTriggeringFromInside(): void
    {
        Deprecation::enableWithTriggerError();

        Foo::triggerDependencyWithDeprecationFromInside();

        $this->assertEquals(0, Deprecation::getUniqueTriggeredDeprecationsCount());
    }

    public function testDeprecationIfCalledFromOutsideNotTriggeringFromInsideClass(): void
    {
        Deprecation::enableWithTriggerError();

        $baz = new Baz();
        $baz->usingOldFunc();

        $this->assertEquals(0, Deprecation::getUniqueTriggeredDeprecationsCount());
    }

    public function testDeprecationCalledFromOutsideInRoot(): void
    {
        Deprecation::enableWithTriggerError();

        $this->expectDeprecationWithIdentifier('https://github.com/doctrine/deprecations/4444');

        try {
            $this->expectErrorHandler(
                'this is deprecated foo 1234 (RootDeprecation.php:%d called by DeprecationTest.php:%d, https://github.com/doctrine/deprecations/4444, package doctrine/orm)',
                'https://github.com/doctrine/deprecations/4444'
            );

            RootDeprecation::run();

            $this->assertEquals(1, Deprecation::getUniqueTriggeredDeprecationsCount());
        } finally {
            restore_error_handler();
        }
    }

    public function testDeprecationTrackByEnv(): void
    {
        $reflectionProperty = new ReflectionProperty(Deprecation::class, 'type');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, null);

        Deprecation::trigger('Foo', 'link', 'message');
        $this->assertSame(0, Deprecation::getUniqueTriggeredDeprecationsCount());

        $reflectionProperty->setValue(null, null);
        $_SERVER['DOCTRINE_DEPRECATIONS'] = 'track';

        Deprecation::trigger('Foo', __METHOD__, 'message');
        $this->assertSame(1, Deprecation::getUniqueTriggeredDeprecationsCount());
    }

    public function testDeprecationTriggerByEnv(): void
    {
        $reflectionProperty = new ReflectionProperty(Deprecation::class, 'type');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, null);
        $_ENV['DOCTRINE_DEPRECATIONS'] = 'trigger';

        try {
            $this->expectErrorHandler(
                'message (DeprecationTest.php:%d called by TestCase.php:%d, ' . __METHOD__ . ', package Foo)',
                __METHOD__
            );

            Deprecation::trigger('Foo', __METHOD__, 'message');
            $this->assertSame(1, Deprecation::getUniqueTriggeredDeprecationsCount());
        } finally {
            restore_error_handler();
        }
    }

    public function testDeprecationTriggeredFromNativeCode(): void
    {
        $ref = new ReflectionClass(ConstructorDeprecation::class);

        Deprecation::enableWithTriggerError();
        try {
            $this->expectErrorHandler(
                'This constructor is deprecated. (ConstructorDeprecation.php:%d called by native code:0, https://github.com/doctrine/deprecations/issues/44, package doctrine/bar)',
                'https://github.com/doctrine/deprecations/issues/44'
            );

            $ref->newInstance();
            $this->assertSame(1, Deprecation::getUniqueTriggeredDeprecationsCount());
        } finally {
            restore_error_handler();
        }
    }
}
