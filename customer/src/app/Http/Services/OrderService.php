<?php

namespace App\Http\Services;

use App\Http\Factory\DistanceFactory;
use App\Models\Distance;
use App\Models\Order as OrderModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class OrderService
{
    /**
     * @var null|string
     */
    public $error = null;

    /**
     * @var int
     */
    public $errorCode;


    /**
     * [createNewOrder create new order based on provide locations]
     * @param  [string] $origin
     * @param  [string] $destination
     * @return [jsonResponse]
     */
    public function createNewOrder($origin, $destination)
    {
        //if distance calculated then create order
        $order = new OrderModel();
        $order->status = OrderModel::UNASSIGNED_ORDER_STATUS;
        $order->origin = $origin;
        $order->destination = $destination;

        $order->save();
        return $order;
    }


    /**
     * Fetches list of order in system using given limit and page variable
     *
     * @param int $page
     * @param int $limit
     *
     * @return array
     */
    public function getList($page, $limit)
    {
        $page = (int) $page;
        $limit = (int) $limit;
        $orders = [];

        if ($page > 0 && $limit > 0) {
            $skip = ($page -1) * $limit;
            $orders = (new OrderModel())->skip($skip)->take($limit)->orderBy('id', 'asc')->get();
        }

        return $orders;
    }

    /**
     * Fetches Order model based on primary key provided
     *
     * @param int $id
     *
     * @return OrderModel
     */
    public function getOrderById($id)
    {
        return OrderModel::findorfail($id);
    }

    /**
     * Mark an order as TAKEN, if not already
     *
     * @param int $orderId
     *
     * @return bool
     */
    public function takeOrder($orderId)
    {
        $order = new OrderModel();

        return $order->takeOrder($orderId);
    }
}
