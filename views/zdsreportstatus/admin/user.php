<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status view for editing user permissions
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net> 
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>


<div class="row">
		<h4><?php echo Kohana::lang('zdsreportstatus.user_can')?></h4>
		<input type="checkbox" id="zds_rs_user_edit" name="zds_rs_user_edit" value="can_edit" <?php echo $form['edit_status'] ? "checked": ""; ?>/>						
</div>