<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;

define('VC_VERSION', '1.0.2');
define('VC_PLUGIN_DIR', __DIR__ . '/');
define('VC_PLUGIN_URL', $CFG->wwwroot . '/local/vinkiusconnector/');
define('VC_PLUGIN_BASENAME', 'local/vinkiusconnector');
define('VC_TEXT_DOMAIN', 'vinkius-connector');
define('VC_API_BASE_URL', 'https://api.vinkius.com');
define('VC_FRONTEND_BASE_URL', 'https://cloud.vinkius.com');

// WP-ish constants used by code.
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Autoloader for VinkiusConnector classes (kept under includes/).
require_once __DIR__ . '/includes/autoload.php';

// Compatibility shims for WordPress functions used by the ported code.
if (!function_exists('__')) {
    function __($text, $domain = null) { return $text; }
}
if (!function_exists('_e')) {
    function _e($text, $domain = null) { echo $text; }
}
if (!function_exists('esc_html')) {
    function esc_html($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_attr')) {
    function esc_attr($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_url')) {
    function esc_url($url) { return (string)$url; }
}
if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = null) { return esc_html($text); }
}
if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = null) { return esc_attr($text); }
}
if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = null) { echo esc_html($text); }
}
if (!function_exists('esc_attr_e')) {
    function esc_attr_e($text, $domain = null) { echo esc_attr($text); }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) { return trim(strip_tags((string)$str)); }
}
if (!function_exists('sanitize_title')) {
    function sanitize_title($title) { return strtolower(preg_replace('/[^a-z0-9]+/', '-', (string)$title)); }
}
if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data) { return json_encode($data); }
}
if (!function_exists('current_time')) {
    function current_time($type, $gmt = 0) { return gmdate('Y-m-d H:i:s'); }
}
if (!function_exists('wp_generate_password')) {
    function wp_generate_password($length = 12, $special = true, $extra_special = false) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ($special) { $chars .= '!@#$%^&*()'; }
        $out = '';
        for ($i=0;$i<$length;$i++) { $out .= $chars[random_int(0, strlen($chars)-1)]; }
        return $out;
    }
}
if (!function_exists('get_option')) {
    function get_option($key, $default = false) {
        global $DB;
        if ($key === 'admin_email') {
            global $CFG;
            return $CFG->supportemail ?? '';
        }
        $rec = $DB->get_record('config_plugins', array('plugin'=>'local_vinkiusconnector','name'=>$key));
        if ($rec) {
            $val = @unserialize($rec->value);
            if ($val === false && $rec->value !== serialize(false)) {
                return $rec->value;
            }
            return $val;
        }
        return $default;
    }
}
if (!function_exists('update_option')) {
    function update_option($key, $value) {
        global $DB;
        $component = 'local_vinkiusconnector';
        $existing = $DB->get_record('config_plugins', array('plugin'=>$component,'name'=>$key));
        $val = is_array($value) || is_object($value) ? serialize($value) : $value;
        if ($existing) {
            $existing->value = $val;
            $DB->update_record('config_plugins', $existing);
        } else {
            $rec = new stdClass();
            $rec->plugin = $component;
            $rec->name = $key;
            $rec->value = $val;
            $DB->insert_record('config_plugins', $rec);
        }
    }
}
if (!function_exists('delete_option')) {
    function delete_option($key) {
        global $DB;
        $DB->delete_records('config_plugins', array('plugin'=>'local_vinkiusconnector','name'=>$key));
    }
}
if (!function_exists('get_transient')) {
    function get_transient($key) { return get_option($key, false); }
}
if (!function_exists('set_transient')) {
    function set_transient($key, $value, $expiration = 0) { update_option($key, $value); return true; }
}
if (!function_exists('delete_transient')) {
    function delete_transient($key) { delete_option($key); return true; }
}
if (!function_exists('wp_remote_request')) {
    // not used; HttpClient uses curl directly
}
if (!function_exists('rest_url')) {
    function rest_url($path = '') {
        global $CFG;
        $parts = explode('?', (string)$path, 2);
        $url = $CFG->wwwroot . '/local/vinkiusconnector/ajax.php?route=' . urlencode(ltrim($parts[0], '/'));
        if (isset($parts[1]) && $parts[1] !== '') {
            $url .= '&' . $parts[1];
        }
        return $url;
    }
}
if (!function_exists('admin_url')) {
    function admin_url($path = '') { global $CFG; return $CFG->wwwroot . '/local/vinkiusconnector/index.php'; }
}
if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show) {
        global $CFG, $SITE;
        if ($show === 'name') { return $SITE->fullname ?? $CFG->sitename ?? 'Moodle'; }
        if ($show === 'description') { return $CFG->sitedescription ?? ''; }
        return '';
    }
}
if (!function_exists('get_site_url')) {
    function get_site_url() { global $CFG; return $CFG->wwwroot; }
}
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) { return __DIR__ . '/'; }
}
if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) { global $CFG; return $CFG->wwwroot . '/local/vinkiusconnector/'; }
}
if (!function_exists('plugin_basename')) {
    function plugin_basename($file) { return 'local/vinkiusconnector/vinkiusconnector.php'; }
}
if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) { return true; }
}
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) { return true; }
}
if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) { return true; }
}
if (!function_exists('do_action')) {
    function do_action($hook, ...$args) { return null; }
}
if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value, ...$args) { return $value; }
}
if (!function_exists('wp_enqueue_style')) { function wp_enqueue_style(...$a){} }
if (!function_exists('wp_enqueue_script')) { function wp_enqueue_script(...$a){} }
if (!function_exists('wp_localize_script')) { function wp_localize_script(...$a){} }
if (!function_exists('wp_add_dashboard_widget')) { function wp_add_dashboard_widget(...$a){} }
if (!function_exists('register_activation_hook')) { function register_activation_hook(...$a){} }
if (!function_exists('register_deactivation_hook')) { function register_deactivation_hook(...$a){} }
if (!function_exists('is_wp_error')) { function is_wp_error($thing){ return false; } }
if (!function_exists('wp_remote_retrieve_response_code')) { function wp_remote_retrieve_response_code($r){ return $r['code'] ?? 0; } }
if (!function_exists('wp_remote_retrieve_body')) { function wp_remote_retrieve_body($r){ return $r['body'] ?? ''; } }
if (!function_exists('wp_unslash')) { function wp_unslash($v){ return $v; } }
if (!function_exists('sanitize_email')) { function sanitize_email($email){ return filter_var($email, FILTER_SANITIZE_EMAIL); } }
if (!function_exists('wp_verify_nonce')) { function wp_verify_nonce($nonce, $action){ return true; } }
if (!function_exists('wp_safe_redirect')) { function wp_safe_redirect($location){ header('Location: '.$location); exit; } }
if (!function_exists('add_query_arg')) { function add_query_arg($args, $url=''){ if (is_array($args)) { $q=http_build_query($args); return $url.'?'.$q; } return $url; } }
if (!function_exists('wp_die')) { function wp_die($msg=''){ echo $msg; exit; } }
if (!function_exists('wp_parse_args')) {
    function wp_parse_args($args, $defaults = array()) {
        if (is_object($args)) { $args = get_object_vars($args); }
        if (!is_array($args)) { $args = array(); }
        return array_merge($defaults, $args);
    }
}
if (!function_exists('current_user_can')) {
    function current_user_can($cap) {
        global $USER;
        return is_siteadmin($USER->id ?? 0);
    }
}
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action) { return sesskey(); }
}
if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action, $name='_wpnonce', $referer=true, $echo=true) {
        $field = '<input type="hidden" name="'.$name.'" value="'.sesskey().'" />';
        if ($echo) { echo $field; return null; }
        return $field;
    }
}
if (!function_exists('check_admin_referer')) {
    function check_admin_referer($action) { return true; }
}

// Moodle DB helper for ConsentRepo table name.
function vc_consents_table() { return 'local_vinkiusconnector_consents'; }

if (!defined('MINUTE_IN_SECONDS')) { define('MINUTE_IN_SECONDS', 60); }
if (!defined('HOUR_IN_SECONDS')) { define('HOUR_IN_SECONDS', 3600); }
if (!function_exists('wp_next_scheduled')) { function wp_next_scheduled($hook){ return false; } }
if (!function_exists('wp_schedule_event')) { function wp_schedule_event(...$a){ return true; } }
if (!function_exists('wp_mail')) { function wp_mail(...$a){ return true; } }
if (!function_exists('add_shortcode')) { function add_shortcode(...$a){ return true; } }
if (!function_exists('get_current_user_id')) { function get_current_user_id() { global $USER; return (int)($USER->id ?? 0); } }
if (!function_exists('get_locale')) { function get_locale() { return 'en_US'; } }
if (!function_exists('wp_rand')) { function wp_rand($min=0,$max=0){ return random_int($min?:0, $max?:mt_getrandmax()); } }

// WP REST shims.
if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response {
        public $data;
        public $status = 200;
        public function __construct($data=null, $status=200) { $this->data=$data; $this->status=$status; }
    }
}
if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request {
        public $method = 'GET';
        private $body = null;
        private function load_body() {
            if ($this->body !== null) { return; }
            $raw = file_get_contents('php://input');
            $dec = json_decode($raw, true);
            $this->body = is_array($dec) ? $dec : array();
        }
        public function get_param($key) {
            if (isset($_REQUEST[$key])) { return $_REQUEST[$key]; }
            $this->load_body();
            return array_key_exists($key, $this->body) ? $this->body[$key] : null;
        }
    }
}
if (!function_exists('rest_ensure_response')) {
    function rest_ensure_response($data) { return $data; }
}
if (!function_exists('rest_url')) {
    // already defined above
}