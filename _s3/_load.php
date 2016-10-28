<?php
/***************************
 * User: Eduardo Kraus
 * @url https://www.eduardokraus.com/
 * Date: 01/01/2016
 * Time: 22:50
 ***************************/

if ( !isset( $aws_s3_mapping ) ) {
    require_once __DIR__ . 'S3Filedir.php';
    require_once __DIR__ . '/aws/aws-autoloader.php';
}

$file = new S3Filedir();
$file->isFileInAmazon ( get_file_argument () );