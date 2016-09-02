<?php


App::uses('Model', 'Model');

/**
 * ProgramWorkout model.
 *
 * Fetch Program workout data
 * 
 */
class ProgramWorkout extends AppModel {
    
    public $name = 'ProgramWorkout';
    public $actsAs = array('Containable');
    
    var $belongsTo = array(
        'Workout'=>array(
            'className'=>'Workout',
            'foreignKey'=>'workout_id'
        ),
        'Program'=>array(
            'className'=>'Program',
            'foreignKey'=>'program_id'
        )
    );
    
    
    public function fetchProgramWorkout($programId, $userId, $conditions = array()){
        
        //get complete userProgramWorkout Ids
        $userProgramWoekoutIds = $this->getUserProgramWorkouts($userId);
        
        $currentWeekArr = $this->getCurrentWeekArr();
        
        $orderByStr = '';
        foreach($currentWeekArr as $weekDay){
            $orderByStr .= '"'.$weekDay.'",';
        }
        $orderByStr = trim($orderByStr, ",");
        
        $conditionsArr = array(
                'program_id'=>$programId,
                'NOT'=>array('ProgramWorkout.id'=>$userProgramWoekoutIds)
            );
        if(!empty($conditions)){
            $conditionsArr = array_merge($conditionsArr,$conditions);
        }
        
        $data = $this->find('all',array(
            'contain'=>array(
                'Workout','Program'
            ),
            'conditions'=>$conditionsArr,
            'order'=>array(
                'week ASC',
                'FIELD(ProgramWorkout.day, '.$orderByStr.') ASC'
                )
            
        ));
        
        return $data;
    }
    
    /*
     * Function will get ids of completed program workouts based on user id
     * @param $userId
     * @return $programWorkoutIds ($data)
     */
    private function getUserProgramWorkouts($userId){
        $userProgramWorkout = ClassRegistry::init('UserWorkout');
        $data = $userProgramWorkout->find('list',array(
            'conditions'=>array('user_id'=>$userId,'completed_status'=>1),
            'contain'=>false,
            'fields'=>array('id','program_workout_id')
        ));
        return $data;
    }
    
    /*
     * Function will get workout points for completed workouts
     * @param $userId
     * @return $points
     */
    public function fetchWorkoutPoints($userId){
        //get complete userProgramWorkout Ids
        $userProgramWoekoutIds = $this->getUserProgramWorkouts($userId);
        $points = count($userProgramWoekoutIds) * WORKOUT_POINTS;
        return $points;
    }

}
