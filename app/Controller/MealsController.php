<?php
/**
 * Meals Controller
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
 * Meals (Nutrition) Controller
 *
 * Add your Meal methods in the class below
 * 
 */
class MealsController extends AppController {
    
    public $components = array('Session');
    
    /**
        * Meal Detail API
        *
        * To get meal related info
        * 
     */
    
    public function mealDetail(){
        $this->autoRender = false;
        
        $success = true;
        $message = '';
        $respData = [];     
        
        $this->loadModel('Nutrition');
        //get user id and check authentication
        $programId = $this->Session->check('User.program_id')?$this->Session->read('User.program_id'):'';
        if(empty($programId)){
            $success = false;
            $message = 'Authentication Failed';
            //prepare response 
            $resp = $this->Nutrition->prepareResponse($success, $message, $respData);        
            return $resp;
        }
        
        if(!$this->request->is('get')){
            $success = false;
            $message = 'Invalid request method';
        }
        
        $mealId = isset($this->request->query['objectId'])?$this->request->query['objectId']:'';        
        $programMealId = isset($this->request->query['programMealId'])?$this->request->query['programMealId']:'';        
        
        if(empty($mealId)){
            return;
        }
        
           
        $this->loadModel('ProgramNutrition');
        try{
            
            $mealData = $this->Nutrition->findById($mealId);
            $respData = $mealData;
            $respData['Nutrition']['program_nutrition_id'] = $programMealId;
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            $success = false;
        }
        
        //prepare response 
        $resp = $this->Nutrition->prepareResponse($success, $message, $respData);        
        return $resp;
    }
    
    /**
    * Function will mark the meal status as complete
    * @param (Get request)
    * @return $resp
    * 
    */
    public function markMealAsComplete(){
        $this->autoRender = false;
        
        $success = true;
        $message = 'Meal has been marked as complete.';
        
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
        $programMealId = $data['programNutritionId'];
        $cookedStatus = isset($data['cooked'])?$data['cooked']:1;
        
        $respData = [];
        $this->loadModel('UserNutrition');
        try{
            
            $programNutritionId = $this->chkUserPogramNutrition($userId, $programMealId);          
            
            $userNutritionData = [];
            $userNutritionData['UserNutrition']['user_id'] = $userId;
            $userNutritionData['UserNutrition']['program_nutrition_id'] = $programMealId;
            $userNutritionData['UserNutrition']['cooked'] = $cookedStatus;
            if(!empty($programNutritionId)){
                $userNutritionData['UserNutrition']['id'] = $programNutritionId;
            }
            $this->UserNutrition->save($userNutritionData);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            $success = false;
        }
        
        //prepare response 
        $resp = $this->UserNutrition->prepareResponse($success, $message, $respData);        
        return $resp;
    }
    
    /**
    * Function will check for user having same program nutrition
    * @param $userId, $programMealId
    * @return $programNutritionId
    * 
    */
    private function chkUserPogramNutrition($userId, $programMealId){
        $this->loadModel('UserNutrition');
        $userProgramNutrition = $this->UserNutrition->getUserNutrition($userId, $programMealId);
        $programNutritionId = !empty($userProgramNutrition)?$userProgramNutrition['UserNutrition']['id']:'';
        return $programNutritionId;
    }
    
}
