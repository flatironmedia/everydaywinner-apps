<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class PrizeSchedulesTable extends Table {
    public function initialize(array $config)
    {
        $this->belongsTo('Prizes');
    }
}
