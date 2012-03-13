<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This controller is used to list/ view and edit sectors
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   March-Hare Communicationsd Collective <info@march-hare.org> 
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class Sectors_Settings_Controller extends Admin_Controller {
	public function __construct()
	{
    parent::__construct();
    $this->template->this_page = 'reports';
  }

	/**
	 * Displays all sectors.
	 */
	public function index()
  {
    return $this->submit();
  }

	/**
	 * Submits a new sector.
	 */
	public function submit($id = FALSE, $saved = FALSE)
  {
		$db = new Database();
		
    // TODO: First, are we allowed to submit new sectors?
    /*
		if ( ! Kohana::config('settings.allow_sectors'))
		{
			url::redirect(url::site().'main');
    }
    */

		//$this->template->header->this_page = 'sectors_submit';
		//$this->template->header->this_page = 'sectors/admin/sectors_submit';
		//$this->template->this_page = 'sectors/admin/sectors_submit';
		$this->template->content = new View('sectors/admin/sectors_submit');
		
		//Retrieve API URL
    $this->template->content->api_url = Kohana::config('settings.api_url');
    plugin::add_stylesheet('sectors/css/sectors');

		// Setup and initialize form field names
		$form = array(
      'sector_zoom' => '',
      'geometry_label' => '',
      'geometry_comment' => '',
      'geometry_color' => '',
      'geometry_strokewidth' => '',
      'geometry' => array(),
      'approved' => '',
      'delete' => '',
			'region_id'	  => '',
      'form_id'	  => '',
      'sectors' => array(),
      'sectors_selected' => '',
		);
		
		// Copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;

    $this->template->content->success_message = '';
		$form_saved = ($saved == 'saved');

		// Check, has the form been submitted, if so, setup validation
		if ($_POST)
    {
      // We can either approve, disapprove, delete or add here
     
      // Instantiate Validation, use $post, so we don't overwrite $_POST fields 
      // with our own things
      $post = array_merge($_POST, $_FILES);

			// Test to see if things passed the rule checks
			if (Sector::validate($post) && Sector::saveSector($post))
			{
        $this->template->content->success_message = Kohana::lang('sectors.success_message');
			}

			// No! We have validation errors, we need to show the form again, with the errors
			else
			{
				// Repopulate the form fields
        $form = arr::overwrite($form, $post->as_array());

				// Populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('sectors'));
        Kohana::log('info', 'Did not succeed: $post->as_array() => '. print_r($errors, 1));
				$form_error = TRUE;
			}
		}

    $form['sectors'] = 
      ORM::factory('region')->select_list('id', 'geometry_label');

    $form['sectors']['add'] = Kohana::lang('sectors.sectors_submit_new'); 

    //Kohana::log('info', 'meow: '. print_r($form['sectors'], 1));

    $this->template->content->sectors_selected = 'add';

		$this->template->content->id = $id;
		$this->template->content->form = $form;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->stroke_width_array = $this->_stroke_width_array();

		// Javascript Header
		$this->template->map_enabled = TRUE;
		$this->template->colorpicker_enabled = TRUE;
		
		$this->template->js = new View('sectors/admin/sectors_submit_edit_js');
		$this->template->js->edit_mode = FALSE;
		$this->template->js->incident_zoom = FALSE;
		$this->template->js->default_map = Kohana::config('settings.default_map');
		$this->template->js->default_zoom = Kohana::config('settings.default_zoom');
    $this->template->js->latitude = Kohana::config('settings.default_lat');
    $this->template->js->longitude = Kohana::config('settings.default_lon');
    $this->template->js->geometries = $form['geometry'];
    $this->template->js->geometries_hash = $this->_get_js_geometries_hash();

		// Inline Javascript
		$this->template->content->date_picker_js = $this->_date_picker_js();
		$this->template->content->color_picker_js = $this->_color_picker_js();

		// Pack Javascript
		$myPacker = new javascriptpacker($this->template->js , 'Normal', false, false);
		$this->template->js = $myPacker->pack();
  }

	/**
	 * Array with Geometry Stroke Widths
    */
	private function _stroke_width_array()
	{
		for ($i = 0.5; $i <= 8 ; $i += 0.5)
		{
			$stroke_width_array["$i"] = $i;
		}
		
		return $stroke_width_array;
  }

	// Javascript functions
	private function _color_picker_js()
	{
		 return "<script type=\"text/javascript\">
					$(document).ready(function() {
					$('#category_color').ColorPicker({
							onSubmit: function(hsb, hex, rgb) {
								$('#category_color').val(hex);
							},
							onChange: function(hsb, hex, rgb) {
								$('#category_color').val(hex);
							},
							onBeforeShow: function () {
								$(this).ColorPickerSetColor(this.value);
							}
						})
					.bind('keyup', function(){
						$(this).ColorPickerSetColor(this.value);
					});
					});
				</script>";
	}

	private function _date_picker_js()
	{
		return "<script type=\"text/javascript\">
				$(document).ready(function() {
				$(\"#incident_date\").datepicker({
				showOn: \"both\",
				buttonImage: \"" . url::base() . "media/img/icon-calendar.gif\",
				buttonImageOnly: true
				});
				});
			</script>";
	}

  private function _get_js_geometries_hash() {
    // Database object
    $db = new Database();
    $sql = 'SELECT AsText(geometry) tGeometry, region.* from region';
    $query = $db->query($sql);

    $js_array = "var geometries = new Object();\n";
    foreach ($query as $region) {
      $js_array .= "geometries[". $region->id ."] = {\n".
        "'label': '". $region->geometry_label ."',\n".
        "'comment': '". $region->geometry_comment."',\n".
        "'color': '". $region->geometry_color."',\n".
        "'strokewidth': ". $region->geometry_strokewidth.",\n".
        "'approved': ". $region->approved.",\n".
        "'geometry': '". $region->tGeometry."'};\n";
    }
    //Kohana::log('info', '$js_array : '. print_r($js_array, 1));
    return $js_array;
  }

}

