<?php

/**
 * <b>ClienteController</b>:
 * Classe Responsável por todo o gerenciamento de clientes do sistema,
 * Cadastro, edição, busca, exclusão e gerenciamento das views relacionadas 
 * a gestão de clientes.
 * @author Weydans Campos de Barros, 16/04/2019.
 */
class ClienteController extends Controller
{
	private static $pageTitle = 'Clientes';
	private static $fraseSubtitulo = 'Aqui você gerencia todos os dados de seus cliente';

	private $dataForm;
	private $dataCliente;
	private $dataEndereco;
	private $dataClienteEndereco;
	private $action;
	private $pageAction;
	private $viewConteudo;
	private $viewClienteConteudo = array();
	private $clienteConteudoValues;

	/**
	 * <b>pageCadastro</b>:
	 *	Executa a exibição da página de cadastro 
	 */
	public function pageCadastro()
	{
		$this->pageAction = 'Cadastrar';
		$this->action = 'cadastrar';
		$this->msg = '';

		$this->clearForm();

		$this->showViewForm();
	}

	/**
	 * <b>cadastrar</b>:
	 * Recebe os dados do formulário de cadastro via metodo POST,
	 * Configura layout, chama metodo auxiliar que realiza o cadastro e
	 * redireciona para a página de edição em caso de sucesso.
	 */
	public function cadastrar()
	{
		$dadosForm = filter_input_array(INPUT_POST, FILTER_DEFAULT);

		if (empty($dadosForm)){
			$this->pageCadastro();
			return 0;

		} elseif (empty($dadosForm['cliente_id'])){
			$this->pageAction = 'Cadastrar';
			$this->action = 'cadastrar';

			$cadResult = $this->exeCadastrar($dadosForm);

			if (is_numeric($cadResult)){
				$id = $cadResult;
				header("location: " . BASE_URL . HOME . "/admin/cliente/{$id}/editar?create=true");
			}

		}

		$this->showViewForm();
	}
	
	/**
	 * <b>editar</b>:
	 * Recebe via GET parametro contendo o id do usuárioa ser editado,
	 * Chama método auxiliar exeAtualizar, caso atualização com sucesso.
	 * Exibe Menságens ao usuário sobre cadastro edição.
	 * Executa a exibição da página de edição.
	 * @param numeric $id Recebe o id do usuário a ser atualizado pelo sistema.
	 */
	public function editar($id)
	{	
		$this->pageAction = 'Atualizar';		
		$this->action = $id . '/atualizar';

		$dadosForm = filter_input_array(INPUT_POST, FILTER_DEFAULT);

		$cadastro = filter_input(INPUT_GET, 'create', FILTER_VALIDATE_BOOLEAN);
		$atualizacao = filter_input(INPUT_GET, 'update', FILTER_VALIDATE_BOOLEAN);

		if (isset($dadosForm['cliente_id'])){
			$atualizaResult = $this->exeAtualizar($dadosForm);

			if (is_numeric($atualizaResult)){
				$id = $atualizaResult;
				header("location: " . BASE_URL . HOME . "/cliente/{$id}/editar?update=true");
			}	

		} elseif (is_numeric($id)){
			$id  = (int) $id;	

			if ($cadastro === true){
				$this->preencherFormEditar($id);

				if (!isset($this->clienteConteudoValues['cliente_endereco_complemento'])){
					$this->clienteConteudoValues['cliente_endereco_complemento'] = '';			
				}

				$this->msg = Msg::setMsg("Cliente <b>{$this->clienteConteudoValues['cliente_name']}</b> cadastrado(a) com sucesso.", ACCEPT);

			} elseif ($atualizacao === true){
				$this->preencherFormEditar($id);

				if (!isset($this->clienteConteudoValues['cliente_endereco_complemento'])){
					$this->clienteConteudoValues['cliente_endereco_complemento'] = '';			
				}

				$this->msg = Msg::setMsg("Cliente <b>{$this->clienteConteudoValues['cliente_name']}</b> atualizado(a) com sucesso.", ACCEPT);

			} else {
				$this->msg = '';
				$this->preencherFormEditar($id);				
			}

		} else {
			$this->msg = Msg::setMsg('Informe um usuário válido.', ERROR);
			$this->clearForm();
		}

		$this->showViewForm();

	}

	/**
	 * <b>listar</b>:
	 * Realiza a listagem de clientes cadastrados no sistema atraves dachamada do metodo getLista(),
	 * Exibe botões de ação de ver, editar, e excluir, podendo assim redirecionar
	 * o fluxo da aplicação para outas telas ou receber dados para exclusão.
	 * Executa a exibição da página de edição,
	 */
	public function listar()
	{		
		$this->pageAction = 'Lista';

		$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

		if ($id){
			$this->excluir($id);

		} elseif ($id !== null && $id === false) {
			$this->msg = Msg::setMsg('Informe um valor válido para poder excluir um usuário.', ERROR);
		}

		$listaClientes = $this->getLista();
		$this->clienteConteudoValues['itemLista'] = $listaClientes;
		$this->setLinkClienteConteudo('./app/view/cliente-lista.html');

		$this->showViewList();
	}

	/**
	 * <b>excluir</b>:
	 * Exclui clientes cadastrados no sistema,
	 * Recebe o id do cliente a ser excluído, exclui, e configura menságens ao usuário,
	 * Redireciona a aplicação para a página de edição,
	 */
	private function excluir($id)
	{
		$cliente = new Cliente;
		$clienteExiste = $cliente->find($id);

		if ($clienteExiste === false){
			$this->msg = Msg::setMsg('Você tentou excluir um cliente que nao está cadastrados no sistema.', ERROR);

		} elseif (is_array($clienteExiste)) {
			$clienteEndereco = new ClienteEndereco;
			$resCadClienteEndereco = $clienteEndereco->removeByClienteId($clienteExiste['cliente_id']);

			if ($resCadClienteEndereco === true){
				$resCliente = $cliente->delete($clienteExiste['cliente_id']);

				if ($resCliente === true){
					$this->msg = Msg::setMsg("Cliente <b>{$clienteExiste['cliente_name']}</b> excluído(a) com sucesso.", ACCEPT);
				} else {
					$this->msg = Msg::setMsg('Erro ao excluir Cliente.', ERROR);
				}

			} else {
				$this->msg = Msg::setMsg('Erro ao excluir ClienteEndereco.', ERROR);
			}

		} else {
			$this->msg = Msg::setMsg('Erro no método excluir.', ERROR);
		}
	}


	public function buscar()
	{
		$this->pageAction = 'Buscar';
		$this->msg = '';

		$this->clienteConteudoValues['data'] = '';
		$this->setLinkClienteConteudo('./app/view/cliente-busca.html');

		$this->showViewBusca();
	}	


	/**
	* <b>show</b>:
	* Configura a motagem das views no template
	* Realiza a montagem e exibição da view completa
	*/
	private function show()
	{	
		$this->verifyLogin();

		$this->setHeader('./app/view/topo.html', $this->userLogin);
		$this->setFooter('./app/view/rodape.html');
		$this->setContent('./app/view/dashboard.html');
	}

	/** 
	* <b>showViewForm</b>:
	* Configura a motagem das view de exibição do formulário de cadastro 
	* Realiza a montagem e exibição da view completa atraves do método show()
	*/
	private function showViewForm()
	{
		$this->setLinkClienteConteudo('./app/view/cliente-form.html');
		$this->setLinkConteudo('./app/view/cliente-view.html');	
		$this->setData();

		$this->show();
	}

	/** 
	* <b>showViewList</b>:
	* Configura a motagem das view de exibição da listagem de clientes
	* Realiza a montagem e exibição da view completa atraves do método show()
	*/
	private function showViewList()
	{
		$this->setLinkConteudo('./app/view/cliente-view.html');	
		$this->setData();

		$this->show();
	}

	/** 
	* <b>showViewBusca</b>:
	* Configura a motagem das view de exibição do formulário de busca de cliente
	* Realiza a montagem e exibição da view completa atraves do método show()
	*/
	private function showViewBusca()
	{
		$this->setLinkConteudo('./app/view/cliente-view.html');	
		$this->setData();

		$this->show();
	}

	/** 
	* <b>getLista</b>:
	* Realiza leitura na tabela clientes e armazena tosos os dados encontrados,
	* Personaliza estilo e carrega os dados na view item-lista.html dinamicamente.
	* @return string $lista String contendo o HTML da lista de clientes.
	*/
	private function getLista()
	{
		$cliente = new Cliente;
		$listaClientes = $cliente->listAll();
		
		$lista = '';
		$backgroundRow = 0;

		foreach ($listaClientes as $cliente) {
			$backgroundRow % 2 == 0 ? $cliente['backgroundRow'] = 'par' : $cliente['backgroundRow'] = 'impar';

			$lista .= SimpleRender::show('./app/view/item-lista.html', $cliente);

			$backgroundRow++;
		}

		return $lista;
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

	/**
	* <b>setLinkClienteConteudo</b>:
	* Substitui o link {clienteConteudo} da view cliente-view.html 
	* pela view que for solicitada dinamicamente.
	* @param string $caminhoArquivoPagina Caminho completo da view 
	* que irá substituir o link clienteConteudo (ex: './app/view/cliente-form.html' )
	*/
	private function setLinkClienteConteudo(string $caminhoArquivoPagina)
	{
		$this->clienteConteudoValues['pageAction'] = $this->pageAction;
		$this->clienteConteudoValues['action'] = $this->action;

		if ($this->pageAction == "Cadastrar"){
			$this->viewClienteConteudo['<li class="lista">'] = '<li>';
			$this->viewClienteConteudo['<li class="buscar">'] = '<li>';

		} elseif ($this->pageAction == "Listar"){
			$this->viewClienteConteudo['<li class="cadastrar">'] = '<li>';
			$this->viewClienteConteudo['<li class="buscar">'] = '<li>';

		} elseif ($this->pageAction == "Buscar"){
			$this->viewClienteConteudo['<li class="cadastrar">'] = '<li>';
			$this->viewClienteConteudo['<li class="lista">'] = '<li>';
		} else {
			$this->viewClienteConteudo['<li class="cadastrar">'] = '<li>';
			$this->viewClienteConteudo['<li class="lista">'] = '<li>';
			$this->viewClienteConteudo['<li class="buscar">'] = '<li>';
		}

		$this->viewClienteConteudo['<li class="' . strtolower($this->pageAction) . '">'] = '<li class="bottomNone">';

		$this->viewClienteConteudo['clienteConteudo'] = SimpleRender::show($caminhoArquivoPagina, $this->clienteConteudoValues);
	}

	/**
	* <b>setLinkConteudo</b>:
	* Substitui o link {conteudo} da view dashboard.html 
	* pela view solicitada dinamicamente.
	* @param string $caminhoArquivoPagina Caminho completo da view 
	* que irá substituir o link {conteudo} (ex: './app/view/cliente-view.html' )
	*/
	private function setLinkConteudo(string $caminhoArquivoPagina)
	{
		$this->viewConteudo = SimpleRender::show($caminhoArquivoPagina, $this->viewClienteConteudo);
	}

	/**
	* <b>exeCadastrar</b>:
	* Executa o cadastro de clientes, endereços e dados auxiliares da tabela cliente_endereco,
	* Recebe via POST os dados do formulário de cadastro.
	* @param array $dadosForm Dados do cliente recebidos via POST 
	* @return int $this->dataForm['cliente_id'] Id do cliente cadastrado em caso de sucesso.
	*/
	private function exeCadastrar(array $dadosForm)
	{
		$this->dataForm = $dadosForm;

		$cliente = new Cliente();
		$this->dataForm['cliente_id'] = $cliente->nextId();

		// Cadastra cliente
		$this->dataCliente = array();
		$resCadCliente = false;
		$resCadCliente = $this->cadastrarClente($cliente, $this->dataCliente);

		$cepExiste = Endereco::findCep($this->dataForm['endereco_cep']);

		$clienteEndereco = new ClienteEndereco;	
		$resCadClienteEndereco = false;	

		$endereco = new Endereco;
		$this->dataEndereco = array();
		$resCadEndereco = false;

		if (is_array($cepExiste) && !empty($cepExiste)){

			// Cadastra dados de cliente_endereco
			$resCadEndereco = true;
			$enderecoId = $cepExiste[0]['endereco_id'];
			$resCadClienteEndereco = $this->cadastrarClienteEndereco($clienteEndereco, $resCadCliente, $enderecoId);

		} else { // Cadastra endereco
			$resCadEndereco = $this->cadastrarEndereco($resCadCliente, $endereco, $this->dataEndereco);

			$cepExiste = Endereco::findCep($this->dataForm['endereco_cep']);			
			$enderecoId = $cepExiste[0]['endereco_id'];

			$resCadClienteEndereco = $this->cadastrarClienteEndereco($clienteEndereco, $resCadCliente, $enderecoId);
		}

		if ($resCadCliente === true && $resCadEndereco === true && $resCadClienteEndereco === true){
			return $this->dataForm['cliente_id'];

		} else {
			$this->dataForm['cliente_id'] = '';
			$this->clienteConteudoValues = $this->dataForm;
		}
		
	}

	/**
	* <b>cadastrarClente</b>:
	* Executa o cadastro de cliente,
	* Recebe um objeto cliente e um array contendo os dados a serem cadastrados.
	* @param object $cliente Objeto da classe Cliente utilizado para manipulação dos dados. 
	* @param array $dadosForm Dados do formulário de cadastro de clientes.
	* @return bool $cliente->save($dataCliente) Retorna true caso de sucesso no cadastramento.
	*/
	private function cadastrarClente(object $cliente, array $dataCliente)
	{
		if (is_numeric($this->dataForm['cliente_id'])){
			$validacao = $this->validarDadosForm($this->dataForm);

			if ($validacao){
				$dataCliente = $this->getObjectData(Cliente::getColumns());
				return $cliente->save($dataCliente);
			}
		}
	}

	/**
	* <b>cadastrarEndereco</b>:
	* Executa o cadastro de endereço.
	* @param bool $resCadCliente Recebe o resultado do cadastramento de um cliente. 
	* @param object $endereco Objeto da classe Endereco utilizado para manipulação dos dados.
	* @return bool $endereco->save($dataEndereco) Retorna true caso de sucesso no cadastramento.
	*/
	private function cadastrarEndereco($resCadCliente, object $endereco)
	{ 
		$this->dataForm['endereco_id'] = $endereco->nextId();  

		if ($resCadCliente === true){
			$dataEndereco = $this->getObjectData(Endereco::getColumns());
			return $endereco->save($dataEndereco);
		}
		
	}

	/**
	* <b>cadastrarClienteEndereco</b>:
	* Executa o cadastro na tabela cliente_endereco,
	* @param bool $resCadCliente Recebe o resultado do cadastramento de um cliente. 
	* @param object $clienteEndereco Objeto da classe ClienteEndereco utilizado para manipulação dos dados.
	* @param int $enderecoId Id do endereço informado no formulário de cadastro.
	* @return bool $clienteEndereco->save($this->dataClienteEndereco) Retorna true caso de sucesso no cadastramento.
	*/
	private function cadastrarClienteEndereco(object $clienteEndereco, $resCadCliente, $enderecoId)
	{
		if ($resCadCliente === true){
			$this->dataClienteEndereco['cliente_id'] = $this->dataForm['cliente_id'];
			$this->dataClienteEndereco['cliente_endereco_numero'] = $this->dataForm['cliente_endereco_numero'];
			$this->dataClienteEndereco['cliente_endereco_complemento'] = $this->dataForm['cliente_endereco_complemento'];
			$this->dataClienteEndereco['endereco_id'] = $enderecoId;

			return $clienteEndereco->save($this->dataClienteEndereco);
		}
	}

	/**
	* <b>exeAtualizar</b>:
	* Executa o atualização de clientes,
	* @param array $dadosForm Dados do cliente recebidos através do formulário de edição
	* @return int $this->dataForm['cliente_id'] Id do cliente que foi atualizado em caso de sucesso.
	*/
	private function exeAtualizar(array $dadosForm)
	{
		$this->dataForm = $dadosForm;

		$cliente = new Cliente();

		$this->dataCliente = array();
		$resAtualizarCliente = false;

		// Atualiza cliente
		$dataCliente = $this->getObjectData(Cliente::getColumns());
		$resAtualizarCliente = $this->atualizarClente($cliente, $this->dataCliente);

		$cepExiste = Endereco::findCep($this->dataForm['endereco_cep']);

		$clienteEndereco = new ClienteEndereco;	
		$resAtualizarClienteEndereco = false;	

		$endereco = new Endereco;
		$this->dataEndereco = array();
		$resCadEndereco = false;

		// Se CEP já cadastrado atualiza clienteEndereco
		if (is_array($cepExiste) && !empty($cepExiste)){
			$resCadEndereco = true;
			$enderecoId = $cepExiste[0]['endereco_id'];
			$this->dataForm['endereco_id'] = $enderecoId;

			$resAtualizarClienteEndereco = $this->atualizarClienteEndereco($clienteEndereco, $resAtualizarCliente, $enderecoId);

		} else { 
			$dataEndereco = $this->getObjectData(Endereco::getColumns());
			$dataEndereco['endereco_id'] = $endereco->nextId();

			// Cadastra endereco
			$resCadEndereco = $this->cadastrarEndereco($resAtualizarCliente, $endereco, $dataEndereco);

			$cepExiste = Endereco::findCep($this->dataForm['endereco_cep']);			
			$enderecoId = $cepExiste[0]['endereco_id'];
			$this->dataForm['endereco_id'] = $enderecoId;

			// Atualiza clienteCndereco
			$dataClienteEndereco = $this->getObjectData(ClienteEndereco::getColumns());
			$resAtualizarClienteEndereco = $clienteEndereco->update($dataClienteEndereco);
		}

		// Se Cliente atualizado, Endereco atualizado e ClienteEndereco atualizado 
		if ($resAtualizarCliente === true && $resCadEndereco === true && $resAtualizarClienteEndereco === true){
			return $this->dataForm['cliente_id'];

		} else {
			$this->dataForm['cliente_id'] = '';
			$this->clienteConteudoValues = $this->dataForm;
		}
		
	}

	/**
	* <b>atualizarClente</b>:
	* Executa o atualização de cliente no banco de dados,
	* Recebe um objeto cliente e um array contendo os dados a serem cadastrados.
	* @param object $cliente Objeto da classe Cliente utilizado para manipulação dos dados. 
	* @param array $dataCliente Dados do cliente recuperados do banco de dados.
	* @return bool $cliente->update($dataCliente) Retorna true caso de sucesso na atualização.
	*/
	private function atualizarClente(object $cliente, array $dataCliente)
	{
		if (is_numeric($this->dataForm['cliente_id'])){
			$validacao = $this->validarDadosForm($this->dataForm);

			if ($validacao){
				$dataCliente = $this->getObjectData(Cliente::getColumns());
				return $cliente->update($dataCliente);
			}
		}
	}

	/**
	* <b>atualizarClienteEndereco</b>:
	* Executa o atualização de clienteEndereco no banco de dados,
	* @param object $clienteEndereco Objeto da classe ClienteEndereco utilizado para manipulação dos dados. 
	* @param bool $resAtualizarCliente Recebe resultado da atualização de cliente. 
	* @param int $enderecoId Id do endereco no banco de dados.
	* @return bool $clienteEndereco->update($this->dataClienteEndereco) Retorna true caso de sucesso na atualização.
	*/
	private function atualizarClienteEndereco(object $clienteEndereco, $resAtualizarCliente, $enderecoId)
	{
		if ($resAtualizarCliente === true){
			$this->dataClienteEndereco['cliente_id'] = $this->dataForm['cliente_id'];
			$this->dataClienteEndereco['cliente_endereco_numero'] = $this->dataForm['cliente_endereco_numero'];
			$this->dataClienteEndereco['cliente_endereco_complemento'] = $this->dataForm['cliente_endereco_complemento'];
			$this->dataClienteEndereco['endereco_id'] = $enderecoId;

			return $clienteEndereco->update($this->dataClienteEndereco);
		}
	}

	/**
	* <b>getObjectData</b>:
	* Monta um array associativo contendo apenas os dados relevantes para cada objeto
	* de acordo com os dados presentes no formulário de cadastros
	* e no array colunas das classes model do sistema.
	* Obs: para funcionar é necessário que os names dos inputs do formulário 
	* sejam iguais aos nomes dos campos da tabela.
	* @param array $colunas Dados do cliente recuperados do banco de dados.
	* @return array $arrObj Retorna um array contendo apenas os dados relevantes pa o objeto.
	*/
	private function getObjectData(array $colunas) : array
	{
		$arrObj = [];

		foreach ($colunas as $colunasValue) {
			foreach ($this->dataForm as $formKey => $formValue) {
				if ($colunasValue === $formKey){
					$arrObj[$colunasValue] = $formValue;
				}
			}
		}

		return $arrObj;
	}

	/**
	* <b>preencherFormEditar</b>:
	* Recupera os dados de um cliente na base de dados,
	* Monta um array associativo e preenche o formulário de edição com os dados recuperados.
	* @param int $clienteId Id do cliente a ser atualizado.
	*/
	private function preencherFormEditar(int $clienteId)
	{
		$clienteConteudoValues = array();

		$this->dataCliente = new Cliente;
		$this->dataCliente = $this->dataCliente->find($clienteId);

		if (is_array($this->dataCliente) && !empty($this->dataCliente)){

			$this->dataClienteEndereco = new ClienteEndereco;
			$this->dataClienteEndereco = $this->dataClienteEndereco->findByClienteId($clienteId);
			$enderecoId = $this->dataClienteEndereco['endereco_id'];

			if (is_array($this->dataClienteEndereco) && !empty($this->dataClienteEndereco)){
				$this->dataEndereco = new Endereco;
				$this->dataEndereco = $this->dataEndereco->find($enderecoId);

				$clienteConteudoValues = array_merge($this->dataCliente, $this->dataEndereco);
				$clienteConteudoValues['cliente_endereco_numero'] = $this->dataClienteEndereco['cliente_endereco_numero'];
				$clienteConteudoValues['cliente_endereco_complemento'] = $this->dataClienteEndereco['cliente_endereco_complemento'];
			}
		}

		if (isset($this->dataCliente['cliente_id'])){
			$this->clienteConteudoValues = $clienteConteudoValues;	

		} else {
			$this->msg = Msg::setMsg('Não é possível editar um usuário que não está cadastrado no sistema.', ERROR);
			$this->clearForm();
		}		
	}

	/**
	* <b>clearForm</b>:
	* Limpa todos os dados do formulário de cadastro.
	*/
	private function clearForm()
	{
		$this->clienteConteudoValues = [
			'cliente_id' => '',
			'cliente_name' => '',
			'cliente_rg' => '',
			'cliente_cpf' => '',
			'cliente_email' => '',
			'cliente_nascimento' => '',
			'cliente_phone1' => '',
			'cliente_phone2' => '',
			'endereco_cep' => '',
			'endereco_logradouro' => '',
			'cliente_endereco_numero' => '',
			'cliente_endereco_complemento' => '',
			'endereco_bairro' => '',
			'endereco_cidade' => '',
			'endereco_estado' => '',
			'endereco_pais' => ''
		];
	}

	/**
	* <b>clearForm</b>:
	* Valida todos os dados do formulário de cadastro.
	* @param array $formData Dados do formulario a serem validados.
	* @return bool $verify Retorna true em caso de validação de todos os dados.
	*/
	private function validarDadosForm(array $formData)
	{
		$verify = false;

		foreach ($formData as $key => $value) {
			if ((($key !== 'cliente_id') && ($key !== 'cliente_endereco_complemento')) && $value === '' ){
				$this->msg = Msg::setMsg('Todos os campos são obrigatórios exceto Id e Complemento.', ERROR);
				return $verify;
			}
		} 

		if (strlen($formData['cliente_name']) > 45 || strlen($formData['cliente_name']) < 10){
			$this->msg = Msg::setMsg('O campo <b>Nome</b> deve ter entre 10 e 45 caracteres.', ERROR);

		} elseif (strlen($formData['cliente_rg']) > 8 || !is_numeric($formData['cliente_rg'])){
			$this->msg = Msg::setMsg('O campo <b>RG</b> deve conter apenas números, sendo no máximo 8 digitos.', ERROR);

		} elseif (strlen($formData['cliente_cpf']) !== 11  || !is_numeric($formData['cliente_cpf'])){
			$this->msg = Msg::setMsg('O campo <b>CPF</b> deve conter apenas números, sendo exatamente 11 digitos.', ERROR);
			
		} elseif (strlen($formData['cliente_email']) > 65 || !filter_var($formData['cliente_email'], FILTER_VALIDATE_EMAIL)){
			$this->msg = Msg::setMsg('Informe um <b>E-mail</b> válido com no máximo 65 caracteres.', ERROR);

		} elseif (strlen($formData['cliente_phone1']) !== 11  || !is_numeric($formData['cliente_phone1'])){
			$this->msg = Msg::setMsg('O campo <b>Telefone 1</b> deve conter apenas números, sendo exatamente 11 digitos.', ERROR);

		} elseif (strlen($formData['cliente_phone2']) !== 11  || !is_numeric($formData['cliente_phone2'])){
			$this->msg = Msg::setMsg('O campo <b>Telefone 2</b> deve conter apenas números, sendo exatamente 11 digitos.', ERROR);

		} elseif (strlen($formData['endereco_cep']) !== 8 || !is_numeric($formData['endereco_cep'])){
			$this->msg = Msg::setMsg('O campo <b>CEP</b> deve conter apenas números, sendo exatamente 8 digitos.', ERROR);

		} elseif (strlen($formData['endereco_logradouro']) > 45){
			$this->msg = Msg::setMsg('O campo <b>Logradouro</b> deve conter no máximo 45 caracteres.', ERROR);

		} elseif (strlen($formData['cliente_endereco_numero']) < 1 || !is_numeric($formData['cliente_endereco_numero'])){
			$this->msg = Msg::setMsg('O campo <b>Número</b> deve conter apenas números.', ERROR);

		} elseif (strlen($formData['cliente_endereco_complemento']) > 45){
			$this->msg = Msg::setMsg('O campo <b>Complemento</b> deve conter no máximo 45 caracteres.', ERROR);

		} elseif (strlen($formData['endereco_bairro']) > 45){
			$this->msg = Msg::setMsg('O campo <b>Bairro</b> deve conter no máximo 45 caracteres.', ERROR);

		} elseif (strlen($formData['endereco_cidade']) > 45){
			$this->msg = Msg::setMsg('O campo <b>Cidade</b> deve conter no máximo 45 caracteres.', ERROR);

		} elseif (strlen($formData['endereco_estado']) > 45){
			$this->msg = Msg::setMsg('O campo <b>Estado</b> deve conter no máximo 45 caracteres.', ERROR);

		} else {
			$verify = true;
		}

		return $verify;
	}

}
