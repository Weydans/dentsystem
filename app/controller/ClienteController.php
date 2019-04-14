<?php

/**
 * 
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


	public function pageCadastro()
	{
		$this->pageAction = 'Cadastrar';
		$this->action = 'cadastrar';
		$this->msg = '';

		$this->clearForm();

		$this->showViewForm();
	}


	public function cadastrar()
	{
		$dadosForm = filter_input_array(INPUT_POST, FILTER_DEFAULT);

		if (empty($dadosForm)){
			$this->pageCadastro();
			return 0;

		} elseif (empty($dadosForm['cliente_id'])){
			$this->pageAction = 'Cadastrar';

			$cadResult = $this->exeCadastrar($dadosForm);

			if (is_numeric($cadResult)){
				$id = $cadResult;
				header("location: http://dentsystem.com/admin/cliente/{$id}/editar?create=true");
			}

		}

		$this->showViewForm();
	}
	

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
				header("location: http://dentsystem.com/admin/cliente/{$id}/editar?update=true");
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

	public function listar()
	{		
		$this->pageAction = 'Lista';

		$listaClientes = $this->setGetLista();
		$this->clienteConteudoValues['itemLista'] = $listaClientes;
		$this->setLinkClienteConteudo('./app/view/cliente-lista.html');

		$this->showViewList();
	}

	public function buscar()
	{
		$this->pageAction = 'Buscar';
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
		$this->setHeader('./app/view/topo.html');
		$this->setFooter('./app/view/rodape.html');
		$this->setContent('./app/view/dashboard.html');
	}

	private function showViewForm()
	{
		$this->setLinkClienteConteudo('./app/view/cliente-form.html');
		$this->setLinkConteudo('./app/view/cliente-view.html');	
		$this->setData();

		$this->show();
	}

	private function showViewList()
	{
		$this->setLinkConteudo('./app/view/cliente-view.html');	
		$this->setData();

		$this->show();
	}

	private function setGetLista()
	{
		$cliente = new Cliente;
		$listaClientes = $cliente->listAll();
		
		$lista = '';
		$backgroundRow = 0;

		foreach ($listaClientes as $cliente) {
			$backgroundRow % 2 == 0 ? $cliente['backgroundRow'] = 'par' : $cliente['backgroundRow'] = 'impar';

			$lista .= Render::show('./app/view/item-lista.html', $cliente);

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
	* Substitui o link clienteConteudo pela view
	* cliente-form ou client-list da view secundária
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

		$this->viewClienteConteudo['clienteConteudo'] = Render::show($caminhoArquivoPagina, $this->clienteConteudoValues);
	}

	/**
	* <b>setLinkConteudo</b>:
	* Substitui o link conteudo da view secundária
	*/
	private function setLinkConteudo(string $caminhoArquivoPagina)
	{
		$this->viewConteudo = Render::show($caminhoArquivoPagina, $this->viewClienteConteudo);
	}

	private function exeCadastrar(array $dadosForm)
	{
		$this->dataForm = $dadosForm;

		$cliente = new Cliente();
		$this->dataForm['cliente_id'] = $cliente->nextId();

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
			$resCadEndereco = true;
			$enderecoId = $cepExiste[0]['endereco_id'];
			$resCadClienteEndereco = $this->cadastrarClienteEndereco($clienteEndereco, $resCadCliente, $enderecoId);

		} else {
			$resCadEndereco = $this->cadastrarEndereco($resCadCliente, $endereco, $this->dataEndereco);

			$cepExiste = Endereco::findCep($this->dataForm['endereco_cep']);			
			$enderecoId = $cepExiste[0]['endereco_id'];

			$resCadClienteEndereco = $this->cadastrarClienteEndereco($clienteEndereco, $resCadCliente, $enderecoId);
		}

		if ($resCadCliente === true && $resCadEndereco === true && $resCadClienteEndereco === true){
			/*$this->clienteConteudoValues = $this->dataForm;*/
			return $this->dataForm['cliente_id'];
		} else {
			$this->dataForm['cliente_id'] = '';
			$this->clienteConteudoValues = $this->dataForm;
		}
		
	}

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

	private function cadastrarEndereco($resCadCliente, object $endereco, array $dataEndereco)
	{ 
		$this->dataForm['endereco_id'] = $endereco->nextId();  

		if ($resCadCliente === true){
			$dataEndereco = $this->getObjectData(Endereco::getColumns());
			return $endereco->save($dataEndereco);
		}
		
	}

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

	private function exeAtualizar(array $dadosForm)
	{
		$this->dataForm = $dadosForm;

		$cliente = new Cliente();

		$this->dataCliente = array();
		$resAtualizarCliente = false;

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

		// Cadastra endereco e atualiza clienteCndereco
		} else {
			$dataEndereco = $this->getObjectData(Endereco::getColumns());
			$dataEndereco['endereco_id'] = $endereco->nextId();

			$resCadEndereco = $this->cadastrarEndereco($resAtualizarCliente, $endereco, $dataEndereco);

			$cepExiste = Endereco::findCep($this->dataForm['endereco_cep']);			
			$enderecoId = $cepExiste[0]['endereco_id'];
			$this->dataForm['endereco_id'] = $enderecoId;

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

	private function atualizarClienteEndereco(object $clienteEndereco, $resCadCliente, $enderecoId)
	{
		if ($resCadCliente === true){
			$this->dataClienteEndereco['cliente_id'] = $this->dataForm['cliente_id'];
			$this->dataClienteEndereco['cliente_endereco_numero'] = $this->dataForm['cliente_endereco_numero'];
			$this->dataClienteEndereco['cliente_endereco_complemento'] = $this->dataForm['cliente_endereco_complemento'];
			$this->dataClienteEndereco['endereco_id'] = $enderecoId;

			return $clienteEndereco->update($this->dataClienteEndereco);
		}
	}

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
