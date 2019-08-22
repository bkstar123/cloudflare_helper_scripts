<?php
require(__DIR__.'/vendor/autoload.php');

/**
 * load environment variables from .env
 */
(new Symfony\Component\Dotenv\Dotenv())->load(__DIR__.'/.env');
