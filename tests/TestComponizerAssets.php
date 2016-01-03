<?php

use Gavrya\Componizer\Asset\AssetsCollection;
use Gavrya\Componizer\Asset\ExternalCssAsset;
use Gavrya\Componizer\Asset\ExternalJsAsset;
use Gavrya\Componizer\Asset\InternalCssAsset;
use Gavrya\Componizer\Asset\InternalJsAsset;

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
