<?php


App::uses('Model', 'Model');

/**
 * Activity model.
 *
 * Fetch Activity data
 * 
 */
class Activity extends AppModel {
    
    public $name = 'Activity';
    public $actsAs = array('Containable');
    
    var $hasMany = array(
        'ChildActivity'=>array(
            'className'=>'Activity',
            'foreignKey'=>'parent_id'
        )
    );
}
