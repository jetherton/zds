<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status tag settings JS view
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net>
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>

$(document).ready(function() {

// Categories JS
function fillFields(id, tag_title, locale<?php foreach($locale_array as $lang_key => $lang_name) echo ', '.$lang_key; ?>)
{
	show_addedit();
	$("#tag_id").attr("value", decodeURIComponent(id));
	$("#tag_title").attr("value", decodeURIComponent(category_title));
	$("#locale").attr("value", decodeURIComponent(locale));
	<?php
		foreach($locale_array as $lang_key => $lang_name) {
			echo '$("#tag_title_'.$lang_key.'").attr("value", decodeURIComponent('.$lang_key.'));'."\n";
		}
	?>
}

// Ajax Submission
function tagAction ( action, confirmAction, id )
{
	var statusMessage;
	var answer = confirm('<?php echo Kohana::lang('ui_admin.are_you_sure_you_want_to'); ?> ' + confirmAction + '?')
	if (answer){
		// Set Category ID
		$("#tag_id_action").attr("value", id);
		// Set Submit Type
		$("#tag_action").attr("value", action);
		// Submit Form
		$("#tagListing").submit();
	}
}