<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Tests must not depend on a built Vite manifest (fresh clones, CI).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
