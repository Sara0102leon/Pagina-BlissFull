<?php
class ProductExtraData {
	public static $tablename = "product_extra";

	public $id, $product_id, $name, $price, $group_key, $is_ingredient;

	public function __construct(){
		$this->id = null;
		$this->product_id = "";
		$this->name = "";
		$this->price = "";
		$this->group_key = "";
		$this->is_ingredient = "0";
	}

	public function add(){
		$product_id = ($this->product_id === "" || $this->product_id === null) ? "NULL" : intval($this->product_id);
		$sql = "insert into ".self::$tablename." (product_id,name,price,is_ingredient) ";
		$sql .= "value ($product_id,\"$this->name\",\"$this->price\"," . intval($this->is_ingredient) . ")";
		Executor::doit($sql);
	}

	public function update(){
		$product_id = ($this->product_id === "" || $this->product_id === null) ? "NULL" : intval($this->product_id);
		$sql = "update ".self::$tablename." set name=\"$this->name\",price=\"$this->price\",product_id=$product_id,is_ingredient=" . intval($this->is_ingredient) . " where id=$this->id";
		Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "delete from ".self::$tablename." where id=$id";
		Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new ProductExtraData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by product_id asc, price desc, name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductExtraData());
	}

	public static function getByProductId($id){
		$sql = "select * from ".self::$tablename." where product_id is null or product_id=$id order by price desc, name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductExtraData());
	}

	public static function getGroups(){
		$sql = "select min(id) as id, group_key, name, price, max(is_ingredient) as is_ingredient from ".self::$tablename." where group_key is not null and group_key<>'' group by group_key,name,price order by name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductExtraData());
	}

	public static function getByGroup($group_key){
		$sql = "select * from ".self::$tablename." where group_key=\"$group_key\" order by product_id is null desc, product_id asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductExtraData());
	}

	public static function groupHasGlobal($rows){
		foreach($rows as $r){ if($r->product_id===null || intval($r->product_id)<=0){ return true; } }
		return false;
	}

	public static function productIdsFromGroup($rows){
		$ids = array();
		foreach($rows as $r){ if(intval($r->product_id)>0){ $ids[] = intval($r->product_id); } }
		return $ids;
	}

	public static function delGroup($group_key){
		$sql = "delete from ".self::$tablename." where group_key=\"$group_key\"";
		Executor::doit($sql);
	}

	public static function addProductToAllGroups($product_id){
		$pid = intval($product_id);
		if($pid<=0){ return; }
		$groups = self::getGroups();
		$vals = array();
		foreach($groups as $g){
			$rows = self::getByGroup($g->group_key);
			if(self::groupHasGlobal($rows)){ continue; }
			$vals[] = "(\"".$g->group_key."\",\"".$g->name."\",\"".$g->price."\",".$pid.",".intval($g->is_ingredient).")";
		}
		if(count($vals)>0){
			$sql = "insert into ".self::$tablename." (group_key,name,price,product_id,is_ingredient) values ".implode(",",$vals);
			Executor::doit($sql);
		}
	}

	public static function setGroup($group_key,$name,$price,$product_ids,$all_products_ids,$is_ingredient=0){
		self::delGroup($group_key);
		$pids = array();
		if(is_array($product_ids)){
			foreach($product_ids as $p){ $pid = intval($p); if($pid>0){ $pids[$pid] = $pid; } }
		}
		if(count($pids)>0 && count($pids)==count($all_products_ids)){
			$pids = array(); // todos los productos marcados -> aplica a todos (fila global)
		}
		$ig = intval($is_ingredient);
		$sql = "insert into ".self::$tablename." (group_key,name,price,product_id,is_ingredient) values ";
		$vals = array();
		if(count($pids)==0){
			$vals[] = "(\"$group_key\",\"$name\",\"$price\",NULL,$ig)";
		}else{
			foreach($pids as $pid){ $vals[] = "(\"$group_key\",\"$name\",\"$price\",$pid,$ig)"; }
		}
		$sql .= implode(",",$vals);
		Executor::doit($sql);
	}

}
?>
