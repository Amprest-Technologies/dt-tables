<?php

namespace Amprest\LaravelDatatables\Http\Controllers;

use Amprest\LaravelDatatables\Traits\HasAssets;
use Illuminate\Routing\Controller;

class AppController extends Controller
{
    use HasAssets;

    /**
     *  Declate the css asset
     *
     * @var string
     */
    protected string $cssPath = __DIR__.'/../../../public/css/app.css';

    /**
     *  Declate the js asset
     *
     * @var string
     */
    protected string $jsPath = __DIR__.'/../../../public/js/app.js';
}
