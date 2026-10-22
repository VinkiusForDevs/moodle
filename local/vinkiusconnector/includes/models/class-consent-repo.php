<?php
/**
 * Visitor-consent repository (Moodle port).
 *
 * @license GPLv2 or later
 */

namespace VinkiusConnector\Models;

defined( 'ABSPATH' ) || exit;

final class ConsentRepo {

    const SCOPES = array(
        'content.read',
        'content.write',
        'users.subscribe',
        'media.upload',
    );

    public static function table() {
        return 'local_vinkiusconnector_consents';
    }

    public static function record( $external_id, $wp_user_id, array $scopes, $purpose = 'agent_action', $ip = '' ) {
        global $DB;

        $external_id = sanitize_text_field( (string) $external_id );
        if ( '' === $external_id ) {
            return 0;
        }

        $valid_scopes = array_values( array_intersect( $scopes, self::SCOPES ) );
        $valid_scopes = empty( $valid_scopes ) ? array( 'content.read' ) : $valid_scopes;

        $ip_hash = self::hash_ip( $ip );

        $existing = $DB->get_record( self::table(), array( 'external_id' => $external_id ) );

        $now = gmdate( 'Y-m-d H:i:s' );

        if ( $existing ) {
            $existing->moodle_user_id = (int) $wp_user_id;
            $existing->scopes = wp_json_encode( $valid_scopes );
            $existing->purpose = sanitize_text_field( (string) $purpose );
            $existing->ip_hash = $ip_hash;
            $existing->revoked_at = null;
            $existing->created_at = $now;
            $DB->update_record( self::table(), $existing );
            return (int) $existing->id;
        }

        $rec = new stdClass();
        $rec->external_id = $external_id;
        $rec->moodle_user_id = (int) $wp_user_id;
        $rec->scopes = wp_json_encode( $valid_scopes );
        $rec->purpose = sanitize_text_field( (string) $purpose );
        $rec->ip_hash = $ip_hash;
        $rec->created_at = $now;
        $rec->revoked_at = null;

        return (int) $DB->insert_record( self::table(), $rec );
    }

    public static function revoke( $row_id ) {
        global $DB;
        $rec = $DB->get_record( self::table(), array( 'id' => (int) $row_id ) );
        if ( ! $rec ) {
            return false;
        }
        $rec->revoked_at = gmdate( 'Y-m-d H:i:s' );
        $DB->update_record( self::table(), $rec );
        return true;
    }

    public static function find( $external_id ) {
        global $DB;
        $row = $DB->get_record( self::table(), array( 'external_id' => sanitize_text_field( (string) $external_id ) ) );
        return $row ? (array) $row : null;
    }

    public static function list_all( $limit = 100 ) {
        global $DB;
        $limit = max( 1, min( (int) $limit, 500 ) );
        $rows = $DB->get_records( self::table(), null, 'created_at DESC', '*', 0, $limit );
        $out = array();
        foreach ( $rows as $r ) { $out[] = (array) $r; }
        return $out;
    }

    public static function is_active( $external_id, $scope ) {
        $row = self::find( $external_id );
        if ( ! $row || ! empty( $row['revoked_at'] ) ) {
            return false;
        }
        $scopes = json_decode( (string) $row['scopes'], true );
        return is_array( $scopes ) && in_array( $scope, $scopes, true );
    }

    public static function revoke_token( $row_id ) {
        global $CFG;
        $secret = (string) \VinkiusConnector\Options::get( 'site_secret' );
        return substr( hash_hmac( 'sha256', 'revoke:' . (int) $row_id, $secret ), 0, 32 );
    }

    public static function resolve_revoke_token( $token ) {
        global $DB;
        $token = substr( (string) $token, 0, 32 );
        if ( '' === $token ) {
            return 0;
        }
        $secret = (string) \VinkiusConnector\Options::get( 'site_secret' );
        $rows = $DB->get_records( self::table(), null, 'id ASC' );
        foreach ( $rows as $r ) {
            $row_token = substr( hash_hmac( 'sha256', 'revoke:' . (int) $r->id, $secret ), 0, 32 );
            if ( hash_equals( $row_token, $token ) ) {
                return (int) $r->id;
            }
        }
        return 0;
    }

    private static function hash_ip( $ip ) {
        $ip = trim( (string) $ip );
        if ( '' === $ip ) {
            return '';
        }
        global $CFG;
        return substr( hash_hmac( 'sha256', $ip, 'moodle_salt' . ( $CFG->wwwroot ?? '' ) ), 0, 64 );
    }
}