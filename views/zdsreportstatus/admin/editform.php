<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status controller for rendering AJAX components
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>

<div class="row">
	<h4><?php echo Kohana::lang('zdsreportstatus.report_status');?></h4>
	<?php echo Kohana::lang('zdsreportstatus.status');?>:
	<textarea id="zds_status" name="zds_status" rows="6" cols="40"></textarea>
	<br/>
	<?php echo Kohana::lang('zdsreportstatus.tags');?>:
	<?php 
	$i = 0;
	foreach($tag_list as $tag_id=>$tag_name) 
	{
		$i++;
		if($i % 2)
		{
			echo "<br/>";
		}
		print form::checkbox('zds_status_tag_'.$tag_id, 'zds_status_tag_'.$tag_id, in_array($tag_id, $currrent_tags));
		print form::label('zds_status_tag_'.$tag_id, $tag_name);
	}?>
</div>