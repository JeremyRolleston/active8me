<?php


App::uses('Model', 'Model');

/**
 * Nutrition model.
 *
 * Add your Nutrition related methods in the class below
 * 
 */
class Nutrition extends AppModel {
    
    public $name = 'Nutrition';
    public $actsAs = array('Containable');
    
    public $hasMany = array(
        'NutritionTips'=>array(
            'className'=>'NutritionTips',
            'foreignKey'=>'nutrition_id',
            'dependent'=>true
        )
    );
}
