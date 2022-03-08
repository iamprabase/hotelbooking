<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_room_detail_id');
            $table->foreign('customer_room_detail_id')->references('id')->on('customer_room_details');
            $table->boolean('has_cancelled')->default(0);
            $table->dateTime('booking_datetime');
            $table->dateTime('booking_cancellation_datetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_history');
    }
}
