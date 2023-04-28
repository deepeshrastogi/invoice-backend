<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
@property bigint $companyID companyID
@property bigint $user_id user id
@property varchar $companyName companyName
@property varchar $firstName firstName
@property varchar $lastName lastName
@property varchar $phoneNumber phoneNumber
@property varchar $accountingEmailAddress accountingEmailAddress
@property varchar $billingStreetName billingStreetName
@property varchar $billingStreetNumber billingStreetNumber
@property varchar $billingZipcode billingZipcode
@property varchar $billingCity billingCity
@property varchar $billingCountry billingCountry
@property text $billingAdditionalInfo billingAdditionalInfo
@property varchar $shippingStreetName shippingStreetName
@property varchar $shippingStreetNumber shippingStreetNumber
@property varchar $shippingZipcode shippingZipcode
@property varchar $shippingCity shippingCity
@property varchar $shippingCountry shippingCountry
@property text $shippingAdditionalInfo shippingAdditionalInfo
@property timestamp $created_at created at
@property timestamp $updated_at updated at
@property timestamp $deleted_at deleted at
@property User $user belongsTo

 */
class Customer extends Model {

	/**
	 * Database table name
	 */
	protected $table = 'customers';

	/**
	 * Mass assignable columns
	 */
	protected $fillable = ['companyID',
		'user_id',
		'companyName',
		'firstName',
		'lastName',
		'phoneNumber',
		'accountingEmailAddress',
		'billingStreetName',
		'billingStreetNumber',
		'billingZipcode',
		'billingCity',
		'billingCountry',
		'billingAdditionalInfo',
		'shippingStreetName',
		'shippingStreetNumber',
		'shippingZipcode',
		'shippingCity',
		'shippingCountry',
		'shippingAdditionalInfo'];

	/**
	 * Date time columns.
	 */
	protected $dates = [];

	/**
	 * user
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user() {
		return $this->belongsTo(User::class, 'user_id');
	}

}