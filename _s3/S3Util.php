<?php

/***************************
 * User: Eduardo Kraus
 * @url https://www.eduardokraus.com/
 * Date: 01/01/2016
 * Time: 22:30
 ***************************/
class S3Util
{
    /**
     * @return string
     */
    public static function getS3Url ()
    {
        global $CFG;

        if ( !isset( $CFG->aws_s3_bucket ) )
            return '';

        if ( isset( $CFG->aws_cloudfront_url ) )
            return 'https://' . $CFG->aws_cloudfront_url . S3Util::getPrefix ();

        return 'https://' . $CFG->aws_s3_bucket . '.s3.amazonaws.com' . S3Util::getPrefix ();
    }

    /**
     * @return string
     */
    public static function getPrefix ()
    {
        global $CFG;

        if ( isset( $CFG->aws_s3_basepath ) && strlen ( $CFG->aws_s3_basepath ) > 3 )
            return $CFG->aws_s3_basepath;

        /** @var array $wwwroots */
        $wwwroots = explode ( '/', $CFG->wwwroot );
        array_shift ( $wwwroots );
        array_shift ( $wwwroots );

        return '/' . implode ( '/', $wwwroots ) . '/';
    }
}