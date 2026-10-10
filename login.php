<?php
/* ══════════════════════════════════════════════════════════
   login.php — Página de acceso de la plataforma sazón
   Standalone (fuera de la landing y del menú público).
   Valida contra la tabla `user` y entra al panel (admin/).
   Mismo hash y misma sesión que el login del panel.
   ══════════════════════════════════════════════════════════ */

include "core/autoload.php";
ob_start();
session_start();

$error     = "";
$email     = isset($_POST["email"]) ? trim((string)$_POST["email"]) : "";
$password  = isset($_POST["password"]) ? (string)$_POST["password"] : "";
$isPost    = ($_SERVER["REQUEST_METHOD"] === "POST");

// Ya hay sesión → directo al panel
if(isset($_SESSION["user_id"])){
	header("Location: admin/");
	exit;
}

if($isPost){
	if($email === "" || $password === ""){
		$error = "Escribe tu correo y tu contraseña.";
	}else{
		$base = new Database();
		$con  = $base->connect();
		$u    = $con->real_escape_string($email);
		$p    = sha1(md5($password)); // mismo hash que `admin/?action=access&opt=login`
		$sql  = "select id from user where (email='$u' or username='$u') and password='$p' and is_active=1 limit 1";
		$res  = $con->query($sql);
		if($res && $row = $res->fetch_assoc()){
			session_regenerate_id(true);
			$_SESSION["user_id"] = intval($row["id"]);
			header("Location: admin/");
			exit;
		}else{
			$error = "Correo o contraseña incorrectos.";
		}
	}
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
  <title>Iniciar sesión — sazón</title>
  <meta name="robots" content="noindex, nofollow"/>
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

      <form method="post" action="login.php" autocomplete="on">
        <div class="field">
          <label class="label" for="f-email">Correo o usuario</label>
          <input class="input" type="text" id="f-email" name="email" required
                 value="<?php echo htmlspecialchars($email, ENT_QUOTES, "UTF-8"); ?>"
                 placeholder="tu@correo.com" autocomplete="username" spellcheck="false"/>
        </div>
        <div class="field">
          <label class="label" for="f-pass">Contraseña</label>
          <div class="input-wrap">
            <input class="input input--pw" type="password" id="f-pass" name="password" required
                   placeholder="••••••••" autocomplete="current-password"/>
            <button type="button" class="pw-toggle" id="pwToggle" aria-pressed="false" data-show="Mostrar" data-hide="Ocultar">Mostrar</button>
          </div>
        </div>
        <button type="submit" class="btn">Entrar</button>
      </form>

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
    })();
  </script>
</body>
</html>