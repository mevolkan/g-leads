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

if( !defined( 'G_LEADS' ) )
	define( 'G_LEADS', '1.0.0' );

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
	private function __construct() {
		// back end
		add_action		( 'plugins_loaded', 					array( $this, 'textdomain'				) 			);
		add_action		( 'admin_enqueue_scripts',				array( $this, 'admin_scripts'			)			);
		add_action		( 'do_meta_boxes',						array( $this, 'create_metaboxes'		),	10,	2	);
		add_action		( 'save_post',							array( $this, 'save_custom_meta'		),	1		);

		// front end
		add_action		( 'wp_enqueue_scripts',					array( $this, 'front_scripts'			),	10		);
		add_filter		( 'comment_form_defaults',				array( $this, 'custom_notes_filter'		) 			);
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return G_Leads
	 */

	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

	/**
	 * load textdomain
	 *
	 * @return void
	 */

	public function textdomain() {

		load_plugin_textdomain( 'gleads', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Admin styles
	 *
	 * @return void
	 */

	public function admin_scripts() {

		$types = $this->get_post_types();

		$screen	= get_current_screen();

		if ( in_array( $screen->post_type , $types ) ) :

			wp_enqueue_style( 'gleads-admin', plugins_url('lib/css/admin.css', __FILE__), array(), G_LEADS, 'all' );

		endif;

	}

	/**
	 * call metabox
	 *
	 * @return void
	 */

	public function create_metaboxes( $page, $context ) {

		$types = $this->get_post_types();

		if ( in_array( $page,  $types ) )
			add_meta_box( 'wp-comment-notes', __( 'Comment Notes', 'gleads' ), array( $this, 'gleads_notes_meta' ), $page, 'advanced', 'high' );

	}

	/**
	 * display meta fields for notes meta
	 *
	 * @return void
	 */

	public function gleads_notes_meta( $post ) {

		// Use nonce for verification
		wp_nonce_field( 'gleads_meta_nonce', 'gleads_meta_nonce' );

		$post_id	= $post->ID;

		// get postmeta, and our initial settings
		$notes		= get_post_meta( $post_id, '_gleads_notes', true );

		$before_text	= isset( $notes['before-text'] )	? $notes['before-text']	: '';
		$before_type	= isset( $notes['before-type'] )	? $notes['before-type']	: 'gleads-notes-standard';
		$after_text		= isset( $notes['after-text'] )		? $notes['after-text']	: '';
		$after_type		= isset( $notes['after-type'] )		? $notes['after-type']	: 'gleads-notes-standard';

		echo '<script type="text/javascript">jQuery(document).ready(function($){$("#comment_status").click(function(){$(".gleads-notes-table tr").toggle();})});</script>';

		$disabled_display = comments_open( $post_id )   ? ' style="display:none;"' : '';
		$enabled_display  = ! comments_open( $post_id ) ? ' style="display:none;"' : '';

		echo '<table class="form-table gleads-notes-table">';

			echo '<tr class="gleads-notes-disabled"' . $disabled_display . '>';
				echo '<th>' . __( 'Enable comments in order to use Comment Notes', 'gleads' ) . '</th>';
			echo '</tr>';

			echo '<tr class="gleads-notes-title"' . $enabled_display . '>';
			echo '<td colspan="2"><h5>'.__( 'Before Notes Area', 'gleads' ) . '</h5></td>';
			echo '</tr>';

			echo '<tr class="gleads-notes-data gleads-notes-before-text"' . $enabled_display . '>';
				echo '<th>'.__( 'Message Text', 'gleads' ) . '</th>';
				echo '<td>';
					echo '<textarea class="widefat" name="gleads-notes[before-text]" id="gleads-before">'.esc_attr( $before_text ) . '</textarea>';
					echo '<p class="description">'.__( 'Note: This will not appear to users who are logged in.', 'gleads' ) . '</p>';
				echo '</td>';
			echo '</tr>';

			echo '<tr class="gleads-notes-data gleads-notes-before-type"' . $enabled_display . '>';
				echo '<th>'.__( 'Message Type', 'gleads' ) . '</th>';
				echo '<td>';
					echo '<select id="gleads-before-type" name="gleads-notes[before-type]">';
					echo '<option value="gleads-notes-standard"' . selected( $before_type, 'gleads-notes-standard', false ) . '>' . __( 'Standard', 'gleads' ) . '</option>';
					echo '<option value="gleads-notes-warning"' . selected( $before_type, 'gleads-notes-warning', false ) . '>' . __( 'Warning', 'gleads' ) . '</option>';
					echo '<option value="gleads-notes-alert"' . selected( $before_type, 'gleads-notes-alert', false ) . '>' . __( 'Alert', 'gleads' ) . '</option>';
					do_action( 'gleads_before_types', $before_type );
					echo '</select>';
				echo '</td>';
			echo '</tr>';

			echo '<tr class="gleads-notes-title"' . $enabled_display . '>';
			echo '<td colspan="2"><h5>'.__( 'After Notes Area', 'gleads' ) . '</h5></td>';
			echo '</tr>';

			echo '<tr class="gleads-notes-data gleads-notes-after-text"' . $enabled_display . '>';
				echo '<th>'.__( 'Message Text', 'gleads' ) . '</th>';
				echo '<td>';
					echo '<textarea class="widefat" name="gleads-notes[after-text]" id="gleads-after">'.esc_attr( $after_text ) . '</textarea>';
				echo '</td>';
			echo '</tr>';

			echo '<tr class="gleads-notes-data gleads-notes-after-type"' . $enabled_display . '>';
				echo '<th>'.__( 'Message Type', 'gleads' ) . '</th>';
				echo '<td>';
					echo '<select id="gleads-after-type" name="gleads-notes[after-type]">';
					echo '<option value="gleads-notes-standard"' . selected( $after_type, 'gleads-notes-standard', false ) . '>' . __( 'Standard', 'gleads' ) . '</option>';
					echo '<option value="gleads-notes-warning"' . selected( $after_type, 'gleads-notes-warning', false ) . '>' . __( 'Warning', 'gleads' ) . '</option>';
					echo '<option value="gleads-notes-alert"' . selected( $after_type, 'gleads-notes-alert', false ) . '>' . __( 'Alert', 'gleads' ) . '</option>';
					do_action( 'gleads_after_types', $after_type );
					echo '</select>';
				echo '</td>';
			echo '</tr>';

		echo '</table>';

	}

	/**
	 * save post metadata
	 *
	 * @return void
	 */

	public function save_custom_meta( $post_id ) {

		// make sure we aren't using autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// do our nonce check. ALWAYS A NONCE CHECK
		if ( ! isset( $_POST['gleads_meta_nonce'] ) || ! wp_verify_nonce( $_POST['gleads_meta_nonce'], 'gleads_meta_nonce' ) )
			return $post_id;

		$types = $this->get_post_types();

		if ( !in_array ( $_POST['post_type'], $types ) )
			return $post_id;

		// and make sure the user has the ability to do shit
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

		}

		// all clear. get data via $_POST and store it
		$notes	= ! empty( $_POST['gleads-notes'] ) ? $_POST['gleads-notes'] : false;

		// update side meta data
		if ( $notes ) {

			$allowed_html = array(
				'a'         => array(
					'href'  => array(),
					'title' => array(),
					'class' => array(),
					'id'    => array()
				),
				'br'        => array(),
				'em'        => array(),
				'strong'    => array(),
				'span'      => array(
					'class' => array(),
					'id'    => array()
				)
			);

			update_post_meta( $post_id, '_gleads_notes', wp_kses( $notes, $allowed_html ) );

			do_action( 'gleads_notes_save', $post_id, $notes );

		} else {
			delete_post_meta( $post_id, '_gleads_notes' );
		}

	}


	/**
	 * call front-end CSS
	 *
	 * @return void
	 */

	public function front_scripts() {

		// check for killswitch first
		$killswitch	= apply_filters( 'gleads_killswitch', false );

		if ( $killswitch )
			return false;

		$types = $this->get_post_types();

		if ( is_singular( $types ) )
			wp_enqueue_style( 'gleads-notes', plugins_url( 'lib/css/gleads-notes.css', __FILE__ ), array(), G_LEADS, 'all' );

	}

	/**
	 * The actual filter for adding the notes.
	 *
	 * @return array
	 */

	public function custom_notes_filter( $fields ) {

		global $post;

		// get the possible meta fields
		$notes	= get_post_meta( $post->ID, '_gleads_notes', true );

		if ( empty( $notes ) )
			return $fields;

		if ( isset( $notes['before-text'] ) ) :
			// grab the variables
			$text	= $notes['before-text'];
			$class	= isset( $notes['before-type'] ) ? $notes['before-type'] : 'gleads-notes-standard';
			// build the string

			$before	= '<p class="gleads-notes gleads-notes-before' . esc_attr( $class ) . '">' . $text . '</p>';

			// output
			$fields['comment_notes_before'] = $before;

		endif;

		if ( isset( $notes['after-text'] ) ) :
			// grab the variables
			$text	= $notes['after-text'];
			$class	= isset( $notes['after-type'] ) ? $notes['after-type'] : 'gleads-notes-standard';
			// build the string

			$after	= '<p class="gleads-notes gleads-notes-after' . esc_attr( $class ) . '">' . $text . '</p>';

			// output
			$fields['comment_notes_after'] = $after;

		endif;


		return $fields;
	}


	/**
	 * Return the list of post types that support Comment Notes
	 *
	 * @return array
	 */
	public function get_post_types() {

		$types = get_post_types( array( 'public' => true, 'show_ui' => true ) );

		foreach( $types as $type ) {
			if( ! post_type_supports( $type, 'comments' ) ) {
				unset( $types[ $type ] );
			}
		}

		return apply_filters( 'gleads_type_support', $types );
	}

/// end class
}


// Instantiate our class
$G_Leads = G_Leads::getInstance();