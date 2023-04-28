<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_number', 255)->nullable()->unique('invoice_number');
            $table->bigInteger('user_id')->nullable();
            $table->dateTime('invoice_generated_date')->nullable();
            $table->dateTime('invoice_active_from')->nullable();
            $table->dateTime('invoice_active_to')->nullable();
            $table->dateTime('invoice_email_start_from')->nullable();
            $table->tinyInteger('invoice_is_recurring')->nullable();
            $table->enum('invoice_recurring_after', ['0', '1', '2', '3','4'])->default('0')->comment("1(15 days), 2(30 days),3(45 days), 4(60 days)");
            $table->string('invoice_signature', 255)->nullable();
            $table->boolean('is_invoice_signature_associated')->nullable();
            $table->text('invoice_bank_details')->nullable();
            $table->text('invoice_notes')->nullable();
            $table->tinyInteger('invoice_is_draft')->nullable();
            $table->tinyInteger('invoice_active')->nullable();
            $table->integer('invoice_tax_percentage')->nullable();
            $table->timestamp('invoice_created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice');
    }
}
