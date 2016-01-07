<?php

namespace Silverorange\Autoloader;

$package = new Package('silverorange/news_flash');

$package->addRule(new Rule('exceptions', 'NewsFlash', 'Exception'));
$package->addRule(new Rule('', 'NewsFlash'));

Autoloader::addPackage($package);

?>
