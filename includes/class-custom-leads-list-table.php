<?php

if ( !class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Custom_Leads_List_Table extends WP_List_Table
{

    public function __construct()
    {
        parent::__construct( [
            'singular' => __( 'Lead', 'textdomain' ), // singular name of the listed records
            'plural'   => __( 'Leads', 'textdomain' ), // plural name of the listed records
            'ajax'     => false, // does this table support ajax?
        ] );
    }

    public function get_columns()
    {
        $columns = [
            'cb'          => '<input type="checkbox" />', // Render a checkbox instead of text
            'message'     => __( 'Message', 'textdomain' ),
            'status'      => __( 'Status', 'textdomain' ),
            'phone'       => __( 'Phone', 'textdomain' ),
            'country'     => __( 'Country', 'textdomain' ),
            'create_date' => __( 'Create Date', 'textdomain' ),
            'actions'     => __( 'Actions', 'textdomain' ),
        ];

        return $columns;
    }

    public function column_cb( $item )
    {
        return sprintf( '<input type="checkbox" name="lead[]" value="%s" />', $item->id );
    }

    public function column_actions( $item )
    {
        $actions = [
            'edit'   => sprintf( '<a href="?page=%s&action=%s&lead=%s">' . __( 'Edit', 'textdomain' ) . '</a>', $_REQUEST['page'], 'edit', $item->id ),
            'delete' => sprintf( '<a href="?page=%s&action=%s&lead=%s">' . __( 'Delete', 'textdomain' ) . '</a>', $_REQUEST['page'], 'delete', $item->id ),
        ];

        return $this->row_actions( $actions );
    }

    public function prepare_items()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'custom_lead'; // do not forget about tables prefix

        $per_page = 10; // constant, how much records will be shown per page

        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $this->process_bulk_action();

        $total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );

        $paged  = $this->get_pagenum();
        $offset = ( $paged - 1 ) * $per_page;

        $items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name LIMIT %d OFFSET %d", $per_page, $offset ) );

        $this->items = $items;

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ] );
    }

    public function column_default( $item, $column_name )
    {
        switch ( $column_name ) {
            case 'message':
            case 'status':
            case 'phone':
            case 'country':
            case 'create_date':
                return $item->$column_name;
            default:
                return print_r( $item, true ); // Show the whole array for troubleshooting purposes
        }
    }

    public function get_bulk_actions()
    {
        $actions = [
            'delete' => 'Delete',
        ];

        return $actions;
    }

    public function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_lead';

        if ( 'delete' === $this->current_action() ) {
            $ids = isset( $_REQUEST['lead'] ) ? $_REQUEST['lead'] : [];

            if ( is_array( $ids ) ) {
                $ids = implode( ',', $ids );
            }

            if ( !empty( $ids ) ) {
                $wpdb->query( "DELETE FROM $table_name WHERE id IN($ids)" );
            }
        }
    }
}