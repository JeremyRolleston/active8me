<?php


App::uses('Model', 'Model');

/**
 * WorkoutActivity model.
 *
 * Add your Workout Activity related methods in the class below
 * 
 */
class WorkoutActivity extends AppModel {
    
    public $name = 'WorkoutActivity';
    public $actsAs = array('Containable');
    
    public $belongsTo = array(
        'Workout' => array(
            'className' => 'Workout',
            'foreignKey' => 'workout_id'
        ),
        'WorkoutType' => array(
            'className' => 'WorkoutType',
            'foreignKey' => 'workout_type_id'
        ),
        'Activity' => array(
            'className' => 'Activity',
            'foreignKey' => 'activity_id'
        ),
        'FitnessLevel' => array(
            'className' => 'FitnessLevel',
            'foreignKey' => 'fitness_level_id'
        )
    );
    
    /*
     * Function will get workout activites data based on workout id, fitness level and workout type id
     * @param $workoutId, $fitnessLevelId
     * @return $workoutPlan
     */
    public function getWorkoutActivities($workoutId, $fitnessLevelId){
        $workoutActivities = $this->find('all',array(
            'conditions'=>array(
                'workout_id'=>$workoutId,
                'fitness_level_id'=>$fitnessLevelId
            ),
            'contain'=>array('Workout'=>array('WorkoutVideo'),'Activity','WorkoutType'),
            'order'=>array('WorkoutActivity.workout_type_id'=>'ASC','Activity.parent_id'=>'ASC','Activity.parent_id'=>'ASC')
        ));
        $workoutPlan = $this->preparePlanData($workoutActivities);        
        return $workoutPlan;
    }
    
    /*
     * Function will prepare workout plan data in desired json format from given activities
     * @param $activities
     * @return $workoutPlan
     */
    private function preparePlanData($activities){
        //intialize workout type id array that will help to create seprate json array for each type
        $activityArr = $workTypeIdArr = [];
        
        if(!empty($activities)){
            $activityArr['Workout'] = $activities[0]['Workout'];
            $videoArr = $activityArr['Workout']['WorkoutVideo'];
            unset($activityArr['Workout']['WorkoutVideo']);
        }
        
        foreach($activities as $activity){            
            //check type id in given array, if not create a workoutType as key
            if(!in_array($activity['WorkoutActivity']['workout_type_id'],$workTypeIdArr)){
                $workTypeIdArr[]=$activity['WorkoutActivity']['workout_type_id'];
                $typeKey = $activity['WorkoutType']['name'];
                //set video data for if workout is AT home
                if(strtolower($typeKey)==strtolower('AT HOME')){
                    $activityArr[$typeKey]['WorkoutVideo'] = $videoArr;
                }
                if(strtolower($typeKey)==strtolower('OUTDOOR')){
                    $typeKey = 'OUTDOORS';
                }
            }
            
            //check if activity is a group
            if(empty($activity['Activity']['parent_id'])){//activity is a group
                $groupActivityData = $this->setWorkoutPlanData($activity,$activities);
                $activityArr[$typeKey]['ActivityGroup'][] = $groupActivityData;
            }
        }
        return $activityArr;
    }
    
    /*
     * Function will get required fields from given activity 
     * @param $activity
     * @return $response
     */
    private function getRequiredActityFields($activity){
        $response = [];
        $response['name'] = $activity['Activity']['name'];
        $response['description1'] = $activity['Activity']['description1'];
        $response['description2'] = $activity['Activity']['description2'];        
        
        if(!empty($activity['WorkoutActivity']['activity_time'])){
            $response['activity_time'] = $activity['WorkoutActivity']['activity_time'];
        }
        if(!empty($activity['WorkoutActivity']['activity_timer'])){
            $response['activity_timer'] = $activity['WorkoutActivity']['activity_timer'];
        }
        if(!empty($activity['Activity']['image'])){
            $response['activity_image'] = $activity['Activity']['image'];
        }
        if(!empty($activity['Activity']['thumb'])){
            $response['activity_thumb'] = $activity['Activity']['thumb'];
        }
        return $response;
    }
    
    /*
     * Function will set alll workout related data into an array
     * @param $currentActivity, $allActivities
     * @return $groupActivityResponse
     */
    private function setWorkoutPlanData($currentActivity, $allActivities){
        
        //prepare group related intomation in array
        $groupActivityResponse = $this->getRequiredActityFields($currentActivity);          
        $groupActivityId = $currentActivity['Activity']['id'];
        $groupWorkoutTypeId = $currentActivity['WorkoutActivity']['workout_type_id'];
        
        $groupActivities = [];
        
//        if(isset($currentActivity['Activity']['ChildActivity'])){
//            $childActivities = $currentActivity['Activity']['ChildActivity'];
//        }
        
        $activityIds = [];
        foreach($allActivities as $activity){
            if($activity['Activity']['parent_id']==''){
                continue;
            }
            //$activity['Activity'] = $child;
            if(
                $activity['Activity']['parent_id']==$groupActivityId
                    &&
                $activity['WorkoutActivity']['workout_type_id']==$groupWorkoutTypeId    
            ){
                if(!in_array($activity['Activity']['id'],$activityIds)){
                    //insert all activites in group create on above step
                    $activityResponse = $this->getRequiredActityFields($activity);
                    $groupActivities[]=$activityResponse;
                    $activityIds[] = $activity['Activity']['id'];
                }                
            }
        }
        $groupActivityResponse['WorkoutActivity'] = $groupActivities;        
        return $groupActivityResponse;
        
    }
}
