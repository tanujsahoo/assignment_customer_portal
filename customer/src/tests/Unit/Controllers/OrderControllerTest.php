<?php
namespace Tests\Unit\Controllers;

use Tests\TestCase;
use App\Http\Controllers\OrderController;
use App\Models\Order;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\JsonResponse;
// use Mockery;

class OrderControllerTest extends TestCase
{
    use WithoutMiddleware;

    protected static $allowedOrderStatus = [
        Order::UNASSIGNED_ORDER_STATUS,
        Order::ASSIGNED_ORDER_STATUS,
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
        $this->orderServiceMock = \Mockery::mock(\App\Http\Services\OrderService::class);

        $this->pathPrefix = "/api";
        $this->responseHelper = \App::make(\App\Helpers\ResponseHelper::class);

         $this->app->instance(OrderController::class,
            new OrderController(
                $this->orderServiceMock,
                $this->responseHelper
            )
        );
    }

    public function tearDown(): void
    {
        \Mockery::close();
    }

    public function testStore_PositiveTestCase()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method::store - with  ---  Postive Test Case --- *** \n";

        $order = $this->generateRandomOrder();

        $params = [
            'origin' => 'delhi',
            'destination' => 'gurgaon'
        ];

        //Order Service will return success
        $this->orderServiceMock
            ->shouldReceive('createNewOrder')
            ->with($params['origin'], $params['destination'])
            ->once()
            ->andReturn($order);

        $response = $this->call('POST', $this->pathPrefix.'/orders', $params);
        $data = json_decode($response->getContent(), true);
        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $data);
    }

    public function testStore_NegativeTestCase_NoOrigin()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:store - with --- Negative Test Case (No input origin param) --- *** \n";

        $order = $this->generateRandomOrder();

        $params = [
            //'origin' => [strval($this->faker->latitude()), strval($this->faker->longitude())],
            'destination' => 'loc3'
        ];

        //Order Service will return failure
        $this->orderServiceMock
            ->shouldReceive('createOrder')
            ->andReturn(false);

        $this->orderServiceMock->error = 'INVALID_PARAMETERS';
        $this->orderServiceMock->errorCode = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;

        $response = $this->call('POST', $this->pathPrefix.'/orders', $params);
        $data = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * [testAssignOrderValid provide valid order id to test]
     */
    public function testAssignOrder_PositiveTestCase(){
        echo "\n *** Unit Test - Controller::OrderController - Method:assignOrder with --- PositiveTestCase --- *** \n";

        $id = $this->faker->numberBetween(1, 9999);

        $order = $this->generateRandomOrder($id);

        //update order status as "UNASSIGNED"
        $order->status = Order::UNASSIGNED_ORDER_STATUS;

        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->once()
            ->with($id)
            ->andReturn($order);

        $this->orderServiceMock
            ->shouldReceive('takeOrder')
            ->once()
            ->with($id)
            ->andReturn(true);

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', $this->pathPrefix."/orders/{$id}", $params);
        $data = json_decode($response->getContent(), true);

        $response->assertStatus(JsonResponse::HTTP_OK);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('SUCCESS', $data['status']);
    }


    public function testAssignOrderInValid_NegativeCase_invalidParamater(){
        echo "\n *** Unit Test - Controller::OrderController - Method:assignOrder with --- NegativeCase (Invalid Input Parameter) --- *** \n";

        $id = $this->faker->numberBetween(1, 9999);

        $order = $this->generateRandomOrder($id);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->with($id)
            ->andReturn(true);

        $params = ['status' => 'ASSIGNED'];

        $response = $this->call('PATCH', $this->pathPrefix."/orders/{$id}", $params);
        $data = json_decode($response->getContent(), true);

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('STATUS_IS_INVALID', $data['error']);
    }


    public function testAssignOrder_NegativeTestCase_invalidId()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:assignOrder with --- NegativeTestCase (Invalid id) --- *** \n";

        $id = $this->faker->numberBetween(499999, 999999);

        $order = $this->generateRandomOrder($id);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->once()
            ->with($id)
            ->andThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', $this->pathPrefix."/orders/{$id}", $params);
        $data = json_decode($response->getContent(), true);
        $response->assertStatus(JsonResponse::HTTP_EXPECTATION_FAILED);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('INVALID_ID', $data['error']);
    }

    public function testAssignOrder_NegativeTestCase_AlreadyTaken()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:assignOrder with --- NegativeTestCase (Already Taken) --- *** \n";

        $id = $this->faker->numberBetween(1, 9999);

        $order = $this->generateRandomOrder($id);

        //status should already taken
        $order->status = Order::ASSIGNED_ORDER_STATUS;

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->once()
            ->with($id)
            ->andReturn($order);

        $params = ['status' => 'TAKEN'];

        $response = $this->call('PATCH', $this->pathPrefix."/orders/{$id}", $params);
        $data = json_decode($response->getContent(), true);

        $response->assertStatus(JsonResponse::HTTP_CONFLICT);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('ORDER_ALREADY_BEEN_TAKEN', $data['error']);
    }

    public function testListOrders_PositiveTestCase()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:listOrders - with --- PositiveTestCase --- *** \n";

        $page = 1;
        $limit = 5;

        $orderList = [];

        for ($i=0; $i < 5; $i++) {
            $orderList[] = $this->generateRandomOrder();
        }

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection($orderList);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getList')
            ->once()
            ->with($page, $limit)
            ->andReturn($orderRecordCollection);

        $params = ['page' => $page, 'limit' => $limit];

        $response = $this->call('GET', $this->pathPrefix."/orders", $params);
        $data = json_decode($response->getContent(), true);

        $response->assertStatus(JsonResponse::HTTP_OK);

        $this->assertIsArray($data);

        $this->assertArrayHasKey('id', (array) $data[0]);
        $this->assertArrayHasKey('status', (array) $data[0]);
    }

    public function testListOrders_PositiveTestCase_Nodata()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:listOrders - PositiveTestCase (No Param) *** \n";

        $page = 599999;
        $limit = 5;

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection([]);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getList')
            ->once()
            ->andReturn($orderRecordCollection);

        $params = ['page' => $page, 'limit' => $limit];

        $response = $this->call('GET', $this->pathPrefix."/orders", $params);

        $response->assertStatus(JsonResponse::HTTP_OK);
    }


    public function testListOrders_NegativeTestCase_inValidPage()
    {
        echo "\n *** Unit Test - Controller::OrderController - Method:listOrders - with --- NegativeTestCase (Invalid Page Param) *** \n";

        $page = 'A';
        $limit = 5;

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection([]);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getList')
            ->andReturn($orderRecordCollection);

        $params = ['page' => $page, 'limit' => $limit];

        $response = $this->call('GET',  $this->pathPrefix."/orders", $params);

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }



    /**
     * @param int|null $id
     *
     * @return Order
     */
    private function generateRandomOrder($id = null)
    {
        $id = $id?:$this->faker->randomDigit();

        $order = new Order();
        $order->id = $id;
        $order->status = $this->faker->randomElement(self::$allowedOrderStatus);
        $order->origin = 'Gurgaon';
        $order->destination = 'Noida';
        $order->created_at = $this->faker->dateTimeBetween();
        $order->updated_at = $this->faker->dateTimeBetween();

        return $order;
    }

}
