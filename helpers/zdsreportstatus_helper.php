<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status plugin helper for commonly called stuff
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */


class zdsreportstatus_helper_Core {
	
	/**
	 * Call this guy when you want to know if the current user has permission to edit/create/delete status
	 * returns true if they have permission
	 */
	public static function has_permission()
	{
		//make sure this user has permission to do this
		$user = new User_Model($_SESSION['auth_user']->id);
		$has_permission = false;
		foreach($user->roles as $role)
		{
			if($role->name == 'PROBLEMSOLVER')
			{
				return true;
			}
		}
		
		//so they dont' have the right role, but maybe they have an exception
		
		return ORM::factory('zds_rs_user')->where('user_id', $user->id)->find()->loaded;
		
	}//end has_permission

}