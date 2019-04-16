<?php

/**
 * <b>ClienteEndereco</b>:
 * Classe de modelo de ClienteEndereco, padrão DAO.
 * @author Weydans C. Barros, Belo Horizonte, 05/04/2019
 */

class ClienteEndereco extends Model
{
	private static $table = 'cliente_endereco';
	private static $columns = [
		'cliente_endereco_numero',
		'cliente_endereco_complemento',
		'cliente_id',
		'endereco_id'
	];

	/**
	 * <b>save</b>:
	 * Realiza cadastro de ClienteEndereco no banco de dados.
	 * @param array $data Recebe o array com os dados a serem cadastrados.
	 * @return bool Retorna true caso o cadastro seja efetuado com sucesso.
	 */
	public function save(array $data)
	{
		return $this->saveModel(self::$table, self::$columns, $data);
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
	 * Recupera todos os registros existentes na tabela cliente_endereco do banco de dados.
	 * @return array Retorna uma matriz numérica de arrays associativos contendo os registros.
	 */
	public function listAll()
	{
		return $this->listAllModel(self::$table);
	}

	/**
	 * <b>delete</b>:
	 * Remove um determinado  registro da tabela cliente_endereco na base de dados.
	 * @param int $id Recebe o id do registro a ser removido.
	 * @return bool Retorna true caso registro seja excluído com sucesso.
	 */
	public function delete(int $id)
	{
		return $this->deleteModel(self::$table, self::$columns, $id);
	}

	/**
	 * <b>nextId</b>:
	 * Obtem o próximo Id a ser inserido na tabela cliente_endereco.
	 * @return int Retorna o próximo id a ser inserido na tabela.
	 */
	public function nextId()
	{
		return $this->nextIdModel(self::$table, self::$columns);
	}

	/**
	 * <b>update</b>:
	 * Atualiza um determinado registro da tabela cliente_endereço.
	 * @param array $data Recebe os dados do registro a ser atualizado.
	 * @return bool Retorna true em caso de registro atualizado com sucesso.
	 */
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
			throw new Exeption( Msg::setMsg($e->getMessage() . ' - ' . $e->getFile() . ' - ' . $e->getLine(), ERROR) );
		}
	}

	/**
	 * <b>findByClienteId</b>:
	 * Recupera um determinado registro da tabela cliente_endereco pelo valor da coluna cliente_id.
	 * @param int $clienteId Recebe o id do cliente.
	 * @return array Retorna um array associativo com dados do registro.
	 */
	public function findByClienteId($clienteId)
	{
		try {
			$sql = new Sql;
			$res = $sql->select("SELECT * FROM " . self::$table . " WHERE cliente_id = :cliente_id", 
				array('cliente_id' => $clienteId)
			);

			if (is_array($res)){
				if (!empty($res[0])){
					return $res[0];
				}
			}			
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	/**
	 * <b>removeByClienteId</b>:
	 * Remove um determinado registro da tabela cliente_endereco pelo valor da coluna cliente_id.
	 * @param int $clienteId Recebe o id do cliente.
	 * @return bool Retorna true caso registro removido com sucesso.
	 */
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

	/**
	 * <b>getColumns</b>:
	 * @return array Retorna um array numérico com os nomes das colunas da tabela cliente_endereco.
	 */
	public static function getColumns() : array
	{
		return self::$columns;
	}

}
