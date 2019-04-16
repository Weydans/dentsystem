<?php

/**
* </b>bootstrap</b>: 
* Arquivo responsável pelo inicialização do sistema,
* Instancia um objeto da classe Route.php para obter a rota e
* direciona a partir daí para os respectivos Controllers com base na rota.
* 
* @author Weydans Campos de Barros, 01/03/2019.
*/

// DEPENDÊNCIAS NECESSÁRIAS PARA O FUNCIONAMENTO DO SISTEMA
require_once('./core/config.php');
require_once('./app/config.php');
require_once('./core/class/Autoload.php');

Autoload::run();

$app = new Route;

$app->post('/', function(){
	$login = new LoginController;
	$login->exeLogin();
});

$app->get('/', function(){
	$login = new LoginController;
	$login->pageLogin();
});

$app->get('/admin/dashboard', function(){
	$dashboard = new DashboardController;
	$dashboard->showDashboard();
});

$app->get('/admin/cliente', function(){
	$cliente = new ClienteController;
	$cliente->buscar();
});

$app->post('/admin/cliente/cadastrar', function(){
	$cliente = new ClienteController;
	$cliente->cadastrar();
});

$app->get('/admin/cliente/cadastrar', function(){
	$cliente = new ClienteController;
	$cliente->cadastrar();
});

$app->post('/admin/cliente/{id}/atualizar', function($id){
	$cliente = new ClienteController;
	$cliente->editar($id);
});

$app->get('/admin/cliente/{id}/editar', function($id){
	$cliente = new ClienteController;
	$cliente->editar($id);
});

$app->get('/admin/cliente/lista', function(){
	$cliente = new ClienteController;
	$cliente->listar();
});

$app->get('/admin/cliente/busca', function(){
	$cliente = new ClienteController;
	$cliente->buscar();
});

$app->run();
