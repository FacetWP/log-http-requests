<?php

class LHR_Query
{
    public $wpdb;
    public $sql;
    public $pager_args;


    function __construct() {
        $this->wpdb = $GLOBALS['wpdb'];
    }


    function get_results( $args ) {
        $defaults = array(
            'page'          => 1,
            'per_page'      => 50,
            'orderby'       => 'date_added',
            'order'         => 'DESC',
            'search'        => '',
        );

        $args = array_merge( $defaults, $args );

        $output = array();
        $orderby = in_array( $args['orderby'], array( 'url', 'runtime', 'date_added' ) ) ? $args['orderby'] : 'date_added';
        $order = in_array( $args['order'], array( 'ASC', 'DESC' ) ) ? $args['order'] : 'DESC';
        $page = (int) $args['page'];
        $per_page = (int) $args['per_page'];
        $limit = ( ( $page - 1 ) * $per_page ) . ',' . $per_page;

        $this->sql = "
            SELECT
                SQL_CALC_FOUND_ROWS
                id, url, request_args, response, runtime, date_added
            FROM {$this->wpdb->prefix}lhr_log
            ORDER BY $orderby $order, id DESC
            LIMIT $limit
        ";
        $results = $this->wpdb->get_results( $this->sql, ARRAY_A );

        $total_rows = (int) $this->wpdb->get_var( "SELECT FOUND_ROWS()" );
        $total_pages = ceil( $total_rows / $per_page );

        $this->pager_args = array(
            'page'          => $page,
            'per_page'      => $per_page,
            'total_rows'    => $total_rows,
            'total_pages'   => $total_pages,
        );

        foreach ( $results as $row ) {
            $row['runtime'] = round( $row['runtime'], 4 );
            $row['date_added'] = LHR()->time_since( $row['date_added'] );
            $output[] = $row;
        }

        return $output;
    }


    function truncate_table() {
        $this->wpdb->query( "TRUNCATE TABLE {$this->wpdb->prefix}lhr_log" );
    }


    function paginate() {
        $params = $this->pager_args;

        $output = '';
        $page = (int) $params['page'];
        $per_page = (int) $params['per_page'];
        $total_rows = (int) $params['total_rows'];
        $total_pages = (int) $params['total_pages'];

        // Only show pagination when > 1 page
        if ( 1 < $total_pages ) {

            if ( 3 < $page ) {
                $output .= '<a class="lhr-page first-page" data-page="1">&lt;&lt;</a>';
            }
            if ( 1 < ( $page - 10 ) ) {
                $output .= '<a class="lhr-page" data-page="' . ($page - 10) . '">' . ($page - 10) . '</a>';
            }
            for ( $i = 2; $i > 0; $i-- ) {
                if ( 0 < ( $page - $i ) ) {
                    $output .= '<a class="lhr-page" data-page="' . ($page - $i) . '">' . ($page - $i) . '</a>';
                }
            }

            // Current page
            $output .= '<a class="lhr-page active" data-page="' . $page . '">' . $page . '</a>';

            for ( $i = 1; $i <= 2; $i++ ) {
                if ( $total_pages >= ( $page + $i ) ) {
                    $output .= '<a class="lhr-page" data-page="' . ($page + $i) . '">' . ($page + $i) . '</a>';
                }
            }
            if ( $total_pages > ( $page + 10 ) ) {
                $output .= '<a class="lhr-page" data-page="' . ($page + 10) . '">' . ($page + 10) . '</a>';
            }
            if ( $total_pages > ( $page + 2 ) ) {
                $output .= '<a class="lhr-page last-page" data-page="' . $total_pages . '">&gt;&gt;</a>';
            }
        }

        return $output;
    }
}
