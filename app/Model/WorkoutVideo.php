<?php


App::uses('Model', 'Model');

/**
 * WorkoutVideo model.
 *
 * Add your Workout Video related methods in the class below
 * 
 */
class WorkoutVideo extends AppModel {
    
    public $name = 'WorkoutVideo';
    
    public $belongsTo = array(
        'Workout' => array(
            'className' => 'Workout',
            'foreignKey' => 'workout_id'
        )
    );
}
