<?php


App::uses('Model', 'Model');

/**
 * User model.
 *
 * Add your User related methods in the class below
 * 
 */
class User extends AppModel {
    
    public $name = 'User';
    
    public $validate = array(
        'name' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
                'required' => true,
                'message' => 'Name  is required',
                'on'=>'create'
            )
        ),
        'email'=>array(
            'rule1' => array(
               'rule' => array('email'),
               'required' => true,
               'message' => 'Please Enter a valid Email Address',
                'last'=>false,
                'on'=>'create'
            ),
            'rule2' => array(
               'rule' => 'isUnique',
               'message' => 'This email is already registered',
               'last'=>false,
                'on'=>'create'
            )
        ),
        'dob' => array(
            'rule' => 'date',
            'required' => true,
            'message' => 'Enter a valid date',
            'allowEmpty' => false,
            'last'=>false,
            'on'=>'create'
        ),
        'program_id' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
                'required' => true,
                'message' => 'Program is required',
                'on'=>'create'
            )
        ),
        'fitness_level' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
                'required' => true,
                'message' => 'Fitness level is required',
                'on'=>'create'
            )
        ),
        'calorie_level' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
                'required' => true,
                'message' => 'Calorie Level is required',
                'on'=>'create'
            )
        ),
        'gender' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
                'required' => true,
                'message' => 'Gender is required field',
                'on'=>'create'
            )
        ),
        'password' => array(
            'rule' => array('minLength', '8'),
            'message' => 'Minimum 8 characters long',
            'on'=>'create'
        )
    );
    
    var $belongsTo = array(
        'Program'=>array(
            'className'=>'Program',
            'foreignKey'=>'program_id'
        ),
        'FitnessLevel'=>array(
            'className'=>'FitnessLevel',
            'foreignKey'=>'fitness_level'
        ),
        'Country'=>array(
            'className'=>'Country',
            'foreignKey'=>'country_id'
        )
    );
    
    /*
     * Function will check if user with given credentials exists in database or not
     * @param data (contains email and password)
     * @return userDetails
     */
    public function chkUser($data){
        
        $email = $data['User']['email'];
        $password = $data['User']['password'];
        
        $userData = $this->find('first',array(
            'conditions'=>array(
                'email'=>$email,
                'password'=>md5($password)
            ),
            'fields'=>array(
                'User.*',
                'Country.name',
                'Program.name',
                'FitnessLevel.name'
            )
        ));
        
        if(!empty($userData)){
            unset($userData['User']['password']);
        }
        
        return $userData;
    }
    
    
    /*
     * Function callback will be called just before the saving of data
     * it will convert the password to md5 format
     * @return true
     */
    public function beforeSave($options = array()) {
        if(isset($this->data['User']['password'])){
            $this->data['User']['password'] = md5($this->data['User']['password']);
        }
        
        if(isset($this->data['User']['program_id']) && is_numeric($this->data['User']['program_id'])){
            $this->data['User']['program_id']=($this->data['User']['program_id']==1)?'lose_weight':'lean_fit_toned';
        }
        
        if(isset($this->data['User']['fitness_level']) && is_numeric($this->data['User']['fitness_level'])){
            switch($this->data['User']['fitness_level']){
                case 1:
                    $this->data['User']['fitness_level'] = 'beginner';
                    break;
                case 2:
                    $this->data['User']['fitness_level'] = 'intermediate';
                    break;
                case 3:
                    $this->data['User']['fitness_level'] = 'advanced';
                    break;
                default:
                    $this->data['User']['fitness_level'] = 'beginner';
                    break;
            }
        }
        return true;
    }

}
