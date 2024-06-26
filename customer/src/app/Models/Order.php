<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const UNASSIGNED_ORDER_STATUS = 'UNASSIGNED';
    const ASSIGNED_ORDER_STATUS = 'TAKEN';

    protected $table = 'orders';

    /**
     * Update order status from UNASSIGNED to TAKEN if order is not already taken
     *
     * @param int $orderId
     *
     * @return bool
     */
    public function takeOrder($orderId)
    {
        $affectedRows = self::where([
            ['id', '=', $orderId],
            ['status', '=', self::UNASSIGNED_ORDER_STATUS],
        ])
        ->update(['orders.status' => self::ASSIGNED_ORDER_STATUS]);

        return $affectedRows > 0 ? true : false;
    }
}
