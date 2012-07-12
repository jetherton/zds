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
<span><?php echo Kohana::lang('zdsreportstatus.tags'); ?></span>
<a  href="<?php echo url::base(); ?>admin/zdsreportstatus_settings/workflow"><?php echo Kohana::lang('zdsreportstatus.workflow'); ?></a>
</li>
</ul>
</div>

<p>
	<?php echo Kohana::lang('zdsreportstatus.tag_explanation'); ?>
</p>
				<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<a name="add"></a>
					<ul class="tabset">
						<li><a href="#" class="active" onclick="show_addedit(true)"><?php echo Kohana::lang('zdsreportstatus.add_edit');?></a></li>
					</ul>
					<!-- tab -->
					<div class="tab" id="addedit" style="display:none">
						<?php print form::open(NULL,array('enctype' => 'multipart/form-data', 'id' => 'tagMain', 'name' => 'tagMain')); ?>
						<input type="hidden" id="tag_id" name="tag_id" value="" />
						<input type="hidden" name="action" id="action" value="a"/>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('zdsreportstatus.tag_name');?>:</strong><br />
							<?php print form::input('tag', '', ' class="text"'); ?><br/>
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
						<div class="tab_form_item">
							<input type="submit" id="tag_add_edit_submit" name="tag_add_edit_submit" value="<?php echo Kohana::lang('zdsreportstatus.save'); ?>"/>
						</div>

						<?php //print form::close(); ?>			
					</div>
				</div>

<!-- report-table -->
				<div class="report-form">
					<?php //print form::open(NULL,array('id' => 'tagListing','name' => 'tagListing')); ?>
						<!-- 
						<input type="hidden" name="action" id="tag_action" value="">
						<input type="hidden" name="tag_id" id="tag_id_action" value="">
						 -->
						<div class="table-holder">
							<table class="table" id="tagSort" style="width:800px;">
								<thead>
									<tr class="nodrag">
										<th class="col-1">&nbsp;</th>
										<th class="col-2"><?php echo Kohana::lang('zdsreportstatus.tag');?></th>
										<th class="col-2"><?php echo Kohana::lang('ui_main.actions');?></th>
									</tr>
								</thead>
								<tfoot>
									<tr class="foot nodrag">
										<td colspan="3">
										</td>
									</tr>
								</tfoot>
								<tbody>
									<?php
									if ($total_items == 0)
									{
									?>
										<tr class="nodrag">
											<td colspan="3" class="col" id="row1">
												<h3><?php echo Kohana::lang('ui_main.no_results');?></h3>
											</td>
										</tr>
									<?php	
									}
									foreach ($tags as $tag)
									{
										$tag_id = $tag->id;
										$tag_title = $tag->tag;
										$tag_locals = array();
										$tag_locals_db = ORM::factory('zds_rs_tag_lang')
											->where('tag_id', $tag_id)
											->find_all();
										foreach($tag_locals_db as $local_db)
										{
											$tag_locals[$local_db->locale] = $local_db->translation;
										}
										//etherton this needs some extra SQL here
										//foreach($category->category_lang as $category_lang){
										//	$category_locals[$category_lang->locale] = $category_lang->category_title;
										//}
										?>
										<tr id="<?php echo $tag_id; ?>">
											<td class="col-1 col-drag-handle">&nbsp;</td>
											<td class="col-2">
												<div class="post">
													<h4><?php echo $tag_title; ?></h4>
												</div>
											</td>											
											<td class="col-4">
												<ul>
													<li class="none-separator"><a href="#add" onClick="fillFields('<?php echo(rawurlencode($tag_id)); ?>','<?php echo(rawurlencode($tag_title)); ?>'<?php
													foreach($locale_array as $lang_key => $lang_name){
														echo ',';
														if(isset($tag_locals[$lang_key])){
															echo ' \''.rawurlencode($tag_locals[$lang_key]).'\'';
														}else{
															echo ' \'\'';
														}
													}
													?>)"><?php echo Kohana::lang('ui_main.edit');?></a></li>
													
												<li><a href="javascript:tagAction('d','DELETE','<?php echo(rawurlencode($tag_id)); ?>')" class="del"><?php echo Kohana::lang('ui_main.delete');?></a></li>
												</ul>
											</td>
										</tr>
										<?php										
									}
									?>
								</tbody>
							</table>
						</div>
					<?php print form::close(); ?>
				</div>
				
				
				
				