<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of linksParser
 *
 * @author Ildar-dev
 */
class linkParser {
    
    public $level = 1.0;
    
    public $accept = array('html','php','');
    
    public $exists = 'undefined';
    
    public function __construct( $link, $level = 1.0 ){
        
        
        
        $this->link = $link;
 
        $this->level = $level < 0.1 ? 0.1 : $level;

 	console::log('Begin processing link '. $this->link . ' with level ' . $this->level );

        if( !in_array( $this->getExtension(), $this->accept) ){
            
            console::log('ERR: Wrong extension: ' . $this->getExtension());
            
            return;
          
        }
      
        if( $this->isExists() ){
            
            console::log('ERR: Already exists!');
            
            return;
            
        }
        
        $html = $this->loadHtml();
        
        if( $html == null ){
            
            console::log('ERR: Load html error!');
            
            return;
            
        }
        
        $this->save();
        
    }
    
    public function save(){
        
        $sql = 'INSERT INTO `links` SET '
                .'`link` = "' . $this->link . '", `level` ="' . $this->level . '";';
    
        if( mysql_query( $sql ) ){
        
            return console::log('Saved!');
            
        }
        
        console::log('ERR: Can`t save to database!');
        
    }
    
    public function isExists(){
        
        if( $this->exists != 'undefined' ){
            
            return $this->exists;
            
        }
        
        $this->exists = FALSE;
        
        $sql = 'SELECT * FROM `links` WHERE `link` LIKE "' . $this->link . '"';
        
        $r = mysql_query( $sql );
        
        
        
        if( mysql_num_rows( $r ) > 0 ){
            
            $data = mysql_fetch_array($r);
            
            if( $data['level'] < $this->level ){
                
                $this->updatelevel( $this->level );
            }
            
            $this->exists = TRUE;
            
        }
        
        return $this->exists;
        
    }
    
    public function getLinks(){
        
        $html = $this->loadHtml();
        
        $regexp = '/href="([^\" >]*?)"/';
        
        preg_match_all( $regexp, $html, $results);
        
        $html = null;
        
        return $results[1];
        
    }
    public function updateLevel( $newlevel ){
        
        console::log('MSG: Update level to ' . $newlevel );
        
        mysql_query('UPDATE `links` SET `level`="' . $newlevel . '" WHERE `link`="'. $this->link .'";');
        
    }
    
    public function getExtension(){
        
        $parts = explode('/', $this->link);
        
        if( !is_array($parts) OR empty($parts[count($parts)]) ){
            
            return '';
            
        }
        

        $parts = explode('.', $parts[count($parts)-1]);
        
        if(is_array($parts)){
            
            return mb_strtolower( $parts[count($parts)-1], 'UTF-8' );
            
        }
        
        return '';
        
    }
    public function loadHtml(){
        $options = array(
               CURLOPT_RETURNTRANSFER => true,     // return web page
               CURLOPT_HEADER         => false,    // don't return headers
               CURLOPT_FOLLOWLOCATION => true,     // follow redirects
               CURLOPT_ENCODING       => "",       // handle all encodings
               CURLOPT_USERAGENT      => "spider", // who am i
               CURLOPT_AUTOREFERER    => true,     // set referer on redirect
               CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
               CURLOPT_TIMEOUT        => 120,      // timeout on response
               CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
           );

           $ch      = curl_init( $this->link );
           curl_setopt_array( $ch, $options );
           $content = curl_exec( $ch );
           $err     = curl_errno( $ch );
           $errmsg  = curl_error( $ch );
           $header  = curl_getinfo( $ch );
           curl_close( $ch );

           if( $err != 0 ) return null;

           return str_replace("\n", "", $content);

        
    }
    
    
}

?>
