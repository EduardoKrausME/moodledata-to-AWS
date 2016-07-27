# moodledata to AWS

Este plug-in, Open Source, envia seus arquivos da pasta ```moodledata/filedir``` para a Amazon S3 e gerencia sua entrega.

This plug-in, Open Source, send your files in the folder ```moodledata/filedir``` to Amazon S3 and manages their delivery.

## PT_BR: Instalação

Envie a pasta ```_s3``` para a raiz da pasta da instalação do Moodle.

### Editando o config.php

Antes da linha ```require_once(dirname(__FILE__) . '/lib/setup.php');``` adicione as seguintes linhas: 

```
if ( strpos ( $_SERVER[ 'REQUEST_URI' ], 'pluginfile.php' ) ) {
    $CFG->aws_s3_key    = 'Access Key ID';
    $CFG->aws_s3_secret = 'Access Secret ID';
    $CFG->aws_s3_bucket = 'Nome do Bucket';

    require_once ( dirname ( __FILE__ ) . '/_s3/_load.php' );

    $file = new S3Filedir();
    $file->isFileInAmazon ( get_file_argument () );
}
```

[Ir para a página de Credenciais do AWS](https://console.aws.amazon.com/iam/home?region=us-east-1#security_credential)

## EN: Installation

Send the folder ```_s3``` to the root of the Moodle installation folder.

### Editing config.php

Before the line ```require_once(dirname(__FILE__) . '/lib/setup.php');``` add the following lines:

```
if ( strpos ( $_SERVER[ 'REQUEST_URI' ], 'pluginfile.php' ) ) {
    $CFG->aws_s3_key    = 'Access Key ID';
    $CFG->aws_s3_secret = 'Access Secret ID';
    $CFG->aws_s3_bucket = 'Bucket Name';

    require_once ( dirname ( __FILE__ ) . '/_s3/_load.php' );

    $file = new S3Filedir();
    $file->isFileInAmazon ( get_file_argument () );
}
```

[Go to AWS Credentials page](https://console.aws.amazon.com/iam/home?region=us-east-1#security_credential)