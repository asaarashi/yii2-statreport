<?php

namespace thrieu\statreport;

use Yii;
use yii\grid\GridView;

class DataTablesGridView extends GridView {
    public $layout = "{items}\n";

    /**
     * Renders the table header.
     * @return string the rendering result.
     */
    public function renderTableBody()
    {
        return '';
    }
}