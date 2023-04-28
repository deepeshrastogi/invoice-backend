<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
@property varchar $name name
@property text $description description
@property datetime $created_at created at

 */
class Subfield extends Model {

	/**
	 * Database table name
	 */
	protected $table = 'subfields';

	/**
	 * Mass assignable columns
	 */
	protected $fillable = ['name',
		'description'];

	/**
	 * Date time columns.
	 */
	protected $dates = [];

	public $timestamps = false;

	public function project()
    {
        return $this->belongsToMany(Project::class, "project_subfields",'subfield_id', 'project_id');
    }


}