#!/bin/sh
set -eu

: "${PIKACHU_DB_HOST:=db}"
: "${PIKACHU_DB_PORT:=3306}"
: "${PIKACHU_DB_NAME:=pikachu}"
: "${PIKACHU_DB_USER:=root}"
: "${PIKACHU_DB_PASSWORD:=root}"

cat > /var/www/html/inc/config.inc.php <<PHP
<?php
session_start();
date_default_timezone_set('Asia/Shanghai');
header('Content-type:text/html;charset=utf-8');

define('DBHOST', '${PIKACHU_DB_HOST}');
define('DBUSER', '${PIKACHU_DB_USER}');
define('DBPW', '${PIKACHU_DB_PASSWORD}');
define('DBNAME', '${PIKACHU_DB_NAME}');
define('DBPORT', '${PIKACHU_DB_PORT}');
?>
PHP

cat > /var/www/html/pkxss/inc/config.inc.php <<PHP
<?php
session_start();
date_default_timezone_set('Asia/Shanghai');
header('Content-type:text/html;charset=utf-8');

define('DBHOST', '${PIKACHU_DB_HOST}');
define('DBUSER', '${PIKACHU_DB_USER}');
define('DBPW', '${PIKACHU_DB_PASSWORD}');
define('DBNAME', 'pkxss');
define('DBPORT', '${PIKACHU_DB_PORT}');
?>
PHP

# Create real container target flags inside the container environment
echo "flag{Pikachu_Web_Container_Root_Flag_2026}" > /flag.txt
echo "flag{Pikachu_System_Include_Flag_2026}" > /etc/flag.txt

# Ensure docker.sock is accessible by www-data web user if mounted
if [ -e /var/run/docker.sock ]; then
    chmod 666 /var/run/docker.sock || true
fi

# Auto-initialize database tables in background if needed
(
    # Wait for MySQL to become ready
    for i in \$(seq 1 30); do
        if php -r "@mysqli_connect('${PIKACHU_DB_HOST}', '${PIKACHU_DB_USER}', '${PIKACHU_DB_PASSWORD}') or exit(1);" 2>/dev/null; then
            php -r "
                \$link = mysqli_connect('${PIKACHU_DB_HOST}', '${PIKACHU_DB_USER}', '${PIKACHU_DB_PASSWORD}');
                if (\$link) {
                    mysqli_query(\$link, 'CREATE DATABASE IF NOT EXISTS ${PIKACHU_DB_NAME}');
                    mysqli_select_db(\$link, '${PIKACHU_DB_NAME}');
                    mysqli_query(\$link, 'CREATE TABLE IF NOT EXISTS users (id int(10) unsigned NOT NULL AUTO_INCREMENT, username varchar(30) NOT NULL, password varchar(66) NOT NULL, level int(11) NOT NULL, PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=4');
                    mysqli_query(\$link, \"INSERT IGNORE INTO users (id,username,password,level) VALUES (1,'admin',md5('123456'),1),(2,'pikachu',md5('000000'),2),(3,'test',md5('abc123'),3)\");
                    mysqli_query(\$link, 'CREATE TABLE IF NOT EXISTS message (id int(10) unsigned NOT NULL AUTO_INCREMENT, content varchar(255) NOT NULL, time datetime NOT NULL, PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=\"stored_xss_1\" AUTO_INCREMENT=56');
                    mysqli_query(\$link, 'CREATE TABLE IF NOT EXISTS xssblind (id int(10) unsigned NOT NULL AUTO_INCREMENT, time datetime NOT NULL, content text NOT NULL, name varchar(255) NOT NULL, PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=7');
                    mysqli_query(\$link, 'CREATE TABLE IF NOT EXISTS member (id int(10) unsigned NOT NULL AUTO_INCREMENT, username varchar(66) NOT NULL, pw varchar(128) NOT NULL, sex char(10) NOT NULL, phonenum varchar(255) NOT NULL, address varchar(255) NOT NULL, email varchar(255) NOT NULL, PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=25');
                    mysqli_query(\$link, \"INSERT IGNORE INTO member (id,username,pw,sex,phonenum,address,email) VALUES (1,'vince',md5('123456'),'boy','18626545453','chain','vince@pikachu.com'),(2,'allen',md5('123456'),'boy','13676767767','nba 76','allen@pikachu.com'),(3,'kobe',md5('123456'),'boy','15988767673','nba lakes','kobe@pikachu.com'),(4,'grady',md5('123456'),'boy','13676765545','nba hs','grady@pikachu.com'),(5,'kevin',md5('123456'),'boy','13677676754','Oklahoma City Thunder','kevin@pikachu.com'),(6,'lucy',md5('123456'),'girl','12345678922','usa','lucy@pikachu.com'),(7,'lili',md5('123456'),'girl','18656565545','usa','lili@pikachu.com')\");
                    mysqli_query(\$link, 'CREATE TABLE IF NOT EXISTS flag_vault (id int(10) unsigned NOT NULL AUTO_INCREMENT, flag_name varchar(66) NOT NULL, flag_val varchar(255) NOT NULL, PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
                    mysqli_query(\$link, \"INSERT IGNORE INTO flag_vault (id,flag_name,flag_val) VALUES (1,'sqli_flag','flag{Pikachu_SQLi_Database_Vault_Extracted}'),(2,'xxe_flag','flag{Pikachu_XXE_Local_Entity_Disclosure}'),(3,'deser_flag','flag{Pikachu_Deserialization_POP_Chain_Executed}')\");
                    
                    // Init pkxss
                    mysqli_query(\$link, 'CREATE DATABASE IF NOT EXISTS pkxss');
                    mysqli_select_db(\$link, 'pkxss');
                    mysqli_query(\$link, 'CREATE TABLE IF NOT EXISTS users (id int(10) unsigned NOT NULL AUTO_INCREMENT, username varchar(30) NOT NULL, password varchar(66) NOT NULL, level int(11) NOT NULL, PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=4');
                    mysqli_query(\$link, \"INSERT IGNORE INTO users (id,username,password,level) VALUES (1,'admin',md5('123456'),1)\");
                    mysqli_query(\$link, 'CREATE TABLE IF NOT EXISTS cookies (id int(10) unsigned NOT NULL AUTO_INCREMENT, time TIMESTAMP, ipaddress VARCHAR(50), cookie VARCHAR(1000), referer VARCHAR(1000), useragent VARCHAR(1000), PRIMARY KEY (id))');
                    mysqli_query(\$link, 'CREATE TABLE IF NOT EXISTS fish (id int(10) unsigned NOT NULL AUTO_INCREMENT, time TIMESTAMP, username VARCHAR(50), password VARCHAR(50), referer VARCHAR(1000), PRIMARY KEY (id))');
                    mysqli_query(\$link, 'CREATE TABLE IF NOT EXISTS keypress (id int(10) unsigned NOT NULL AUTO_INCREMENT, data VARCHAR(1000), PRIMARY KEY (id))');
                }
            " 2>/dev/null || true
            break
        fi
        sleep 1
    done
) &

exec "$@"
