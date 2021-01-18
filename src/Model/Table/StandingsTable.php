<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class StandingsTable extends Table {
    public function initialize(array $config)
    {
        $this->belongsTo('Sites');
        $this->belongsTo('Winners');
    }
}