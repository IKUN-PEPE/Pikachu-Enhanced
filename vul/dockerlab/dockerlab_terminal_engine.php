<?php
/**
 * Pikachu-Enhanced Universal Linux Bash Terminal Engine
 * Provides a fully-featured, realistic Linux Bash shell simulator for Docker & K8s Labs.
 * Enforces strict sequential state verification for realistic CTF exploit chains.
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
        $container_map = [
            'docker_privileged' => 'pikachu-lab-priv-escape',
            'docker_sock'       => 'pikachu-lab-sock-escape',
            'docker_caps'       => 'pikachu-lab-caps-escape',
            'docker_cve'        => 'pikachu-lab-cve-escape',
            'k8s_token'         => 'pikachu-lab-k8s-escape'
        ];

        $target_container = isset($container_map[$lab_type]) ? $container_map[$lab_type] : 'pikachu-lab-priv-escape';

        // DIRECT REAL DOCKER EXECUTION
        // Warning: highly dangerous!
        $cmd_lower = strtolower(trim($cmd));
        if ($cmd_lower === 'help' || $cmd_lower === '?') {
            return "=== Pikachu-Enhanced 真实 Linux 终端已连接 ===\n" .
                   "警告：当前所有的命令输入都将直接在物理机上运行的真实 Docker 容器 ($target_container) 内执行。\n" .
                   "容器已开启特定危险配置，并去除了相关安全限制。\n" .
                   "你可以使用任何真实的 Linux 命令。请谨慎操作，避免损坏宿主机物理环境。";
        }
        
        // CWD navigation handling for PHP shell_exec context
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
        
        // Pass to true docker container
        $escaped_cmd = escapeshellarg("cd " . escapeshellarg($cwd) . " && " . $cmd);
        // Note: Running commands inside target dynamic container!
        $docker_cmd = "/var/www/html/docker-cli exec $target_container /bin/bash -c " . $escaped_cmd . " 2>&1";
        
        $output = shell_exec($docker_cmd);
        return $output !== null ? trim($output) : "";
    }
}









