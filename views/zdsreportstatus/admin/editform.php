<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status view for adding a status
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>


<script type="text/javascript">



function deleteZdsRs(id)
{
	var youSure = confirm("<?php echo Kohana::lang('zdsreportstatus.are_you_sure_delete'); ?>");

	if(youSure == true)
	{
		$.post('<?php echo url::base();?>admin/zdsreportstatus/inlinedelete', {id:id}, function(data){
			if(data == 'success')
			{
				$("#zds_rs_"+id).remove();
			}
		});
	}
}



function editZdsRs(id)
{
	//get the current text
	var currentStatusText = $("#zds_rs_status_"+id).text();
	//swap out the text for the span
	$("#zds_rs_status_"+id).after('<textarea style="width:320px;height:100px;" class="zds_rs_inline_textarea" cols="10" rows="6" id="zds_rs_status_area_'+id+'">'+ currentStatusText + '</textarea>');
	//now drop the span
	$("#zds_rs_status_"+id).hide();
	//change the edit to a save
	$("#zds_rs_edit_button_"+id).after('<button type="button" id="zds_rs_cancel_button_'+id+'" onclick="cancelZdsRs('+id+'); return false;"><?php echo Kohana::lang('zdsreportstatus.cancel');?></button>');
	$("#zds_rs_edit_button_"+id).after('<button type="button" id="zds_rs_save_button_'+id+'" onclick="saveZdsRs('+id+'); return false;"><?php echo Kohana::lang('zdsreportstatus.save');?></button>');
	$("#zds_rs_edit_button_"+id).hide();
	
}

function saveZdsRs(id)
{
	var statusText = $("#zds_rs_status_area_"+id).val();
	$.post('<?php echo url::base();?>admin/zdsreportstatus/inlineedit', {id:id, statusText:statusText}, function(data){
			
			$("#zds_rs_status_area_"+id).remove();
			$("#zds_rs_cancel_button_"+id).remove();
			$("#zds_rs_save_button_"+id).remove();
			
			$("#zds_rs_edit_button_"+id).show();
			$("#zds_rs_status_"+id).show();
			$("#zds_rs_status_"+id).text(statusText);
			
		});
}


function cancelZdsRs(id)
{
	$("#zds_rs_status_area_"+id).remove();
	$("#zds_rs_cancel_button_"+id).remove();
	$("#zds_rs_save_button_"+id).remove();
	
	$("#zds_rs_edit_button_"+id).show();
	$("#zds_rs_status_"+id).show();
}
</script>



<div id="new_zds_statuses" class="row">
	<h4><?php echo Kohana::lang('zdsreportstatus.update_report_status');?></h4>
	
	<?php echo Kohana::lang('zdsreportstatus.is_public');?>:
	<?php print form::checkbox('zds_is_public', 'zds_is_public', $is_public == '1'); ?>
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
<?php 
	$status_views = new View('zdsreportstatus/status_views');
	$status_views->statuses = $statuses;
	$status_views->on_backend = true;
	echo $status_views;
?>

</div>