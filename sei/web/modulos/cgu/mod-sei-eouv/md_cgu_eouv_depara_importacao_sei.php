<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 04/10/2012 - CRIADO POR MKR
*
*
*/

try {
  //require_once dirname(__FILE__).'/Infra.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  //SessaoInfra::getInstance()->validarSessao();
  SessaoInfra::getInstance()->validarLink();

  SessaoInfra::getInstance()->validarPermissao($_GET['acao']);
  
  $arrComandos = array();
  
  switch($_GET['acao']){
    case 'infra_banco_comparar':
      
      $strTitulo = 'Comparação de Bancos';
      $arrComandos[] = '<input type="submit" name="sbmVerificarComparacaoBancoDireta" value="Comparação Direta" class="infraButton" />';
      $arrComandos[] = '<input type="submit" name="sbmVerificarComparacaoBancoInversa" value="Comparação Inversa" class="infraButton" />';
      //$arrComandos[] = '<input type="button" name="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaInfra::getInstance()->formatarXHTML(SessaoInfra::getInstance()->assinarLink('controlador.php?acao=sistema_listar')).'\';" class="infraButton" />';
      	
      $objInfraComparacaoBancoDTO = new InfraComparacaoBancoDTO();

      $objInfraComparacaoBancoDTO->setStrServidorOrigem($_POST['txtServidorOrigem']);
      $objInfraComparacaoBancoDTO->setStrPortaOrigem($_POST['txtPortaOrigem']);
      $objInfraComparacaoBancoDTO->setStrBancoOrigem($_POST['txtBancoOrigem']);
      $objInfraComparacaoBancoDTO->setStrUsuarioOrigem($_POST['txtUsuarioOrigem']);
      $objInfraComparacaoBancoDTO->setStrSenhaOrigem($_POST['txtSenhaOrigem']);
      $objInfraComparacaoBancoDTO->setStrBancoDadosOrigem($_POST['selTipoBancoDadosOrigem']);

      $objInfraComparacaoBancoDTO->setStrServidorDestino($_POST['txtServidorDestino']);
      $objInfraComparacaoBancoDTO->setStrPortaDestino($_POST['txtPortaDestino']);
      $objInfraComparacaoBancoDTO->setStrBancoDestino($_POST['txtBancoDestino']);
      $objInfraComparacaoBancoDTO->setStrUsuarioDestino($_POST['txtUsuarioDestino']);
      $objInfraComparacaoBancoDTO->setStrSenhaDestino($_POST['txtSenhaDestino']);
      $objInfraComparacaoBancoDTO->setStrBancoDadosDestino($_POST['selTipoBancoDadosDestino']);

      $objInfraComparacaoBancoDTO->setArrTabelasIgnorar(explode("\n",$_POST['txaTabelasIgnorar']));
      
      $objInfraComparacaoBancoDTO->setStrSinComparaQtdeRegistrosTabela(PaginaInfra::getInstance()->getCheckbox($_POST['chkSinComparaQtdeRegistrosTabela']));
      $objInfraComparacaoBancoDTO->setStrSinComparaTipoColunasTabela(PaginaInfra::getInstance()->getCheckbox($_POST['chkSinComparaTipoColunasTabela']));
      $objInfraComparacaoBancoDTO->setStrSinComparaMaxIdTabela(PaginaInfra::getInstance()->getCheckbox($_POST['chkSinComparaMaxIdTabela']));
                  	
      if (isset($_POST['sbmVerificarComparacaoBancoDireta']) || isset($_POST['sbmVerificarComparacaoBancoInversa'])) {
        try{          
          
          if (isset($_POST['sbmVerificarComparacaoBancoInversa'])){
            $objInfraComparacaoBancoDTO->setStrServidorOrigem($_POST['txtServidorDestino']);
            $objInfraComparacaoBancoDTO->setStrPortaOrigem($_POST['txtPortaDestino']);
            $objInfraComparacaoBancoDTO->setStrBancoOrigem($_POST['txtBancoDestino']);
            $objInfraComparacaoBancoDTO->setStrUsuarioOrigem($_POST['txtUsuarioDestino']);
            $objInfraComparacaoBancoDTO->setStrSenhaOrigem($_POST['txtSenhaDestino']);
            $objInfraComparacaoBancoDTO->setStrBancoDadosOrigem($_POST['selTipoBancoDadosDestino']);
            
            $objInfraComparacaoBancoDTO->setStrServidorDestino($_POST['txtServidorOrigem']);
            $objInfraComparacaoBancoDTO->setStrPortaDestino($_POST['txtPortaOrigem']);
            $objInfraComparacaoBancoDTO->setStrBancoDestino($_POST['txtBancoOrigem']);
            $objInfraComparacaoBancoDTO->setStrUsuarioDestino($_POST['txtUsuarioOrigem']);
            $objInfraComparacaoBancoDTO->setStrSenhaDestino($_POST['txtSenhaOrigem']);
            $objInfraComparacaoBancoDTO->setStrBancoDadosDestino($_POST['selTipoBancoDadosOrigem']);
          }
          
          $objInfraComparacaoBancoRN = new InfraComparacaoBancoRN();
          
          $arrObjInfraComparacaoBancoDTO_Tabela = $objInfraComparacaoBancoRN->compararTabelas($objInfraComparacaoBancoDTO);
          
          $arrObjInfraComparacaoBancoDTO_Constraint = $objInfraComparacaoBancoRN->compararConstraints($objInfraComparacaoBancoDTO);

          $arrObjInfraComparacaoBancoDTO_Indice = $objInfraComparacaoBancoRN->compararIndices($objInfraComparacaoBancoDTO);

          if ($objInfraComparacaoBancoDTO->getStrSinComparaMaxIdTabela()=='S'){
            $arrObjInfraComparacaoBancoDTO_Sequencia = $objInfraComparacaoBancoRN->compararSequencias($objInfraComparacaoBancoDTO);
          }
                   
        }catch(Exception $e){
          PaginaInfra::getInstance()->processarExcecao($e);
        }
			}
      break;
      
    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

    
  $strResultado = '';
  
  $numRegistrosTabela = count($arrObjInfraComparacaoBancoDTO_Tabela);

  if ($numRegistrosTabela > 0){
          
    $strSumarioTabela = 'Tabela de Tabelas.';
    $strCaptionTabela = 'Tabelas';
    

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaInfra::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistrosTabela).'</caption>';
    $strResultado .= '<tr>';    
    $strResultado .= '<th class="infraTh" width="20%">Tabela</th>'."\n";
    $strResultado .= '<th class="infraTh">Colunas</th>'."\n";
      
    if ($objInfraComparacaoBancoDTO->getStrSinComparaQtdeRegistrosTabela() == 'S'){    
      $strResultado .= '<th class="infraTh" width="20%">Quantidade de Registros</th>'."\n";
    }
            
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistrosTabela; $i++){

      
      if ($arrObjInfraComparacaoBancoDTO_Tabela[$i]->getStrSinColunasTabelaDestinoOK() == 'S'
          && ($objInfraComparacaoBancoDTO->getStrSinComparaQtdeRegistrosTabela() == 'N' || $arrObjInfraComparacaoBancoDTO_Tabela[$i]->getStrSinQtdeRegistrosTabelaDestinoOK()=='S')){
        $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      }else{
        $strCssTr= '<tr class="trVermelha">';
      }           
      $strResultado .= $strCssTr;
      
      $strResultado .= '<td>'.$arrObjInfraComparacaoBancoDTO_Tabela[$i]->getStrNomeTabelaOrigem().'</td>';
      $strResultado .= '<td>'.$arrObjInfraComparacaoBancoDTO_Tabela[$i]->getStrColunasTabelaOrigem().'</td>';

      if ($objInfraComparacaoBancoDTO->getStrSinComparaQtdeRegistrosTabela() == 'S'){
        
        $strResultado .= '<td align="center">'.$arrObjInfraComparacaoBancoDTO_Tabela[$i]->getNumQtdeRegistrosTabelaOrigem();
        
        if ($arrObjInfraComparacaoBancoDTO_Tabela[$i]->getNumQtdeRegistrosTabelaOrigem()!=$arrObjInfraComparacaoBancoDTO_Tabela[$i]->getNumQtdeRegistrosTabelaDestino()){
          $strResultado .= ' &lt;&gt; '.$arrObjInfraComparacaoBancoDTO_Tabela[$i]->getNumQtdeRegistrosTabelaDestino();
        }
        $strResultado .= '</td>';
      }
      
      $strResultado .= '</tr>'."\n";
    }
    $strResultado .= '</table><br/>';
  }
  
  $numRegistrosConstraint = count($arrObjInfraComparacaoBancoDTO_Constraint);
  
  if ($numRegistrosConstraint > 0){
       
    $strSumarioTabela = 'Tabela de Constraints.';
    $strCaptionTabela = 'Constraints';
  
  
    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaInfra::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistrosConstraint).'</caption>';
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="20%">Tabela</th>'."\n";
    $strResultado .= '<th class="infraTh" width="20%">Constraint</th>'."\n";    
    $strResultado .= '<th class="infraTh">Colunas</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistrosConstraint; $i++){
      if ($arrObjInfraComparacaoBancoDTO_Constraint[$i]->getStrSinNomeColunasConstraintDestinoOK() == 'S'){
        $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      }else{
        $strCssTr= '<tr class="trVermelha">';
      }           
      $strResultado .= $strCssTr;
  
      $strResultado .= '<td>'.$arrObjInfraComparacaoBancoDTO_Constraint[$i]->getStrNomeTabelaOrigem().'</td>';
      $strResultado .= '<td>'.$arrObjInfraComparacaoBancoDTO_Constraint[$i]->getStrNomeConstraintOrigem().'</td>';
      $strResultado .= '<td>'.$arrObjInfraComparacaoBancoDTO_Constraint[$i]->getStrNomeColunasConstraintOrigem().'</td>';  
      $strResultado .= '</tr>'."\n";
    }
    $strResultado .= '</table><br />';
  }

  $numRegistrosIndice = count($arrObjInfraComparacaoBancoDTO_Indice);
  
  if ($numRegistrosIndice > 0){
       
    $strSumarioTabela = 'Tabela de Índices.';
    $strCaptionTabela = 'Índices';
  
  
    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaInfra::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistrosIndice).'</caption>';
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="20%">Tabela</th>'."\n";
    $strResultado .= '<th class="infraTh" width="20%">Índice</th>'."\n";    
    $strResultado .= '<th class="infraTh">Colunas</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistrosIndice; $i++){
      if ($arrObjInfraComparacaoBancoDTO_Indice[$i]->getStrSinColunasIndiceDestinoOK() == 'S'){
        $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      }else{
        $strCssTr= '<tr class="trVermelha">';
      }           
      $strResultado .= $strCssTr;
  
      $strResultado .= '<td>'.$arrObjInfraComparacaoBancoDTO_Indice[$i]->getStrNomeTabelaOrigem().'</td>';
      $strResultado .= '<td>'.PaginaInfra::getInstance()->formatarXHTML($arrObjInfraComparacaoBancoDTO_Indice[$i]->getStrNomeIndiceOrigem()).'</td>';  
      $strResultado .= '<td>'.$arrObjInfraComparacaoBancoDTO_Indice[$i]->getStrColunasIndiceOrigem().'</td>';  
      $strResultado .= '</tr>'."\n";
    }
    $strResultado .= '</table><br />';
  }

  if ($objInfraComparacaoBancoDTO->getStrSinComparaMaxIdTabela()=='S'){
    $numRegistrosSequencia = count($arrObjInfraComparacaoBancoDTO_Sequencia);
    
    if ($numRegistrosSequencia > 0){
       
      $strSumarioTabela = 'Tabela de Sequências.';
      $strCaptionTabela = 'Sequências';
    
    
      $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
      $strResultado .= '<caption class="infraCaption">'.PaginaInfra::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistrosSequencia).'</caption>';
      $strResultado .= '<tr>';
      $strResultado .= '<th class="infraTh" width="20%">Tabela</th>'."\n";
      $strResultado .= '<th class="infraTh" width="15%">Max ID Origem</th>'."\n";
      $strResultado .= '<th class="infraTh" width="15%">Max ID Destino</th>'."\n";
      $strResultado .= '<th class="infraTh">Próximo ID Sequência Origem</th>'."\n";
      $strResultado .= '<th class="infraTh">Próximo ID Sequência Destino</th>'."\n";
      
      $strResultado .= '</tr>'."\n";
      
      $strCssTr='';
      
      for($i = 0;$i < $numRegistrosSequencia; $i++){
        
        if ($arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaOrigem() == $arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaDestino() &&
            $arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaOrigem() != '[erro]' && 
            $arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaOrigem() == $arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaDestino() &&
            ($arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaOrigem()==null || $arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaOrigem()<=$arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaOrigem()) &&
            ($arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaDestino()==null || $arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaDestino()<=$arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaDestino())){
          $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
        }else{
          $strCssTr= '<tr class="trVermelha">';
        }
        $strResultado .= $strCssTr;
    
        $strResultado .= '<td>'.$arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getStrNomeTabelaOrigem().'</td>';
        $strResultado .= '<td align="center">'.$arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaOrigem().'</td>';
        $strResultado .= '<td align="center">'.$arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaDestino().'</td>';
        $strResultado .= '<td align="center">'.$arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaOrigem().'</td>';
        $strResultado .= '<td align="center">'.$arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaDestino().'</td>';
  
          /*
          if ($arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaDestino()==1 && $arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaOrigem() > 1){
            $sql .= 'ALTER TABLE '.$arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getStrNomeTabelaOrigem().' AUTO_INCREMENT = '.$arrObjInfraComparacaoBancoDTO_Sequencia[$i]->getNumMaxIdTabelaSequenciaOrigem().';'."<br />";
          }
          */
          
        $strResultado .= '</tr>'."\n";
      }
      $strResultado .= '</table><br />';
    }
    //$strResultado .= $sql;
  }  
  

  $strItensSelTipoBancoDadosOrigem = InfraComparacaoBancoINT::montarSelectTipoBancoDados('null','',$objInfraComparacaoBancoDTO->getStrBancoDadosOrigem());
  $strItensSelTipoBancoDadosDestino = InfraComparacaoBancoINT::montarSelectTipoBancoDados('null','',$objInfraComparacaoBancoDTO->getStrBancoDadosDestino());
  
}catch(Exception $e){
  PaginaInfra::getInstance()->processarExcecao($e);
} 

PaginaInfra::getInstance()->montarDocType();
PaginaInfra::getInstance()->abrirHtml();
PaginaInfra::getInstance()->abrirHead();
PaginaInfra::getInstance()->montarMeta();
PaginaInfra::getInstance()->montarTitle(':: '.PaginaInfra::getInstance()->getStrNomeSistema().' - '.$strTitulo.' ::');
PaginaInfra::getInstance()->montarStyle();
PaginaInfra::getInstance()->abrirStyle();
?>

tr.trVermelha{
background-color:#F59F9F; 
}

#fldDadosOrigem {position:absolute;left:0;top:0%;height:80%;width:30%;}

#lblServidorOrigem {position:absolute;left:5%;top:5%;width:85%;}
#txtServidorOrigem {position:absolute;left:5%;top:10%;width:85%;}

#lblPortaOrigem {position:absolute;left:5%;top:20%;width:70%;}
#txtPortaOrigem {position:absolute;left:5%;top:25%;width:70%;}

#lblBancoOrigem {position:absolute;left:5%;top:35%;width:70%;}
#txtBancoOrigem {position:absolute;left:5%;top:40%;width:70%;}

#lblUsuarioOrigem {position:absolute;left:5%;top:50%;width:70%;}
#txtUsuarioOrigem {position:absolute;left:5%;top:55%;width:70%;}

#lblSenhaOrigem {position:absolute;left:5%;top:65%;width:70%;}
#txtSenhaOrigem {position:absolute;left:5%;top:70%;width:70%;}

#lblTipoBancoDadosOrigem {position:absolute;left:5%;top:80%;width:70%;}
#selTipoBancoDadosOrigem {position:absolute;left:5%;top:85%;width:70%;}

#fldDadosDestino {position:absolute;left:34%;top:0%;height:80%;width:30%;}

#lblServidorDestino {position:absolute;left:5%;top:5%;width:85%;}
#txtServidorDestino {position:absolute;left:5%;top:10%;width:85%;}

#lblPortaDestino{position:absolute;left:5%;top:20%;width:70%;}
#txtPortaDestino {position:absolute;left:5%;top:25%;width:70%;}

#lblBancoDestino {position:absolute;left:5%;top:35%;width:70%;}
#txtBancoDestino {position:absolute;left:5%;top:40%;width:70%;}

#lblUsuarioDestino {position:absolute;left:5%;top:50%;width:70%;}
#txtUsuarioDestino {position:absolute;left:5%;top:55%;width:70%;}

#lblSenhaDestino {position:absolute;left:5%;top:65%;width:70%;}
#txtSenhaDestino {position:absolute;left:5%;top:70%;width:70%;}

#lblTipoBancoDadosDestino{position:absolute;left:5%;top:80%;width:70%;}
#selTipoBancoDadosDestino {position:absolute;left:5%;top:85%;width:70%;}

#fldTabelasIgnorar {position:absolute;left:68%;top:0%;height:80%;width:28%;}
#lblTabelasIgnorar {position:absolute;left:5%;top:5%;width:85%;}
#txaTabelasIgnorar {position:absolute;left:5%;top:10%;width:85%;}

#lblSinComparaTipoColunasTabela {position:absolute;left:3%;top:87%;}
#chkSinComparaTipoColunasTabela {position:absolute;left:0%;top:86%;}

#lblSinComparaQtdeRegistrosTabela {position:absolute;left:3%;top:91%;}
#chkSinComparaQtdeRegistrosTabela {position:absolute;left:0%;top:90%;}

#lblSinComparaMaxIdTabela {position:absolute;left:3%;top:95%;}
#chkSinComparaMaxIdTabela {position:absolute;left:0%;top:94%;}

<?
PaginaInfra::getInstance()->fecharStyle();
PaginaInfra::getInstance()->montarJavaScript();
PaginaInfra::getInstance()->abrirJavaScript();
?>
function habilitarOuDesabilitarOpcaoCompararColunasTabela(){
  
  document.getElementById('chkSinComparaTipoColunasTabela').disabled=false;
  
  /*
  if (document.getElementById('selTipoBancoDadosOrigem').value == document.getElementById('selTipoBancoDadosDestino').value){
    document.getElementById('chkSinComparaTipoColunasTabela').disabled=false;
  }else{
    document.getElementById('chkSinComparaTipoColunasTabela').checked=false;
    document.getElementById('chkSinComparaTipoColunasTabela').disabled=true;
  }
  */
}
function OnSubmitForm() {
  return validarForm();
}

function validarForm() { 
  if (document.getElementById('txtServidorOrigem').value == '') {
    alert('Informe o Servidor Origem.');
    document.getElementById('txtServidorOrigem').focus();
    return false;
  }
  if (document.getElementById('txtPortaOrigem').value == '') {
    alert('Informe a Porta Origem.');
    document.getElementById('txtPortaOrigem').focus();
    return false;
  }
  if (document.getElementById('txtBancoOrigem').value == '') {
    alert('Informe o Banco Origem.');
    document.getElementById('txtBancoOrigem').focus();
    return false;
  }
  if (document.getElementById('txtUsuarioOrigem').value == '') {
    alert('Informe o Usuário Origem.');
    document.getElementById('txtUsuarioOrigem').focus();
    return false;
  }
  if (document.getElementById('txtSenhaOrigem').value == '') {
    alert('Informe a Senha Origem.');
    document.getElementById('txtSenhaOrigem').focus();
    return false;
  }

  if (!infraSelectSelecionado('selTipoBancoDadosOrigem')) {
    alert('Selecione um Tipo de Banco de Dados Origem.');
    document.getElementById('selTipoBancoDadosOrigem').focus();
    return false;
  }
  
  if (document.getElementById('txtServidorDestino').value == '') {
    alert('Informe o Servidor Destino.');
    document.getElementById('txtServidorDestino').focus();
    return false;
  }
  if (document.getElementById('txtPortaDestino').value == '') {
    alert('Informe a Porta Destino.');
    document.getElementById('txtPortaDestino').focus();
    return false;
  }
  if (document.getElementById('txtBancoDestino').value == '') {
    alert('Informe o Banco Destino.');
    document.getElementById('txtBancoDestino').focus();
    return false;
  }
  if (document.getElementById('txtUsuarioDestino').value == '') {
    alert('Informe o Usuário Destino.');
    document.getElementById('txtUsuarioDestino').focus();
    return false;
  }
  if (document.getElementById('txtSenhaDestino').value == '') {
    alert('Informe a Senha Destino.');
    document.getElementById('txtSenhaDestino').focus();
    return false;
  }
    
  if (!infraSelectSelecionado('selTipoBancoDadosDestino')) {
    alert('Selecione um Tipo de Banco de Dados Destino.');
    document.getElementById('txtSenhaDestino').focus();
    return false;
  }
  
  infraExibirAviso(true);
  
  return true;
}

function inicializar(){
  habilitarOuDesabilitarOpcaoCompararColunasTabela();
}


<?
PaginaInfra::getInstance()->fecharJavaScript();
PaginaInfra::getInstance()->fecharHead();
PaginaInfra::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmComparacaoBanco" method="post" onsubmit="return OnSubmitForm();" action="<?=PaginaInfra::getInstance()->formatarXHTML(SessaoInfra::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
  <?
  //PaginaInfra::getInstance()->montarBarraLocalizacao('Importar Sistema');
  PaginaInfra::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaInfra::getInstance()->abrirAreaDados('42em');
  ?>
	  
	<fieldset id="fldDadosOrigem">
	  <legend style="font-weight: bold;background-color:#e0e0e0;">&nbsp;Banco de Dados Origem&nbsp;</legend>

		<label id="lblServidorOrigem" for="txtServidorOrigem" accesskey="" class="infraLabelObrigatorio">Servidor:</label>
		<input type="text" id="txtServidorOrigem" name="txtServidorOrigem" class="infraText" value="<?=$objInfraComparacaoBancoDTO->getStrServidorOrigem();?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

		<label id="lblPortaOrigem" for="txtPortaOrigem" accesskey="" class="infraLabelObrigatorio">Porta:</label>
		<input type="text" id="txtPortaOrigem" name="txtPortaOrigem" class="infraText" value="<?=$objInfraComparacaoBancoDTO->getStrPortaOrigem();?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />
		
		<label id="lblBancoOrigem" for="txtBancoOrigem" accesskey="" class="infraLabelObrigatorio">Banco:</label>
		<input type="text" id="txtBancoOrigem" name="txtBancoOrigem" class="infraText" value="<?=$objInfraComparacaoBancoDTO->getStrBancoOrigem();?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

		<label id="lblUsuarioOrigem" for="txtUsuarioOrigem" accesskey="" class="infraLabelObrigatorio">Usuário:</label>
		<input type="text" id="txtUsuarioOrigem" name="txtUsuarioOrigem" class="infraText" value="<?=$objInfraComparacaoBancoDTO->getStrUsuarioOrigem();?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />
		
		<label id="lblSenhaOrigem" for="txtSenhaOrigem" accesskey="" class="infraLabelObrigatorio">Senha:</label>
		<input type="password" id="txtSenhaOrigem" name="txtSenhaOrigem" class="infraText" value="<?=$objInfraComparacaoBancoDTO->getStrSenhaOrigem();?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />
		
		<label id="lblTipoBancoDadosOrigem" for="selTipoBancoDadosOrigem" class="infraLabelObrigatorio">Tipo:</label>
		<select id="selTipoBancoDadosOrigem" name="selTipoBancoDadosOrigem" onchange="habilitarOuDesabilitarOpcaoCompararColunasTabela()" class="infraSelect" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>">		 
      <?=$strItensSelTipoBancoDadosOrigem?>            
    </select>
		
	</fieldset>
	
	<fieldset id="fldDadosDestino">
	  <legend style="font-weight: bold;background-color:#e0e0e0;">&nbsp;Banco de Dados Destino&nbsp;</legend>

  	<label id="lblServidorDestino" for="txtServidorDestino" accesskey="" class="infraLabelObrigatorio">Servidor:</label>
		<input type="text" id="txtServidorDestino" name="txtServidorDestino" class="infraText" value="<?=$objInfraComparacaoBancoDTO->getStrServidorDestino();?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

		<label id="lblPortaDestino" for="txtPortaDestino" accesskey="" class="infraLabelObrigatorio">Porta:</label>
		<input type="text" id="txtPortaDestino" name="txtPortaDestino" class="infraText" value="<?=$objInfraComparacaoBancoDTO->getStrPortaDestino();?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />
		
		<label id="lblBancoDestino" for="txtBancoDestino" accesskey="" class="infraLabelObrigatorio">Banco:</label>
		<input type="text" id="txtBancoDestino" name="txtBancoDestino" class="infraText" value="<?=$objInfraComparacaoBancoDTO->getStrBancoDestino();?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

		<label id="lblUsuarioDestino" for="txtUsuarioDestino" accesskey="" class="infraLabelObrigatorio">Usuário:</label>
		<input type="text" id="txtUsuarioDestino" name="txtUsuarioDestino" class="infraText" value="<?=$objInfraComparacaoBancoDTO->getStrUsuarioDestino();?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />
		
		<label id="lblSenhaDestino" for="txtSenhaDestino" accesskey="" class="infraLabelObrigatorio">Senha:</label>
		<input type="password" id="txtSenhaDestino" name="txtSenhaDestino" class="infraText" value="<?=$objInfraComparacaoBancoDTO->getStrSenhaDestino();?>" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />
		
		<label id="lblTipoBancoDadosDestino" for="selTipoBancoDadosDestino" accesskey="" class="infraLabelObrigatorio">Tipo:</label>
		<select id="selTipoBancoDadosDestino" name="selTipoBancoDadosDestino" onchange="habilitarOuDesabilitarOpcaoCompararColunasTabela()" class="infraSelect" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>">		 
      <?=$strItensSelTipoBancoDadosDestino?>            
    </select>
	</fieldset>

  <fieldset id="fldTabelasIgnorar">
    <legend style="font-weight: bold;background-color:#e0e0e0;">&nbsp;Ignorar Tabelas&nbsp;</legend>
    <label id="lblTabelasIgnorar" for="txaTabelasIgnorar" class="infraLabelOpcional">Informar uma tabela por linha:</label>
    <textarea id="txaTabelasIgnorar" name="txaTabelasIgnorar" rows="17"><?=$_POST['txaTabelasIgnorar']?></textarea>
  </fieldset>

  <label id="lblSinComparaTipoColunasTabela" for="chkSinComparaTipoColunasTabela" accesskey="" class="infraLabelCheckbox">Comparar tipos das colunas</label>
  <input type="checkbox" id="chkSinComparaTipoColunasTabela" name="chkSinComparaTipoColunasTabela" class="infraCheckbox" <?=PaginaInfra::getInstance()->setCheckbox($objInfraComparacaoBancoDTO->getStrSinComparaTipoColunasTabela())?>  tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />
	
	<label id="lblSinComparaQtdeRegistrosTabela" for="chkSinComparaQtdeRegistrosTabela" accesskey="" class="infraLabelCheckbox">Comparar quantidade de registros das tabelas</label>
  <input type="checkbox" id="chkSinComparaQtdeRegistrosTabela" name="chkSinComparaQtdeRegistrosTabela" class="infraCheckbox" <?=PaginaInfra::getInstance()->setCheckbox($objInfraComparacaoBancoDTO->getStrSinComparaQtdeRegistrosTabela())?>  tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />
  
  <label id="lblSinComparaMaxIdTabela" for="chkSinComparaMaxIdTabela" accesskey="" class="infraLabelCheckbox">Comparar máximo valor dos IDs</label>
  <input type="checkbox" id="chkSinComparaMaxIdTabela" name="chkSinComparaMaxIdTabela" class="infraCheckbox" <?=PaginaInfra::getInstance()->setCheckbox($objInfraComparacaoBancoDTO->getStrSinComparaMaxIdTabela())?>  tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>" />

  <?
  PaginaInfra::getInstance()->fecharAreaDados();
  PaginaInfra::getInstance()->montarAreaTabela($strResultado,$numRegistrosTabela + $numRegistrosConstraint + $numRegistrosSequencia);
  PaginaInfra::getInstance()->montarAreaDebug();
  //PaginaInfra::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaInfra::getInstance()->fecharBody();
PaginaInfra::getInstance()->fecharHtml();
?>