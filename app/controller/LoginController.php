<?php

/**
 * <b>LoginController</b>:
 * Classe responsável pelo gerenciamento de acesso ao sistema,
 * valida, mantém e interrompe todo tipo de requisição de acesso ao sistema.
 * @author Weydans C. Barros, Belo Horizonte, 05/04/2019
 */

class LoginController extends Controller
{	
	private $linkHome = HOME;
	private $dataLogin;

	/**
	 * <b>pageLogin</b>
	 * Limpa os campos do formulário e exibe a view de login.
	 */

	public function pageLogin()
	{ 
		$this->msg = '';

		$exe = filter_input(INPUT_GET, 'exe', FILTER_DEFAULT);

		if ($exe === 'resrito'){
			$this->msg = Msg::setMsg('Desculpe! Você não tem permição para acessar esta área.', ERROR);
		}

		$this->clearForm();
		$this->show();
	}

	/**
	 * <b>exeLogin</b>
	 * Valida dados de login informados pelo usuário,
	 * Verifica existência de usúario, e se senha pertence ao mesmo,
	 * Inicia sessão e carrega dados atraves de $_SERVER.
	 */
	public function exeLogin()
	{
		$this->dataLogin = filter_input_array(INPUT_POST, FILTER_DEFAULT);

		$resValidaLogin = $this->validarLogin();

		if ($resValidaLogin){
			$userObj = new User;
			$user = $userObj->findByEmail($this->dataLogin['user_email']);

			if (is_array($user) && !empty($user)){

				// if (password_verify($this->dataLogin['user_pass'], $user['user_pass'])){
				if ($this->dataLogin['user_pass'] == $user['user_pass']){
					
					session_start();					
					$_SESSION['user'] = $user;

					header('location: ' . $this->linkHome . '/admin/dashboard');

				} else {
					$this->msg = Msg::setMsg('Verifique as informações,<br/> usuário e/ou senha incorretos.',ERROR);
				}

			} elseif (is_array($user) && empty($user)){
				$this->msg = Msg::setMsg('Verifique as informações,<br/> usuário e/ou senha incorretos.',ERROR);

			} else {
				$this->msg = $user;
			}
		}

		$this->show();
	}

	/**
	 * <b>show</b>
	 * Responsável pela substituição de links da view e
	 * Renderização da view de login;
	 */
	private function show()
	{
		$this->data['home'] = $this->linkHome;
		$this->data['<div class="msg"></div>'] = $this->msg;
		$this->data['email'] = $this->dataLogin['user_email'];
		$this->data['senha'] = $this->dataLogin['user_pass'];

		$this->view = Render::show('./app/view/login.html', $this->data);

		echo $this->view;
	}

	/**
	 * <b>clearForm</b>:
	 * Limpa os campos do formlário de login.
	 */
	private function clearForm()
	{
		$this->data = [
			'email' => '',
			'senha' => ''
		];
	}

	/**
	 * <b>validarLogin</b>:
	 * Valida os dados informados no formulário de login.
	 * @return bool Retorna true caso dados sejam válidos.
	 */
	private function validarLogin() : bool
	{
		if (!filter_var($this->dataLogin['user_email'], FILTER_VALIDATE_EMAIL)){
			$this->msg = Msg::setMsg('Informe um e-mail válido', ALERT);
			return false;

		} elseif (strlen($this->dataLogin['user_pass']) !== 6){
			$this->msg = Msg::setMsg('Informe uma senha com 6 caracteres.', ALERT);
			return false;
		}

		return true;
	}

	/**
	 * <b>exeLogout</b>:
	 * Realiza logout e redireciona para a view de login.
	 */
	public function exeLogout()
	{	
		session_start();

		if ($_SESSION){
			session_unset();
		}

		header('location: ' . $this->linkHome);
	}
}
