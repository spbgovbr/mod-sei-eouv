<?
/**
 * CONTROLADORIA GERAL DA UNIÃO - CGU
 *
 * 09/10/2015 - criado por Rafael Leandro
 *
 */

require_once dirname(__FILE__) . '/../../../../SEI.php';

class EOuvAgendamentoRN extends InfraRN
{

    public function __construct()
    {
        parent::__construct();
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
        if (isset($retornoWsLista) and count($retornoWsLista) == 1) {
            $retornoWsLinha[] = $retornoWsLista;
        } else {
            $retornoWsLinha = $retornoWsLista;
        }
        return $retornoWsLinha;
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

        if($objExisteDetalheDTO==null) {
            $objEouvRelatorioImportacaoDetalheRN->cadastrar($objEouvRelatorioImportacaoDetalheDTO);
        }
        else{
            $objEouvRelatorioImportacaoDetalheRN->alterar($objEouvRelatorioImportacaoDetalheDTO);
        }

    }

    public function validarEnderecoWebService($urlWebService){

        if (!@file_get_contents($urlWebService)) {
            throw new InfraException('Arquivo WSDL ' . $urlWebService . ' não encontrado.');
        }

    }

    public function gerarObjWebService($urlWebService){
        try {
            $objWS = new SoapClient($urlWebService, array('encoding' => 'ISO-8859-1', 'trace' => true, 'exceptions' => true));
            //para uso com MTOM utilizar um componente que dê suporte a esta característica (ex.: BeSimpleSoap)
            //$objWS = new BeSimple\SoapClient\SoapClient($urlWebServiceESic, array ('encoding'=>'ISO-8859-1',
            // 'attachment_type'=>BeSimple\SoapCommon\Helper::ATTACHMENTS_TYPE_MTOM,
            // 'soap_version' => SOAP_1_1));
        } catch (Exception $e) {
            throw new InfraException('Erro acessando serviço:' . $urlWebService, $e);
        }
        return $objWS;
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


    public function executarImportacaoManifestacaoEOuv()
    {

        //try{
        InfraDebug::getInstance()->setBolLigado(true);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(false);
        InfraDebug::getInstance()->limpar();

        LogSEI::getInstance()->gravar('Rotina de Importação de Manifestações do E-Ouv');

        //$strWSDL = 'http://ares-h.df.cgu/Ouvidorias/Servicos/ServicoConsultaManifestacao.svc?singleWsdl';

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
        //$urlWebServiceEOuv = 'http://sistema.ouvidorias.gov.br/Servicos/ServicoConsultaManifestacao.svc?singleWsdl';
        //$urlWebServiceAnexosEOuv = 'http://sistema.ouvidorias.gov.br/Servicos/ServicoAnexosManifestacao.svc?singleWsdl';
        $idTipoDocumentoAnexoPadrao = $objInfraParametro->getValor('ID_SERIE_EXTERNO_OUVIDORIA');
        $idTipoDocumentoAnexoDadosManifestacao = $objInfraParametro->getValor('EOUV_ID_SERIE_DOCUMENTO_EXTERNO_DADOS_MANIFESTACAO');
        $idUnidadeOuvidoria = $objInfraParametro->getValor('ID_UNIDADE_OUVIDORIA');
        $idUsuarioSei = $objInfraParametro->getValor('ID_USUARIO_SEI');
        $urlEouvDetalhesManifestacao = $objInfraParametro->getValor('EOUV_URL_DETALHE_MANIFESTACAO');
        $dataAtual = InfraData::getStrDataHoraAtual();
        $dataAtualFormatoEOuv = $this->retornaDataFormatoEouv($dataAtual);
        $SiglaSistema = 'EOUV';
        $IdentificacaoServico = 'CadastrarManifestacao';
        $usuarioWebService = 'usTiCGU';
        $senhaUsuarioWebService = 'teste123';

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

        //$ultimaDataExecucao = '29/09/2016 14:00:00';
        $ultimaDataExecucaoFormatoEouv = $this->retornaDataFormatoEouv($ultimaDataExecucao);

        //$dataAtual = '20-12-2015';
        $dataAtualFormatoEOuv = $this->retornaDataFormatoEouv($dataAtual);

        $this->validarEnderecoWebService($urlWebServiceEOuv);
        $this->validarEnderecoWebService($urlWebServiceAnexosEOuv);

        $objWS = $this->gerarObjWebService($urlWebServiceEOuv);
        $objWSAnexo = $this->gerarObjWebService($urlWebServiceAnexosEOuv);

        echo "ultimaExecucao" . $ultimaDataExecucaoFormatoEouv . "<br>";
        echo "dataAtual" . $dataAtualFormatoEOuv;

        exit();

        $retornoWs = $objWS->GetListaManifestacaoOuvidoria(array("login" => $usuarioWebService,
            "senha" => $senhaUsuarioWebService, "numProtocolo" => '', "dataCadastroInicio" => $ultimaDataExecucaoFormatoEouv, "dataCadastroFim" => $dataAtualFormatoEOuv, "dataPrazoRespostaInicio" => '', "dataPrazoRespostaFim" => '', "situacaoManifestacao" => 1))->GetListaManifestacaoOuvidoriaResult;

        /*$retornoWs = $objWS->GetListaManifestacaoOuvidoria(array("login" => 'rafael.ferreira@cgu.gov.br',
            "senha" => 'R*afa1982', "numProtocolo" => '00106000175201523', "dataCadastroInicio" => '', "dataCadastroFim" => '', "dataPrazoRespostaInicio" => '', "dataPrazoRespostaFim" => '', "situacaoManifestacao" => 0))->GetListaManifestacaoOuvidoriaResult;*/

        /*if (isset($retornoWs->CodigoErro)){
            echo $retornoWs->CodigoErro;
        }*/

        $objEouvRelatorioImportacaoDTO = $this->gravarLogImportacao($ultimaDataExecucao, $dataAtual);

        try {

            foreach ($retornoWs->ManifestacoesOuvidoria as $retornoWsLista) {
                foreach ($this->verificaRetornoWS($retornoWsLista) as $retornoWsLinha) {
                    $this->executarImportacaoLinha($retornoWsLinha);

                }
            }

            //Após a execução da importação das manifestações novas tentará importar as que ocorreram em problema em outra importação e ainda não foram resolvidas
            if ($idUltimaExecucao != null && $idUltimaExecucao != null) {

                //Busca protocolos problemáticos
                $arrProtocolosProblematicos = MdCguEouvAgendamentoINT::retornarManifestacoesNaoImportadasPorProblema($idUltimaExecucao);

                if ($arrProtocolosProblematicos != null) {
                    foreach ($arrProtocolosProblematicos as $objProtocolosProblematicosDTO) {

                        $retornoWsProblematicos = $objWS->GetListaManifestacaoOuvidoria(array("login" => $usuarioWebService,
                            "senha" => $senhaUsuarioWebService, "numProtocolo" => InfraUtil::retirarFormatacao($objProtocolosProblematicosDTO->getStrProtocoloFormatado()), "dataCadastroInicio" => '', "dataCadastroFim" => '', "dataPrazoRespostaInicio" => '', "dataPrazoRespostaFim" => '', "situacaoManifestacao" => 1))->GetListaManifestacaoOuvidoriaResult;

                        foreach ($retornoWsProblematicos->ManifestacoesOuvidoria as $retornoWsListaProblematicos) {

                            foreach ($this->verificaRetornoWS($retornoWsListaProblematicos) as $retornoWsLinhaProblematicos) {

                                $this->executarImportacaoLinha($retornoWsLinhaProblematicos);

                            }
                        }
                    }
                }
            }

            $textoMensagemFinal = 'Execução Finalizada com Sucesso!';
            if ($ocorreuErroEmProtocolo){
                $textoMensagemFinal = $textoMensagemFinal . ' Porém ocorreram erros em 1 ou mais protocolos.';
            }

            //Grava a execução com sucesso se tiver corrido tudo bem
            $objEouvRelatorioImportacaoRN = new MdCguEouvRelatorioImportacaoRN();
            $objEouvRelatorioImportacaoDTO2 = new MdCguEouvRelatorioImportacaoDTO();

            $objEouvRelatorioImportacaoDTO2->setNumIdRelatorioImportacao($objEouvRelatorioImportacaoDTO->getNumIdRelatorioImportacao());
            $objEouvRelatorioImportacaoDTO2->setStrSinSucesso('S');
            $objEouvRelatorioImportacaoDTO2->setStrDeLogProcessamento($textoMensagemFinal);
            $objEouvRelatorioImportacaoRN->alterar($objEouvRelatorioImportacaoDTO2);
        }
        catch(Exception $e){

            $objEouvRelatorioImportacaoDTO3 = new MdCguEouvRelatorioImportacaoDTO();
            $objEouvRelatorioImportacaoDTO3->setNumIdRelatorioImportacao($objEouvRelatorioImportacaoDTO->getNumIdRelatorioImportacao());
            $objEouvRelatorioImportacaoDTO3->setStrDeLogProcessamento('Ocorreu um erro no processamento:' . $e);

            $objEouvRelatorioImportacaoRN->alterar($objEouvRelatorioImportacaoDTO3);

            PaginaSEI::getInstance()->processarExcecao($e);
            die;
        }

        //var_dump($arrRetorno);
        //exit;

        /*}catch(Exception $e){
          throw new InfraException('Erro realizando teste de agendamento.',$e);
        }*/

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

        $dataRegistro = $retornoWsLinha->DataCadastro;
        $numProtocoloFormatado =  $retornoWsLinha->NumProtocolo;

        if (!isset($retornoWsLinha->IdTipoManifestacao)) {
            $this->gravarLogLinha($numProtocoloFormatado, $idRelatorioImportacao, 'Tipo de processo não foi informado.', 'N');
            //throw new InfraException('Tipo de processo não foi informado.');
            //break;
        } else {

            $objEouvDeparaImportacaoDTO = new MdCguEouvDeparaImportacaoDTO();
            $objEouvDeparaImportacaoDTO->retNumIdTipoProcedimento();
            $objEouvDeparaImportacaoDTO->setNumIdTipoManifestacaoEouv($retornoWsLinha->IdTipoManifestacao);


            $objEouvDeparaImportacaoRN = new MdCguEouvDeparaImportacaoRN();
            $objEouvDeparaImportacaoDTO = $objEouvDeparaImportacaoRN->consultarRN0186($objEouvDeparaImportacaoDTO);

            if (!$objEouvDeparaImportacaoDTO == null) {
                $retornoWsLinha->IdTipoManifestacao = $objEouvDeparaImportacaoDTO->getNumIdTipoProcedimento();
            } else {
                $this->gravarLogLinha($numProtocoloFormatado, $objEouvRelatorioImportacaoDTO->getNumIdRelatorioImportacao(), 'Não existe mapeamento DePara do Tipo de Manifestação do E-Ouv para o tipo de procedimento do SEI.', 'N');
                //continue;
            }
        }

        $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
        $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
        $objTipoProcedimentoDTO->retStrNome();
        $objTipoProcedimentoDTO->retStrStaNivelAcessoSugestao();
        $objTipoProcedimentoDTO->retStrStaGrauSigiloSugestao();
        $objTipoProcedimentoDTO->retStrSinIndividual();
        $objTipoProcedimentoDTO->retNumIdHipoteseLegalSugestao();
        $objTipoProcedimentoDTO->setNumIdTipoProcedimento($retornoWsLinha->IdTipoManifestacao);

        $objTipoProcedimentoRN = new TipoProcedimentoRN();

        $objTipoProcedimentoDTO = $objTipoProcedimentoRN->consultarRN0267($objTipoProcedimentoDTO);

        if ($objTipoProcedimentoDTO == null) {
            throw new InfraException('Tipo de processo não encontrado.');
        }

        $objProcedimentoDTO->setStrProtocoloProcedimentoFormatado($retornoWsLinha->NumProtocolo);
        $objProcedimentoDTO->setNumIdTipoProcedimento($objTipoProcedimentoDTO->getNumIdTipoProcedimento());
        $objProcedimentoDTO->setStrNomeTipoProcedimento($objTipoProcedimentoDTO->getStrNome());
        $objProtocoloDTO->setNumIdTipoProcedimentoProcedimento($objTipoProcedimentoDTO->getNumIdTipoProcedimento());
        //$objProtocoloDTO->setDtaGeracao(InfraData::getStrDataAtual());
        $objProtocoloDTO->setDtaGeracao($retornoWsLinha->DataCadastro);
        //$objProtocoloAnexoDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
        $objProtocoloDTO->setStrStaNivelAcessoLocal($objTipoProcedimentoDTO->getStrStaNivelAcessoSugestao());
        $objProtocoloDTO->setNumIdUnidadeGeradora($idUnidadeOuvidoria);
        $objProtocoloDTO->setStrStaGrauSigilo($objTipoProcedimentoDTO->getStrStaGrauSigiloSugestao());
        $objProtocoloDTO->setNumIdHipoteseLegal($objTipoProcedimentoDTO->getNumIdHipoteseLegalSugestao());


        $varEspecificacaoAssunto = '';
        $varEspecificacaoAssunto = $retornoWsLinha->IdAssunto . " - " . $retornoWsLinha->DescAssunto;
        if ($retornoWsLinha->IdSubAssunto > 0) {
            $varEspecificacaoAssunto = $varEspecificacaoAssunto . " / " . $retornoWsLinha->IdSubAssunto . " - " . $retornoWsLinha->DescSubAssunto;
        }

        //echo $objProcedimentoDTO->getStrProtocoloProcedimentoFormatado();

        $objProtocoloDTO->setStrProtocoloFormatado($objProcedimentoDTO->getStrProtocoloProcedimentoFormatado());
        $objProtocoloDTO->setStrDescricao($varEspecificacaoAssunto);
        $objProcedimentoDTO->setStrSinGerarPendencia('S');
        $objProcedimentoDTO->setNumVersaoLock(0);

        $objProtocoloDTO->setDblIdProtocolo(null);


        //ASSUNTOS
        //Busca e adiciona os assuntos sugeridos para o tipo de Processo
        $objRelTipoProcedimentoAssuntoDTO = new RelTipoProcedimentoAssuntoDTO();
        $objRelTipoProcedimentoAssuntoDTO->retNumIdAssunto();
        $objRelTipoProcedimentoAssuntoDTO->retNumSequencia();
        $objRelTipoProcedimentoAssuntoDTO->setNumIdTipoProcedimento($objTipoProcedimentoDTO->getNumIdTipoProcedimento());

        $objRelTipoProcedimentoAssuntoRN = new RelTipoProcedimentoAssuntoRN();
        $arrObjRelTipoProcedimentoAssuntoDTO = $objRelTipoProcedimentoAssuntoRN->listarRN0192($objRelTipoProcedimentoAssuntoDTO);
        $arrObjAssuntoDTO = array();

        foreach ($arrObjRelTipoProcedimentoAssuntoDTO as $objRelTipoProcedimentoAssuntoDTO) {
            $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
            $objRelProtocoloAssuntoDTO->setNumIdAssunto($objRelTipoProcedimentoAssuntoDTO->getNumIdAssunto());
            $objRelProtocoloAssuntoDTO->setNumSequencia($objRelTipoProcedimentoAssuntoDTO->getNumSequencia());
            $arrObjAssuntoDTO[] = $objRelProtocoloAssuntoDTO;
        }
        $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrObjAssuntoDTO);

        //$objObservacaoDTO = new ObservacaoDTO();
        //$objObservacaoDTO->setStrDescricao('');
        $objProtocoloDTO->setArrObjObservacaoDTO(array());

        $arrObjParticipantesDTO = array();
        $objProtocoloDTO->setArrObjParticipanteDTO($arrObjParticipantesDTO);


        //ATRIBUTOS
        //$objProtocoloDTO->setArrObjRelProtocoloAtributoDTO(array());
        //////////////////////////////////////////////////////////////////////////////////////////////////
        $arrRelProtocoloAtributo = AtributoINT::processarRI0691();
        $arrObjRelProtocoloAtributoDTO = array();
        for ($x = 0; $x < count($arrRelProtocoloAtributo); $x++) {
            $arrRelProtocoloAtributoDTO = new RelProtocoloAtributoDTO();
            $arrRelProtocoloAtributoDTO->setStrValor($arrRelProtocoloAtributo[$x]->getStrValor());
            $arrRelProtocoloAtributoDTO->setNumIdAtributo($arrRelProtocoloAtributo[$x]->getNumIdAtributo());
            $arrObjRelProtocoloAtributoDTO[$x] = $arrRelProtocoloAtributoDTO;
        }
        $objProtocoloDTO->setArrObjRelProtocoloAtributoDTO($arrObjRelProtocoloAtributoDTO);
        //////////////////////////////////////////////////////////////////////////////////////////////////

        //ANEXOS
        $objProtocoloDTO->setArrObjAnexoDTO(array());

        $objProcedimentoDTO->setObjProtocoloDTO($objProtocoloDTO);
        $objProcedimentoDTO->retDblIdProcedimento();
        $objProcedimentoDTO->retStrProtocoloProcedimentoFormatadoPesquisa();

        try {
            $objInfraException = new InfraException();
            $objProcedimentoDTO = $objProcedimentoRN->gerarRN0156($objProcedimentoDTO);
            $this->gravarLogLinha($numProtocoloFormatado, $idRelatorioImportacao, 'Protocolo ' . $retornoWsLinha->numProtocolo . ' gravado com sucesso.', 'S');

        } catch (Exception $e) {
            $this->excluirProcessoComErro($objProcedimentoDTO->getDblIdProcedimento());
            //throw new InfraException('Erro gerando Processo.',$e);
            $this->gravarLogLinha($numProtocoloFormatado, $idRelatorioImportacao, 'Erro na gravação: ' . $e, 'N');
        }

        if ($objProcedimentoDTO->getDblIdProcedimento()!=null) {

            try {
                $this->gerarAnexosProtocolo($numProtocoloFormatado);
            } catch (Exception $e) {
                //$this->excluirProcessoComErro($objProcedimentoDTO->getDblIdProcedimento());
                $ocorreuErroEmProtocolo = true;
                $this->gravarLogLinha($numProtocoloFormatado, $idRelatorioImportacao, 'Erro na importação de arquivos anexos. ' . $e, 'S', '');
            }

            try {
                $this->gerarPDFPedidoInicial($retornoWsLinha);
            } catch (Exception $e) {
                $this->excluirProcessoComErro($objProcedimentoDTO->getDblIdProcedimento());
                $ocorreuErroEmProtocolo = true;
                $this->gravarLogLinha($numProtocoloFormatado, $idRelatorioImportacao, 'Erro gerando PDF do Pedido Inicial. ' . $e, 'N', '');
            }


            /*Atualiza o andamento para ficar vermelho na caixa de entrada.
            */
            $objAtividadeDTOVisualizacao = new AtividadeDTO();
            $objAtividadeDTOVisualizacao->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
            $objAtividadeDTOVisualizacao->setNumTipoVisualizacao(AtividadeRN::$TV_NAO_VISUALIZADO);

            $objAtividadeRN = new AtividadeRN();
            $objAtividadeRN->atualizarVisualizacao($objAtividadeDTOVisualizacao);

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
        $nup = $retornoWsLinha->NumProtocolo;
        $dt_cadastro = $retornoWsLinha->DataCadastro;
        $id_assunto = $retornoWsLinha->IdAssunto;
        $desc_assunto = $retornoWsLinha->DescAssunto;
        $id_sub_assunto = $retornoWsLinha->IdSubAssunto;
        $desc_sub_assunto = $retornoWsLinha->DescSubAssunto;
        $id_tipo_manifestacao = $retornoWsLinha->IdTipoManifestacao;
        $desc_tipo_manifestacao = $retornoWsLinha->DescTipoManifestacao;
        $envolve_das4_superior = $retornoWsLinha->EhDenunciaEnvolvendoOcupanteCargoComissionadoDAS4OuSuperior;
        $dt_prazo_atendimento = $retornoWsLinha->PrazoAtendimento;
        $nome_orgao = $retornoWsLinha->NomeOrgaoDestinatario;

        $nome = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->NomeSolicitante;
        $id_faixa_etaria = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->IdFaixaEtaria;
        $desc_faixa_etaria = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->DescFaixaEtaria;
        $id_raca_cor = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->IdRacaCor;
        $desc_raca_cor = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->DescRacaCor;
        $sexo = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->SexoSolicitante;
        $id_documento_identificacao = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->IdTipoDocumentoIdentificacao;
        $desc_documento_identificacao = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->DescTipoDocumentoIdentificacao;
        $numero_documento_identificacao = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->NumDocumentoIdentificacao;
        $endereco = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->LogradouroSolicitante . " " . $retornoWsLinha->SolicitanteManifestacaoOuvidoria->ComplementoLogradouroSolicitante;
        $bairro = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->BairroLogradouroSolicitante;
        $id_municipio = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->IdMunicipioSolicitante;
        $desc_municipio = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->DescMunicipioSolicitante;
        $uf = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->SigUfSolicitante;
        $cep = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->CepSolicitante;
        $ddd_telefone = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->DddTelefone;
        $telefone = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->NumTelefone;
        $email = $retornoWsLinha->SolicitanteManifestacaoOuvidoria->EmailSolicitante;

        $id_municipio_fato = $retornoWsLinha->FatoManifestacaoOuvidoria->IdMunicipio;
        $desc_municipio_fato = $retornoWsLinha->FatoManifestacaoOuvidoria->DescMunicipio;
        $uf_fato = $retornoWsLinha->FatoManifestacaoOuvidoria->SigUf;
        $descricao_fato = $retornoWsLinha->FatoManifestacaoOuvidoria->FatoManifestacao;

        $envolvidos = array();
        if (isset($retornoWsLinha->FatoManifestacaoOuvidoria->ListaEnvolvidos->Envolvido)) {
            $iEnvolvido = 0;
            foreach ($this->verificaRetornoWS($retornoWsLinha->FatoManifestacaoOuvidoria->ListaEnvolvidos->Envolvido) as $envolvidosFatoManifestacao) {
                $envolvidos[$iEnvolvido][0] = $envolvidosFatoManifestacao->IdFuncaoEnvolvido . " - " . $envolvidosFatoManifestacao->DescFuncaoEnvolvido;
                $envolvidos[$iEnvolvido][1] = $envolvidosFatoManifestacao->Nome;
                $envolvidos[$iEnvolvido][2] = $envolvidosFatoManifestacao->Orgao;
                $iEnvolvido++;
            }
        }

        $campos_adicionais = array();

        if (isset($retornoWsLinha->ListaCamposAdicionaisManifestacao->CampoAdicional)) {
            $iCamposAdicionais = 0;
            foreach ($this->verificaRetornoWS($retornoWsLinha->ListaCamposAdicionaisManifestacao->CampoAdicional) as $camposAdicionais) {
                $campos_adicionais[$iCamposAdicionais][0] = $camposAdicionais->NomeCampo;
                $campos_adicionais[$iCamposAdicionais][1] = $camposAdicionais->ValorCampo;
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

        $objDocumentoInicialDTO = new DocumentoDTO();
        $objDocumentoInicialDTO->setDblIdDocumento(null);

        //echo "<br><br>Dentro de EouvAgendamentoRN: " . $objProcedimentoDTO->getDblIdProcedimento() . "<br>";

        $objDocumentoInicialDTO->setDblIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());
        $objDocumentoInicialDTO->setDblIdDocumentoEdoc(null);
        $objDocumentoInicialDTO->setDblIdDocumentoEdocBase(null);
        $objDocumentoInicialDTO->setNumIdSerie($idTipoDocumentoAnexoDadosManifestacao);
        $objDocumentoInicialDTO->setNumIdUnidadeResponsavel($idUnidadeOuvidoria);
        $objDocumentoInicialDTO->setStrNumero("DadosDaManifestacao.pdf");
        $objDocumentoInicialDTO->setNumIdTipoConferencia(null);
        $objDocumentoInicialDTO->setNumVersaoLock(0);
        $objDocumentoInicialDTO->setStrConteudo(null);

        $objProtocoloAnexoInicialDTO = new ProtocoloDTO();
        $objProtocoloAnexoInicialDTO->setDblIdProtocolo(null);
        //$objProtocoloAnexoDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
        $objProtocoloAnexoInicialDTO->setStrStaNivelAcessoLocal($objTipoProcedimentoDTO->getStrStaNivelAcessoSugestao());
        $objProtocoloAnexoInicialDTO->setNumIdHipoteseLegal($objTipoProcedimentoDTO->getNumIdHipoteseLegalSugestao());
        //$objProtocoloAnexoInicialDTO->setDblIdProtocoloAgrupador(null);
        $objProtocoloAnexoInicialDTO->setStrDescricao('Documento que contém os Dados da Manifestação Importada do E-OUV');
        $objProtocoloAnexoInicialDTO->setNumIdSerieDocumento($idTipoDocumentoAnexoDadosManifestacao);
        $objProtocoloAnexoInicialDTO->setDtaGeracao($retornoWsLinha->DataCadastro);
        $objProtocoloAnexoInicialDTO->setArrObjRelProtocoloAssuntoDTO($arrObjAssuntoDTO);
        $objProtocoloAnexoInicialDTO->setArrObjParticipanteDTO($arrObjParticipantesDTO);
        $objProtocoloAnexoInicialDTO->setArrObjObservacaoDTO(array());
        $objProtocoloAnexoInicialDTO->setArrObjRelProtocoloAtributoDTO(array());

        $objAnexoInicialDTO = new AnexoDTO();
        $objAnexoInicialDTO->setNumIdAnexo($strNomeArquivoInicialUpload);
        $objAnexoInicialDTO->setStrNome('DadosDaManifestacao.pdf');
        $objAnexoInicialDTO->setDthInclusao(InfraData::getStrDataHoraAtual());
        $objAnexoInicialDTO->setNumTamanho(filesize(DIR_SEI_TEMP . '/' . $strNomeArquivoInicialUpload));
        //$objAnexoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAnexoInicialDTO->setNumIdUsuario($idUsuarioSei);

        $objProtocoloAnexoInicialDTO->setArrObjAnexoDTO(array($objAnexoInicialDTO));


        $objDocumentoInicialDTO->setObjProtocoloDTO($objProtocoloAnexoInicialDTO);

        $objDocumentoInicialRN = new DocumentoRN();
        $objDocumentoDTORelatorio = $objDocumentoInicialRN->receberRN0991($objDocumentoInicialDTO);

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

        // AQUI GERA UM NOVO PROTOCOLO E DOCUMENTO PARA OS DOCUMENTOS ANEXOS
        /*$retornoWsAnexo = $objWSAnexo->GetAnexosManifestacao(array("login" => 'rafael.ferreira@cgu.gov.br',
            "senha" => 'R*afa1982', "numeroProtocolo" => $objProtocoloDTO->getStrProtocoloFormatadoPesquisa()))->GetAnexosManifestacaoResult;*/
        $retornoWsAnexo = $objWSAnexo->GetAnexosManifestacao(array("login" => '11111111111',
            "senha" => 'abcd1234', "numeroProtocolo" => InfraUtil::retirarFormatacao($numProtocoloFormatado)))->GetAnexosManifestacaoResult;


        $strMensagemErroAnexos = '';
        $ocorreuErroAdicionarAnexo = false;

        foreach ($retornoWsAnexo->AnexosManifestacao as $retornoWsAnexoLista) {
            foreach ($this->verificaRetornoWS($retornoWsAnexoLista) as $retornoWsAnexoLinha) {

                try {
                    $objDocumentoDTO = new DocumentoDTO();
                    $objDocumentoDTO->setDblIdDocumento(null);

                    $objDocumentoDTO->setDblIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());
                    $objDocumentoDTO->setDblIdDocumentoEdoc(null);
                    $objDocumentoDTO->setDblIdDocumentoEdocBase(null);
                    $objDocumentoDTO->setNumIdSerie($idTipoDocumentoAnexoPadrao);
                    $objDocumentoDTO->setNumIdUnidadeResponsavel($idUnidadeOuvidoria);
                    $objDocumentoDTO->setStrNumero($retornoWsAnexoLinha->NomeArquivo);
                    $objDocumentoDTO->setNumIdTipoConferencia(null);
                    $objDocumentoDTO->setNumVersaoLock(0);
                    $objDocumentoDTO->setStrConteudo(null);

                    $objProtocoloAnexoDTO = new ProtocoloDTO();
                    $objProtocoloAnexoDTO->setDblIdProtocolo(null);
                    //$objProtocoloAnexoDTO->setStrStaNivelAcessoLocal(ProtocoloRN::$NA_PUBLICO);
                    $objProtocoloAnexoDTO->setStrStaNivelAcessoLocal($objTipoProcedimentoDTO->getStrStaNivelAcessoSugestao()); //Estava dando erro, verificar depois
                    $objProtocoloAnexoDTO->setNumIdHipoteseLegal($objTipoProcedimentoDTO->getNumIdHipoteseLegalSugestao());
                    //$objProtocoloAnexoDTO->setDblIdProtocoloAgrupador(null);
                    $objProtocoloAnexoDTO->setStrDescricao('Documento importado do E-Ouv');
                    $objProtocoloAnexoDTO->setNumIdSerieDocumento($idTipoDocumentoAnexoPadrao);
                    $objProtocoloAnexoDTO->setDtaGeracao($dataRegistro);
                    $objProtocoloAnexoDTO->setArrObjRelProtocoloAssuntoDTO($arrObjAssuntoDTO);
                    $objProtocoloAnexoDTO->setArrObjParticipanteDTO($arrObjParticipantesDTO);
                    $objProtocoloAnexoDTO->setArrObjObservacaoDTO(array());
                    $objProtocoloAnexoDTO->setArrObjRelProtocoloAtributoDTO(array());

                    $strNomeArquivo = $retornoWsAnexoLinha->NomeArquivo;

                    $objAnexoRN = new AnexoRN();
                    $strNomeArquivoUpload = $objAnexoRN->gerarNomeArquivoTemporario();

                    $fp = fopen(DIR_SEI_TEMP . '/' . $strNomeArquivoUpload, 'w');

                    $strConteudoCodificado = $retornoWsAnexoLinha->ConteudoZipadoEBase64;
                    $binConteudoDecodificado = '';
                    for ($i = 0; $i < ceil(strlen($strConteudoCodificado) / 256); $i++) {
                        $binConteudoDecodificado = $binConteudoDecodificado . base64_decode(substr($strConteudoCodificado, $i * 256, 256));
                    }

                    $binConteudoUnzip = $this->gzdecode($binConteudoDecodificado);

                    fwrite($fp, $binConteudoUnzip);
                    fclose($fp);

                    $objAnexoDTO = new AnexoDTO();
                    $objAnexoDTO->setNumIdAnexo($strNomeArquivoUpload);
                    $objAnexoDTO->setStrNome($strNomeArquivo);
                    $objAnexoDTO->setDthInclusao(InfraData::getStrDataHoraAtual());
                    $objAnexoDTO->setNumTamanho(filesize(DIR_SEI_TEMP . '/' . $strNomeArquivoUpload));
                    //$objAnexoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                    $objAnexoDTO->setNumIdUsuario($idUsuarioSei);

                    $objProtocoloAnexoDTO->setArrObjAnexoDTO(array($objAnexoDTO));

                    $objDocumentoDTO->setObjProtocoloDTO($objProtocoloAnexoDTO);

                    $objDocumentoRN = new DocumentoRN();
                    $objDocumentoDTORelatorio = $objDocumentoRN->receberRN0991($objDocumentoDTO);
                }
                catch(Exception $e){
                    $ocorreuErroAdicionarAnexo = true;
                    $strMensagemErroAnexos = $strMensagemErroAnexos . " " . $e;
                }
            }

            if($ocorreuErroAdicionarAnexo==true){
                $this->gravarLogLinha($numProtocoloFormatado, $idRelatorioImportacao, 'Um ou mais documentos anexos não foram importados corretamente: ' . $strMensagemErroAnexos, 'S', '');
            }
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
}

?>
