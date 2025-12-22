<?php

class App {
    public static function baseUrl() {
        $protocol = ( !empty( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off'
        || $_SERVER[ 'SERVER_PORT' ] == 443 ) ? 'https' : 'http';
        $host = $_SERVER[ 'HTTP_HOST' ];
        $folder = '/www.ikawa.rw';
        return $protocol . '://' . $host . $folder;
    }
}
