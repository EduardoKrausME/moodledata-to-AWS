# moodledata to AWS

Este plug-in, Open Source, envia seus arquivos da pasta ```moodledata/filedir``` para a Amazon S3 e gerencia sua entrega.

This plug-in, Open Source, send your files in the folder ```moodledata/filedir``` to Amazon S3 and manages their delivery.

## PT_BR: Instalação

Baixe o fonte e substitua o arquivo ```pluginfile.php``` pelo que você baixou. 
 
Após isso, envie a pasta ```_s3``` para a raiz da pasta da instalação do Moodle.

### Editando o config.php

Antes da linha ```require_once(dirname(__FILE__) . '/lib/setup.php');``` adicione as seguintes linhas: 

```
$CFG->aws_s3_key    = 'Access Key ID';
$CFG->aws_s3_secret = 'Access Secret ID';
$CFG->aws_s3_bucket = 'Nome do Bucket';
```

[Ir para a página de Credenciais do AWS](https://console.aws.amazon.com/iam/home?region=us-east-1#security_credential)

## EN: Installation

Download the source and replace the ```pluginfile.php``` for what you downloaded.

After that, send the folder ```_s3``` to the root of the Moodle installation folder.

### Editing config.php

Before the line ```require_once(dirname(__FILE__) . '/lib/setup.php');``` add the following lines:

```
$CFG->aws_s3_key    = 'Access Key ID';
$CFG->aws_s3_secret = 'Access Secret ID';
$CFG->aws_s3_bucket = 'Nome do Bucket';
```

[Go to AWS Credentials page](https://console.aws.amazon.com/iam/home?region=us-east-1#security_credential)