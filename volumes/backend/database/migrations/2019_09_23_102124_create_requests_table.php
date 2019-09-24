<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateRequestsTable
 */
class CreateRequestsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('requests', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('request_type'); // 0 - Series, 1 - Movie
            $table->string('title');
            $table->unsignedInteger('year');
            $table->unsignedInteger('status')->default(0); // 0 - Request Created | 1 - Request Approved | 2 - Request Declined | 3 - Request Completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('requests');
    }

}
