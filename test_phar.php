<?php
class Logger { 
    public function __destruct() { 
        echo "DESTRUCT CALLED!\n"; 
    } 
} 
file_exists("phar:///var/www/html/vul/phar/evil_payload.jpg");
