<?php

class sitemapGenerator{
    
    public $entryPoint = '';
    
    public $options = array( 'perPage' => 50000 );
    
    public function __construct( $entryPoint ){
        
        $this->entryPoint = $entryPoint;
        
    }
    
    public function loadDatabase(){
        
        mysql_query('TRUNCATE `links`;');
        
        $this->loadLink( $this->entryPoint, 1.0 );
        
    }
    
    private function loadLink( $link, $level ){
        
        $lprc = new linkParser( $link, $level);
        
        $links = $lprc->getLinks();
        
        if( $lprc->isExists() ){
            
            return;
        
            
        }
        
        if( count($links) == 0 ){
            
            return;
            
        }
        
        $level = $level - 0.1;
        
        for( $i=0; $i< count($links); $i++ ){
            
            $link = $this->getLink( $links[$i] );
            
            if( in_array( $link, array('#','/') ) ) continue;
            
            $this->loadLink( $link, $level );
            
        }
        
    }
    
    private function getLink( $link ){
        
        if( mb_strstr( $this->entryPoint, $link, FALSE, 'UTF-8') !== FALSE )
        {
            
            return $link;
            
        }
        
        
        if( $link[0] != '/' ){
            
            if( mb_strstr( $this->entryPoint, 'http://', FALSE, 'UTF-8') !== FALSE ){
            
                return '#';
            
            }
            
            return $this->entryPoint . $link;
    
        }
        
        return $this->entryPoint . mb_substr( $link, 1 );
    }
    
    public function saveFiles(){
        
        console::log('Begin files generation!');
        
        $r = mysql_query('SELECT count(`id`) FROM `links`;');
        
        $count = mysql_fetch_array( $r );
        
        if( $count[0] > $this->options['perPage'] ){
            
            $this->saveToManyFiles( $count[0] );
            
        }else{
            
            $this->saveToOneFile();
        }
    }
    
    public function saveToOneFile(){
        
        console::log('Writing sitemap.xml');
        
        $fp = fopen('sitemap.xml', 'w+');
        
        $str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
               .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        fwrite($fp, $str);
        
        $r = mysql_query('SELECT * FROM `links`;');
        
        while( $link = mysql_fetch_array( $r ) ){
            
            console::log('Writing ' . $link['link'] . ' ' . $link['level'] );
            
            $str = "\t" . '<url>' . "\n" 
                   . "\t\t" . '<loc>' . $link['link'] . '</loc>' . "\n"
                   . "\t\t" . '<lastmod>' . date('Y-m-d') . '</lastmod>' . "\n"
                   . "\t\t" . '<changefreq>daily</changefreq>' . "\n"
                   . "\t\t" . '<priority>' . $link['level'] . '</priority>' . "\n"
                   . "\t" . '</url>' . "\n";
            
            fwrite($fp, $str);
            
        }
        
        $str = '</urlset>';
        
        fwrite($fp, $str);
        
        fclose($fp);
        
        console::log('Finish sitemap.xml!');
        
    }
    
    public function saveToManyFiles( $linksCount ){
        
        $perPage = $this->options['perPage'];
        
        $pagesCount = ceil( $linksCount/$perPage );
        
        console::log('Writing sitemap.xml');
        
        $fp = fopen( 'sitemap.xml', 'w+');
        
        $str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        .'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        fwrite($fp, $str);
        
        for( $i = 0; $i < $pagesCount; $i++ ){
            
            $str = "\t" . '<sitemap>' . "\n"
                   . "\t\t" . '<loc>' . $this->entryPoint .'sitemap' . $i . '.xml</loc>' . "\n"
                   . "\t\t" . '<lastmod>' . date('Y-m-d') . '</lastmod>' . "\n"
                   . "\t" . '</sitemap>' . "\n";
            
            fwrite($fp, $str);
            
            console::log('Writing sitemap' . $i . '.xml');
            
            $fpsm = fopen('sitemap' . $i . '.xml', 'w+');
            
            $str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                   .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            fwrite($fpsm, $str);  
            
            $sql = 'SELECT * FROM `links` LIMIT ' . ( $i * $perPage ) . ', ' . $perPage . ';';
            
            $r = mysql_query( $sql );
        
            while( $link = mysql_fetch_array($r) ){
                
                console::log('Writing ' . $link['link'] . ' ' . $link['level'] );
                
                $str = "\t" . '<url>' . "\n" 
                       . "\t\t" . '<loc>' . $link['link'] . '</loc>' . "\n"
                       . "\t\t" . '<lastmod>' . date('Y-m-d') . '</lastmod>' . "\n"
                       . "\t\t" . '<changefreq>daily</changefreq>' . "\n"
                       . "\t\t" . '<priority>' . $link['level'] . '</priority>' . "\n"
                       . "\t" . '</url>' . "\n";

                fwrite($fpsm, $str);
            }
            
            $str = '</urlset>';

            fwrite($fpsm, $str);
        
            fclose($fpsm);
            
            console::log('Finish sitemap' . $i . '.xml');
            
        }
            
            
        $str = '</sitemapindex>';
        
        fwrite($fp, $str);
        
        fclose($fp);
        
        console::log('Finish sitemap.xml!');
        
    }
    
    
}
?>