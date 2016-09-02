<?php


App::uses('Model', 'Model');

/**
 * UserNutrition model.
 *
 * Fetch User nutrition data
 * 
 */
class UserWorkout extends AppModel {
    
    public $name = 'UserWorkout';
    public $actsAs = array('Containable');
    
    var $belongsTo = array(
        'ProgramWorkout'=>array(
            'className'=>'ProgramWorkout',
            'foreignKey'=>'program_workout_id'
        ),
        'User'=>array(
            'className'=>'User',
            'foreignKey'=>'user_id'
        )
    );
    
    
    /*
     * Function will check if en entry exists for given programWorkout and user id
     * @param $userId, $programWorkoutId
     * @return $userWorkoutId
     */
    public function getUserWorkout($userId, $programWorkoutId){
        $userWorkouts = $this->find('first',array(
            'conditions'=>array(
                'user_id'=>$userId,
                'program_workout_id'=>$programWorkoutId
            ),
            'contain'=>false
        ));
        return  $userWorkouts;
    }

}
