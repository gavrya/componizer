<?php

use Gavrya\Componizer\Assets\AssetsCollection;
use Gavrya\Componizer\Assets\ExternalCssAsset;
use Gavrya\Componizer\Assets\ExternalJsAsset;
use Gavrya\Componizer\Assets\InternalCssAsset;
use Gavrya\Componizer\Assets\InternalJsAsset;

include('init.php');

$assetsCollection = new AssetsCollection();

var_dump($assetsCollection);

$externalCssAsset = new ExternalCssAsset('/componizer/12345678/css/file.css');

var_dump($externalCssAsset);

$externalJsAsset = new ExternalJsAsset('/componizer/12345678/js/file.js');

var_dump($externalJsAsset);

$internalCssAsset = new InternalCssAsset('<style>alert(\'hello\');</style>');

var_dump($internalCssAsset);

$internalJsAsset = new InternalJsAsset('<script>alert(\'hello\');</script>');

var_dump($internalJsAsset);

$assetsCollection->add([
    $externalCssAsset,
    $externalJsAsset,
    $internalCssAsset,
    $internalJsAsset,
]);

$assetsCollection->inject([
    $internalCssAsset,
    $internalJsAsset,
]);

var_dump($assetsCollection->getAssets());
