<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
@property int $project_id project id
@property int $subfield_id subfield id
@property datetime $created_at created at

 */
class ProjectSubfield extends Model {

	/**
	 * Database table name
	 */
	protected $table = 'project_subfields';

	/**
	 * Mass assignable columns
	 */
	protected $fillable = ['project_id',
		'subfield_id'];

	/**
	 * Date time columns.
	 */
	protected $dates = [];

}