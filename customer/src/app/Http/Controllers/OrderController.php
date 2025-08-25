<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
//validate all input parameters
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\AssignOrderRequest;
use App\Http\Requests\ShowOrdersRequest;
use App\Helpers\ResponseHelper;
use App\Http\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{

    /**
     * @var ResponseHelper
     */
    protected $responseHelper;

    /**
     * $orderService
     * @var OrderService
     */
    protected $orderService;

    public function __construct(
        OrderService $orderService,
        ResponseHelper $responseHelper
    ) {
        $this->orderService = $orderService;
        $this->responseHelper = $responseHelper;
    }

    /**
     * function to create new order for provided source and destination
     * validation for proper format for lat long of source and destination
     * if validation failed then raise error
     *
     * @param  $orderRequest
     * @return json
     */
    public function store(CreateOrderRequest $orderRequest)
    {
        $origin = $orderRequest->input('origin');
        $destination = $orderRequest->input('destination');

        try {
            $order = $this->orderService->createNewOrder($origin, $destination);

            if ($order) {
                $orderResponseData = ['id' => $order->id, 'origin' => $order->origin, 'destination' => $order->destination, 'status' => $order->status];

                return $this->responseHelper->sendSuccess('Success', JsonResponse::HTTP_OK, $orderResponseData);
            } else {
                return $this->responseHelper->sendError($this->orderService->error, $this->orderService->errorCode);
            }
        } catch (\Exception $e) {
            return $this->responseHelper->sendError($e->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * [assignOrder to assign order, it will change the order status to taken if successfully assigned]
     * @param  AssignOrderRequest $assignRequest
     * @param  [int]             $id            [Order id]
     * @return [json]
     */
    public function assignOrder($id)
    {
        try {

            $order = $this->orderService->getOrderById($id);
            //if order status is already taken then raise error
            if ($order->status == Order::ASSIGNED_ORDER_STATUS) {
                return $this->responseHelper->sendError('ALREADY_TAKEN', JsonResponse::HTTP_CONFLICT);
            }

            //take order
            if (false === $this->orderService->takeOrder($id)) {
                return $this->responseHelper->sendError('ALREADY_TAKEN', JsonResponse::HTTP_CONFLICT);
            }

            //if successfully assigned then send success message
            return $this->responseHelper->sendSuccess('Success', JsonResponse::HTTP_OK, ["status" => "SUCCESS"]);
        } catch (\Exception $e) {
            return $this->responseHelper->sendError('INVALID_ORDER', JsonResponse::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * [listOrders to retune all orders as per pagination]
     * @return [json] [Order List]
     */
    public function listOrders(ShowOrdersRequest $showOrderRequest)
    {
        try {
            $page = (int) $showOrderRequest->get('page', 1);
            $limit = (int) $showOrderRequest->get('limit', 1);

            $records = $this->orderService->getList($page, $limit);

            $orders = [];
            if ($records && $records->count() > 0) {
                foreach ($records as $order) {
                    $orders[] = ['id' => $order->id, 'origin' => $order->origin, 'destination' => $order->destination, 'status' => $order->status];
                }
            }
            //send orders
            return $this->responseHelper->sendSuccess('Success', JsonResponse::HTTP_OK, $orders);
        } catch (\Exception $e) {
            return $this->responseHelper->sendError($e->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
