<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

class CompanyProfile extends Model {
	use HasFactory;
	protected $fillable = ['user_id', 'company_name', 'authorised_person_name', 'company_number', 'company_phone_number', 'company_address', 'bank_name', 'bank_company_name', 'bank_address', 'bank_account_number', 'bank_ifsc_code', 'bank_swift_code', 'signature_image', 'default_tax', 'invoice_prefix', 'invoice_series'];
	protected $dates = ['deleted_at'];
	protected $table = 'company_profile';

	protected $hidden = [
		'created_at',
		'updated_at',
	];

	protected $appends = ['url'];

	public function getUrlAttribute() {
		return config('app.url') . Storage::url($this->signature_image);
	}
}
