<?php
declare(strict_types=1);

namespace Ankor\Tests;

use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\Console\Application;

/**
 * TestCase
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Actions to perform after each test.
     */
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return Application|null
     */
    public function getApplication(): ?Application
    {
        return new Application;
    }
}
