<?php

/**
 *  Created by Ildar Usmanov
 *  e-mail: wajox@mail.ru
 *  http://github.com/ildarusmanov/sitemap_generator
 *  Creation date 14.11.2012
 */

$START_TIME = microtime( true );

require_once 'config.php';

require_once 'inc/db.php';

require_once 'inc/sitemapGenerator.php';

require_once 'inc/linksParser.php';

require_once 'inc/console.php';

$generator = new sitemapGenerator( START_URL );

$generator->loadDatabase();

$generator->saveFiles();

$FINISH_TIME = microtime( true );

$EXEC_TIME = $FINISH_TIME - $START_TIME;

console::log('Started: ' . $START_TIME );

console::log('Finished: ' . $FINISH_TIME );

console::log('Execution time: ' . $EXEC_TIME );


?>
