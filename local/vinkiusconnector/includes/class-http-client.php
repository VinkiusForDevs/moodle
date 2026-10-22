<?php
/**
 * Minimal HTTP client (Moodle port).
 */
namespace VinkiusConnector;

defined( 'ABSPATH' ) || exit;

final class HttpClient {

    const TIMEOUT = 30;

    public function request( $method, $path, $body = null, $bearer = null ) {
        $base = rtrim( VC_API_BASE_URL, '/' );
        $url  = $base . ( 0 === strpos( $path, '/' ) ? $path : '/' . $path );

        $ch = curl_init( $url );
        $headers = array(
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
        );
        if ( null !== $bearer && '' !== $bearer ) {
            $headers[] = 'Authorization: Bearer ' . $bearer;
        }

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_TIMEOUT, self::TIMEOUT );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, strtoupper( $method ) );
        if ( null !== $body ) {
            curl_setopt( $ch, CURLOPT_POSTFIELDS, wp_json_encode( $body ) );
        }

        $raw = curl_exec( $ch );
        $err = curl_error( $ch );
        $status = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );

        if ( false === $raw || '' !== $err ) {
            return Result::error( 'vc_http_curl', array( 'message' => $err ) );
        }

        $decoded = json_decode( $raw, true );

        if ( $status >= 200 && $status < 300 ) {
            return Result::ok( is_array( $decoded ) ? $decoded : array( 'raw' => $decoded ) );
        }

        $code = 'vc_http_' . $status;
        $data = is_array( $decoded ) ? $decoded : array( 'message' => $raw );
        return Result::error( $code, $data );
    }
}