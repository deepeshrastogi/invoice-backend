<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
@property varchar $name name
@property text $description description
@property datetime $created_at created at

 */
class Fields extends Model {

	/**
	 * Database table name
	 */
	protected $table = 'fields';

	/**
	 * Mass assignable columns
	 */
	protected $fillable = ['field_title',
		'field_description',
		'field_rate',
		'field_amount',
		'field_quantity',
		'parent_id'

	];

	protected $hidden = ['pivot'];

	/**
	 * Date time columns.
	 */
	protected $dates = [];

	public $timestamps = false;

	public function project()
    {
        return $this->belongsToMany(Project::class, "project_fields",'field_id', 'project_id');
    }

	public function parent() {
		return $this->belongsToOne(Fields::class, 'parent_id');
	  }

	  //each category might have multiple children
	  public function subfields() {

		return $this->hasMany(Fields::class, 'parent_id')->orderBy('field_title', 'asc');
	  }



}