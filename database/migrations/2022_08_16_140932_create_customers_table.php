<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('companyID')->nullable();
            $table->string('companyName')->nullable();;
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('phoneNumber')->nullable();
            $table->string('accountingEmailAddress')->nullable();

            $table->string('billingStreetName')->nullable();
            $table->string('billingStreetNumber')->nullable();
            $table->string('billingZipcode')->nullable();
            $table->string('billingCity')->nullable();
            $table->string('billingCountry')->nullable();
            $table->text('billingAdditionalInfo')->nullable();

            $table->string('shippingStreetName')->nullable();
            $table->string('shippingStreetNumber')->nullable();
            $table->string('shippingZipcode')->nullable();
            $table->string('shippingCity')->nullable();
            $table->string('shippingCountry')->nullable();
            $table->text('shippingAdditionalInfo')->nullable();

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
        Schema::dropIfExists('customers');
    }
}
