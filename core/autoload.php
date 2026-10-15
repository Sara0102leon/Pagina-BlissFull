<?php
// Zona horaria del negocio, en el punto de entrada (antes que cualquier
// include): sin esto el sitio hereda date.timezone del php.ini (Europe/Berlin).
date_default_timezone_set("America/Caracas");

include "controller/Core.php";
include "controller/View.php";
//include "controller/Module.php"; // Renamed to Layout
include "admin/core/controller/Database.php";
include "controller/Executor.php";

// 10 octubre 2014
include "controller/Lb.php";
include "controller/Model.php";
include "controller/Bootload.php";
include "controller/Action.php";

include "controller/class.upload.php";

// 6 Agosto 2022
include "controller/Extra.php";
include "controller/Layout.php";
// 8 Agosto 2022
include "controller/FormTool.php";
include "controller/TableTool.php";

?>