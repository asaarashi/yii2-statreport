<?php

namespace thrieu\statreport;

use yii\web\AssetBundle;

class DataTablesAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower/datatables/media';
    public $css = [
        'css/jquery.dataTables.css',
    ];
    public $js = [
        'js/jquery.dataTables.js'
    ];
    public $depends = ['yii\web\JqueryAsset'];
}
