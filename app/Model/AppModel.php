<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
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
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
    
    
    public function prepareResponse($status, $message, $outData){
        
        $data['status']['success'] = $status;
        
        if(!empty($message)){
            $data['status']['message'] = $message;
        }        
        if(!empty($outData)){
            $data['data'] = $outData;
        }
        return json_encode($data);
    }
    
    /**
        * Function will get week arr from given current day
        * @param 
        * @response $weekArr
     */
    public function getCurrentWeekArr(){
        
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
}
