<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
@property int $customers_id customers id
@property int $invoice_id invoice id
@property datetime $created_at created at

 */
class CustomersInvoice extends Model {

	/**
	 * Database table name
	 */
	protected $table = 'customers_invoice';

	/**
	 * Mass assignable columns
	 */
	protected $fillable = ['customers_id',
		'invoice_id'];

	/**
	 * Date time columns.
	 */
	protected $dates = [];

}