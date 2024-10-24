<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

abstract class LibsTestCase extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function getMockResponse($content = [], $statusCode = 200, $headers = [])
    {
        $jsonString = json_encode($content);
        return new Response($statusCode, $headers, $jsonString);
    }
}
