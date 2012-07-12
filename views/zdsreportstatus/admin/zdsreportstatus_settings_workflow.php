<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ZDS Report Status workflow settings view
 *
 * @author	   Z Data Solutions <info@zdatasolutions.net>
 * @package	   ZDS Admin Alerts - http://www.zdatasolutions.net/
 */
?>
<div class="btns">
<ul>
<li>

<a  href="<?php echo url::base(); ?>admin/zdsreportstatus_settings"><?php echo Kohana::lang('zdsreportstatus.tags'); ?></a>
<span><?php echo Kohana::lang('zdsreportstatus.workflow'); ?></span>
</li>
</ul>
</div>

<p>
	<?php echo Kohana::lang('zdsreportstatus.workflow_explanation'); ?>
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
						<input type="hidden" id="workflow_id" name="workflow_id" value="" />
						<input type="hidden" name="action" id="action" value="a"/>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('zdsreportstatus.tag');?>:</strong><br />
							<?php print form::dropdown('current_tag_id', $tags, null, array('id'=>'current_tag_id')); ?><br/>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('zdsreportstatus.can_go_to');?>:</strong><br />
							<?php print form::dropdown('next_tag_id', $no_start_tags, null, array('id'=>'next_tag_id')); ?><br/>
						</div>
					</div>

				</div>

				
				<!-- report-table -->
				<div class="report-form">
						<div class="table-holder">
							<table class="table" id="tagSort" style="width:800px;">
								<thead>
									<tr class="nodrag">
										<th class="col-1">&nbsp;</th>
										<th class="col-2"><?php echo Kohana::lang('zdsreportstatus.tag');?></th>
										<th class="col-2"><?php echo Kohana::lang('ui_main.actions');?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									if (count($workflows) == 0)
									{
									?>
										<tr class="nodrag">
											<td class="col-2" colspan="3" class="col" id="row1">
												<h3><?php echo Kohana::lang('ui_main.no_results');?></h3>
											</td>
										</tr>
									<?php	
									}
									foreach ($workflows as $id=>$workflow)
									{
										?>
										
											<tr class="sub-header">
												<td class="col-1"  colspan="2"> 
													<?php echo Kohana::lang('zdsreportstatus.tag'). ' "'. $tags[$id] . '" ' . Kohana::lang('zdsreportstatus.can_go_to');?>:
												</td> 
												<td class="col-4"></td>
												
											</tr>
											<?php 
											foreach($workflow as $wf_id=>$w)
											{
											?>
												<tr>
													<td class="col-1">
													</td>
													<td class="col-2" > 
														<div class="post">
														<h4><?php echo $tags[$w];?></h4>
														</div>
													</td> 
													<td class="col-4" >
														<ul> 
															<li class="none-separator"><a href="javascript:workflowAction('d','DELETE','<?php echo(rawurlencode($wf_id)); ?>')" class="del"><?php echo Kohana::lang('ui_main.delete');?></a></li>
														</ul>
													</td> 
												</tr>	
											<?php 
											}
											?>
										
										
										<?php 										
									}
									?>
								</tbody>
							</table>
						</div>
				</div>
				
				
				
				
				