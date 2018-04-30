#!/usr/bin/env php
<?php

umask(0000);
set_time_limit(0);

require_once __DIR__.'/bootstrap.php.cache';
require_once __DIR__.'/AppKernel.php';

$input = new Symfony\Component\Console\Input\ArgvInput();

(
    new AppKernel(
        $input->getParameterOption(['--env', '-e'], null),
        $input->hasParameterOption('--no-debug') ? false : null
    )
)->console($input);
