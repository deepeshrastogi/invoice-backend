<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndAmountToInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->integer('amount')->after('invoice_active')->nullable();;
            $table->enum('currency', ['0', '1', '2'])->default('0')->after('amount')->comment("0(Euro), 1(USD))")->nullable();;
            $table->enum('paying_status', ['0', '1', '2'])->default('0')->after('currency')->comment("0(unpaid), 1(paid),2(overdue)")->nullable();
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('currency');
            $table->dropColumn('paying_status');
            //
        });
    }
}
