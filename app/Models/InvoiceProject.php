<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
@property int $invoice_id invoice id
@property int $project_id project id
@property int $created_at created at

 */
class InvoiceProject extends Model {

	/**
	 * Database table name
	 */
	protected $table = 'invoice_project';

	/**
	 * Mass assignable columns
	 */
	protected $fillable = ['invoice_id',
		'project_id'];

	/**
	 * Date time columns.
	 */
	protected $dates = [];

	public $timestamps = false;


}