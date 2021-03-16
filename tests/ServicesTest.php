<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ServicesTest extends TestCase
{
    public function testKeyValuesAreCreated(): void
    {
        $result = \Mon\Oversight\inc\Services::createKeyValueHeadersArray([
            'HTTP/2 200',
            'foo: bar',
            'test: something'
        ]);
        $this->assertArrayHasKey('foo', $result);
    }
}