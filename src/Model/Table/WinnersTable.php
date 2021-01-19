<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;

class WinnersTable extends Table {

    public function initialize(array $config){
        $this->hasOne('Standings');
        $this->belongsTo('Prizes',['propertyName' => 'Prize']);
    }
    public function findByToken(Query $query, array $options){
        $token = $options['token'];
        return $query->contain(['standings'])->where(['token' => $token]);
    }
}