<?php
class Logger { 
    public function __destruct() { 
        echo "DESTRUCT CALLED!\n"; 
    } 
} 
$p = new Phar("/var/www/html/vul/phar/evil_payload.jpg");
$m = $p->getMetadata();
