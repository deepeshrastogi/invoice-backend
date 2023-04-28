<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
@property varchar $project_name project name
@property int $project_quantity project quantity
@property int $project_rate project rate
@property int $project_amount project amount
@property datetime $project_created_at project created at

 */
class Project extends Model {

	/**
	 * Database table name
	 */
	protected $table = 'project';

	protected $hidden = ['pivot'];

	/**
	 * Mass assignable columns
	 */
	protected $fillable = ['project_created_at',
		'project_name',
		'project_quantity',
		'project_rate',
		'project_amount',
		'project_created_at'];

	/**
	 * Date time columns.
	 */
	protected $dates = ['project_created_at'];

	public $timestamps = false;

	public function field()
    {

		return $this->belongsToMany(Fields::class,"project_fields",  'project_id', 'field_id');
    }

	public function invoice()
    {

        return $this->belongsToMany(Subfield::class,"project_subfields");
    }
	public function subfields() {

		return $this->hasMany(Fields::class, 'parent_id')->orderBy('field_title', 'asc');
	  }






}