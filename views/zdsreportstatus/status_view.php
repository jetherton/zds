<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status view for rendering status updates
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>

<div class="zds_rs_status_view" id="zds_rs_<?php echo $status->id?>">
	<?php if(isset($on_backend) AND $on_backend) {?>
		<p><a href="<?php echo url::base() . 'admin/zdsreportstatus/edit/'. $status->id;?>"><?php echo Kohana::lang('zdsreportstatus.edit'); ?></a></p>
	<?php }?>
	<p>
		<strong><?php echo Kohana::lang('zdsreportstatus.submitted_by');?>:</strong> <?php echo ORM::factory('user')->find($status->user_id)->name;?>
	</p>
	<p>
		<strong><?php echo Kohana::lang('zdsreportstatus.at');?>:</strong> <?php echo $status->time; ?>
	</p>
	<?php if(isset($on_backend) AND $on_backend) {?>
	<p>
		<strong><?php echo Kohana::lang('zdsreportstatus.is_public');?>:</strong> <?php echo $status->is_public == 1 ? Kohana::lang('zdsreportstatus.yes') : Kohana::lang('zdsreportstatus.no'); ?>
	</p>
	<?php }?>	
	<p>
		<strong><?php echo Kohana::lang('zdsreportstatus.status');?>:</strong> <?php echo $status->comment; ?>
	</p>
	<p>
	<strong><?php echo Kohana::lang('zdsreportstatus.tags'); ?>:</strong>
	<br/>
		<?php
		//get the tags
		$tags = ORM::factory('zds_rs_tag')
			->join('zds_rs_tag_status', 'zds_rs_tag_status.tag_id', 'zds_rs_tag.id')
			->where('zds_rs_tag_status.status_id', $status->id)
			->find_all(); 
	$i = 0;
	foreach($tags as $tag) 
	{
		$i++;
		if($i % 2)
		{
			echo "<br/>";
		}
		echo '<class="zds_rs_tag" span>'. $tag->tag . '</span>';
	}?>	
	</p>
</div>