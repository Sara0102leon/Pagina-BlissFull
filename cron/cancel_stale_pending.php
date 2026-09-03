<?php
// Auto-cancela pedidos PENDIENTES (status 1) con más de 24 horas sin pago.
// Ejecutar via crontab cada 15 minutos:
//   */15 * * * * php /var/www/blissfull/cron/cancel_stale_pending.php >> /var/log/blissfull-cancel.log 2>&1

require_once __DIR__ . "/../webhooks/_bootstrap.php";

$affected = 0;

// 1) Pedidos programados futuros NO se cancelan (aún no vencieron su fecha agendada).
//    Solo pendientes sin programación o cuya programación ya pasó hace +24h respecto a created.
$con = Database::getCon();
$sql = "
	UPDATE buy SET status_id = 3
	WHERE status_id = 1
	  AND (scheduled_at IS NULL OR scheduled_at <= NOW())
	  AND created_at < (NOW() - INTERVAL 24 HOUR)
";
$con->query($sql);
$affected = $con->affected_rows;

echo date("Y-m-d H:i:s")." [blissfull-cancel] cancelados=".$affected."\n";
