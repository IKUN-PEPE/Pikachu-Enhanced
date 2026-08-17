<?php
/**
 * Real-time Docker Lab Sandbox API
 * Provides instant status checking, start, stop, and batch clean operations for lab containers.
 */

header('Content-Type: application/json; charset=utf-8');

$action = trim($_REQUEST['action'] ?? 'status');
$name = trim($_REQUEST['name'] ?? '');

$DOCKER_CLI = '/var/www/html/docker-cli';

$WHITELIST = array(
    'pikachu-lab-priv-escape' => array(
        'name' => 'pikachu-lab-priv-escape',
        'run_cmd' => "$DOCKER_CLI run -d --name pikachu-lab-priv-escape --privileged -v /:/host_root docker.m.daocloud.io/library/ubuntu:22.04 sleep infinity",
        'init_cmd' => "$DOCKER_CLI exec pikachu-lab-priv-escape bash -c 'echo \"flag{Docker_Privileged_Mode_Host_Mount_Escape_Done}\" > /host_root/flag_host.txt'",
        'title' => '特权模式逃逸 (--privileged)'
    ),
    'pikachu-lab-sock-escape' => array(
        'name' => 'pikachu-lab-sock-escape',
        'run_cmd' => "$DOCKER_CLI run -d --name pikachu-lab-sock-escape -v /var/run/docker.sock:/var/run/docker.sock docker.m.daocloud.io/library/ubuntu:22.04 sleep infinity",
        'init_cmd' => "$DOCKER_CLI exec pikachu-lab-sock-escape bash -c 'echo \"flag{docker_sock_Mode_Host_Mount_Escape_Done}\" > /etc/docker_escape_flag.txt'",
        'title' => 'Docker Socket 敏感挂载逃逸'
    ),
    'pikachu-lab-caps-escape' => array(
        'name' => 'pikachu-lab-caps-escape',
        'run_cmd' => "$DOCKER_CLI run -d --name pikachu-lab-caps-escape --cap-add=SYS_ADMIN --security-opt apparmor=unconfined docker.m.daocloud.io/library/ubuntu:22.04 sleep infinity",
        'init_cmd' => "$DOCKER_CLI exec pikachu-lab-caps-escape bash -c 'echo \"flag{Linux_CAP_SYS_ADMIN_Escape_Success}\" > /tmp/caps_escape_flag.txt'",
        'title' => 'Linux Capabilities 逃逸 (CAP_SYS_ADMIN)'
    ),
    'pikachu-lab-cve-escape' => array(
        'name' => 'pikachu-lab-cve-escape',
        'run_cmd' => "$DOCKER_CLI run -d --name pikachu-lab-cve-escape docker.m.daocloud.io/library/ubuntu:22.04 sleep infinity",
        'init_cmd' => "$DOCKER_CLI exec pikachu-lab-cve-escape bash -c 'echo \"flag{Docker_Runc_CVE_2019_5736_Overwrite_Success}\" > /etc/docker_cve_flag.txt'",
        'title' => 'Docker 组件与内核 CVE 逃逸'
    ),
    'pikachu-lab-k8s-escape' => array(
        'name' => 'pikachu-lab-k8s-escape',
        'run_cmd' => "$DOCKER_CLI run -d --name pikachu-lab-k8s-escape -v /var/run/secrets/kubernetes.io/serviceaccount:/var/run/secrets/kubernetes.io/serviceaccount docker.m.daocloud.io/library/ubuntu:22.04 sleep infinity",
        'init_cmd' => "$DOCKER_CLI exec pikachu-lab-k8s-escape bash -c 'echo \"flag{K8s_ServiceAccount_Token_ClusterRole_Pwned}\" > /var/run/secrets/kubernetes.io/serviceaccount/token'",
        'title' => 'Kubernetes 越权逃逸'
    )
);

function get_single_status($container_name, $docker_cli) {
    $escaped = escapeshellarg('^' . $container_name . '$');
    $out = shell_exec("$docker_cli ps --filter name=$escaped --format \"{{.Names}}\\t{{.Status}}\" 2>/dev/null");
    $out = trim($out ?? '');
    if (!empty($out) && strpos($out, $container_name) !== false) {
        $parts = explode("\t", $out);
        $uptime = isset($parts[1]) ? trim($parts[1]) : 'Running';
        return array(
            'running' => true,
            'status' => 'running',
            'statusText' => '运行中 (Running)',
            'uptime' => $uptime
        );
    }
    return array(
        'running' => false,
        'status' => 'stopped',
        'statusText' => '已停止 (Stopped)',
        'uptime' => ''
    );
}

switch ($action) {
    case 'status':
        if (!isset($WHITELIST[$name])) {
            echo json_encode(array('success' => false, 'error' => '未知或非法容器名称: ' . htmlspecialchars($name)));
            exit;
        }
        $st = get_single_status($name, $DOCKER_CLI);
        echo json_encode(array(
            'success' => true,
            'container' => $name,
            'running' => $st['running'],
            'status' => $st['status'],
            'statusText' => $st['statusText'],
            'uptime' => $st['uptime']
        ));
        break;

    case 'all_status':
        $results = array();
        $out = shell_exec("$DOCKER_CLI ps --filter name=pikachu-lab- --format \"{{.Names}}\\t{{.Status}}\" 2>/dev/null");
        $out = trim($out ?? '');
        $running_map = array();
        if (!empty($out)) {
            $lines = explode("\n", $out);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $parts = explode("\t", $line);
                    $c_name = trim($parts[0]);
                    $c_status = isset($parts[1]) ? trim($parts[1]) : 'Running';
                    $running_map[$c_name] = $c_status;
                }
            }
        }
        foreach ($WHITELIST as $key => $info) {
            $is_run = isset($running_map[$key]);
            $results[$key] = array(
                'running' => $is_run,
                'status' => $is_run ? 'running' : 'stopped',
                'statusText' => $is_run ? '运行中 (Running)' : '已停止 (Stopped)',
                'uptime' => $is_run ? $running_map[$key] : ''
            );
        }
        echo json_encode(array('success' => true, 'sandboxes' => $results));
        break;

    case 'start':
        if (!isset($WHITELIST[$name])) {
            echo json_encode(array('success' => false, 'error' => '未知或非法容器名称: ' . htmlspecialchars($name)));
            exit;
        }
        $conf = $WHITELIST[$name];
        
        // 1. Force remove existing container if any
        shell_exec("$DOCKER_CLI rm -f " . escapeshellarg($name) . " 2>&1");
        
        // 2. Start container
        $run_out = shell_exec($conf['run_cmd'] . " 2>&1");
        
        // 3. Initialize Flag inside container
        if (!empty($conf['init_cmd'])) {
            shell_exec($conf['init_cmd'] . " 2>&1");
        }
        
        // 4. Verify running state
        $check = get_single_status($name, $DOCKER_CLI);
        if ($check['running']) {
            echo json_encode(array(
                'success' => true,
                'running' => true,
                'container' => $name,
                'statusText' => $check['statusText'],
                'uptime' => $check['uptime'],
                'message' => "真实靶场容器 [{$name}] 已部署并在后台运行！"
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'running' => false,
                'container' => $name,
                'error' => "启动失败，Docker 输出: " . substr($run_out, 0, 300)
            ));
        }
        break;

    case 'stop':
        if (!isset($WHITELIST[$name]) && strpos($name, 'pikachu-lab-') !== 0) {
            echo json_encode(array('success' => false, 'error' => '未知或非法容器名称: ' . htmlspecialchars($name)));
            exit;
        }
        shell_exec("$DOCKER_CLI rm -f " . escapeshellarg($name) . " 2>&1");
        $check = get_single_status($name, $DOCKER_CLI);
        echo json_encode(array(
            'success' => true,
            'running' => $check['running'],
            'container' => $name,
            'statusText' => $check['statusText'],
            'message' => "真实靶场容器 [{$name}] 已强制销毁清理！"
        ));
        break;

    case 'stop_all':
        $ids = shell_exec("$DOCKER_CLI ps -a -q --filter name=pikachu-lab- 2>/dev/null");
        $ids = trim($ids ?? '');
        if (!empty($ids)) {
            shell_exec("$DOCKER_CLI rm -f $ids 2>&1");
        }
        echo json_encode(array(
            'success' => true,
            'message' => "所有临时靶场沙箱容器已全部成功销毁释放！"
        ));
        break;

    default:
        echo json_encode(array('success' => false, 'error' => '不支持的操作类型: ' . htmlspecialchars($action)));
        break;
}
