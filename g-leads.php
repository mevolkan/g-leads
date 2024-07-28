<?php
/*
Plugin Name: G Leads
Plugin URI: github.com/mevolkan/g-leads
Description: Plugin  adds a new menu item to the admin panel. The menu item should allow users to manage a list of custom data entries stored in a database table. Provide functionalities to add, edit, delete, and view these entries.
Version: 1.0.0
Author: Samuel Nzaro
Author URI: nzaro19@gmail.com

	Copyright 2013 Samuel Nzaro

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('G_LEADS'))
	define('G_LEADS', '1.0.0');

// Start up the engine
class G_Leads
{

	/**
	 * Static property to hold our singleton instance
	 *
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return void
	 */
	private function __construct()
	{
		// back end
		add_action('plugins_loaded', array($this, 'textdomain'));
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_action('admin_menu', array($this, 'add_custom_leads_menu'));


		// front end
		add_action('wp_enqueue_scripts',					array($this, 'front_scripts'),	10);
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return G_Leads
	 */

	public static function getInstance()
	{
		if (!self::$instance)
			self::$instance = new self;
		return self::$instance;
	}

	/**
	 * load textdomain
	 *
	 * @return void
	 */

	public function textdomain()
	{

		load_plugin_textdomain('gleads', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}

	/**
	 * Admin styles
	 *
	 * @return void
	 */

	public function admin_scripts()
	{
		wp_enqueue_style('gleads-admin', plugins_url('lib/css/admin.css', __FILE__), array(), G_LEADS, 'all');
	}


	/**
	 * call front-end CSS
	 *
	 * @return void
	 */

	public function front_scripts()
	{


		wp_enqueue_style('gleads-notes', plugins_url('lib/css/gleads-notes.css', __FILE__), array(), G_LEADS, 'all');
	}

	/// end class
}


// Instantiate our class
$G_Leads = G_Leads::getInstance();
