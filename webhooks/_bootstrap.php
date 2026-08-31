<?php
// Bootstrap mínimo para scripts standalone (webhooks/cron)
// Expone: Database, Executor, Model y autoload de modelos (admin/core/app/model)

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("America/Caracas");

$BOOT_ROOT = realpath(__DIR__ . "/..");
if(!$BOOT_ROOT){ http_response_code(500); exit("root not found"); }

// Garantiza que los includes relativos de core/autoload.php resuelvan
// respecto a la raíz web.
chdir($BOOT_ROOT);

include $BOOT_ROOT . "/core/autoload.php";
include $BOOT_ROOT . "/core/app/autoload.php";
