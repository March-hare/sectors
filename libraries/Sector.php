<?php defined('SYSPATH') or die('No direct script access.');
class Sector {
  function incidentInSector($incidentId) {
    return incidentInRegion($incidentId);
  }

  public static function incidentInRegion($incidentId) {
    return false;
  }

  public static function saveSector($post) {
    Kohana::log('error', '$post => '. print_r($post->as_array(), 1));
		if (isset($post->geometry)) 
		{
			// Database object
			$db = new Database();
			
			// SQL for creating the incident geometry
			$sql = "INSERT INTO ".Kohana::config('database.default.table_prefix')."region "
				. "(geometry, geometry_label, geometry_comment, geometry_color, geometry_strokewidth) "
				. "VALUES(GeomFromText('%s'), '%s', '%s', '%s', %s)";

      // The last geometry value has the polygon
      $item = array_pop($post->geometry);

      if ( ! empty($item))
      {
        //Decode JSON
        $item = json_decode($item);

        //++ TODO - validate geometry
        $geometry = (isset($item->geometry)) ? $db->escape_str($item->geometry) : "";

        $label = (isset($post->geometry_label)) ? 
          $db->escape_str(substr($post->geometry_label, 0, 150)) : "";

        $comment = (isset($post->geometry_comment)) ? 
          $db->escape_str(substr($post->geometry_comment, 0, 255)) : "";

        $color = (isset($post->geometry_color)) ? 
          $db->escape_str(substr($post->geometry_color, 0, 6)) : "";

        $strokewidth = (
          isset($post->geometry_strokewidth) AND 
          (float) $post->geometry_strokewidth) ? (float) $post->geometry_strokewidth : "2.5";
        // Add errors to $post->add_error('report', 'errori_in_i18n')
        if ($geometry)
        {
          // 	Format the SQL string
          $sql = sprintf($sql, $geometry, $label, $comment, $color, $strokewidth);
          
          // Execute the query
          $result = $db->query($sql);
          Kohana::log('info', 'Sector::saveSector query result => '. print_r($result, 1));
          return $result;
        }
      }
    } 

    Kohana::log('error', 'Sector::saveSector Could not save sector geometry');
    $post->add_error('report', 'not_added_to_db');
    return false;
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
		$post = Validation::factory($post)
				->pre_filter('trim', TRUE);
		
    $post->add_rules('geometry_label','required', 'length[3,200]');

    // TODO: verify the geometry is within BBOX of deployment

		// Return
		return $post->validate();
	}
	
}

