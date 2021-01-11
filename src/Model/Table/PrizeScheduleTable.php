<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class PrizeScheduleTable extends Table {
    public $actsAs = array('Containable');
    public $belongsTo = array('Prize');
}
