<?php
/**
 * Created by PhpStorm.
 * User: gavrya
 * Date: 10/25/15
 * Time: 2:49 PM
 */

namespace Gavrya\Gravitizer\Skeleton;


interface GravitizerComponent
{

    public function id();

    public function name();

    public function version();

    public function info();

    public function hasAssets();

    public function assetsDir();

    public function init($lang);

    public function up();

    public function down();

}