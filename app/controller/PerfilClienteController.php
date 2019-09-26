<?php

/**
 * <b>ClienteController</b>:
 * Classe Responsável por todo o gerenciamento de clientes do sistema,
 * Cadastro, edição, busca, exclusão e gerenciamento das views relacionadas 
 * a gestão de clientes.
 * @author Weydans Campos de Barros, 16/04/2019.
 */
class PerfilClienteController extends Controller
{
	private static $pageTitle = 'Clientes';
	private static $fraseSubtitulo = 'Aqui você gerencia todos os dados de seus clientes';

	private $dataCliente;
	private $dataEndereco;
	private $dataClienteEndereco;
	private $action;
	private $pageAction;
	private $viewConteudo;
	private $viewClienteConteudo = array();
    private $clienteConteudoValues;

    public function __construct()
    {
        $this->pageAction = 'Cliente Perfil';
    }

    public function loadCliente($id)
    {
        $cliente = new Cliente();
        $cliente = $cliente->find($id);

        $clienteEndereco = new ClienteEndereco();
        $clienteEndereco = $clienteEndereco->findByClienteId($id);

        $endereco = new Endereco();
        $endereco = $endereco->find($clienteEndereco['endereco_id']);

        unset($clienteEndereco['cliente_id']);
        unset($clienteEndereco['endereco_id']);
        unset($endereco['endereco_id']);

        $this->dataCliente = array_merge($cliente, $clienteEndereco);
        $this->dataCliente = array_merge($this->dataCliente, $endereco);

        unset($cliente);
        unset($clienteEndereco);
        unset($endereco);

        $template = [
            'header' => file_get_contents('./app/view/topo.html'),
            'main'   => file_get_contents('./app/view/dashboard-2.html'),
            'footer' => file_get_contents('./app/view/rodape.html')
        ];

        Render::view($template, [
            'pageTitle'         => self::$pageTitle,
            'fraseSubtitulo'    => self::$fraseSubtitulo,
            'pageAction'        => $this->pageAction,
            'dataCliente'       => $this->dataCliente
        ]);
    }
}