<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PolicyDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('policy_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('policy_number');
            $table->string('vechicle_number');
            $table->string('vehicle_model');
            $table->enum('policy_type',['COMPREHENSIVE', '3RD_PARTY']);
            $table->integer('customer_id');
            $table->date('start_date');
            $table->date('expiry_date');
            $table->string('payment_cycle');
            $table->date('next_payment_due_date');
            $table->boolean('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('policy_details');
    }
}
