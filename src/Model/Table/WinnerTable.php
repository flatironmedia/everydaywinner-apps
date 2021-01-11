<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class WinnerTable extends Table {
    var $actsAs = array('Containable');

    public $hasOne = array(
        'Standing'
    );

    public $belongsTo = array('Prize');

}