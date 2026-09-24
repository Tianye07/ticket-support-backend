<?php

namespace Tests;

use App\Constants\GeneralConstants;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // RefreshDatabase wipes every table, so never run tests against a non-test database
        // (e.g. when the config is cached and phpunit.xml's DB_DATABASE is ignored)
        $database = (string) $app['config']->get('database.connections.'.GeneralConstants::DB_NAME.'.database');

        if (! str_ends_with($database, '_test')) {
            throw new RuntimeException("Refusing to run tests against database [{$database}]. Its name must end with \"_test\" (see phpunit.xml).");
        }

        return $app;
    }
}
