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
     * @throws coding_exception
     */
    public function isFileInAmazon ( $relativepath )
    {
        global $DB;

        $args = explode('/', ltrim($relativepath, '/'));

        $contextid = (int) array_shift ( $args );
        $component = clean_param ( array_shift ( $args ), PARAM_COMPONENT );
        $filearea  = clean_param ( array_shift ( $args ), PARAM_AREA );

        $sql = "SELECT * FROM {files}
                 WHERE contextid =  " . $contextid . "
                   AND component = '" . $component . "'
                   AND filearea  = '" . $filearea . "'
                   AND filename != '.'";
        $result = $DB->get_records_sql( $sql );

        if( count($result) > 1 )
            return;

        foreach($result as $file)
        {
            if ( !$this->testCreateColumn ( $file ) )
                return;

            if( $file->statusamazon == 'nao' )
                $this->sendToAws ( $file );

            header('Location: '. $this->getTokenUrl ( $file->contenthash, $file->statusamazon ) );
            die();
        }
        return;
    }

    private function testCreateColumn ( $file )
    {
        global $CFG, $DB;
        if ( !isset( $file->statusamazon ) ) {
            try{
                $DB->change_database_structure ( "ALTER TABLE `" . $CFG->prefix . "files` ADD `statusamazon` ENUM('nao','private','public') NOT NULL DEFAULT 'nao' AFTER `status` " );
            }catch ( Exception $e ){}

            return false;
        }

        return true;
    }

    /**
     * @param $file
     */
    private function sendToAws ( $file )
    {
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