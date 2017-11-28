<?php
/**
 * CONTROLADORIA GERAL DA UNIAO
 *
 * 03/10/2017 - criado por rafael.ferreira@cgu.gov.br
 *
 */

class MdCguEouvIntegracao extends SeiIntegracao
{

//    public function __construct()
//    {
//    }

    public function getNome()
    {
        return 'Mуdulo de Integraзгo entre o sistema SEI e o E-ouv(Sistema de Ouvidorias)';
    }

    public function getVersao()
    {
        return '2.0.0';
    }

    public function getInstituicao()
    {
        return 'CGU - Controladoria Geral da Uniгo';
    }

//    public function inicializar($strVersaoSEI)
//    {
//        /*
//        if (substr($strVersaoSEI, 0, 2) != '3.'){
//          die('Mуdulo "'.$this->getNome().'" ('.$this->getVersao().') nгo й compatнvel com esta versгo do SEI ('.$strVersaoSEI.').');
//        }
//        */
//    }

    public function processarControladorWebServices($strServico)
    {
        $strArq = null;
        switch ($strServico) {
            case 'eouv':
                $strArq = 'eouv.wsdl';
                break;
        }

        if ($strArq!=null){
            $strArq = dirname(__FILE__).'/ws/'.$strArq;
        }
        return $strArq;
    }

    public function processarControlador($strAcao)
    {

        switch($strAcao) {

            case 'md_cgu_eouv_relatorio_importacao_listar':
                require_once dirname(__FILE__).'/md_cgu_eouv_relatorio_importacao.php';
                return true;

            case 'md_cgu_eouv_relatorio_importacao_detalhar':
                require_once dirname(__FILE__).'/md_cgu_eouv_relatorio_importacao_detalhar.php';
                return true;

            case 'md_cgu_eouv_integracao_sei':
                require_once dirname(__FILE__).'/md_cgu_eouv_relatorio_importacao.php';
                return true;
        }
        return false;

    }
}

?>