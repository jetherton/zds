<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status view for filtering reports based on status
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net>
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>
<h3 id="zds_rs_filter_header">
	<a class="f-title" href="#"><?php echo Kohana::lang('zdsreportstatus.status_tags'); ?></a>
</h3>
<div class="f-simpleGroups-box" id="zds_rs_filter_body">
	<ul class="filter-list fl-zds_rs">
		<?php 
				foreach($tags as $tag_id=>$title) 
				{
					echo '<li>';					
					print form::checkbox('zds_status_tag[]', $tag_id, false, 'onchange="zds_rs_change('.$tag_id.'); return false;", id="zds_status_tag_'.$tag_id.'"');
					echo ' ';
					print form::label('zds_status_tag_'.$tag_id, $title);
					echo '</li>';
					
				}
		?>	
	</ul>
	
	
</div>