<?php
$file = 'C:\xampp\tmp\sess_d4lrb60l494257m8tfa74008am';
echo 'File size: ' . filesize($file) . ' bytes' . PHP_EOL;
$content = file_get_contents($file);
echo 'Content: ' . $content . PHP_EOL;