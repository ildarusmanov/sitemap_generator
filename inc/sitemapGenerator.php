<?php

class sitemapGenerator{    

    public $entryPoint = '';
    
    public $options = array( 'perPage' => 50000 );


    public function __construct( $entryPoint ){
        
        $this->entryPoint = $entryPoint;

	$this->clearLinks();
	
	$this->clearQueue();

	$this->loadLink( $this->getLink( $this->entryPoint ), 1.0 );

	$this->loadDatabase( 0.9 );

	$this->saveFiles();
        
    }
    
    public function loadDatabase( $level ){

	if( $r = mysql_query('SELECT * FROM `queue`;') ){

			if( mysql_num_rows( $r ) > 0 ){

				while( $link = mysql_fetch_array( $r ) ){

					$this->loadLink( urldecode( $link['link'] ), $level );
		
				}

			}

	}

	$this->clearQueue();
			


	if( $this->haveQueue() ) {

		$this->loadDatabase( $level - 0.1  );

	}
        
    }
    
    private function loadLink( $link, $level ){
        
        $lprc = new linkParser( $link, $level);
        
        if( $lprc->error != 0 ){
            
            return;
        
            
        }

        if( $lprc->isExists() ){
            
            return;
        
            
        }    
        
	$links = $lprc->getLinks();

        if( count($links) == 0 ){
            
            return;
            
        }
        
        $level = $level - 0.1;
        
        for( $i=0; $i< count($links); $i++ ){
            
            $link = $this->getLink( $links[$i], $lprc->link );

            if( in_array( $link, array('#','/') ) ) continue;

	    $this->addQueue( $link );
            
        }
        
    }

    private function addQueue( $link ){

	$sql = 'SELECT `id` FROM `queue` WHERE `link` = "' . urlencode( $link ) . '";';	
	
	$r = mysql_query( $sql );
	
	if( mysql_error() != '' OR mysql_num_rows( $r ) > 0 ){

		return;

	}
	
        $sql = 'INSERT INTO `queue` SET '
                .'`link` = "' . urlencode( $link ) 
		. '";';
    
        mysql_query( $sql );
        
            
        if( mysql_error() != '' ){

		console::log('ERR: MySQL: ' . mysql_error() );

	}

    }

    private function clearQueue(){

	mysql_query('TRUNCATE `queue`;');

    }

    private function clearLinks(){

	mysql_query('TRUNCATE `links`;');

    }

    private function haveQueue(){

	$r = mysql_query('SELECT count(*) as `count` FROM `queue`;');
	
	$count = mysql_fetch_array( $r );

	return $count['count'] > 0 ? TRUE : FALSE ;
	
	
    }

    private function getLink( $link, $currentLink = '' ){


	if( mb_substr( $link, mb_strlen($link) - 1, 1 ) == '/' ){

		$link = mb_substr( $link, 0, mb_strlen($link) - 1 );	

	}        

	if( $link == '' ){

		return 	mb_substr( $this->entryPoint, 0, mb_strlen($link) - 1 );
		
	}


        if( mb_strlen($link) > 0 AND mb_strstr( $this->entryPoint, $link, FALSE, 'UTF-8') !== FALSE )
        {
            
            return $link;
            
        }

        if( mb_substr($link, 0, 1) != '/' ){
            
            if( mb_strstr( $link, '#', FALSE, 'UTF-8') !== FALSE 
		OR mb_strstr( $link, 'http://', FALSE, 'UTF-8') !== FALSE 
	    ){
            
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
       

        $fp = fopen(SITEMAP_DIR . 'sitemap.xml', 'w+');
        
        $str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
               .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        fwrite($fp, $str);
        
        $r = mysql_query('SELECT * FROM `links`;');
        
        while( $link = mysql_fetch_array( $r ) ){

            $link['link'] = urldecode( $link['link'] );

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
        
        $fp = fopen(SITEMAP_DIR . 'sitemap.xml', 'w+');
        
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
            
            $fpsm = fopen(SITEMAP_DIR . 'sitemap' . $i . '.xml', 'w+');
            
            $str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                   .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            fwrite($fpsm, $str);  
            
            $sql = 'SELECT * FROM `links` LIMIT ' . ( $i * $perPage ) . ', ' . $perPage . ';';
            
            $r = mysql_query( $sql );
        
            while( $link = mysql_fetch_array($r) ){
		
		$link['link'] = urldecode( $link['link'] );
                
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
