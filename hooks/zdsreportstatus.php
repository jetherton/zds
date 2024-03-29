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
			//add the style sheet
			plugin::add_stylesheet("zdsreportstatus/css/zdsreportstatus");
			
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
			elseif(Router::$method == 'view')
			{
				Event::add('ushahidi_action.report_extra', array($this, '_inject_status'));
			}

			elseif(Router::$method == 'index')
			{
				Event::add('ushahidi_action.report_filters_ui', array($this,'_add_report_filter_ui'));
				Event::add('ushahidi_action.header_scripts', array($this, '_add_report_filter_js'));				
			}
			elseif(Router::$method == 'fetch_reports')
			{
				Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_filter_logic'));
			}
		}
		elseif(Router::$controller== 'json')
		{
			Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_filter_logic'));
		}		
		if(Router::$controller == "users")
		{
		
			//hook into the UI for user admin/edit
			Event::add('ushahidi_action.users_form_admin', array($this, '_add_user_view'));	 //add the UI for setting up alerts
		
			//hook into the controller so we can see the contents of the post
			Event::add('ushahidi_action.users_add_admin', array($this, '_collect_post'));
			//hook into the controller so we can get the details of the user that was edited for the above post
			Event::add('ushahidi_action.user_edit', array($this, '_user_edited'));
		
		}

	}//end add
	
	/**
	 * This collects the contents of the HTTP post that has the details of the users
	 * preferences that we want
	 */
	public function _collect_post()
	{
		$this->post = event::$data;
	}
	
	
	/**
	 * Grab the posted data
	 */
	public function _grab_post()	
	{
		
			
		if(!zdsreportstatus_helper::has_permission())
		{
			return;
		}
		
		$this->post = event::$data;
	}
	
	/**
	 * Since we've already grabed the post data, this
	 * takes the incident ID and updates the status accordingly
	 */
	public function _save_status()
	{
		
		
		//make sure this user has permission to do this
		if(!zdsreportstatus_helper::has_permission())
		{
			return;
		}
		
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
		if(isset($post['zds_status_tag']))
		{
			foreach($post['zds_status_tag'] as $tag_id)
			{
				$tag_to_status = ORM::factory('zds_rs_tag_status');
				$tag_to_status->tag_id = $tag_id;
				$tag_to_status->status_id = $status->id;
				$tag_to_status->save();
			}
		}
	}

	/**
	 * Used to create the JS that will grab the status form and put it exactly where we want it
	 */
	public function _inject_status_form()
	{

		$url = 'getform';
		
		$has_permission = zdsreportstatus_helper::has_permission();
		
		
		$id = event::$data;
		
		if(!$has_permission)
		{
			$url = 'getformnoedit';	
		}
		
		
		
		echo "<script type=\"text/javascript\">$(document).ready(function(){
			$.get('".url::base()."admin/zdsreportstatus/".$url."/".$id."', function(data){
				$(\"#custom_forms\").before(data);
				});
			});		
		</script>";
	}
	
	
	
	public function _inject_status()
	{
	
		$id = event::$data;
		
		//get the status
		//see if there's already a status
		$statuses = ORM::factory('zds_rs_status')
		->where('incident_id', $id)
		->orderby('time', 'DESC')
		->find_all();
		
		//render the views
		$status_views = new View('zdsreportstatus/status_views');
		$status_views->statuses = $statuses;
		$status_views->on_backend = false;
		echo $status_views;
	}

	/**
	 * Return true if we're on the backend
	 * false otherwise.
	 */
	private function _on_back_end()
	{
		return strpos(url::current(), 'admin/') === 0;
	}
	
	/**
	 * This little guy will add the UI to the /reports page so we can filter by tag
	 */
	public function _add_report_filter_ui()
	{
			
			// Get locale
		$locale = Kohana::config('locale.language.0');
		
		//get the tags
		$tags = Zds_rs_tag_Model::categories($locale);
		
		$view = new View('zdsreportstatus/report_filter_ui');
		$view->tags = $tags;
		$view->render(true);
	}
	
	/**
	 * This little guy will add the JS to the /reports page so we can filter by tag
	 */
	public function _add_report_filter_js()
	{
		$view = new View('zdsreportstatus/report_filter_js');
		$view->render(true);
	}
	
	public function _add_filter_logic()
	{
		//get the table prefix
		$table_prefix = Kohana::config('database.default.table_prefix');
		
		//check if the zds_rs is in the data
		if(!isset($_GET['zds_rs']))
		{
			return;
		}
		

		
		$params = Event::$data;
		
		$sql = 'i.id IN (SELECT DISTINCT incident_id FROM '.$table_prefix.'zds_rs_status AS status '.
				'INNER JOIN '.$table_prefix.'zds_rs_tag_status AS tag ON (status.id = tag.status_id) '.
				'WHERE ';
		
		$i = 0;
		foreach($_GET['zds_rs'] as $tag_id)
		{
			$i++;
			if($i > 1){$sql .= ' OR ';}
			$sql .= 'tag.tag_id = '.$tag_id;
		}
		
		$sql .= ' )';
		
		array_push($params, $sql);
		
		Event::$data = $params;
		
	}//end _add_filter_logic
	
	
	
	/**
	 * Adds the UI for zds report status to the user edit page
	 */
	public function _add_user_view()
	{
	
		$form = array('edit_status'=> false);
	
		//is this for a new user, or a previous user?
		if(Router::$controller == "profile") //the profile doesn't do us the courtesy of telling us the user's id, so we have to figure it out ourselves
		{
			if(isset($_SESSION['auth_user']))
			{
				$id = $_SESSION['auth_user']->id;
			}
			else
			{
				return;
			}
		}
		else
		{
			$id = Event::$data;
		}
		if($id)
		{ //figure out who this user is and what they're settings are
			$zds_rs_user = ORM::factory('zds_rs_user')
			->where('user_id', $id)
			->find();
	
			//if the user has no admin alert settings
			if($zds_rs_user->loaded)
			{
				$form['edit_status'] = true;
			}
		}
	
	
		$view = new View('zdsreportstatus/admin/user');		
		$view->form = $form;
		echo $view;
	}//end add_user_view
	
	
	/**
	 * This grabs the details of the user that was just edited, primarily the user ID, that's what we
	 * really want. This also does all the work of saving things to the DB
	 */
	public function _user_edited()
	{
		$user = event::$data;
		$post = $this->post;
	
		$can_edit = isset($post['zds_rs_user_edit']) AND $post['zds_rs_user_edit'] == 'can_edit';
		
		
		
		//if they can edit, then set that in the DB
		if($can_edit)
		{
			
			
			//but first see if this already exists, don't create duplicates, that's bad
			$zds_rs_edit = ORM::factory('zds_rs_user')->where('user_id', $user->id)->find();
			if(!$zds_rs_edit->loaded)
			{
				
				$zds_rs_edit = ORM::factory('zds_rs_user');
				$zds_rs_edit->user_id = $user->id;
				$zds_rs_edit->save();
			}
		}
		else
		{
			//they can't edit any more, so take away their permission
			$zds_rs_edit = ORM::factory('zds_rs_user')->where('user_id', $user->id)->delete_all();
		}
		
	}//end _user_edited
}

new zdsreportstatus;
