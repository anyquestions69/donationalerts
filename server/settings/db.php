<?php
require 'rb.php';
$host='localhost';
$db = 'donat';
$username = 'public_hysteria';
$password = '0666';

$dbconn = pg_connect("host=$host port=5432 dbname=$db user=$username password=$password");
 
if (!$dbconn) {
die('Could not connect');
}
else {
$sql = "CREATE TABLE IF NOT EXISTS users (
id serial PRIMARY KEY,
login character varying(255) NOT NULL,
password character varying(255) NOT NULL,
balance integer,
goal_id integer
)";
 
$res = pg_query($dbconn, $sql);

if (!$res) {
echo "Произошла ошибка.\n";
}
$sql = "CREATE TABLE IF NOT EXISTS donats (
    Id SERIAL PRIMARY KEY,
    Login CHARACTER VARYING(30),
    Amount INTEGER,
    Date CHARACTER VARYING(30),
    Streamer_id INTEGER,
    goal_id INTEGER)";
     
    $res = pg_query($dbconn, $sql);
    
    if (!$res) {
    echo "Произошла ошибка.\n";
    }
$sql = "CREATE TABLE IF NOT EXISTS goals (
    Id SERIAL PRIMARY KEY,
    Current INTEGER,
    Goal INTEGER,
    Title CHARACTER VARYING(30),
    Start INTEGER,
    Finish INTEGER NULL,
    Streamer_id INTEGER
    )";

$res = pg_query($dbconn, $sql);
    
}