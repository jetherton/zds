<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status view for adding a status
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>

<div id="new_zds_statuses" class="row">
	<h4><?php echo Kohana::lang('zdsreportstatus.report_status');?></h4>
	
	<?php echo Kohana::lang('zdsreportstatus.is_public');?>:
	<?php print form::checkbox('zds_is_public', 'zds_is_public'); ?>
	<br/>
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
		print form::checkbox('zds_status_tag[]', $tag_id, in_array($tag_id, $currrent_tags));
		print form::label('zds_status_tag_'.$tag_id, $tag_name);
	}?>	
</div>
<div id="old_zds_statuses" class="row">
	<?php 
		foreach($statuses as $status)
		{
			$view = new View('zdsreportstatus/status_view');
			$view->status = $status;
			$view->on_backend = true;
			echo $view;
		}
	?>
</div>