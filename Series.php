<?php

namespace thrieu\statreport;

use yii\base\Object;

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
