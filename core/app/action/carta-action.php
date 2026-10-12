<?php
// carta-action.php — Carta / menú imprimible por tienda (página completa, sin layout).
// Rutas:  ./?action=carta                (todas las sedes)
//         ./?action=carta&sede=N          (solo la sede N)
//
// Config editable por tienda (tabla configuration, cfg_id = tienda):
//   carta_titulo, carta_subtitulo, carta_mostrar_logo, carta_mostrar_precios,
//   carta_mostrar_descripcion, carta_mostrar_imagenes, carta_columnas, carta_nota.

require_once __DIR__."/../helpers/product-extras-helper.php";

// ---- Config efectiva de la tienda activa ----
$carta_cfg = function($name, $def=""){
	$r = ConfigurationData::getByPreffix($name);
	return ($r && $r->val!=="") ? $r->val : $def;
};

$store      = class_exists("Tenant") ? Tenant::current() : null;
$store_name = ($store && trim($store->nombre)!="") ? trim($store->nombre) : "";

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');   // p.ej. "/menu"
if($base==="/"){ $base = ""; }

$coin = $carta_cfg("general_coin", "$");
$wa   = trim($carta_cfg("general_whatsapp", ""));

$c_titulo   = trim($carta_cfg("carta_titulo", ""));
$c_sub      = trim($carta_cfg("carta_subtitulo", ""));
$show_logo  = $carta_cfg("carta_mostrar_logo","1")==="1";
$show_price = $carta_cfg("carta_mostrar_precios","1")==="1";
$show_desc  = $carta_cfg("carta_mostrar_descripcion","1")==="1";
$show_img   = $carta_cfg("carta_mostrar_imagenes","0")==="1";
$nota       = trim($carta_cfg("carta_nota", ""));
$cols       = intval($carta_cfg("carta_columnas","2"));
if($cols<1){ $cols=1; } if($cols>3){ $cols=3; }

$title = $c_titulo!=="" ? $c_titulo : ($store_name!=="" ? $store_name : "Nuestra Carta");

// ---- Sede ----
$sedes = SedeData::getActives();
$sede = isset($_GET["sede"]) ? intval($_GET["sede"]) : 0;
$sede_obj = null;
if($sede>0){
	foreach($sedes as $sd){ if(intval($sd->id)===$sede){ $sede_obj=$sd; break; } }
	if(!$sede_obj){ $sede = 0; }
}

// ---- Tema (motor de tema Fase 1) ----
$tt_theme = array();
foreach(ConfigurationData::getAll() as $c){ $tt_theme[$c->name] = $c->val; }
$tg = function($name,$def="") use ($tt_theme){ return (isset($tt_theme[$name]) && $tt_theme[$name]!=="") ? $tt_theme[$name] : $def; };

// Logo: el de la tienda (Fase 2) si está definido; si no, el logo legacy de la carta.
$carta_logo = $base."/assets/img/logo.png";
$carta_logo_fn = $tg("tema_logo","");
if($carta_logo_fn!=="" && file_exists("admin/storage/brand/".$carta_logo_fn)){ $carta_logo = $base."/admin/storage/brand/".$carta_logo_fn; }

$accent  = $tg("tema_primary","#e0a96d");
$accent2 = $tg("tema_primary_2","#b87e38");
$red     = $tg("tema_red","#e63946");
$font_display = $tg("tema_font_display","Bebas Neue");
$font_body    = $tg("tema_font_body","Inter");

$categories = CategoryData::getPublics();

// Pre-cargar productos (para saber cuántas categorías quedan con contenido)
$carta = array();
$carta_total = 0;
foreach($categories as $cat){
	$prods = ($sede>0) ? ProductData::getPublicsByCategoryId($cat->id, $sede) : ProductData::getPublicsByCategoryIdAll($cat->id);
	if(count($prods)==0){ continue; }
	$carta[] = array("cat"=>$cat, "prods"=>$prods);
	$carta_total += count($prods);
}
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo htmlspecialchars($title); ?> — Carta</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Caveat:wght@500;600;700&family=Inter:wght@400;600;800;900&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $base; ?>/assets/bootstrap-icons/bootstrap-icons.css">
<style>
  :root{
    --c-accent: <?php echo $accent; ?>;
    --c-accent2: <?php echo $accent2; ?>;
    --c-red: <?php echo $red; ?>;
    --c-cols: <?php echo $cols; ?>;
    --c-display: '<?php echo addslashes($font_display); ?>', 'Bebas Neue', 'Outfit', sans-serif;
    --c-body: '<?php echo addslashes($font_body); ?>', 'Inter', -apple-system, sans-serif;
  }
  *{ box-sizing: border-box; }
  html, body{ margin:0; padding:0; }
  body{
    font-family: var(--c-body);
    background: #10100f;
    background-image: radial-gradient(ellipse at 50% -10%, rgba(224,169,109,.10) 0%, rgba(0,0,0,0) 60%);
    color:#171717;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
  }
  .toolbar{
    max-width: 940px; margin: 0 auto; padding: 16px 16px 0;
    display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;
  }
  .toolbar .brand{ color:#fff; font-weight:800; letter-spacing:.06em; text-transform:uppercase; font-size:.82rem; opacity:.9; }
  .toolbar .tools{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
  .pill{
    display:inline-block; padding:7px 13px; border-radius:999px; font-size:.8rem; font-weight:700;
    color:#d9c6a6; border:1px solid rgba(224,169,109,.4); text-decoration:none; line-height:1; background:transparent;
  }
  .pill:hover{ border-color: var(--c-accent); color:#fff; }
  .pill.active{ background: var(--c-accent); color:#1a1206; border-color: var(--c-accent); }
  .btn-print{
    display:inline-flex; align-items:center; gap:6px; border:0; cursor:pointer;
    background: var(--c-accent); color:#1a1206; font-weight:800; font-size:.85rem;
    padding:8px 15px; border-radius:999px;
  }
  .btn-back{
    display:inline-flex; align-items:center; gap:6px; text-decoration:none;
    border:1px solid rgba(255,255,255,.25); color:#eee; font-weight:600; font-size:.85rem;
    padding:8px 15px; border-radius:999px;
  }
  .btn-back:hover{ border-color: var(--c-accent); color:#fff; }

  .paper{
    background:#fff; color:#191919; max-width: 940px; margin: 18px auto 40px;
    padding: 48px 50px 36px; border-radius: 16px;
    box-shadow: 0 30px 80px rgba(0,0,0,.55);
  }
  .head{
    display:flex; align-items:center; gap:18px; flex-wrap:wrap;
    padding-bottom:16px; border-bottom:3px double var(--c-accent); margin-bottom:26px;
  }
  .logo{ height:66px; width:auto; object-fit:contain; }
  .head-txt{ flex:1 1 auto; min-width:180px; }
  .title{
    font-family: var(--c-display); font-weight:400; font-size:3rem; line-height:.95;
    margin:0; letter-spacing:.02em; color:#141414; text-transform:uppercase;
  }
  .sub{ margin:5px 0 0; font-size:.95rem; color:#6b6b6b; font-style:italic; }
  .sede{ text-align:right; font-size:.82rem; color:#444; line-height:1.5; }
  .sede b{ display:block; color: var(--c-accent2); text-transform:uppercase; letter-spacing:.05em; }
  .sede span{ display:block; }

  .cols{ column-count: var(--c-cols); column-gap: 46px; }
  .cat{ break-inside: avoid; page-break-inside: avoid; margin:0 0 24px; }
  .cat-title{
    font-family: var(--c-display); font-weight:400; font-size:1.55rem; letter-spacing:.07em;
    text-transform:uppercase; margin:0 0 8px; color:#141414;
    border-bottom:2px solid var(--c-accent); padding-bottom:3px;
  }
  .item{ display:flex; gap:10px; padding:7px 0; border-bottom:1px dotted rgba(0,0,0,.16); break-inside:avoid; }
  .thumb{ width:46px; height:46px; flex:0 0 46px; object-fit:cover; border-radius:9px; }
  .item-main{ flex:1 1 auto; min-width:0; }
  .row{ display:flex; align-items:baseline; gap:6px; }
  .name{ font-weight:700; font-size:.98rem; color:#171717; }
  .dots{ flex:1 1 auto; min-width:12px; border-bottom:1px dotted #c2c2c2; transform: translateY(-3px); }
  .price{ font-weight:800; white-space:nowrap; font-size:1rem; }
  .old{ color:#a0a0a0; font-weight:500; font-size:.85rem; margin-right:3px; }
  .off{ color: var(--c-red); }
  .desc{ font-size:.82rem; color:#6f6f6f; line-height:1.35; margin-top:1px; }

  .foot{ margin-top:22px; padding-top:14px; border-top:1px solid rgba(0,0,0,.14); }
  .nota{ font-size:.86rem; color:#555; margin-bottom:8px; }
  .foot-row{ display:flex; gap:16px; flex-wrap:wrap; justify-content:space-between; font-size:.8rem; color:#777; }
  .empty{ color:#777; font-style:italic; }

  @media (max-width: 760px){
    .cols{ column-count: 1; }
    .paper{ padding: 30px 22px; border-radius:0; margin:0; }
    .title{ font-size: 2.2rem; }
  }
  @media print{
    @page{ size: A4; margin: 12mm; }
    body{ background:#fff; background-image:none; }
    .toolbar{ display:none !important; }
    .paper{ box-shadow:none; border-radius:0; margin:0; max-width:none; width:100%; padding:0; }
    .cat, .item{ break-inside: avoid; page-break-inside: avoid; }
    a{ text-decoration:none; color:inherit; }
  }
</style>
</head>
<body>
  <div class="toolbar">
    <span class="brand"><i class="bi bi-journal-text me-1"></i> Carta</span>
    <div class="tools">
      <?php if(count($sedes)>0): ?>
      <span>
        <a class="pill <?php echo $sede==0 ? 'active' : ''; ?>" href="?action=carta">Todas</a>
        <?php foreach($sedes as $sd): ?>
        <a class="pill <?php echo ($sede>0 && intval($sd->id)===$sede) ? 'active' : ''; ?>" href="?action=carta&amp;sede=<?php echo intval($sd->id); ?>"><?php echo htmlspecialchars($sd->name); ?></a>
        <?php endforeach; ?>
      </span>
      <?php endif; ?>
      <button type="button" class="btn-print" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir / PDF</button>
      <a class="btn-back" href="<?php echo $base!=="" ? $base."/" : "./"; ?>"><i class="bi bi-arrow-left"></i> Menú</a>
    </div>
  </div>

  <main class="paper">
    <header class="head">
      <?php if($show_logo): ?><img class="logo" src="<?php echo $carta_logo; ?>" alt=""><?php endif; ?>
      <div class="head-txt">
        <h1 class="title"><?php echo htmlspecialchars($title); ?></h1>
        <?php if($c_sub!==""): ?><p class="sub"><?php echo htmlspecialchars($c_sub); ?></p><?php endif; ?>
      </div>
      <?php if($sede_obj): ?>
      <div class="sede">
        <b><?php echo htmlspecialchars($sede_obj->name); ?></b>
        <?php if(trim((string)$sede_obj->address)!==""): ?><span><?php echo htmlspecialchars($sede_obj->address); ?></span><?php endif; ?>
        <?php if(trim((string)$sede_obj->phone)!==""): ?><span>Tel: <?php echo htmlspecialchars($sede_obj->phone); ?></span><?php endif; ?>
      </div>
      <?php endif; ?>
    </header>

    <div class="cols">
      <?php foreach($carta as $blk): $cat=$blk["cat"]; ?>
      <section class="cat">
        <h3 class="cat-title"><?php echo htmlspecialchars($cat->name); ?></h3>
        <?php foreach($blk["prods"] as $p):
          $offer = ProductData::offerActive($p);
          $img_raw = "admin/storage/products/".$p->image;
          $has_img = $show_img && $p->image!="" && file_exists($img_raw);
        ?>
        <div class="item">
          <?php if($has_img): ?><img class="thumb" src="<?php echo $base."/".htmlspecialchars(tt_imgv($img_raw)); ?>" alt="" loading="lazy"><?php endif; ?>
          <div class="item-main">
            <div class="row">
              <span class="name"><?php echo htmlspecialchars($p->name); ?></span>
              <span class="dots"></span>
              <?php if($show_price): ?>
              <span class="price">
                <?php if($offer): ?><s class="old"><?php echo $coin.number_format(floatval($p->price),2,".",","); ?></s><?php endif; ?>
                <span class="<?php echo $offer ? 'off' : ''; ?>"><?php echo $coin.number_format(ProductData::getEffectivePrice($p),2,".",","); ?></span>
              </span>
              <?php endif; ?>
            </div>
            <?php if($show_desc && trim((string)$p->description)!==""): ?><div class="desc"><?php echo htmlspecialchars($p->description); ?></div><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </section>
      <?php endforeach; ?>

      <?php if($carta_total==0): ?>
      <p class="empty">Esta tienda aún no tiene productos publicados en el menú.</p>
      <?php endif; ?>
    </div>

    <footer class="foot">
      <?php if($nota!==""): ?><div class="nota"><?php echo nl2br(htmlspecialchars($nota)); ?></div><?php endif; ?>
      <div class="foot-row">
        <?php if($show_price): ?><span>Moneda: <?php echo htmlspecialchars($coin); ?></span><?php endif; ?>
        <?php if($wa!==""): ?><span>Pedidos: <?php echo htmlspecialchars($wa); ?></span><?php endif; ?>
        <span><?php echo $sede_obj ? htmlspecialchars($sede_obj->name) : "Todas las sedes"; ?></span>
      </div>
    </footer>
  </main>
</body>
</html>
