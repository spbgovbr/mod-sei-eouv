<?php

/**
 * Created by PhpStorm.
 * User: flaviomy
 * Date: 25/10/2017
 * Time: 17:53
 */
require_once dirname(__FILE__) . '/../../../../SEI.php';

class MdCguEouvAtualizadorBDRN extends InfraRN
{
    private $numSeg = 0;
    private $versaoAtualDesteModulo = '1.0.0';
    private $nomeDesteModulo = 'EOUV - Integração com sistema E-ouv';
    private $prefixoParametro = 'MD_CGU_EOUV';
    private $nomeParametroVersaoModulo = 'VERSAO_MODULO_CGU_EOUV';

    public function __construct()
    {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    protected function atualizarVersaoConectado()
    {

        try {
            $this->inicializar('INICIANDO ATUALIZACAO DO MODULO ' . $this->nomeDesteModulo . ' NO SEI VERSAO ' . SEI_VERSAO);

            /**testando versao do framework
            $numVersaoInfraRequerida = '1.385';
            $versaoInfraFormatada = (int) str_replace('.','', VERSAO_INFRA);
            $versaoInfraReqFormatada = (int) str_replace('.','', $numVersaoInfraRequerida);

            if ($versaoInfraFormatada < $versaoInfraReqFormatada){
                $this->finalizar('VERSAO DO FRAMEWORK PHP INCOMPATIVEL (VERSAO ATUAL '.VERSAO_INFRA.', SENDO REQUERIDA VERSAO IGUAL OU SUPERIOR A '.$numVersaoInfraRequerida.')',true);
            }**/

            //checando BDs suportados
            if (!(BancoSEI::getInstance() instanceof InfraMySql) &&
                !(BancoSEI::getInstance() instanceof InfraSqlServer) &&
                !(BancoSEI::getInstance() instanceof InfraOracle)
            ) {
                $this->finalizar('BANCO DE DADOS NAO SUPORTADO: ' . get_parent_class(BancoSEI::getInstance()), true);
            }

            //checando permissoes na base de dados
            $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());

            if (count($objInfraMetaBD->obterTabelas('sei_teste')) == 0) {
                BancoSEI::getInstance()->executarSql('CREATE TABLE sei_teste (id ' . $objInfraMetaBD->tipoNumero() . ' null)');
            }

            BancoSEI::getInstance()->executarSql('DROP TABLE sei_teste');

            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

            $strVersaoModuloEOuv = $objInfraParametro->getValor($this->prefixoParametro . $this->nomeParametroVersaoModulo, false);

            //VERIFICANDO QUAL VERSAO DEVE SER INSTALADA NESTA EXECUCAO
            //nao tem nenhuma versao ainda, instalar primeira versão
            if (InfraString::isBolVazia($strVersaoModuloEOuv)) {
                $this->instalarv100();
                $this->logar('ATUALIZAÇÔES DA VERSÃO ' . $this->versaoAtualDesteModulo . ' DO MODULO ' . $this->nomeDesteModulo . ' INSTALADAS COM SUCESSO NA BASE DO SEI');
                $this->finalizar('FIM', false);
            }

            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);

        } catch (Exception $e) {
            InfraDebug::getInstance()->setBolLigado(true);
            InfraDebug::getInstance()->setBolDebugInfra(true);
            InfraDebug::getInstance()->setBolEcho(true);
            $this->logar($e->getTraceAsString());
            $this->finalizar($e->getMessage(), true);
            print_r($e);
            die;
            throw new InfraException('Erro atualizando versão.', $e);
        }

    }

    private function inicializar($strTitulo)
    {

        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');

        try {
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
        } catch (Exception $e) {
        }

        ob_implicit_flush();

        InfraDebug::getInstance()->setBolLigado(true);
        InfraDebug::getInstance()->setBolDebugInfra(true);
        InfraDebug::getInstance()->setBolEcho(true);
        InfraDebug::getInstance()->limpar();

        $this->numSeg = InfraUtil::verificarTempoProcessamento();

        $this->logar($strTitulo);
    }

    private function logar($strMsg)
    {
        InfraDebug::getInstance()->gravar($strMsg);
        flush();
    }

    private function finalizar($strMsg = null, $bolErro)
    {

        if (!$bolErro) {
            $this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
            $this->logar('TEMPO TOTAL DE EXECUÇÃO: ' . $this->numSeg . ' s');
        } else {
            $strMsg = 'ERRO: ' . $strMsg;
        }

        if ($strMsg != null) {
            $this->logar($strMsg);
        }

        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(false);
        $this->numSeg = 0;
        die;
    }

    private function instalarv100()
    {
        SessaoInfra::setObjInfraSessao(SessaoSEI::getInstance());
        BancoInfra::setObjInfraIBanco(BancoSEI::getInstance());

        $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());

        $this->logar('EXECUTANDO A INSTALACAO DA VERSAO 1.0.0 DO MODULO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');

        //6.1	Para o mapeamento DE-PARA entre os Tipos de Manifestação E-ouv e Tipo de processo SEI
        $this->logar('CRIANDO A TABELA md_cgu_eouv_depara_importacao');

        BancoSEI::getInstance()->executarSql('CREATE TABLE md_cgu_eouv_depara_importacao(id_tipo_manifestacao_eouv ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL ,
            id_tipo_procedimento ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL ,
		    de_tipo_manifestacao_eouv ' . $objInfraMetaBD->tipoTextoVariavel(50) . ' NULL)');

        $objInfraMetaBD->adicionarChavePrimaria('md_cgu_eouv_depara_importacao', 'pk_md_cgu_eouv_depara_importacao', array('id_tipo_manifestacao_eouv', 'id_tipo_procedimento'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk1_md_cgu_eouv_tipo_procedimento', 'md_cgu_eouv_depara_importacao', array('id_tipo_procedimento'), 'tipo_procedimento', array('id_tipo_procedimento'));
        $objInfraMetaBD->criarIndice('md_cgu_eouv_depara_importacao', 'i01_md_cgu_eouv_depara_importacao', array(id_tipo_procedimento));

        $this->logar('CRIANDO REGISTROS PARA A TABELA md_cgu_eouv_depara_importacao');
        BancoSEI::getInstance()->executarSql('INSERT INTO md_cgu_eouv_depara_importacao (id_tipo_manifestacao_eouv, de_tipo_manifestacao_eouv, id_tipo_procedimento) VALUES (\'1\', \'Denúncia\', \'100000335\');');
        BancoSEI::getInstance()->executarSql('INSERT INTO md_cgu_eouv_depara_importacao (id_tipo_manifestacao_eouv, de_tipo_manifestacao_eouv, id_tipo_procedimento) VALUES (\'2\', \'Reclamação\', \'100000336\');');
        BancoSEI::getInstance()->executarSql('INSERT INTO md_cgu_eouv_depara_importacao (id_tipo_manifestacao_eouv, de_tipo_manifestacao_eouv, id_tipo_procedimento) VALUES (\'3\', \'Elogio\', \'100000333\');');
        BancoSEI::getInstance()->executarSql('INSERT INTO md_cgu_eouv_depara_importacao (id_tipo_manifestacao_eouv, de_tipo_manifestacao_eouv, id_tipo_procedimento) VALUES (\'4\', \'Sugestão\', \'100000338\');');
        BancoSEI::getInstance()->executarSql('INSERT INTO md_cgu_eouv_depara_importacao (id_tipo_manifestacao_eouv, de_tipo_manifestacao_eouv, id_tipo_procedimento) VALUES (\'5\', \'Solicitação\', \'100000334\');');

        $this->logar('CRIANDO A TABELA md_cgu_eouv_relatorio_importacao');
        BancoSEI::getInstance()->executarSql('CREATE TABLE md_cgu_eouv_relatorio_importacao(id_md_cgu_eouv_relatorio_importacao ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL ,
        dth_importacao ' . $objInfraMetaBD->tipoDataHora() . ' NOT NULL ,
        sin_sucesso ' . $objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ,
        dth_periodo_inicial ' . $objInfraMetaBD->tipoDataHora() . ' NULL ,
        dth_periodo_final ' . $objInfraMetaBD->tipoDataHora() . ' NULL ,
        des_log_processamento ' . $objInfraMetaBD->tipoTextoVariavel(500) . ' NULL)');
        $objInfraMetaBD->adicionarChavePrimaria('md_cgu_eouv_relatorio_importacao', 'pk_md_cgu_eouv_relatorio_importacao', array('id_md_cgu_eouv_relatorio_importacao'));

        $this->logar('CRIANDO A TABELA md_cgu_eouv_relatorio_import_detalhe');
        BancoSEI::getInstance()->executarSql('CREATE TABLE md_cgu_eouv_relatorio_import_detalhe(id_md_cgu_eouv_relatorio_importacao ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL ,
        num_protocolo_formatado ' . $objInfraMetaBD->tipoTextoFixo(50) . ' NOT NULL ,
        sin_sucesso ' . $objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ,
        des_log_processamento ' . $objInfraMetaBD->tipoTextoVariavel(500) . ' NULL,
        dth_importacao ' . $objInfraMetaBD->tipoDataHora() . ' NULL)');

        $objInfraMetaBD->adicionarChavePrimaria('md_cgu_eouv_relatorio_import_detalhe', 'pk_md_cgu_eouv_relatorio_import_detalhe',
        array('id_md_cgu_eouv_relatorio_importacao', 'num_protocolo_formatado'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk1_md_cgu_eouv_relatorio_import_detalhe', 'md_cgu_eouv_relatorio_import_detalhe', array('id_md_cgu_eouv_relatorio_importacao'), 'md_cgu_eouv_relatorio_importacao', array('id_md_cgu_eouv_relatorio_importacao'));

        if (BancoSEI::getInstance() instanceof InfraMySql) {
        BancoSEI::getInstance()->executarSql('create table seq_md_cgu_eouv_relatorio_importacao (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
        BancoSEI::getInstance()->executarSql('create table seq_md_cgu_eouv_relatorio_importacao (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle) {
        BancoSEI::getInstance()->criarSequencialNativa('seq_md_cgu_eouv_relatorio_importacao', 1);
        }

        $this->logar('CRIANDO Parâmetros do Sei');
        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $objInfraParametro->setValor('EOUV_URL_WEBSERVICE_IMPORTACAO_ANEXO_MANIFESTACAO', 'https://sistema.ouvidorias.gov.br/Servicos/ServicoAnexosManifestacao.svc');
        $objInfraParametro->setValor('EOUV_URL_WEBSERVICE_IMPORTACAO_MANIFESTACAO', 'https://sistema.ouvidorias.gov.br/Servicos/ServicoConsultaManifestacao.svc');
        $objInfraParametro->setValor('ID_UNIDADE_OUVIDORIA', '110000001');
        $objInfraParametro->setValor('ID_SERIE_EXTERNO_OUVIDORIA', '92');
        $objInfraParametro->setValor('EOUV_ID_SERIE_DOCUMENTO_EXTERNO_DADOS_MANIFESTACAO', '63');
        $objInfraParametro->setValor('EOUV_DATA_INICIAL_IMPORTACAO_MANIFESTACOES ', '01/12/2015');
        $objInfraParametro->setValor('EOUV_URL_DETALHE_MANIFESTACAO', '');
        $objInfraParametro->setValor('EOUV_USUARIO_ACESSO_WEBSERVICE', '');
        $objInfraParametro->setValor('EOUV_SENHA_ACESSO_WEBSERVICE', '');

        $this->logar('CRIANDO Agendamento da tarefa no Sei');
        session_start();
        $objInfraAgendamentoTarefaDTO = new InfraAgendamentoTarefaDTO();
        $objInfraAgendamentoTarefaDTO->setNumIdInfraAgendamentoTarefa(null);
        $objInfraAgendamentoTarefaDTO->setStrDescricao('Rotina responsável pela execução da importação de manifestações cadastradas no E-Ouv que serão importadas para o SEI como um novo processo. Se baseia na data da última execução com sucesso até a data atual.');
        $objInfraAgendamentoTarefaDTO->setStrComando('MdCguEouvAgendamentoRN :: executarImportacaoManifestacaoEOuv');
        $objInfraAgendamentoTarefaDTO->setStrStaPeriodicidadeExecucao('D');

        $objInfraAgendamentoTarefaDTO->setStrPeriodicidadeComplemento('1');
        $objInfraAgendamentoTarefaDTO->setDthUltimaExecucao(null);
        $objInfraAgendamentoTarefaDTO->setDthUltimaConclusao(null);
        $objInfraAgendamentoTarefaDTO->setStrSinSucesso('N');
        $objInfraAgendamentoTarefaDTO->setStrParametro(null);
        $objInfraAgendamentoTarefaDTO->setStrEmailErro('flavio.yamashita@cgu.gov.br');
        $objInfraAgendamentoTarefaDTO->setStrSinAtivo('S');
        $objInfraAgendamentoTarefaRN = new InfraAgendamentoTarefaRN();
        $objInfraAgendamentoTarefaRN->getObjInfraIBanco();
        $objInfraAgendamentoTarefaDTO = $objInfraAgendamentoTarefaRN->cadastrar($objInfraAgendamentoTarefaDTO);
        $this->logar('Tarefa cadastrada com sucesso.');

        SessaoInfra::setObjInfraSessao(SessaoSEI::getInstance());
        BancoInfra::setObjInfraIBanco(BancoSEI::getInstance());

        $this->logar('Criando Sistema EOUV NA BASE DO SEI...');
        $objUsuarioDTO = new UsuarioDTO();
        $objUsuarioDTO->setNumIdUsuario(null);
        $objUsuarioDTO->setNumIdOrgao(0);
        $objUsuarioDTO->setStrIdOrigem(null);
        $objUsuarioDTO->setStrSigla('EOUV');
        $objUsuarioDTO->setStrNome('Integração com sistema E-Ouv');
        $objUsuarioDTO->setNumIdContato(null);
        $objUsuarioDTO->setStrStaTipo(UsuarioRN::$TU_SISTEMA);
        $objUsuarioDTO->setStrSenha(null);
        $objUsuarioDTO->setStrSinAcessibilidade('N');
        $objUsuarioDTO->setStrSinAtivo('S');
        $objUsuarioRN = new UsuarioRN();
        $objUsuarioDTO = $objUsuarioRN->cadastrarRN0487($objUsuarioDTO);

        $this->logar('Criando Serviço CadastrarManifestacao NA BASE DO SEI...');
        $objServicoDTO = new ServicoDTO();
        $objServicoDTO->setNumIdServico(null);
        $objServicoDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
        $objServicoDTO->setStrIdentificacao('CadastrarManifestacao');
        $objServicoDTO->setStrDescricao('Cadastrar Manifestação Importada do sistema E-Ouv');
        $objServicoDTO->setStrServidor('*');
        $objServicoDTO->setStrSinLinkExterno('N');
        $objServicoDTO->setStrSinAtivo('S');
        $objServicoRN = new ServicoRN();
        $objServicoDTO = $objServicoRN->cadastrar($objServicoDTO);

        $this->logar('Criando Operação NA BASE DO SEI...');
        $objOperacaoServicoDTO = new OperacaoServicoDTO();
        $objOperacaoServicoDTO->setNumIdOperacaoServico(null);
        $objOperacaoServicoDTO->setNumIdServico($objServicoDTO->getNumIdServico());
        $objOperacaoServicoDTO->setNumStaOperacaoServico(0); //Gerar Procedimento
        $objOperacaoServicoDTO->setNumIdUnidade(null);
        $objOperacaoServicoDTO->setNumIdSerie(null);
        $objOperacaoServicoDTO->setNumIdTipoProcedimento(null);
        $objOperacaoServicoRN = new OperacaoServicoRN();
        $objOperacaoServicoDTO = $objOperacaoServicoRN->cadastrar($objOperacaoServicoDTO);
    }
}