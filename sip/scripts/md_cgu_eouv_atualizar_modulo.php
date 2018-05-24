<?php
/**
 * Created by PhpStorm.
 * User: flaviomy
 * Date: 26/10/2017
 * Time: 17:35
 */

require_once dirname(__FILE__).'/../web/Sip.php';

class MdCguEouvAtualizadorSipRN extends InfraRN{

    private $numSeg = 0;
    private $versaoAtualDesteModulo = '2.0.5';
    private $nomeDesteModulo = 'EOUV - Integração com sistema E-ouv';
    private $historicoVersoes = array('1.0.0');
    private $prefixoParametro = 'MD_CGU_EOUV';
    private $nomeParametroModulo = 'VERSAO_MODULO_CGU_EOUV';
    private $numIdOrgao = 0;
    private $numHierarquia = 100000018;

    public function __construct(){
        parent::__construct();
        $this->inicializar(' SIP - INICIALIZAR ');
    }

    protected function inicializarObjInfraIBanco(){
        return BancoSip::getInstance();
    }

    private function inicializar($strTitulo){

        ini_set('max_execution_time','0');
        ini_set('memory_limit','-1');

        try {
            @ini_set('zlib.output_compression','0');
            @ini_set('implicit_flush', '1');
        }catch(Exception $e){}

        ob_implicit_flush();

        InfraDebug::getInstance()->setBolLigado(true);
        InfraDebug::getInstance()->setBolDebugInfra(true);
        InfraDebug::getInstance()->setBolEcho(true);
        InfraDebug::getInstance()->limpar();

        $this->numSeg = InfraUtil::verificarTempoProcessamento();

        $this->logar($strTitulo);
    }

    private function logar($strMsg){
        InfraDebug::getInstance()->gravar($strMsg);
        flush();
    }

    private function finalizar($strMsg=null, $bolErro){

        if (!$bolErro) {
            $this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
            $this->logar('TEMPO TOTAL DE EXECUÇÃO: ' . $this->numSeg . ' s');
        }else{
            $strMsg = 'ERRO: '.$strMsg;
        }

        if ($strMsg!=null){
            $this->logar($strMsg);
        }

        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(false);
        $this->numSeg = 0;
        die;
    }

    protected function atualizarVersaoConectado(){

        try{
            $this->inicializar('INICIANDO ATUALIZACAO DO MODULO '. $this->nomeDesteModulo .' NO SIP VERSAO '.SIP_VERSAO);

            //testando versao do framework
            //checando BDs suportados
            if (!(BancoSip::getInstance() instanceof InfraMySql) &&
                !(BancoSip::getInstance() instanceof InfraSqlServer) &&
                !(BancoSip::getInstance() instanceof InfraOracle)){
                $this->finalizar('BANCO DE DADOS NAO SUPORTADO: '.get_parent_class(BancoSip::getInstance()),true);
            }

            //checando permissoes na base de dados
            $objInfraMetaBD = new InfraMetaBD(BancoSip::getInstance());

            if (count($objInfraMetaBD->obterTabelas('sip_teste'))==0){
                BancoSip::getInstance()->executarSql('CREATE TABLE sip_teste (id '.$objInfraMetaBD->tipoNumero().' null)');
            }

            BancoSip::getInstance()->executarSql('DROP TABLE sip_teste');

            $objInfraParametro = new InfraParametro(BancoSip::getInstance());

            $strVersaoModuloPeticionamento = $objInfraParametro->getValor($this->nomeParametroModulo, false);

            //VERIFICANDO QUAL VERSAO DEVE SER INSTALADA NESTA EXECUCAO
            //se nao tem nenhuma versao instalada, instalar todas
            if (InfraString::isBolVazia($strVersaoModuloPeticionamento)){
                $this->instalarv100();
                $this->logar('ATUALIZAÇÔES DA VERSÃO '. $this->versaoAtualDesteModulo .' DO MODULO '. $this->nomeDesteModulo .' INSTALADAS COM SUCESSO NA BASE DO SIP');
                $this->finalizar('FIM', false);
            }

            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);

        } catch(Exception $e){
            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);
            throw new InfraException('Erro atualizando versão.', $e);
        }

    }

    protected function instalarv100(){

        $objSistemaRN = new SistemaRN();
        $objPerfilRN = new PerfilRN();
        $objMenuRN = new MenuRN();
        $objItemMenuRN = new ItemMenuRN();
        $objRecursoRN = new RecursoRN();

        $objSistemaDTO = new SistemaDTO();
        $objSistemaDTO->retNumIdSistema();
        $objSistemaDTO->setStrSigla('SEI');

        $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);

        if ($objSistemaDTO == null){
            throw new InfraException('Sistema SEI não encontrado.');
        }

        $numIdSistemaSei = $objSistemaDTO->getNumIdSistema();

        $objPerfilDTO = new PerfilDTO();
        $objPerfilDTO->retNumIdPerfil();
        $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
        $objPerfilDTO->setStrNome('Administrador');
        $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);

        if ($objPerfilDTO == null){
            throw new InfraException('Perfil Administrador do sistema SEI não encontrado.');
        }

        $this->logar('ATUALIZANDO RECURSOS, MENUS E PERFIS DO MODULO '. $this->nomeDesteModulo .' NA BASE DO SIP...');

        $numIdPerfilSeiAdministrador = $objPerfilDTO->getNumIdPerfil();
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador,
            'md_cgu_eouv_relatorio_importacao', 'Relatório de importação de manifestações do EOUV',
            'controlador.php?acao=md_cgu_eouv_relatorio_importacao');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador,
            'md_cgu_eouv_depara_importacao_consultar', 'Consulta a tabela DePara referente a importação de Tipo de manifestação',
            'controlador.php?acao=md_cgu_eouv_depara_importacao_consultar');
        $numIdRecursoIntegracaoSei = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador,
            'md_cgu_eouv_integracao_sei', 'Integração entre E-ouv e SEI',
            'controlador.php?acao=md_cgu_eouv_integracao_sei');

        $this->logar('Valor id objeto' . $numIdRecursoIntegracaoSei);

        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador,
            'md_cgu_eouv_relatorio_importacao_detalhar', 'Relatório Detalhado de importação de manifestações do EOUV',
            'controlador.php?acao=md_cgu_eouv_relatorio_importacao_detalhar');
		$this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador,
            'md_cgu_eouv_relatorio_importacao_excluir', 'Excluir Item no Relatório Detalhado de importação de manifestações do EOUV',
            'controlador.php?acao=md_cgu_eouv_relatorio_importacao_excluir');
        $numIdRecursoRelatorioImportacaoEouvSei = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador,
            'md_cgu_eouv_relatorio_importacao_listar', 'Relatório de importação de manifestações do EOUV',
            'controlador.php?acao=md_cgu_eouv_relatorio_importacao_listar');

        $objMenuDTO = new MenuDTO();
        $objMenuDTO->retNumIdMenu();
        $objMenuDTO->setNumIdSistema($numIdSistemaSei);
        $objMenuDTO->setStrNome('Principal');
        $objMenuDTO = $objMenuRN->consultar($objMenuDTO);

        if ($objMenuDTO == null){
            throw new InfraException('Menu do sistema SEI não encontrado.');
        }
        $numIdMenuSei = $objMenuDTO->getNumIdMenu();

        $menuEouv = $this->adicionarItemMenu($numIdSistemaSei, $numIdPerfilSeiAdministrador,
            $numIdMenuSei, null, null, 'E-Ouv',
            'Integração entre E-ouv e SEI', 1100);

        $this->adicionarItemMenu($numIdSistemaSei, $numIdPerfilSeiAdministrador,
            $numIdMenuSei, $menuEouv->getNumIdItemMenu(),
            $numIdRecursoRelatorioImportacaoEouvSei, 'Importação de Manifestação', 'Relatório de Importação de Manifestação', 10);
    }

    private function adicionarItemMenu($numIdSistema, $numIdPerfil, $numIdMenu, $numIdItemMenuPai, $numIdRecurso, $strRotulo, $strDescricao, $numSequencia ){

        $objItemMenuDTO = new ItemMenuDTO();
        $objItemMenuDTO->retNumIdItemMenu();
        $objItemMenuDTO->setNumIdMenu($numIdMenu);
        $objItemMenuDTO->setNumIdMenuPai($numIdMenu);

        if ($numIdItemMenuPai==null){

            $objItemMenuDTO->setNumIdItemMenuPai(null);
        }else{
            $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuPai);
        }

        $objItemMenuDTO->setNumIdSistema($numIdSistema);
        $objItemMenuDTO->setNumIdRecurso($numIdRecurso);
        $objItemMenuDTO->setStrRotulo($strRotulo);

        $objItemMenuRN = new ItemMenuRN();
        $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

        if ($objItemMenuDTO==null){
            $objItemMenuDTO = new ItemMenuDTO();
            $objItemMenuDTO->setNumIdItemMenu(null);
            $objItemMenuDTO->setNumIdMenu($numIdMenu);

            if ($numIdItemMenuPai==null){
                $objItemMenuDTO->setNumIdMenuPai(null);
                $objItemMenuDTO->setNumIdItemMenuPai(null);
            }else{
                $objItemMenuDTO->setNumIdMenuPai($numIdMenu);
                $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuPai);
            }

            $objItemMenuDTO->setNumIdSistema($numIdSistema);
            $objItemMenuDTO->setNumIdRecurso($numIdRecurso);
            $objItemMenuDTO->setStrRotulo($strRotulo);
            $objItemMenuDTO->setStrDescricao($strDescricao);
            $objItemMenuDTO->setNumSequencia($numSequencia);
            $objItemMenuDTO->setStrSinNovaJanela('N');
            $objItemMenuDTO->setStrSinAtivo('S');

            $objItemMenuDTO = $objItemMenuRN->cadastrar($objItemMenuDTO);
        }

        if ($numIdPerfil!=null && $numIdRecurso!=null){
            $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
            $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilRecursoDTO->setNumIdPerfil($numIdPerfil);
            $objRelPerfilRecursoDTO->setNumIdRecurso($numIdRecurso);

            $objRelPerfilRecursoRN = new RelPerfilRecursoRN();

            if ($objRelPerfilRecursoRN->contar($objRelPerfilRecursoDTO)==0){
                $objRelPerfilRecursoRN->cadastrar($objRelPerfilRecursoDTO);
            }

            $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
            $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfil);
            $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilItemMenuDTO->setNumIdRecurso($numIdRecurso);
            $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
            $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());

            $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();

            if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO)==0){
                $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
            }
        }

        return $objItemMenuDTO;

    }

    private function adicionarRecursoPerfil($numIdSistema, $numIdPerfil, $strNome, $strDescricao, $strCaminho = null){

        $objRecursoDTO = new RecursoDTO();
        $objRecursoDTO->retNumIdRecurso();
        $objRecursoDTO->setNumIdSistema($numIdSistema);
        $objRecursoDTO->setStrNome($strNome);

        $objRecursoRN = new RecursoRN();
        $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

        if ($objRecursoDTO==null){
            $objRecursoDTO = new RecursoDTO();
            $objRecursoDTO->setNumIdRecurso(null);
            $objRecursoDTO->setNumIdSistema($numIdSistema);
            $objRecursoDTO->setStrNome($strNome);
            $objRecursoDTO->setStrDescricao($strDescricao);

            if ($strCaminho == null){
                $objRecursoDTO->setStrCaminho('controlador.php?acao='.$strNome);
            }else{
                $objRecursoDTO->setStrCaminho($strCaminho);
            }
            $objRecursoDTO->setStrSinAtivo('S');
            $objRecursoDTO = $objRecursoRN->cadastrar($objRecursoDTO);
        }

        if ($numIdPerfil!=null){
            $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
            $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilRecursoDTO->setNumIdPerfil($numIdPerfil);
            $objRelPerfilRecursoDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());

            $objRelPerfilRecursoRN = new RelPerfilRecursoRN();

            if ($objRelPerfilRecursoRN->contar($objRelPerfilRecursoDTO)==0){
                $objRelPerfilRecursoRN->cadastrar($objRelPerfilRecursoDTO);
            }
        }

        return $objRecursoDTO->getNumIdRecurso();

    }


}


//========================= INICIO SCRIPT EXECUÇAO =============

try{

    session_start();

    SessaoSip::getInstance(false);

    $objVersaoRN = new MdCguEouvAtualizadorSipRN();
    $objVersaoRN->atualizarVersao();

}catch(Exception $e){
    echo(nl2br(InfraException::inspecionar($e)));
    try{LogSip::getInstance()->gravar(InfraException::inspecionar($e));}catch(Exception $e){}
}

//========================== FIM SCRIPT EXECUÇÂO ====================
?>