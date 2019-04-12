<?php

class Endereco extends Model
{
	private static $table = 'endereco';
	private static $columns = [
		'endereco_id',
		'endereco_cep',
		'endereco_logradouro',
		'endereco_bairro',
		'endereco_cidade',
		'endereco_estado',
		'endereco_pais'
	];

	public function save(array $data)
	{
		return $this->saveModel(self::$table, self::$columns, $data);
	}

	public function update(array $data)
	{
		return $this->updateModel(self::$table, self::$columns, $data);
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

	public static function findCep($cep)
	{
		try{
			$sql = new Sql();
			$res = $sql->select('SELECT * FROM endereco WHERE endereco_cep = :cep', array('cep' => $cep));

			if(is_array($res) && !empty($res)){
				return $res;
			}
		} catch (PDOExeption $e){
			return $e->getMessage();
		}

		return false;
	}

	public static function getColumns() : array
	{
		return self::$columns;
	}
}
