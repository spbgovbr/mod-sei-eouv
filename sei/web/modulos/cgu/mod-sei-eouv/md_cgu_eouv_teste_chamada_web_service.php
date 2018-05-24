<?
/**
 * Created by PhpStorm.
 * User: rafaellf
 * Date: 15/09/2015
 * Time: 16:08
 */

error_reporting(E_ALL); ini_set('display_errors', '1');

require_once dirname(__FILE__) . '/../../../SEI.php';

$objEouvWS = new MdCguEouvWS();
$objEouvWS -> testarAgendamentoEouv('EOuv','CadastrarManifestacao',110000001);
//$objEouvWS -> testarGerarPDF('EOuv','Cadastrar Manifestacao',110000001);

exit();

$objProtocoloDTO = new ProtocoloDTO();
$objProcedimentoDTO->setDblIdProcedimento(null);

if (isset($_GET['id_tipo_procedimento'])){
    $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
    $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
    $objTipoProcedimentoDTO->retStrNome();
    $objTipoProcedimentoDTO->setNumIdTipoProcedimento($_GET['id_tipo_procedimento']);

    $objTipoProcedimentoRN = new TipoProcedimentoRN();
    $objTipoProcedimentoDTO = $objTipoProcedimentoRN->consultarRN0267($objTipoProcedimentoDTO);

    if ($objTipoProcedimentoDTO==null){
        throw new InfraException('Tipo de processo nгo encontrado.');
    }
    $objProcedimentoDTO->setStrProtocoloProcedimentoFormatado('');
    $objProcedimentoDTO->setNumIdTipoProcedimento($objTipoProcedimentoDTO->getNumIdTipoProcedimento());
    $objProcedimentoDTO->setStrNomeTipoProcedimento($objTipoProcedimentoDTO->getStrNome());
    $objProtocoloDTO->setNumIdTipoProcedimentoProcedimento($objTipoProcedimentoDTO->getNumIdTipoProcedimento());
    $objProtocoloDTO->setDtaGeracao(InfraData::getStrDataAtual());
    $objProtocoloDTO->setStrStaNivelAcessoLocal(null);
    //$objProtocoloDTO->setStrStaGrauSigilo(null);
    //$objProtocoloDTO->setNumIdHipoteseLegal(null);
}else{

    if ($_POST['rdoProtocolo']=='M'){
        $objProcedimentoDTO->setStrProtocoloProcedimentoFormatado($_POST['txtProtocoloInformar']);
        $objProtocoloDTO->setDtaGeracao($_POST['txtDtaGeracaoInformar']);
    }else{
        $objProcedimentoDTO->setStrProtocoloProcedimentoFormatado(null);
        $objProtocoloDTO->setDtaGeracao(null);
    }

    $objProcedimentoDTO->setNumIdTipoProcedimento($_POST['hdnIdTipoProcedimento']);
    $objProtocoloDTO->setNumIdTipoProcedimentoProcedimento($_POST['hdnIdTipoProcedimento']);
    $objProcedimentoDTO->setStrNomeTipoProcedimento($_POST['hdnNomeTipoProcedimento']);
    $objProtocoloDTO->setStrStaNivelAcessoLocal($_POST['rdoNivelAcesso']);
    $objProtocoloDTO->setNumIdHipoteseLegal($_POST['selHipoteseLegal']);
    $objProtocoloDTO->setStrStaGrauSigilo($_POST['selGrauSigilo']);
}

$objProcedimentoDTO = new ProcedimentoDTO();
$objProcedimentoRN = new ProcedimentoRN();



$numIdUnidade = '100000969'; //CORREG
//Procedimento
$Procedimento = array();
$Procedimento['IdTipoProcedimento'] = '100000368'; //Designacao de Magistrado - Suspeicao/Impedimento
$Procedimento['Especificacao'] = 'especificacao teste processo';

$arrAssuntos = array();
$arrAssuntos[] = array('CodigoEstruturado'=>'00.01.01.01');
$arrAssuntos[] = array('CodigoEstruturado'=>'00.01.08.02');
$Procedimento['Assuntos'] = $arrAssuntos;

$arrInteressados = array();
$arrInteressados[] = array('Sigla'=>'dgx', 'Nome' => 'Alberto');
$arrInteressados[] = array('Sigla'=>'utv', 'Nome' => 'Maria');

$Procedimento['Interessados'] = $arrInteressados;
$Procedimento['Observacao'] = 'observacao teste processo';
$Procedimento['NivelAcesso'] = null;

$ProcedimentosRelacionados = array('1210000004770');
$UnidadesEnvio = array('110000015','100000983');

//Documento Gerado
$DocumentoGerado = array();
$DocumentoGerado['Tipo'] = 'G';

//se incluindo em um processo existente informar o id neste campo
//se incluindo o documento no momento da geracao do processo passar null
$DocumentoGerado['IdProcedimento'] = null;
$DocumentoGerado['IdSerie'] = '3'; //Portaria
$DocumentoGerado['Numero'] = null;
$DocumentoGerado['Data'] = null;
$DocumentoGerado['Descricao'] = 'descricao teste documento';
$DocumentoGerado['Remetente'] = null;

$arrInteressados = array();
$arrInteressados[] = array('Sigla'=>'kiv', 'Nome' => 'Pedro');
$DocumentoGerado['Interessados'] = $arrInteressados;

$arrDestinatarios = array();
$arrDestinatarios[] = array('Sigla'=>'udv', 'Nome' => 'Joгo');
$arrDestinatarios[] = array('Sigla'=>'prm', 'Nome' => 'Paulo');

$DocumentoGerado['Destinatarios'] = $arrDestinatarios;
$DocumentoGerado['Observacao'] = 'observacao teste documento';
$DocumentoGerado['NomeArquivo'] = null;
$DocumentoGerado['Conteudo'] = base64_encode('aaabbbccc');
$DocumentoGerado['NivelAcesso'] = null;
//Documento Recebido
$DocumentoRecebido = array();
$DocumentoRecebido['Tipo'] = 'R';

//se incluindo em um processo existente informar o id neste campo
//se incluindo o documento no momento da geracao do processo passar null
$DocumentoRecebido['IdProcedimento'] = null;
$DocumentoRecebido['IdSerie'] = '301'; //Ofнcio
$DocumentoRecebido['Numero'] = '1000';
$DocumentoRecebido['Data'] = '10/09/2011';
$DocumentoRecebido['Descricao'] = 'descricao teste documento';
$DocumentoRecebido['Remetente'] = array('Sigla'=>'lmr','Nome'=>'Luiza');

$arrInteressados = array();
$arrInteressados[] = array('Sigla'=>'rub', 'Nome' => 'Roberto');
$arrInteressados[] = array('Sigla'=>'nay', 'Nome' => 'Nadir');
$DocumentoRecebido['Interessados'] = $arrInteressados;
$DocumentoRecebido['Destinatarios'] = null;
$DocumentoRecebido['Observacao'] = 'observacao teste documento';
$DocumentoRecebido['NomeArquivo'] = 'oficio.txt';
$DocumentoRecebido['Conteudo'] = base64_encode('conteudo do documento oficio.txt');

//Para MTOM
//$DocumentoRecebido['Conteudo'] = '';
//$DocumentoRecebido['ConteudoMTOM'] = file_get_contents(dirname(__FILE__).'/OFIC832014CEF.pdf');
$DocumentoRecebido['NivelAcesso'] = null;
//1 - gera processo
//$ret = $objWS->gerarProcedimento('Corregedoria','Suspeiзгo/Impedimento', $numIdUnidade, $Procedimento, array(),$ProcedimentosRelacionados, $UnidadesEnvio);

//2 - gera processo + documento gerado
//$ret = $objWS->gerarProcedimento('Corregedoria','Suspeiзгo/Impedimento', $numIdUnidade, $Procedimento, array($DocumentoGerado), array(),$UnidadesEnvio);

//3 - gera processo + documento gerado + documento externo
//$ret = $objWS->gerarProcedimento('Corregedoria','Suspeiзгo/Impedimento', $numIdUnidade, $Procedimento, array($DocumentoGerado, $DocumentoRecebido));

//4 - inclui documento gerado em processo existente
//$DocumentoGerado['IdProcedimento'] deve estar com o id preenchido
//$ret = $objWS->incluirDocumento('Corregedoria','Suspeiзгo/Impedimento', $numIdUnidade, $DocumentoGerado);

//5 -inclui documento externo em processo existente
//$DocumentoRecebido['IdProcedimento'] deve estar com o id preenchido
//$ret = $objWS->incluirDocumento('Corregedoria','Suspeiзгo/Impedimento', $numIdUnidade, $DocumentoRecebido);
?>