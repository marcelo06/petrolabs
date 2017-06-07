<?php
class database
{
	var $host;
	var $user;
	var $password;
	var $database;
	var $connection;
	var $selected_db;

	function __construct ()
	{
		$this->host = "localhost";
		$this->user = "miguel_website";
		$this->password = "WLi8&u)?A^f?";
		$this->database = "miguel_website";
		$this->connection = mysql_connect($this->host, $this->user, $this->password);
		if(!$this->connection)
		{
			echo "No fue posible establecer conexi&oacute;n con el servidor.";
			exit();
		}
		mysql_query("SET NAMES 'utf8'", $this->connection);
		$this->selected_db = mysql_select_db($this->database, $this->connection);
	}

	function disconnect ()
	{
		return mysql_close($this->connection);
	}

	function insert ($table, $columns, $values)
	{
		$sql = "INSERT INTO ".$table." (".$columns.") VALUES (".$values.")";
				$result = mysql_query($sql, $this->connection);
		if(!$result)
			echo "<script>alert(".mysql_error($this->connection).")</script>";
		return $result;
	}

	function update ($table, $columns, $condition)
	{
		$sql = "UPDATE ".$table." SET ".$columns." WHERE ".$condition;
		
		$result = mysql_query($sql, $this->connection);
		if(!$result)
			echo "<script>alert(".mysql_error($this->connection).")</script>";
		return $result;
	}

	function delete ($table, $condition)
	{
		$sql = "DELETE FROM ".$table." WHERE ".$condition;
		$result = mysql_query($sql, $this->connection);
		if(!$result)
			echo mysql_error($this->connection);
		return $result;
	}

	function select ($columns, $table, $condition = null)
	{
		$sql = "SELECT ".$columns." FROM ".$table;
		
		if(isset($condition))
			$sql = $sql." WHERE ".$condition;
			
		$result = mysql_query($sql, $this->connection);
		return $result;
	}

	function fetch_array ($result)
	{
		return mysql_fetch_array($result, MYSQL_ASSOC);
	}

	function fetch_all ($result)
	{
		while(($row[] = mysql_fetch_array($result, MYSQL_ASSOC)) || array_pop($row));
		return $row;
	}

	function num_rows ($sql)
	{
		return mysql_num_rows($sql);
	}
	function fetch_assoc ($sql)
	{
		return mysql_fetch_assoc($sql);
	}

	function last_insert_id ()
	{
		return mysql_insert_id();
	}

	function greater ($column, $table)
	{
		$sql = "SELECT MAX(".$column.") AS ".$column." FROM ".$table;
		$result = mysql_query($sql, $this->connection);
		if($result)
		{
			$row = mysql_fetch_array($result);
			if($row[$column] != null)
				$greater = $row[$column];
			else
				$greater = 0;
		}
		else
			$greater = 0;
		return $greater;
	}

	function getFieldName($result, $index){

		return mysql_field_name($result, $index);
	}

	function fetch_row($result)	{
		
		return mysql_fetch_row($result);

	}

	function query ($columns, $table, $condition = null)
	{
		$sql = "SELECT ".$columns." FROM ".$table;
		if(isset($condition))
			$sql = $sql." WHERE ".$condition;
			
		$result = mysql_query($sql, $this->connection);
		return $result;
	}
	
}
?>