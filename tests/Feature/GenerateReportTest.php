<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenerateReportTest extends TestCase
{
    public function testGenerateCommand()
{
    $this->artisan('generate:report')
        ->expectsQuestion('Student ID','student1')
        ->expectsQuestion('Report to generate (1 for Diagnostic, 2 for Progress, 3 for Feedback)',1)
        ->assertExitCode(0);

}
}
