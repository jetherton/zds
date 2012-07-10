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
		if(Router::$controller == "users")
		{
			//add our CSS
			plugin::add_stylesheet("zdsadminalerts/css/zdsadminalerts");
				
			//hook into the UI for user admin/edit
			Event::add('ushahidi_action.users_form_admin', array($this, '_add_user_view'));	 //add the UI for setting up alerts
				
			//hook into the controller so we can see the contents of the post
			Event::add('ushahidi_action.users_add_admin', array($this, '_collect_post'));
			//hook into the controller so we can get the details of the user that was edited for the above post
			Event::add('ushahidi_action.user_edit', array($this, '_user_edited'));

		}
	
		else if(Router::$controller == "reports")
		{
			//handle reports coming in from the public facing web
			Event::add('ushahidi_action.report_add', array($this, '_web_report'));
			//handle reports coming in fromt he administrative side
			Event::add('ushahidi_action.report_edit', array($this, '_admin_report'));
			//handle approving reports from the index controller
			Event::add('ushahidi_action.report_approve', array($this, '_report_approved'));
			//handle approving reports from the edit controller
			if(Router::$method == 'edit' && count(Router::$arguments) > 0)
			{
				Event::add('ushahidi_action.report_submit_admin', array($this, '_edit_report_approved_verified'));
			}
			if(Router::$method == 'index' && $this->_on_back_end())
			{
				Event::add('ushahidi_filter.pagination', array($this, '_index_verified'));
			}
		}

		else if(Router::$controller == "api")
		{
			//handle reports coming in via the API,
			Event::add('ushahidi_action.report_edit_api', array($this, '_api_report'));
		}

		//handle incoming SMS
		Event::add('ushahidi_action.message_sms_add',  array($this, '_sms_message'));

		//hanlde incoming email
		Event::add('ushahidi_action.message_email_add', array($this, '_email_message'));

	}

	/**
	 * Adds the UI for zds admin alerts to the user edit page
	 */
	public function _add_user_view()
	{

		$form = array('zds_enable'=> 0,
				'selected_categories'=>array(),
				'zds_sms'=>false,
				'zds_email'=>false,
				'zds_web'=>false,
				'zds_admin'=>false,
				'zds_api'=>false,
				'zds_approved'=>false,
				'zds_verified'=>false);

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
			$zds_setting = ORM::factory('zds_admin_alert')
			->where('user_id', $id)
			->find();
				
			//if the user has no admin alert settings
			if($zds_setting->loaded)
			{

				$selected_categories = array();
				//get the categories
				$zde_cats = ORM::factory('zds_admin_alert_cat')
				->where('alert_id', $zds_setting->id)
				->find_all();
				foreach($zde_cats as $cat)
				{
					$selected_categories[] = $cat->category_id;
				}



				$form = array('zds_enable'=> 1,
						'selected_categories'=>$selected_categories,
						'zds_sms' => $zds_setting->sms == '1',
						'zds_email' => $zds_setting->email == '1',
						'zds_web' => $zds_setting->web == '1',
						'zds_admin' => $zds_setting->admin == '1',
						'zds_api' => $zds_setting->api == '1',
						'zds_approved' => $zds_setting->approved == '1',
						'zds_verified' => $zds_setting->verified == '1');
			}
		}


		$view = new View('zdsadminalerts/admin/zdsadminalerts_alert_setting');
		$view->yesno_array = array(
				'1' => strtoupper(Kohana::lang('ui_main.yes')),
				'0' => strtoupper(Kohana::lang('ui_main.no'))
		);

		$view->categories = Category_Model::get_categories(0, FALSE, FALSE);
		$view->form = $form;
		echo $view;
	}

	/**
	 * This collects the contents of the HTTP post that has the details of the users
	 * alerts preferences that we want
	 */
	public function _collect_post()
	{
		$this->post = event::$data;
	}

	/**
	 * This grabs the details of the user that was just edited, primarily the user ID, that's what we
	 * really want. This also does all the work of saving things to the DB
	 */
	public function _user_edited()
	{
		$user = event::$data;
		$post = $this->post;

		//pull out the data we care about
		$zds_enabled = $post['zds_enable'] == '1';
		$zds_cateogry = isset($post['zds_category']) ? $post['zds_category'] : array();
		$zds_sms = isset($post['zds_sms']);
		$zds_email = isset($post['zds_email']);
		$zds_web = isset($post['zds_web_report']);
		$zds_admin = isset($post['zds_admin_report']);
		$zds_api = isset($post['zds_api_report']);
		$zds_approve = isset($post['zds_report_approved']);
		$zds_verify = isset($post['zds_report_verified']);

		//grab the record in the DB if it exists
		$zds_setting = ORM::factory('zds_admin_alert')
		->where('user_id', $user->id)
		->find();

		//now if zds isn't turned on then drop any corresponding data from the DB and move on with life
		if(!$zds_enabled)
		{
			if($zds_setting->loaded)
			{
				//drop any category associates
				ORM::factory('zds_admin_alert_cat')
				->where('alert_id', $zds_setting->id)
				->delete_all();
			}
			//delete the setting
			$zds_setting->delete();
		}
		else
		{
			$zds_setting->sms = $zds_sms;
			$zds_setting->email = $zds_email;
			$zds_setting->web = $zds_web;
			$zds_setting->admin = $zds_admin;
			$zds_setting->api = $zds_api;
			$zds_setting->user_id = $user->id;
			$zds_setting->approved = $zds_approve;
			$zds_setting->verified = $zds_verify;
			$zds_setting->save();
			//now update the categories
			//first delete all the categories associated with this alert
			ORM::factory('zds_admin_alert_cat')
			->where('alert_id', $zds_setting->id)
			->delete_all();
			//now add back in the ones we want
			foreach($zds_cateogry as $key=>$value)
			{
				$zds_cat = ORM::factory('zds_admin_alert_cat');
				$zds_cat->alert_id = $zds_setting->id;
				$zds_cat->category_id = $value;
				$zds_cat->save();
			}
		}
	}


	/**
	 * Called when a new report comes in through the web interface
	 */
	public function _web_report()
	{
		$incident = event::$data;

		$this->handle_reports($incident, 'web', $incident->incident_title, url::base(). 'admin/reports/edit/'.$incident->id);
	}


	/**
	 * Called when an admin edits a report
	 * has to verify that this is indeed a new report
	 */
	public function _admin_report()
	{
		$incident = event::$data;
		//chop up the url on /
		$url_pieces = explode('/', url::current());

		//make sure the last bit of that url is not the id
		if(count(Router::$arguments) == 0)
		{
			$this->handle_reports($incident, 'admin', $incident->incident_title, url::base(). 'admin/reports/edit/'.$incident->id);
		}
	}


	/**
	 * Called when a new report comes in via the API
	 */
	public function _api_report()
	{
		$incident = event::$data;

		$this->handle_reports($incident, 'API', $incident->incident_title, url::base(). 'admin/reports/edit/'.$incident->id);
	}

	/**
	 * Called when a new sms comes in
	 */
	public function _sms_message()
	{
		$sms = event::$data;

		$this->handle_messages($sms, 'SMS', url::base(). 'admin/messages');
	}

	/**
	 * Called when a new email comes in
	 */
	public function _email_message()
	{
		$email = event::$data;

		$this->handle_messages($email, 'email', url::base(). 'admin/messages');
	}


	/**
	 * Handles the sending of notifications when they're for reports
	 */
	private function handle_reports($incident, $method, $text, $url)
	{
		//get a list of alerts that match this
		$alerts = ORM::factory('zds_admin_alert');
		//filter based on what just came down the line
		if($method == "web")
		{
			$alerts = $alerts->where('web', '1');
		}
		else if($method == "admin")
		{
			$alerts = $alerts->where('admin', '1');
		}
		else if($method == "API")
		{
			$alerts = $alerts->where('api', '1');
		}
		//find them all
		$alerts = $alerts->find_all();

		//put the categories of the incoming report into a nifty array
		$cats_array = array();
		$incident_cats = ORM::factory('incident_category')->where('incident_id',$incident->id)->find_all();
		foreach($incident_cats as $cat)
		{
			$cats_array[] = $cat->category_id;
		}


		//now loop over those alerts and check categories
		foreach($alerts as $alert)
		{
			//check categories
			//get the cats for this alert
			$alert_cats = ORM::factory('zds_admin_alert_cat')
			->where('alert_id', $alert->id)
			->find_all();
				
			//if there are no cats then assume the user wants all cats and send on
			if(count($alert_cats) == 0)
			{
				//send away
				$this->send_alert($alert, 'report', $method, $text, $url);
			}
			else
			{ //check if there's a match in categories
				$cat_match = false;
				foreach($alert_cats as $ac)
				{
					if(in_array($ac->category_id, $cats_array))
					{
						$cat_match = true;
						break;
					}
				}
				if($cat_match)
				{
					//send away
					$this->send_alert($alert, 'report', $method, $text, $url);
				}
			}
		}//end for loop over alerts
	}//end method handle_reports

	/**
	 * Handles setting up notification for incoming messages
	 */
	private function handle_messages($message, $method, $url)
	{
		//get a list of alerts that match this
		$alerts = ORM::factory('zds_admin_alert');
		if($method == 'SMS')
		{
			$alerts = $alerts->where('sms', '1');
		}
		else if($method == 'email')
		{
			$alerts = $alerts->where('email', '1');
		}

		$alerts = $alerts->find_all();

		foreach($alerts as $alert)
		{
			$this->send_alert($alert, 'message', $method, $message->message, $url);
		}

	}

	/**
	 * Does the actual sending of alerts
	 */
	private function send_alert($alert, $item, $method, $text, $url)
	{
		//first get the email address that we're going to use.
		$user = ORM::factory('user')
		->where('id', $alert->user_id)
		->find();
		$to = $user->email;

		$user_url = url::base() . 'admin/users/edit/'. $user->id;

		//grab some settings that we'll use in the email
		$settings = kohana::config('settings');
		$site_name = $settings['site_name'];
		$alerts_email = ($settings['alerts_email']) ? $settings['alerts_email']: $settings['site_email'];

		//compose the message
		$message = '<strong>'.Kohana::lang('zdsadminalerts.new') . ':</strong> '. $item . '<br/>'.
				'<strong>'.Kohana::lang('zdsadminalerts.received_via'). ':</strong> ' . $method . '<br/>'.
				'<strong>'.Kohana::lang('zdsadminalerts.recieved_on'). ':</strong> ' . date('D, M/d/Y H:i') . '<br/>'.
				'<strong>'.Kohana::lang('zdsadminalerts.title') . ':</strong> '. $text . '<br/>'.
				'<strong>'.Kohana::lang('zdsadminalerts.link'). ':</strong> <a href="'.$url.'">'.$url.'</a><br/><br/><br/><br/>'.
				Kohana::lang('zdsadminalerts.you_recieved_becase') . '<br/><br/>'.
				Kohana::lang('zdsadminalerts.to_unsubscribe_click') . ': <a href="'.$user_url.'">'.$user_url.'</a>';



		//compose the subject and from lines
		$from = array();
		$from[] = $alerts_email;
		$from[] = $site_name;
		$subject = "[$site_name] - " . Kohana::lang('zdsadminalerts.new') . ' '. $item . ' - ' . substr($text, 0, 20) . '...';

		//send
		$ret_val = email::send($to, $from, $subject, $message, true);
	}

	
	
	/**
	 * Does the actual sending of alerts, when an incident is verified
	 */
	private function send_alert_verify($alert, $item, $text, $url)
	{
		//first get the email address that we're going to use.
		$user = ORM::factory('user')
		->where('id', $alert->user_id)
		->find();
		$to = $user->email;
	
		$user_url = url::base() . 'admin/users/edit/'. $user->id;
	
		//grab some settings that we'll use in the email
		$settings = kohana::config('settings');
		$site_name = $settings['site_name'];
		$alerts_email = ($settings['alerts_email']) ? $settings['alerts_email']: $settings['site_email'];
	
		//compose the message
		$message = '<strong>'.Kohana::lang('zdsadminalerts.report_verified_subject'). '<br/>'.
				'<strong>'.Kohana::lang('zdsadminalerts.verified_on'). ':</strong> ' . date('D, M/d/Y H:i') . '<br/>'.
				'<strong>'.Kohana::lang('zdsadminalerts.title') . ':</strong> '. $text . '<br/>'.
				'<strong>'.Kohana::lang('zdsadminalerts.link'). ':</strong> <a href="'.$url.'">'.$url.'</a><br/><br/><br/><br/>'.
				Kohana::lang('zdsadminalerts.you_recieved_becase') . '<br/><br/>'.
				Kohana::lang('zdsadminalerts.to_unsubscribe_click') . ': <a href="'.$user_url.'">'.$user_url.'</a>';
	
	
	
		//compose the subject and from lines
		$from = array();
		$from[] = $alerts_email;
		$from[] = $site_name;
		$subject = "[$site_name] - " . Kohana::lang('zdsadminalerts.report_verified_subject') . ' - ' . substr($text, 0, 20) . '...';
	
		//send
		$ret_val = email::send($to, $from, $subject, $message, true);
	}
	
	
	
	
	

	/**
	 * Does the actual sending of alerts, when an incident is approved
	 */
	private function send_alert_approve($alert, $item, $text, $url)
	{
		//first get the email address that we're going to use.
		$user = ORM::factory('user')
		->where('id', $alert->user_id)
		->find();
		$to = $user->email;

		$user_url = url::base() . 'admin/users/edit/'. $user->id;

		//grab some settings that we'll use in the email
		$settings = kohana::config('settings');
		$site_name = $settings['site_name'];
		$alerts_email = ($settings['alerts_email']) ? $settings['alerts_email']: $settings['site_email'];

		//compose the message
		$message = '<strong>'.Kohana::lang('zdsadminalerts.report_approved_subject'). '<br/>'.
				'<strong>'.Kohana::lang('zdsadminalerts.approved_on'). ':</strong> ' . date('D, M/d/Y H:i') . '<br/>'.
				'<strong>'.Kohana::lang('zdsadminalerts.title') . ':</strong> '. $text . '<br/>'.
				'<strong>'.Kohana::lang('zdsadminalerts.link'). ':</strong> <a href="'.$url.'">'.$url.'</a><br/><br/><br/><br/>'.
				Kohana::lang('zdsadminalerts.you_recieved_becase') . '<br/><br/>'.
				Kohana::lang('zdsadminalerts.to_unsubscribe_click') . ': <a href="'.$user_url.'">'.$user_url.'</a>';



		//compose the subject and from lines
		$from = array();
		$from[] = $alerts_email;
		$from[] = $site_name;
		$subject = "[$site_name] - " . Kohana::lang('zdsadminalerts.report_approved_subject') . ' - ' . substr($text, 0, 20) . '...';

		//send
		$ret_val = email::send($to, $from, $subject, $message, true);
	}


	/**
	 * Handles the case when the report approved event is called
	 */
	public function _report_approved()
	{
		$incident = event::$data;

		$this->handle_report_approved($incident, $incident->incident_title, url::base(). 'admin/reports/edit/'.$incident->id);
	}
	
	/**
	 * when we want to know if something has been verified via the /admin/reports/index controller
	 */
	public function _index_verified()
	{
		//check if the verified action was enacted
		if(isset($_POST['action']) AND $_POST['action'] == 'v')
		{
			//now loop over each reports that was verified
			foreach($_POST['incident_id'] as $id)
			{
				
				$incident = new Incident_Model($id);
				if($incident->incident_verified == '1')
				{
					$text = $incident->incident_title;
					$url = url::base(). 'admin/reports/edit/'.$incident->id;
					$this->handle_report_verified($incident, $text, $url);
				}
			}
		}
	}

	
	
	/**
	 * Handles the sending of notifications when a report is verified
	 */
	private function handle_report_verified($incident, $text, $url)
	{
		$alerts = ORM::factory('zds_admin_alert')
		->where('verified', '1')
		->find_all();
	
		//put the categories of the incoming report into a nifty array
		$cats_array = array();
		$incident_cats = ORM::factory('incident_category')->where('incident_id',$incident->id)->find_all();
		//if the incident_category param is set in the POST variables then use that because it'll be more up to date
		if(isset($_POST['incident_category']))
		{
			foreach($_POST['incident_category'] as $cat_id)
			{
				$cats_array[] = $cat_id;
			}
		}
		else //use the categories that are already associated with the report
		{
			foreach($incident_cats as $cat)
			{
				$cats_array[] = $cat->category_id;
			}
		}
		
	
	
		foreach($alerts as $alert)
		{
			//check categories
			//get the cats for this alert
			$alert_cats = ORM::factory('zds_admin_alert_cat')
			->where('alert_id', $alert->id)
			->find_all();
				
			//if there are no cats then assume the user wants all cats and send on
			if(count($alert_cats) == 0)
			{
				//send away
				$this->send_alert_verify($alert, $incident, $text, $url);
			}
			else
			{ //check if there's a match in categories
				$cat_match = false;
				foreach($alert_cats as $ac)
				{
					if(in_array($ac->category_id, $cats_array))
					{
						$cat_match = true;
						break;
					}
				}
				if($cat_match)
				{
					//send away
					$this->send_alert_verify($alert, $incident, $text, $url);
				}
			}
		}//end foreach alert
	}
	

	/**
	 * Handles the sending of notifications when a report is approved
	 */
	private function handle_report_approved($incident, $text, $url)
	{
		$alerts = ORM::factory('zds_admin_alert')
		->where('approved', '1')
		->find_all();
		
		//put the categories of the incoming report into a nifty array
		$cats_array = array();
		$incident_cats = ORM::factory('incident_category')->where('incident_id',$incident->id)->find_all();
		foreach($incident_cats as $cat)
		{
			$cats_array[] = $cat->category_id;
		}
		

		foreach($alerts as $alert)
		{
			//check categories
			//get the cats for this alert
			$alert_cats = ORM::factory('zds_admin_alert_cat')
				->where('alert_id', $alert->id)
				->find_all();
			
			//if there are no cats then assume the user wants all cats and send on
			if(count($alert_cats) == 0)
			{
				//send away
				$this->send_alert_approve($alert, $incident, $text, $url);
			}
			else
			{ //check if there's a match in categories
				$cat_match = false;
				foreach($alert_cats as $ac)
				{
					if(in_array($ac->category_id, $cats_array))
					{
						$cat_match = true;
						break;
					}
				}
				if($cat_match)
				{
					//send away
					$this->send_alert_approve($alert, $incident, $text, $url);
				}
			}
		}//end foreach alert
	}

	//used to see if a user has approved a report via the edit pager
	public function _edit_report_approved_verified()
	{		
		$id = intval(Router::$arguments[0]);
		if($id > 0)
		{
			//check if the post data calls to make the variable approved
			$post = event::$data;
			if(intval($post['incident_active']) == 1)
			{
				//it should be approved, so check and see if it's currently not approved
				$incident = new Incident_Model($id);
				if($incident->incident_active == '0')
				{
					//it's about to get aproved
					$this->handle_report_approved($incident, $incident->incident_title, url::base(). 'admin/reports/edit/'.$incident->id);
				}
			}
			if (intval($post['incident_verified']) == 1)
			{
				//it should be approved, so check and see if it's currently not approved
				$incident = new Incident_Model($id);
				if($incident->incident_verified == '0')
				{
					//it's about to get aproved
					$this->handle_report_verified($incident, $incident->incident_title, url::base(). 'admin/reports/edit/'.$incident->id);
				}
			}					
		}
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
