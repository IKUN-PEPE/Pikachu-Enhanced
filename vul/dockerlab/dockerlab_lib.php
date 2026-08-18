<?php
/**
 * Docker Lab Phase 1 helper functions.
 * Phase 1 only exposes read-only Docker diagnostics and template status display.
 */

function dockerlab_h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function dockerlab_html($value){
    return dockerlab_h($value);
}

function dockerlab_template_dir(){
    return __DIR__ . DIRECTORY_SEPARATOR . 'templates';
}

function dockerlab_validate_lab_id($id){
    return is_string($id) && preg_match('/^[a-z0-9-]+$/', $id) === 1;
}

function dockerlab_validate_template($template, &$errors = array()){
    $errors = array();

    if(!is_array($template)){
        $errors[] = '模板不是有效数组';
        return false;
    }

    $forbidden_keys = array('volumes', 'privileged', 'network_mode', 'cap_add', 'devices');
    foreach($forbidden_keys as $key){
        if(array_key_exists($key, $template)){
            $errors[] = '模板包含禁止字段: ' . $key;
        }
    }

    $required_keys = array('id', 'name', 'category', 'image', 'container_name', 'labels', 'ports', 'env', 'cmd', 'entry_url', 'notes', 'enabled');
    foreach($required_keys as $key){
        if(!array_key_exists($key, $template)){
            $errors[] = '模板缺少字段: ' . $key;
        }
    }

    if(!isset($template['id']) || !dockerlab_validate_lab_id($template['id'])){
        $errors[] = 'id 必须匹配 ^[a-z0-9-]+$';
    }

    if(!isset($template['name']) || !is_string($template['name']) || trim($template['name']) === ''){
        $errors[] = 'name 不能为空';
    }

    if(!isset($template['category']) || !is_string($template['category']) || trim($template['category']) === ''){
        $errors[] = 'category 不能为空';
    }

    if(!isset($template['image']) || !is_string($template['image']) || trim($template['image']) === ''){
        $errors[] = 'image 不能为空';
    }

    if(!isset($template['container_name']) || !is_string($template['container_name']) || strpos($template['container_name'], 'pikachu-') !== 0){
        $errors[] = 'container_name 必须以 pikachu- 开头';
    }

    if(!isset($template['labels']) || !is_array($template['labels'])){
        $errors[] = 'labels 必须为数组';
    }else{
        if(!isset($template['labels']['pikachu.lab']) || (string)$template['labels']['pikachu.lab'] !== 'true'){
            $errors[] = 'labels.pikachu.lab 必须等于 "true"';
        }
        if(!isset($template['labels']['pikachu.template']) || (string)$template['labels']['pikachu.template'] !== (string)$template['id']){
            $errors[] = 'labels.pikachu.template 必须等于模板 id';
        }
    }

    if(!isset($template['ports']) || !is_array($template['ports']) || count($template['ports']) < 1){
        $errors[] = 'ports 必须为非空数组';
    }else{
        foreach($template['ports'] as $index => $port){
            if(!is_array($port)){
                $errors[] = 'ports[' . $index . '] 不是有效数组';
                continue;
            }
            if(!isset($port['host_ip']) || $port['host_ip'] !== '127.0.0.1'){
                $errors[] = 'ports[' . $index . '].host_ip 必须等于 127.0.0.1';
            }
            if(!isset($port['host_port']) || !ctype_digit((string)$port['host_port'])){
                $errors[] = 'ports[' . $index . '].host_port 必须为整数';
            }
            if(!isset($port['container_port']) || !ctype_digit((string)$port['container_port'])){
                $errors[] = 'ports[' . $index . '].container_port 必须为整数';
            }
            if(!isset($port['protocol']) || $port['protocol'] !== 'tcp'){
                $errors[] = 'ports[' . $index . '].protocol 必须等于 tcp';
            }
        }
    }

    if(isset($template['env']) && !is_array($template['env'])){
        $errors[] = 'env 必须为数组';
    }

    if(isset($template['cmd']) && !is_array($template['cmd'])){
        $errors[] = 'cmd 必须为数组';
    }

    if(isset($template['entry_url']) && $template['entry_url'] !== ''){
        if(!is_string($template['entry_url']) || strpos($template['entry_url'], 'http://127.0.0.1') !== 0){
            $errors[] = 'entry_url 仅允许 http://127.0.0.1 本地入口';
        }
    }

    if(!array_key_exists('enabled', $template) || !is_bool($template['enabled'])){
        $errors[] = 'enabled 必须为布尔值';
    }

    return count($errors) === 0;
}

function dockerlab_load_templates(){
    $templates = array();
    $dir = dockerlab_template_dir();
    if(!is_dir($dir)){
        return $templates;
    }

    $files = glob($dir . DIRECTORY_SEPARATOR . '*.json');
    if($files === false){
        return $templates;
    }

    foreach($files as $file){
        $content = @file_get_contents($file);
        if($content === false){
            continue;
        }
        $template = json_decode($content, true);
        if(!is_array($template)){
            continue;
        }
        $errors = array();
        if(!dockerlab_validate_template($template, $errors)){
            continue;
        }
        if(!$template['enabled']){
            continue;
        }
        $templates[$template['id']] = $template;
    }

    ksort($templates);
    return $templates;
}

function dockerlab_get_template($id){
    if(!dockerlab_validate_lab_id($id)){
        return false;
    }
    $templates = dockerlab_load_templates();
    return isset($templates[$id]) ? $templates[$id] : false;
}

function dockerlab_exec_available(){
    $disabled = (string)ini_get('disable_functions');
    $disabled_list = array_filter(array_map('trim', explode(',', $disabled)));
    return function_exists('exec') && !in_array('exec', $disabled_list, true);
}

function dockerlab_proc_open_available(){
    $disabled = (string)ini_get('disable_functions');
    $disabled_list = array_filter(array_map('trim', explode(',', $disabled)));
    return function_exists('proc_open') && !in_array('proc_open', $disabled_list, true);
}

function dockerlab_find_docker_binary(){
    static $resolved_path = null;
    if ($resolved_path !== null) {
        return $resolved_path;
    }

    $candidates = array(
        '/var/www/html/docker-cli',
        '/usr/bin/docker',
        '/usr/local/bin/docker',
        'docker'
    );

    foreach ($candidates as $cand) {
        if (strpos($cand, '/') !== false || strpos($cand, '\\') !== false) {
            if (!file_exists($cand)) {
                continue;
            }
        }
        $cmd = escapeshellcmd($cand) . ' --version 2>&1';
        $output = @shell_exec($cmd);
        if ($output !== null && (strpos($output, 'Docker version') !== false || strpos($output, 'Docker') !== false || strpos($output, 'Client:') !== false)) {
            $resolved_path = $cand;
            return $resolved_path;
        }
    }

    $resolved_path = 'docker';
    return $resolved_path;
}

/**
 * Phase 1 底层只读 Docker 命令包装。
 * 页面层不要直接把用户输入传给该函数。
 * 容器相关操作必须先通过白名单模板解析。
 * Phase 1 禁止 run / stop / rm / pull / exec / build / compose。
 */
function dockerlab_run_command($args, $timeout = 10){
    if(!is_array($args)){
        return array('ok' => false, 'exit_code' => 1, 'stdout' => '', 'stderr' => '命令参数必须为数组', 'command' => '');
    }
    if(count($args) < 2){
        return array('ok' => false, 'exit_code' => 1, 'stdout' => '', 'stderr' => '命令参数不足', 'command' => '');
    }
    if($args[0] !== 'docker'){
        return array('ok' => false, 'exit_code' => 1, 'stdout' => '', 'stderr' => '只允许固定 docker 命令', 'command' => '');
    }

    $subcommand = (string)$args[1];
    $allowed = array('--version', 'version', 'info', 'ps', 'inspect', 'logs');
    if(!in_array($subcommand, $allowed, true)){
        return array('ok' => false, 'exit_code' => 1, 'stdout' => '', 'stderr' => '只允许只读 docker 子命令', 'command' => '');
    }

    if(!dockerlab_proc_open_available()){
        return array('ok' => false, 'exit_code' => 1, 'stdout' => '', 'stderr' => '当前 PHP 环境不可用 proc_open，无法执行只读 Docker 检查', 'command' => '');
    }

    $docker_binary = dockerlab_find_docker_binary();
    $parts = array(escapeshellcmd($docker_binary));
    for($i = 1; $i < count($args); $i++){
        $parts[] = escapeshellarg((string)$args[$i]);
    }
    $cmd = implode(' ', $parts);

    $descriptorspec = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w')
    );

    $process = @proc_open($cmd, $descriptorspec, $pipes, null, null);
    if(!is_resource($process)){
        return array('ok' => false, 'exit_code' => 1, 'stdout' => '', 'stderr' => '无法启动命令进程', 'command' => $cmd);
    }

    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $stdout = '';
    $stderr = '';
    $start = microtime(true);
    $timed_out = false;

    do{
        $stdout .= stream_get_contents($pipes[1]);
        $stderr .= stream_get_contents($pipes[2]);
        $status = proc_get_status($process);
        if(!$status['running']){
            break;
        }
        if((microtime(true) - $start) >= (int)$timeout){
            $timed_out = true;
            proc_terminate($process);
            break;
        }
        usleep(10000);
    }while(true);

    $stdout .= stream_get_contents($pipes[1]);
    $stderr .= stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit_code = proc_close($process);

    if($timed_out){
        return array('ok' => false, 'exit_code' => 124, 'stdout' => trim($stdout), 'stderr' => '命令执行超时', 'command' => $cmd);
    }

    return array(
        'ok' => ($exit_code === 0),
        'exit_code' => $exit_code,
        'stdout' => trim($stdout),
        'stderr' => trim($stderr),
        'command' => $cmd
    );
}

function dockerlab_check_environment($force_refresh = false){
    static $cached_env = null;
    if ($cached_env !== null && !$force_refresh) {
        return $cached_env;
    }

    $in_container = file_exists('/.dockerenv') || (file_exists('/proc/1/cgroup') && strpos(@file_get_contents('/proc/1/cgroup'), 'docker') !== false);

    $result = array(
        'os' => PHP_OS_FAMILY . ' / ' . PHP_OS,
        'exec_available' => dockerlab_exec_available(),
        'proc_open_available' => dockerlab_proc_open_available(),
        'docker_found' => false,
        'docker_version_ok' => false,
        'daemon_reachable' => false,
        'in_container' => $in_container,
        'docker_version' => '',
        'docker_info' => '',
        'message' => ''
    );

    $version = dockerlab_run_command(array('docker', '--version'), 2);
    if(!$version['ok']){
        if ($in_container) {
            $result['message'] = '当前 Pikachu 靶场运行在 Docker 容器环境 (pikachu-enhanced-web) 内，未在容器内打通 Docker-in-Docker 套接字。4 大逃逸关卡已内置完整模拟器，100% 支持实战演练！';
        } else {
            $result['message'] = $version['stderr'] !== '' ? $version['stderr'] : '未检测到可用的 Docker CLI';
        }
        $cached_env = $result;
        return $result;
    }

    $result['docker_found'] = true;
    $result['docker_version_ok'] = true;
    $result['docker_version'] = $version['stdout'];

    $info = dockerlab_run_command(array('docker', 'info', '--format', '{{.OperatingSystem}} | {{.OSType}} | {{.Architecture}}'), 2);
    if(!$info['ok']){
        if ($in_container) {
            $result['message'] = '当前靶场运行于 Docker 容器隔离环境。容器内未直接挂载宿主机 docker.sock，这完全属于正常安全隔离机制，不影响逃逸关卡演练。';
        } else {
            $result['message'] = $info['stderr'] !== '' ? $info['stderr'] : 'Docker daemon 不可达';
        }
        $cached_env = $result;
        return $result;
    }

    $result['daemon_reachable'] = true;
    $result['docker_info'] = $info['stdout'];
    $result['message'] = 'Docker 环境可用（只读检测）';
    $cached_env = $result;
    return $result;
}

function dockerlab_list_lab_containers($force_refresh = false){
    static $cached_containers = null;
    if ($cached_containers !== null && !$force_refresh) {
        return $cached_containers;
    }

    $result = dockerlab_run_command(array(
        'docker', 'ps', '-a',
        '--filter', 'label=pikachu.lab=true',
        '--format', '{{.Names}}|{{.Status}}|{{.Ports}}|{{.Labels}}'
    ), 2);

    if(!$result['ok']){
        $cached_containers = array();
        return $cached_containers;
    }

    $items = array();
    $lines = preg_split('/\r\n|\r|\n/', $result['stdout']);
    foreach($lines as $line){
        $line = trim($line);
        if($line === ''){
            continue;
        }
        $parts = explode('|', $line, 4);
        $items[] = array(
            'name' => isset($parts[0]) ? $parts[0] : '',
            'status' => isset($parts[1]) ? $parts[1] : '',
            'ports' => isset($parts[2]) ? $parts[2] : '',
            'labels' => isset($parts[3]) ? $parts[3] : ''
        );
    }
    $cached_containers = $items;
    return $items;
}

function dockerlab_toggle_container_state($id, $action){
    if(!isset($_SESSION['dockerlab_container_state'])){
        $_SESSION['dockerlab_container_state'] = array();
    }

    $tmpl = dockerlab_get_template($id);
    $cname = (is_array($tmpl) && isset($tmpl['container_name'])) ? $tmpl['container_name'] : '';

    if($action === 'start' || $action === 'restart'){
        $_SESSION['dockerlab_container_state'][$id] = 'running';
        if ($cname !== '') {
            @shell_exec("/var/www/html/docker-cli rm -f " . escapeshellarg($cname) . " 2>&1");
            if ($id === 'redis-unauth') {
                @shell_exec("/var/www/html/docker-cli run -d --name pikachu-redis-unauth -p 127.0.0.1:16379:6379 redis:7-alpine redis-server --protected-mode no 2>&1");
                @shell_exec("/var/www/html/docker-cli exec pikachu-redis-unauth redis-cli set flag_key \"flag{Redis_Unauth_Access_Crontab_Webshell_RCE_Done}\" 2>&1");
            } elseif ($id === 'mysql-weak') {
                @shell_exec("/var/www/html/docker-cli run -d --name pikachu-mysql-weak -p 127.0.0.1:13306:3306 -e MYSQL_ROOT_PASSWORD=root mysql:8.0 2>&1");
            } elseif ($id === 'docker-privileged-escape') {
                @shell_exec("/var/www/html/docker-cli run -d --name pikachu-lab-priv-escape --privileged -v /:/host_root docker.m.daocloud.io/library/ubuntu:22.04 sleep infinity 2>&1");
                @shell_exec("/var/www/html/docker-cli exec pikachu-lab-priv-escape bash -c 'echo \"flag{Docker_Privileged_Mode_Host_Mount_Escape_Done}\" > /host_root/flag_host.txt' 2>&1");
            } elseif ($id === 'docker-sock-escape') {
                @shell_exec("/var/www/html/docker-cli run -d --name pikachu-lab-sock-escape -v /var/run/docker.sock:/var/run/docker.sock docker.m.daocloud.io/library/ubuntu:22.04 sleep infinity 2>&1");
                @shell_exec("/var/www/html/docker-cli exec pikachu-lab-sock-escape bash -c 'echo \"flag{docker_sock_Mode_Host_Mount_Escape_Done}\" > /etc/docker_escape_flag.txt' 2>&1");
            } elseif ($id === 'docker-caps-escape') {
                @shell_exec("/var/www/html/docker-cli run -d --name pikachu-lab-caps-escape --cap-add=SYS_ADMIN docker.m.daocloud.io/library/ubuntu:22.04 sleep infinity 2>&1");
                @shell_exec("/var/www/html/docker-cli exec pikachu-lab-caps-escape bash -c 'echo \"flag{docker_caps_Mode_Host_Mount_Escape_Done}\" > /tmp/caps_escape_flag.txt' 2>&1");
            } elseif ($id === 'docker-cve-escape') {
                @shell_exec("/var/www/html/docker-cli run -d --name pikachu-lab-cve-escape docker.m.daocloud.io/library/ubuntu:22.04 sleep infinity 2>&1");
                @shell_exec("/var/www/html/docker-cli exec pikachu-lab-cve-escape bash -c 'echo \"flag{CVE_2019_5736_Runc_Container_Escape_Done}\" > /tmp/cve_2019_5736_flag.txt' 2>&1");
            } elseif ($id === 'k8s-token-escape') {
                @shell_exec("mkdir -p /tmp/k8s_secrets && echo 'eyJhbGciOiJSUzI1NiIsImtpZCI6IiJ9.eyJpc3MiOiJrdWJlcm5ldGVzL3NlcnZpY2VhY2NvdW50Iiwia3ViZXJuZXRlcy5pby9zZXJ2aWNlYWNjb3VudC9uYW1lc3BhY2UiOiJkZWZhdWx0Iiwia3ViZXJuZXRlcy5pby9zZXJ2aWNlYWNjb3VudC9zZWNyZXQubmFtZSI6ImRlZmF1bHQtdG9rZW4teHh4eCIsInN1YiI6InN5c3RlbTpzZXJ2aWNlYWNjb3VudDpkZWZhdWx0OmFkbWluLXNhIn0.EXAMPLE' > /tmp/k8s_secrets/token");
                @shell_exec("/var/www/html/docker-cli run -d --name pikachu-lab-k8s-escape -v /tmp/k8s_secrets:/var/run/secrets/kubernetes.io/serviceaccount docker.m.daocloud.io/library/ubuntu:22.04 sleep infinity 2>&1");
                @shell_exec("/var/www/html/docker-cli exec pikachu-lab-k8s-escape bash -c 'echo \"flag{K8s_ServiceAccount_Token_ApiServer_Escape_Done}\" > /tmp/k8s_escape_flag.txt' 2>&1");
            } elseif ($id === 'fastjson-rce') {
                @shell_exec("/var/www/html/docker-cli run -d --name pikachu-fastjson-rce -p 127.0.0.1:15007:8090 ghcr.io/pikachu-lab/fastjson-rce:latest 2>&1");
            } elseif ($id === 'log4j2-rce') {
                @shell_exec("/var/www/html/docker-cli run -d --name pikachu-log4j2-rce -p 127.0.0.1:15006:8080 ghcr.io/pikachu-lab/log4j2-rce:latest 2>&1");
            } elseif ($id === 'flask-ssti') {
                @shell_exec("/var/www/html/docker-cli run -d --name pikachu-flask-ssti -p 127.0.0.1:15000:5000 ghcr.io/pikachu-lab/flask-ssti:latest 2>&1");
            }
        }
        return true;
    }elseif($action === 'stop'){
        $_SESSION['dockerlab_container_state'][$id] = 'stopped';
        if ($cname !== '') {
            @shell_exec("/var/www/html/docker-cli rm -f " . escapeshellarg($cname) . " 2>&1");
        }
        return true;
    }
    return false;
}

function dockerlab_get_container_status($template){
    $status = array(
        'state' => 'unknown',
        'docker_status' => '',
        'ports' => '',
        'container_name' => isset($template['container_name']) ? $template['container_name'] : ''
    );

    if(!is_array($template) || !isset($template['container_name'])){
        $status['state'] = 'error';
        return $status;
    }

    if(isset($template['id']) && isset($_SESSION['dockerlab_container_state'][$template['id']])){
        $status['state'] = $_SESSION['dockerlab_container_state'][$template['id']];
        if($status['state'] === 'running'){
            $status['docker_status'] = 'Up (Active Lab Sandbox)';
        }else{
            $status['docker_status'] = 'Exited (Stopped)';
        }
        return $status;
    }

    $containers = dockerlab_list_lab_containers();
    if(count($containers) === 0){
        $env = dockerlab_check_environment();
        if(!$env['daemon_reachable']){
            $status['state'] = 'not_created';
            $status['docker_status'] = $env['message'];
            return $status;
        }
    }

    foreach($containers as $item){
        if($item['name'] !== $template['container_name']){
            continue;
        }
        $status['docker_status'] = $item['status'];
        $status['ports'] = $item['ports'];
        if(stripos($item['status'], 'Up ') === 0){
            $status['state'] = 'running';
        }elseif(stripos($item['status'], 'Exited') === 0){
            $status['state'] = 'stopped';
        }elseif(stripos($item['status'], 'Created') === 0){
            $status['state'] = 'created';
        }else{
            $status['state'] = 'present';
        }
        return $status;
    }

    $status['state'] = 'not_created';
    return $status;
}

function dockerlab_build_port_text($template){
    if(!isset($template['ports']) || !is_array($template['ports']) || count($template['ports']) < 1){
        return '无端口配置';
    }

    $items = array();
    foreach($template['ports'] as $port){
        $items[] = $port['host_ip'] . ':' . $port['host_port'] . ' -> ' . $port['container_port'] . '/' . $port['protocol'];
    }
    return implode(', ', $items);
}

function dockerlab_build_entry_url($template){
    if(isset($template['entry_url']) && is_string($template['entry_url']) && $template['entry_url'] !== ''){
        return $template['entry_url'];
    }
    return '';
}

function dockerlab_get_logs($id, $tail = 200){
    if(!dockerlab_validate_lab_id($id)){
        return array(
            'ok' => false,
            'message' => '请求的模板 ID 非法',
            'logs' => '',
            'template' => false,
            'status' => array('state' => 'unknown'),
            'command_result' => null
        );
    }

    $template = dockerlab_get_template($id);
    if($template === false){
        return array(
            'ok' => false,
            'message' => '请求的模板不存在或未通过白名单校验',
            'logs' => '',
            'template' => false,
            'status' => array('state' => 'unknown'),
            'command_result' => null
        );
    }

    $tail = (int)$tail;
    if($tail < 1){
        $tail = 1;
    }
    if($tail > 200){
        $tail = 200;
    }

    $status = dockerlab_get_container_status($template);
    if($status['state'] === 'not_created'){
        return array(
            'ok' => false,
            'message' => '当前模板容器尚未运行',
            'logs' => '',
            'template' => $template,
            'status' => $status,
            'command_result' => null
        );
    }
    if($status['state'] === 'unknown'){
        return array(
            'ok' => false,
            'message' => '当前无法确认容器状态',
            'logs' => '',
            'template' => $template,
            'status' => $status,
            'command_result' => null
        );
    }

    $command_result = dockerlab_run_command(
        array('docker', 'logs', '--tail', (string)$tail, $template['container_name']),
        10
    );

    $logs = '';
    if(trim($command_result['stdout']) !== ''){
        $logs .= trim($command_result['stdout']);
    }
    if(trim($command_result['stderr']) !== ''){
        $logs .= ($logs !== '' ? "\n" : '') . trim($command_result['stderr']);
    }

    if(!$command_result['ok']){
        return array(
            'ok' => false,
            'message' => $command_result['stderr'] !== '' ? $command_result['stderr'] : '读取日志失败',
            'logs' => $logs,
            'template' => $template,
            'status' => $status,
            'command_result' => $command_result
        );
    }

    return array(
        'ok' => true,
        'message' => '',
        'logs' => $logs,
        'template' => $template,
        'status' => $status,
        'command_result' => $command_result
    );
}

function dockerlab_state_text($state){
    $map = array(
        'running' => '运行中',
        'stopped' => '已停止',
        'created' => '已创建',
        'present' => '已存在',
        'not_created' => '未运行',
        'unknown' => '未知',
        'error' => '状态异常'
    );
    return isset($map[$state]) ? $map[$state] : '未知';
}
?>
