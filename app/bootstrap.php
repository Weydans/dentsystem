<?php

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
	$cliente->pageCadastro();
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

$app->run();
