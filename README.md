# Módulo de Integração SEI x e-Ouv

## Requisitos:
- SEI 3.0.0 instalado/atualizado ou versão superior (verificar valor da constante de versão do SEI no arquivo /sei/web/SEI.php).

- Utilizar o Sistema de Ouvidorias do Governo Federal e-Ouv(sistema.ouvidorias.gov.br). Caso ainda não tenha aderido ao e-Ouv e queira saber mais informações acesse www.ouvidorias.gov.br.
		
- Antes de executar os scripts de instalação/atualização (itens 4 e 5 abaixo), o usuário de acesso aos bancos de dados do SEI e do SIP, constante nos arquivos ConfiguracaoSEI.php e ConfiguracaoSip.php, deverá ter permissão de acesso total ao banco de dados, permitindo, por exemplo, criação e exclusão de tabelas.

- Instalar na pasta infra/infra_php a biblioteca nusoap. Como o sistema e-Ouv utiliza versionamento de WebServices a biblioteca padrão do SEI para consumir webservices não consegue resolver essa questão. A mesma pode ser baixada em: https://sourceforge.net/projects/nusoap/files/?source=navbar
	- Após a instalação é necessário fazer uma correção na biblioteca conforme abaixo:
	
	

> 
	alterar o arquivo nusoap.php na linha 4694
		de:$this->schemas[$ns]->imports[$ns2][$ii]['loaded'] = true; 
		para:$this->schemas[$ns][$ns2]->imports[$ns2][$ii]['loaded'] = true; 


## Procedimentos para Instalação:

1. Antes, fazer backup dos bancos de dados do SEI e do SIP.

2. Carregar no servidor os arquivos do módulo localizados na pasta "/sei/web/modulos/cgu/mod-sei-eouv" e os scripts de instalação/atualização "/sei/scripts/md_cgu_eouv_atualizar_modulo.php" e "/sip/scripts/md_cgu_eouv_atualizar_modulo.php".

3. Editar o arquivo "/sei/config/ConfiguracaoSEI.php", tomando o cuidado de usar editor que não altere o charset do arquivo, para adicionar a referência à classe de integração do módulo e seu caminho relativo dentro da pasta "/sei/web/modulos" na array 'Modulos' da chave 'SEI':

		'SEI' => array(
			'URL' => 'http://[Servidor_PHP]/sei',
			'Producao' => false,
			'RepositorioArquivos' => '/var/sei/arquivos',
			'Modulos' => array('MdCguEouvIntegracao' => 'cgu/mod-sei-eouv',)
			),

4. Rodar o script de banco "/sei/scripts/md_cgu_eouv_atualizar_modulo.php" em linha de comando no servidor do SEI, verificando se não houve erro em sua execução, em que ao final do log deverá ser informado "FIM". Exemplo de comando de execução:

		/usr/bin/php -c /etc/php.ini /opt/sei/scripts/md_cgu_eouv_atualizar_modulo.php > md_cgu_eouv_atualizar_modulo_1.log

5. Rodar o script de banco "/sip/scripts/md_cgu_eouv_atualizar_modulo.php" em linha de comando no servidor do SIP, verificando se não houve erro em sua execução, em que ao final do log deverá ser informado "FIM". Exemplo de comando de execução:

		/usr/bin/php -c /etc/php.ini /opt/sip/scripts/md_cgu_eouv_atualizar_modulo.php > md_cgu_eouv_atualizar_modulo-1.log

6. Após a execução com sucesso, com um usuário com permissão de Administrador no SEI, seguir os passos dispostos no tópico Orientações Negociais, abaixo.

7. **IMPORTANTE**: Na execução dos dois scripts acima, ao final deve constar o termo "FIM" e informação de que a instalação ocorreu com sucesso (SEM ERROS). Do contrário, o script não foi executado até o final e algum dado não foi inserido/atualizado no banco de dados correspondente, devendo recuperar o backup do banco pertinente e repetir o procedimento.
		- Constando o termo "FIM" e informação de que a instalação ocorreu com sucesso, pode logar no SEI e SIP e verificar no menu Infra > Módulos se consta o módulo "Módulo de Integração entre o sistema SEI e o E-ouv(Sistema de Ouvidorias)" com o valor da última versão do módulo.

8. Em caso de erro durante a execução do script verificar (lendo as mensagens de erro e no menu Infra > Log do SEI e do SIP) se a causa é algum problema na infra-estrutura local. Neste caso, após a correção, deve recuperar o backup do banco pertinente e repetir o procedimento, especialmente a execução dos scripts indicados nos itens 4 e 5 acima.
	- Caso não seja possível identificar a causa, entrar em contato com: Rafael Leandro - rafael.ferreira@cgu.gov.br

## Orientações Negociais:

Criamos um vídeo com a demonstração do funcionamento do módulo focado na parte negocial, caso queira entender um pouco mais sobre o módulo acesse:

https://www.youtube.com/watch?v=geUCx7H79Gw

1. Imediatamente após a instalação com sucesso, com usuário com permissão de "Administrador" do SEI, é necessário realizar as parametrizações do módulo no menu Infra > Parâmetros alterando os seguintes Parâmetros:

- EOUV_DATA_INICIAL_IMPORTACAO_MANIFESTACOES: Colocar a Data Inicial no formato (DD/MM/AAAA) para carregar as manifestações do e-Ouv. Sugerimos que seja colocada a data atual para que apenas as novas manifestações sejam importadas para o SEI.

- EOUV_ID_SERIE_DOCUMENTO_EXTERNO_DADOS_MANIFESTACAO: Quando a rotina for executada ela criará um documento PDF com os dados da Manifestação do EOUV que será anexada ao processo. Esse parâmetro será usado para dizer qual o Tipo de Documento será usado para criar esse documento. Lembrando que deve ser do Grupo de Documentos Externos. Para verificar os tipos existentes acesse Administração > Tipos de Documento > Listar.

- EOUV_USUARIO_ACESSO_WEBSERVICE: Nome de usuário para acesso aos WebServices do e-Ouv.
Este nome de usuário é gerado para cada órgão especificamente para consumir os Webservices do e-Ouv.
Caso ainda não possua esse usuário e a senha abaixo entrar em contato através do e-mail abaixo solicitando o mesmo: marcos.silva@cgu.gov.br

- EOUV_SENHA_ACESSO_WEBSERVICE: Senha do usuário para acesso aos WebServices do e-Ouv.

- EOUV_URL_WEBSERVICE_IMPORTACAO_MANIFESTACAO: Já vem configurado para o ambiente de produção do e-Ouv com https://sistema.ouvidorias.gov.br/Servicos/ServicoConsultaManifestacao.svc
Obs: Para efeitos de testes e homologação utilizar o ambiente de treinamento: http://treinamentoouvidorias.cgu.gov.br

- EOUV_URL_WEBSERVICE_IMPORTACAO_ANEXO_MANIFESTACAO: Já vem configurado para o ambiente de produção do e-Ouv com https://sistema.ouvidorias.gov.br/Servicos/ServicoAnexosManifestacao.svc
Obs: Para efeitos de testes e homologação utilizar o ambiente de treinamento: http://treinamentoouvidorias.cgu.gov.br

- ID_UNIDADE_OUVIDORIA: Código da Unidade que deverá registrar os novos processos. Ao importar os processos do e-Ouv para o SEI essa será a unidade que receberá os Processos no SEI.

2. Foi criado um novo Agendamento de Tarefa com o nome "MdCguEouvAgendamentoRN :: executarImportacaoManifestacaoEOuv". O mesmo é configurado por padrão para ser executado apenas uma vez por dia e deverá ser configurado conforme desejado pelo órgão. Os agendamentos podem ser acessados em Infra > Agendamentos.

3. Foi criado um menu com o nome E-Ouv que possui um relatório das execuções de Importação executadas. A cada execução do agendamento é gerado um registro que contém os detalhes da execução informando se houve sucesso e os Protocolos que foram importados.

4. Foi criada uma tabela com o nome md_cgu_eouv_depara_importacao que serve para dizer para a rotina qual o Tipo de Processo será cadastrado para cada tipo de Manifestação do e-Ouv. Seguindo a tabela abaixo informe qual o código do tipo de processo(Administração > Tipos de Processo) para cada equivalente. 

|id_tipo_manifestacao_eouv |id_tipo_procecimento    |de_tipo_manifestacao_eouv |
|--------------------------|------------------------|--------------------------|
|1                         |`xxx`                   |Denúncia                  |
|2                         |`xxx`                   |Reclamação                |
|3                         |`xxx`                   |Elogio                    |
|4                         |`xxx`                   |Sugestão                  |
|5                         |`xxx`                   |Solicitação               |
|6                         |`xxx`                   |Simplifique               |
