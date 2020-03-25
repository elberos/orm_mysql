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
	
	
	
	/**
	 * Execute sql query
	 */
	static function query($con, $sql, $arr = [])
	{
		global $ctx;
		
		$driver = $ctx::getDriver($ctx, "Elberos.Orm.Mysql.Driver");
		return $driver->query($con, $sql, $arr);
	}
	
	
	
	/**
	 * Get first item
	 */
	static function getOne($con, $sql, $arr)
	{
		$st = static::query($con, $sql, $arr);
		return $st->fetch(\PDO::FETCH_ASSOC);
	}
	
	
	
	/**
	 * Execute sql query
	 */
	static function insert($con, $table_name, $data)
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
		$st = static::query($con, $sql, $data);
		return $st;
	}
	
	
	
	
	/**
	 * Execute sql query
	 */
	static function insert_or_update($con, $table_name, $insert, $update)
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
		
		$st = static::query($con, $sql, $data);
		return $st;
	}
	
	
	
	/**
	 * Execute
	 */
	static function foundRows($con)
	{
		$sql = "SELECT FOUND_ROWS() as c;";
		$st = static::query($con, $sql);
		$res = $st->fetch(\PDO::FETCH_ASSOC);
		return $res['c'];
	}
	
}