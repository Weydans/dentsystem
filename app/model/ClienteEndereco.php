<?php

class ClienteEndereco extends Model
{
	private static $table = 'cliente_endereco';
	private static $columns = [
		'cliente_endereco_numero',
		'cliente_endereco_complemento',
		'cliente_id',
		'endereco_id'
	];

	public function save(array $data)
	{
		return $this->saveModel(self::$table, self::$columns, $data);
	}

	public function update(array $data)
	{
		try{
			$sql = new Sql;
			$sql->query(
				'UPDATE ' . self::$table . ' 
				SET 
				cliente_endereco_numero = :cliente_endereco_numero,
				cliente_endereco_complemento = :cliente_endereco_complemento,
				cliente_id = :cliente_id,
				endereco_id = :endereco_id
				WHERE 
				cliente_id = :cliente_id',
				array(
					':cliente_endereco_numero' =>$data['cliente_endereco_numero'],
					':cliente_endereco_complemento' =>$data['cliente_endereco_complemento'],
					':cliente_id' =>$data['cliente_id'],
					':endereco_id' =>$data['endereco_id']
				)
			);

			return true;

		} catch (PDOException $e){
			echo Msg::setMsg($e->getMessage() . $e->getFile() . $e->getLine(), ERROR);
		}
	}

	public function find(int $id)
	{
		return $this->findModel(self::$table, self::$columns, $id);
	}

	public function listAll()
	{
		return $this->listAllModel(self::$table);
	}

	public function delete(int $id)
	{
		return $this->deleteModel(self::$table, self::$columns, $id);
	}

	public function nextId()
	{
		return $this->nextIdModel(self::$table, self::$columns);
	}

	public function findByClienteId($clienteId)
	{
		try {
			$sql = new Sql;
			$res = $sql->select("SELECT * FROM " . self::$table . " WHERE cliente_id = :cliente_id", 
				array('cliente_id' => $clienteId)
			);

			if (is_array($res)){
				if (!empty($res[0])){
					//var_dump($res[0]);
					return $res[0];
				}
			}			
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function removeByClienteId($clienteId)
	{
		try {
			$sql = new Sql;
			$sql->delete("DELETE FROM " . self::$table . " WHERE cliente_id = :cliente_id", 
				array('cliente_id' => $clienteId)
			);

			return true;

		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public static function getColumns() : array
	{
		return self::$columns;
	}

}
