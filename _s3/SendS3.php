<?php

/***************************
 * User: Eduardo Kraus
 * @url https://www.eduardokraus.com/
 * Date: 01/01/2016
 * Time: 21:57
 ***************************/


class SendS3
{
    /**
     * @var \Aws\S3\S3Client
     */
    private $client;
    /**
     * @var String
     */
    private $bucket_name;

    public function __construct()
    {
        global $CFG;
        $this->client = \Aws\S3\S3Client::factory (
            array (
                'key'    => $CFG->aws_s3_key,
                'secret' => $CFG->aws_s3_secret
            )
        );
        $this->bucket_name = $CFG->aws_s3_bucket;
    }

    /**
     * @param object $file
     * @param bool $isPrivate
     * @return \Guzzle\Service\Resource\Model
     */
    public function sendFiledir ( $file, $isPrivate )
    {
        global $CFG;
        $filePara = $this->path_from_hash ( $file->contenthash );
        $fileDe = $CFG->dataroot . $filePara;

        if( $isPrivate )
            $acl=\Aws\S3\Enum\CannedAcl::PRIVATE_ACCESS;
        else
            $acl=\Aws\S3\Enum\CannedAcl::PUBLIC_READ;

        try {
            return $this->send ( $file->filename, $fileDe, $filePara, $file->mimetype, $acl );
        }
        catch ( Exception $e ) {
            return null;
        }
    }

    /**
     * @param string $file_filename
     * @param string $fileDe
     * @param string $filePara
     * @param string $file_mimetype
     * @param string $acl
     * @return \Guzzle\Service\Resource\Model
     */
    public function send ( $file_filename, $fileDe, $filePara, $file_mimetype=null, $acl=\Aws\S3\Enum\CannedAcl::PUBLIC_READ )
    {
        if( $file_mimetype == null )
            $file_mimetype = $this->getMimeType( $file_filename );

        $filePara = str_replace ( '/_s/', '/', $filePara );

        if ( !class_exists ( 'S3Util' ) )
            require_once __DIR__ . '/S3Util.php';


        $sendObject =  array (
            'Bucket'             => $this->bucket_name,
            'SourceFile'         => $fileDe,
            'Key'                => S3Util::getPrefix() . $filePara,
            'ACL'                => $acl,
            'CacheControl'       => 'max-age=1296000',
            'Expires'            => date ( 'r', mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ), date ( 'Y' ) + 10 ) ),
            'Etag'               => md5 ( $filePara ),
            'ContentType'        => $file_mimetype,
            'StorageClass'       => \Aws\S3\Enum\StorageClass::STANDARD,
            'ContentDisposition' => 'filename="' . $file_filename . '"',
        );
        try {
            return $this->client->putObject ( $sendObject );
        } catch ( Exception $e ) {}

        return null;
    }

    /**
     * @param $file_contenthash
     * @param $file_statusamazon
     * @return string
     */
    public function getTokenUrl ( $file_contenthash, $file_statusamazon )
    {
        $fileRelativeLocation = substr ( $this->path_from_hash ( $file_contenthash ), 1 );

        if ( !class_exists ( 'S3Util' ) )
            require_once __DIR__ . '/S3Util.php';

        $file = S3Util::getPrefix() . $fileRelativeLocation;

        if( $file_statusamazon == 'public' )
            return S3Util::getS3Url() . $fileRelativeLocation;

        return $this->client->getObjectUrl ( $this->bucket_name, $file, '+5 minutes' );
    }

    /**
     * @param string $contenthash
     * @return string
     */
    public static function path_from_hash ( $contenthash )
    {
        $l1 = $contenthash[ 0 ] . $contenthash[ 1 ];
        $l2 = $contenthash[ 2 ] . $contenthash[ 3 ];

        return '/filedir/' . $l1 . '/' . $l2 . '/' . $contenthash;
    }

    /**
     * @param $file_filename
     * @return string
     */
    private function getMimeType ( $file_filename )
    {
        $pathinfo = pathinfo ( $file_filename );
        $mimeinfo = $this->getMimeTypeList();

        if ( isset( $mimeinfo[ $pathinfo[ 'extension' ] ] ) )
            return $mimeinfo[ $pathinfo[ 'extension' ] ];

        return 'application/octet-stream';
    }

    /**
     * @return array
     */
    private function getMimeTypeList()
    {
        return array (
            'xxx'  => 'document/unknown',
            '3gp'  => 'video/quicktime',
            'aac'  => 'audio/aac',
            'accdb'  => 'application/msaccess',
            'ai'   => 'application/postscript',
            'aif'  => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'applescript'  => 'text/plain',
            'asc'  => 'text/plain',
            'asm'  => 'text/plain',
            'au'   => 'audio/au',
            'avi'  => 'video/x-ms-wm',
            'bmp'  => 'image/bmp',
            'c'    => 'text/plain',
            'cct'  => 'shockwave/director', 
            'cpp'  => 'text/plain',
            'cs'   => 'application/x-csh',
            'css'  => 'text/css',
            'csv'  => 'text/csv',
            'dv'   => 'video/x-dv',
            'dmg'  => 'application/octet-stream',

            'doc'  => 'application/msword',
            'bdoc' => 'application/x-digidoc',
            'cdoc' => 'application/x-digidoc',
            'ddoc' => 'application/x-digidoc',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',

            'dcr'  => 'application/x-director',
            'dif'  => 'video/x-dv',
            'dir'  => 'application/x-director',
            'dxr'  => 'application/x-director',
            'eps'  => 'application/postscript',
            'epub' => 'application/epub+zip',
            'fdf'  => 'application/pdf',
            'flv'  => 'video/x-flv',
            'f4v'  => 'video/mp4',

            'gallery'           => 'application/x-smarttech-notebook',
            'galleryitem'       => 'application/x-smarttech-notebook',
            'gallerycollection' => 'application/x-smarttech-notebook',
            'gif'  => 'image/gif',
            'gtar' => 'application/x-gtar',
            'tgz'  => 'application/g-zip',
            'gz'   => 'application/g-zip',
            'gzip' => 'application/g-zip',
            'h'    => 'text/plain',
            'hpp'  => 'text/plain',
            'hqx'  => 'application/mac-binhex40',
            'htc'  => 'text/x-component',
            'html' => 'text/html',
            'xhtml'=> 'application/xhtml+xml',
            'htm'  => 'text/html',
            'ico'  => 'image/vnd.microsoft.icon',
            'ics'  => 'text/calendar',
            'isf'  => 'application/inspiration',
            'ist'  => 'application/inspiration.template',
            'java' => 'text/plain',
            'jar'  => 'application/java-archive',
            'jcb'  => 'text/xml',
            'jcl'  => 'text/xml',
            'jcw'  => 'text/xml',
            'jmt'  => 'text/xml',
            'jmx'  => 'text/xml',
            'jnlp' => 'application/x-java-jnlp-file',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'jqz'  => 'text/xml',
            'js'   => 'application/x-javascript',
            'latex'=> 'application/x-latex',
            'm'    => 'text/plain',
            'mbz'  => 'application/vnd.moodle.backup',
            'mdb'  => 'application/x-msaccess',
            'mht'  => 'message/rfc822',
            'mhtml'=> 'message/rfc822',
            'mov'  => 'video/quicktime',
            'movie'=> 'video/x-sgi-movie',
            'mw'   => 'application/maple',
            'mws'  => 'application/maple',
            'm3u'  => 'audio/x-mpegurl',
            'mp3'  => 'audio/mp3',
            'mp4'  => 'video/mp4',
            'm4v'  => 'video/mp4',
            'm4a'  => 'audio/mp4',
            'mpeg' => 'video/mpeg',
            'mpe'  => 'video/mpeg',
            'mpg'  => 'video/mpeg',
            'mpr'  => 'application/vnd.moodle.profiling',

            'nbk'       => 'application/x-smarttech-notebook',
            'notebook'  => 'application/x-smarttech-notebook',

            'odt'  => 'application/vnd.oasis.opendocument.text',
            'ott'  => 'application/vnd.oasis.opendocument.text-template',
            'oth'  => 'application/vnd.oasis.opendocument.text-web',
            'odm'  => 'application/vnd.oasis.opendocument.text-master',
            'odg'  => 'application/vnd.oasis.opendocument.graphics',
            'otg'  => 'application/vnd.oasis.opendocument.graphics-template',
            'odp'  => 'application/vnd.oasis.opendocument.presentation',
            'otp'  => 'application/vnd.oasis.opendocument.presentation-template',
            'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
            'ots'  => 'application/vnd.oasis.opendocument.spreadsheet-template',
            'odc'  => 'application/vnd.oasis.opendocument.chart',
            'odf'  => 'application/vnd.oasis.opendocument.formula',
            'odb'  => 'application/vnd.oasis.opendocument.database',
            'odi'  => 'application/vnd.oasis.opendocument.image',
            'oga'  => 'audio/ogg',
            'ogg'  => 'audio/ogg',
            'ogv'  => 'video/ogg',

            'pct'  => 'image/pict',
            'pdf'  => 'application/pdf',
            'php'  => 'text/plain',
            'pic'  => 'image/pict',
            'pict' => 'image/pict',
            'png'  => 'image/png',
            'pps'  => 'application/vnd.ms-powerpoint',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'ps'   => 'application/postscript',
            'pub'  => 'application/x-mspublisher',

            'qt'   => 'video/quicktime',
            'ra'   => 'audio/x-realaudio-plugin',
            'ram'  => 'audio/x-pn-realaudio-plugin',
            'rhb'  => 'text/xml',
            'rm'   => 'audio/x-pn-realaudio-plugin',
            'rmvb' => 'application/vnd.rn-realmedia-vbr',
            'rtf'  => 'text/rtf',
            'rtx'  => 'text/richtext',
            'rv'   => 'audio/x-pn-realaudio-plugin',
            'sh'   => 'application/x-sh',
            'sit'  => 'application/x-stuffit',
            'smi'  => 'application/smil',
            'smil' => 'application/smil',
            'sqt'  => 'text/xml',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'swa'  => 'application/x-director',
            'swf'  => 'application/x-shockwave-flash',
            'swfl' => 'application/x-shockwave-flash',

            'sxw'  => 'application/vnd.sun.xml.writer',
            'stw'  => 'application/vnd.sun.xml.writer.template',
            'sxc'  => 'application/vnd.sun.xml.calc',
            'stc'  => 'application/vnd.sun.xml.calc.template',
            'sxd'  => 'application/vnd.sun.xml.draw',
            'std'  => 'application/vnd.sun.xml.draw.template',
            'sxi'  => 'application/vnd.sun.xml.impress',
            'sti'  => 'application/vnd.sun.xml.impress.template',
            'sxg'  => 'application/vnd.sun.xml.writer.global',
            'sxm'  => 'application/vnd.sun.xml.math',

            'tar'  => 'application/x-tar',
            'tif'  => 'image/tiff',
            'tiff' => 'image/tiff',
            'tex'  => 'application/x-tex',
            'texi' => 'application/x-texinfo',
            'texinfo'  => 'application/x-texinfo',
            'tsv'  => 'text/tab-separated-values',
            'txt'  => 'text/plain',
            'wav'  => 'audio/wav',
            'webm'  => 'video/webm',
            'wmv'  => 'video/x-ms-wmv',
            'asf'  => 'video/x-ms-asf',
            'wma'  => 'audio/x-ms-wma',

            'xbk'  => 'application/x-smarttech-notebook',
            'xdp'  => 'application/pdf',
            'xfd'  => 'application/pdf',
            'xfdf' => 'application/pdf',

            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',

            'xml'  => 'application/xml',
            'xsl'  => 'text/xml',

            'zip'  => 'application/zip',
        );
    }
}