<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status tag settings view
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net>
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>
<div class="btns">
<ul>
<li>
<a class="btn_save_add_new" href="<?php echo url::base(); ?>/admin/zdsreportstatus_setting/workflow"><?php echo Kohana::lang('zdsreportstatus.workflow'); ?></a>
</li>
</ul>
</div>


				<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<a name="add"></a>
					<ul class="tabset">
						<li><a href="#" class="active" onclick="show_addedit(true)"><?php echo Kohana::lang('zdsreportstatus.add_edit');?></a></li>
					</ul>
					<!-- tab -->
					<div class="tab" id="addedit" style="display:none">
						<?php print form::open(NULL,array('enctype' => 'multipart/form-data', 
							'id' => 'tagMain', 'name' => 'tagMain')); ?>
						<input type="hidden" id="tag_id" name="tag_id" value="" />
						<input type="hidden" name="action" id="action" value="a"/>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('zdsreportstatus.tag_name');?>:</strong><br />
							<?php print form::input('tag_name', '', ' class="text"'); ?><br/>
							<a href="#" id="tag_translations" class="new-cat" style="clear:both;"><?php echo Kohana::lang('zdsreportstatus.tag_translation'); ?></a>
							<div id="tag_translations_form_fields" style="display:none;">
								<div style="clear:both;"></div>
								<?php
									foreach($locale_array as $lang_key => $lang_name){
										echo '<div style="margin-top:10px;"><strong>'.$lang_name.':</strong></div>';
										print form::input('tag_title_lang['.$lang_key.']', $form['tag_title_'.$lang_key], ' class="text" id="tag_title_'.$lang_key.'"');
										echo '<br />';
									}
								?>

							</div>
						</div>

						<script type="text/javascript">
						    $(document).ready(function() {

						    $('a#tag_translations').click(function() {
							    $('#tag_translations_form_fields').toggle(400);
							    $('#tag_translations').toggle(0);
							    return false;
							});

							});
						</script>

						<?php print form::close(); ?>			
					</div>
				</div>
				
				
				