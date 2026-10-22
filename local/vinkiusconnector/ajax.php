<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();
$context = context_system::instance();

header('Content-Type: application/json; charset=utf-8');

$public_routes = array('agent-manifest','consent','consent/revoke','vinkius/v1/agent-manifest','vinkius/v1/consent','vinkius/v1/consent/revoke');

$route = isset($_GET['route']) ? trim($_GET['route'], '/') : '';
if ($route === '') {
    $route = isset($_REQUEST['route']) ? trim($_REQUEST['route'], '/') : '';
}

if (!in_array($route, $public_routes, true)) {
    $provided = $_REQUEST['sesskey'] ?? ($_SERVER['HTTP_X_WP_NONCE'] ?? '');
    if ($provided !== sesskey()) {
        http_response_code(403);
        echo json_encode(array('error' => 'invalid_sesskey'));
        exit;
    }
    require_capability('local/vinkiusconnector:manage', $context);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

require_once __DIR__ . '/includes/rest/class-rest.php';

$rest = 'VinkiusConnector\\Rest\\Rest';
$request = new WP_REST_Request();

$map = array(
    'agent-manifest' => 'agent_manifest',
    'vinkius/v1/agent-manifest' => 'agent_manifest',
    'consent' => 'consent_create',
    'vinkius/v1/consent' => 'consent_create',
    'consent/revoke' => 'consent_revoke',
    'vinkius/v1/consent/revoke' => 'consent_revoke',
    'panel/status' => 'panel_status',
    'panel/section' => 'panel_section',
    'panel/capabilities' => 'panel_capabilities',
    'panel/incidents' => 'panel_incidents',
    'panel/briefing' => 'panel_briefing',
    'panel/evidence-packs' => 'panel_evidence_packs',
    'panel/agents-export' => 'panel_agents_export',
    'panel/tokens' => 'panel_tokens',
    'panel/halt' => 'panel_halt',
    'panel/resume' => 'panel_resume',
    'panel/circuit-reset' => 'panel_circuit_reset',
    'panel/regen-secret' => 'panel_regen_secret',
    'panel/connector' => 'panel_connector',
    'panel/evidence-pack/generate' => 'panel_evidence_pack_generate',
    'panel/evidence-pack' => 'panel_evidence_pack',
    'panel/audit' => 'panel_audit',
    'panel/settings' => 'panel_settings',
    'auth/device/init' => 'device_init',
    'auth/device/poll' => 'device_poll',
    'auth/logout' => 'logout',
);

if (!isset($map[$route])) {
    http_response_code(404);
    echo json_encode(array('error' => 'not_found', 'route' => $route));
    exit;
}

$cb = array($rest, $map[$route]);
if (!is_callable($cb)) {
    http_response_code(500);
    echo json_encode(array('error' => 'not_callable'));
    exit;
}

try {
    $result = call_user_func($cb, $request);
    if ($result instanceof WP_REST_Response) {
        http_response_code($result->status ?: 200);
        echo json_encode($result->data);
    } else {
        echo json_encode($result);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(array('error' => $e->getMessage()));
}
exit;