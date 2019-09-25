<?php

class User extends Model
{
	private static $table = 'user';
	private static $columns = [
		'user_id',
		'user_name',
		'user_email',
		'user_pass',
		'user_level'
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

	public function findByEmail(string $email)
	{
		try {
			$sql = new Sql;

			$result = $sql->select("SELECT * FROM " . self::$table . " WHERE user_email = :email", array(
				':email' => $email
			));

			if (count($result) > 0){
				return $result[0];

			} else {
				return $result;
			}

		} catch (Exception $e) {
			return Msg::setMsg($e->getMessage() . '<br/>' . $e->getFile() . ' ## '. $e->getLine(), ERROR);
		}
	}

}
