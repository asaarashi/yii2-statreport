<?php

namespace thrieu\statreport;

use yii\web\AssetBundle;

class DataTablesAsset extends AssetBundle
{
    public $sourcePath = '@vendor/datatables/datatables/media';
    public $css = [
        'css/jquery.dataTables.min.css',
    ];
    public $js = [
        'js/jquery.dataTables.min.js'
    ];
    public $depends = ['yii\web\JqueryAsset'];
}
