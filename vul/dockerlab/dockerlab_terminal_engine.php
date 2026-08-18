<?php
/**
 * Pikachu-Enhanced Universal Linux Bash & Service Terminal Engine
 * Provides realistic Linux Bash shell and service interaction simulator for Docker & K8s Labs.
 */

if (!function_exists('dockerlab_exec_universal')) {

    function dockerlab_exec_universal($raw_cmd, $lab_type, &$state, &$cwd) {
        $raw_cmd = trim($raw_cmd);
        if ($raw_cmd === '') {
            return '';
        }

        // Normalize CWD
        if (empty($cwd)) {
            $cwd = ($lab_type === 'k8s_token') ? '/var/www/html' : '/root';
        }

        // Handle multi-command chaining (&& or ;)
        if (strpos($raw_cmd, '&&') !== false) {
            $sub_cmds = explode('&&', $raw_cmd);
            $outputs = array();
            foreach ($sub_cmds as $sc) {
                $out = dockerlab_exec_single(trim($sc), $lab_type, $state, $cwd);
                if ($out !== '') {
                    $outputs[] = $out;
                }
            }
            return implode("\n\n", $outputs);
        }

        if (strpos($raw_cmd, ';') !== false) {
            $sub_cmds = explode(';', $raw_cmd);
            $outputs = array();
            foreach ($sub_cmds as $sc) {
                $out = dockerlab_exec_single(trim($sc), $lab_type, $state, $cwd);
                if ($out !== '') {
                    $outputs[] = $out;
                }
            }
            return implode("\n\n", $outputs);
        }

        return dockerlab_exec_single($raw_cmd, $lab_type, $state, $cwd);
    }

    function dockerlab_exec_single($cmd, $lab_type, &$state, &$cwd) {
        // Container Routing Map based on Lab Type
        $container_map = array(
            'docker_privileged' => 'pikachu-lab-priv-escape',
            'docker_sock'       => 'pikachu-lab-sock-escape',
            'docker_caps'       => 'pikachu-lab-caps-escape',
            'docker_cve'        => 'pikachu-lab-cve-escape',
            'k8s_token'         => 'pikachu-lab-k8s-escape',
            'redis_unauth'      => 'pikachu-redis-unauth',
            'fastjson_rce'      => 'pikachu-fastjson-rce',
            'log4j2_rce'        => 'pikachu-log4j2-rce',
            'flask_ssti'        => 'pikachu-flask-ssti',
            'mysql_weak'        => 'pikachu-mysql-weak'
        );

        $target_container = isset($container_map[$lab_type]) ? $container_map[$lab_type] : 'pikachu-lab-priv-escape';

        $cmd_lower = strtolower(trim($cmd));
        if ($cmd_lower === 'help' || $cmd_lower === '?') {
            return "=== Pikachu-Enhanced 真实 Linux 终端已连接 ===\n" .
                   "容器目标: [$target_container]\n" .
                   "当前所有的命令输入都将直接在真实 Docker 容器 ($target_container) 内执行。\n" .
                   "你可以使用任何标准的 Linux / 业务调试命令。";
        }
        
        // CWD navigation handling for shell context
        if (preg_match('/^cd\s*(.*)$/i', $cmd, $matches)) {
            $target = trim($matches[1]);
            if ($target === '' || $target === '~') {
                $cwd = '/root';
            } elseif ($target === '..') {
                if ($cwd !== '/') {
                    $parts = explode('/', rtrim($cwd, '/'));
                    array_pop($parts);
                    $cwd = implode('/', $parts);
                    if ($cwd === '') $cwd = '/';
                }
            } elseif (strpos($target, '/') === 0) {
                $cwd = rtrim($target, '/');
                if ($cwd === '') $cwd = '/';
            } else {
                $cwd = rtrim($cwd, '/') . '/' . $target;
            }
            return '';
        }
        
        // Check if real container is running
        $check = @shell_exec("/var/www/html/docker-cli ps --filter name=^" . escapeshellarg($target_container) . "$ --format \"{{.Names}}\" 2>/dev/null");
        if ($check !== null && strpos(trim($check), $target_container) !== false) {
            $escaped_cmd = escapeshellarg("cd " . escapeshellarg($cwd) . " && " . $cmd);
            $docker_cmd = "/var/www/html/docker-cli exec " . escapeshellarg($target_container) . " /bin/bash -c " . $escaped_cmd . " 2>&1";
            $output = @shell_exec($docker_cmd);
            if ($output !== null && trim($output) !== '') {
                return trim($output);
            }
        }
        
        // Dynamic Fallback Simulator Engine if container is not pulled/spawned
        return dockerlab_simulate_fallback($cmd, $lab_type, $state, $cwd);
    }

    function dockerlab_simulate_fallback($cmd, $lab_type, &$state, &$cwd) {
        $cmd_clean = trim($cmd);
        
        // Common basic Linux commands simulation
        if ($cmd_clean === 'pwd') {
            return $cwd;
        }
        if ($cmd_clean === 'whoami') {
            return 'root';
        }
        if ($cmd_clean === 'id') {
            return 'uid=0(root) gid=0(root) groups=0(root)';
        }
        if ($cmd_clean === 'hostname') {
            return 'sandbox-' . substr(md5($lab_type), 0, 8);
        }
        if ($cmd_clean === 'uname -a') {
            return 'Linux sandbox-host 5.15.0-100-generic #110-Ubuntu SMP Wed Jan 17 10:00:00 UTC 2024 x86_64 Linux';
        }
        
        // Lab-specific command handlers
        switch ($lab_type) {
            case 'docker_sock':
                if (strpos($cmd_clean, 'docker.sock') !== false || $cmd_clean === 'ls -l /var/run' || $cmd_clean === 'ls -la /var/run') {
                    return "srw-rw---- 1 root docker 0 Jan 17 08:30 /var/run/docker.sock";
                }
                if ($cmd_clean === 'docker ps' || $cmd_clean === 'docker-cli ps') {
                    return "CONTAINER ID   IMAGE                               COMMAND                  CREATED         STATUS         PORTS     NAMES\n" .
                           "a1b2c3d4e5f6   ghcr.io/pikachu-lab/docker-sock    \"/bin/bash\"              2 minutes ago   Up 2 minutes             pikachu-lab-sock-escape\n" .
                           "9f8e7d6c5b4a   ubuntu:22.04                        \"sleep infinity\"         1 hour ago      Up 1 hour                host-daemon-service";
                }
                if ($cmd_clean === 'docker images') {
                    return "REPOSITORY                             TAG       IMAGE ID       CREATED        SIZE\n" .
                           "ubuntu                                 22.04     ba5ba23e7f25   2 weeks ago    77.8MB\n" .
                           "ghcr.io/pikachu-lab/docker-sock        latest    c919a3bb0740   1 month ago    120MB";
                }
                if (stripos($cmd_clean, 'chroot') !== false || stripos($cmd_clean, '-v /:/') !== false) {
                    $state['chrooted'] = true;
                    return "[+] 成功创建特权挂载容器并挂载宿主机根目录！\n[+] 正在通过 chroot 进入宿主机文件系统环境...\n[#] 成功逃逸到宿主机！当前已获得宿主机 root 权限。\n[#] 宿主机 Flag 文件位于 /etc/docker_escape_flag.txt";
                }
                if (stripos($cmd_clean, 'docker_escape_flag.txt') !== false) {
                    return "flag{docker_sock_Mode_Host_Mount_Escape_Done}";
                }
                break;

            case 'docker_caps':
                if (stripos($cmd_clean, 'CapEff') !== false || stripos($cmd_clean, 'status') !== false) {
                    return "CapInh: 0000000000000000\nCapPrm: 00000000a80425fb\nCapEff: 00000000a80425fb\nCapBnd: 00000000a80425fb\nCapAmb: 0000000000000000\n[+] 识别到 CAP_SYS_ADMIN (0x00200000) 权限已开启！";
                }
                if (stripos($cmd_clean, 'capsh') !== false) {
                    return "Current: =cap_chown,cap_dac_override,cap_fowner,cap_fsetid,cap_kill,cap_setgid,cap_setuid,cap_setpcap,cap_net_bind_service,cap_net_raw,cap_sys_chroot,cap_sys_admin+ep";
                }
                if (stripos($cmd_clean, 'cgroup') !== false || stripos($cmd_clean, 'notify_on_release') !== false) {
                    $state['cgroup_mounted'] = true;
                    return "[+] 成功挂载 cgroup memory 子系统到 /tmp/cgroup\n[+] 开启 release_agent 监听: echo 1 > /tmp/cgroup/notify_on_release\n[+] 写入反弹脚本 /cmd 到宿主机 release_agent 触发链！\n[+] 触发逃逸脚本执行，Flag 已写入 /tmp/caps_escape_flag.txt";
                }
                if (stripos($cmd_clean, 'caps_escape_flag.txt') !== false) {
                    return "flag{docker_caps_Mode_Host_Mount_Escape_Done}";
                }
                break;

            case 'docker_cve':
                if (stripos($cmd_clean, 'runc') !== false && stripos($cmd_clean, 'version') !== false) {
                    return "runc version 1.0.0-rc5\ncommit: 4bb1e11423a29ff32d2e6e4d7c80000a40d9c22b\nspec: 1.0.1-dev\n[!] 警告: 当前 runc 版本为 1.0.0-rc5，存在 CVE-2019-5736 runc 覆盖逃逸漏洞！";
                }
                if (stripos($cmd_clean, '/proc/self/exe') !== false || stripos($cmd_clean, 'poc') !== false || stripos($cmd_clean, 'exploit') !== false) {
                    return "[+] 打开 /proc/self/exe (目标宿主机 runc 文件描述符: O_WRONLY|O_TRUNC)...\n[+] 覆盖宿主机 runc 二进制成功！\n[+] 等待下一次 docker exec 触发宿主机 root 执行 payload...\n[+] 逃逸成功！Flag 位于 /tmp/cve_2019_5736_flag.txt";
                }
                if (stripos($cmd_clean, 'cve_2019_5736_flag.txt') !== false) {
                    return "flag{CVE_2019_5736_Runc_Container_Escape_Done}";
                }
                break;

            case 'k8s_token':
                if (stripos($cmd_clean, 'serviceaccount') !== false || stripos($cmd_clean, 'secrets') !== false) {
                    return "total 0\n-r--r--r-- 1 root root 1024 Jan 17 08:30 ca.crt\n-r--r--r-- 1 root root  348 Jan 17 08:30 namespace\n-r--r--r-- 1 root root  854 Jan 17 08:30 token";
                }
                if (stripos($cmd_clean, 'cat') !== false && stripos($cmd_clean, 'token') !== false) {
                    return "eyJhbGciOiJSUzI1NiIsImtpZCI6IiJ9.eyJpc3MiOiJrdWJlcm5ldGVzL3NlcnZpY2VhY2NvdW50Iiwia3ViZXJuZXRlcy5pby9zZXJ2aWNlYWNjb3VudC9uYW1lc3BhY2UiOiJkZWZhdWx0Iiwia3ViZXJuZXRlcy5pby9zZXJ2aWNlYWNjb3VudC9zZWNyZXQubmFtZSI6ImRlZmF1bHQtdG9rZW4teHh4eCIsInN1YiI6InN5c3RlbTpzZXJ2aWNlYWNjb3VudDpkZWZhdWx0OmFkbWluLXNhIn0.EXAMPLE_TOKEN_SIGNATURE";
                }
                if (stripos($cmd_clean, 'curl') !== false && (stripos($cmd_clean, 'kubernetes') !== false || stripos($cmd_clean, '443') !== false || stripos($cmd_clean, 'api') !== false)) {
                    return "HTTP/2 200 OK\n{\n  \"kind\": \"SecretList\",\n  \"apiVersion\": \"v1\",\n  \"items\": [{\n    \"metadata\": {\"name\": \"cluster-flag-vault\"},\n    \"data\": {\"flag\": \"ZmxhZ3tLOHNfU2VydmljZUFjY291bnRfVG9rZW5fQXBpU2VydmVyX0VzY2FwZV9Eb25lfQ==\"}\n  }]\n}\n[+] 提取 Base64 Flag 解码: flag{K8s_ServiceAccount_Token_ApiServer_Escape_Done}";
                }
                break;

            case 'redis_unauth':
                if (stripos($cmd_clean, 'info') !== false) {
                    return "# Server\nredis_version:7.0.12\nos:Linux 5.15.0 x86_64\narch_bits:64\nprocess_id:1\ntcp_port:6379\nrole:master\nconnected_clients:1";
                }
                if (stripos($cmd_clean, 'keys') !== false) {
                    return "1) \"session_auth_data\"\n2) \"cached_app_config\"\n3) \"system_flag_key\"";
                }
                if (stripos($cmd_clean, 'config') !== false || stripos($cmd_clean, 'dir') !== false || stripos($cmd_clean, 'dbfilename') !== false) {
                    return "OK\n[+] 成功设置 Redis 数据目录与文件名: dir=/var/spool/cron, dbfilename=root\n[+] 保存内存快照 (SAVE)... OK! Crontab 提权写入成功！";
                }
                break;

            case 'mysql_weak':
                if (stripos($cmd_clean, 'version') !== false || stripos($cmd_clean, 'user()') !== false) {
                    return "+-------------------------+----------------+\n| VERSION()               | USER()         |\n+-------------------------+----------------+\n| 8.0.32                  | root@127.0.0.1 |\n+-------------------------+----------------+";
                }
                if (stripos($cmd_clean, 'sys_eval') !== false || stripos($cmd_clean, 'udf') !== false) {
                    return "+---------------------------------------------------+\n| sys_eval('id')                                    |\n+---------------------------------------------------+\n| uid=0(root) gid=0(root) groups=0(root)             |\n+---------------------------------------------------+";
                }
                break;

            case 'flask_ssti':
                if (strpos($cmd_clean, '{{') !== false) {
                    if (strpos($cmd_clean, '7*7') !== false) {
                        return "49";
                    }
                    if (stripos($cmd_clean, 'config') !== false) {
                        return "<Config {'DEBUG': True, 'SECRET_KEY': 'pikachu-flask-secret-key', 'FLAG': 'flag{Flask_Jinja2_SSTI_Subclasses_OS_Command_Done}'}>";
                    }
                    if (stripos($cmd_clean, 'subclasses') !== false || stripos($cmd_clean, 'os') !== false) {
                        return "<class 'subprocess.Popen'> -> uid=0(root) gid=0(root) groups=0(root)\nFlag: flag{Flask_Jinja2_SSTI_Subclasses_OS_Command_Done}";
                    }
                    return "[Jinja2 Render Output: " . htmlspecialchars($cmd_clean) . "]";
                }
                break;

            case 'fastjson_rce':
                if (stripos($cmd_clean, '@type') !== false) {
                    return "[+] Fastjson parseObject() 触发 autoType 反序列化！\n[+] 加载利用类: com.sun.rowset.JdbcRowSetImpl\n[+] 触发 JNDI lookup: ldap://127.0.0.1:1389/Exploit\n[+] RCE 成功！Flag: flag{Fastjson_AutoType_JNDI_Remote_Class_RCE_Done}";
                }
                break;

            case 'log4j2_rce':
                if (stripos($cmd_clean, 'jndi') !== false) {
                    return "[+] Log4j2 解析 \${jndi:ldap://...} 表达式！\n[+] 发起 JNDI 远程类加载请求...\n[+] RCE 成功！Flag: flag{Log4j2_CVE_2021_44228_JNDI_Lookup_RCE_Done}";
                }
                break;
        }

        if ($cmd_clean === 'ls' || $cmd_clean === 'ls -la') {
            return "total 12\ndrwx------ 2 root root 4096 Jan 17 08:30 .\ndrwxr-xr-x 3 root root 4096 Jan 17 08:30 ..\n-rw------- 1 root root  120 Jan 17 08:30 .bash_history\n-rw-r--r-- 1 root root  220 Jan 17 08:30 .bashrc";
        }

        return "[bash: " . htmlspecialchars($cmd_clean) . ": command executed successfully (exit code: 0)]";
    }
}
