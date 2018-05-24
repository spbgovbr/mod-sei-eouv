<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 15/12/2011 - criado por tamir_db
*
* Versão do Gerador de Código: 1.32.1
*
* Versão no CVS: $Id$
*/

//try {

  require_once dirname(__FILE__).'/../../../SEI.php';
  
  session_start();

  //////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(true);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->prepararSelecao('servico_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $strParametros = '&id_relatorio_importacao='.$_GET['id_relatorio_importacao'];

  switch($_GET['acao']){

        case 'md_cgu_eouv_relatorio_importacao_excluir':

          try{
            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            $arrObjRelatorioImportacaoDetalheDTO = array();
            for ($i=0;$i<count($arrStrIds);$i++){
              $objEouvRelatorioImportacaoDetalheDTO = new MdCguEouvRelatorioImportacaoDetalheDTO();
              $objEouvRelatorioImportacaoDetalheDTO->setNumIdRelatorioImportacao($_POST['hdnIdRelatorioImportacao']);
              $objEouvRelatorioImportacaoDetalheDTO->setStrProtocoloFormatado($arrStrIds[$i]);
              $arrObjEouvRelatorioImportacaoDetalheDTO[] = $objEouvRelatorioImportacaoDetalheDTO;
            }
            $objEouvRelatorioImportacaoDetalheRN = new MdCguEouvRelatorioImportacaoDetalheRN();
            $objEouvRelatorioImportacaoDetalheRN->excluir($arrObjEouvRelatorioImportacaoDetalheDTO);
            PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
          }catch(Exception $e){
            PaginaSEI::getInstance()->processarExcecao($e);
          }
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].$strParametros));
          die;

        case 'md_cgu_eouv_relatorio_importacao_detalhar':
          $strTitulo = 'Detalhamento de Importações Realizadas';
          break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  if ($_GET['acao'] == 'infra_agendamento_tarefa_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  if ($_GET['acao'] == 'infra_agendamento_tarefa_listar' || $_GET['acao'] == 'infra_agendamento_tarefa_selecionar'){
    $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('infra_agendamento_tarefa_cadastrar');
    if ($bolAcaoCadastrar){
        $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=infra_agendamento_tarefa_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'])).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }
  }

  $objEouvRelatorioImportacaoDetalheDTO = new MdCguEouvRelatorioImportacaoDetalheDTO();
  $objEouvRelatorioImportacaoDetalheDTO->retNumIdRelatorioImportacao();
  $objEouvRelatorioImportacaoDetalheDTO->retStrProtocoloFormatado();
  $objEouvRelatorioImportacaoDetalheDTO->retStrSinSucesso();
  $objEouvRelatorioImportacaoDetalheDTO->retDthDthImportacao();
  $objEouvRelatorioImportacaoDetalheDTO->retStrDescricaoLog();

  $numIdRelatorio = $_GET['id_relatorio_importacao'];

  $objEouvRelatorioImportacaoDetalheDTO->setNumIdRelatorioImportacao($numIdRelatorio);

  PaginaSEI::getInstance()->prepararOrdenacao($objEouvRelatorioImportacaoDetalheDTO, 'ProtocoloFormatado', InfraDTO::$TIPO_ORDENACAO_ASC);
  PaginaSEI::getInstance()->prepararPaginacao($objEouvRelatorioImportacaoDetalheDTO);

  $objEouvRelatorioImportacaoDetalheRN = new MdCguEouvRelatorioImportacaoDetalheRN();
  $arrObjEouvRelatorioImportacaoDetalheDTO = $objEouvRelatorioImportacaoDetalheRN->listar($objEouvRelatorioImportacaoDetalheDTO);

  PaginaSEI::getInstance()->processarPaginacao($objEouvRelatorioImportacaoDetalheDTO);
  $numRegistros = count($arrObjEouvRelatorioImportacaoDetalheDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='md_cgu_eouv_relatorio_importacao_detalhar'){

      $bolAcaoConsultar = false;
      //$bolCheck = true;
      $bolAcaoExecutar = false;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_cgu_eouv_relatorio_importacao_excluir');
    }else{
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_cgu_eouv_relatorio_importacao_detalhe');

    }

    if ($bolAcaoExcluir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_cgu_eouv_relatorio_importacao_excluir&acao_origem='.$_GET['acao'].$strParametros);
    }

    $strResultado = '';

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }

    $strResultado .= '<th class="infraTh" width="15%">'.PaginaSEI::getInstance()->getThOrdenacao($objEouvRelatorioImportacaoDetalheDTO,'Protocolo Formatado','ProtocoloFormatado',$arrObjEouvRelatorioImportacaoDetalheDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSEI::getInstance()->getThOrdenacao($objEouvRelatorioImportacaoDetalheDTO,'Sucesso','SinSucesso',$arrObjEouvRelatorioImportacaoDetalheDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSEI::getInstance()->getThOrdenacao($objEouvRelatorioImportacaoDetalheDTO,'Data Importacao','DthImportacao',$arrObjEouvRelatorioImportacaoDetalheDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objEouvRelatorioImportacaoDetalheDTO,'Detalhe','DescricaoLog',$arrObjEouvRelatorioImportacaoDetalheDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="25%">Ações</th>'."\n";

    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td>'.PaginaSEI::getInstance()->getTrCheck($i,trim($arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getStrProtocoloFormatado()),trim($arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getStrProtocoloFormatado())).'</td>';
      }

      $strResultado .= '<td>'.trim($arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getStrProtocoloFormatado()).'</td>';
      $strResultado .= '<td align="center">'.$arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getStrSinSucesso().'</td>';
      $strResultado .= '<td align="center">'.$arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getDthDthImportacao().'</td>';
      $strResultado .= '<td align="center">'.$arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getStrDescricaoLog().'</td>';

      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i,$arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getNumIdRelatorioImportacao());

      $strId = $arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getNumIdRelatorioImportacao();
      //$strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjEouvRelatorioImportacaoDTO[$i]->getStrComando());

      $strResultado .= '<td align="center">';

      if ($bolAcaoExcluir){
        $strResultado .= '<a href="#ID-'.trim($arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getStrProtocoloFormatado()).'"  onclick="acaoExcluir(\''.trim($arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getStrProtocoloFormatado()).'\',\''.PaginaSEI::tratarHTML(trim($arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getStrProtocoloFormatado())).'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/excluir.gif" title="Excluir Registro" alt="Excluir Registro" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }

  if ($_GET['acao'] == 'infra_agendamento_tarefa_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'])).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }

/*}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
} */

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: '.PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo.' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
#lblHelp {position:absolute;left:0%;top:0%;width:95%;}

#lblStaPeriodicidadeExecucao {position:absolute;left:0%;top:0%;width:25%;}
#selStaPeriodicidadeExecucao {position:absolute;left:0%;top:40%;width:25%;}

tr.trVermelha{
background-color:#f59f9f; 
}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='infra_agendamento_tarefa_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  infraEfeitoTabelas();
}


function executarAgendamento(comando, link){
  if (confirm('Confirma execução do comando ' + comando + '?')){
    document.getElementById('frmInfraAgendamentoTarefaLista').action=link;
    document.getElementById('frmInfraAgendamentoTarefaLista').submit();
    infraExibirAviso(false);
  }
}

<? if ($bolAcaoExcluir){ ?>
  function acaoExcluir(id,desc){
  if (confirm("Confirma exclusão do Registro \""+desc+"\"?")){
  document.getElementById('hdnInfraItemId').value=id;
  document.getElementById('frmEouvRelatorioImportacaoLista').action='<?=$strLinkExcluir?>';
  document.getElementById('frmEouvRelatorioImportacaoLista').submit();
  }
  }

  function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
  alert('Nenhum Processo selecionado.');
  return;
  }
  if (confirm("Confirma exclusão dos Processos selecionados?")){
  document.getElementById('hdnInfraItemId').value='';
  document.getElementById('frmEouvRelatorioImportacaoLista').action='<?=$strLinkExcluir?>';
  document.getElementById('frmEouvRelatorioImportacaoLista').submit();
  }
  }
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmEouvRelatorioImportacaoLista" method="post" action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">

  <input type="hidden" id="hdnIdRelatorioImportacao" name="hdnIdRelatorioImportacao" value="<?=$numIdRelatorio?>" />

  <?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  //PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>