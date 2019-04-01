<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 12/02/2008 - criado por marcio_db
 *
 * Versão do Gerador de Código: 1.13.1
 *
 * Versão no CVS: $Id$
 */

require_once dirname(__FILE__).'/../../../../SEI.php';

class MdCguEouvParametroRN extends InfraRN {

    public function __construct(){
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    protected function cadastrarParametroControlado(MdCguEouvParametroDTO $objEouvParametroDTO) {
        try{

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('md_cgu_eouv_parametro',__METHOD__,$objEouvParametroDTO);

            //Regras de Negocio
            $objInfraException = new InfraException();

            $objInfraException->lancarValidacoes();

            $objEouvParametroBD = new MdEouvParametroBD($this->getObjInfraIBanco());
            $ret = $objEouvParametroBD->cadastrar($objEouvParametroDTO);

            //Auditoria

            return $ret;

        }catch(Exception $e){
            throw new InfraException('Erro cadastrando Paramêtro do módulo Sei x E-Ouv.',$e);
        }
    }

    protected function alterarParametroControlado(MdCguEouvParametroDTO $objEouvParametroDTO){
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('md_cgu_eouv_parametro_alterar',__METHOD__,$objEouvParametroDTO);

            $objEouvParametroBD = new MdCguEouvParametroBD($this->getObjInfraIBanco());
            $objEouvParametroBD->alterar($objEouvParametroDTO);

            //Auditoria

        }catch(Exception $e){
            throw new InfraException('Erro alterando parâmetro.',$e);
        }
    }

    protected function excluirRN0224Controlado($arrObjMdCguEouvParametroDTO){
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('rel_protocolo_assunto_excluir',__METHOD__,$arrObjMdCguEouvParametroDTO);

            //Regras de Negocio
            //$objInfraException = new InfraException();

            //$objInfraException->lancarValidacoes();

            $objEouvParametroBD = new RelProtocoloAssuntoBD($this->getObjInfraIBanco());
            for($i=0;$i<count($arrObjMdCguEouvParametroDTO);$i++){
                $objEouvParametroBD->excluir($arrObjMdCguEouvParametroDTO[$i]);
            }

            //Auditoria

        }catch(Exception $e){
            throw new InfraException('Erro excluindo .',$e);
        }
    }

    
    protected function consultarParametroConectado(MdCguEouvParametroDTO $objEouvParametroDTO){
      try {

        //Valida Permissao
        SessaoSEI::getInstance()->validarAuditarPermissao('md_cgu_eouv_parametro_consultar');

        $objEouvParametroBD = new MdCguEouvParametroBD($this->getObjInfraIBanco());
        $ret = $objEouvParametroBD->consultar($objEouvParametroDTO);

        //Auditoria

        return $ret;
      }catch(Exception $e){
        throw new InfraException('Erro consultando Parâmetro do módulo de integração SEI x E-ouv.',$e);
      }
    }


    protected function listarParametroConectado(MdCguEouvParametroDTO $objEouvParametroDTO) {
        try {

            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('md_cgu_eouv_parametro_listar',__METHOD__,$objEouvParametroDTO);

            $objEouvParametroBD = new MdCguEouvParametroBD($this->getObjInfraIBanco());
            $ret = $objEouvParametroBD->listar($objEouvParametroDTO);


            //Auditoria

            return $ret;

        }catch(Exception $e){
            throw new InfraException('Erro listando associações entre Protocolo e Assunto.',$e);
        }
    }



}
?>