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

use Elberos\Core\CoreDriver;
use Elberos\Core\Struct;


class Driver extends CoreDriver
{
	
	public $connnections = [];
	
	
	/**
	 * Returns connection hash
	 */
	static function getConnHash($conn)
	{
		return $conn->hostname . "|" . $conn->port . "|" . $conn->login . "|" .
			$conn->password . "|" . $conn->db . "|" . $conn->prefix
		;
	}
	
	
	
	/**
	 * Connect
	 */
	static function connect($conn)
	{
		$last_error = "";
		try
		{
			$str = 'mysql:host='.$conn->host;
			if ($conn->port != null) $str .= ':'.$conn->port;
			if ($conn->database != null) $str .= ';dbname='.$conn->database;
			$pdo = new \PDO(
				$str, $conn->login, $conn->password, 
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
		
		return [$pdo, $last_error];
	}
	
	
	
	/**
	 * Returns pdo connection
	 */
	public function getPDO($conn)
	{
		$hash = static::getConnHash($conn);
		
		if (isset($this->connnections[$hash]))
		{
			if ($this->connnections[$hash]['connected'])
			{
				return $this->connnections[$hash]['pdo'];
			}
		}
		
		list($pdo, $last_error) = static::connect($conn);
		if ($last_error)
		{
			$this->connnections[$hash] = [
				'pdo' => null,
				'connected' => false,
				'last_error' => $last_error,
			];
			throw new \Exception($last_error);
		}
		
		$this->connnections[$hash] = [
			'pdo' => $pdo,
			'connected' => true,
			'last_error' => '',
		];
		
		return $pdo;
	}
	
	
	
	/**
	 * query
	 */
	public function query($conn, $sql, $arr = [])
	{
		$pdo = $this->getPDO($conn);
		$st = $pdo->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
		$st->execute($arr);
		return $st;
	}
	
	
	
	
}