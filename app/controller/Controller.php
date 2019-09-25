<?php

/**
 * <b>Controller</b>:
 * Classe abstrata responsável por tarefas comuns 
 * em todas as outras classes de controle do sistema.
 * @author Weydans C. Barros, Belo Horizonte, 05/04/2019
 */

abstract class Controller
{	
	private $mainHeader;
	protected $pageController;
	private $mainFooter;

	protected $msg;
	protected $data;
	protected $view;
	protected $userLogin;

	/**
	* <b>setHeader</b>: 
	* Configura o header padrão da página
	* @param string $caminhoArquivoHeader Caminho da view que contem o header
	* @param array $dados Dados para substituir links (Ex: {link} ) do header
	*/
	protected function setHeader(string $caminhoArquivoHeader, array $dados = array())
	{
		$this->mainHeader = Render::show($caminhoArquivoHeader, $dados);
	}

	/**
	* <b>setFooter</b>: 
	* Configura o footer padrão da página
	* @param string $caminhoArquivoFooter Caminho da view que contem o footer
	* @param array $dados Dados para substituir links (Ex: {link} ) do footer
	*/
	protected function setFooter(string $caminhoArquivoFooter, array $dados = array())
	{
		$this->mainFooter = Render::show($caminhoArquivoFooter, $dados);
	}

	/**
	* <b>show</b>: 
	* Configura a página principal,
	* substitui links (Ex: {link} ) por valores contidos no atributo $this->data,
	* renderiza view dinamicamente para o usuário
	* @param string $caminhoArquivoPagina Caminho da view principal
	*/
	protected function setContent(string $caminhoArquivoPagina)
	{
		$this->data['<div class="msg"></div>'] = $this->msg;

		$this->pageController = Render::show($caminhoArquivoPagina , $this->data);

		$this->view = $this->mainHeader . $this->pageController . $this->mainFooter;

		echo $this->view;
	}

	/**
	 * <b>verifyLogin</b>:
	 * Verifica se existe uma sessão de acesso ao admin,
	 * Redireciona para view login caso não exista.
	 */
	protected function verifyLogin()
	{		
		session_start();
		
		if (isset($_SESSION['user'])){
			$this->userLogin = $_SESSION['user'];
		} else {
			header('location: ' . HOME . '?exe=restrito');
			exit();
		}
	}
	
}
