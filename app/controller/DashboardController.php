<?php

/**
 * <b>DashboardController</b>:
 *
 * @author Weydans C. Barros, Belo Horizonte, 05/04/2019
 */

class DashboardController extends Controller
{
	private static $pageTitle = 'Dashboard';
	private static $fraseSubtitulo = 'Painel de controle para gerenciamento do sistema.';

	private $pageAction;
	private $viewConteudo;
	private $viewClienteConteudo;
	private $clienteConteudoValues;

	public function showDashboard()
	{
		$this->pageAction = 'Painel de Controle';
		$this->msg = '';

		$this->setData();
		$this->show();
	}

		/**
	* <b>show</b>:
	* Realiza a montagem e exibição da view
	*/
	private function show()
	{		
		$this->verifyLogin();

		$this->setHeader('./app/view/topo.html', $this->userLogin);
		$this->setFooter('./app/view/rodape.html');
		$this->setContent('./app/view/dashboard.html');
	}

	/**
	* <b>setData</b>:
	* Reune os dados a serem substituidos na view principal
	*/
	private function setData()
	{
		$this->data['pageTitle'] = self::$pageTitle;
		$this->data['fraseSubtitulo'] = self::$fraseSubtitulo;
		$this->data['pageAction'] = $this->pageAction;
		$this->data['conteudo'] = $this->viewConteudo;
	}

}