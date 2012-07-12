<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status plugin workflow model
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */


class Zds_rs_workflow_Model extends ORM
{
	
	// Database table name
	protected $table_name = 'zds_rs_workflow';
	
	
	/**
	 * Validates and optionally saves a tag record from an array
	 *
	 * @param array $array Values to check
	 * @param bool $save Saves the record when validation succeeds
	 * @return bool
	 */
	public function validate(array & $array, $save = FALSE)
	{
		// Set up validation
		$array = Validation::factory($array)
		->pre_filter('trim', TRUE)
		->add_rules('current_tag_id','required', 'numeric')
		->add_rules('next_tag_id','required', 'numeric');
	
		// Pass on validation to parent and return
		return parent::validate($array, $save);
	}
}
