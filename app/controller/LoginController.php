<?php

/**
 * <b>LoginController</b>:
 *
 * @author Weydans C. Barros, Belo Horizonte, 05/04/2019
 */

class LoginController {

	private $view;
	private $msg;
	private $data;

	public function pageLogin()
	{
		$this->msg = '';
		$this->clearForm();
		$this->show();
	}

	public function exeLogin()
	{
		$dataLogin = filter_input_array(INPUT_POST, FILTER_DEFAULT);

		header('location: http://dentsystem.com/admin/dashboard');
	}

	private function show()
	{
		$this->data['<div class="msg"></div>'] = $this->msg;

		$this->view = Render::show('./app/view/login.html', $this->data);

		echo $this->view;
	}

	private function clearForm()
	{
		$this->data = [
			'email' => '',
			'senha' => ''
		];
	}

}
