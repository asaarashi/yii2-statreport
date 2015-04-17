<?php

namespace thrieu\statreport;

use yii\base\Object;
use yii\base\InvalidConfigException;

class DataSeries extends Object {
    public $name;
    public $isInChart = true;

    public function init() {
        if( ! $this->name) {
            throw new InvalidConfigException('The "name" property must be specified.');
        }
    }
}