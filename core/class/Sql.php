<?php

/**
 * 
 */
class Sql 
{	
	/** @var PDO */
	private $conn;

	public function init()
	{
		$this->conn = Conn::getConnection();
	}

	public function query(string $query, $params = array())
	{	
		$this->init();
	
		$stmt = $this->conn->prepare($query);
		$this->setParams($stmt, $params);
		$stmt->execute();
		
		return $stmt;
	}
	
	public function select(string $query, array $params)
	{
		$stmt = $this->query($query, $params);
		
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return $res;	
	}

	public function selectAll(string $tabela)
	{
		$this->init();

		$query = "SELECT * FROM {$tabela}";
		$stmt = $this->conn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (!empty($stmt)){
			return $result;
		} else {
			return false;
		}
	}

	public function delete(string $query, array $data)
	{
		$this->init();

		$stmt = $this->conn->prepare($query);
		$this->setParams($stmt, $data);	
		$result = $stmt->execute();

		return $result;		
	}

	public function prepareData(array $data)
	{
		$result = [];

		foreach ($data as $key => $value) {
			$result += [':'. $key => $value];
		}

		return $result;
	}

	private function setParams($pdoStatement, $values)
	{
		foreach ($values as $key => $value) {
			$this->setParam($pdoStatement, $key, $value);
		}
	}
	
	private function setParam($pdoStatement, $key, $value)
	{
		$pdoStatement->bindParam($key, $value);
	}
	
	public function getConn()
	{
		return $this->conn;
	}
	
}
