<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customers extends Model {
	use HasFactory, SoftDeletes;
	protected $fillable = ['companyID', 'user_id', 'companyName', 'firstName', 'lastName', 'phoneNumber', 'accountingEmailAddress','billingStreetName','billingStreetNumber','billingZipcode','billingCity','billingCountry','shippingStreetName','shippingStreetNumber','shippingZipcode',
	'shippingCity','shippingCountry','billingAdditionalInfo','shippingAdditionalInfo'];
	
	protected $dates = ['deleted_at'];
	/**
	 * user
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user() {
		return $this->belongsTo(User::class, 'user_id');
	}

}
