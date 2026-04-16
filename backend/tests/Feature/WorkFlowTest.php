<?php

namespace Tests\Feature;

use Tests\TestCase;

class WorkFlowTest extends TestCase
{
    public function test_api_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
