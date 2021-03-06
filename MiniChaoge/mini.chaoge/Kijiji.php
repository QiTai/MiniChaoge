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
	private static $connection = null;

	public static function getConnection() {
		if (self::$connection == null) {
			self::$connection = mysql_connect("localhost", "root", "") or die(mysql_error());
			mysql_select_db("chaoge") or die(mysql_error());
			mysql_query("set names utf8") or die(mysql_error());
		}
		return self::$connection;
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
		$sql = "select * from {$this->table} where {$this->columns[$key]} = $id";
//		var_dump($sql);
		DataConnection::getConnection();
		$rs = mysql_query($sql) or die(mysql_error());;
		$row = mysql_fetch_array($rs);
		if ($row) {
			foreach ($this->columns as $objCol => $dbCol) {
				$this->$objCol = $row[$dbCol];
			}
			return $this;
		} else {
			return null;
		}
	}

	public function find() {
		$result = array();
		$where = 'where 1 = 1 ';
		foreach ($this->columns as $objCol => $dbCol) {
			if ($this->$objCol) {
				$where .= " and {$dbCol} = {$this->$objCol}";			//only bug: here I " and {$objCol} = {$this->$objCol}"; but with former experience, I know that database handling is the place where bug fills. So I var_dump($sql) and find it.
			}
		}
		$sql = "select * from {$this->table} $where";
//		var_dump($sql);
		DataConnection::getConnection();
		$rs = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_array($rs);
		while ($row) {
			$o = clone $this;
			foreach ($o->columns as $objCol => $dbCol) {
				$o->$objCol = $row[$dbCol];
			}
			$result[] = $o;
			$row = mysql_fetch_array($rs);					
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
		$o = clone $this;
		do {
			$result[] = $o;
			$o = $o->parent();
		} while ($o);
		return array_reverse($result);
	}
}

class Category extends Tree {

	public function __construct() {
		$options =array(
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
		$options =array(
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

	public $user, $category, $area;

	public function  __construct() {
		$options =array(
			'key' => 'id',
			'table' => 'babel_topic',
			'columns' => array(
				'id' => 'tpc_id',
				'name' => 'tpc_title',
				'content' => 'tpc_content',
				'userId' => 'tpc_uid',
				'categoryId' => 'tpc_pid',
				'areaId' => 'tpc_area'
			)
		);
		parent::init($options);
	}

	public function load($id = null) {
		parent::load($id);
		$this->user = new User();
		$this->user->id = $this->userId;
		$this->area = new Area();
		$this->area->id = $this->areaId;
		$this->category = new Category();
		$this->category->id = $this->categoryId;
	}

	public function comments() {
		$c = new Comment();
		$c->adId = $this->id;
		return $c->find();
	}
}

class User extends Data {
	public function  __construct() {
		$options =array(
			'key' => 'id',
			'table' => 'babel_user',
			'columns' => array(
				'id' => 'usr_id',
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
	public function  __construct() {
		$options =array(
			'key' => 'id',
			'table' => 'babel_reply',
			'columns' => array(
				'id' => 'rpl_id',
				'content' => 'rpl_content',
				'adId' => 'rpl_tpc_id',
				'userNick' => 'rpl_post_nick',
				'userId' => 'rpl_post_usr_id'
			)
		);
		parent::init($options);
	}
}
