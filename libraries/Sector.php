<?php defined('SYSPATH') or die('No direct script access.');
class Sector {
  function incidentInSector($incidentId) {
    return incidentInRegion($incidentId);
  }

  // TODO: this is actually a Model method and should be added as a static i
  // method in a Sector_Model class
  public static function incidentInRegion($incidentId, $regionId) {
    $oIncident;

    // Get the point information for a incident and process it with sql
    if(
      $incidentId && Incident_Model::is_valid_incident($incidentId) &&
      $regionId && Sector::is_valid_region($regionId)
    ) {
      $oIncident = ORM::factory('incident', $incidentId)->with('incident::location');
      $incidentGeom = 'Point('. 
        $oIncident->location->longitude .' '.
        $oIncident->location->latitude .')';

			// Database object
			$db = new Database();
			
      // Get the geometry for the region
      /*
      $oGeometry = ORM::factory('region', $regionId)->select_list(
        'AsText(geometry) tGeometry', 'geometry');
      $regionGeometry = $oGeometry->tGeometry;
      */
      $sql = 'SELECT AsText(geometry) tGeometry from region where id='. $regionId;
      $query = $db->query($sql);
      foreach ($query as $result) {}

      // Use the database to determine if the incident is in the region
      $sql = 'SELECT MBRContains( GeomFromText(\''. $result->tGeometry
        ."'), GeomFromText('$incidentGeom')) as inRegion";
      $query = $db->query($sql);
      foreach ($query as $result) {}

      Kohana::log('info', "Incident id = $incidentId is ". 
        ($result->inRegion? '' : 'not ') ."in Sector id = $regionId");

      return $result->inRegion;
    }
    else {
      Kohana::log('error', 'Sector::incidentInRegion recieved invalid incident id: '. $incidentId);
      return false;
    }

  }

  public static function saveSector($post) {
    if (
      isset($post->submit) &&
      $post->submit == Kohana::lang('ui_main.modify')
    ) {
      return Sector::updateSector($post); 
    }
    else if (
      isset($post->submit) &&
      $post->submit == Kohana::lang('ui_main.reports_btn_submit')
    ) {
      return Sector::insertSector($post);
    }
    elseif (
      isset($post->delete) &&
      $post->delete == Kohana::lang('ui_main.delete')
    ) {
      return Sector::removeSector($post);
    }
  }

  public static function insertSector($post) {
    // Database object
    $db = new Database();
    
    // SQL for creating the incident geometry
    $sql = "INSERT INTO ".Kohana::config('database.default.table_prefix')."region "
      . "(geometry, geometry_label, geometry_comment, geometry_color, geometry_strokewidth, approved) "
      . "VALUES(GeomFromText('%s'), '%s', '%s', '%s', %s, %d)";

    $item = Sector::clean_geometry_item($post);
    if ($item && $item->geometry)
    {
      // 	Format the SQL string
      $sql = sprintf($sql, $item->geometry, $item->label, 
        $item->comment, $item->color, $item->strokewidth, (isset($post->approved)?1:0));
      
      // Execute the query
      $result = $db->query($sql);
      Kohana::log('info', 'Sector::insertSector query result => '. print_r($result, 1));
      return $result;
    }

    Kohana::log('error', 'Sector::saveSector Could not save sector geometry');
    // Set the default error message if it is not set
    if (!$post->errors('sectors')) {
      $post->add_error('sectors', 'not_added_to_db');
    }
    return false;
  }

  public static function clean_geometry_item($post) {
    // The last geometry value has the polygon
    if (!isset($post->geometry)) {
      Kohana::log('error', 'Sector::clean_geometry_item A geometry was not submitted');
      $post->add_error('sectors', 'no_sector_specified');
      return false;
    }
    $item = array_pop($post->geometry);

    // Database object
    $db = new Database();

    //Decode JSON
    $item = json_decode($item);

    //++ TODO - validate geometry
    $item->geometry = (isset($item->geometry)) ? 
      $db->escape_str($item->geometry) : "";

    $item->label = (isset($post->geometry_label)) ? 
      $db->escape_str(substr($post->geometry_label, 0, 150)) : "";

    $item->comment = (isset($post->geometry_comment)) ? 
      $db->escape_str(substr($post->geometry_comment, 0, 255)) : "";

    $item->color = (isset($post->geometry_color)) ? 
      $db->escape_str(substr($post->geometry_color, 0, 6)) : "";

    $item->strokewidth = (
      isset($post->geometry_strokewidth) AND 
      (float) $post->geometry_strokewidth) ? (float) $post->geometry_strokewidth : "2.5";

    return $item;
  }

  public static function removeSector($post) {
    $user = new User_Model($_SESSION['auth_user']->id);
    if (!admin::permissions($user, "manage")) {
      Kohana::log('error', 'Sector::removeSector() accessed with out '.
        '\'manage\' permission');
      return false;
    }
  
    if(!isset($post->sectors) || !Sector::is_valid_region($post->sectors)) {
      Kohana::log('error', 'Sector::removeSector() passed invalid region_id');
      return false;
    }

    $oRegion = ORM::factory('region', $post->sectors)->delete();
    Kohana::log('info', 'Sector::removeSector() deleted region '. $post->sectors);
    return true;

  }

  // This can be used to dis/approve
  public static function updateSector($post) {
    $user = new User_Model($_SESSION['auth_user']->id);
    if (!admin::permissions($user, "manage")) {
      Kohana::log('error', 'Sector::updateSector() accessed with out '.
        '\'manage\' permission');
      return false;
    }

    if(!isset($post->sectors) || !Sector::is_valid_region($post->sectors)) {
      Kohana::log('error', 'Sector::updateSector() passed invalid region_id');
      return false;
    }

    $oRegion = ORM::factory('region', $post->sectors);
    $item = Sector::clean_geometry_item($post);
    $oRegion->approved = isset($post->approved)?1:0;

    // We can't update associated polygons in this version anyhow.  See Issue #12
    // https://github.com/March-hare/sectors/issues/12
    // $oRegion->geometry = "GeomFromtext('".$item->geometry."')";
    
    $oRegion->geometry_label = $item->label;
    $oRegion->geometry_comment = $item->comment;
    $oRegion->geometry_color = $item->color;
    $oRegion->geometry_strokewidth = $item->strokewidth;
    Kohana::log('info', 'Sector::update_sector updated sector '. $post->sectors);
    return $oRegion->save();
  }

	/**
	 * Validation of form fields
	 *
	 * @param array $post Values to be validated
	 * @param bool $admin_section Whether the validation is for the admin section
	 */
	public static function validate(array & $post, $admin_section = FALSE)
	{
		// Exception handling
		if ( ! isset($post) OR ! is_array($post))
			return FALSE;
		
		// Create validation object
		$post = Validation::factory($post)->pre_filter('trim', TRUE);
		
    $post->add_rules('geometry_label','required', 'length[3,200]');

    // TODO: verify the geometry is within BBOX of deployment

		// Return
		return $post->validate();
  }

  public static function is_valid_region($regionId) {
		return (intval($regionId) > 0)
			? ORM::factory('region')->find(intval($regionId))->loaded
			: FALSE;
  }
	
}

