<?php
/* ══════════════════════════════════════════════════════════
   login.php — Página de acceso de la plataforma sazón
   Standalone (fuera de la landing y del menú público).
   Valida contra la tabla `user` y entra al panel (admin/).
   Mismo hash y misma sesión que el login del panel.
   ══════════════════════════════════════════════════════════ */

include "core/autoload.php";

/* ── Sesión: parámetros ANTES de session_start (habilita "Recordarme") ─────── */
$REMEMBER_DAYS = 30;
$remember_prev = (isset($_COOKIE["sazon_remember"]) && $_COOKIE["sazon_remember"] === "1");

ini_set("session.gc_maxlifetime", 60 * 60 * 24 * $REMEMBER_DAYS);
session_set_cookie_params(array(
	"lifetime" => $remember_prev ? 60 * 60 * 24 * $REMEMBER_DAYS : 0,
	"path"     => "/",
	"httponly" => true,
	"samesite" => "Lax",
));

ob_start();
session_start();

/* La página de acceso nunca debe quedar en caché ni indexarse */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("X-Robots-Tag: noindex, nofollow");

/* Destino tras entrar. Solo rutas internas del panel (evita "open redirect"). */
function saz_next_ok($n){
	if($n === "" || strpos($n, "..") !== false) return false;
	if(strpos($n, "//") !== false) return false;
	if(strpos($n, "\\") !== false) return false;
	return (bool)preg_match('#^/?(menu/)?admin/(\?[^\s]*)?$#', $n);
}
function saz_destino_rol($role, $next){
	if(saz_next_ok($next)) return $next;
	if($role === "cocina")     return "admin/?view=cocina";
	if($role === "repartidor") return "admin/?view=repartidor";
	return "admin/";
}
function saz_role_of($con, $id){
	$role = "";
	$rr = $con->query("select role from user where id=".intval($id)." limit 1");
	if($rr && $r = $rr->fetch_assoc()){ $role = (string)$r["role"]; }
	return $role;
}

$error    = "";
$email    = isset($_POST["email"]) ? trim((string)$_POST["email"]) : "";
$password = isset($_POST["password"]) ? (string)$_POST["password"] : "";
$isPost   = (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST");
$next     = isset($_REQUEST["next"]) ? trim((string)$_REQUEST["next"]) : "";

// Mensaje heredado del login del panel (?action=access&opt=login → ?err=1)
if($error === "" && isset($_GET["err"])){ $error = "Usuario o contraseña incorrectos."; }

$base = new Database();
$con  = $base->connect();

// Ya hay sesión → directo al panel del rol correspondiente
if(isset($_SESSION["user_id"])){
	header("Location: ".saz_destino_rol(saz_role_of($con, $_SESSION["user_id"]), $next));
	exit;
}

if($isPost){
	if($email === "" || $password === ""){
		$error = "Escribe tu usuario y tu contraseña.";
	}else{
		$u   = $con->real_escape_string($email);
		$p   = sha1(md5($password)); // mismo hash que el resto del sistema
		$sql = "select id, is_active from user where (email='$u' or username='$u') and password='$p' limit 1";
		$res = $con->query($sql);
		$row = ($res) ? $res->fetch_assoc() : null;

		if(!$row){
			usleep(350000); // frena la fuerza bruta
			$error = "Usuario o contraseña incorrectos.";
		}elseif(intval($row["is_active"]) !== 1){
			$error = "Tu cuenta está desactivada. Escríbenos para reactivarla.";
		}else{
			session_regenerate_id(true);
			$_SESSION["user_id"] = intval($row["id"]);

			// "Recordarme": cookie marca + cookie de sesión persistente
			if(!empty($_POST["remember"])){
				setcookie("sazon_remember", "1", time() + 60 * 60 * 24 * $REMEMBER_DAYS, "/");
				setcookie(session_name(), session_id(), time() + 60 * 60 * 24 * $REMEMBER_DAYS, "/", "", false, true);
			}else{
				setcookie("sazon_remember", "", time() - 3600, "/");
			}

			header("Location: ".saz_destino_rol(saz_role_of($con, $row["id"]), $next));
			exit;
		}
	}
}

/* Pista de credenciales SOLO en entorno local (jamás en producción) */
$__host = strtolower((string)(isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : ""));
if(($__pos = strpos($__host, ":")) !== false){ $__host = substr($__host, 0, $__pos); }
$show_demo_hint = in_array($__host, array("localhost", "127.0.0.1", "[::1]"), true);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
  <title>Iniciar sesión — sazón</title>
  <meta name="robots" content="noindex, nofollow"/>
  <meta name="theme-color" content="#0f0c09"/>
  <link rel="icon" type="image/svg+xml" href="landing/assets/img/favicon.svg"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Caveat:wght@500;600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    html{height:100%}
    body{
      min-height:100%;
      display:flex;
      flex-direction:column;
      background:#0f0c09;
      color:#f5eee2;
      font-family:"Inter",system-ui,sans-serif;
      font-size:16px;
      line-height:1.6;
      -webkit-font-smoothing:antialiased;
    }
    :focus-visible{outline:2px solid #e9b94a;outline-offset:3px;border-radius:6px}
    a{color:inherit;text-decoration:none}

    /* Fondo con brillos radiales (igual que la landing) */
    .page{
      position:relative;
      flex:1;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:48px 20px;
      overflow:hidden;
    }
    .page::before,
    .page::after{
      content:"";
      position:absolute;
      border-radius:50%;
      pointer-events:none;
    }
    .page::before{width:420px;height:420px;left:-140px;top:-140px;background:radial-gradient(circle,rgba(233,185,74,.16) 0%,transparent 70%)}
    .page::after{width:520px;height:520px;right:-180px;bottom:-180px;background:radial-gradient(circle,rgba(255,91,43,.12) 0%,transparent 70%)}

    .card{
      position:relative;
      width:min(410px,100%);
      background:rgba(22,17,16,.88);
      border:1px solid rgba(245,238,226,.09);
      border-radius:22px;
      padding:38px 34px 28px;
      box-shadow:0 40px 90px -30px rgba(0,0,0,.85);
      backdrop-filter:blur(6px);
    }

    .brand{display:flex;align-items:center;justify-content:center;gap:12px}
    .brand__mark{
      width:44px;height:44px;border-radius:13px;
      background:#e9b94a;color:#1a130a;
      font-family:"Bebas Neue","Arial Narrow",sans-serif;
      font-size:28px;line-height:1;
      display:flex;align-items:center;justify-content:center;
    }
    .brand__name{font-family:"Bebas Neue","Arial Narrow",sans-serif;font-size:31px;letter-spacing:.07em;text-transform:lowercase}

    .eyebrow{
      margin-top:24px;text-align:center;
      font-size:11px;font-weight:700;letter-spacing:.2em;
      text-transform:uppercase;color:#b3a795;
    }
    h1{
      margin-top:6px;
      font-family:"Bebas Neue","Arial Narrow",sans-serif;
      font-weight:400;font-size:34px;letter-spacing:.04em;
      text-align:center;
    }
    h1 em{font-style:normal;color:#e9b94a}

    form{margin-top:26px;display:flex;flex-direction:column;gap:16px}
    .field{display:flex;flex-direction:column;gap:7px}
    .label{font-size:13px;font-weight:600;color:#b3a795}
    .input-wrap{position:relative}
    .input{
      width:100%;
      background:#1d1713;
      border:1px solid rgba(245,238,226,.16);
      border-radius:12px;
      padding:12px 14px;
      color:#f5eee2;
      font-size:15px;
      font-family:inherit;
      outline:none;
      transition:border-color .2s,box-shadow .2s;
    }
    .input::placeholder{color:rgba(179,167,149,.55)}
    .input:focus{border-color:#e9b94a;box-shadow:0 0 0 3px rgba(233,185,74,.18)}
    .input--pw{padding-right:52px}
    .pw-toggle{
      position:absolute;top:50%;right:8px;transform:translateY(-50%);
      background:none;border:none;cursor:pointer;
      color:#b3a795;font-size:12px;font-weight:700;letter-spacing:.04em;
      padding:8px;border-radius:8px;
    }
    .pw-toggle:hover{color:#e9b94a}

    .caps{margin-top:5px;font-size:12px;color:#ff8f5e}
    .remember{
      display:flex;align-items:center;gap:9px;
      font-size:13.5px;color:#b3a795;cursor:pointer;user-select:none;
      margin-top:-2px;
    }
    .remember input{width:16px;height:16px;accent-color:#e9b94a;cursor:pointer}

    .btn{
      margin-top:6px;
      background:#e9b94a;color:#1a130a;
      border:none;border-radius:12px;
      padding:13px;font-size:15px;font-weight:700;font-family:inherit;
      cursor:pointer;
      transition:background .2s,box-shadow .2s;
    }
    .btn:hover{background:#ffd97a;box-shadow:0 12px 34px -12px rgba(233,185,74,.55)}
    .btn:active{transform:translateY(1px)}

    .error{
      margin-top:18px;
      background:#2a120b;border:1px solid rgba(255,91,43,.4);
      color:#ff8f5e;border-radius:12px;
      padding:11px 14px;font-size:13.5px;
      display:flex;gap:9px;align-items:flex-start;
    }

    .demo{
      margin-top:20px;padding:13px 15px;border-radius:12px;
      background:rgba(233,185,74,.07);border:1px solid rgba(233,185,74,.28);
      color:#e8d9b6;font-size:13px;line-height:1.8;
    }
    .demo b{color:#e9b94a}
    .demo code{
      background:rgba(0,0,0,.38);border:1px solid rgba(245,238,226,.14);
      border-radius:6px;padding:1px 6px;
      font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12.5px;color:#f5eee2;
    }

    .links{
      margin-top:22px;text-align:center;
      font-size:13.5px;color:#b3a795;
      display:flex;flex-direction:column;gap:6px;
    }
    .links a{font-weight:600;color:#f5eee2}
    .links a:hover{color:#e9b94a}
    .links small{font-size:12px;color:rgba(179,167,149,.7)}

    .foot{
      text-align:center;
      padding:18px 20px;
      font-size:12px;color:rgba(179,167,149,.65);
      border-top:1px solid rgba(245,238,226,.07);
    }
    .foot b{font-family:"Bebas Neue","Arial Narrow",sans-serif;font-weight:400;letter-spacing:.06em;color:#b3a795}
  </style>
</head>
<body>

  <div class="page">
    <div class="card" role="main">
      <a href="landing/" class="brand" aria-label="sazón — volver al sitio">
        <span class="brand__mark">s</span>
        <span class="brand__name">sazón</span>
      </a>
      <p class="eyebrow">Panel · Acceso</p>
      <h1>Bienvenido, <em>entra</em></h1>

      <?php if($error !== ""): ?>
        <div class="error" role="alert">
          <span aria-hidden="true">⚠</span>
          <span><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></span>
        </div>
      <?php endif; ?>

      <form method="post" action="login.php<?php echo $next !== "" ? "?next=".htmlspecialchars(urlencode($next), ENT_QUOTES, "UTF-8") : ""; ?>" autocomplete="on">
        <div class="field">
          <label class="label" for="f-email">Usuario o correo</label>
          <input class="input" type="text" id="f-email" name="email" required autofocus
                 value="<?php echo htmlspecialchars($email, ENT_QUOTES, "UTF-8"); ?>"
                 placeholder="tu usuario o correo" autocomplete="username" spellcheck="false"/>
        </div>
        <div class="field">
          <label class="label" for="f-pass">Contraseña</label>
          <div class="input-wrap">
            <input class="input input--pw" type="password" id="f-pass" name="password" required
                   placeholder="••••••••" autocomplete="current-password"
                   aria-describedby="capsHint"/>
            <button type="button" class="pw-toggle" id="pwToggle" aria-pressed="false" data-show="Mostrar" data-hide="Ocultar">Mostrar</button>
          </div>
          <span class="caps" id="capsHint" hidden>Bloq Mayús está activado</span>
        </div>
        <label class="remember">
          <input type="checkbox" name="remember" value="1" <?php echo $remember_prev ? "checked" : ""; ?>/>
          <span>Mantener la sesión iniciada</span>
        </label>
        <button type="submit" class="btn">Entrar</button>
      </form>

      <?php if($show_demo_hint): ?>
        <div class="demo" role="note">
          <span aria-hidden="true">🔑</span>
          <div>
            <b>Acceso de prueba (solo en local)</b><br>
            Dueño: <code>admin</code> / <code>admin</code><br>
            Super-admin: <code>superadmin</code> / <code>superadmin</code>
          </div>
        </div>
      <?php endif; ?>

      <div class="links">
        <a href="landing/">← Volver a la landing</a>
        <small>¿Aún no tienes tienda? <a href="landing/#planes">Crea tu menú</a></small>
      </div>
    </div>
  </div>

  <footer class="foot"><b>sazón</b> · menús digitales con subdominio propio</footer>

  <script>
    (function(){
      var toggle = document.getElementById("pwToggle");
      var input  = document.getElementById("f-pass");
      if(toggle && input){
        toggle.addEventListener("click", function(){
          var show = input.type === "password";
          input.type = show ? "text" : "password";
          toggle.setAttribute("aria-pressed", show ? "true" : "false");
          toggle.textContent = show ? toggle.dataset.hide : toggle.dataset.show;
        });
      }

      // Aviso de Bloq Mayús en el campo de contraseña
      var caps = document.getElementById("capsHint");
      if(input && caps){
        var check = function(e){
          if(e.getModifierState){ caps.hidden = !e.getModifierState("CapsLock"); }
        };
        input.addEventListener("keyup", check);
        input.addEventListener("keydown", check);
      }
    })();
  </script>
</body>
</html>
