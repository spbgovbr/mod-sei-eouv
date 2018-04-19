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

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  switch($_GET['acao']){

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
  $objEouvRelatorioImportacaoDetalheDTO->setNumIdRelatorioImportacao($_GET['id_relatorio_importacao']);

  PaginaSEI::getInstance()->prepararOrdenacao($objEouvRelatorioImportacaoDetalheDTO, 'ProtocoloFormatado', InfraDTO::$TIPO_ORDENACAO_ASC);
  PaginaSEI::getInstance()->prepararPaginacao($objEouvRelatorioImportacaoDetalheDTO);

  $objEouvRelatorioImportacaoDetalheRN = new MdCguEouvRelatorioImportacaoDetalheRN();
  $arrObjEouvRelatorioImportacaoDetalheDTO = $objEouvRelatorioImportacaoDetalheRN->listar($objEouvRelatorioImportacaoDetalheDTO);

  PaginaSEI::getInstance()->processarPaginacao($objEouvRelatorioImportacaoDetalheDTO);
  $numRegistros = count($arrObjEouvRelatorioImportacaoDetalheDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='md_cgu_eouv_relatorio_importacao_detalhar'){
      $bolAcaoConsultar = false;//SessaoSEI::getInstance()->verificarPermissao('infra_agendamento_tarefa_consultar');
      //$bolCheck = true;
      $bolAcaoExecutar = false;

    }else{
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_cgu_eouv_relatorio_importacao_detalhe');

    }

    $strResultado = '';

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';

    $strResultado .= '<th class="infraTh" width="15%">'.PaginaSEI::getInstance()->getThOrdenacao($objEouvRelatorioImportacaoDetalheDTO,'Protocolo Formatado','ProtocoloFormatado',$arrObjEouvRelatorioImportacaoDetalheDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSEI::getInstance()->getThOrdenacao($objEouvRelatorioImportacaoDetalheDTO,'Sucesso','SinSucesso',$arrObjEouvRelatorioImportacaoDetalheDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSEI::getInstance()->getThOrdenacao($objEouvRelatorioImportacaoDetalheDTO,'Data Importacao','DthImportacao',$arrObjEouvRelatorioImportacaoDetalheDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objEouvRelatorioImportacaoDetalheDTO,'Detalhe','DescricaoLog',$arrObjEouvRelatorioImportacaoDetalheDTO).'</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td>'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjEouvRelatorioImportacaoDTO[$i]->getNumIdInfraAgendamentoTarefa(),$arrObjEouvRelatorioImportacaoDTO[$i]->getNumIdRelatorioImportacao()).'</td>';
      }

      $strResultado .= '<td>'.$arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getStrProtocoloFormatado().'</td>';
      $strResultado .= '<td align="center">'.$arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getStrSinSucesso().'</td>';
      $strResultado .= '<td align="center">'.$arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getDthDthImportacao().'</td>';
      $strResultado .= '<td align="center">'.$arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getStrDescricaoLog().'</td>';

      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i,$arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getNumIdRelatorioImportacao());

      $strId = $arrObjEouvRelatorioImportacaoDetalheDTO[$i]->getNumIdRelatorioImportacao();
      //$strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjEouvRelatorioImportacaoDTO[$i]->getStrComando());

      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_cgu_eouv_relatorio_importacao_detalhar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_reatorio_importacao='.$arrObjEouvRelatorioImportacaoDTO[$i]->getNumIdRelatorioImportacao())).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/consultar.gif" title="Detalhar Importação" alt="Detalhar Importacação" class="infraImg" /></a>&nbsp;';
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

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmEouvRelatorioImportacaoLista" method="post" action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
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