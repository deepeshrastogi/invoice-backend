<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatefieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('field_title', 50)->nullable();
            $table->text('field_description')->nullable();
            $table->integer('field_quantity')->nullable();
            $table->integer('field_rate')->nullable();
            $table->integer('field_amount')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamp('field_created_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fields');
    }
}
