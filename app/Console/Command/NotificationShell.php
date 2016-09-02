<?php


/**
 * Notification Shell
 *
 * Add your notification methods in the class below, your shells
=======

 * will inherit them.
 *
 * @package       app.Console.Command
 */

class NotificationShell extends AppShell {
    public $uses = array('User','ProgramNutrition','ProgramWorkout');

    public function show() {
        $user = $this->User->findByName($this->args[0]);
        $this->out(print_r($user, true));
    }
    
    /**
        * Function will get notifications and push to device
        * @param 
        * @response
     */
    public function getNotifications(){
        //get all users of DB
        $users = $this->User->find('all',array('fields'=>array('id','program_id','device_token'),'contain'=>false));
        //$this->out(print_r($users, true));die;
        foreach($users as $user){
            
            //set user details in variables
            $programId = $user['User']['program_id'];
            $userId = $user['User']['id'];
            $deviceToken = $user['User']['device_token'];

            //fetch program nutritions data
            $nutritionCondition = array('ProgramNutrition.day'=>date('l'));
            $nutritionData = $this->ProgramNutrition->fetchProgramNutrition($programId, $userId, $nutritionCondition);
            //fetch program workout data
            $workoutCondition = array('ProgramWorkout.day'=>date('l'));
            $workoutData = $this->ProgramWorkout->fetchProgramWorkout($programId, $userId, $workoutCondition);
            
            $nutritionFeed = $this->setNutritionFeedData($nutritionData);
            $workoutFeed = $this->setWorkoutFeedData($workoutData);

            $allFeeds = array_merge($nutritionFeed,$workoutFeed);
            //send notifications to device
            $this->sendNotifications($deviceToken,$allFeeds);
        }
        $this->out(print_r(array('Working'), true));
    }
    
    /**
        * Function used to send notifications to device
        * @param $deviceToken, $allFeeds
        * @response
     */
    private function sendNotifications($deviceToken,$allFeeds){
        App::import('Component', 'CakeApns.Apns');
        $this->Apns = new ApnsComponent(new ComponentCollection());
        
        $timeArr = array(
            'Breakfast'=>  strtotime(BREAKFAST_TIME),
            'Lunch'=>  strtotime(LUNCH_TIME),
            'Workout'=>  strtotime(WORKOUT_TIME),
            'Snack'=>  strtotime(SNACK_TIME),
            'Dinner'=>  strtotime(DINNER_TIME)
            );
        $notificationCount = 0;
        foreach($allFeeds as $feed){
            $timeDiff = (strtotime(date('H:i:s'))-$timeArr[$feed['feed_type']])/60;
            //if($timeDiff>15 && $timeDiff<30){ 
            if(true){ 
                $notificationCount++;
                //$this->Apns->add($deviceToken, 'You have missed your '.$feed['feed_type']);
                $this->Apns->add('BBC9CD35-7E9F-4303-97F1-90D367A80589', 'You have missed your '.$feed['feed_type']);
            }
        }
        if($notificationCount){
            $this->Apns->pushMany();
        }
    }
    
    /**
        * Function will arrange data for home feeds
        * @param $nutritionDatas
        * @response $returnData
     */
    private function setNutritionFeedData($nutritionDatas){
        $data = [];
        foreach($nutritionDatas as $nutritionData){
            $data[]['feed_type'] = $nutritionData['Nutrition']['NutritionType']['type'];
        }
        return $data;
    }
    
    
    /**
        * Function will prepare data from workout for home feeds
        * @param $workoutDatas
        * @response $returnData
     */
    private function setWorkoutFeedData($workoutDatas){
        
        $data = [];
        foreach($workoutDatas as $workoutData){
            $data[]['feed_type'] = 'Workout';
        }
        return $data;
    }

}
