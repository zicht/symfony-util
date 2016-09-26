<?php

require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';

if (preg_match('!^/admin/!', $_SERVER['REQUEST_URI'])) {
    (new AppKernel('admin'))->web();
} else {
    (new AppKernel('site'))->web();
}
