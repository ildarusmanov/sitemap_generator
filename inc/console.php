<?php

class console {
    
    public static $log = '';
    
    public static function log( $message ){
        
        self::$log .= $message . "\n";
                
    }
    
    public static function saveTo( $fileName ){
        
        $fp = fopen( $fileName, 'w+' );
        
        fwrite( $fp, self::$log );
        
        fclose( $fp );
        
    }
    
}

?>
