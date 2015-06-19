<?php

namespace thrieu\statreport;

use yii\base\Object;
use yii\base\InvalidConfigException;

class DataSeries extends Object {
    public $name;
    public $isInChart = true;
    public $value;
    public $encode = true;

    public function init() {
        if( ! $this->name && ! $this->value) {
            throw new InvalidConfigException('The "name" or "value" property must be specified.');
        }
    }
}