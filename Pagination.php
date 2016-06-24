<?php

namespace thrieu\statreport;

use yii\web\AssetBundle;

class Pagination extends AssetBundle {

    public $sourcePath = '@vendor/bower/bootstrap-pagy';
    public $js = [
        YII_DEBUG ? 'src/bootstrap-pagy.js' : 'src/bootstrap-pagy.min.js',
    ];
    public $depends = ['thrieu\statreport\DataTablesAsset'];
}
