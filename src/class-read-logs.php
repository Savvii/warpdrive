<?php

namespace Savvii;

/**
 * Class ReadLogs
 * @package Savvii
 */
class ReadLogs {

    const LOG_LINE_SIZE = 1024;

    function clean_log_name( $log ) {
        return ( 'access' === $log || 'error' === $log ) ? $log : 'access';
    }

    function clean_lines( $lines ) {
        $nr = intval( $lines );
        return ( 10 === $nr || 100 === $nr ) ? $nr : 10;
    }

    function get_log_lines( $log, $lines ) {
        // Clean parameters
        $log   = $this->clean_log_name( $log );
        $lines = $this->clean_lines( $lines );

        // Search for log file
        $log_path = $this->search_log_file( Options::system_name(), $log );
        if ( is_null( $log_path ) ) {
            return [ \ucfirst( $log ) . ' log not found.' ];
        }

        // Read lines from log file
        $log_lines = $this->read_lines_from_log_file( $log_path, $lines );
        if ( ! count( $log_lines ) ) {
            $log_lines = [ \ucfirst( $log ) . ' log empty' ];
        }

        return $log_lines;
    }

    function find_match_in_log( $log, $regex) {
         // Clean log name
        $log     = $this->clean_log_name( $log );
        
        // Search for log file
        $log_path = $this->search_log_file( Options::system_name(), $log );
        if ( is_null( $log_path ) ) {
            return [ \ucfirst( $log ) . ' log not found.' ];
        }

        $file    = fopen( $log_path, 'r' );
        $results = array();
        while (($bufferedline = fgets($file))) {
            if (preg_match( $regex, $bufferedline )) {
                array_push( $results, $bufferedline);
            }
        }
        fclose( $file );
        return $results;
    }

    function search_log_file( $system_name, $log ) {
        // Construct log path
        $log_path = "/var/www/{$system_name}/log/{$system_name}.{$log}.log";
        return $this->file_exists( $log_path ) ? $log_path : null;
    }

    function read_lines_from_log_file( $path, $lines ) {
        // Calculate offset
        $log_size = $this->file_size( $path );
        $length   = $lines * self::LOG_LINE_SIZE;
        $offset   = $log_size - $length;
        if ( 0 > $offset ) {
            $offset = 0;
        }
        // Get contents as array
        $log_lines = explode( "\n", $this->file_get_contents( $path, $offset, $length ) );
        // Remove empty line
        array_pop( $log_lines );
        // Get slice of lines we want and reverse order to have most recent lines first
        return array_reverse( array_slice( $log_lines, -$lines ) );
    }

    function print_lines( $log, $lines ) {
        $result = self::get_log_lines( $log, $lines );
        if (count($result) == 1) {
            printf("<p>%s</p>", $result[0]);        
        } else {
            foreach ( $result as $line ) {
                $e = explode( " ", $line );
                if ( $log === 'access' ) {
                    printf("<p><span class='date'>%s</span>
                        <span class='ip_address'>%s</span>
                        <span class='http_version'>%s</span>
                        <span class='http_status'>%s</span>
                        <span class='url'>%s</span></p>", 
                        substr($e[3], 1), $e[0], $e[7], $e[8], $e[6]);
                }
                if ( $log === 'error' ) {
                    printf("<p><span class='date'>%s %s</span>%s</p>", $e[0], $e[1], substr($line, 19));
                } 
            }
        }
    }

    /**
     * @param $path
     * @return bool
     * @codeCoverageIgnore
     */
    function file_exists( $path ) {
        return \file_exists( $path );
    }

    /**
     * @param $path
     * @return int
     * @codeCoverageIgnore
     */
    function file_size( $path ) {
        return \filesize( $path );
    }

    /**
     * @param $path
     * @param $offset
     * @param $maxlength
     * @return string
     * @codeCoverageIgnore
     */
    function file_get_contents( $path, $offset, $maxlength ) {
        return \file_get_contents( $path, null, null, $offset, $maxlength );
    }
}
