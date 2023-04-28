<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_profile', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('company_name')->nullable();
            $table->string('authorised_person_name')->nullable();
            $table->string('company_number')->nullable();
            $table->string('company_phone_number')->nullable();
            $table->string('company_address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_company_name')->nullable();
            $table->string('bank_address')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc_code')->nullable();
            $table->string('bank_swift_code')->nullable();
            $table->string('signature_image')->nullable();
            $table->string('default_tax')->nullable();
            $table->string('invoice_prefix')->nullable();
            $table->string('invoice_series')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('company_profile');
    }
}
