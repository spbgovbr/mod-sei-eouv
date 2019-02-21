<?
/**
 * CONTROLADORIA GERAL DA UNIÃO - CGU
 *
 * 09/10/2015 - criado por Rafael Leandro
 *
 */
error_reporting(E_ALL); ini_set('display_errors', '1');

require_once dirname(__FILE__) . '/../../../../SEI.php';
require_once dirname(__FILE__) . '/../../../../../../infra/infra_php/nusoap/lib/nusoap.php';

class MdCguEouvAgendamentoRN extends InfraRN
{

    public function __construct()
    {
        parent::__construct();
        //ini_set('memory_limit', '1024M');
    }

    /**
     * @param $objWS
     * @param $usuarioWebService
     * @param $senhaUsuarioWebService
     * @param $ultimaDataExecucaoFormatoEouv
     * @param $dataAtualFormatoEOuv
     * @return mixed
     * @throws Exception
     */
    public function executarServicoConsultaManifestacoes($objWS, $usuarioWebService, $senhaUsuarioWebService, $ultimaDataExecucaoFormatoEouv, $dataAtualFormatoEOuv, $numprotocolo = null, $numIdRelatorio = null)
    {

        $arrParametros = array(
            'login' => $usuarioWebService,
            'senha' => $senhaUsuarioWebService,
            'dataPrazoRespostaInicio' => '',
            'dataPrazoRespostaFim' => '',
            'situacaoManifestacao' => 1
        );

        if (!is_null($ultimaDataExecucaoFormatoEouv)){
            $arrParametros['dataCadastroInicio'] = $ultimaDataExecucaoFormatoEouv;
        }

        if (!is_null($dataAtualFormatoEOuv)){
            $arrParametros['dataCadastroFim'] = $dataAtualFormatoEOuv;
        }

        if (!is_null($numprotocolo)){
            $arrParametros['numProtocolo'] = $numprotocolo;
        }

        $retornoWs = $objWS->call('GetListaManifestacaoOuvidoria', $arrParametros, '', '', false, true);

        $soapError = $objWS->getError();

        if (!empty($soapError)) {
            //print_r('<br/><br/>Erro Serviço');
            //print_r($soapError);
            $errorMessage = 'SOAP method invocation failed: ' . $soapError;
            throw new Exception($errorMessage);
        }
        if ($objWS->fault) {
            $fault = "{$objWS->faultcode}: {$objWS->faultdetail} ";
            throw new Exception($fault
            );
        }

        //Verifica se ocorreram erros;
        if (empty($retornoWs['GetListaManifestacaoOuvidoriaResult'])) {
            throw new Exception("Erro ao executar serviço: " . print_r($retornoWs, true));
        }

        $intIdCodigoErroExecucao = $retornoWs['GetListaManifestacaoOuvidoriaResult']['CodigoErro'];

        if ($intIdCodigoErroExecucao > 0) {

            //Faz tratamento diferenciado para consulta por Protocolo específico
            if(!is_null($numprotocolo)){
                //Se for erro de permissão para um protocolo específico segue o fluxo, caso contrário para a execução
                if ((strpos($retornoWs['GetListaManifestacaoOuvidoriaResult']['DescricaoErro'], 'Usuário Sem Acesso a essa Manifestação') !== false) == false) {
                    throw new Exception($retornoWs['GetListaManifestacaoOuvidoriaResult']['DescricaoErro']);
                }
                else{
                    $this->gravarLogLinha($this->formatarProcesso($numprotocolo),$numIdRelatorio,$retornoWs['GetListaManifestacaoOuvidoriaResult']['DescricaoErro'],'N');
                    $retornoWs = null;
                }
            }

            else {

                $objEouvRelatorioImportacaoRN2 = new MdCguEouvRelatorioImportacaoRN();

                //Grava a execução com sucesso se tiver corrido tudo bem
                $objEouvRelatorioImportacaoDTO4 = new MdCguEouvRelatorioImportacaoDTO();

                $objEouvRelatorioImportacaoDTO4->setNumIdRelatorioImportacao($numIdRelatorio);
                $objEouvRelatorioImportacaoDTO4->setStrSinSucesso('N');
                $objEouvRelatorioImportacaoDTO4->setStrDeLogProcessamento($retornoWs['GetListaManifestacaoOuvidoriaResult']['DescricaoErro']);
                $objEouvRelatorioImportacaoRN2->alterar($objEouvRelatorioImportacaoDTO4);
            }
        }


        return $retornoWs;
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    // GZIP DECODE
    function gzdecode($data)
    {
        return gzinflate(substr($data, 10, -8));
    }

    public function verificaRetornoWS($retornoWsLista)
    {
        /*
        função criada para tratar o retorno de dados do WS, pois quando existe apenas um unico resultado retorna um objeto e
        quando tem mais de um resultado retorna um array ocasionando falhas na exibição dos dados.
        */
        if (isset($retornoWsLista) and key_exists(0, $retornoWsLista)) {
            $resultado = $retornoWsLista;
        } else {
            $resultado = array ( $retornoWsLista );
        }
        return $resultado;
    }

    public function retornaDataFormatoEouv($strData)
    {
        $dataFormatada = substr($strData, 6, 4) . "-" . substr($strData, 3, 2) . "-" . substr($strData, 0, 2) . " " . substr($strData, 11, 8);
        return $dataFormatada;
    }

    public function gravarLogImportacao($ultimaDataExecucao, $dataAtual){

        try {
            $objEouvRelatorioImportacaoDTO = new MdCguEouvRelatorioImportacaoDTO();

            $objEouvRelatorioImportacaoDTO->retNumIdRelatorioImportacao();
            $objEouvRelatorioImportacaoDTO->setNumIdRelatorioImportacao(null);
            $objEouvRelatorioImportacaoDTO->setDthDthImportacao(InfraData::getStrDataHoraAtual());
            $objEouvRelatorioImportacaoDTO->setDthDthPeriodoInicial($ultimaDataExecucao);
            $objEouvRelatorioImportacaoDTO->setDthDthPeriodoFinal($dataAtual);
            $objEouvRelatorioImportacaoDTO->setStrDeLogProcessamento('Passo 1 - Iniciando processamento.');
            $objEouvRelatorioImportacaoDTO->setStrSinSucesso('N');

            $objEouvRelatorioImportacaoRN = new MdCguEouvRelatorioImportacaoRN();
            $objEouvRelatorioImportacaoRN = $objEouvRelatorioImportacaoRN->cadastrar($objEouvRelatorioImportacaoDTO);

            return $objEouvRelatorioImportacaoDTO;

        }catch (Exception $e) {
            PaginaInfra::getInstance()->processarExcecao($e);
            die;
        }

    }

    public function gravarLogLinha($numProtocolo, $idRelatorioImportacao, $mensagem, $sinSucesso)
    {

        $objEouvRelatorioImportacaoDetalheDTO = new MdCguEouvRelatorioImportacaoDetalheDTO();
        $objEouvRelatorioImportacaoDetalheDTO->retStrProtocoloFormatado();
        $objEouvRelatorioImportacaoDetalheDTO->setNumIdRelatorioImportacao($idRelatorioImportacao);
        $objEouvRelatorioImportacaoDetalheDTO->setStrProtocoloFormatado($numProtocolo);


        $objEouvRelatorioImportacaoDetalheRN = new MdCguEouvRelatorioImportacaoDetalheRN();
        $objExisteDetalheDTO = $objEouvRelatorioImportacaoDetalheRN->consultar($objEouvRelatorioImportacaoDetalheDTO);

        $objEouvRelatorioImportacaoDetalheDTO->setStrSinSucesso($sinSucesso);
        $objEouvRelatorioImportacaoDetalheDTO->setStrDescricaoLog(substr($mensagem,0,254));

        $objEouvRelatorioImportacaoDetalheDTO->setDthDthImportacao(InfraData::getStrDataHoraAtual());

        if($objExisteDetalheDTO==null) {
            $objEouvRelatorioImportacaoDetalheRN->cadastrar($objEouvRelatorioImportacaoDetalheDTO);
        }
        else{
            $objEouvRelatorioImportacaoDetalheRN->alterar($objEouvRelatorioImportacaoDetalheDTO);
        }

    }

    public function obterManifestacoesComErro($objWS, $usuarioWebService, $senhaUsuarioWebService, $ultimaDataExecucaoFormatoEouv, $dataAtualFormatoEOuv, $numIdRelatorio)
    {
        $objEouvRelatorioImportacaoDetalheDTO = new MdCguEouvRelatorioImportacaoDetalheDTO();
        $objEouvRelatorioImportacaoDetalheDTO->retStrProtocoloFormatado();
        $objEouvRelatorioImportacaoDetalheDTO->setStrSinSucesso('N');

        $objEouvRelatorioImportacaoDetalheRN = new MdCguEouvRelatorioImportacaoDetalheRN();
        $objListaErros = $objEouvRelatorioImportacaoDetalheRN->listar($objEouvRelatorioImportacaoDetalheDTO);

        $arrResult = array();
        $arrProtocolos = array();

        foreach($objListaErros as $erro){

            $numProtocolo = preg_replace("/[^0-9]/", "", $erro->getStrProtocoloFormatado());

            //Se já estiver na lista não faz novamente para determinado protocolo
            if (!in_array($numProtocolo, $arrProtocolos)){

                //Adiciona no array de Protocolos
                array_push($arrProtocolos, $numProtocolo);

                $retornoWsErro = $this->executarServicoConsultaManifestacoes($objWS, $usuarioWebService, $senhaUsuarioWebService, null, $dataAtualFormatoEOuv, $numProtocolo, $numIdRelatorio);

                if (!is_null($retornoWsErro)){
                    $arrRetornoWs = $this->verificaRetornoWS($retornoWsErro['GetListaManifestacaoOuvidoriaResult']['ManifestacoesOuvidoria']['ManifestacaoOuvidoria']);
                    $arrResult = array_merge($arrResult, $arrRetornoWs);
                }
            }
        }

        return $arrResult;
    }

    public function validarEnderecoWebService($urlWebService){

        if (!@file_get_contents($urlWebService)) {
            throw new InfraException('Arquivo WSDL ' . $urlWebService . ' não encontrado.');
        }

    }

    public function gerarObjWebService($urlWebService){

        $proxyhost = '';
        $proxyport = '';
        $proxyusername = '';
        $proxypassword = '';

        try {

            $objWSTemp = new nusoap_client($urlWebService.'?wsdl', true,
                $proxyhost, $proxyport, $proxyusername, $proxypassword);
            $objWSTemp->soap_defencoding = 'UTF-8';
            $objWSTemp->response_timeout = 240;

        } catch (Exception $e) {
            throw new InfraException('Erro acessando serviço.', $e);
        }

        return $objWSTemp;
    }

    private function obterServico($SiglaSistema, $IdentificacaoServico){

        $objUsuarioDTO = new UsuarioDTO();
        $objUsuarioDTO->retNumIdUsuario();
        $objUsuarioDTO->setStrSigla($SiglaSistema);
        $objUsuarioDTO->setStrStaTipo(UsuarioRN::$TU_SISTEMA);

        $objUsuarioRN = new UsuarioRN();
        $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);

        if ($objUsuarioDTO==null){
            throw new InfraException('Sistema ['.$SiglaSistema.'] não encontrado.');
        }

        $objServicoDTO = new ServicoDTO();
        $objServicoDTO->retNumIdServico();
        $objServicoDTO->retStrIdentificacao();
        $objServicoDTO->retStrSiglaUsuario();
        $objServicoDTO->retNumIdUsuario();
        $objServicoDTO->retStrServidor();
        $objServicoDTO->retStrSinLinkExterno();
        $objServicoDTO->retNumIdContatoUsuario();
        $objServicoDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
        $objServicoDTO->setStrIdentificacao($IdentificacaoServico);

        $objServicoRN = new ServicoRN();
        $objServicoDTO = $objServicoRN->consultar($objServicoDTO);

        if ($objServicoDTO==null){
            throw new InfraException('Serviço ['.$IdentificacaoServico.'] do sistema ['.$SiglaSistema.'] não encontrado.');
        }

        return $objServicoDTO;
    }

    private function obterUnidade($IdUnidade, $SiglaUnidade){

        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->retNumIdUnidade();
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->retStrDescricao();

        if($IdUnidade!=null) {
            $objUnidadeDTO->setNumIdUnidade($IdUnidade);
        }
        if($SiglaUnidade!=null){
            $objUnidadeDTO->setStrSigla($SiglaUnidade);
        }

        $objUnidadeRN = new UnidadeRN();
        $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

        if ($objUnidadeDTO==null){
            throw new InfraException('Unidade ['.$IdUnidade.'] não encontrada.');
        }

        return $objUnidadeDTO;
    }

    function array_to_object($array) {
        $obj = new stdClass;
        foreach($array as $k => $v) {
            if(strlen($k)) {
                if(is_array($v)) {
                    $obj->{$k} = $this->array_to_object($v); //RECURSION
                } else {
                    $obj->{$k} = $v;
                }
            }
        }
        return $obj;
    }


    public function executarImportacaoManifestacaoEOuv()
    {

        //try{
        InfraDebug::getInstance()->setBolLigado(true);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(false);
        InfraDebug::getInstance()->limpar();

        LogSEI::getInstance()->gravar('Rotina de Importação de Manifestações do E-Ouv', InfraLog::$INFORMACAO);

        global $objEouvRelatorioImportacaoDTO,
               $objEouvRelatorioImportacaoRN,
               $objInfraParametro,
               $urlWebServiceEOuv,
               $urlWebServiceAnexosEOuv,
               $idTipoDocumentoAnexoPadrao,
               $idTipoDocumentoAnexoDadosManifestacao,
               $idUnidadeOuvidoria,
               $idUsuarioSei,
               $dataAtual,
               $objUltimaExecucao,
               $objWSAnexo,
               $ocorreuErroEmProtocolo,
               $idRelatorioImportacao,
               $urlEouvDetalhesManifestacao;

        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $urlWebServiceEOuv = $objInfraParametro->getValor('EOUV_URL_WEBSERVICE_IMPORTACAO_MANIFESTACAO');
        $urlWebServiceAnexosEOuv = $objInfraParametro->getValor('EOUV_URL_WEBSERVICE_IMPORTACAO_ANEXO_MANIFESTACAO');
        $idTipoDocumentoAnexoPadrao = $objInfraParametro->getValor('ID_SERIE_EXTERNO_OUVIDORIA');
        $idTipoDocumentoAnexoDadosManifestacao = $objInfraParametro->getValor('EOUV_ID_SERIE_DOCUMENTO_EXTERNO_DADOS_MANIFESTACAO');
        $idUnidadeOuvidoria = $objInfraParametro->getValor('ID_UNIDADE_OUVIDORIA');
        $idUsuarioSei = $objInfraParametro->getValor('ID_USUARIO_SEI');
        $urlEouvDetalhesManifestacao = $objInfraParametro->getValor('EOUV_URL_DETALHE_MANIFESTACAO');
        $dataAtual = InfraData::getStrDataHoraAtual();
        $SiglaSistema = 'EOUV';
        $IdentificacaoServico = 'CadastrarManifestacao';
        $usuarioWebService = $objInfraParametro->getValor('EOUV_USUARIO_ACESSO_WEBSERVICE');
        $senhaUsuarioWebService = $objInfraParametro->getValor('EOUV_SENHA_ACESSO_WEBSERVICE');;

        //Quando estiver executando agendamento Simula Login
        if (SessaoSEI::getInstance()->getNumIdUnidadeAtual()==null && SessaoSEI::getInstance()->getNumIdUsuario()==null){

            try{

                InfraDebug::getInstance()->gravar(__METHOD__);
                InfraDebug::getInstance()->gravar('SIGLA SISTEMA:'.$SiglaSistema);
                InfraDebug::getInstance()->gravar('IDENTIFICACAO SERVICO:'.$IdentificacaoServico);
                InfraDebug::getInstance()->gravar('ID UNIDADE:'.$idUnidadeOuvidoria);

                SessaoSEI::getInstance(false);

                $objServicoDTO = $this->obterServico($SiglaSistema, $IdentificacaoServico);

                if ($idUnidadeOuvidoria!=null){
                    $objUnidadeDTO = $this->obterUnidade($idUnidadeOuvidoria,null);
                }else{
                    $objUnidadeDTO = null;
                }

               // $this->validarAcessoAutorizado(explode(',',str_replace(' ','',$objServicoDTO->getStrServidor())));

                SessaoSEI::getInstance()->simularLogin(null, null, $objServicoDTO->getNumIdUsuario(), $objUnidadeDTO->getNumIdUnidade());

            }catch(Exception $e){
                PaginaSEI::getInstance()->processarExcecao($e);
            }
        }



        try {

            //Retorna dados da última execução com Sucesso
            $objUltimaExecucao = MdCguEouvAgendamentoINT::retornarUltimaExecucaoSucesso();

            if ($objUltimaExecucao != null) {
                //WebService Eouv trabalha com Data no formato AAAA-MM-DD HH:MM:SS
                $ultimaDataExecucao = $objUltimaExecucao->getDthDthImportacao();
                $idUltimaExecucao = $objUltimaExecucao->getNumIdRelatorioImportacao();
            } //Primeira execução ou nenhuma executada com sucesso
            else {
                $ultimaDataExecucao = $objInfraParametro->getValor('EOUV_DATA_INICIAL_IMPORTACAO_MANIFESTACOES');
            }

            $ultimaDataExecucaoFormatoEouv = $this->retornaDataFormatoEouv($ultimaDataExecucao);
            $dataAtualFormatoEOuv = $this->retornaDataFormatoEouv($dataAtual);

            $objEouvRelatorioImportacaoDTO = $this->gravarLogImportacao($ultimaDataExecucao, $dataAtual);
            $idRelatorioImportacao = $objEouvRelatorioImportacaoDTO->getNumIdRelatorioImportacao();
            $objEouvRelatorioImportacaoRN = new MdCguEouvRelatorioImportacaoRN();

            $this->validarEnderecoWebService($urlWebServiceEOuv);
            $this->validarEnderecoWebService($urlWebServiceAnexosEOuv);

            $objWS = $this->gerarObjWebService($urlWebServiceEOuv);
            $objWSAnexo = $this->gerarObjWebService($urlWebServiceAnexosEOuv);

            $retornoWs = $this->executarServicoConsultaManifestacoes($objWS, $usuarioWebService, $senhaUsuarioWebService, $ultimaDataExecucaoFormatoEouv, $dataAtualFormatoEOuv, null, $idRelatorioImportacao);
            $arrComErro = $this->obterManifestacoesComErro($objWS, $usuarioWebService, $senhaUsuarioWebService, $ultimaDataExecucaoFormatoEouv, $dataAtualFormatoEOuv, $idRelatorioImportacao);

            $arrManifestacoes = array();

            if (is_array ($retornoWs['GetListaManifestacaoOuvidoriaResult']['ManifestacoesOuvidoria'])){
                $arrManifestacoes = $this->verificaRetornoWS($retornoWs['GetListaManifestacaoOuvidoriaResult']['ManifestacoesOuvidoria']['ManifestacaoOuvidoria']);
            }

            if (is_array($arrComErro)){
                $arrManifestacoes = array_merge($arrManifestacoes, $arrComErro);
            }

            if (count($arrManifestacoes) > 0){
                foreach ($arrManifestacoes as $retornoWsLinha) {
                    $this->executarImportacaoLinha($retornoWsLinha);
                }
            }

            $textoMensagemFinal = 'Execução Finalizada com Sucesso!';

            if ($ocorreuErroEmProtocolo){
                $textoMensagemFinal = $textoMensagemFinal . ' Porém ocorreram erros em 1 ou mais protocolos.';
            }

            //Grava a execução com sucesso se tiver corrido tudo bem
            $objEouvRelatorioImportacaoDTO2 = new MdCguEouvRelatorioImportacaoDTO();

            $objEouvRelatorioImportacaoDTO2->setNumIdRelatorioImportacao($objEouvRelatorioImportacaoDTO->getNumIdRelatorioImportacao());
            $objEouvRelatorioImportacaoDTO2->setStrSinSucesso('S');
            $objEouvRelatorioImportacaoDTO2->setStrDeLogProcessamento($textoMensagemFinal);
            $objEouvRelatorioImportacaoRN->alterar($objEouvRelatorioImportacaoDTO2);
        }
        catch(Exception $e){

            //print_r ($e);

            $objEouvRelatorioImportacaoDTO3 = new MdCguEouvRelatorioImportacaoDTO();
            $objEouvRelatorioImportacaoDTO3->setNumIdRelatorioImportacao($objEouvRelatorioImportacaoDTO->getNumIdRelatorioImportacao());
            $strMensagem = 'Ocorreu um erro no processamento:' . $e;
            $strMensagem = substr($strMensagem, 0, 500);
            $objEouvRelatorioImportacaoDTO3->setStrDeLogProcessamento($strMensagem);

            $objEouvRelatorioImportacaoRN->alterar($objEouvRelatorioImportacaoDTO3);

            PaginaSEI::getInstance()->processarExcecao($e);

            die;
        }

    }

    public function executarImportacaoLinha($retornoWsLinha){

        global $objEouvRelatorioImportacaoDTO,
               $idTipoDocumentoAnexoPadrao,
               $objProcedimentoDTO,
               $objTipoProcedimentoDTO,
               $arrObjAssuntoDTO,
               $arrObjParticipantesDTO,
               $idTipoDocumentoAnexoDadosManifestacao,
               $idUnidadeOuvidoria,
               $idUsuarioSei,
               $objWSAnexo,
               $dataRegistro,
               $ocorreuErroEmProtocolo,
               $numProtocoloFormatado,
               $idRelatorioImportacao;

        $objProcedimentoDTO = new ProcedimentoDTO();
        $objProtocoloDTO = new ProtocoloDTO();
        $objProcedimentoRN = new ProcedimentoRN();
        $objProcedimentoDTO->setDblIdProcedimento(null);

        $dataRegistro = $retornoWsLinha['DataCadastro'];
        $numProtocoloFormatado =  $retornoWsLinha['NumProtocolo'];

        //Limpa os registros de detalhe de importação com erro para este nup. Caso ocorra um novo, será criado
        // novo registro de erro para o NUP no tratamento desta function.
        $this->limparErrosParaNup($numProtocoloFormatado);

        if (!isset($retornoWsLinha['IdTipoManifestacao'])) {
            print_r("Numero Protocolo Formatado:" . $numProtocoloFormatado);
            print_r($retornoWsLinha);
            $this->gravarLogLinha($numProtocoloFormatado, $idRelatorioImportacao, 'Tipo de processo não foi informado.', 'N');
        } else {

            $objEouvDeparaImportacaoDTO = new MdCguEouvDeparaImportacaoDTO();
            $objEouvDeparaImportacaoDTO->retNumIdTipoProcedimento();
            $objEouvDeparaImportacaoDTO->setNumIdTipoManifestacaoEouv($retornoWsLinha['IdTipoManifestacao']);


            $objEouvDeparaImportacaoRN = new MdCguEouvDeparaImportacaoRN();
            $objEouvDeparaImportacaoDTO = $objEouvDeparaImportacaoRN->consultarRN0186($objEouvDeparaImportacaoDTO);

            if (!$objEouvDeparaImportacaoDTO == null) {
                $retornoWsLinha['IdTipoManifestacao'] = $objEouvDeparaImportacaoDTO->getNumIdTipoProcedimento();
            } else {
                $this->gravarLogLinha($numProtocoloFormatado, $objEouvRelatorioImportacaoDTO->getNumIdRelatorioImportacao(), 'Não existe mapeamento DePara do Tipo de Manifestação do E-Ouv para o tipo de procedimento do SEI.', 'N');
                //continue;
            }
        }



        try {
            $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
            $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
            $objTipoProcedimentoDTO->retStrNome();
            $objTipoProcedimentoDTO->retStrStaNivelAcessoSugestao();
            $objTipoProcedimentoDTO->retStrStaGrauSigiloSugestao();
            $objTipoProcedimentoDTO->retStrSinIndividual();
            $objTipoProcedimentoDTO->retNumIdHipoteseLegalSugestao();
            $objTipoProcedimentoDTO->setNumIdTipoProcedimento($retornoWsLinha['IdTipoManifestacao']);

            $objTipoProcedimentoRN = new TipoProcedimentoRN();

            $objTipoProcedimentoDTO = $objTipoProcedimentoRN->consultarRN0267($objTipoProcedimentoDTO);

            if ($objTipoProcedimentoDTO == null) {
                throw new Exception('Tipo de processo não encontrado: ' . $retornoWsLinha['IdTipoManifestacao']);
            }

            $objProcedimentoAPI = new ProcedimentoAPI();
            $objProcedimentoAPI->setIdTipoProcedimento($objTipoProcedimentoDTO->getNumIdTipoProcedimento());

            $varEspecificacaoAssunto = $retornoWsLinha['IdAssunto'] . " - " . $retornoWsLinha['DescAssunto'];

            $objProcedimentoAPI->setEspecificacao($varEspecificacaoAssunto);
            $objProcedimentoAPI->setIdUnidadeGeradora($idUnidadeOuvidoria);
            $objProcedimentoAPI->setNumeroProtocolo($retornoWsLinha['NumProtocolo']);
            $objProcedimentoAPI->setDataAutuacao($retornoWsLinha['DataCadastro']);
            $objEntradaGerarProcedimentoAPI = new EntradaGerarProcedimentoAPI();
            $objEntradaGerarProcedimentoAPI->setProcedimento($objProcedimentoAPI);

            $objSaidaGerarProcedimentoAPI = new SaidaGerarProcedimentoAPI();

            $objSeiRN = new SeiRN();

            $arrDocumentos = $this->gerarAnexosProtocolo($numProtocoloFormatado);
            $documentoManifestacao =  $this->gerarPDFPedidoInicial($retornoWsLinha);
            LogSEI::getInstance()->gravar('Importação de Manifestação ' . $numProtocoloFormatado . ': total de  Anexos configurados: ' . count($arrDocumentos), InfraLog::$INFORMACAO);

            array_push($arrDocumentos, $documentoManifestacao);

            $objEntradaGerarProcedimentoAPI->setDocumentos($arrDocumentos);

            $objSaidaGerarProcedimentoAPI = $objSeiRN->gerarProcedimento($objEntradaGerarProcedimentoAPI);

            $this->gravarLogLinha($numProtocoloFormatado, $idRelatorioImportacao, 'Protocolo ' . $retornoWsLinha['numProtocolo'] . ' gravado com sucesso.', 'S');

        } catch (Exception $e) {

            if ($objSaidaGerarProcedimentoAPI != null and $objSaidaGerarProcedimentoAPI->getIdProcedimento() > 0){
                $this->excluirProcessoComErro($objSaidaGerarProcedimentoAPI->getIdProcedimento());
            }
            //print_r($e);
            //throw new InfraException('Erro gerando Processo.',$e);
            $this->gravarLogLinha($numProtocoloFormatado, $idRelatorioImportacao, 'Erro na gravação: ' . $e, 'N');
        }
    }

    public function limparErrosParaNup($numProtocoloComErro){
        $objEouvRelatorioImportacaoDetalheDTO = new MdCguEouvRelatorioImportacaoDetalheDTO();
        $objEouvRelatorioImportacaoDetalheDTO->retTodos(true);
        $objEouvRelatorioImportacaoDetalheDTO->setStrProtocoloFormatado($numProtocoloComErro);
        $objEouvRelatorioImportacaoDetalheDTO->setStrSinSucesso('N');

        $objEouvRelatorioImportacaoDetalheRN = new MdCguEouvRelatorioImportacaoDetalheRN();
        $objListaErros = $objEouvRelatorioImportacaoDetalheRN->listar($objEouvRelatorioImportacaoDetalheDTO);
        foreach($objListaErros as $erro){
            $erro->setStrSinSucesso('C');
            $objEouvRelatorioImportacaoDetalheRN->alterar($erro);
        }

    }

    public function gerarPDFPedidoInicial($retornoWsLinha){

        global $objProcedimentoDTO,
               $objTipoProcedimentoDTO,
               $arrObjAssuntoDTO,
               $arrObjParticipantesDTO,
               $idTipoDocumentoAnexoDadosManifestacao,
               $idUnidadeOuvidoria,
               $idUsuarioSei,
               $objWSAnexo,
               $ocorreuErroAdicionarAnexo,
               $urlEouvDetalhesManifestacao;

        /***********************************************************************************************
         * //DADOS INICIAIS DA MANIFESTAÇÃO
         * Primeiro é gerado o PDF com todas as informações referentes a Manifestação, e mais abaixo
         * é incluído como um anexo do novo Processo Gerado
         * **********************************************************************************************/
        $nup = $retornoWsLinha['NumProtocolo'];
        $dt_cadastro = $retornoWsLinha['DataCadastro'];
        $id_assunto = $retornoWsLinha['IdAssunto'];
        $desc_assunto = $retornoWsLinha['DescAssunto'];
        $id_sub_assunto = $retornoWsLinha['IdSubAssunto'];
        $desc_sub_assunto = $retornoWsLinha['DescSubAssunto'];
        $id_tipo_manifestacao = $retornoWsLinha['IdTipoManifestacao'];
        $desc_tipo_manifestacao = $retornoWsLinha['DescTipoManifestacao'];
        $envolve_das4_superior = $retornoWsLinha['EhDenunciaEnvolvendoOcupanteCargoComissionadoDAS4OuSuperior'];
        $dt_prazo_atendimento = $retornoWsLinha['PrazoAtendimento'];
        $nome_orgao = $retornoWsLinha['NomeOrgaoDestinatario'];

        //print_r($retornoWsLinha['SolicitanteManifestacaoOuvidoria']);
        //exit();

        $nome = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['NomeSolicitante'];
        $id_faixa_etaria = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['IdFaixaEtaria'];
        $desc_faixa_etaria = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['DescFaixaEtaria'];
        $id_raca_cor = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['IdRacaCor'];
        $desc_raca_cor = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['DescRacaCor'];
        $sexo = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['SexoSolicitante'];
        $id_documento_identificacao = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['IdTipoDocumentoIdentificacao'];
        $desc_documento_identificacao = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['DescTipoDocumentoIdentificacao'];
        $numero_documento_identificacao = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['NumDocumentoIdentificacao'];
        $endereco = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['LogradouroSolicitante'] . " " . $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['ComplementoLogradouroSolicitante'];
        $bairro = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['BairroLogradouroSolicitante'];
        $id_municipio = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['IdMunicipioSolicitante'];
        $desc_municipio = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['DescMunicipioSolicitante'];
        $uf = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['SigUfSolicitante'];
        $cep = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['CepSolicitante'];
        $ddd_telefone = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['DddTelefone'];
        $telefone = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['NumTelefone'];
        $email = $retornoWsLinha['SolicitanteManifestacaoOuvidoria']['EmailSolicitante'];

        $id_municipio_fato = $retornoWsLinha['FatoManifestacaoOuvidoria']['IdMunicipio'];
        $desc_municipio_fato = $retornoWsLinha['FatoManifestacaoOuvidoria']['DescMunicipio'];
        $uf_fato = $retornoWsLinha['FatoManifestacaoOuvidoria']['SigUf'];
        $descricao_fato = $retornoWsLinha['FatoManifestacaoOuvidoria']['FatoManifestacao'];

        $envolvidos = array();
        if (isset($retornoWsLinha['FatoManifestacaoOuvidoria']['ListaEnvolvidos']['Envolvido'])) {
            $iEnvolvido = 0;
            foreach ($this->verificaRetornoWS($retornoWsLinha['FatoManifestacaoOuvidoria']['ListaEnvolvidos']['Envolvido']) as $envolvidosFatoManifestacao) {
                $envolvidos[$iEnvolvido][0] = $envolvidosFatoManifestacao['IdFuncaoEnvolvido'] . " - " . $envolvidosFatoManifestacao['DescFuncaoEnvolvido'];
                $envolvidos[$iEnvolvido][1] = $envolvidosFatoManifestacao['Nome'];
                $envolvidos[$iEnvolvido][2] = $envolvidosFatoManifestacao['Orgao'];
                $iEnvolvido++;
            }
        }

        $campos_adicionais = array();

        if (isset($retornoWsLinha['ListaCamposAdicionaisManifestacao']['CampoAdicional'])) {
            $iCamposAdicionais = 0;
            foreach ($this->verificaRetornoWS($retornoWsLinha['ListaCamposAdicionaisManifestacao']['CampoAdicional']) as $camposAdicionais) {
                $campos_adicionais[$iCamposAdicionais][0] = $camposAdicionais['NomeCampo'];
                $campos_adicionais[$iCamposAdicionais][1] = $camposAdicionais['ValorCampo'];
                $iCamposAdicionais++;
            }
        }

        $pdf = new InfraPDF("P", "pt", "A4");

        $pdf->AddPage();
        //$pdf->Image('logog8.jpg');

        $pdf->SetFont('arial', 'B', 18);
        $pdf->Cell(0, 5, "Dados da Manifestação", 0, 1, 'C');
        $pdf->Cell(0, 5, "", "B", 1, 'C');
        $pdf->Ln(20);

        //***********************************************************************************************
        //1. Dados INICIAIS
        //***********************************************************************************************
        $pdf->SetFont('arial', 'B', 14);
        $pdf->Cell(0, 20, "1. Dados Iniciais da Manifestação", 0, 0, 'L');
        $pdf->Ln(20);

        //NUP
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(150, 20, "NUP:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(0, 20, $nup, 0, 1, 'L');

        //Data Cadastro
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(150, 20, "Data do Cadastro:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(0, 20, $dt_cadastro, 0, 1, 'L');

        //Assunto / SubAssunto
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(150, 20, "Assunto/SubAssunto:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(0, 20, $id_assunto . " - " . $desc_assunto . " / " . $id_sub_assunto . " - " . $desc_sub_assunto, 0, 1, 'L');

        //Tipo de Manifestação
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(150, 20, "Tipo da Manifestação:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(0, 20, $id_tipo_manifestacao . " - " . $desc_tipo_manifestacao, 0, 1, 'L');

        //EnvolveDas4OuSuperior
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(450, 20, "Denúncia Envolvendo Ocupante de Cargo Comissionado DAS4 ou Superior?:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(20, 20, $envolve_das4_superior, 0, 1, 'L');

        //Prazo de Atendimento
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(150, 20, "Prazo de Atendimento:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $dt_prazo_atendimento, 0, 1, 'L');

        //Nome do Órgão
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(150, 20, "Nome do Órgão:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $nome_orgao, 0, 1, 'L');

        //***********************************************************************************************
        //2. Dados do Solicitante
        //***********************************************************************************************
        $pdf->Ln(20);
        $pdf->SetFont('arial', 'B', 14);
        $pdf->Cell(70, 20, "2. Dados do Solicitante:", 0, 0, 'L');
        $pdf->Ln(20);

        //Nome do Solicitante
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(150, 20, "Nome do Solicitante:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $nome, 0, 1, 'L');

        //Faixa Etária
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(150, 20, "Faixa Etária:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $id_faixa_etaria . " - " . $desc_faixa_etaria, 0, 1, 'L');

        //Raça Cor
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(150, 20, "Raça/Cor:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $id_raca_cor . " - " . $desc_raca_cor, 0, 1, 'L');

        //Sexo
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(150, 20, "Sexo:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $sexo, 0, 1, 'L');

        //Documento Identificação
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(170, 20, "Documento de Identificação:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $id_documento_identificacao . " - " . $desc_documento_identificacao, 0, 1, 'L');

        //Número do Documento Identificação
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(150, 20, "Número do Documento:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $numero_documento_identificacao, 0, 1, 'L');

        $pdf->ln(4);
        //Endereço
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(70, 20, "Endereço:", 0, 1, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $endereco, 0, 1, 'L');
        $pdf->Cell(70, 20, $bairro, 0, 1, 'L');
        $pdf->Cell(70, 20, $desc_municipio . " - " . $uf, 0, 1, 'L');

        //CEP
        $pdf->Cell(70, 20, "CEP:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $cep, 0, 1, 'L');

        //Telefone
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(70, 20, "Telefone:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, "(" . $ddd_telefone . ") " . $telefone, 0, 1, 'L');

        //Email
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(70, 20, "E-mail:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $email, 0, 1, 'L');

        //***********************************************************************************************
        //3. Dados do Fato da Manifestação
        //***********************************************************************************************
        $pdf->Ln(20);
        $pdf->SetFont('arial', 'B', 14);
        $pdf->Cell(70, 20, "3. Fato da Manifestação:", 0, 0, 'L');
        $pdf->Ln(20);

        //Município/UF
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(115, 20, "Município/UF:", 0, 0, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->Cell(70, 20, $id_municipio_fato . " - " . $desc_municipio_fato . " / " . $uf_fato, 0, 1, 'L');

        //Descrição
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(115, 20, "Descrição:", 0, 1, 'L');
        $pdf->setFont('arial', '', 12);
        $pdf->MultiCell(0, 20, $descricao_fato, 0, 'J');

        //Envolvidos
        $pdf->SetFont('arial', 'B', 12);
        $pdf->Cell(115, 20, "Envolvidos:", 0, 1, 'L');
        $pdf->setFont('arial', '', 12);

        for ($x = 0; $x < count($envolvidos); $x++) {
            $pdf->Cell(70, 20, "Função:", 0, 0, 'L');
            $pdf->Cell(0, 20, $envolvidos[$x][0], 0, 1, 'L');
            $pdf->Cell(70, 20, "Nome:", 0, 0, 'L');
            $pdf->Cell(0, 20, $envolvidos[$x][1], 0, 1, 'L');
            $pdf->Cell(70, 20, "Órgão:", 0, 0, 'L');
            $pdf->Cell(0, 20, $envolvidos[$x][2], 0, 1, 'L');
            $pdf->Ln(10);
        }

        //***********************************************************************************************
        //4. Campos Adicionais
        //***********************************************************************************************
        $pdf->Ln(20);
        $pdf->SetFont('arial', 'B', 14);
        $pdf->Cell(70, 20, "4. Campos Adicionais:", 0, 0, 'L');
        $pdf->Ln(20);

        for ($y = 0; $y < count($campos_adicionais); $y++) {
            $pdf->SetFont('arial', 'B', 12);
            $pdf->Cell(180, 20, $campos_adicionais[$y][0] . ":", 0, 0, 'L');
            $pdf->setFont('arial', '', 12);
            $pdf->Cell(0, 20, $campos_adicionais[$y][1], 0, 1, 'L');
        }

        if($ocorreuErroAdicionarAnexo == true){
            $pdf->Ln(20);
            $pdf->SetFont('arial', 'B', 14);
            $pdf->Cell(70, 20, "5. Observações:", 0, 0, 'L');
            $pdf->Ln(20);

            $pdf->SetFont('arial', '', 12);
            $pdf->MultiCell(0, 20, "Um ou mais anexos da manifestação não foram importados para o SEI devido a restrições da extensão do arquivo. Acesse o E-ouv para mais detalhes. ", 0, 'J');
            //$pdf->Cell(0, 20, $urlEouvDetalhesManifestacao, 0, 1, 'L');

        }


        $objAnexoRN = new AnexoRN();
        $strNomeArquivoInicialUpload = $objAnexoRN->gerarNomeArquivoTemporario();

        $pdf->Output(DIR_SEI_TEMP . "/" . $strNomeArquivoInicialUpload . ".pdf", "F");

        //Renomeia tirando a extensão para o SEI trabalhar o Arquivo
        rename(DIR_SEI_TEMP . "/" . $strNomeArquivoInicialUpload . ".pdf", DIR_SEI_TEMP . "/" . $strNomeArquivoInicialUpload);

        $objDocumentoManifestacao = new DocumentoAPI();
        $objDocumentoManifestacao->setTipo('R');
        $objDocumentoManifestacao->setIdSerie($idTipoDocumentoAnexoDadosManifestacao);
        $objDocumentoManifestacao->setData($retornoWsLinha['DataCadastro']);
        $objDocumentoManifestacao->setNomeArquivo('RelatórioDadosManifestação.pdf');
        $objDocumentoManifestacao->setConteudo(base64_encode(file_get_contents(DIR_SEI_TEMP . "/" . $strNomeArquivoInicialUpload)));
        return $objDocumentoManifestacao;

    }

    public function gerarAnexosProtocolo($numProtocoloFormatado){

        global $idTipoDocumentoAnexoPadrao,
               $objProcedimentoDTO,
               $objTipoProcedimentoDTO,
               $arrObjAssuntoDTO,
               $arrObjParticipantesDTO,
               $idTipoDocumentoAnexoDadosManifestacao,
               $idUnidadeOuvidoria,
               $idUsuarioSei,
               $objWSAnexo,
               $dataRegistro,
               $strMensagemErroAnexos,
               $ocorreuErroAdicionarAnexo,
               $idRelatorioImportacao;

        /**********************************************************************************************************************************************
         * Início da importação de anexos de cada protocolo
         * Desativado momentaneamente
         */

        /*$retornoWsAnexo = $objWSAnexo->GetAnexosManifestacao(array("login" => '11111111111',
            "senha" => 'abcd1234', "numeroProtocolo" => InfraUtil::retirarFormatacao($numProtocoloFormatado)))->GetAnexosManifestacaoResult;
        */

        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $usuarioWebService = $objInfraParametro->getValor('EOUV_USUARIO_ACESSO_WEBSERVICE');
        $senhaUsuarioWebService = $objInfraParametro->getValor('EOUV_SENHA_ACESSO_WEBSERVICE');

        $retornoWsAnexo = $objWSAnexo->call('GetAnexosManifestacao', array(
            'login'=>$usuarioWebService,
            'senha'=>$senhaUsuarioWebService,
            'numeroProtocolo'=>InfraUtil::retirarFormatacao($numProtocoloFormatado)
        ), '', '', false, true);

        $strMensagemErroAnexos = '';
        $ocorreuErroAdicionarAnexo = false;

        $arrAnexos = array();

        $intIdCodigoErroExecucao = $retornoWsAnexo['GetAnexosManifestacaoResult']['CodigoErro'];
        if ($intIdCodigoErroExecucao > 0){
            throw new Exception($retornoWsAnexo['GetAnexosManifestacaoResult']['DescricaoErro']);
        }

        $intTotAnexos = $retornoWsAnexo['GetAnexosManifestacaoResult']['QtdAnexos'];

        if($intTotAnexos == 0){
            //Não encontrou anexos..
            return $arrAnexos;
        }

        //Trata as extensões permitidas
        $objArquivoExtensaoDTO = new ArquivoExtensaoDTO();
        $objArquivoExtensaoDTO->retNumIdArquivoExtensao();
        $objArquivoExtensaoDTO->retStrExtensao();
        $objArquivoExtensaoDTO->retStrDescricao();
        $objArquivoExtensaoDTO->retNumTamanhoMaximo();
        $objArquivoExtensaoRN = new ArquivoExtensaoRN();
        $arrObjArquivoExtensaoDTO = $objArquivoExtensaoRN->listar($objArquivoExtensaoDTO);
        $arrExtensoesPermitidas = array();
        foreach($arrObjArquivoExtensaoDTO as $extensao){
            array_push($arrExtensoesPermitidas, strtoupper ($extensao->getStrExtensao()));
        }

        foreach ($retornoWsAnexo['GetAnexosManifestacaoResult']['AnexosManifestacao'] as $retornoWsAnexoLista) {
            foreach ($this->verificaRetornoWS($retornoWsAnexoLista) as $retornoWsAnexoLinha) {
                try {
                    $strNomeArquivoOriginal = $retornoWsAnexoLinha['NomeArquivo'];
                    $ext = strtoupper(pathinfo($strNomeArquivoOriginal, PATHINFO_EXTENSION));
                    $intIndexExtensao = array_search($ext, $arrExtensoesPermitidas);

                    if (is_numeric($intIndexExtensao)) {
                        $objAnexoRN = new AnexoRN();
                        $strNomeArquivoUpload = $objAnexoRN->gerarNomeArquivoTemporario();

                        $fp = fopen(DIR_SEI_TEMP . '/' . $strNomeArquivoUpload, 'w');

                        $strConteudoCodificado = $retornoWsAnexoLinha['ConteudoZipadoEBase64'];
                        $binConteudoDecodificado = '';
                        for ($i = 0; $i < ceil(strlen($strConteudoCodificado) / 256); $i++) {
                            $binConteudoDecodificado = $binConteudoDecodificado . base64_decode(substr($strConteudoCodificado, $i * 256, 256));
                        }

                        $binConteudoUnzip = $this->gzdecode($binConteudoDecodificado);

                        fwrite($fp, $binConteudoUnzip);
                        fclose($fp);

                        $objAnexoManifestacao = new DocumentoAPI();
                        $objAnexoManifestacao->setTipo('R');
                        $objAnexoManifestacao->setIdSerie($idTipoDocumentoAnexoDadosManifestacao);
                        $objAnexoManifestacao->setData(InfraData::getStrDataHoraAtual());
                        $objAnexoManifestacao->setNomeArquivo($strNomeArquivoOriginal);
                        $objAnexoManifestacao->setConteudo(base64_encode(file_get_contents(DIR_SEI_TEMP . '/' . $strNomeArquivoUpload)));

                        array_push($arrAnexos, $objAnexoManifestacao);
                        $this->gravarLogLinha($numProtocoloFormatado, $idRelatorioImportacao, 'Arquivo adicionado como anexo: ' . $strNomeArquivoOriginal, '');
                    }
                    else
                    {
                        $ocorreuErroAdicionarAnexo = true;
                        LogSEI::getInstance()->gravar('Importação de Manifestação ' . $numProtocoloFormatado . ': Arquivo ' . $strNomeArquivoOriginal . ' possui extensão inválida.', InfraLog::$INFORMACAO);
                        continue;
                    }

                }
                catch(Exception $e){
                    $ocorreuErroAdicionarAnexo = true;
                    $strMensagemErroAnexos = $strMensagemErroAnexos . " " . $e;
                }
            }

            if($ocorreuErroAdicionarAnexo==true){
                $this->gravarLogLinha($numProtocoloFormatado, $idRelatorioImportacao, 'Um ou mais documentos anexos não foram importados corretamente: ' . $strMensagemErroAnexos, 'S', '');
            }

            return $arrAnexos;
        }

    }

    public function excluirProcessoComErro($idProcedimento){

        try{
            $objProcedimentoExcluirDTO = new ProcedimentoDTO();
            $objProcedimentoExcluirDTO->setDblIdProcedimento($idProcedimento);
            $objProcedimentoRN = new ProcedimentoRN();
            $objProcedimentoRN->excluirRN0280($objProcedimentoExcluirDTO);
            ProcedimentoINT::removerProcedimentoVisitado($idProcedimento);
            //PaginaSEI::getInstance()->setStrMensagem('Exclusão realizada com sucesso.');
            //$bolFlagProcessou = true;

        }catch(Exception $e){
            PaginaSEI::getInstance()->processarExcecao($e);
        }

    }

    public static function formatarProcesso($strProcesso) {

        $strProcesso = InfraUtil::retirarFormatacao($strProcesso);

        if (strlen($strProcesso)==0){
            return '';
        }

        if (strlen($strProcesso) == 17){
            $strProcesso = substr($strProcesso,0,5).".".
                substr($strProcesso,5,6)."/".
                substr($strProcesso,11,4)."-".
                substr($strProcesso,15,2);
        }
        return $strProcesso;
    }
}

?>
