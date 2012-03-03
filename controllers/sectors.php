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

class Sectors_Controller extends Main_Controller {
	/**
	 * Whether an admin console user is logged in
	 * @var bool
	 */
	var $logged_in;

	public function __construct()
	{
		parent::__construct();

		$this->themes->validator_enabled = TRUE;

		// Is the Admin Logged In?
		$this->logged_in = Auth::instance()->logged_in();

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

		$this->template->header->this_page = 'sectors_submit';
		$this->template->content = new View('sectors/sectors_submit');
		
		//Retrieve API URL
		$this->template->api_url = Kohana::config('settings.api_url');

		// Setup and initialize form field names
		$form = array(
      'sector_zoom' => '',
      'geometry_label' => '',
      'geometry_comment' => '',
      'geometry_color' => '',
      'geometry_strokewidth' => '',
			'geometry' => array(),
			'form_id'	  => '',
		);
		
		// Copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;

    $this->template->content->success_message = '';
		$form_saved = ($saved == 'saved');

		// Check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = array_merge($_POST, $_FILES);

			// Test to see if things passed the rule checks
			if (Sector::validate($post) && Sector::saveSector($post))
			{
        $this->template->content->success_message = Kohana::lang('sectors.success_message');
			}

			// No! We have validation errors, we need to show the form again, with the errors
			else
			{
        Kohana::log('info', 'Did not succeed: $post->as_array() => '. print_r($post->as_array(), 1));
				// Repopulate the form fields
        $form = arr::overwrite($form, $post->as_array());

				// Populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('sectors'));
				$form_error = TRUE;
			}
		}

		$this->template->content->id = $id;
		$this->template->content->form = $form;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->stroke_width_array = $this->_stroke_width_array();
		$this->themes->colorpicker_enabled = TRUE;

		// Javascript Header
		$this->themes->map_enabled = TRUE;
		
		$this->themes->js = new View('sectors/sectors_submit_edit_js');
		$this->themes->js->edit_mode = FALSE;
		$this->themes->js->incident_zoom = FALSE;
		$this->themes->js->default_map = Kohana::config('settings.default_map');
		$this->themes->js->default_zoom = Kohana::config('settings.default_zoom');
    $this->themes->js->latitude = Kohana::config('settings.default_lat');
    $this->themes->js->longitude = Kohana::config('settings.default_lon');
		$this->themes->js->geometries = $form['geometry'];

		// Rebuild Header Block
		$this->template->header->header_block = $this->themes->header_block();
	}

	 /**
	 * Displays a report.
	 * @param boolean $id If id is supplied, a report with that id will be
	 * retrieved.
	 */
	public function view($id = FALSE)
	{
	}

	public function geocode()
	{
		$this->template = "";
		$this->auto_render = FALSE;

		if (isset($_POST['address']) AND ! empty($_POST['address']))
		{
			$geocode = map::geocode($_POST['address']);
			if ($geocode)
			{
				echo json_encode(array("status"=>"success", "message"=>array($geocode['lat'], $geocode['lon'])));
			}
			else
			{
				echo json_encode(array("status"=>"error", "message"=>"ERROR!"));
			}
		}
		else
		{
			echo json_encode(array("status"=>"error", "message"=>"ERROR!"));
		}
	}

	/**
	 * Retrieves Cities
	 */
	private function _get_cities()
	{
		$cities = ORM::factory('city')->orderby('city', 'asc')->find_all();
		$city_select = array('' => Kohana::lang('ui_main.sectors_select_city'));

		foreach ($cities as $city)
		{
			$city_select[$city->city_lon.",".$city->city_lat] = $city->city;
		}

		return $city_select;
	}

	/**
	 * Validates a numeric array. All items contained in the array must be numbers or numeric strings
	 *
	 * @param array $nuemric_array Array to be verified
	 */
	private function _is_numeric_array($numeric_array=array())
	{
		if (count($numeric_array) == 0)
			return FALSE;
		else
		{
			foreach ($numeric_array as $item)
			{
				if (! is_numeric($item))
					return FALSE;
			}

			return TRUE;
		}
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
	
}
