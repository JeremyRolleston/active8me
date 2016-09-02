<?php


App::uses('Model', 'Model');

/**
 * Workout model.
 *
 * Add your Workout related methods in the class below
 * 
 */
class Workout extends AppModel {
    
    public $name = 'Workout';
    
    public $hasMany = array(
        'WorkoutVideo' => array(
            'className' => 'WorkoutVideo',
            'foreignKey' => 'workout_id',
            'order' => 'WorkoutVideo.title ASC'
        )
    );
}
