<?php
/**
 * Extras Controller
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
class ExtrasController extends AppController {
    
    var $name = 'Extras';
    //var $uses = array('User');
    public $components = array('Session');
    
    public function doImport() {
        //$this->loadModel('Program');
        $fileArr = array(
            //'Program.csv',
            //'Nutrition.csv',
            //'Activity.csv',
            'ProgramWorkout.csv',
            //'WorkoutActivity.csv',
            //'Nutrition.csv',
            //'NutritionTip.csv',
            //'ProgramNutrition.csv',
            //'WorkoutType.csv',
            //'WorkoutVideo.csv'
            );
        $messages = $this->importCSVs($fileArr);
        exit('Done');
    }
    
    function import($filename) {//echo $filename;die;
        // to avoid having to tweak the contents of 
        // $data you should use your db field name as the heading name 
        // eg: Post.id, Post.title, Post.description
        
        //extract model name from filename
        $fileNameArr = explode('.',$filename);
        $modelName = $fileNameArr[0];
        $this->loadModel($modelName);
        
        // set the filename to read CSV from
        $filename = APP . 'webroot' . DS . 'files' . DS . 'csvs'. DS. $filename;
        

        // open the file
        $handle = fopen($filename, "r");

        // read the 1st row as headings
        $header = fgetcsv($handle);

        // create a message container
        $return = array(
                'messages' => array(),
                'errors' => array(),
        );
        
        $error = false;
        $i = 0;

        // read each data row in the file
        while (($row = fgetcsv($handle)) !== FALSE) {
            $i++;
            $data = array();

            // for each header field 
            foreach ($header as $k=>$head) {
                    // get the data field from Model.field
                    if (strpos($head,'.')!==false) {
                            $h = explode('.',$head);
                            $data[$h[0]][$h[1]]=(isset($row[$k])) ? $row[$k] : '';
                    }
                    // get the data field from field
                    else {
                            $data[$modelName][$head]=(isset($row[$k])) ? $row[$k] : '';
                    }

            }

            //do extra formatting of data
            $this->setDataFormatting($data,$modelName);                

            // see if we have an id 			
            $id = isset($data[$modelName]['id']) ? $data[$modelName]['id'] : 0;

            // we have an id, so we update
            if ($id) {
                    // there is 2 options here, 

                    // option 1:
                    // load the current row, and merge it with the new data
                    //$this->recursive = -1;
                    //$post = $this->read(null,$id);
                    //$data['Post'] = array_merge($post['Post'],$data['Post']);

                    // option 2:
                    // set the model id
                    $this->$modelName->id = $id;
            }

            // or create a new record
            else {
                    $this->$modelName->create();
            }

            // see what we have
            // debug($data);

            // validate the row
            $this->$modelName->set($data);
            if (!$this->$modelName->validates()) {
                    $error = true;
                    $return['errors'][] = __(sprintf($modelName.' for Row %d failed to validate.',$i), true);
            }

            // save the row
            if (!$this->$modelName->save($data)) {
                    $error = true;
                    $return['errors'][] = __(sprintf($modelName.' for Row %d failed to save.',$i), true);
            }

            // success message!
            if (!$error) {
                    $return['messages'][] = __(sprintf($modelName.' for Row %d was saved.',$i), true);
            }
        }

        // close the file
        fclose($handle);

        // return the messages
        return $return;
 		
    }

    function importCSVs($fileNames){
        foreach($fileNames as $fileName){
            $this->import($fileName);
        }
    }
        
    private function setIdFormat($id){
        // strip out all whitespace
        $underscoreId = str_replace(' ', '_', trim($id));
        // convert the string to all lowercase
        $lowerUnderscoreId = strtolower($underscoreId);
        return $lowerUnderscoreId;
    }

    private function setDataFormatting(&$data, $modelName){
        if(isset($data[$modelName]['id'])){
            $data[$modelName]['id'] = $this->setIdFormat($data[$modelName]['id']);
        }            
        switch($modelName){                
            case 'Nutrition':
                if(!empty($data)){
                    $data[$modelName]['description'] = '<b>Ingredients:</b><br/>'.$data[$modelName]['ingredients_description'].' <br/><br/><b>Method:</b><br/>'.$data[$modelName]['method_description'];                        
                    $data[$modelName]['image'] = SITE_IMAGE_URL.'meals/'.$data[$modelName]['image'];
                    $data[$modelName]['thumb'] = SITE_IMAGE_URL.'meals/thumb/'.$data[$modelName]['thumb'];
                }
                break;
            case 'NutritionTip':
                if(!empty($data)){
                    $data[$modelName]['nutrition_id'] = $this->setIdFormat($data[$modelName]['nutrition_id']);
                }
                break;
            case 'ProgramNutrition':
                if(!empty($data)){
                    $data[$modelName]['nutrition_id'] = $this->setIdFormat($data[$modelName]['nutrition_id']);
                }
                break;
            case 'ProgramWorkout':
                if(!empty($data)){
                    $data[$modelName]['workout_id'] = $this->setIdFormat($data[$modelName]['workout_id']).'_wk_'.$data[$modelName]['week'];
                }
                break;
        }
        return;
    }

    public function readCsv(){
        $fileNameArr = explode('.','');

        // set the filename to read CSV from
        $filename = APP . 'webroot' . DS . 'files' . DS . 'csvs'. DS. 'plft.csv';
$arr=array();
$row = -1;

        // open the file
        $handle = fopen($filename, "r");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);            

            $row++;
            for ($c = 0; $c < $num; $c++) {
                $arr[$row][$c]= $data[$c];
            }
        }echo "<pre>";print_r($arr);die('done');
    }

    public function executeSQL(){
        $this->loadModel('User');


        // set the filename to read CSV from
        //$filename = APP . 'webroot' . DS . 'files' . DS . 'csvs'. DS. 'activateme.sql';           

        // open the file
        //$handle = fopen($filename, "r");
        //$sql = fread($handle, filesize($filename));
        //fclose($handle);
        //echo $sql;die;
        
        //$sql1 = "UPDATE workout_types SET name = 'AT HOME' WHERE id = 'at_home';";
        //$sql2 = "UPDATE workout_types SET name = 'GYM CLASSES' WHERE id = 'gym_classes';";
        //$sql1 = "UPDATE nutritions SET thumb = REPLACE( thumb , 'portrait', 'medium' ) ;";
        //$sql2 = "UPDATE nutritions SET thumb = 'http://active8me.sourcefuse.com/img/meals/thumb/Pho with chicken or beef_1_medium.png' WHERE id = 'pho_with_chicken_or_beef';";
        //$sql3 = "INSERT INTO workouts (id ,name, image, thumb , calorie_burned, workout_time) VALUES ('rest_day', 'Rest Day', 'http://active8me.sourcefuse.com/img/workouts/rest_day_portrait_750x1185.png', 'http://active8me.sourcefuse.com/img/workouts/thumb/rest_day_thumb_750x313.png', '', '');";
        
        $sql = "TRUNCATE activities;TRUNCATE workouts;TRUNCATE workout_activities;TRUNCATE workout_videos;
                ALTER TABLE workout_activities ADD activity_order INT( 11 ) NOT NULL AFTER activity_timer;";
        
        if ($sql) {
            //$this->User->query($sql1);
            //$this->User->query($sql2);
            
            
            if($this->User->query($sql)){
                echo "Done3";
            }else{
                echo "Not Done3";
            }
        }

        //$this->loadModel('ProgramNutrition');
        //$data = $this->ProgramNutrition->find('all');
        //echo "<pre>";print_r($data);

        exit("Executed");
    }
    
    public function executeSQL2(){
        $this->loadModel('User');
        
        $sql = "UPDATE workouts SET image = 'http://active8me.sourcefuse.com/img/workouts/Cardio_portrait.png', thumb = 'http://active8me.sourcefuse.com/img/workouts/thumb/Cardio_thumb.png' WHERE id like 'cardio%';
UPDATE workouts SET image = 'http://active8me.sourcefuse.com/img/workouts/toning_portrait.png', thumb = 'http://active8me.sourcefuse.com/img/workouts/thumb/toning_thumb.png' WHERE id like 'toning%';
UPDATE workouts SET image = 'http://active8me.sourcefuse.com/img/workouts/Yoga&Core_portrait.png', thumb = 'http://active8me.sourcefuse.com/img/workouts/thumb/Yoga&Core_thumb.png' WHERE id like 'yoga&core%';
INSERT INTO workouts (id, name, image, thumb, calorie_burned, workout_time) VALUES
('rest_day', 'Rest Day', 'http://active8me.sourcefuse.com/img/workouts/rest_day_portrait_750x1185.png', 'http://active8me.sourcefuse.com/img/workouts/thumb/rest_day_thumb_750x313.png', '', '');";
        
        if ($sql) {
            //$this->User->query($sql1);
            //$this->User->query($sql2);
            
            
            if($this->User->query($sql)){
                echo "Done3";
            }else{
                echo "Not Done3";
            }
        }
        
        exit("Executed");
        
    }
    
    public function executeSQL3(){
        $this->loadModel('User');
        
        $sql = "update program_workouts set workout_id = 'rest_day' where workout_id like '%rest_day%';";
        
        if ($sql) {            
            if($this->User->query($sql)){
                echo "Done3";
            }else{
                echo "Not Done3";
            }
        }
        
        exit("Executed");
        
    }
        
    public function contentActivityData(){
        
        //fetch circuit data
        $circuit = curl_init();
        //curl_setopt($ch, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries/atHomeVideos");
        curl_setopt($circuit, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=circuit&include=0&limit=1000");
        curl_setopt($circuit, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($circuit, CURLOPT_HEADER, FALSE);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //  "Authorization: Bearer 1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4"
        //));
        $circuits = curl_exec($circuit);
        curl_close($circuit);
        $circuitsArr1 = json_decode($circuits, true);
        //circuit data ends here
        
        //fetch circuit data
        $circuit2 = curl_init();
        //curl_setopt($ch, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries/atHomeVideos");
        curl_setopt($circuit2, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=circuit&include=0&limit=1000&skip=1000");
        curl_setopt($circuit2, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($circuit2, CURLOPT_HEADER, FALSE);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //  "Authorization: Bearer 1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4"
        //));
        $circuits2 = curl_exec($circuit2);
        curl_close($circuit2);
        $circuitsArr2 = json_decode($circuits2, true);
        //circuit data ends here
        
        $allCircuits = array_merge($circuitsArr1['items'],$circuitsArr2['items']);
        $circuitsArr['items'] = $allCircuits;
        
        //fetch circuit exercises
        $circuitExer = curl_init();
        curl_setopt($circuitExer, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=circuitExercise&include=0&limit=400");
        curl_setopt($circuitExer, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($circuitExer, CURLOPT_HEADER, FALSE);
        $circuitExercise = curl_exec($circuitExer);
        curl_close($circuitExer);        
        $circuitExercises = json_decode($circuitExercise, true);
        //circuit exercises ends here
        
        //fetch exercises data
        $exercise = curl_init();
        curl_setopt($exercise, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=exercises&include=0");
        curl_setopt($exercise, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($exercise, CURLOPT_HEADER, FALSE);
        $exercises = curl_exec($exercise);
        curl_close($exercise);        
        $exercisesArr = json_decode($exercises, true);
        
        //fetch exercises data
        $exercise2 = curl_init();
        curl_setopt($exercise2, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=exercises&include=0&skip=100");
        curl_setopt($exercise2, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($exercise2, CURLOPT_HEADER, FALSE);
        $exercises2 = curl_exec($exercise2);
        curl_close($exercise2);        
        $exercisesArr2 = json_decode($exercises2, true);
        
        //fetch exercises data
        $exercise3 = curl_init();
        curl_setopt($exercise3, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=exercises&include=0&skip=200");
        curl_setopt($exercise3, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($exercise3, CURLOPT_HEADER, FALSE);
        $exercises3 = curl_exec($exercise3);
        curl_close($exercise3);        
        $exercisesArr3 = json_decode($exercises3, true);
        //exercises array ends here
        
        $allExercises = array_merge($exercisesArr['items'],$exercisesArr2['items'],$exercisesArr3['items']);
        $exercisesArr['items'] = $allExercises;        
        
        foreach($circuitsArr['items'] as $key=>$ciruitItem){
            $newData[$key] = $ciruitItem['fields'];            
            $modifiedData = $this->getCircuitExercises($ciruitItem['fields']['circuitExercises'], $circuitExercises, $exercisesArr);
            $newData[$key]['circuitExercises'] = $modifiedData;
            $newData[$key]['id'] = $ciruitItem['sys']['id'];
        }
        return $newData;
    }
    
    public function contentWorkoutVideo(){
        
        //fetch video data
        $video = curl_init();
        //curl_setopt($ch, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries/atHomeVideos");
        curl_setopt($video, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=atHomeVideos&include=0");
        curl_setopt($video, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($video, CURLOPT_HEADER, FALSE);
        
        $videos = curl_exec($video);
        curl_close($video);
        $videosArr = json_decode($videos, true);
        //video data ends here
        
        foreach($videosArr['items'] as $key=>$videoItem){
            $newData[$key] = $videoItem['fields'];
            $newData[$key]['id'] = $videoItem['sys']['id'];
        }
        return $newData;
    }
    
    public function processActivityData(){
        ini_set('max_execution_time', 0);
        $datas = $this->contentActivityData();
        
        $this->loadModel('Activity');
        foreach($datas as $data ){
            $dbData = [];
            $dbData['id'] = $this->setIdFormat($data['circuitName']);
            $dbData['name'] = $this->setNameFormat($data['circuitName']);
            $dbData['description1'] = $data['circuitMessage'];
            $this->Activity->create();
            $this->Activity->save($dbData);
            
            $this->saveChildActivities($data['circuitExercises'], $dbData['id']);
        }
        
        //$log = $this->Activity->getDataSource()->getLog(false, false);
        //debug($log);
        exit('Done');
    }
    
    private function setNameFormat($name){
        $nameParts = explode('_',$name);
        return trim(end($nameParts));
    }
    
    private function saveChildActivities($activities, $parentid){
        //echo "<pre>";print_r($activities);die;
        $this->loadModel('Activity');
        foreach($activities as $data ){
            if(isset($data['exercise'])){
                $dbData = [];
                $dbData['id'] = $parentid.'_'.$this->setIdFormat($data['exercise']['exerciseName']);
                $dbData['name'] = $data['exercise']['exerciseName'];
                $dbData['description1'] = $data['exercise']['description'];
                
                if(isset($data['exercise']['imageLarge750x470'])){
                    $dbData['image'] = SITE_IMAGE_URL.'workouts/'.$data['exercise']['imageLarge750x470'];
                }
                if(isset($data['exercise']['imageSmall81x81'])){
                    $dbData['thumb'] = SITE_IMAGE_URL.'workouts/thumb/'.$data['exercise']['imageSmall81x81'];
                }

                $dbData['parent_id'] = $parentid;
                $this->Activity->create();

                $this->Activity->save($dbData);
            }
        }
    }
    
    private function getCircuitExercises($crEx, $circuitExercises, $exercisesArr ){
        
        $exercise = [];
        $exerciseCount = 0;
        foreach($crEx as $crExs){
            foreach($circuitExercises['items'] as $circuitExerciseItem){                
                if($crExs['sys']['id']==$circuitExerciseItem['sys']['id']){                    
                    $exercise[$exerciseCount] = $circuitExerciseItem['fields'];
                    //echo "<pre>";print_r($crExs);echo "<pre>";print_r($circuitExerciseItem);die;
                    if(isset($circuitExerciseItem['fields']['exercise'])){
                        $exerciseDetail = $this->getExerciseName($circuitExerciseItem['fields']['exercise'], $exercisesArr);
                        $exercise[$exerciseCount]['exercise'] = $exerciseDetail;                    
                    }
                    $exercise[$exerciseCount]['id'] = $circuitExerciseItem['sys']['id'];                    
                    $exerciseCount++;
                    break;
                }
            }
        }  
        return $exercise;
    }
    
    private function getExerciseName($ex, $exercisesArr){
        $exerciseId = $ex['sys']['id'];        
        
        $exerciseDetail = [];
        foreach($exercisesArr['items'] as $exItem){            
            if($exItem['sys']['id']==$exerciseId){                
                $exerciseDetail = $exItem['fields'];
                $exerciseDetail['id'] = $exerciseId;
                break;
            }
        }
        
        return $exerciseDetail;
    }
    
    public function getContentFullWorkout(){
        //fetch workout data
        $workoutCu = curl_init();
        //curl_setopt($ch, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries/atHomeVideos");
        curl_setopt($workoutCu, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=workout&include=0&limit=1000");
        curl_setopt($workoutCu, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($workoutCu, CURLOPT_HEADER, FALSE);
        $workoutJson = curl_exec($workoutCu);
        curl_close($workoutCu);
        $workouts = json_decode($workoutJson, true);
        
        //fetch workouts 100 + records data
//        $workoutCu2 = curl_init();
//        curl_setopt($workoutCu2, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=workout&include=0&skip=100");
//        curl_setopt($workoutCu2, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt($workoutCu2, CURLOPT_HEADER, FALSE);
//        $workoutJson2 = curl_exec($workoutCu2);
//        curl_close($workoutCu2);        
//        $workouts2 = json_decode($workoutJson2, true);
        //workout data ends here
        
        //fetch workouts 200 + records data
//        $workoutCu3 = curl_init();
//        curl_setopt($workoutCu3, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=workout&include=0&skip=200");
//        curl_setopt($workoutCu3, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt($workoutCu3, CURLOPT_HEADER, FALSE);
//        $workoutJson3 = curl_exec($workoutCu3);
//        curl_close($workoutCu3);        
//        $workouts3 = json_decode($workoutJson3, true);
        //workout data ends here
        
        //fetch workouts 300 + records data
//        $workoutCu4 = curl_init();
//        curl_setopt($workoutCu4, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=workout&include=0&skip=300");
//        curl_setopt($workoutCu4, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt($workoutCu4, CURLOPT_HEADER, FALSE);
//        $workoutJson4 = curl_exec($workoutCu4);
//        curl_close($workoutCu4);        
//        $workouts4 = json_decode($workoutJson4, true);
        //workout data ends here
        
        //fetch workouts 400 + records data
//        $workoutCu5 = curl_init();
//        curl_setopt($workoutCu5, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=workout&include=0&skip=400");
//        curl_setopt($workoutCu5, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt($workoutCu5, CURLOPT_HEADER, FALSE);
//        $workoutJson5 = curl_exec($workoutCu5);
//        curl_close($workoutCu5);        
//        $workouts5 = json_decode($workoutJson5, true);
        //workout data ends here
        
        //fetch workouts 500 + records data
//        $workoutCu6 = curl_init();
//        curl_setopt($workoutCu6, CURLOPT_URL, "https://cdn.contentful.com/spaces/pivujts0p337/entries?access_token=1e7b0cc034e9d883554d9f807f5953e713372ed0aa311e91db4372ad8e198ab4&content_type=workout&include=0&skip=500");
//        curl_setopt($workoutCu6, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt($workoutCu6, CURLOPT_HEADER, FALSE);
//        $workoutJson6 = curl_exec($workoutCu6);
//        curl_close($workoutCu6);        
//        $workouts6 = json_decode($workoutJson6, true);
        //workout data ends here
        
        //$allWorkouts = array_merge($workouts1['items'],$workouts2['items'],$workouts3['items'],$workouts4['items'],$workouts5['items'],$workouts6['items']);
        //$workouts['items'] = $allWorkouts; 
        //echo "<pre>";print_r($workouts);die;
        
        $activitesData = $this->contentActivityData();
        $videosData = $this->contentWorkoutVideo();
        
        $workoutData = [];
        foreach($workouts['items'] as $key=>$workout){
            $workoutData[$key] = $workout['fields'];
            if(isset($workout['fields']['circuits'])){
                $workoutActivities = $this->getWorkoutActivities($workout['fields']['circuits'], $activitesData);                
                $workoutData[$key]['circuits'] = $workoutActivities;
            }
            
            if(isset($workout['fields']['atHomeVideo'])){                
                $workoutVideos = $this->getWorkoutVideos($workout['fields']['atHomeVideo'], $videosData);
                $workoutData[$key]['WorkoutVideo'] = $workoutVideos;
            }
            
        }
        return $workoutData;
        
    }
    
    private function getWorkoutActivities($activitiesIds, $activitesData){
        $k = 0;
        $workoutActivity = [];
        foreach($activitiesIds as $id){
            foreach($activitesData as $key=>$activity){
                if($id['sys']['id']==$activity['id']){
                    $workoutActivity[$k]['name'] = $activity['circuitName'];
                    $workoutActivity[$k]['duration'] = $activity['duration'];
                    if(isset($activity['circuitExercises'])){
                        $childActivity = $this->preapreChildActivities($activity['circuitExercises']);
                        $workoutActivity[$k]['childActs'] = $childActivity;
                    }
                    $k++;
                    break;
                }
            }
        }
        return $workoutActivity;
        
    }
    
    private function getWorkoutVideos($videoIds, $videosData){
        $workoutVideo = [];
        
        $videoId = $videoIds['sys']['id'];
        foreach($videosData as $video){
            if($videoId==$video['id']){
                $workoutVideo['title'] = $video['videoName'];
                $workoutVideo['description'] = $video['videoDescription'];
                $workoutVideo['video_link'] = $video['videoLink'];
                break;
            }
        }
        return $workoutVideo;        
    }
    
    private function preapreChildActivities($activities){
        $childActivity = [];
        foreach($activities as $key=>$activity){//echo "<pre>";print_r($activity);die;
            $childActivity[$key]['name'] = isset($activity['exercise']['exerciseName'])?$activity['exercise']['exerciseName']:'';
            $childActivity[$key]['effort'] = isset($activity['effort'])?$activity['effort']:'';
            $childActivity[$key]['effortUnit'] = isset($activity['effortUnit'])?$activity['effortUnit']:'';            
        }
        return $childActivity;
    }
    
    public function processWorkoutData(){
        ini_set('max_execution_time', 0);
        $datas = $this->getContentFullWorkout();
        
        $this->loadModel('Workout');
        foreach($datas as $data ){
            $dbData = [];//echo "<pre>";print_r($data);die;
            $dbData['id'] = $this->setWorkoutIdFormat($data['workoutName'],$data['week']);
            $dbData['name'] = $this->setWorkoutNameFormat($data['workoutName']);
            if(isset($data['calories'])){
                $dbData['calorie_burned'] = $data['calories'];
            }
            if(isset($data['workoutDuration'])){
                $dbData['workout_time'] = $data['workoutDuration'];
            }
            
            //if(!$this->workoutExist($dbData['id'])){
                $this->Workout->create();
                $this->Workout->save($dbData);
            //}
            
            $activityData = [];
            $activityData['workout_id'] = $dbData['id'];
            $activityData['workout_type_id'] = $this->setIdFormat($data['location']);
            $activityData['fitness_level_id'] = $this->setIdFormat($data['level']);
            $this->saveWorkoutActivities($data, $activityData);
            
            //save workout video data
            if(!$this->workoutVideoExist($dbData['id'])){
                $this->saveWorkoutVideos($data, $dbData['id']);
            }
        }
        
        //$sql4 = "UPDATE workouts SET image = 'http://active8me.sourcefuse.com/img/workouts/toning.jpg', thumb = 'http://active8me.sourcefuse.com/img/workouts/thumb/toning_thumb.png' WHERE id = 'toning_1';";
        //$this->Workout->query($sql4);
        exit('Done');
    }
    
    private function workoutVideoExist($workoutId){
        
        $this->loadModel('WorkoutVideo');
        //$workoutData = $this->Workout->find('first',array('conditions'=>array('Workout.id'=>$id)));echo "<pre>";print_r($workoutData);die;
        $workoutVideoData = $this->WorkoutVideo->findByWorkoutId($workoutId);
        return !empty($workoutVideoData)?true:false;
    }
    
    private function setWorkoutNameFormat($name){
        $nameParts = explode('_',$name);
        return trim($nameParts[0]);
    }
    private function setWorkoutIdFormat($id, $week){
        $spaceId = $this->setWorkoutNameFormat($id);
        $newId = $this->setIdFormat($spaceId).'_wk_'.$week;
        return $newId;
    }
    
    private function saveWorkoutActivities($datas, $activityData){
        if(isset($datas['circuits'])){
            $this->loadModel('WorkoutActivity');
            foreach($datas['circuits'] as $data ){
                $dbData = [];
                $dbData = $activityData;
                $dbData['activity_id'] = $this->setIdFormat($data['name']);
                $dbData['activity_time'] = $data['duration'];
                $time = $data['duration'][0];
                $timer = intval($time)*60;            
                $dbData['activity_timer'] = $timer;
                $this->WorkoutActivity->create();
                $this->WorkoutActivity->save($dbData);            

                $activityOrder = 0;
                if(isset($data['childActs'])){                
                    foreach($data['childActs'] as $childData){
                        $activityOrder++;
                        $chDbData = [];
                        $chDbData = $activityData;
                        $chDbData['activity_id'] = $dbData['activity_id'].'_'.$this->setIdFormat($childData['name']);
                        $chDbData['activity_time'] = $childData['effort'].' '.$childData['effortUnit'];
                        $chDbData['activity_order'] = $activityOrder;

                        if($childData['effortUnit']=='Seconds'){
                            $chDbData['activity_timer'] = intval($childData['effort'])*1;
                        }else if($childData['effortUnit']=='Minutes'){
                            $chDbData['activity_timer'] = intval($childData['effort'])*60;
                        }


                        $this->WorkoutActivity->create();
                        $this->WorkoutActivity->save($chDbData);
                    }
                }
            }
        }
        return;
    }
    
    private function saveWorkoutVideos($datas, $workoutId){
        if(isset($datas['WorkoutVideo'])){
            $this->loadModel('WorkoutVideo');
            
            $dbData = $datas['WorkoutVideo'];
            $dbData['workout_id'] = $workoutId;
            $this->WorkoutVideo->create();
            $this->WorkoutVideo->save($dbData);
        }
        return;
    }
    
    public function fetchModelData($modelName){
        $this->loadModel($modelName);
        
        $data = $this->$modelName->find('all');
        echo "<pre>";print_r($data);
        die('Fetched');
    }
}
