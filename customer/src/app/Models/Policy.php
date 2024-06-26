<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    const COMPREHENSIVE = 'COMPREHENSIVE';
    const THIRD_PARTY = '3RD_PARTY';

    protected $table = 'policy_details';

    /**
     * Update order status from UNASSIGNED to TAKEN if order is not already taken
     *
     * @param int $orderId
     *
     * @return bool
     */
    public function getPolicyDetails($policyNumber)
    {
        $policyDetails = self::where('policy_number', $policyNumber)->first();

        return $policyDetails ?? false;
    }
}
