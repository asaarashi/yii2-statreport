<?php

namespace thrieu\statreport;

use yii\web\AssetBundle;

class DataTablesBootstrapAsset extends AssetBundle {

    public $sourcePath = '@vendor/bower/datatables-bootstrap3/BS3/assets';
    public $css = [
        'css/datatables.css',
    ];
    public $js = [
        'js/datatables.js'
    ];
    public $depends = ['thrieu\statreport\DataTablesAsset'];
}