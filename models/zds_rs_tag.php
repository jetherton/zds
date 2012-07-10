<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status plugin tag model
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */


class Zds_rs_tag_Model extends ORM
{
	
	// Database table name
	protected $table_name = 'zds_rs_tag';
	
	
	
	
	/**
	 * Checks if the specified tag ID is of type INT and exists in the database
	 *
	 * @param	int	$tag_id Database id of the tag to be looked up
	 * @return	bool
	 */
	public static function is_valid_category($tag_id)
	{
		// Hiding errors/warnings here because child categories are seeing category_id as an obj and this fails poorly
		return ( ! is_object($tag_id) AND intval($tag_id) > 0)
		? self::factory('zds_rs_tag', intval($tag_id))->loaded
		: FALSE;
	}
	
	
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
		->add_rules('tag_title','required', 'length[3,80]');
		
		// Pass on validation to parent and return
		return parent::validate($array, $save);
	}
}
