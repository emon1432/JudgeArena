<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\JetstreamServiceProvider;
use App\Providers\MailConfigServiceProvider;
use LaraIzitoast\LaraIzitoastServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    JetstreamServiceProvider::class,
    MailConfigServiceProvider::class,
    LaraIzitoastServiceProvider::class,
];
