<?php

namespace thrieu\statreport;

use yii\web\AssetBundle;

class FixedHeaderAsset extends AssetBundle {

    public $sourcePath = '@vendor/bower/bootstrap-pagy';
    public $js = [
        'src/bootstrap-pagy.min.js',
    ];
    public $depends = ['thrieu\statreport\DataTablesAsset'];
}
