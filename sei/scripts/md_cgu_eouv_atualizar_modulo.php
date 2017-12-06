<?
try{
    require_once dirname(__FILE__).'/../web/SEI.php';
    require_once dirname(__FILE__) . '/../web/modulos/cgu/mod-sei-eouv/rn/MdCguEouvAtualizadorBDRN.php';

    session_start();

    SessaoSEI::getInstance(false);

    $objVersaoRN = new MdCguEouvAtualizadorBDRN();
    $objVersaoRN->atualizarVersao();
    exit;

}catch(Exception $e){
    echo(InfraException::inspecionar($e));
    try{LogSEI::getInstance()->gravar(InfraException::inspecionar($e));	}catch (Exception $e){}
}
?>