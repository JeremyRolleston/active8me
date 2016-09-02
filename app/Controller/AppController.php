<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
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
//header("Access-Control-Allow-Origin: *.active8me.com");
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
    
    public $components = array('Session','Cookie');
    
    public function beforeFilter(){
        $this->response->header('Access-Control-Allow-Origin','http://actui.sourcefuse.com');
        $this->response->header('Access-Control-Allow-Credentials','true');
        $this->response->header('Access-Control-Allow-Methods','PUT,POST,GET,DELETE,OPTIONS');
        $this->response->header('Access-Control-Allow-Headers','Origin, X-Requested-With, Content-Type, Accept');        
        $action = $this->request->params['action'];
        session_set_cookie_params(0, '/', '.sourcefuse.com');
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {           
           $method = $this->request->header('Access-Control-Request-Method');
           $headers = $this->request->header('Access-Control-Request-Headers');
           $this->response->header('Access-Control-Allow-Headers', $headers);
           $this->response->header('Access-Control-Allow-Methods', empty($method) ? 'GET, POST, PUT, DELETE' : $method);           
           $this->response->header('Access-Control-Max-Age', '86400');
           $this->response->send();
           die;
        }
        
        //if action is login or registartion, skip this
        if(!($action=='register' || $action=='login')){
            if(!$this->Session->check('User')){
                $message = 'Authentication Failed';
                $success = false;
                
                //prepare response 
                $this->loadModel('User');
                $resp = $this->User->prepareResponse($success, $message, array());
                return $resp;
            }
        }
        
    }
    
    
    
    public function encodeId($id){
        return convert_uuencode(base64_encode($id));
    }
    
    public function decodeId($id){
        return convert_uuencode(base64_encode($id));
    }
}
