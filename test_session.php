<?php
session_start();
$_SESSION['test'] = 'hello';
echo 'Session ID: ' . session_id() . PHP_EOL;
echo 'Session data: ' . print_r($_SESSION, true);