<?php
/**
 * Home Controller
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
session_start();
App::uses('Controller', 'Controller');

/**
 * Home Controller
 *
 * Add your home page methods in the class below
 * 
 */
class HomesController extends AppController {
    
    //var $uses = array('User');
    public $components = array('Session');
    
    /**
        * Register API
        *
        * To register user 
        * 
     */
    
    public function index(){
        $this->autoRender = false;       
        
        $this->loadModel('User');
        //prepare response 
        $resp = $this->User->prepareResponse('true', 'Welcome to Active8me', array());        
        return $resp;
    }
    
    /**
        * Register API
        *
        * To register user 
        * 
     */
    
    public function register(){
        $this->autoRender = false;
        
        //initialize an empty array for response
        $respData = [];
        $data = $this->request->input('json_decode', true);
        
        if(empty($data)){
            return;
        }
        
        $this->loadModel('User');
        try{
            
            $message = 'User added successfully.';
            $success = true;

            $userData['User'] = $data;
            
            //covert date to Y-m-d format
            if(isset($data['dob']) && !empty($data['dob'])){
                $userData['User']['dob'] = date('Y-m-d', strtotime($data['dob']));
            }
            
            //save user data
            $this->User->set($userData);
            if(!$this->User->save($userData)){
                $message = $this->User->validationErrors;
                $success = false;
            }else{//get saved user data              
                $respData = $this->User->chkUser($userData);                
                $this->saveUserSession($respData);
            }
            //debug($this->User->getDataSource()->getLog(false, false));die;
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            $success = false;
        }
        
        //prepare response 
        $resp = $this->User->prepareResponse($success, $message, $respData);        
        return $resp;
    }
    
    
    /**
        * Login API
        *
        * To login user 
        * 
     */
    
    public function login(){
        $this->autoRender = false;
        
        //initialize an empty array for response
        $respData = [];
        $data = $this->request->input('json_decode', true);
        
        if(empty($data)){
            return;
        }
        
        $this->loadModel('User');
        
        try{
            
            $message = 'Login successfully.';
            $success = true;

            $userData['User'] = $data;
            
            $userDetails = $this->User->chkUser($userData);
            if(empty($userDetails)){
                $message = 'Authentication Failed';
                $success = false;
            }else{
                $respData = $userDetails;
                $this->saveUserSession($respData);
            }            
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            $success = false;
        }
        
        //prepare response 
        $resp = $this->User->prepareResponse($success, $message, $respData);        
        return $resp;
    }
    
    
    /**
        * function will write the given information in session
        *
        * To login user 
        * 
     */
    private function saveUserSession($data){
        $this->Session->write('User',$data['User']);
    }
    
    /**
        * function will logout user and destroy session
        *
        * To logout user
        * 
     */
    public function userLogout(){
        $this->Session->destroy();
        exit('done');
    }
    
    /**
        * function will get user home page schedule
        *
        * 
     */
    public function getUserSchedule(){
        $this->autoRender = false;
        
        //load program nutrition model
        $this->loadModel('ProgramNutrition');
        
        $userId = $this->Session->read('User.id');        
        if(empty($userId)){
            $success = false;
            $message = 'Authentication Failed';    
            $resp = $this->ProgramNutrition->prepareResponse($success, $message, array());        
            return $resp;
        }
        
        $programId = $this->Session->read('User.program_id');
        
        //load program workout model
        $this->loadModel('ProgramWorkout');
        
        //get week related info
        $week = $this->getUserCurrentWeek();
        $totalPoints = $week*WEEKLY_POINTS;
        
        //fetch program nutritions data
        $nutritionCondition = array('ProgramNutrition.week'=>$week);
        $nutritionData = $this->ProgramNutrition->fetchProgramNutrition($programId, $userId,$nutritionCondition);
        //fetch program workout data
        $workoutCondition = array('ProgramWorkout.week'=>$week);
        $workoutData = $this->ProgramWorkout->fetchProgramWorkout($programId, $userId,$workoutCondition);
        
        //get points info
        $nutritionPoints = $this->ProgramNutrition->fetchNutritionPoints($userId);
        $workoutPoints = $this->ProgramWorkout->fetchWorkoutPoints($userId);
        $earnedPoints = $nutritionPoints + $workoutPoints;
        
        //prepare data for home page for sorting as well
        $homeFeeds = $this->prepareHomePageFeeds($nutritionData, $workoutData); 
        
        //add header info in response
        $homeFeeds['header_info'] = array('earned_points'=>$earnedPoints,'week'=>$week,'total_points'=>$totalPoints);        
        
        //prepare response 
        $resp = $this->ProgramWorkout->prepareResponse(true, '', $homeFeeds);        
        return $resp;
        
    }
    
    /**
        * Function will sort the home page feed data from given data
        * @param $nutritionData, $workoutData
        * @response $resultantFeed
     */
    private function prepareHomePageFeeds($nutritionData, $workoutData){
        
        $feed = [];
        
        if(!empty($nutritionData) && !empty($workoutData)){
            
            foreach($nutritionData as $nData){                
                $workoutFeedData = [];
                $feedData = $this->setNutritionFeedData($nData);
                foreach($workoutData as $wKey=>$wData){
                    if(
                        $nData['ProgramNutrition']['day']==$wData['ProgramWorkout']['day']
                            &&
                        $nData['ProgramNutrition']['week']==$wData['ProgramWorkout']['week']
                            &&
                        !empty($wData['Workout']['id'])    
                    ){
                        $workoutFeedData = $this->setWorkoutFeedData($wData); 
                        break;
                    }
                }
                if(!empty($workoutFeedData)){
                    $timeArr = array(
                    'Breakfast'=>  strtotime(BREAKFAST_TIME),
                    'Lunch'=>  strtotime(LUNCH_TIME),
                    'Workout'=>  strtotime(WORKOUT_TIME),
                    'Snack'=>  strtotime(SNACK_TIME),
                    'Dinner'=>  strtotime(DINNER_TIME)
                    );
                    if($timeArr[$nData['NutritionType']['type']]>$timeArr['Workout']){
                        $feed[]=$workoutFeedData;
                        unset($workoutData[$wKey]);
                    }
                }
                $feed[]=$feedData;
            }
        }
        
        $resultantFeed['feed_info'] = $feed;        
        return $resultantFeed;
    }
    
    
    /**
        * Function will arrange data for home feeds
        * @param $nutritionData
        * @response $data
     */
    private function setNutritionFeedData($nutritionData){
        
        $data = [];
        $data['title'] = $nutritionData['Nutrition']['title'];
        $data['thumb'] = $nutritionData['Nutrition']['thumb'];
        $data['nutrition_id'] = $nutritionData['Nutrition']['id'];
        $data['program_nutrition_id'] = $nutritionData['ProgramNutrition']['id'];
        $data['nutrition_tip'] = isset($nutritionData['Nutrition']['NutritionTips'][0]['description'])?$nutritionData['Nutrition']['NutritionTips'][0]['description']:'';
        
        $dayText = $this->getDayDetail($nutritionData['ProgramNutrition']['day']);
        
        $data['day'] = $dayText;
        $data['week'] = $nutritionData['ProgramNutrition']['week'];
        $data['nutrition_type'] = $nutritionData['NutritionType']['type'];
        $data['feed_type'] = 'nutrition';
        return $data;
    }
    
    
    /**
        * Function will prepare data from workout for home feeds
        * @param $workoutData
        * @response $data
     */
    private function setWorkoutFeedData($workoutData){
        
        $data = [];
        $data['title'] = $workoutData['Workout']['name'];
        $data['image'] = $workoutData['Workout']['image'];
        $data['thumb'] = $workoutData['Workout']['thumb'];
        $data['workout_id'] = urlencode($workoutData['Workout']['id']);
        $data['program_workout_id'] = $workoutData['ProgramWorkout']['id'];
        
        $dayText = $this->getDayDetail($workoutData['ProgramWorkout']['day']);
        
        $data['day'] = $dayText;
        $data['week'] = $workoutData['ProgramWorkout']['week'];
        $data['workout_type'] = 'Workout';
        $data['feed_type'] = 'workout';
        return $data;
    }
    
    /**
        * Function will get day text from given day
        * @param $day
        * @response $dayText
     */
    
    private function getDayDetail($day){
        
        $currentDay = date('l');
        
        if($day==$currentDay){
            $dayText = 'Today';
            return $dayText;
        }
        
        $weekArr = $this->getCurrentWeekArr();        
        $dayIndex = array_search($day, $weekArr);
        
        //get day text based on day index
        if($dayIndex==0){
            $dayText = 'Today';
        }else if($dayIndex==1){
            $dayText = 'Tomorrow';
        }else{
            $dayDate = date('Y-m-d', strtotime("+$dayIndex days"));
            $dayText = date('D d M',strtotime($dayDate));
        }
        return $dayText;
        
    }
    
    
    /**
        * Function will get week arr from given current day
        * @param 
        * @response $weekArr
     */
    private function getCurrentWeekArr(){
        
        $currentDay = date('l');
        
        switch($currentDay){
            case 'Monday':
                $weekArr = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
                break;
            case 'Tuesday':
                $weekArr = array('Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday','Monday');
                break;
            case 'Wednesday':
                $weekArr = array('Wednesday','Thursday','Friday','Saturday','Sunday','Monday','Tuesday');
                break;
            case 'Thursday':
                $weekArr = array('Thursday','Friday','Saturday','Sunday','Monday','Tuesday','Wednesday');
                break;
            case 'Friday':
                $weekArr = array('Friday','Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday');
                break;
            case 'Saturday':
                $weekArr = array('Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday');
                break;
            case 'Sunday':
                $weekArr = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
                break;
            default:
                $weekArr = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
                break;
        }
        return $weekArr;
    }
    
    /**
        * Function will get notifications and push to device
        * @param 
        * @response
     */
    public function getNotifications(){
        //if (!defined('CRON_DISPATCHER')) { $this->redirect('/'); exit(); }
        //return;
        $this->autoRender = false;
        
        $this->loadModel('User');
        //get all users of DB
        $users = $this->User->find('all',array('fields'=>array('id','program_id','device_token','created'),'contain'=>false));
        //$users = $this->User->find('all',array('conditions'=>array('email'=>'manish.kumar@sourcefuse.com'),'fields'=>array('id','program_id','device_token','created'),'contain'=>false));
        $deviceTokens = array();
        foreach($users as $user){
            
            $week = $this->getUserCurrentWeek($user['User']['created']);
            
            //set user details in variables
            $programId = $user['User']['program_id'];
            $userId = $user['User']['id'];
            $deviceToken = $user['User']['device_token'];
            if(empty($deviceToken)){
                continue;
            }
            
            //load program nutrition model
            $this->loadModel('ProgramNutrition');
            
            //load program workout model
            $this->loadModel('ProgramWorkout');

            //fetch program nutritions data
            $nutritionCondition = array('ProgramNutrition.day'=>date('l'),'ProgramNutrition.week'=>$week);
            $nutritionData = $this->ProgramNutrition->fetchProgramNutrition($programId, $userId, $nutritionCondition);
            //fetch program workout data
            $workoutCondition = array('ProgramWorkout.day'=>date('l'),'ProgramWorkout.week'=>$week);
            $workoutData = $this->ProgramWorkout->fetchProgramWorkout($programId, $userId, $workoutCondition);

            //arrange data in order
            $homeFeeds = $this->prepareHomePageFeeds($nutritionData, $workoutData);
            
            if(!empty($homeFeeds['feed_info']) && !in_array($deviceToken,$deviceTokens)){
                $deviceTokens[] = $deviceToken;
                //send notifications to device
                $this->sendNotifications($deviceToken,$homeFeeds);
            }
            
        }
        if(mail('manish.kumar@sourcefuse.com','Notification','Working')){
            echo "email sent";
        }else{
            echo "email not sent";
        }
        return;
    }
    
    /**
        * Function used to send notifications to device
        * @param $deviceToken, $allFeeds
        * @response
     */
    private function sendNotifications($deviceToken,$allFeeds, $setTimeDiff = false){
        $timeArr = array(
            'Breakfast'=>  strtotime(BREAKFAST_TIME),
            'Lunch'=>  strtotime(LUNCH_TIME),
            'Workout'=>  strtotime(WORKOUT_TIME),
            'Snack'=>  strtotime(SNACK_TIME),
            'Dinner'=>  strtotime(DINNER_TIME)
            );
        
        if(isset($allFeeds['feed_info'])){
            foreach($allFeeds['feed_info'] as $feed){
                $feedType = isset($feed['nutrition_type'])?$feed['nutrition_type']:$feed['workout_type'];            
                $timeDiff = (strtotime(date('H:i:s'))-$timeArr[$feedType])/60;
                //$timeDiff = (strtotime('20:15:00')-$timeArr[$feedType])/60;
                
                $notificationMsg = $this->getNotificationMessage($feedType);
                
                if($setTimeDiff || ($timeDiff>=0 && $timeDiff<15)){
                    $this->pushNotification($deviceToken,$notificationMsg);                
                }
            }
        }
        
    }
    
    private function getNotificationMessage($feedType){
        $msg = '';
        switch(strtolower($feedType)){
            case "breakfast":
                $msg = 'Don\'t forget to log your breakfast by clicking "Done" in your meal recipe. It is the most important meal of the day, Make sure you start your day with a healthy, nutritious breaskfast.';
                break;
            case "lunch":
                $msg = 'Don\'t forget to log your lunch by clicking "Done" in your meal recipe.';
                break;
            case "snack":
                $msg = 'Don\'t forget to log your snack by clicking "Done" in your meal recipe.';
                break;
            case "workout":
                $msg = 'Life gets busy so don\'t forget to schedule your workout for today. Active8me allows you to workout anywhere and anytime. Just do it!';
                break;
            case "dinner":
                $msg = 'Don\'t forget to log your dinner by clicking "Done" in your meal recipe.';
                break;
        }
        return $msg;
    }
    
    /**
        * Function used to send notifications to device
        * @param $deviceToken, $allFeeds
        * @response
     */
    private function pushNotification($deviceToken,$message){
        // Put your device token here (without spaces):
        //$deviceToken = 'e586aa8481f8357fe5fb6150b93c56a778fa0ca06224ce39862dc5338fb6a876';
//echo $deviceToken;
        // Put your private key's passphrase here:
        $passphrase = 'activateme';

        ////////////////////////////////////////////////////////////////////////////////

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', APP . 'Certificates' . DS . 'activateme.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        // Open a connection to the APNS server
        $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp)
                exit("Failed to connect: $err $errstr" . PHP_EOL);       

        // Create the payload body
        $body['aps'] = array(
                'alert' => array(
                'body' => $message,
                        'action-loc-key' => 'Active8me App',
            ),
            'badge' => 1,
                'sound' => 'oven.caf',
                );

        // Encode the payload as JSON
        $payload = json_encode($body);

        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));
        if (!$result)
                echo 'Message not delivered' . PHP_EOL;
        else
                echo 'Message successfully delivered' . PHP_EOL;

        // Close the connection to the server
        fclose($fp);
    }
    
    /**
        * Function used to get logged in user's current week
        * @param 
        * @response $week
     */
    public function getUserCurrentWeek($userCreatedDate = null){
                
        $createdDateTime = !empty($userCreatedDate)?$userCreatedDate:$this->Session->read('User.created');
        $dateTimeArr = explode(' ', $createdDateTime);
        $startDate = $dateTimeArr[0];
        $today = date('Y-m-d');
        $dateDiff = strtotime($today)-  strtotime($startDate);
        //increment week to get current running week
        $week =  floor($dateDiff / 604800) +1;
        return $week;
    }
    
    /**
        * Function will get notifications test and push to device
        * @param 
        * @response
     */
    public function getNotificationsTest(){
        
        //$this->autoRender = false;
        
        $this->loadModel('User');
        //get all users of DB
        //$users = $this->User->find('all',array('fields'=>array('id','program_id','device_token','created'),'contain'=>false));
        $users = $this->User->find('all',array('conditions'=>array('email'=>'manish@sourcefuse.com'),'fields'=>array('id','program_id','device_token','created'),'contain'=>false));
        $deviceTokens = array();
        
        foreach($users as $user){
            
            $week = $this->getUserCurrentWeek($user['User']['created']);
            
            //set user details in variables
            $programId = $user['User']['program_id'];
            $userId = $user['User']['id'];
            $deviceToken = $user['User']['device_token'];
            if(empty($deviceToken)){
                continue;
            }
            
            //load program nutrition model
            $this->loadModel('ProgramNutrition');
            
            //load program workout model
            $this->loadModel('ProgramWorkout');

            //fetch program nutritions data
            $nutritionCondition = array('ProgramNutrition.day'=>date('l'),'ProgramNutrition.week'=>$week);
            $nutritionData = $this->ProgramNutrition->fetchProgramNutrition($programId, $userId, $nutritionCondition);
            //fetch program workout data
            $workoutCondition = array('ProgramWorkout.day'=>date('l'),'ProgramWorkout.week'=>$week);
            $workoutData = $this->ProgramWorkout->fetchProgramWorkout($programId, $userId, $workoutCondition);

            //arrange data in order
            $homeFeeds = $this->prepareHomePageFeeds($nutritionData, $workoutData);
            
            if(!empty($homeFeeds['feed_info']) && !in_array($deviceToken,$deviceTokens)){
                $deviceTokens[] = $deviceToken;
                //send notifications to device
                $this->sendNotifications($deviceToken,$homeFeeds, true);
            }
            
        }
        return;
    }
}
