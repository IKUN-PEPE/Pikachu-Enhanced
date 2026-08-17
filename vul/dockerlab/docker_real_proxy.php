<?php
// Simple proxy to pass commands from PHP to the local Docker container
header('Content-Type: text/plain; charset=utf-8');

$cmd = isset($_POST['cmd']) ? $_POST['cmd'] : (isset($_GET['cmd']) ? $_GET['cmd'] : '');
if (empty($cmd)) {
    echo "No command provided.";
    exit;
}

$escaped_cmd = escapeshellarg($cmd);
$full_cmd = "docker exec pikachu-web /bin/bash -c " . $escaped_cmd . " 2>&1";

$output = @shell_exec($full_cmd);
echo $output ?: "Command executed (no output).";
?>
