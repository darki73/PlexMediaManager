<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateIntegrationsTable
 */
class CreateIntegrationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void {
        Schema::create('integrations', static function (Blueprint $table) {
            $table->string('integration')->unique();
            $table->boolean('enabled')->default(0);
            $table->json('configuration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void {
        Schema::dropIfExists('integrations');
    }
}
