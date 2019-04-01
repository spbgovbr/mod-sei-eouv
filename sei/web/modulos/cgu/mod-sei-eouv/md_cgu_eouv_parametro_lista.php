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

try {
  require_once dirname(__FILE__).'/../../../SEI.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(true);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->prepararSelecao('md_cgu_eouv_parametro_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  switch($_GET['acao']){
    case 'md_cgu_eouv_parametro_excluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjMdCguEouvParametroDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objMdCguEouvParametroDTO = new MdCguEouvParametroDTO();
          $objMdCguEouvParametroDTO->setNumIdParametro($arrStrIds[$i]);          
          $arrObjMdCguEouvParametroDTO[] = $objMdCguEouvParametroDTO;
        }
        $objMdCguEouvParametroRN = new MdCguEouvParametroRN();
        $objMdCguEouvParametroRN->excluirParametro($arrObjMdCguEouvParametroDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'md_cgu_eouv_parametro_selecionar':
      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Paramêtro','Selecionar Paramêtros');

      //Se cadastrou alguem
      if ($_GET['acao_origem']=='md_cgu_eouv_parametro_cadastrar'){
        if (isset($_GET['id_md_cgu_eouv_parametro'])){
          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_md_cgu_eouv_parametro']);
        }
      }
      break;

    case 'md_cgu_eouv_parametro_listar':
      $strTitulo = 'Paramêtros do Módulo de Integração SEI x E-Ouv';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  if ($_GET['acao'] == 'md_cgu_eouv_parametro_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  if ($_GET['acao'] == 'md_cgu_eouv_parametro_listar' || $_GET['acao'] == 'md_cgu_eouv_parametro_selecionar'){
    $bolAcaoCadastrar = false; //SessaoSEI::getInstance()->verificarPermissao('md_cgu_eouv_parametro_cadastrar');
    if ($bolAcaoCadastrar){
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_cgu_eouv_parametro_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }
  }
   
  $objMdCguEouvParametroDTO = new MdCguEouvParametroDTO();
  $objMdCguEouvParametroDTO->retNumIdParametro();
  $objMdCguEouvParametroDTO->retStrNoParametro();
  $objMdCguEouvParametroDTO->retStrDeValorParametro();

  PaginaSEI::getInstance()->prepararOrdenacao($objMdCguEouvParametroDTO, 'IdParametro', InfraDTO::$TIPO_ORDENACAO_ASC);
  //PaginaSEI::getInstance()->prepararPaginacao($objMdCguEouvParametroDTO);

  $objMdCguEouvParametroRN = new MdCguEouvParametroRN();
  $arrObjMdCguEouvParametroDTO = $objMdCguEouvParametroRN->listarParametro($objMdCguEouvParametroDTO);

  //PaginaSEI::getInstance()->processarPaginacao($objMdCguEouvParametroDTO);
  $numRegistros = count($arrObjMdCguEouvParametroDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    $bolAcaoReativar = false;
    $bolAcaoConsultar = false;
    $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_cgu_eouv_parametro_alterar');
    $bolAcaoImprimir = false;
    $bolAcaoExcluir = false; //SessaoSEI::getInstance()->verificarPermissao('md_cgu_eouv_parametro_excluir');
    $bolAcaoDesativar = false;

    if ($bolAcaoDesativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="T" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_cgu_eouv_parametro_desativar&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoReativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_cgu_eouv_parametro_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }
    
    if ($bolAcaoExcluir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_cgu_eouv_parametro_excluir&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    }

    $strResultado = '';

    if ($_GET['acao']!='md_cgu_eouv_parametro_reativar'){
      $strSumarioTabela = 'Tabela de Paramêtros.';
      $strCaptionTabela = 'Paramêtros';
    }else{
      $strSumarioTabela = 'Tabela de Paramêtros Inativos.';
      $strCaptionTabela = 'Paramêtros Inativos';
    }

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objMdCguEouvParametroDTO,'ID','IdParametro',$arrObjMdCguEouvParametroDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objMdCguEouvParametroDTO,'Nome','NoParametro',$arrObjMdCguEouvParametroDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">Descrição</th>'."\n";
    $strResultado .= '<th class="infraTh">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td valign="top">'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjMdCguEouvParametroDTO[$i]->getNumIdParametro(),$arrObjMdCguEouvParametroDTO[$i]->getStrNoParametro()).'</td>';
      }
      $strResultado .= '<td width="10%" align="center">'.$arrObjMdCguEouvParametroDTO[$i]->getNumIdParametro().'</td>';
      $strResultado .= '<td width="30%">'.PaginaSEI::tratarHTML($arrObjMdCguEouvParametroDTO[$i]->getStrNoParametro()).'</td>';
      $strResultado .= '<td>'.nl2br(PaginaSEI::tratarHTML($arrObjMdCguEouvParametroDTO[$i]->getStrDeValorParametro())).'</td>';
      $strResultado .= '<td width="15%" align="center">';
      
      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i,$arrObjMdCguEouvParametroDTO[$i]->getNumIdParametro());
      
      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_cgu_eouv_parametro_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_md_cgu_eouv_parametro='.$arrObjMdCguEouvParametroDTO[$i]->getNumIdParametro()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/consultar.gif" title="Consultar Paramêtro" alt="Consultar Paramêtro" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_cgu_eouv_parametro_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_md_cgu_eouv_parametro='.$arrObjMdCguEouvParametroDTO[$i]->getNumIdParametro()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/alterar.gif" title="Alterar Paramêtro" alt="Alterar Paramêtro" class="infraImg" /></a>&nbsp;';
      }


      if ($bolAcaoDesativar){
        $strResultado .= '<a href="#ID-'.$arrObjMdCguEouvParametroDTO[$i]->getNumIdParametro().'"  onclick="acaoDesativar(\''.$arrObjMdCguEouvParametroDTO[$i]->getNumIdParametro().'\',\''.$arrObjMdCguEouvParametroDTO[$i]->getStrNoParametro().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/desativar.gif" title="Desativar Paramêtro" alt="Desativar Paramêtro" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar){
        $strResultado .= '<a href="#ID-'.$arrObjMdCguEouvParametroDTO[$i]->getNumIdParametro().'"  onclick="acaoReativar(\''.$arrObjMdCguEouvParametroDTO[$i]->getNumIdParametro().'\',\''.$arrObjMdCguEouvParametroDTO[$i]->getStrNoParametro().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/reativar.gif" title="Reativar Paramêtro" alt="Reativar Paramêtro" class="infraImg" /></a>&nbsp;';
      }


      if ($bolAcaoExcluir){
        $strResultado .= '<a href="#ID-'.$arrObjMdCguEouvParametroDTO[$i]->getNumIdParametro().'"  onclick="acaoExcluir(\''.$arrObjMdCguEouvParametroDTO[$i]->getNumIdParametro().'\',\''.$arrObjMdCguEouvParametroDTO[$i]->getStrNoParametro().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/excluir.gif" title="Excluir Paramêtro" alt="Excluir Paramêtro" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  if ($_GET['acao'] == 'md_cgu_eouv_parametro_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
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
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){

  if ('<?=$_GET['acao']?>'=='md_cgu_eouv_parametro_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
 }
  
  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  if (confirm("Confirma desativação do Paramêtro \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmMdCguEouvParametroLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmMdCguEouvParametroLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Paramêtro selecionado.');
    return;
  }
  if (confirm("Confirma desativação dos Paramêtros selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmMdCguEouvParametroLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmMdCguEouvParametroLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc){
  if (confirm("Confirma reativação do Paramêtro \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmMdCguEouvParametroLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmMdCguEouvParametroLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Paramêtro selecionado.');
    return;
  }
  if (confirm("Confirma reativação dos Paramêtros selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmMdCguEouvParametroLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmMdCguEouvParametroLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  if (confirm("Confirma exclusão do Paramêtro \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmMdCguEouvParametroLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmMdCguEouvParametroLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum Paramêtro selecionado.');
    return;
  }
  if (confirm("Confirma exclusão dos Paramêtros selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmMdCguEouvParametroLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmMdCguEouvParametroLista').submit();
  }
}
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmMdCguEouvParametroLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  //PaginaSEI::getInstance()->abrirAreaDados('5em');
  //PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  //PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>