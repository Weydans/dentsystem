<?php

/**
 * <b>Endereco</b>:
 * Classe de modelo de Endereco, padrão DAO.
 * @author Weydans C. Barros, Belo Horizonte, 05/04/2019
 */

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

	/**
	 * <b>save</b>:
	 * Realiza cadastro de endereço no banco de dados.
	 * @param array $data Recebe o array com os dados a serem cadastrados.
	 * @return bool Retorna true caso o cadastro seja efetuado com sucesso.
	 */
	public function save(array $data)
	{
		return $this->saveModel(self::$table, self::$columns, $data);
	}

	/**
	 * <b>update</b>:
	 * Realiza atualização de endereço no banco de dados.
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
	 * Recupera todos os registros existentes na tabela endereco do banco de dados.
	 * @return array Retorna uma matriz numérica de arrays associativos contendo os registros.
	 */
	public function listAll()
	{
		return $this->listAllModel(self::$table);
	}

	/**
	 * <b>delete</b>:
	 * Remove um determinado registro da tabela endereco na base de dados.
	 * @param int $id Recebe o id do registro a ser removido.
	 * @return bool Retorna true caso registro seja excluído com sucesso.
	 */
	public function delete(int $id)
	{
		return $this->deleteModel(self::$table, self::$columns, $id);
	}
 	
 	/**
	 * <b>nextId</b>:
	 * Obtem o próximo Id a ser inserido na tabela endereco
	 * @return int Retorna o próximo id a ser inserido na tabela.
	 */
	public function nextId()
	{
		return $this->nextIdModel(self::$table, self::$columns);
	}

	/**
	 * <b>nextId</b>:
	 * Verifica se um determinado endereço está cadastrado no sistema através do cep.
	 * @param string $cep Recebe o cep a ser verificado.
	 * @return array Retorna um array associativo com os dados do regitro caso cep esteja cadastrado.
	 */
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

	/**
	 * <b>getColumns</b>:
	 * @return array Retorna um array numérico com os nomes das colunas da tabela endereco.
	 */
	public static function getColumns() : array
	{
		return self::$columns;
	}
}
