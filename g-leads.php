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
include_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-leads-list-table.php';

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
        // Check if form is submitted
        if ( isset( $_POST['gleads_submit'] ) ) {
            $this->handle_form_submission();
        }

        // Handle edit and delete actions
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
            $this->handle_edit_action();
        } elseif ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' ) {
            $this->handle_delete_action();
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Custom Leads', 'textdomain' ); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field( 'add_glead' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="gleads_message"><?php esc_html_e( 'Message', 'textdomain' ); ?></label></th>
                        <td><textarea name="gleads_message" id="gleads_message" rows="5" cols="50" required></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="gleads_status"><?php esc_html_e( 'Status', 'textdomain' ); ?></label></th>
                        <td>
                            <select name="gleads_status" id="gleads_status" required>
                                <option value="Pending"><?php esc_html_e( 'Pending', 'textdomain' ); ?></option>
                                <option value="In Progress"><?php esc_html_e( 'In Progress', 'textdomain' ); ?></option>
                                <option value="Done"><?php esc_html_e( 'Done', 'textdomain' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="gleads_phone"><?php esc_html_e( 'Phone', 'textdomain' ); ?></label></th>
                        <td><input type="text" name="gleads_phone" id="gleads_phone" required /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="gleads_country"><?php esc_html_e( 'Country', 'textdomain' ); ?></label></th>
                        <td><input type="text" name="gleads_country" id="gleads_country" required /></td>
                    </tr>
                </table>
                <?php submit_button( __( 'Add Lead', 'textdomain' ), 'primary', 'gleads_submit' ); ?>
            </form>
        </div>
        <?php

        // Display the list table
        $leads_table = new Custom_Leads_List_Table();
        $leads_table->prepare_items();
        ?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Leads List', 'textdomain' ); ?></h2>
            <form method="post">
                <?php
                $leads_table->display();
        ?>
            </form>
        </div>
        <?php
    }

    /**
     * Handle edit action
     *
     * @return void
     */
    public function handle_edit_action()
    {
        //code to handle edit action here
    }

    /**
     * Handle delete action
     *
     * @return void
     */
    public function handle_delete_action()
    {
        //code to handle delete action here
    }

    /**
     * Handle form submission
     *
     * @return void
     */
    public function handle_form_submission()
    {
        global $wpdb;

        // Check nonce for security
        if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'add_glead' ) ) {
            wp_die( __( 'Security check failed', 'textdomain' ) );
        }

        // Validate and sanitize input fields
        $message = isset( $_POST['gleads_message'] ) ? sanitize_text_field( $_POST['gleads_message'] ) : '';
        $status  = isset( $_POST['gleads_status'] ) ? sanitize_text_field( $_POST['gleads_status'] ) : '';
        $phone   = isset( $_POST['gleads_phone'] ) ? sanitize_text_field( $_POST['gleads_phone'] ) : '';
        $country = isset( $_POST['gleads_country'] ) ? sanitize_text_field( $_POST['gleads_country'] ) : '';

        // Check required fields
        if ( empty( $message ) || empty( $status ) || empty( $phone ) || empty( $country ) ) {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Please fill in all required fields.', 'textdomain' ) . '</p></div>';

            return;
        }

        // Insert data into the database
        $table_name = $wpdb->prefix . 'custom_lead';
        $wpdb->insert(
            $table_name,
            [
                'message'     => $message,
                'status'      => $status,
                'phone'       => $phone,
                'country'     => $country,
                'create_date' => current_time( 'mysql' ),
            ],
            [
                '%s', // message
                '%s', // status
                '%s', // phone
                '%s', // country
                '%s',  // create_date
            ]
        );

        // Show success message
        if ( $wpdb->insert_id ) {
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Lead added successfully.', 'textdomain' ) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Failed to add lead.', 'textdomain' ) . '</p></div>';
        }
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
