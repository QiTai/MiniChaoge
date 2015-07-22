<?php

header("Content-type : text/html; charset = utf-8"); //------------
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);  //-----------

class DataConnection {
	private static $connection = null;

	public static function getConnection() {
		if (self::$connection == null) {					//-------------------
			self::$connection = mysql_connect('localhost','root','') or die(mysql_error());  //------------
			mysql_select_db('chaoge') or die(mysql_error());
			mysql_query('set names utf8') or die(mysql_error());	//-----names不是name;是utf8不是utf-8
		}
		return self::$connection;
	}
}

class Data {
	public $key, $table, $columns;

	public function init($option) {				//数据结构统一写init函数
		$this->key 		= $option['key'];			//omit $this->key
		$this->table 	= $option['table'];
		$this->columns	= $option['columns'];
	}

	public function reset() {
		foreach ($this->columns as $objCol => $dbCol) {
			$this->$objCol = null;			//---------------
		}
	}

	public function load($id = null) {	//----------($id = null)
		$key = $this->key;
		if($id == null) {
			$id = $this->$key;
		}
		$sql = "select * from {$this->table} where {$this->columns[$key]} = $id";	//--------
		DataConnection::getConnection();
		$rs = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_array($rs);
		//-------------------------------------------------------------|
		if ($row) {												//     |
			foreach ($this->columns as $objCol => $dbCol) {	//     |
				$this->$objCol = $row[$dbCol];					//     |
			}													//     |
			return $this;										//     |
		} else {												//     |
			return null;										//     |
		}														//     |
		//-------------------------------------------------------------|
	}

	public function find() {
		$result = array();			//---------
		$where = 'where 1=1 ';
		foreach ($this->columns as $objCol => $dbCol) {
			if ($this->$objCol) {
				$where .= " and $dbCol = {$this->$objCol}";
			}
		}
		$sql = "select * from {$this->table} $where";
		DataConnection::getConnection();
		$rs = mysql_query($sql) or die(mysql_error());
		//-------------------------------------------------------------
		$row = mysql_fetch_assoc($rs);
		while ($row) {
			$o = clone $this;
			foreach ($o->columns as $objCol => $dbCol) {
				$o->$objCol = $row[$dbCol];
			}
			$result[] = $o;
//			var_dump($o);
			$row = mysql_fetch_assoc($rs);
		}
		return $result;
		//-------------------------------------------------------------
	}
}

class Tree extends Data {
	private $pkey;

	public function init($options) {		//-----------不是__construct
		parent::init($options);        		//----------数据结构统一写init()函数
		$this->pkey = $options['pkey'];
	}

	public function parent() {
		$o = clone $this;
		$o->reset();
		$o->{$o->key} = $this->{$this->pkey};
		return $o->load();			//---------写成$o->load(); 父亲只有一个，精准定位，根据id定位
	}

	public function children() {
		$o = clone $this;
		$o->reset();
		$o->{$o->pkey} = $this->{$this->key};
		return $o->find();			//--------写成$o->load() 儿子有很多个，要find()，非精准定位
	}

	public function toRoot() {
		$o = clone $this;
		do {
			$result[] = $o;
			$o = $o->parent();
		} while ($o);
		return array_reverse($result); 	//------omit array_reverse
	}
}

class Category extends Tree {

	public function __construct() {
		$options = array(
			'key' => 'id',
			'pkey' => 'pid',
			'table' => 'babel_node',
			'columns' => array(			//----字段
				'id' => 'node_id',
				'pid' => 'nod_pid',
				'name' => 'nod_title'
			)
		);
		parent::init($options);
	}

	public function ads() {
		$o = new Ad();
		$o->categoryId = $this->id;
		return $o->find();				//categoryId为当前category的广告有很多，用find
	}
}

class Area extends Tree {
	public function __construct() {
		$options = array(
			'key' => 'id',
			'pkey' => 'pid',
			'table' => 'babel_area',
			'columns' => array(				//----字段
				'id' => 'area_id',
				'pid' => 'area_pid',
				'name' => 'area_title'
			)
		);
		parent::init($options);				//------
	}

	public function ads() {
		$o = new Ad();
		$o->areaId = $this->id;
		return $o->find();
	}
}

class Ad extends Data {
	public $user, $area, $category;

	public function __construct() {
		$options = array(
			'key' => 'id',
			'table' => 'babel_topic',
			'columns' => array(
				'id' => 'tpc_id',
				'name' => 'tpc_title',
				'categoryId' => 'tpc_pid',
				'areaId' => 'tpc_area',
				'userId' => 'tpc_uid',
				'content' => 'tpc_content'
			)
		);
		parent::init($options);
	}

	public function load($id = null) {		//---$id
		parent::load($id);						//------
		$this->category = new Category();		//this->
		$this->category->id = $this->categoryId;
		$this->user = new User();
		$this->user->id = $this->userId;
		$this->area = new Area();
		$this->area->id = $this->areaId;
	}

	public function comments() {
		$o = new Comment();
		$o->adId = $this->id;
		return $o->find();					//---关于这个广告的comments有很多，所以find()
	}
}

class User extends Data {

	public function __construct() {
		$options = array(
			'key' => 'id',
			'table' => 'babel_user',
			'columns' => array(			//竟然因为columns少写了s导致出错
				'id' => 'usr_id',
				'email' => 'usr_email',
				'name' => 'usr_nick'
			)
		);
		parent::init($options);
	}

	public function ads() {
		$o = new Ad();
		$o->userId = $this->id;
		return $o->find();				//----------关于此人的广告多，用find()
	}
}

class Comment extends Data {
	public function __construct() {
		$options = array(
			'key' => 'id',
			'table' => 'babel_reply',
			'columns' => array(			//竟然因为columns少写了s导致出错
				'id' => 'rpl_id',
				'userId' => 'rpl_post_usr_id',
				'userNick' => 'rpl_post_nick',
				'adId' => 'rpl_tpc_id',
				'content' => 'rpl_content'
			)
		);
		parent::init($options);
	}
}

