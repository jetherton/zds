<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status workflow settings JS view
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net>
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>




// Ajax Submission
function workflowAction ( action, confirmAction, id )
{
	var statusMessage;
	var answer = confirm('<?php echo Kohana::lang('ui_admin.are_you_sure_you_want_to'); ?> ' + confirmAction + '?')
	if (answer){
		// Set Category ID
		$("#workflow_id").attr("value", id);
		// Set Submit Type
		$("#action").attr("value", action);
		// Submit Form
		$("form").submit();
	}
}

