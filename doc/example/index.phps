#!/usr/bin/env php
<?php

umask(0000);
set_time_limit(0);

require_once __DIR__.'/bootstrap.php.cache';
require_once __DIR__.'/AppKernel.php';

(new AppKernel())->console();
