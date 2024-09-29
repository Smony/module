<?php

namespace Smony\Module\Tests\Feature;

use Orchestra\Testbench\TestCase;

class MakeModuleCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Smony\Module\Providers\ModuleServiceProvider::class];
    }

    /** @test */
    public function it_creates_a_module()
    {
        $this->artisan('make:module TestModule')
            ->expectsOutput('Module TestModule created successfully.')
            ->assertExitCode(0);
    }
}
