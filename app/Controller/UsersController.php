<?php
/**
 * Users Controller
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
 * Users Controller
 *
 * Add your user related methods in the class below
 * 
 */
class UsersController extends AppController {    
    
    public $components = array('Session');
    
    /**
     * Function addImage
     * This API will save the image uploaded
     */
    public function addImage() {
        

        $this->autoRender = false;
        
        //initialize an empty array for response
        $respData = [];

        if(empty($this->request->params)){
            return;
        }
        
        $this->loadModel('User');

        
        $userId = $this->Session->check('User.id')?$this->Session->read('User.id'):'';
        if(empty($userId)){
            return $this->User->prepareResponse(false, 'Authentication Failed', array());                    
        }
        
        if ($this->request->is('post')) {
            
            try{
                $message = 'Image save successfully.';
                $success = true;
                $respData = [];           

                $this->User->create();
                $file = $this->request->params['form']['image'];
                $data = $this->request->data;

                //Check if image has been uploaded
                if (!empty($file['name'])) {
                    $imageType = $data['image_type'];

                    if(!in_array($imageType,array('before','after'))){
                        return $this->User->prepareResponse(false, 'Invalid image type', array());   
                    }

                    $ext = substr(strtolower(strrchr($file['name'], '.')), 1);
                    $arr_ext = array('jpg', 'jpeg', 'gif','png');

                    if (in_array($ext, $arr_ext)) {
                        $fileNewName = time().'.'.$ext;
                        move_uploaded_file($file['tmp_name'], WWW_ROOT . 'img/users/' . $fileNewName);
                        //echo SITE_IMAGE_URL;die;
                        $this->request->data['User'][$imageType.'_image'] = SITE_IMAGE_URL.'users/'.$fileNewName;
                        $this->request->data['User'][$imageType.'_image_date'] = date('Y-m-d',strtotime($data['image_date']));
                        $this->request->data['User']['id'] = $userId;
                        
                        $this->User->save($this->request->data);
                        
                        $respData[$imageType.'_image'] = SITE_IMAGE_URL.'users/'.$fileNewName;                        
                        $respData[$imageType.'_image_date'] = strtotime($data['image_date'])*1000;
                    }
                }
            }  catch (Exception $ex){
                $message = $ex->getMessage();
                $success = false;
            }

            $resp = $this->User->prepareResponse($success, $message, $respData);        
            return $resp;
        }
    }
    
    /**
        * Get Image Detail API
        * image detail $resp
        * 
     */
    
    public function getImageDetail(){
        $this->autoRender = false;
        
        $success = true;
        $message = '';
        $respData = [];     
        
        $this->loadModel('User');
        //get user id and check authentication
        $userId = $this->Session->check('User.id')?$this->Session->read('User.id'):'';
        if(empty($userId)){
            //prepare response 
            return $this->User->prepareResponse(false, 'Authentication Failed', $respData);
        }
        
        if(!$this->request->is('get')){
            return $this->User->prepareResponse(false, 'Invalid request method', $respData);
        }
        
        try{
            
            $userData = $this->User->findById($userId,array('User.before_image','User.before_image_date','User.after_image','User.after_image_date'));
            $respData = $userData;
            if(!empty($userData['User']['before_image_date'])){
                $respData['User']['before_image_date'] = strtotime($userData['User']['before_image_date'])*1000;
            }
            if(!empty($userData['User']['after_image_date'])){
                $respData['User']['after_image_date'] = strtotime($userData['User']['after_image_date'])*1000;
            }

        } catch (Exception $ex) {
            $message = $ex->getMessage();
            $success = false;
        }
        
        //prepare response 
        $resp = $this->User->prepareResponse($success, $message, $respData);        
        return $resp;
    }
}
