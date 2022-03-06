<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->mediumText('abstract')->nullable();
            $table->string('address');
            $table->string('phone_number', 10);
            $table->string('e_mail')->nullable();
            $table->longText('description');
            $table->json('facilities');
            $table->time('regular_checkin');
            $table->time('regular_checkout');
            $table->unsignedInteger('num_of_rooms')->default(1);
            $table->json('tags');
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
        Schema::dropIfExists('hotels');
    }
}
