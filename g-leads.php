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

if ( !defined( 'G_LEADS' ) ) {
    define( 'G_LEADS', '1.0.0' );
}

// Start up the engine
class G_Leads
{

    /**
         * Static property to hold our singleton instance
         */
    public static $instance = false;

    /**
     * This is our constructor
     *
     * @return void
     */
    private function __construct()
    {
        // Activation hook to create the custom leads table
        register_activation_hook( __FILE__, [$this, 'create_custom_lead_table'] );

        // back end
        add_action( 'plugins_loaded', [$this, 'textdomain'] );
        add_action( 'admin_enqueue_scripts', [$this, 'admin_scripts'] );
        add_action( 'admin_menu', [$this, 'add_custom_leads_menu'] );

        // front end
        add_action( 'wp_enqueue_scripts', [$this, 'front_scripts'], 10 );
    }

    /**
     * If an instance exists, this returns it.  If not, it creates one and
     * retuns it.
     *
     * @return G_Leads
     */
    public static function getInstance()
    {
        if ( !self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * load textdomain
     *
     * @return void
     */
    public function textdomain()
    {
        load_plugin_textdomain( 'gleads', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Admin styles
     *
     * @return void
     */
    public function admin_scripts()
    {
        wp_enqueue_style( 'gleads-admin', plugins_url( 'lib/css/admin.css', __FILE__ ), [], G_LEADS, 'all' );
    }

    /**
     * Custom leads Menu
     *
     * @return void
     */
    public function add_custom_leads_menu()
    {
        // Ensure the user has the capability to manage options
        if ( current_user_can( 'manage_options' ) ) {
            // Add a new menu item
            add_menu_page(
                'Custom Leads',
                'Custom Leads',
                'manage_options',
                'custom-leads',
                [$this, 'display_custom_leads_page'],
                'dashicons-welcome-learn-more',
                26
            );
        }
    }

    /**
     * display the custom leads page
     *
     * @return void
     */
    public function display_custom_leads_page()
    {
        ?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Custom Leads', 'textdomain' ); ?></h1>
			<p><?php esc_html_e( 'Welcome to the Custom Leads page.', 'textdomain' ); ?></p>
		</div>
<?php
    }

    /**
     * Create custom leads table
     *
     * @return void
     */
    public function create_custom_lead_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'custom_lead';

        // Check if the table already exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				message text NOT NULL,
				status enum('Pending', 'In Progress', 'Done') NOT NULL,
				phone varchar(20) DEFAULT '' NOT NULL,
				country varchar(100) DEFAULT '' NOT NULL,
				create_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
        }
    }

    /**
     * call front-end CSS
     *
     * @return void
     */
    public function front_scripts()
    {
        wp_enqueue_style( 'gleads-notes', plugins_url( 'lib/css/gleads-notes.css', __FILE__ ), [], G_LEADS, 'all' );
    }

    /// end class
}

// Instantiate our class
$G_Leads = G_Leads::getInstance();
