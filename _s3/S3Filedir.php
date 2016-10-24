<?php

/***************************
 * User: Eduardo Kraus
 * @url https://www.eduardokraus.com/
 * Date: 01/01/2016
 * Time: 16:56
 ***************************/

/**
 * Class S3Filedir
 */
class S3Filedir extends SendS3
{
    /**
     * @param $relativepath
     * @return bool
     */
    public function isFileInAmazon ( $relativepath ) {
        global $DB;

        $args = explode ( '/', ltrim ( $relativepath, '/' ) );

        $contextid = (int) array_shift ( $args );
        $component = clean_param ( array_shift ( $args ), PARAM_COMPONENT );
        $filearea = clean_param ( array_shift ( $args ), PARAM_AREA );

        $sql = "SELECT *
                  FROM {files}
                 WHERE contextid = :contextid
                   AND component = :component
                   AND filearea  = :filearea
                   AND filename != '.'";
        $result = $DB->get_records_sql ( $sql, array (
                    'contextid' => $contextid,
                    'component' => $component,
                    'filearea'  => $filearea
                ) );

        if ( count ( $result ) > 1 )
            return false;

        foreach ( $result as $file ) {
            if ( !$this->testCreateColumn ( $file ) )
                return false;

            if ( $file->statusamazon != 'private' && $file->statusamazon != 'public' )
                $this->sendToAws ( $file );

            header ( 'Location: ' . $this->getTokenUrl ( $file->contenthash, $file->statusamazon ) );
            die();
        }

        return false;
    }

    private function testCreateColumn ( $file ) {
        global $DB;

        if ( !isset( $file->statusamazon ) ) {
            $table = new xmldb_table( 'file' );
            $field = new xmldb_field( 'statusamazon', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'status' );

            $dbman = $DB->get_manager ();
            if ( !$dbman->field_exists ( $table, $field ) ) {
                $dbman->add_field ( $table, $field );
            }
        }

        return true;
    }

    /**
     * @param $file
     */
    private function sendToAws ( $file ) {
        global $DB;

        $isPrivate = strpos ( $file->component, 'mod_' ) === 0;
        $this->sendFiledir ( $file, $isPrivate );

        if ( $isPrivate )
            $file->statusamazon = 'private';
        else
            $file->statusamazon = 'public';

        $DB->update_record ( 'files', $file );
    }
}