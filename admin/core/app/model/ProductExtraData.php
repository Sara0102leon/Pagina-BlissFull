<?php
class ProductExtraData {
	public static $tablename = "product_extra";

	public $id, $product_id, $name, $price, $group_key;

	public function __construct(){
		$this->id = null;
		$this->product_id = "";
		$this->name = "";
		$this->price = "";
		$this->group_key = "";
	}

	public function add(){
		$product_id = ($this->product_id === "" || $this->product_id === null) ? "NULL" : intval($this->product_id);
		$sql = "insert into ".self::$tablename." (product_id,name,price) ";
		$sql .= "value ($product_id,\"$this->name\",\"$this->price\")";
		Executor::doit($sql);
	}

	public function update(){
		$product_id = ($this->product_id === "" || $this->product_id === null) ? "NULL" : intval($this->product_id);
		$sql = "update ".self::$tablename." set name=\"$this->name\",price=\"$this->price\",product_id=$product_id where id=$this->id";
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
		$sql = "select min(id) as id, group_key, name, price from ".self::$tablename." where group_key is not null and group_key<>'' group by group_key,name,price order by name asc";
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

	public static function setGroup($group_key,$name,$price,$product_ids,$all_products_ids){
		self::delGroup($group_key);
		$pids = array();
		if(is_array($product_ids)){
			foreach($product_ids as $p){ $pid = intval($p); if($pid>0){ $pids[$pid] = $pid; } }
		}
		if(count($pids)>0 && count($pids)==count($all_products_ids)){
			$pids = array(); // todos los productos marcados -> aplica a todos (fila global)
		}
		$sql = "insert into ".self::$tablename." (group_key,name,price,product_id) values ";
		$vals = array();
		if(count($pids)==0){
			$vals[] = "(\"$group_key\",\"$name\",\"$price\",NULL)";
		}else{
			foreach($pids as $pid){ $vals[] = "(\"$group_key\",\"$name\",\"$price\",$pid)"; }
		}
		$sql .= implode(",",$vals);
		Executor::doit($sql);
	}

}
?>
