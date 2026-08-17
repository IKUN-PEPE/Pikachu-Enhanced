<?php
// Simple proxy to pass commands from PHP to the local Docker container 'pikachu-enhanced-web'
\ = isset(\['cmd']) ? \['cmd'] : '';
if (empty(\)) {
    echo '';
    exit;
}

// We execute the command inside the pikachu-enhanced-web container.
// Warning: This is extremely dangerous and allows RCE on the host if Docker escapes are possible.
\ = escapeshellarg(\);
\ = "docker exec pikachu-enhanced-web /bin/bash -c " . \ . " 2>&1";

\ = shell_exec(\);
echo \;
