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

class MdCguEouvParametroDTO extends InfraDTO {

    public function getStrNomeTabela() {
        return 'md_eouv_parametros';
    }

    public function montar() {

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
            'IdParametro',
            'id_parametro');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'NoParametro',
            'no_parametro');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            'DeValorParametro',
            'de_valor_parametro');

        $this->configurarPK('IdParametro',InfraDTO::$TIPO_PK_INFORMADO);

    }
}
?>