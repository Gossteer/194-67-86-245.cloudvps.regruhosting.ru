<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testSaveChat()
    {
        $response = $this->post('/save-chat', [
            'id' => 1,
            'token' => 'test'
        ]);

        $response->assertStatus(200);
    }
}
