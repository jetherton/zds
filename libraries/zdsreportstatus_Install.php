<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Performs install/uninstall methods for the ZDS Report Status Plugin
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Report Status Plugin - http://www.zdatasolutions.net/
 */
class Zdsreportstatus_Install {
	
	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db =  new Database();
	}

	/**
	 * Creates the required columns for the FrontlineSMS Plugin
	 */
	public function run_install()
	{
		
		// ****************************************
		// DATABASE STUFF
		// Store the alert data
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."zds_rs_status`
			(
				id int(15) unsigned NOT NULL AUTO_INCREMENT,
				user_id int(11) NOT NULL,
				incident_id int(11) NOT NULL,
				time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				comment longtext NOT NULL,
				is_public tinyint(4) NOT NULL,				
				PRIMARY KEY (`id`)
			);
		");
		
		// Tags
		$this->db->query("
				CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."zds_rs_tag`
				(
				id int(15) unsigned NOT NULL AUTO_INCREMENT,
				tag char(255) NOT NULL,
				PRIMARY KEY (`id`)
			);
		");
		
		// Tags to status mapping
		$this->db->query("
				CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."zds_rs_tag_status`
				(
				id int(15) unsigned NOT NULL AUTO_INCREMENT,
				tag_id int(15) unsigned NOT NULL,
				status_id int(15) unsigned NOT NULL,
				PRIMARY KEY (`id`)
			);
		");
		
		// Tags to language mapping
		$this->db->query("
				CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."zds_rs_tag_lang`
				(
				id int(15) unsigned NOT NULL AUTO_INCREMENT,
				tag_id int(15) unsigned NOT NULL,
				locale char(10) NOT NULL,
				translation char(255) NOT NULL,
				PRIMARY KEY (`id`)
			);
		");

		// Tags work flow mapping
		$this->db->query("
				CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."zds_rs_workflow`
				(
				id int(15) unsigned NOT NULL AUTO_INCREMENT,
				current_tag_id int(15) unsigned NOT NULL,
				next_tag_id int(15) unsigned NOT NULL,				
				PRIMARY KEY (`id`)
			);
		");
		
		//make sure the problem solver role doesn't already exists
		if(!ORM::factory('role')->where('name','PROBLEMSOLVER')->find()->loaded)
		{
			// adds the problem solver role
			$this->db->query("INSERT INTO  `".Kohana::config('database.default.table_prefix')."roles` (
					`id` ,
					`name` ,
					`description` ,
					`reports_view` ,
					`reports_edit` ,
					`reports_evaluation` ,
					`reports_comments` ,
					`reports_download` ,
					`reports_upload` ,
					`messages` ,
					`messages_reporters` ,
					`stats` ,
					`settings` ,
					`manage` ,
					`users` ,
					`manage_roles` ,
					`checkin` ,
					`checkin_admin` ,
					`access_level`
			)
			VALUES (
					NULL ,  'PROBLEMSOLVER',  'Used to update ZDS Report Status',  '1',  '1',  '0',  '0',  '0',  '0',  '0',  '0',  '0',  '0',  '0',  '0',  '0',  '1',  '0',  '0'
			);");
		}
		
		
		// Tags work flow mapping
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."zds_rs_user`
			(
			id int(15) unsigned NOT NULL AUTO_INCREMENT,
			user_id int(15) unsigned NOT NULL,
			PRIMARY KEY (`id`)
			);
				");
		

	}

	/**
	 * Drops the FrontlineSMS Tables
	 */
	public function uninstall()
	{
		/*Etherton: It scares me too much to give any old admin the ability to permanently blow away a table in the
		 * database, so I've commented this out.
		$this->db->query("
			DROP TABLE ".Kohana::config('database.default.table_prefix')."zds_rs_status;
			");			
		$this->db->query("
			DROP TABLE ".Kohana::config('database.default.table_prefix')."zds_rs_tag;
			");
		$this->db->query("
			DROP TABLE ".Kohana::config('database.default.table_prefix')."zds_rs_tag_status;
			");
		$this->db->query("
			DROP TABLE ".Kohana::config('database.default.table_prefix')."zds_rs_tag_lang;
			");
		$this->db->query("
			DROP TABLE ".Kohana::config('database.default.table_prefix')."zds_rs_workflow;
			");									
		*/
	}
}