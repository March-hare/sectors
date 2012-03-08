<div id="content">
	<div class="content-bg">

		<!-- start report form block -->
		<?php print form::open(NULL, array('enctype' => 'multipart/form-data', 'id' => 'reportForm', 'name' => 'reportForm', 'class' => 'gen_forms')); ?>
		<input type="hidden" name="sector_zoom" id="sector_zoom" value="<?php echo $form['sector_zoom']; ?>" />
		<div class="big-block">
			<div class="report_right" style="width:500px;">
				<div class="report_row row">
        <h1><?php echo Kohana::lang('sectors.sectors_administration'); ?></h1>
        <?php if ($form_error): ?>
        <!-- red-box -->
        <div class="red-box">
          <h3>Error!</h3>
          <ul>
            <?php
              foreach ($errors as $error_item => $error_description)
              {
                print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
              }
            ?>
          </ul>
        </div>
        <?php elseif(isset($success_message) && strlen($success_message)): ?>
        <div class="green-box"><?php print $success_message; ?></div>
        <?php endif; ?>
        </div>
          <input type="hidden" name="form_id" id="form_id" value="<?php echo $id?>">
        <div class="report_row row">
          <?php 
              print "<div id='geometries' style='width:50%;float:left;'>".
                form::dropdown(
                'sectors', 
                $form['sectors'], 
                $sectors_selected)
                .'</div>';

              print "<div id='approved' style='width:20%;float:left'>".
                form::label('approved', Kohana::lang('sectors.approved')) .
                form::checkbox('approved', 'approved', 'approved')
                  .'</div>';

              print "<div id='submit' style='float:left;'>".
                form::submit('submit', Kohana::lang('ui_main.reports_btn_submit'))
                .'</div>';

              print "<div id='delete'>".
                form::submit('delete', Kohana::lang('ui_main.delete'))
                  .'</div>';
          ?>
        </div>
				<div class="report_row row">
					<div id="divMap" class="report_map map_holder_reports"></div>
        </div>
        <div class="report_row row">
          <div id="geometryLabelerHolder" class="olControlNoSelect">
            <div id="geometryLabeler">
              <div id="geometryLabelComment">
                <span id="geometryLabel"><label><?php echo Kohana::lang('ui_main.geometry_label');?>:</label> <?php print form::input('geometry_label', $form['geometry_label'], ' class="lbl_text"'); ?></span>
                <span id="geometryComment"><label><?php echo Kohana::lang('ui_main.geometry_comments');?>:</label> <?php print form::input('geometry_comment', $form['geometry_comment'], ' class="lbl_text2"'); ?></span>
              </div>
              <div>
                <span id="geometryColor"><label><?php echo Kohana::lang('ui_main.geometry_color');?>:</label> <?php print form::input('geometry_color', $form['geometry_color'], ' class="lbl_text"'); ?></span>
                <span id="geometryStrokewidth"><label><?php echo Kohana::lang('ui_main.geometry_strokewidth');?>:</label> <?php print form::dropdown('geometry_strokewidth', $stroke_width_array, $form['geometry_strokewidth']); ?></span>
              </div>
            </div>
            <div id="geometryLabelerClose"></div>
          </div>
        </div>  
        <div class="report-find-location report_row row">
            <div id="panel" class="olControlEditingToolbar"></div>
          <div class="btns" style="float:left;">
            <ul style="padding:4px;">
              <li><a href="#" class="btn_del_last"><?php echo strtoupper(Kohana::lang('ui_main.delete_last'));?></a></li>
              <li><a href="#" class="btn_del_sel"><?php echo strtoupper(Kohana::lang('ui_main.delete_selected'));?></a></li>
              <li><a href="#" class="btn_clear"><?php echo strtoupper(Kohana::lang('ui_main.clear_map'));?></a></li>
            </ul>
          </div>
          <div style="clear:both;"></div>
      </div>

			</div>
		</div>
		<?php print form::close(); ?>
		<!-- end report form block -->
	</div>
</div>
