<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 *
 * 17/12/2007 - criado por fbv
 *
 * Versão do Gerador de Código: 1.10.1
 *
 * Versão no CVS: $Id$
 */

error_reporting(E_ALL); ini_set('display_errors', '1');

try {
  require_once dirname(__FILE__).'/../../../SEI.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(true);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->verificarSelecao('md_cgu_eouv_parametro_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $objMdCguEouvParametroDTO = new MdCguEouvParametroDTO();

  $strDesabilitar = '';

  $arrComandos = array();

  switch($_GET['acao']){
    case 'md_cgu_eouv_parametro_cadastrar':
      $strTitulo = 'Novo Parâmetro';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarMdCguEouvParametro" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      $objMdCguEouvParametroDTO->setNumIdParametro(null);
      $objMdCguEouvParametroDTO->setStrNoParametro($_POST['txtNome']);
      $objMdCguEouvParametroDTO->setStrValorParametro($_POST['txaDescricao']);

      if (PaginaSEI::getInstance()->getCheckbox($_POST['chkSinPesquisaCompleta']) == 'S'){
        $objMdCguEouvParametroDTO->setStrStaAcesso(MdCguEouvParametroRN::$TA_CONSULTA_COMPLETA);
      }else if (PaginaSEI::getInstance()->getCheckbox($_POST['chkSinPesquisaResumida']) == 'S'){
        $objMdCguEouvParametroDTO->setStrStaAcesso(MdCguEouvParametroRN::$TA_CONSULTA_RESUMIDA);
      }else{
        $objMdCguEouvParametroDTO->setStrStaAcesso(MdCguEouvParametroRN::$TA_NENHUM);
      }

      $objMdCguEouvParametroDTO->setStrSinSistema('N');
      $objMdCguEouvParametroDTO->setStrSinAtivo('S');

      $arrUnidadesAlteracao = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnUnidadesAlteracao']);

      $arrObjRelUnidadeMdCguEouvParametroDTO = array();
      foreach($arrUnidadesAlteracao as $numIdUnidade){
        $objRelUnidadeMdCguEouvParametroDTO = new RelUnidadeMdCguEouvParametroDTO();
        $objRelUnidadeMdCguEouvParametroDTO->setNumIdUnidade($numIdUnidade);
        $objRelUnidadeMdCguEouvParametroDTO->setStrStaAcesso(MdCguEouvParametroRN::$TA_ALTERACAO);
        $arrObjRelUnidadeMdCguEouvParametroDTO[] = $objRelUnidadeMdCguEouvParametroDTO;
      }

      $arrUnidadesConsulta = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnUnidadesConsulta']);
      foreach($arrUnidadesConsulta as $numIdUnidade){
        $objRelUnidadeMdCguEouvParametroDTO = new RelUnidadeMdCguEouvParametroDTO();
        $objRelUnidadeMdCguEouvParametroDTO->setNumIdUnidade($numIdUnidade);
        $objRelUnidadeMdCguEouvParametroDTO->setStrStaAcesso(MdCguEouvParametroRN::$TA_CONSULTA_COMPLETA);
        $arrObjRelUnidadeMdCguEouvParametroDTO[] = $objRelUnidadeMdCguEouvParametroDTO;
      }

      $objMdCguEouvParametroDTO->setArrObjRelUnidadeMdCguEouvParametroDTO($arrObjRelUnidadeMdCguEouvParametroDTO);

      if (isset($_POST['sbmCadastrarMdCguEouvParametro'])) {
        try{
          $objMdCguEouvParametroRN = new MdCguEouvParametroRN();
          $objMdCguEouvParametroDTO = $objMdCguEouvParametroRN->cadastrarRN0334($objMdCguEouvParametroDTO);
          PaginaSEI::getInstance()->setStrMensagem('Parâmetro "'.$objMdCguEouvParametroDTO->getStrNoParametro().'" cadastrado com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_md_cgu_eouv_parametro='.$objMdCguEouvParametroDTO->getNumIdParametro().'#ID-'.$objMdCguEouvParametroDTO->getNumIdParametro()));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_cgu_eouv_parametro_alterar':
      $strTitulo = 'Alterar Parâmetro';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarMdCguEouvParametro" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';


      if (isset($_GET['id_md_cgu_eouv_parametro'])){
        $objMdCguEouvParametroDTO->setNumIdParametro($_GET['id_md_cgu_eouv_parametro']);
        $objMdCguEouvParametroDTO->retTodos();
        $objMdCguEouvParametroRN = new MdCguEouvParametroRN();
        $objMdCguEouvParametroDTO = $objMdCguEouvParametroRN->consultarParametro($objMdCguEouvParametroDTO);
        if ($objMdCguEouvParametroDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
      } else {

        $objMdCguEouvParametroDTO->setNumIdParametro($_POST['hdnIdMdCguEouvParametro']);
        $objMdCguEouvParametroDTO->setStrNoParametro($_POST['txtNome']);
        $objMdCguEouvParametroDTO->setStrDeValorParametro($_POST['txaDescricao']);

      }

      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'#ID-'.$objMdCguEouvParametroDTO->getNumIdParametro().'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarMdCguEouvParametro'])) {
        try{
          $objMdCguEouvParametroRN = new MdCguEouvParametroRN();
          $objMdCguEouvParametroRN->alterarParametro($objMdCguEouvParametroDTO);
          PaginaSEI::getInstance()->setStrMensagem('Parâmetro "'.$objMdCguEouvParametroDTO->getStrNoParametro().'" alterado com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'#ID-'.$objMdCguEouvParametroDTO->getNumIdParametro()));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_cgu_eouv_parametro_consultar':
      $strTitulo = "Consultar Parâmetro";
      $arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'#ID-'.$_GET['id_md_cgu_eouv_parametro'].'\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
      $objMdCguEouvParametroDTO->setNumIdParametro($_GET['id_md_cgu_eouv_parametro']);
      $objMdCguEouvParametroDTO->retTodos();
      $objMdCguEouvParametroRN = new MdCguEouvParametroRN();
      $objMdCguEouvParametroDTO = $objMdCguEouvParametroRN->consultarRN0336($objMdCguEouvParametroDTO);
      if ($objMdCguEouvParametroDTO===null){
        throw new InfraException("Registro não encontrado.");
      }
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();

?>

#divGeral {height:30em;}
#lblNome {position:absolute;left:0%;top:0%;width:70%;}
#txtNome {position:absolute;left:0%;top:6%;width:70%;}

#lblDescricao {position:absolute;left:0%;top:16%;width:70%;}
#txaDescricao {position:absolute;left:0%;top:22%;width:70%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();

?>
//<script>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='md_cgu_eouv_parametro_cadastrar'){
    document.getElementById('txtNome').focus();
  } else if ('<?=$_GET['acao']?>'=='md_cgu_eouv_parametro_consultar'){
    infraDesabilitarCamposAreaDados();
  }

}

function OnSubmitForm() {
  return ValidarCadastroParametro();
}

function ValidarCadastroParametro() {
  if (infraTrim(document.getElementById('txtNome').value)=='') {
    alert('Informe o Nome.');
    document.getElementById('txtNome').focus();
    return false;
  }
  return true;
}

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
  <form id="frmMdCguEouvParametroCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
    <?
    //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    //PaginaSEI::getInstance()->montarAreaValidacao();
    ?>

    <div id="divGeral" class="infraAreaDados">
      <label id="lblNome" for="txtNome" accesskey="N" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">N</span>ome:</label>
      <input type="text" id="txtNome" name="txtNome" class="infraText" value="<?=PaginaSEI::tratarHTML($objMdCguEouvParametroDTO->getStrNoParametro());?>" onkeypress="return infraMascaraTexto(this,event,50);" maxlength="50" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

      <label id="lblDescricao" for="txaDescricao" accesskey="V" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">V</span>alor Parâmetro:</label>
      <textarea id="txaDescricao" name="txaDescricao" onkeypress="return infraLimitarTexto(this,event,250);" rows='2' class="infraTextarea" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><?=PaginaSEI::tratarHTML($objMdCguEouvParametroDTO->getStrDeValorParametro());?></textarea>

    </div>

    <?
    //PaginaSEI::getInstance()->montarAreaDebug();
    //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
    ?>

    <input type="hidden" id="hdnIdMdCguEouvParametro" name="hdnIdMdCguEouvParametro" value="<?=$objMdCguEouvParametroDTO->getNumIdParametro();?>" />

  </form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>