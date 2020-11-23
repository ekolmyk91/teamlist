<?php
$DB_USERNAME = getParam('DB_USERNAME=',"/var/www/teamlistloc/.env");
$DB_PASSWORD = getParam('DB_PASSWORD=',"/var/www/teamlistloc/.env");
$DB_DATABASE = getParam('DB_DATABASE=',"/var/www/teamlistloc/.env");
$DB_HOST = getParam('DB_HOST=',"/var/www/teamlistloc/.env");

$dsn = "mysql:dbname=$DB_DATABASE;host=$DB_HOST";
$dbh = null;
try {
    $dbh = new PDO($dsn, $DB_USERNAME, str_replace('\'', '', $DB_PASSWORD),
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}

function getParam($param,$file){
    $fp = fopen($file, 'r');
    while (($line = fgets($fp)) !== false) {
        if (preg_match("~.*\b$param(.*)~", $line, $matches)){

            return $matches[1];
        }
    }

    return false;
}
