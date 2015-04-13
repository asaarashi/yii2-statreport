<?php

namespace thrieu\statreport;

use yii\base\Object;
use yii\helpers\Html;

class Series extends Object
{
    /**
     * @var DataTablesGridView
     */
    public $header;
    public $footer;
    public $visible = true;
    public $headerOptions = [];
    public $footerOptions = [];
    public $isInChart = true;

}
