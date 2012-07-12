<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status settings controller
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */


class zdsreportstatus_Settings_Controller extends Admin_Controller
{
	
	/**
	 * (non-PHPdoc)
	 * Used to add and edit tags
	 * @see Admin_Controller::index()
	 */
	public function index()
	{		
		$this->template->this_page = 'addons';
		
		// Standard Settings View
		$this->template->content = new View("admin/plugins_settings");
		$this->template->content->title = Kohana::lang('zdsreportstatus.zds_report_stat_settings') . ' - '. Kohana::lang('zdsreportstatus.tags');

		//add some custom CSS
		plugin::add_stylesheet('zdsreportstatus/css/zdsreportstatus');
		
		// Settings Form View
		$this->template->content->settings_form = new View("zdsreportstatus/admin/zdsreportstatus_settings");
		
		// Locale (Language) Array
		$locales = ush_locale::get_i18n();
		
		// Setup and initialize form field names
		$form = array(
				'action' => '',
				'tag_id' => '',
				'tag_title' => '',
				'form_auth_token' => ''
			);
		
		// Add the different language form keys for fields
		foreach ($locales as $lang_key => $lang_name)
		{
			$form['tag_title_'.$lang_key] = '';
		}
		
		// Copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";
		
		
		// Check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
		
			// Fetch the post data
			$post_data = $_POST;
				
			// Extract category-specific  information
			$tag_data = arr::extract($post_data, 'tag');
				
			// Extract category image and category languages for independent validation
			$secondary_data = arr::extract($post_data, 'tag_title_lang', 'action');
				
			// Setup validation for the secondary data
			$post = Validation::factory($secondary_data)
				->pre_filter('trim', TRUE);

		
			// Add validation for the add/edit action
			if ($post->action == 'a')
			{
				// Add the different language form keys for fields
				foreach ($locales as $lang_key => $lang_name)
				{
					$post->add_rules('tag_title_lang['.$lang_key.']','length[1,80]');
				}
			}
				
			// tag instance for the operation
			$tag = (! empty($_POST['tag_id']) AND Zds_rs_tag_Model::is_valid_tag($_POST['tag_id']))
				? new Zds_rs_tag_Model($_POST['tag_id'])
				: new Zds_rs_tag_Model();

				
			// Check the specified action
			if ($post->action == 'a')
			{
				// Test to see if things passed the rule checks
				if ($tag->validate($tag_data) AND  $post->validate(FALSE))
				{
					
					// Save the category
					$tag->save();
						
					// Get the category localization
					$languages = ($tag->loaded) ? Zds_rs_tag_lang_Model::tag_langs($tag->id) : FALSE;
						
					$tag_lang = (isset($languages[$tag->id])) ? $languages[$tag->id] : FALSE;
						
					// Save localizations
					foreach ($post->tag_title_lang as $lang_key => $localized_tag_name)
					{
						$tl = (isset($tag_lang[$lang_key]['id']))
							? ORM::factory('zds_rs_tag_lang',$tag_lang[$lang_key]['id'])
							: ORM::factory('zds_rs_tag_lang');
		
						$tl->translation = $localized_tag_name;
						$tl->locale = $lang_key;
						$tl->tag_id = $tag->id;
						$tl->save();
					}
						
					$form_saved = TRUE;
					$form_action = strtoupper(Kohana::lang('ui_admin.added_edited'));
		
					// Empty $form array
					array_fill_keys($form, '');
				}
				else
				{

					// Validation failed
		
					// Repopulate the form fields
					$form = arr::overwrite($form, array_merge($tag_data->as_array(), $post->as_array()));
		
					// populate the error fields, if any
					$errors = arr::overwrite($errors, array_merge($tag_data->errors('tag'), $post->errors('tag')));
					$form_error = TRUE;
				}
		
			}
			elseif ($post->action == 'd' AND $post->validate())
			{
				// Delete action
				if ($tag->loaded)
				{
					ORM::factory('zds_rs_tag_lang')
						->where(array('tag_id' => $tag->id))
						->delete_all();
						
					// Delete tag itself 
					ORM::factory('zds_rs_tag')
						->delete($tag->id);
		
					$form_saved = TRUE;
					$form_action = strtoupper(Kohana::lang('ui_admin.deleted'));
				}
			}
		}
		
		
		$tags = ORM::factory('zds_rs_tag')
			->orderby('tag', 'asc')
			->find_all();
		
		$this->template->content->settings_form->form = $form;
		$this->template->content->settings_form->errors = $errors;
		$this->template->content->settings_form->form_error = $form_error;
		$this->template->content->settings_form->form_saved = $form_saved;
		$this->template->content->settings_form->form_action = $form_action;
		$this->template->content->settings_form->total_items = count($tags);
		$this->template->content->settings_form->tags = $tags;
		$this->template->content->settings_form->locale_array = $locales;
		$this->template->content->settings_form->form_error = $form_error;
		
		// Javascript Header
		$this->template->js = new View('zdsreportstatus/admin/zdsreportstatus_settings_js');
		$this->template->js->locale_array = $locales;
		
		
	}
	
	
	/**
	 * (non-PHPdoc)
	 * Used to add and delete workflow
	 * @see Admin_Controller::index()
	 */
	public function workflow()
	{

		$this->template->this_page = 'addons';
	
		// Standard Settings View
		$this->template->content = new View("admin/plugins_settings");
		$this->template->content->title = Kohana::lang('zdsreportstatus.zds_report_stat_settings') . ' - '. Kohana::lang('zdsreportstatus.workflow');
	
		//add some custom CSS
		plugin::add_stylesheet('zdsreportstatus/css/zdsreportstatus');
	
		// Settings Form View
		$this->template->content->settings_form = new View("zdsreportstatus/admin/zdsreportstatus_settings_workflow");
		
		// Get locale
		$locale = Kohana::config('locale.language.0');
		
		//get the tags
		$this->template->content->settings_form->no_start_tags = Zds_rs_tag_Model::categories($locale);
		//get the tags with start
		$this->template->content->settings_form->tags = array();
		$this->template->content->settings_form->tags[0] = "** ".Kohana::lang('zdsreportstatus.start')." **";
		foreach($this->template->content->settings_form->no_start_tags as $id=>$t)
		{
			$this->template->content->settings_form->tags[$id] = $t; 
		}
		
		
		
		
		// Setup and initialize form field names
		$form = array(
				'action' => '',
				'workflow_id' => '',
				'current_tag_id' => '',
				'next_tag_id' => '',
				'form_auth_token' => ''
		);

		// Copy the form as errors, so the errors will be stored with keys corresponding to the form field names		
		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";
		
		//////////////////////////Handles the post
		// Check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
		
			// Fetch the post data
			$post_data = $_POST;
		
			// Extract category-specific  information
			$workflow_data = arr::extract($post_data, 'current_tag_id', 'next_tag_id');
		
			// Extract category image and category languages for independent validation
			$admin_data = arr::extract($post_data, 'workflow_id', 'action');
		
			// Setup validation for the secondary data
			$post = Validation::factory($admin_data)
				->pre_filter('trim', TRUE);
			
			//we're going to add a workflow
			if ($post->action == 'a')
			{
				
				// Category instance for the operation
				$workflow = ORM::factory('zds_rs_workflow');
				
				// Test to see if things passed the rule checks
				if ($workflow->validate($workflow_data) AND  $post->validate(FALSE))
				{
					$workflow->save();
				}
				else
				{
					// Repopulate the form fields
					$form = arr::overwrite($form, array_merge($tag_data->as_array(), $post->as_array()));
					
					// populate the error fields, if any
					$errors = arr::overwrite($errors, array_merge($tag_data->errors('tag'), $post->errors('tag')));
					$form_error = TRUE;
				}
			}
			elseif($post->action == 'd') //deleting stuff
			{
				// Delete tag itself 
					ORM::factory('zds_rs_workflow')
						->delete($post->workflow_id);
			}
		
		}//end if POST
		
		//get the current work flows
		$workflows_db = ORM::factory('zds_rs_workflow')->find_all();
		//now put the work flows into an easy to use set of arrays.
		$workflows = array();
		
		foreach($workflows_db as $workflow)
		{
			$workflows[$workflow->current_tag_id][$workflow->id] = $workflow->next_tag_id;
		}
		$this->template->content->settings_form->workflows = $workflows;
		
		$this->template->content->settings_form->form = $form;
		$this->template->content->settings_form->errors = $errors;
		$this->template->content->settings_form->form_error = $form_error;
		$this->template->content->settings_form->form_saved = $form_saved;
		// Javascript Header
		$this->template->js = new View('zdsreportstatus/admin/zdsreportstatus_settings_workflow_js');
		
	}//end workflow()
	
}
