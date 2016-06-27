<?php
// This demo is taken/modofied from numerous places on the net for the purpose of this demo
require_once('libs.php');

myecho("Attempting to connect to the database...");

// Connecting, selecting database
$link = mysql_connect('demodemo.cesogq7okpis.eu-west-1.rds.amazonaws.com', 'demo', 'demodemo')
    or die('Could not connect: ' . mysql_error());
myecho("Connected successfully");
mysql_select_db('demo') or die('Could not select database');

// Check if table exists
$val = mysql_query('select 1 from `demotable` LIMIT 1');

if($val == FALSE) {
    myecho("Database is missing, lets create it...");

    $create_table_query = "CREATE TABLE demotable (
	id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
	firstname VARCHAR(30) NOT NULL,
	lastname VARCHAR(30) NOT NULL,
	email VARCHAR(50),
	reg_date TIMESTAMP default CURRENT_TIMESTAMP
    )";

    $result = mysql_query($create_table_query) or die('Unable to create table: ' . mysql_error());
}

$query = 'SELECT count(*) FROM demotable';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

// Printing results
$line = mysql_fetch_row($result);
myecho($line[0]." rows in the table...");

mysql_free_result($result);

// Insert into this table
$insert_query = "
    insert into demotable 
        (firstname, lastname, email)
     values (
         'firstname_".generateRandomString()."',
         'lastname_".generateRandomString()."',
         'myemail_".generateRandomString()."@mydomain".generateRandomString().".com'
     )";

$result = mysql_query($insert_query) or die('Query failed: ' . mysql_error());

// Closing connection
mysql_close($link);
