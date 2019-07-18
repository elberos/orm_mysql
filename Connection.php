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
	
	protected $host;
	protected $login;
	protected $password;
	protected $db;
	protected $pdo;
	
	
	
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
		$pdo = new \PDO(
			'mysql:host='.$this->host.';dbname='.$this->db, $this->login, $this->password, 
			array(
				\PDO::ATTR_PERSISTENT => false
			)
		);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$pdo->exec("set names utf8");
		return $this->copy([ "pdo"=>$pdo ]);
	}
	
	
	
	/**
	 * Execute sql query
	 */
	function query($sql, $arr)
	{
		global $pdo;
		$st = $pdo->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
		$st->execute($arr);
		return $st;
	}
	
	
	
	/**
	 * Get first item
	 */
	function getOne($sql, $arr)
	{
		$st = pdo_query($sql, $arr);
		return $st->fetch(PDO::FETCH_ASSOC);
	}
	
	
	
}