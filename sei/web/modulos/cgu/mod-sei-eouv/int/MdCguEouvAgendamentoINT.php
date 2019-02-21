<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4Є REGIГO
 *
 * 20/12/2007 - criado por mga
 *
 * Versгo do Gerador de Cуdigo: 1.12.0
 *
 * Versгo no CVS: $Id$
 */

require_once dirname(__FILE__).'/../../../../SEI.php';

class MdCguEouvAgendamentoINT extends InfraINT {


  public static function retornarUltimaExecucaoSucesso(){

    $objEouvRelatorioImportacaoDTO=new MdCguEouvRelatorioImportacaoDTO();
    $objEouvRelatorioImportacaoDTO->retDthDthImportacao();
    $objEouvRelatorioImportacaoDTO->retDthDthPeriodoInicial();
    $objEouvRelatorioImportacaoDTO->retDthDthPeriodoFinal();
    $objEouvRelatorioImportacaoDTO->retNumIdRelatorioImportacao();
    $objEouvRelatorioImportacaoDTO->setStrSinSucesso('S');
    $objEouvRelatorioImportacaoDTO->setOrdDthDthImportacao(InfraDTO::$TIPO_ORDENACAO_DESC);
    $objEouvRelatorioImportacaoDTO->setNumMaxRegistrosRetorno(1);

    $objEouvRelatorioImportacaoRN = new MdCguEouvRelatorioImportacaoRN();

      try{
          $resultadoObjEouvRelatorioImportacaoDTO = $objEouvRelatorioImportacaoRN->consultar($objEouvRelatorioImportacaoDTO);
      }catch(Exception $e){
          throw new InfraException('Erro obtendo ъltima execuзгo da Importacao SEI x EOuv ocorrida com Sucesso.',$e);
      }

      LogSEI::getInstance()->gravar('Ъltima Execuзгo com Sucesso:' . $resultadoObjEouvRelatorioImportacaoDTO);
    return $resultadoObjEouvRelatorioImportacaoDTO;
  }

   public static function retornarManifestacoesNaoImportadasPorProblema($idUltimaExecucao){

       $objEouvRelatorioImportacaoDetalheDTO=new MdCguEouvRelatorioImportacaoDetalheDTO();
       $objEouvRelatorioImportacaoDetalheDTO->retStrProtocoloFormatado();
       $objEouvRelatorioImportacaoDetalheDTO->setStrSinSucesso('N');
       $objEouvRelatorioImportacaoDetalheDTO->setNumIdRelatorioImportacao($idUltimaExecucao);

       $objEouvRelatorioImportacaoDetalheRN = new EouvRelatorioImportacaoDetalheRN();

       $arrObjEouvRelatorioImportacaoDetalheDTO = $objEouvRelatorioImportacaoDetalheRN->listar($objEouvRelatorioImportacaoDetalheDTO);

       return $arrObjEouvRelatorioImportacaoDetalheDTO;
   }
}
?>