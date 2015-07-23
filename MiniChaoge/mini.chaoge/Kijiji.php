<?php
/**
 * Created by PhpStorm.
 * User: acer
 * Date: 2015/7/21
 * Time: 19:59
 */

header("Content-type : text/html ; charset = utf-8");
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);

class DataConnection {
	private static $connection = null;  																//又一次忘了null

	public static function getConnection() {
		if (self::$connection == null) {
			self::$connection = mysql_connect("localhost", "root", "") or die(mysql_error());			//又一次忘了self
			mysql_select_db("chaoge") or die(mysql_error());
			mysql_query("set names utf8") or die(mysql_error());
		}
		return self::$connection;																		//又一次忘了self
	}
}

class Data {
	public $key, $table, $columns;

	public function init($options) {
		$this->key = $options['key'];
		$this->table = $options['table'];
		$this->columns = $options['columns'];
	}

	public function reset() {
		foreach ($this->columns as $objCol => $dbCol) {
			$this->$objCol = null;
		}
	}

	public function load($id = null) {
		$key = $this->key;
		if ($id == null) {
			$id = $this->$key;
		}
		$sql = "select * from {$this->table} where {$this->columns[$key]} = {$id}";				//我又一次在这里出现了错误。不只一次了
		DataConnection::getConnection();
		$rs = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_array($rs);
		if ($row) {
			foreach ($this->columns as $objCol => $dbCol) {
				$this->$objCol = $row[$dbCol];
			}
			return $this;																			//我写成了return $row
		} else {
			return null;
		}
	}

	public function find() {
		$result = array();
		$where = 'where 1 =1 ';
		foreach ($this->columns as $objCol => $dbCol) {
			if ($this->$objCol) {
				$where .= " and $dbCol = {$this->$objCol}";
			}
		}
		$sql = "select * from {$this->table} $where";
//		var_dump($sql);
		DataConnection::getConnection();
		$rs = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_assoc($rs);
		while ($row) {
			$o = clone $this;																		//会忘记
			foreach ($o->columns as $objCol => $dbCol) {
				$o->$objCol = $row[$dbCol];
			}
			$result[] = $o;
			$row = mysql_fetch_assoc($rs);
		}
		return $result;
	}
}

class Tree extends Data {
	public $pkey;

	public function init($options) {
		parent::init($options);
		$this->pkey = $options['pkey'];
	}

	public function parent() {
		$o = clone $this;
		$o->reset();
		$o->{$o->key} = $this->{$this->pkey};
		return $o->load();
	}

	public function children() {
		$o = clone $this;
		$o->reset();
		$o->{$o->pkey} = $this->{$this->key};
		return $o->find();
	}

	public function toRoot() {
		$o = clone $this;																		//地方放错了
		do {
			$result[] = $o;																		//感觉要实现声明$result啊？？
			$o = $o->parent();
		} while ($o);
		return array_reverse($result);
	}
}

class Category extends Tree {
	public function __construct() {
		$options = array(
			'key' => 'id',
			'pkey' => 'pid',
			'table' => 'babel_node',
			'columns' => array(
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
		return $o->find();
	}
}

class Area extends Tree {

	public function __construct() {
		$options = array(
			'key' => 'id',
			'pkey' => 'pid',
			'table' => 'babel_area',
			'columns' => array(
				'id' => 'area_id',
				'pid' => 'area_pid',
				'name' => 'area_title'
			)
		);
		parent::init($options);
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
				'content' => 'tpc_content',					//竟然有这个属性；有两个地方有content属性，分别是Ad的content和评论Comment的content;

				'categoryId' => 'tpc_pid',
				'userId' => 'tpc_uid',
				'areaId' => 'tpc_area'

			)
		);
		parent::init($options);
	}

	public function load($id = null) {
		parent::load($id);
		$this->category = new Category();						//这里完全写错了
		$this->category->id = $this->categoryId ;				//
		$this->user = new User();								//
		$this->user->id = $this->userId;						//
		$this->area = new Area();								//
		$this->area->id = $this->areaId;						//
	}

	public function comments() {
		$o = new Comment();
		$o->adId = $this->id;
		return $o->find();
	}
}

class User extends Data {
	public function __construct() {
		$options = array(
			'key' => 'id',
			'table' => 'babel_user',
			'columns' => array(
				'id' => 'usr_id',							//也就是只有这三个地方和rpl_post_usr_id写usr了，我错写成了user
				'name' => 'usr_nick',
				'email' => 'usr_email'
			)
		);
		parent::init($options);
	}

	public function ads() {
		$o = new Ad();
		$o->userId = $this->id;
		return $o->find();
	}
}

class Comment extends Data {

	public function __construct() {
		$options = array(
			'key' => 'id',
			'table' => 'babel_reply',
			'columns' => array(
				'id' => 'rpl_id',
				'content' => 'rpl_content',                         	//只有这一个地方是content
				'userId' => 'rpl_post_usr_id',							//这里也有usr
				'userNick' => 'rpl_post_nick',
				'adId' => 'rpl_tpc_id'
			)
		);
		parent::init($options);
	}
}