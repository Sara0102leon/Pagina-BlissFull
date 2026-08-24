<?php
session_start();
echo 'Session ID: ' . session_id() . PHP_EOL;
echo 'Session user_id: ' . ($_SESSION['user_id'] ?? 'NOT SET') . PHP_EOL;
echo 'Session data: ' . print_r($_SESSION, true);