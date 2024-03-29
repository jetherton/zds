<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status view for rendering multiple status updates
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>

<h4><?php echo Kohana::lang('zdsreportstatus.report_statuses');?></h4>
<div id="old_zds_statuses" class="row">
	<?php 
		if(count($statuses) == 0)
		{
			echo Kohana::lang('zdsreportstatus.no_status_for_report');
		}
		foreach($statuses as $status)
		{
			//skip if on the front end and private
			if(!$on_backend AND $status->is_public == 0)
			{
				continue;
			}
			$view = new View('zdsreportstatus/status_view');
			$view->status = $status;
			$view->on_backend = $on_backend;
			echo $view;
		}
	?>
</div>