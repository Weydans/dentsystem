<?php

/**
 * <b>Cliente</b>:
 * Classe de modelo de clientes, padrão DAO.
 * @author Weydans C. Barros, Belo Horizonte, 05/04/2019
 */

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

	/**
	 * <b>save</b>:
	 * Realiza cadastro de cliente no banco de dados.
	 * @param array $data Recebe o array com os dados a serem cadastrados.
	 * @return bool Retorna true caso o cadastro seja efetuado com sucesso.
	 */
	public function save(array $data)
	{
		return $this->saveModel(self::$table, self::$columns, $data);
	}

	/**
	 * <b>update</b>:
	 * Realiza atualização de cliente no banco de dados.
	 * @param array $data Recebe o array com os dados a serem atualizados.
	 * @return bool Retorna true caso o atualização seja realizada com sucesso.
	 */
	public function update(array $data)
	{
		return $this->updateModel(self::$table, self::$columns, $data);
	}

	/**
	 * <b>find</b>:
	 * Recupera um determinado registro na base de dados.
	 * @param int $id Recebe o id do registro a ser recuperado.
	 * @return array Retorna um array associativo caso o registro exista.
	 */
	public function find(int $id)
	{
		return $this->findModel(self::$table, self::$columns, $id);
	}

	/**
	 * <b>listAll</b>:
	 * Recupera todos os registros existentes na tabela cliente do banco de dados.
	 * @return array Retorna uma matriz numérica de arrays associativos contendo os registros.
	 */
	public function listAll()
	{
		return $this->listAllModel(self::$table);
	}

	/**
	 * <b>delete</b>:
	 * Remove um determinado registro da tabela cliente na base de dados.
	 * @param int $id Recebe o id do registro a ser removido.
	 * @return bool Retorna true caso registro seja excluído com sucesso.
	 */
	public function delete(int $id)
	{
		return $this->deleteModel(self::$table, self::$columns, $id);
	}

	/**
	 * <b>nextId</b>:
	 * Obtem o próximo Id a ser inserido na tabela cliente
	 * @return int Retorna o próximo id a ser inserido na tabela.
	 */
	public function nextId()
	{
		return $this->nextIdModel(self::$table, self::$columns);
	}

	/**
	 * <b>getColumns</b>:
	 * @return array Retorna um array numérico com os nomes das colunas da tabela cliente.
	 */
	public static function getColumns() : array
	{
		return self::$columns;
	}

}
