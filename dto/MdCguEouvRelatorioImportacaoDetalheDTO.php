<?
/**
 * CONTROLADORIA GERAL DA UNIÃO
 *
 * 18/10/2015 - criado por Rafaele Leandro
 *
 * Versão do Gerador de Código: 1.29.1
 *
 * Versão no CVS: $Id$
 */


require_once dirname(__FILE__) . '/../../../../SEI.php';

class MdCguEouvRelatorioImportacaoDetalheDTO extends InfraDTO
{

    public function getStrNomeTabela()
    {
        return 'md_cgu_eouv_relatorio_import_detalhe';
    }

    public function montar()
    {

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdRelatorioImportacao',
            'id_md_cgu_eouv_relatorio_importacao');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'ProtocoloFormatado',
            'num_protocolo_formatado');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'SinSucesso',
            'sin_sucesso');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'DescricaoLog',
            'des_log_processamento');

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DTH,
            'DthImportacao',
            'dth_importacao',
            'md_cgu_eouv_relatorio_importacao');



        $this->configurarPK('IdRelatorioImportacao', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('ProtocoloFormatado', InfraDTO::$TIPO_PK_INFORMADO);

        $this->configurarFK('IdRelatorioImportacao', 'md_cgu_eouv_relatorio_importacao', 'id_md_cgu_eouv_relatorio_importacao');

    }
}

?>