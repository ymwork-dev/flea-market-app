<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // テストではViteを使わない（CSS/JSの読み込みはテストの対象外のため）
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }
}
