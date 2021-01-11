<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class NotesTable extends Table {
    public function getLastNote($winnerID)
    {
        $latestNote=$this->find('first',array(
            'conditions'=>array(
                'winner_id'=>$winnerID
            ),
            'order'=>array('id'=>'DESC')
        ));
        
        $result = isset($latestNote['Notes']['note'])?date('m/d:',strtotime($latestNote['Notes']['date_time']))." ".$latestNote['Notes']['note']:"";
        
        return $result;
    }
    
    public function noteLog(
        $note,
        $winnerID,
        $userID='',
        $type='system'
    )
    {
        date_default_timezone_set('EST');
        $systemLog = [
            'user_id' => $userID,
            'winner_id' => $winnerID, 
            'date_time' => date('Y-m-d H:i:s'), 
            'note' => $note, 
            'type' => $type, 
        ];

        $note = $this->newEntity($systemLog);

        return $this->save($note);
    }
}