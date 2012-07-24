<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status javascript view for filtering reports based on status
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net>
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>

<script type="text/javascript">

/**
 * Toggle AND or OR
 */
function zds_rs_change(id)
{
	if(!urlParameters['zds_rs'] || typeof urlParameters['zds_rs'] == 'undefined')
	{
		urlParameters['zds_rs'] = new Array();
	}
	if($("#zds_status_tag_"+id).attr("checked"))
	{
		urlParameters['zds_rs'].push(id);
	}
	else
	{
		var index = urlParameters['zds_rs'].indexOf(id);
		urlParameters['zds_rs'].splice(index,1);
	}	
}




</script>