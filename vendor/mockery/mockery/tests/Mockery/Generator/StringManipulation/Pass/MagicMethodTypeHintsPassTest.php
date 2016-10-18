<?php
/**
 * Mockery
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://github.com/padraic/mockery/master/LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to padraic@php.net so we can send you a copy immediately.
 *
 * @category   Mockery
 * @package    Mockery
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2010 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://github.com/padraic/mockery/blob/master/LICENSE New BSD License
 */

declare(strict_types=1);

namespace Mockery\Test\Generator\StringManipulation\Pass;

use Mockery as m;
use Mockery\Generator\DefinedTargetClass;
use Mockery\Generator\StringManipulation\Pass\MagicMethodTypeHintsPass;

class MagicMethodTypeHintsPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MagicMethodTypeHintsPass
     */
    private $pass;

    /**
     * @var MockConfiguration
     */
    private $mockedConfiguration;

    /**
     * Setup method
     * @return void
     */
    public function setup()
    {
        $targetClass = DefinedTargetClass::factory(
            'Mockery\Test\Generator\StringManipulation\Pass\MagicDummy'
        );
        $this->pass = new MagicMethodTypeHintsPass;
        $this->mockedConfiguration = m::mock(
            'Mockery\Generator\MockConfiguration'
        );
        $this->mockedConfiguration
             ->shouldReceive('getTargetClass')
             ->andReturn($targetClass)
             ->byDefault();
    }

    /**
     * @test
     */
    public function itShouldWork()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function itShouldGrabClassMagicMethods()
    {
        $targetClass = DefinedTargetClass::factory(
            'Mockery\Test\Generator\StringManipulation\Pass\MagicDummy'
        );
        $magicMethods = $this->pass->getMagicMethods($targetClass);

        $this->assertCount(6, $magicMethods);
        $this->assertEquals('__isset', $magicMethods[0]->getName());
    }

    /**
     * @test
     */
    public function itShouldAddStringTypeHintOnMagicMethod()
    {
        $code = $this->pass->apply(
            'public function __isset($name) {}',
            $this->mockedConfiguration
        );
        $this->assertContains('string $name', $code);
    }

    /**
     * @test
     */
    public function itShouldAddBooleanReturnOnMagicMethod()
    {
        $code = $this->pass->apply(
            'public function __isset($name) {}',
            $this->mockedConfiguration
        );
        $this->assertContains(' : bool', $code);
    }

    /**
     * @test
     */
    public function itShouldAddTypeHintsOnToStringMethod()
    {
        $code = $this->pass->apply(
            'public function __toString() {}',
            $this->mockedConfiguration
        );
        $this->assertContains(' : string', $code);
    }

    /**
     * @test
     */
    public function itShouldAddTypeHintsOnCallMethod()
    {
        $code = $this->pass->apply(
            'public function __call($method, array $args) {}',
            $this->mockedConfiguration
        );
        $this->assertContains('string $method', $code);
    }

    /**
     * @test
     */
    public function itShouldAddTypeHintsOnCallStaticMethod()
    {
        $code = $this->pass->apply(
            'public static function __callStatic($method, array $args) {}',
            $this->mockedConfiguration
        );
        $this->assertContains('string $method', $code);
    }

    /**
     * @test
     */
    public function itShouldNotAddReturnTypeHintIfOneIsNotFound()
    {
        $targetClass = DefinedTargetClass::factory(
            'Mockery\Test\Generator\StringManipulation\Pass\MagicReturnDummy'
        );
        $this->mockedConfiguration
             ->shouldReceive('getTargetClass')
             ->andReturn($targetClass)
             ->byDefault();

        $code = $this->pass->apply(
            'public static function __isset($parameter) {}',
            $this->mockedConfiguration
        );
        $this->assertContains(') {', $code);
    }

    /**
     * @test
     */
    public function itShouldReturnEmptyArrayIfClassDoesNotHaveMagicMethods()
    {
        $targetClass = DefinedTargetClass::factory(
            '\StdClass'
        );
        $magicMethods = $this->pass->getMagicMethods($targetClass);
        $this->assertInternalType('array', $magicMethods);
        $this->assertEmpty($magicMethods);
    }

    /**
     * @test
     */
    public function itShouldReturnEmptyArrayIfClassTypeIsNotExpected()
    {
        $magicMethods = $this->pass->getMagicMethods(null);
        $this->assertInternalType('array', $magicMethods);
        $this->assertEmpty($magicMethods);
    }

    /**
     * Tests if the pass correclty replaces all the magic
     * method parameters with those found in the
     * Mock class. This is made to avoid variable
     * conflicts withing Mock's magic methods
     * implementations.
     *
     * @test
     */
    public function itShouldGrabAndReplaceAllParametersWithTheCodeStringMatches()
    {
        $code = $this->pass->apply(
            'public function __call($method, array $args) {}',
            $this->mockedConfiguration
        );
        $this->assertContains('$method', $code);
        $this->assertContains('array $args', $code);
    }
}

class MagicDummy
{
    public function __isset(string $name) : bool
    {
        return false;
    }

    public function __toString() : string
    {
        return '';
    }

    public function __wakeup()
    {
    }

    public function __destruct()
    {
    }

    public function __call(string $name, array $arguments) : string
    {
    }

    public static function __callStatic(string $name, array $arguments) : int
    {
    }

    public function nonMagicMethod()
    {
    }
}

class MagicReturnDummy
{
    public function __isset(string $name)
    {
        return false;
    }
}
