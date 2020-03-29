<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->bigInteger('external_id');
            $table->enum('status', ['inactive', 'active', 'uninstall']);
            $table->string('email')->nullable();
            $table->string('domain');
            $table->string('platform_domain');
            $table->string('platform_type');
            $table->string('currency')->nullable();
            $table->string('timezone')->nullable();
            $table->string('access_token')->nullable();
            $table->timestamps();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shops');
    }
}
