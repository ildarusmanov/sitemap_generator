<?php

/**
 *  Created by Ildar Usmanov
 *  e-mail: wajox@mail.ru
 *  http://github.com/ildarusmanov/sitemap_generator
 *  Creation date 14.11.2012
 */

require_once 'config.php';

require_once 'inc/db.php';

require_once 'inc/sitemapGenerator.php';

require_once 'inc/linksParser.php';

require_once 'inc/console.php';

$generator = new sitemapGenerator( START_URL );

$generator->loadDatabase();

$generator->saveFiles();


?>
