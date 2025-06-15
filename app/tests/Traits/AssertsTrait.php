<?php

namespace App\Tests\Traits;

trait AssertsTrait
{
    public function assertJsonStructure(array $structure, array $jsonData): void
    {
        foreach ($structure as $key => $value) {
            if (is_array($value)) {
                $this->assertArrayHasKey($key, $jsonData);
                $this->assertJsonStructure($value, $jsonData[$key]);
            } else {
                $this->assertArrayHasKey($value, $jsonData);
            }
        }
    }
}
