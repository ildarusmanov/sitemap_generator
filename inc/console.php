<?php

class console {
    
    public static $log = '';

    public static $output = 'console';
    
    public static function log( $message ){
	
        if( self::$output == 'console' ){
		
		echo $message . "\n";
		
		return;

	} 

        self::$log .= $message . "\n";
	
                
    }
    
    public static function saveTo( $fileName ){
        
        $fp = fopen( $fileName, 'w+' );
        
        fwrite( $fp, self::$log );
        
        fclose( $fp );
        
    }
    
}

?>
