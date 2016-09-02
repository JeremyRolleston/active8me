<?php
/**
 * Workouts Controller
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

App::uses('Controller', 'Controller');
session_start();
/**
 * Workouts Controller
 *
 * Add your workouts methods in the class below
 * 
 */
class WorkoutsController extends AppController {
    
    public $components = array('Session');
    
    /**
        * Workout Detail API
        *
        * To get workout related info
        * 
     */
    
    public function workoutDetail(){
        
        $this->autoRender = false;
        
        //initialize an empty array for response
        $success = true;
        $message = '';
        $respData = [];  
        
        if(!$this->request->is('get')){
            $success = false;
            $message = 'Invalid request method';
        }
        
        $workoutId = isset($this->request->query['workoutId'])?urldecode($this->request->query['workoutId']):'';        
        $programWorkoutId = isset($this->request->query['programWorkoutId'])?$this->request->query['programWorkoutId']:'';                
        
        if(empty($workoutId)){
            return;
        }
        
        
        $this->loadModel('WorkoutActivity');
        
        //get fitness level id and check authentication
        $fitnessLevelId = $this->Session->check('User.fitness_level')?$this->Session->read('User.fitness_level'):'';
        if(empty($fitnessLevelId)){
            $success = false;
            $message = 'Authentication Failed';
            //prepare response 
            $resp = $this->WorkoutActivity->prepareResponse($success, $message, $respData);        
            return $resp;
        }
        //echo $workoutId.','.$fitnessLevelId;die;
        try{
            $workoutActivities = $this->WorkoutActivity->getWorkoutActivities($workoutId,$fitnessLevelId);            
            if(empty($workoutActivities)){
                $this->loadModel('Workout');
                $workoutActivities = $this->Workout->findById($workoutId);
                unset($workoutActivities['WorkoutVideo']);
            }
            $workoutActivities['Workout']['program_workout_id'] = $programWorkoutId;
            $respData = $workoutActivities;
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            $success = false;
        }
        
        //prepare response 
        $resp = $this->WorkoutActivity->prepareResponse($success, $message, $respData);        
        return $resp;
    }
    
    /**
    * Function will mark the workout status as complete
    * @param (Get request)
    * @return $resp
    * 
    */
    public function markWorkoutAsComplete(){
        $this->autoRender = false;
        
        $success = true;
        $message = 'Workout has been marked as complete.';
        
        //get user id and check authentication
        $userId = $this->Session->check('User.id')?$this->Session->read('User.id'):'';
        if(empty($userId)){
            $success = false;
            $message = 'Authentication Failed';
        }
        
        if(!$this->request->is('put')){
            $success = false;
            $message = 'Invalid request method';
        }
        
        $data = $this->request->input('json_decode', true);        
        if(empty($data)){
            return;
        }
        $programWorkoutId = $data['programWorkoutId'];
        $completed = isset($data['completed'])?$data['completed']:1;
        
        $respData = [];
        $this->loadModel('UserWorkout');
        try{
            
            $userProgramWorkoutId = $this->chkUserPogramWorkout($userId, $programWorkoutId);
            
            $userWrokoutdata = [];
            $userWrokoutdata['UserWorkout']['user_id'] = $userId;
            $userWrokoutdata['UserWorkout']['program_workout_id'] = $programWorkoutId;
            $userWrokoutdata['UserWorkout']['completed_status'] = $completed;
            if(!empty($userProgramWorkoutId)){
                //update the data for given id
                $userWrokoutdata['UserWorkout']['id'] = $userProgramWorkoutId;                
            }
            $this->UserWorkout->save($userWrokoutdata);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            $success = false;
        }
        
        //prepare response 
        $resp = $this->UserWorkout->prepareResponse($success, $message, $respData);        
        return $resp;
    }
    
    /**
    * Function will check for user having same program workout
    * @param $userId, $programWorkoutId
    * @return $userProgramWorkoutId
    * 
    */
    private function chkUserPogramWorkout($userId, $programWorkoutId){
        $this->loadModel('UserWorkout');
        $userProgramWorkout = $this->UserWorkout->getUserWorkout($userId, $programWorkoutId);
        $userProgramWorkoutId = !empty($userProgramWorkout)?$userProgramWorkout['UserWorkout']['id']:'';
        return $userProgramWorkoutId;
    }
    
}
