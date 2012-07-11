<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status plugin tag to lang mapping model
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */


class Zds_rs_tag_lang_Model extends ORM
{
	
	// Database table name
	protected $table_name = 'zds_rs_tag_lang';
	
	
	static function tag_langs($tag_id=FALSE)
	{
		if($tag_id != FALSE)
		{
			$tag_langs = ORM::factory('zds_rs_tag_lang')->where(array('tag_id'=>$tag_id))->find_all();
		}else{
			$tag_langs = ORM::factory('zds_rs_tag_lang')->find_all();
		}
	
		$t_langs = array();
		foreach($tag_langs as $tag_lang) {
			$t_langs[$tag_lang->tag_id][$tag_lang->locale]['id'] = $tag_lang->id;
			$t_langs[$tg_lang->tag_id][$tag_lang->locale] = $tag_lang->translation;
		}
	
		return $t_langs;
	}
	
}
