<?php


App::uses('Model', 'Model');

/**
 * UserNutrition model.
 *
 * Fetch User nutrition data
 * 
 */
class UserNutrition extends AppModel {
    
    public $name = 'UserNutrition';
    public $actsAs = array('Containable');
    
    var $belongsTo = array(
        'Nutrition'=>array(
            'className'=>'ProgramNutrition',
            'foreignKey'=>'program_nutrition_id'
        ),
        'User'=>array(
            'className'=>'User',
            'foreignKey'=>'user_id'
        )
    );
    
    
    /*
     * Function will check if en entry exists for given programNutrition and user id
     * @param $userId, $programNutritionId
     * @return $userNutritionId
     */
    public function getUserNutrition($userId, $programNutritionId){
        $userNutritions = $this->find('first',array(
            'conditions'=>array(
                'user_id'=>$userId,
                'program_nutrition_id'=>$programNutritionId
            ),
            'contain'=>false
        ));
        return  $userNutritions;
    }

}
