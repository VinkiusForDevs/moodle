<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();
$context = context_system::instance();
require_capability('local/vinkiusconnector:manage', $context);

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url(new moodle_url('/local/vinkiusconnector/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_vinkiusconnector'));

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vc_save_settings') {
    \VinkiusConnector\Admin\ScreenSettings::save_settings();
    exit;
}

$slug = isset($_GET['page']) ? preg_replace('/[^a-z0-9\-]/', '', $_GET['page']) : 'connect';
if ($slug === '') { $slug = 'connect'; }

$screen_map = array(
    'connect' => 'VinkiusConnector\\Admin\\ScreenConnect',
    'mission-control' => 'VinkiusConnector\\Admin\\ScreenMissionControl',
    'agent-activity' => 'VinkiusConnector\\Admin\\ScreenAgentActivity',
    'connector-traffic' => 'VinkiusConnector\\Admin\\ScreenConnectorTraffic',
    'access-tokens' => 'VinkiusConnector\\Admin\\ScreenAccessTokens',
    'agent-passport' => 'VinkiusConnector\\Admin\\ScreenAgentPassport',
    'ai-spend' => 'VinkiusConnector\\Admin\\ScreenAiSpend',
    'capability-reliability' => 'VinkiusConnector\\Admin\\ScreenCapabilityReliability',
    'security-posture' => 'VinkiusConnector\\Admin\\ScreenSecurityPosture',
    'request-failures' => 'VinkiusConnector\\Admin\\ScreenRequestFailures',
    'compliance' => 'VinkiusConnector\\Admin\\ScreenCompliance',
    'incidents' => 'VinkiusConnector\\Admin\\ScreenIncidents',
    'consent' => 'VinkiusConnector\\Admin\\ScreenConsent',
    'settings' => 'VinkiusConnector\\Admin\\ScreenSettings',
);

if (!isset($screen_map[$slug])) { $slug = 'connect'; }
$screen_class = $screen_map[$slug];

echo $OUTPUT->header();

// VCConfig (must precede scripts)
$auth = \VinkiusConnector\Auth\AuthStore::status();
$flow = new \VinkiusConnector\Auth\DeviceFlow();
$links = \VinkiusConnector\Links::all();
$config = array(
    'restUrl' => $CFG->wwwroot.'/local/vinkiusconnector/ajax.php?',
    'restNonce' => sesskey(),
    'links' => $links,
    'gov' => array(
        'capabilityBase' => \VinkiusConnector\Links::capability(''),
        'upgrade' => \VinkiusConnector\Links::cloud('upgrade'),
    ),
    'labels' => array(),
);
echo '<script>var VCConfig = '.json_encode($config).';</script>';

// Assets
$base = VC_PLUGIN_URL;
echo '<link rel="stylesheet" href="'.$base.'admin/css/vinkius-connector.css">';
echo '<link rel="stylesheet" href="'.$base.'admin/css/vc-governance.css">';
echo '<script src="'.$base.'admin/js/vinkius-connector.js"></script>';
echo '<script src="'.$base.'admin/js/vendor/echarts.min.js"></script>';
echo '<script src="'.$base.'admin/js/vc-charts.js"></script>';
echo '<script src="'.$base.'admin/js/vc-governance.js"></script>';

// Simple navigation
echo '<div class="vc-nav" style="margin:10px 0;">';
foreach ($screen_map as $s => $c) {
    $active = ($s === $slug) ? 'font-weight:bold;' : '';
    echo '<a style="margin-right:8px;'.$active.'" href="'.new moodle_url('/local/vinkiusconnector/index.php', array('page'=>$s)).'">'.$s.'</a>';
}
echo '</div>';

$screen_class::render_page();

echo $OUTPUT->footer();