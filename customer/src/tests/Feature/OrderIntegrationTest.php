<?php

namespace App\Test\Feature\ApiController;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;

class OrderIntegrationTest extends TestCase
{

    protected $path;

    public function setUp(): void {
        parent::setUp();
        $this->path = '/api/orders';
    }

    public function testNewOrderCreateIncorrectParameters()
    {
        echo "\n *** Starting Integration Test Cases *** \n";

        echo "\n *** Starting Order Create Scenario *** \n";

        echo "\n > Order Create - Negative Test - With Invalid Parameter Keys - Should get 422 - Unprocessable Entity";

        $invalidData1 = [
            'origin1' => 'Delhi',
            'destination' => 'Bombay',
        ];

        $response = $this->json('POST', $this->path, $invalidData1);

        $response->assertStatus(422);
    }

    public function testOrderCreationPositiveScenario()
    {
        echo "\n\n\n > Order Create Positive Test - Valid Data ";

        $validData = [
            'origin' => 'Delhi',
            'destination' => 'Bombay',
        ];

        $response = $this->json('POST', $this->path, $validData);
        $data = (array) $response->getData();

        echo "\n\t > should have status 200";
        $response->assertStatus(200);

        echo "\n\t > Response should have order details - id, status";

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('status', $data);
    }

    public function testOrderListSuccessData()
    {
        echo "\n\n > Order Listing Positive Test - Valid Data Keys (page=1&limit=4)";

        $query = 'page=1&limit=4';
        $response = $this->json('GET', $this->path."?$query", []);
        $data = (array) $response->getData();

        echo "\t > Status should be 200\n";
        $response->assertStatus(200);

        foreach ($data as $order) {
            $order = (array) $order;
            $this->assertArrayHasKey('id', $order);
            $this->assertArrayHasKey('status', $order);
        }
    }

    protected function orderListFailure($query, $expectedCode)
    {
        $response = $this->json('GET', "/orders?$query", []);
        $data = (array) $response->getData();

        $response->assertStatus($expectedCode);
    }
}
