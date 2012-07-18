<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status controller for rendering AJAX components
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */


class zdsreportstatus_Controller extends Admin_Controller
{
	
	/**
	 * (non-PHPdoc)
	 * Used to get the edit form
	 * @see Admin_Controller::index()
	 */
	public function getform($id = 0)
	{
		$this->template = new View('zdsreportstatus/admin/editform');
		
		// Get locale
		$locale = Kohana::config('locale.language.0');
		
		//get the tags
		$tags = Zds_rs_tag_Model::categories($locale);
		
		//prepare the current tag list
		$current_tags = array();
		
		//see if there's already a status
		$statuses = ORM::factory('zds_rs_status')
			->where('incident_id', $id)
			->orderby('time', 'DESC')
			->find_all();
		//get the current status
		$current_status = null;
		foreach($statuses as $status)
		{
			$current_status = $status;
			break;
		}
		
		if($current_status == null)
		{
			$current_tags[] = 0;
		}
		else
		{
			$tags_db = ORM::factory('zds_rs_tag_status')
				->where('status_id', $current_status->id)
				->find_all();
			foreach($tags_db as $tag)
			{
				$current_tags[] = $tag->tag_id;
			}
			//double check that there were some tags
			if(count($current_tags) == 0)
			{
				$current_tags[] = 0;
			}
		}
		
		//now figure out based on the tags we have, what tags are allowed via the workflow
		$allowed_tags = array();
		foreach($current_tags as $tag)
		{
			$allowed_tags[$tag] = $tag;
			$workflow_db = ORM::factory('zds_rs_workflow')
				->where('current_tag_id',$tag)
				->find_all();
			
			foreach($workflow_db as $workflow)
			{
				$allowed_tags[$workflow->next_tag_id] = $workflow->next_tag_id; 
			}
		}
		
		//now compile the final list of tags and their wording
		$final_tag_list = array();
		foreach($allowed_tags as $allowed_tag)
		{
			if(isset($tags[$allowed_tag])) //handle zero or start
			$final_tag_list[$allowed_tag] = $tags[$allowed_tag];
		}
		if($current_status != null)
		{
			$this->template->is_public = $current_status->is_public;
		}
		else
		{
			$this->template->is_public = false;
		}
		$this->template->tag_list = $final_tag_list;
		$this->template->currrent_tags = $current_tags;
		$this->template->statuses = $statuses;
		
	}//end workflow()
	
	
	/** 
	 * Used to perform inline edits
	 */
	public function inlineedit()
	{
		//turn off the template
		$this->template = "";
		//turn off auto render
		$this->auto_render = false;
		//make sure we have a valid ID
		if(!isset($_POST['id']))
		{
			return;
		}
		$id = intval($_POST['id']);
		if($id == 0)
		{
			return;
		}
		//grab the status text
		$status_text = $_POST['statusText'];
		
		$status = ORM::factory('zds_rs_status')->find($id);
		$status->comment = $status_text;
		$status->save();
		
	}
	
}
