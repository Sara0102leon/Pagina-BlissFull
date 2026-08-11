<?php
// BCV rate endpoint with DB cache (10 min)
// Uses https://ve.dolarapi.com/v1/dolares/bcv (free, no key) with fallback

function bcv_http_get($url){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 15);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Blissfull/1.0)");
  $res = curl_exec($ch);
  curl_close($ch);
  return $res;
}

function bcv_fetch_rate(){
  $rate = null;
  $sources = array(
    "https://ve.dolarapi.com/v1/dolares",
    "https://pydolarve.org/api/v1/dollar?moneda=ves"
  );
  foreach($sources as $url){
    $json = @bcv_http_get($url);
    if($json){
      $data = json_decode($json, true);
      if($data){
        if(isset($data["promedio"])){ $rate = $data["promedio"]; break; }
        if(is_array($data)){
          foreach($data as $item){
            if(isset($item["fuente"]) && $item["fuente"]=="oficial" && isset($item["promedio"])){
              $rate = $item["promedio"];
              break 2;
            }
          }
        }
        if(isset($data["data"]) && isset($data["data"]["bcv"])){ $rate = $data["data"]["bcv"]; break; }
        if(isset($data["rate"])){ $rate = $data["rate"]; break; }
      }
    }
  }
  return $rate ? floatval($rate) : null;
}

if(isset($_GET["opt"]) && $_GET["opt"]=="get"){
  $rate_row = ConfigurationData::getByPreffix("bcv_rate");
  $updated_row = ConfigurationData::getByPreffix("bcv_rate_updated");
  $cached = $rate_row ? floatval($rate_row->val) : 0;
  $updated = $updated_row ? strtotime($updated_row->val) : 0;
  $fresh = (time() - $updated) < 600;

  if(!$fresh || $cached<=0){
    $new_rate = bcv_fetch_rate();
    if($new_rate && $new_rate>0){
      $cached = $new_rate;
      ConfigurationData::updateValFromName("bcv_rate", $new_rate);
      ConfigurationData::updateValFromName("bcv_rate_updated", date("Y-m-d H:i:s"));
      // save daily rate for monthly reports
      Executor::doit("insert into bcv_history (rate_date,rate,created_at) values ('".date("Y-m-d")."', $new_rate, NOW()) on duplicate key update rate=$new_rate");
    }
  }

  header("Content-Type: application/json");
  echo json_encode(array(
    "rate" => $cached,
    "updated" => $updated_row ? $updated_row->val : "",
    "fresh" => $fresh
  ));
}
?>
