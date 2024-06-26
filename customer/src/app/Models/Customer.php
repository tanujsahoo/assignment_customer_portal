<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillables = ['name', 'address', 'email', 'mobile', 'status'];

    protected $table = 'customer';

    /**
     * Update order status from UNASSIGNED to TAKEN if order is not already taken
     *
     * @param int $orderId
     *
     * @return bool
     */
    public function updateDetails($customerDetails, $custId)
    {
        $customer = self::where('id', $custId)->first();

        if (!$customer) {
            $customer = new self;
        }
        foreach ($customerDetails as $k => $v) {
            if (in_array($k, $this->fillables)) {
                $customer->{$k} = $v;
            }
        }
        
        $customer->save();

        return $customer;
    }
}
