<?php


App::uses('Model', 'Model');

/**
 * ProgramNutrition model.
 *
 * Fetch Program nutrition data
 * 
 */
class ProgramNutrition extends AppModel {
    
    public $name = 'ProgramNutrition';
    public $actsAs = array('Containable');
    
    var $belongsTo = array(
        'Nutrition'=>array(
            'className'=>'Nutrition',
            'foreignKey'=>'nutrition_id'
        ),
        'Program'=>array(
            'className'=>'Program',
            'foreignKey'=>'program_id'
        ),
        'NutritionType'=>array(
            'className'=>'NutritionType',
            'foreignKey'=>'nutrition_type'
        )
    );
    
    
    /*
     * Function will fetch program nutritions not cooked and related to program
     * @param $programId, $userId
     * @return $programNutritionData ($data)
     */
    public function fetchProgramNutrition($programId, $userId, $conditions = array()){
        
        //get complete userProgramNutritions Ids
        $userProgramNutritionIds = $this->getUserProgramNutritions($userId);
        
        $currentWeekArr = $this->getCurrentWeekArr();
        
        $orderByStr = '';
        foreach($currentWeekArr as $weekDay){
            $orderByStr .= '"'.$weekDay.'",';
        }
        $orderByStr = trim($orderByStr, ",");
        
        $orderByNutritionType = '"breakfast","lunch","snack","dinner"';
        
        $conditionsArr = array(
                'program_id'=>$programId,
                'NOT'=>array('ProgramNutrition.id'=>$userProgramNutritionIds)
            );
        if(!empty($conditions)){
            $conditionsArr = array_merge($conditionsArr,$conditions);
        }
        
        $data = $this->find('all',array(
            'contain'=>array(
                'Nutrition'=>array(
                    'fields'=>array('id','title','thumb'),
                    'NutritionTips'
                    ),'Program','NutritionType'
            ),
            'conditions'=>$conditionsArr,
            'order'=>array(
                'week ASC',
                'FIELD(ProgramNutrition.day, '.$orderByStr.') ASC',
                'FIELD(ProgramNutrition.nutrition_type, '.$orderByNutritionType.') ASC'
                )
            
        ));
        return $data;
    }
    
    /*
     * Function will get ids of program nutritions based on user id
     * @param $userId
     * @return $programNutritionIds ($data)
     */
    private function getUserProgramNutritions($userId){
        $userProgramNutrition = ClassRegistry::init('UserNutrition');
        $data = $userProgramNutrition->find('list',array(
            'conditions'=>array('user_id'=>$userId,'cooked'=>1),
            'contain'=>false,
            'fields'=>array('id','program_nutrition_id')
        ));
        return $data;
    }
    
    /*
     * Function will get nutrition points for completed nutritions
     * @param $userId
     * @return $points
     */
    public function fetchNutritionPoints($userId){
        //get complete userProgramNutritions Ids
        $userProgramNutritionIds = $this->getUserProgramNutritions($userId);
        $points = count($userProgramNutritionIds) * NUTRITION_POINTS;
        return $points;
    }
    
    

}
