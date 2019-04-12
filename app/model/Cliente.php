<?php

class Cliente extends Model
{
	private static $table = 'cliente';
	private static $columns = [
		'cliente_id',
		'cliente_name',
		'cliente_rg',
		'cliente_cpf',
		'cliente_nascimento',
		'cliente_phone1',
		'cliente_phone2',
		'cliente_email'
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

	public static function getColumns() : array
	{
		return self::$columns;
	}

}
