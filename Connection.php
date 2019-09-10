<?php

/*!
 *  Elberos ORM Mysql
 *
 *  (c) Copyright 2019 "Ildar Bikmamatov" <support@bayrell.org>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      https://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Elberos\Orm\Mysql;

use Elberos\Core\Struct;


class Connection extends Struct
{
	
	protected $__host = "";
	protected $__port = "";
	protected $__login = "";
	protected $__password = "";
	protected $__database = "";
	protected $__prefix = "";
	protected $__pdo = null;
	
	
	
	/**
	 * Returns is connected
	 */
	function isConnected()
	{
		return $this->pdo != null;
	}
	
	
	
	/**
	 * Connect to database
	 */
	function connect()
	{
		if ($this->__pdo) return $this;
		
		$last_error = "";
		try
		{
			$str = 'mysql:host='.$this->host;
			if ($this->port != null) $str .= ':'.$this->port;
			if ($this->database != null) $str .= ';dbname='.$this->database;
			$pdo = new \PDO(
				$str, $this->login, $this->password, 
				array(
					\PDO::ATTR_PERSISTENT => false
				)
			);
			$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$pdo->exec("set names utf8");
		}
		catch (\PDOException $e)
		{
			$last_error = 'Failed connected to database!';
		}
		catch (\Excepion $e)
		{
			$last_error = $e->getMessage();
		}
		
		if ($last_error)
		{
			throw new \Exception($last_error);
		}
		
		return $this->copy([ "pdo"=>$pdo ]);
	}
	
	
	
	/**
	 * Execute sql query
	 */
	function query($sql, $arr)
	{
		$st = $this->pdo->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
		$st->execute($arr);
		return $st;
	}
	
	
	
	/**
	 * Get first item
	 */
	function getOne($sql, $arr)
	{
		$st = $this->query($sql, $arr);
		return $st->fetch(\PDO::FETCH_ASSOC);
	}
	
	
	
	/**
	 * Execute sql query
	 */
	function insert($table_name, $data)
	{
		$keys = [];
		$values = [];
		foreach ($data as $key=>$val)
		{
			$keys[] = "`" . $key . "`";
			$values[] = ":" . $key;
		}
		$sql = "insert into " . $table_name . 
			" (" . implode(",",$keys) . ") values (" . implode(",",$values) . ")"
		;
		$st = $this->pdo->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
		$st->execute($data);
		return $st;
	}
	
	
	
	
	/**
	 * Execute sql query
	 */
	function insert_or_update($table_name, $insert, $update)
	{
		$ins_keys = [];
		$ins_values = [];
		$upd_data = [];
		$data = [];
		
		foreach ($insert as $key => $val)
		{
			$keys[] = "`".$key."`";
			$values[] = ":".$key;
			$data[ $key ] = $val;
		}
		foreach ($update as $key => $val)
		{
			$keys[] = "`" . $key . "`";
			$values[] = ":" . $key;
			$upd_data[] = "`".$key."` = :" . $key;
			$data[ $key ] = $val;
		}
		
		$sql = "insert into " . $table_name . 
			" (" . implode(",",$keys) . ") " .
			" values (" . implode(",",$values) . ") " .
			(
				(count($upd_data) > 0) ? 
					" ON DUPLICATE KEY UPDATE " . implode(",", $upd_data) : ""
			)
		;
		
		$st = $this->pdo->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
		$st->execute($data);
		return $st;
	}
	
	
}