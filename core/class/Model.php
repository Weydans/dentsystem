<?php

/**
 * 
 */
abstract class Model
{
	private $campos;
	private $binds;
	private $campoId;
	private $bindId;
	private $setUpdate;

    /**
     * ========================================================
     *  SALVA USUÁRIO NO BANCO DE DADOS
     * ========================================================
     */
    protected function saveModel(string $tabela, array $campos, array $data) 
    {	
    	try{
    		$sql = new Sql();
    		$data = $sql->prepareData($data);

    		$this->getCampos($campos);
    		$this->getBinds($campos);

    		$sql->query("INSERT INTO {$tabela} ({$this->campos}) VALUES ({$this->binds})", $data);

    		if ($sql->getConn()->lastInsertId() > 0){
    			return true;
    		} else {
                return true;
            }

    	} catch (PDOException $e){
    		return $e->getMessage();
    	}
    }

    /**
     * ========================================================
     *  ATUALIZA DADOS DO USUÁRIO NO BANCO DE DADOS
     * ========================================================
     */
    protected function updateModel(string $tabela, array $campos, array $data) 
    {  
    	try{
    		$sql = new Sql();
    		$data = $sql->prepareData($data);

    		$this->setUpdate($campos);
    		$this->getCampoId($campos);
    		$this->getBindId($campos);

    		$sql->query("UPDATE {$tabela} SET {$this->setUpdate} WHERE {$this->campoId} = {$this->bindId}", $data);

    		return true;

    	} catch (PDOException $e){
    		return $e->getMessage();
    	}		
    }

    /**
     * ========================================================
     *  ENCONTRA UM DETERMINADO USUARIO NO BANCO DE DADOS
     * ========================================================
     */
    protected  function findModel(string $tabela, array $campos, int $id) 
    {
    	try{
    		$sql = new Sql();

    		$this->getCampoId($campos);
    		$this->getBindId($campos);

    		$result = $sql->select("SELECT * FROM {$tabela} WHERE {$this->campoId} = {$this->bindId}", 
    								array($this->bindId => $id));  

    		if (!empty($result)) {
    			return $result[0];
    		} else {
    			return false;
    		}

    	} catch (PDOException $e){
    		return $e->getMessage();
    	}
    }

    /**
     * ========================================================
     *  LISTA TODOS OS USUARIOS CADASTRADOS NO BANCO DE DADOS
     * ========================================================
     */
    protected function listAllModel(string $tabela) 
    {
    	try{
    		$sql = new Sql;
    		$sql = $sql->selectAll($tabela);

    		return $sql;

    	} catch (PDOException $e){
    		return $e->getMessage();
    	}		
    }

    /**
     * ========================================================
     * EXCLUI USUARIO NO BANCO DE DADOS
     * ========================================================
     */
    protected function deleteModel(string $tabela, array $campos, int $id) 
    {
    	try{
    		$sql = new Sql;

    		$this->getCampoId($campos);
    		$this->getBindId($campos);

    		$result = $sql->delete("DELETE FROM {$tabela} WHERE {$this->campoId} = {$this->bindId}", 
    								array($this->bindId => $id));

    		return $result;

    	} catch (PDOException $e){
    		return $e->getMessage();
    	}		
    }

    /**
     * ========================================================
     *  ENCONTRA O PROXIMO ID A SER INSERIDO NA TABELA PESSOAS
     * ========================================================
     */
    protected function nextIdModel(string $tabela, array $campos)
    {
    	try{
    		$array = self::listAllModel($tabela);

    		if (is_array($array) && count($array) > 0){
    			$this->getCampoId($campos);

    			$nextId = ($array[count($array) - 1][$this->campoId]) + 1;

                unset($array);

    			return $nextId;

    		} else {
    			return 1;
    		}
    	} catch (PDOException $e){
    		return $e->getMessage();
    	}
    }

    /**
     * ========================================================
     *  SALVA USUÁRIO NO BANCO DE DADOS
     * ========================================================
     */
    private function setUpdate(array $campos)
    {
    	foreach ($campos as $campo) { 
    		$array = $campo . ' = :' . $campo;
    		$this->setUpdate[] = $array;
    	}

    	$this->setUpdate = implode(', ', $this->setUpdate);
    }

    /**
     * ========================================================
     *  SALVA USUÁRIO NO BANCO DE DADOS
     * ========================================================
     */
    private function getCampos(array $campos)
    {
    	$this->campos = implode(', ', $campos);
    }

    /**
     * ========================================================
     *  SALVA USUÁRIO NO BANCO DE DADOS
     * ========================================================
     */
    private function getBinds(array $campos)
    {
    	$this->binds = ':' . implode(', :', $campos);
    }

    /**
     * ========================================================
     *  SALVA USUÁRIO NO BANCO DE DADOS
     * ========================================================
     */
    private function getCampoId(array $campos)
    {
    	$this->campoId = $campos[0];
    }

    /**
     * ========================================================
     *  SALVA USUÁRIO NO BANCO DE DADOS
     * ========================================================
     */
    private function getBindId(array $campos)
    {
    	$this->bindId = ':' . $campos[0];
    }
}
