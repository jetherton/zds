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
		foreach($statuses as $status)
		{
			$view = new View('zdsreportstatus/status_view');
			$view->status = $status;
			$view->on_backend = $on_backend;
			echo $view;
		}
	?>
</div>