<?php

// =======================================
// --- CONFIGURAÇÕES DO BANCO DE DADOS --- 
define('DSN', 'mysql:host=localhost;dbname=dentsystem');
define('USER', 'root');
define('PASS', '');
define('OPTIONS', [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8' ]);

// =======================================
// -------- CONSTANTES DO SISTEMA -------- 
define('HOME', '/weydans/projetos/dentsystem');
define('BASE_URL', 'http://' . $_SERVER['SERVER_NAME']);


// ===============================================================================================

<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
caso de erro de max upload (mudar php.ini):
php_value upload_max_filesize 10M
php_value post_max_size 10M
*/

class MoskitContato extends MY_Controller {

	private $separator;

	function __construct()
	{

        parent::__construct();
        $this->load->model("moskit_contato_model", 'moskit');
		$this->load->model("moskit_negociacao_model", 'moskit_negociacao');
		$this->load->model("MoskitHistoricoContatoModel", 'historico');
        date_default_timezone_set('America/Sao_Paulo'); //Fuso horario
    }

    //Função principal
    public function index(){
        try{
            //Se o usuário estiver logado
            if($this->session->userdata('logado')){
				
				if(!isset($_GET['limit'])){
					$_GET['limit'] = 10;
				}

				if(!isset($_GET['page']) || $_GET['page']<1) {
					$_GET['page'] = 1;
				}
					
				if(!isset($_GET['search'])){
					$_GET['search'] = '';
				}
					
                //Dados e arquivos utilizados na montagem na página
                $dados = array(
							'title'		=> 'Rehagro - Contatos do Moskit',
							'contatos'	=> $this->moskit->getAllContatos($_GET['limit'],$_GET['page'],$_GET['search']),
							'total'		=> $this->moskit->getTotalContatos($_GET['search']),
                            'view'		=> 'moskit_contato/moskit_view',
                            'importjs'	=> 'moskit/moskit.js',
                            'link'		=> 'moskit',
                            'extras'	=> [
											'plugins/datatable/datatables.min.js',
											'plugins/datatable/datatables_basic.js',
											'plugins/datatable/select2.min.js',
											'plugins/modal/components_modals.js'
										]
                            ); 

                //Carrega a view
				$this->load->view("principal",$dados);
				
            } else {
                //Caso não esteja logado, redireciona para página de login
                $this->session->set_flashdata('error','Faça login para continuar!');
                redirect('/');
            }
        }catch(Exception $e){
            echo $e->getmessage();
        }
    }
	
	public function download(){
		$this->arquivo(true);
	}


	public function importarLista()
	{
		try{
            //Se o usuário estiver logado
            if ($this->session->userdata('logado')){

				if (!isset($_GET['limit'])) {
					$_GET['limit'] = 10;
				}
					
				if (!isset($_GET['page']) || $_GET['page'] < 1) {
					$_GET['page'] = 1;
				}
					
				if (!isset($_GET['search'])) {
					$_GET['search'] = '';
				}

				// Histórico de todos os contatos
				$dados = array(	'title'		=> 'Rehagro - Importar lista de contatos',
								'view'		=> 'moskit_contato/moskit_importar_lista',
								'importcss'	=> 'datepicker/bootstrap-datepicker.min.css',
								'importjs'	=> 'main/datepicker/js/bootstrap-datepicker.min.js',
								'link'		=> 'moskit',
								'extras'	=> [
												'plugins/datatable/datatables.min.js',
												'plugins/datatable/datatables_basic.js',
												'plugins/datatable/select2.min.js',
												'plugins/modal/components_modals.js'
											]
							); 	

				//Carrega a view
				$this->load->view("principal", $dados);

			} else {
                //Caso não esteja logado, redireciona para página de login
                $this->session->set_flashdata('error','Faça login para continuar!');
                redirect('/');
			}

				$filters = $_GET['search'];
		} catch (Exception $e) {
			echo $e->getmessage();
		}
	}
	

	public function updateStorico()
	{
		try{
            //Se o usuário estiver logado
            if ($this->session->userdata('logado')){

				if (!isset($_GET['limit'])) {
					$_GET['limit'] = 10;
				}
					
				if (!isset($_GET['page']) || $_GET['page'] < 1) {
					$_GET['page'] = 1;
				}
					
				if (!isset($_GET['search'])) {
					$_GET['search'] = '';
				}	

				$arquivo = $_FILES['listaContatos'];
				$ext 	 = substr($arquivo['name'], strrpos($arquivo['name'], '.') + 1);
				$csv 	 = file($arquivo['tmp_name']);
				$header  = null;

				if ($ext !== 'csv') {
					$this->session->set_flashdata('error','Tipo de arquivo não permitido! Informe apenas arquivos com extensão <b>.csv</b>.');
					redirect('moskit_contato/importar_lista');
				}

				$separators = [";", ",", "\t", " "];
				$header 	= $this->validateCsvSeparator($csv[0], $separators);

				$this->validateLayoutFile($header);

				// if (is_array($header) && count($header) > 0) {
				// 	$header[4] = utf8_encode($header[4]);
				// 	$header[5] = utf8_encode($header[5]);
				// }

				$aux  = [];
				foreach ($csv as $value) {
					$aux[] = str_getcsv($value, $this->separator);
				}
				
				$data = $this->changeListToArray($aux, $header);				
				$data = $this->removeRepeatedItems($data);

				unset($csv);
				unset($aux);

				// var_dump($data); 
				// die('stop');

				$response = $this->historico->updateHistoricoByLista($data);

				if ($response) {
					$this->session->set_flashdata('success', 'Histórico atualizado com sucesso!');
					redirect('moskit_contato/importar_lista');

				} else {
					$this->session->set_flashdata('error', 'Erro ao atualizar histórico.');
					redirect('moskit_contato/importar_lista');
				}

			} else {
                //Caso não esteja logado, redireciona para página de login
                $this->session->set_flashdata('error','Faça login para continuar!');
                redirect('/');
			}

				$filters = $_GET['search'];
		} catch (Exception $e) {
			echo $e->getmessage();
		}
	}
	

	//Histórico de contatos
    public function historico($download = false){
        try{
            //Se o usuário estiver logado
            if ($this->session->userdata('logado')){

				if (!isset($_GET['limit'])) {
					$_GET['limit'] = 10;
				}
					
				if (!isset($_GET['page']) || $_GET['page'] < 1) {
					$_GET['page'] = 1;
				}
					
				if (!isset($_GET['search'])) {
					$_GET['search'] = '';
				}

				$filters = $_GET['search'];
				
				// Histórico de todos os contatos
				$dados = array(	'title'		=> 'Rehagro - Histórico de contatos',
								'contatos'	=> $this->historico->getAllContatos($_GET['limit'], $_GET['page'], $filters),
								'total'		=> $this->historico->getTotalContatos($filters),
								'view'		=> 'moskit_contato/moskit_historico',
								'importcss'	=> 'datepicker/bootstrap-datepicker.min.css',
								'importjs'	=> 'main/datepicker/js/bootstrap-datepicker.min.js',
								'link'		=> 'moskit',
								'extras'	=> [
												'plugins/datatable/datatables.min.js',
												'plugins/datatable/datatables_basic.js',
												'plugins/datatable/select2.min.js',
												'plugins/modal/components_modals.js'
											]
							); 

				//Carrega a view
				$this->load->view("principal", $dados);

			} else {
                //Caso não esteja logado, redireciona para página de login
                $this->session->set_flashdata('error','Faça login para continuar!');
                redirect('/');
			}
			
        } catch(Exception $e) {
            echo $e->getmessage();
        }
    }


	//Função lista de contato
    public function arquivo($download = false){
        try{
            //Se o usuário estiver logado
            if ($this->session->userdata('logado')){
				
				if (!isset($_GET['limit'])) {
					$_GET['limit'] = 10;
				}
					
				if (!isset($_GET['page']) || $_GET['page'] < 1) {
					$_GET['page'] = 1;
				}
					
				if (!isset($_GET['search'])) {
					$_GET['search'] = '';
				}					
					
				if (!isset($_GET['responsible'])) {						
					$_GET['responsible'] = array();

				} else if (!is_array($_GET['responsible'])) {
					$_GET['responsible'] = array($_GET['responsible']);
				}
				
				if (!isset($_GET['turma'])) {						
					$_GET['turma'] = array();

				} else if (!is_array($_GET['turma'])) {
					$_GET['turma'] = array($_GET['turma']);
				}
				
				if (!isset($_GET['cidade'])) {						
					$_GET['cidade'] = array();

				} else if (!is_array($_GET['cidade'])) {
					$_GET['cidade'] = array($_GET['cidade']);
				}
				
				if (!isset($_GET['estado'])) {						
					$_GET['estado'] = array();

				} else if (!is_array($_GET['estado'])) {
					$_GET['estado'] = array($_GET['estado']);
				}
				
				if (!isset($_GET['origem'])) {						
					$_GET['origem'] = array();

				} else if (!is_array($_GET['origem'])) {
					$_GET['origem'] = array($_GET['origem']);
				}
				
				if (!isset($_GET['campanha'])) {						
					$_GET['campanha'] = array();

				} else if (!is_array($_GET['campanha'])) {
					$_GET['campanha'] = array($_GET['campanha']);
				}
				
				if (!isset($_GET['status'])) {						
					$_GET['status'] = array();

				} else if (!is_array($_GET['status'])) {
					$_GET['status'] = array($_GET['status']);
				}
				
				if (!isset($_GET['stage'])) {						
					$_GET['stage'] = array();

				} else if (!is_array($_GET['stage'])) {
					$_GET['stage'] = array($_GET['stage']);
				}
				
				if (!isset($_GET['data-inicio'])) {			
					$_GET['data-inicio'] = '';			
					$datainicio = '';

				} else {
					//covert date to format yyyy-mm-dd
					$datainicio = $this->convertDateDB($_GET['data-inicio']);
				}

				if (!isset($_GET['data-final'])) {	
					$_GET['data-final'] = '';					
					$datafinal = '';
					
				} else {
					//covert date to format yyyy-mm-dd
					$datafinal = $this->convertDateDB($_GET['data-final']);
				}
		
				if (!isset($_GET['tempoInicial']) || $_GET['tempoInicial'] == '') {
					$_GET['tempoInicial'] = '';					
					$datafinal = $datafinal;
					
				} else {
					$datafinal = date('Y-m-d', strtotime("-{$_GET['tempoInicial']} day", strtotime(date('Y-m-d'))));
				}

				if (!isset($_GET['tempoFinal']) || $_GET['tempoFinal'] == '') {
					$_GET['tempoFinal'] = '';					
					$datainicio = $datainicio;
					
				} else {
					$datainicio = date('Y-m-d', strtotime("-{$_GET['tempoFinal']} day", strtotime(date('Y-m-d'))));
				}	

				$filters = array(
								'search' 	   => $_GET['search'],
								'responsible'  => $_GET['responsible'],
								'turma' 	   => $_GET['turma'],
								'cidade' 	   => $_GET['cidade'],
								'estado' 	   => $_GET['estado'],
								'origem' 	   => $_GET['origem'],
								'campanha' 	   => $_GET['campanha'],
								'status' 	   => $_GET['status'],
								'stage' 	   => $_GET['stage'],
								'data-inicio'  => $datainicio,
								'data-final'   => $datafinal,
							);
								
				if ($download) {
					$contatos = $this->moskit->getAllContatosExportacao(99999, $_GET['page'], $filters);
					// => Data preparation
					$array_header = array(
						'Identificador',
						'Nome',
						'Telefone',
						'LinkMoskit'
					);

					$array_content = array();
					foreach ($contatos as $contato) {
						$array_content[] = array(
							$contato->id,
							$contato->name,
							preg_replace('/[^0-9]/', '', $contato->phone),
							'https://app.moskitcrm.com/?/deal/' . $contato->deal
						);
					}

					$generate = new FileCSV();
					$generate->setHeader($array_header);
					$generate->setContent($array_content);
					$generate->generateAndDownloadFileCSV();

				} else {
					//Dados e arquivos utilizados na montagem na página
					$dados = array(	'title'		=> 'Rehagro - Lista de Negociações do Moskit para Exportação',
									'contatos'	=> $this->moskit->getAllContatosExportacao($_GET['limit'],$_GET['page'],$filters),
									'total'		=> $this->moskit->getAllContatosExportacao($_GET['limit'],$_GET['page'],$filters,true),
									'view'		=> 'moskit_contato/moskit_arquivo',
									'importcss'	=> 'datepicker/bootstrap-datepicker.min.css',
									'importjs'	=> 'main/datepicker/js/bootstrap-datepicker.min.js',
									'link'		=> 'moskit',
									'extras'	=> [
													'plugins/datatable/datatables.min.js',
													'plugins/datatable/datatables_basic.js',
													'plugins/datatable/select2.min.js',
													'plugins/modal/components_modals.js',
													'moskit/moskitArquivo.js'
												]
									); 
	
					//Carrega a view
					$this->load->view("principal", $dados);
				}

            } else {
                //Caso não esteja logado, redireciona para página de login
                $this->session->set_flashdata('error','Faça login para continuar!');
                redirect('/');
			}
			
        } catch(Exception $e) {
            echo $e->getmessage();
        }
    }
	
	//Função autocomplete do filtro na lista
    public function autocomplete(){
        try{
            //Se o usuário estiver logado
            if($this->session->userdata('logado')){
				
				if(!isset($_GET['term']))
					$_GET['term'] = '';
				if(!isset($_GET['table']))
					$_GET['table'] = 'contact';
				
				$json = array();
				if($_GET['table'] == 'deal')
					$results = $this->moskit->getNegociacaoField($_GET['field'],$_GET['term']);
				else
					$results = $this->moskit->getContatoField($_GET['field'],$_GET['term']);
				if($results){
					$json['results'] = $results;
				}
				
				header('Content-Type: application/json');
				echo json_encode($json);
				exit;
            }
            else{
                //Caso não esteja logado, redireciona para página de login
                $this->session->set_flashdata('error','Faça login para continuar!');
                redirect('/');
            }
        }catch(Exception $e){
            echo $e->getmessage();
        }
    }
	
	//Função atualizar
    public function atualizar(){
		
		$this->load->library('PHPRequests');

        try{
            //Se o usuário estiver logado
            if($this->session->userdata('logado')){
				
				try{					
					$total_registros = $this->updateContacts();
					
					//finalizou tudo
					if($total_registros==-1){
						$this->session->set_flashdata('sucesso','Os contatos foram atualizados com sucesso!');								
						redirect('moskit_contato/?sucesso=1');
						exit;
					}
					
					$porcentagem = number_format(((int)$_GET['inicio']/(int)$total_registros)*100,2);
					if((float)$porcentagem>100)
						$porcentagem = 100;
					
					//Dados e arquivos utilizados na montagem na página
					$dados = array('title'=>'Rehagro - Atualizando Contatos do Moskit',
								'view'=>'moskit_contato/moskit_update',
								'link'=>'moskit',
								'inicio'=>$_GET['inicio'],
								'porcentagem'=>$porcentagem,
								'extras'=> [
									'plugins/datatable/datatables.min.js',
									'plugins/datatable/datatables_basic.js',
									'plugins/datatable/select2.min.js',
									'plugins/modal/components_modals.js'
									]
								); 
			
					//Carrega a view
					$this->load->view("principal",$dados);
				}
				catch(Exception $e){
					$this->session->set_flashdata('error',$e->getMessage());
					redirect('moskit_contato/?error='.$e->getMessage());
					exit;
				}
            }
            else{
                //Caso não esteja logado, redireciona para página de login
                $this->session->set_flashdata('error','Faça login para continuar!');
                redirect('/');
            }
        }catch(Exception $e){
            echo $e->getmessage();
        }
    }
	
	//Função detalhes
    public function detalhes($id){
        try{
            //Se o usuário estiver logado
            if($this->session->userdata('logado')){
				
                //Dados e arquivos utilizados na montagem na página
                $dados = array('title'=>'Rehagro - Detalhes do Contato do Moskit',
							'contato'=>$this->moskit->getByIdContato($id),
							'emails'=>$this->moskit->getByIdContatoEmails($id),
							'phones'=>$this->moskit->getByIdContatoPhones($id),
							'negociacoes'=>$this->moskit_negociacao->getAllNegociacoesByContato(999,1,$id),
                            'view'=>'moskit_contato/moskit_details_view',
                            'importjs'=>'moskit/moskit.js',
                            'link'=>'moskit',
                            'extras'=> [
                                'plugins/datatable/datatables.min.js',
                                'plugins/datatable/datatables_basic.js',
                                'plugins/datatable/select2.min.js',
                                'plugins/modal/components_modals.js'
                                ]
                            ); 

                //Carrega a view
                $this->load->view("principal",$dados);
            }
            else{
                //Caso não esteja logado, redireciona para página de login
                $this->session->set_flashdata('error','Faça login para continuar!');
                redirect('/');
            }
        }catch(Exception $e){
            echo $e->getmessage();
        }
    }
	
	private function updateContacts(){
		//parametros para acesso a API
		$options = array(
			'apikey'=>'8ab865d3-4a74-471d-b3fb-200a6f98d26f'
		);
		//Chamada realizada
		$url= 'contacts';
		if(!isset($_GET['inicio']))
			$_GET['inicio'] = 0;
		$inicio = $_GET['inicio'];
		$limit = 25;
		
		$response = Requests::get('https://api.moskitcrm.com/v1/'.$url.'?start='.$inicio.'&limit='.$limit.'&sort=id&order=asc',$options);
		$response = json_decode($response->body);
		
		/*
		echo '<pre>';
		print_r($response);
		echo '</pre>';
		exit;
		*/
		
		if((int)$response->metadata->pagination->total<$_GET['inicio'])
			return -1;
		
		$_GET['inicio'] += $limit;		
		
		if($response->results){
			foreach($response->results as $result){
				//Monta o objeto que será salvo
				$contato = array(
					'id' => $result->id,
					'name' =>  $result->name,
					'idCreatedBy' =>  $result->createdBy->id,
					'createdBy' =>  $result->createdBy->name,
					'idResponsible' =>  $result->responsible->id,
					'responsible' =>  $result->responsible->name,
					'dateCreated' =>  $this->getData($result->dateCreated),
				);
				
				if($result->customFieldValues)
					foreach($result->customFieldValues as $custom){
						switch($custom->customField->name){
							case 'CIDADE':
								$contato['cidade'] = $custom->value;
								break;
							case 'ESTADO':
								$contato['estado'] = $custom->value;
								break;
						}
					}
	
				//Salva o objeto na base de dados
				$this->moskit->replaceContato('moskit_contato', $contato, $result->id);
				
				//salvar emails
				if($result->emails)
					foreach($result->emails as $email){
						$email_array = array(
							'id' => $email->id,
							'id_contato' => $result->id,
							'email' => $email->address,
							'principal' => $result->primaryEmail->id==$email->id?1:0
						);
						$this->moskit->replaceContato('moskit_contato_email', $email_array, $email->id);
					}
					
				//salvar phones
				if($result->phones)
					foreach($result->phones as $phone){
						$phone_array = array(
							'id' => $phone->id,
							'id_contato' => $result->id,
							'phone' => $phone->number,
							'principal' => $result->primaryPhone->id==$phone->id?1:0
						);
						$this->moskit->replaceContato('moskit_contato_phone', $phone_array, $phone->id);
					}
			}
		}
		
		return $response->metadata->pagination->total;
	}
	
	//Transforma a data de milisegundos para uma data legivel
    private function getData($milisegundos){
        $segundos = $milisegundos/ 1000;
        return date("Y-m-d H:i:s", $segundos);
    }
	
	private function convertDateDB($date) {
		if($date)
		   return substr($date, 6, 4) . '-' . substr($date, 3, 2) . '-' . substr($date, 0, 2);
		else
			return '';
	}


	/** 
	 * removeRepeatedItems()
	 * 
	 * Verifica existência de itens com links repetidos e remove o repetido.
	 * @param 	array $data Dados (content) do arquivo
	 * @return	array $data Dados sem as repetições
	 */
	private function removeRepeatedItems(array $data) : array
	{
		$arrNegociacao = [];
		foreach ($data as $key => $value) {
			if (!in_array($value['LinkMoskit'], $arrNegociacao)) {
				$arrNegociacao[] = $value['LinkMoskit'];
			} else {
				unset($data[$key]);
			}
		}

		return $data;
	}


	/**
	 * changeListToArray()
	 * 
	 * Obtem os dados do arquivo csv e tranforma-os em um array associativo
	 * @param  array $list	 lista de dados a serem atualizados
	 * @param  array $header Cabeçalho do arquivo csv
	 * @return array $data	 Array associativo com dados a serem atualizados
	 */
	private function changeListToArray(array $list, array $header) : array
	{		
		$data = [];
		foreach ($list as $key => $value) {
			$temp = [];
			if ($key !== 0 && $key < count($list)) {
				for ($i = 0; $i < count($value); $i++) {
					$temp[$header[$i]] = $value[$i];
				}
				$data[] = $temp;
			}
		}

		return $data;
	}


	/**
	 * validateCsvSeparator()
	 * 
	 * Valida os separadores aceitos para os arquivos csv informados 
	 * @param string $csvHeader  Cabeçalho do arquivo
	 * @param array  $separators Separadores aceitos
	 * @return 		 $header     Array com o cabeçalho validado
	 * 
	 * Obs: Caso sejam aceitos espaço e tabulação, a tabulação deve vir primeiro no array de separadores
	 */
	private function validateCsvSeparator(string $csvHeader, array $separators) : array
	{
		foreach ($separators as $value) {
			if (strrpos($csvHeader, $value, 0) > 0 && $value != ' ') {
				$this->separator = $value;				
				break;	

			} elseif ($value == ' ') {
				$this->separator = $value;
				break;
			}
		}

		$header = str_getcsv($csvHeader, $this->separator);
	
		if (is_array($header) && count($header) !== 7) {
			$this->session->set_flashdata(
				'error', 
				'Arquivo com separador de csv não suportado. <br>Separadores aceitos:<br> 
				<b> 
				";" 	(ponto e vírgula),	<br>
				","  	(vírgula),			<br>  
				" "  	(espaço),			<br>  
				"    "  (tabulação)
				</b>.'
			);
			redirect('moskit_contato/importar_lista');
		}

		return $header;
	}


	/**
	 * validateLayoutFile()
	 * 
	 * Valida layout do arquivo csv
	 * @param array $header Dados do header do arquivo
	 */
	private function validateLayoutFile(array $header)
	{
		// foreach ($header as $key => $value) {
		// 	var_dump(mb_detect_encoding($value), $value);
		// }
		// die;
		if (
			$header[0] != 'Identificador'
			||
			$header[1] != 'Nome'
			||
			$header[2] != 'Telefone 1'
			||
			$header[3] != 'LinkMoskit'
			||
			$header[4] != 'Qualificação Telefone 1'
			||
			$header[5] != 'Data ultima ligação Telefone 1'
			||
			$header[6] != 'Tentantivas Telefone 1'
		) {
			$this->session->set_flashdata(
				'error', 
				'Arquivo fora de padrão!<br><br>
				O arquivo de conter exatamente e em ordem os seguintes campos:<br>
				<b>Identificador, Nome, Telefone 1, LinkMoskit, Qualificação Telefone 1, Data ultima ligação Telefone 1, Tentantivas Telefone 1</b>'
			);

			redirect('moskit_contato/importar_lista');
		}
	}
}

class FileCSV
{
 
    private $header = array();
    private $array_content = array();
 
    public function setHeader($array_header)
    {
        $this->header = $array_header;
    }
 
    public function setContent($array_content)
    {
        $this->array_content = $array_content;
    }
 
    /**
     * Generate the file and Download
     */
    public function generateAndDownloadFileCSV()
    {
        $header_file = $this->getHeader();
        $content_file = $this->getContent();
 
        header('Cache-Control: max-age=0');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="lista_negociacoes_moskit.csv";');
        $output = fopen('php://output', 'w');
        if (!empty($header_file)) { // => Optional header
            fputcsv($output, $header_file, ';');
        }
        foreach ($content_file as $value) {
            fputcsv($output, $value, ';');
        }
    }
 
    /**
     * Gets the file header
     */
    private function getHeader()
    {
        return $this->header;
    }
 
    /**
     * Gets the content from array (usualy the database rows)
     */
    private function getContent()
    {
        $array_retorno = array();
        // => Checks whether data exists to be add on file
        if (count($this->array_content) > 0) {
            // => Scroll through the array
            foreach ($this->array_content as $value) {
                // => Contents definitions from database or other place
                $array_temp = array();
                foreach ($value as $col) {
                    // => Column "Column 1"
                    $array_temp[] = $col;
                }
                $array_retorno[] = $array_temp;
                unset($array_temp);
            }
        }
        return $array_retorno;
    }
 
}

// =================================================================================================================

<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MoskitHistoricoContatoModel extends MY_Model 
{
    public function getTotalContatos($search = '')
    {
        if (!empty($search)) {
            $num = $this->db->select('count(*) as num')
                            ->from('historico_discador')
                            ->join('moskit_negociacao','moskit_negociacao.id = historico_discador.id_negociacao', 'left')
                            ->join('moskit_contato','moskit_contato.id = moskit_negociacao.id_contato', 'left')
                            ->like('moskit_contato.name', $search, 'match')
                            ->or_like('historico_discador.id', $search, 'match')
                            ->or_like('historico_discador.qualificacao_ligacao', $search, 'match')
                            ->or_like('historico_discador.numero_tentativas', $search, 'match')
                            ->or_like('historico_discador.data_ultima_ligacao', $this->getDateSearch($search), 'match')
                            ->get()
                            ->result();

            return $num[0]->num;
        }

        $num = $this->db->select('count(*) as num')
                        ->from('historico_discador')
                        ->join('moskit_negociacao','moskit_negociacao.id = historico_discador.id_negociacao', 'left')
                        ->join('moskit_contato','moskit_contato.id = moskit_negociacao.id_contato', 'left')
                        ->get()
                        ->result();

        return $num[0]->num;
    }

    public function getAllContatos($limit = 10, $page = 1, $search = '')
    {
        $page = ($page - 1) * $limit;
        
        $resultSet = $this->db->select('
                                        historico_discador.id_negociacao,
                                        moskit_contato.name as nome,
                                        historico_discador.qualificacao_ligacao,
                                        historico_discador.numero_tentativas,
                                        historico_discador.data_ultima_ligacao
                                ')
                                ->from('historico_discador')
                                ->join('moskit_negociacao','moskit_negociacao.id = historico_discador.id_negociacao', 'left')
                                ->join('moskit_contato','moskit_contato.id = moskit_negociacao.id_contato', 'left')
                                ->like('moskit_contato.name', $search, 'match')
                                ->or_like('historico_discador.id_negociacao', $search, 'match')
                                ->or_like('historico_discador.qualificacao_ligacao', $search, 'match')
                                ->or_like('historico_discador.numero_tentativas', $search, 'match')
                                ->or_like('historico_discador.data_ultima_ligacao', $this->getDateSearch($search), 'match')
                                ->limit($limit, $page)
                                ->get()
                                ->result();

        return $resultSet;
    }

    public function updateHistoricoByLista(array $data) : bool
    {
        $this->db->trans_start();
        foreach ($data as $key => $value) {
            if (!empty($value['Identificador'])) {
                
                $idNegociacao = substr($value['LinkMoskit'], strlen('https://app.moskitcrm.com/?/deal/'), (strlen($value['LinkMoskit']) - 1));

                $res = $this->db->select()->from('historico_discador')->where('id_negociacao', $idNegociacao)->get()->result_array();

                // Atualiza dados caso já exista o id da negociação
                if (isset($res[0]) && is_array($res[0]) && count($res[0]) > 0) {
                    $register = $res[0];
                    $data = array( 
                        'qualificacao_ligacao' => $value['Qualificação Telefone 1'],
                        'numero_tentativas'    => $register['numero_tentativas'] + $value['Tentantivas Telefone 1'], 
                        'data_ultima_ligacao'  => $this->dateFromListToDB($value['Data ultima ligação Telefone 1'])
                    );
                    $this->db->where('id_negociacao', $idNegociacao);
                    $this->db->update('historico_discador', $data);

                // Insere dados caso não exista o id da negociação
                } else {
                    $data = array( 
                        'id_negociacao'        => $idNegociacao,
                        'qualificacao_ligacao' => $value['Qualificação Telefone 1'],
                        'numero_tentativas'    => $value['Tentantivas Telefone 1'], 
                        'data_ultima_ligacao'  => $this->dateFromListToDB($value['Data ultima ligação Telefone 1'])
                    );
                    $this->db->insert('historico_discador', $data);
                }

                // Grava "log" dos contatos do discador
                $data = array( 
                    'id_negociacao'        => $idNegociacao,
                    'qualificacao_ligacao' => $value['Qualificação Telefone 1'],
                    'numero_tentativas'    => $value['Tentantivas Telefone 1'], 
                    'data_ultima_ligacao'  => $this->dateFromListToDB($value['Data ultima ligação Telefone 1'])
                );
                $this->db->insert('log_historico_discador', $data);
            }
        }
        $this->db->trans_complete();

        // Se banco atualizado com sucesso
        // if ($this->db->trans_status()) {
        //     $update = $this->updateMoskitByList();

        //     // Se moskit atualizado com sucesso
        //     if (!$update['error']) {
        //         return true;

        //     } else {
        //         return $update['message'];
        //     }
        // }

        return $this->db->trans_status();
    }

    private function dateFromListToDB(string $date) 
    {
        $aux   = explode(' ', $date);
        $date  = explode('/', $aux[0]);
        $hours = explode(':', $aux[1]);

        list($dia, $mes, $ano) = $date;
        list($hour, $inches)   = $hours;

        return $ano . '-' . $mes . '-' . $dia . ' ' . $hour . ':' . $inches . ':00';
    }

    private function getDateSearch($search)
    {
        if (substr_count($search, '/') == 2) {
            $data = explode("/", $search);
            list($dia, $mes, $ano) = $data;
            $search = $ano . '-' . $mes . '-' . $dia;

        } elseif (substr_count($search, '/') == 1) {
            $data = explode("/", $search);
            list($first, $second) = $data;
            $search = $second . '-' . $first;
        }

        return $search;
    }

    private function updateMoskitByList() : bool
    {
        $response = [
            'error' => '',
            'message' => ''
        ];



        return $response;
    }
}