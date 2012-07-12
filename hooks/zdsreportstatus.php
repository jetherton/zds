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
