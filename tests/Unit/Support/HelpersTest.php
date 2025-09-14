<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    public function testEnvReturnsSetValue(): void
    {
        $_ENV['FOO'] = 'bar';
        putenv('FOO=bar');

        $this->assertSame('bar', env('FOO'));
    }

    public function testEnvReturnsDefaultWhenMissing(): void
    {
        unset($_ENV['MISSING'], $_SERVER['MISSING']);
        putenv('MISSING');

        $this->assertSame('default', env('MISSING', 'default'));
    }

    public function testEnvCastsBoolean(): void
    {
        $_ENV['APP_DEBUG'] = 'true';
        putenv('APP_DEBUG=true');

        $this->assertTrue(env('APP_DEBUG'));
    }

    public function testArrayGetReturnsNestedValue(): void
    {
        $data = ['a' => ['b' => ['c' => 'value']]];

        $this->assertSame('value', array_get($data, 'a.b.c'));
    }

    public function testArrayGetReturnsDefaultForMissingKey(): void
    {
        $data = ['a' => ['b' => ['c' => 'value']]];

        $this->assertSame('default', array_get($data, 'a.b.d', 'default'));
    }
}
