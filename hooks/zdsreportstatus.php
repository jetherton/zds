<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status plugin Hook
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net>
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */

class zdsreportstatus {

	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));

		//use this to store the post data
		$this->post = null;
	}

	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
	
		if(Router::$controller == "reports")
		{

			//handle approving reports from the edit controller
			if(Router::$method == 'edit')
			{
				//to insert the status form
				Event::add('ushahidi_action.report_pre_form_admin', array($this, '_inject_status_form'));
				//to grab the status post data
				Event::add('ushahidi_action.report_submit_admin', array($this, '_grab_post'));
				//to know the ID of the report
				Event::add('ushahidi_action.report_edit', array($this, '_save_status'));
			}		
		}		

	}//end add
	
	/**
	 * Grab the posted data
	 */
	public function _grab_post()
	{
		$this->post = event::$data;
	}
	
	/**
	 * Since we've already grabed the post data, this
	 * takes the incident ID and updates the status accordingly
	 */
	public function _save_status()
	{
		//grab the incident ID
		$incident_id = event::$data;
		
		//just to make typing this out easier.
		$post = $this->post;
		
		//first thing we want to do is know if there's a status to update. 
		//we'll assume that if the status field is blank that there's no status, and thus, we're done
		if(!isset($post['zds_status']) OR strlen($post['zds_status']) == 0)
		{
			return;
		}
		//so there's a status, let's record it.
		//get the current user
		$current_user = new User_Model($_SESSION['auth_user']->id);
		
		$status = ORM::factory('zds_rs_status');
		$status->user_id = $current_user->id;
		$status->incident_id = $incident_id;
		$status->comment = $post['zds_status'];
		$status->is_public = isset($post['zds_is_public']) ? 1 : 0; 
		$status->save();
		
		//next handle the tags
		//loop over the newly assigned categories
		foreach($post['zds_status_tag'] as $tag_id)
		{
			$tag_to_status = ORM::factory('zds_rs_tag_status');
			$tag_to_status->tag_id = $tag_id;
			$tag_to_status->status_id = $status->id;
			$tag_to_status->save();
		}
	}

	/**
	 * Used to create the JS that will grab the status form and put it exactly where we want it
	 */
	public function _inject_status_form()
	{
		$id = event::$data;
		
		echo "<script type=\"text/javascript\">$(document).ready(function(){
			$.get('".url::base()."admin/zdsreportstatus/getform/".$id."', function(data){
				$(\"#custom_forms\").before(data);
				});
			});		
		</script>";
	}

	/**
	 * Return true if we're on the backend
	 * false otherwise.
	 */
	private function _on_back_end()
	{
		return strpos(url::current(), 'admin/') === 0;
	}
}

new zdsreportstatus;
