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
        $defaults = [
            'page'          => 1,
            'per_page'      => 50,
            'orderby'       => 'date_added',
            'order'         => 'DESC',
            'search'        => '',
        ];

        $args = array_merge( $defaults, $args );

        $output = [];
        $orderby = in_array( $args['orderby'], [ 'url', 'runtime', 'date_added' ] ) ? sanitize_sql_orderby( $args['orderby'] ) : 'date_added';
        $order = in_array( $args['order'], [ 'ASC', 'DESC' ] ) ? sanitize_sql_orderby( $args['order'] ) : 'DESC';
        $page = absint( $args['page'] );
        $per_page = absint( $args['per_page'] );
        $offset = ( $page - 1 ) * $per_page;

        $this->sql = $this->wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS
                id, url, request_args, response, runtime, date_added
            FROM {$this->wpdb->prefix}lhr_log
            ORDER BY $orderby $order, id DESC
            LIMIT %d, %d",
            $offset,
            $per_page
        );
        $results = $this->wpdb->get_results( $this->sql, ARRAY_A );

        $total_rows = (int) $this->wpdb->get_var( "SELECT FOUND_ROWS()" );
        $total_pages = ceil( $total_rows / $per_page );

        $this->pager_args = [
            'page'          => $page,
            'per_page'      => $per_page,
            'total_rows'    => $total_rows,
            'total_pages'   => $total_pages,
        ];

        foreach ( $results as $row ) {
            $row['status_code'] = '-';
            $response = json_decode( $row['response'], true );
            if ( ! empty( $response['response']['code'] ) ) {
                $row['status_code'] = (int) $response['response']['code'];
            }
            $row['runtime'] = round( floatval( $row['runtime'] ), 4 );
            // Return timestamp for client-side formatting
            $row['date_timestamp'] = strtotime( $row['date_added'] . ' UTC' );
            $row['url'] = esc_url( $row['url'] );
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
                $output .= '<a class="lhr-page" data-page="' . absint( $page - 10 ) . '">' . absint( $page - 10 ) . '</a>';
            }
            for ( $i = 2; $i > 0; $i-- ) {
                if ( 0 < ( $page - $i ) ) {
                    $output .= '<a class="lhr-page" data-page="' . absint( $page - $i ) . '">' . absint( $page - $i ) . '</a>';
                }
            }

            // Current page
            $output .= '<a class="lhr-page active" data-page="' . absint( $page ) . '">' . absint( $page ) . '</a>';

            for ( $i = 1; $i <= 2; $i++ ) {
                if ( $total_pages >= ( $page + $i ) ) {
                    $output .= '<a class="lhr-page" data-page="' . absint( $page + $i ) . '">' . absint( $page + $i ) . '</a>';
                }
            }
            if ( $total_pages > ( $page + 10 ) ) {
                $output .= '<a class="lhr-page" data-page="' . absint( $page + 10 ) . '">' . absint( $page + 10 ) . '</a>';
            }
            if ( $total_pages > ( $page + 2 ) ) {
                $output .= '<a class="lhr-page last-page" data-page="' . absint( $total_pages ) . '">&gt;&gt;</a>';
            }
        }

        return $output;
    }
}
