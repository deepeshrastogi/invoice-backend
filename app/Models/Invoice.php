<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
@property varchar $invoice_number invoice number
@property datetime $invoice_generated_date invoice generated date
@property datetime $invoice_active_from invoice active from
@property datetime $invoice_active_to invoice active to
@property datetime $invoice_email_start_from invoice email start from
@property tinyint $invoice_is_recurring invoice is recurring
@property enum $invoice_recurring_after invoice recurring after
@property varchar $invoice_signature invoice signature
@property tinyint $is_invoice_signature_associated is invoice signature associated
@property text $Invoice_bank_details Invoice bank details
@property text $invoice_notes invoice notes
@property tinyint $invoice_is_draft invoice is draft
@property tinyint $invoice_active invoice active
@property int $invoice_tax_percentage invoice tax percentage
@property datetime $invoice_created_at invoice created at
@property \Illuminate\Database\Eloquent\Collection $emailed belongsToMany

 */
class Invoice extends Model {
	const INVOICE_RECURRING_AFTER_0 = '0';

	const INVOICE_RECURRING_AFTER_1 = '1';

	const INVOICE_RECURRING_AFTER_2 = '2';

	const INVOICE_RECURRING_AFTER_3 = '3';

	/**
	 * Database table name
	 */
	protected $table = 'invoice';

	public $timestamps = false;
	protected $hidden = ['pivot'];

	/**
	 * Mass assignable columns
	 */
	protected $fillable = [
		'id',
		'user_id',
		'invoice_created_at',
		'invoice_number',
		'invoice_generated_date',
		'invoice_active_from',
		'invoice_active_to',
		'invoice_email_start_from',
		'invoice_is_recurring',
		'invoice_recurring_after',
		'invoice_signature',
		'is_invoice_signature_associated',
		'invoice_bank_details',
		'invoice_notes',
		'invoice_is_draft',
		'invoice_active',
		'amount',
		'invoice_tax_percentage',
		'invoice_created_at'];

	/**
	 * Date time columns.
	 */
	protected $dates = ['invoice_generated_date',
		'invoice_active_from',
		'invoice_active_to',
		'invoice_email_start_from',
		'invoice_created_at'];

	/**
	 * emaileds
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function emaileds() {
		return $this->hasMany(InvoiceEmailed::class);
	}

	public function customer()
    {
        return $this->belongsToMany(Customers::class);
    }
	public function project()
    {
		 return $this->belongsToMany(Project::class, "invoice_project", 'invoice_id', 'project_id');
    }


}