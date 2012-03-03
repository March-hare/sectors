<?php
/**
 * Performs install/uninstall methods for the sectors plugin
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   March-Hare Communicationsd Collective <info@march-hare.org> 
 * @module	   Sector Installer
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Sectors_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the sectors plugin
	 */
	public function run_install()
	{
		// Create the database tables.
		// Also include table_prefix in name
    $this->db->query(
      "CREATE TABLE IF NOT EXISTS `". Kohana::config('database.default.table_prefix') ."region` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `geometry` geometry NOT NULL,
        `geometry_label` varchar(150) DEFAULT NULL,
        `geometry_comment` varchar(255) DEFAULT NULL,
        `geometry_color` varchar(20) DEFAULT NULL,
        `geometry_strokewidth` varchar(5) DEFAULT NULL,
        PRIMARY KEY (`id`),
        SPATIAL KEY `geometry` (`geometry`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 "
    );

	}

	/**
	 * Deletes the database tables for the sectors module
	 */
	public function uninstall()
  {
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'region`');
  }

}
