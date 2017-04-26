/*this involves the usage of sms gateway that will be opened if the flag is updated continously for four times.i.e if the moisture level is below the threshold, then the flag will be updated.
The sms will alert the registered Users and also provide recommendations.*/



<?php
 	require_once("Rest.inc.php");
	
	class API extends REST {
	
		public $data = "";
		
		const DB_SERVER = "127.0.0.1";
		const DB_USER = "root";
		const DB_PASSWORD = "";
		const DB = "xxxxxxx";//your database name

		private $db = NULL;
		private $mysqli = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}
		
		/*
		 *  Connect to Database
		*/
		private function dbConnect(){
			$this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
		}
		
		/*
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('file not found',404); // If the method not exist with in this class "Page not found".
		}
    	private function sensor_insert()
		{     
				if($this->get_request_method() != "POST")
				{
								$this->response('',406);
				}
				
				date_default_timezone_set('Asia/Kolkata');
				//$sensor_time = date('H:i:s');
				//$sensor_date = date('Y-m-d');
				$sen = date('Y-m-d H:i:s');
				$id=$this->_request['id'];// id specifies from which node the data is collected.
                $temp=$this->_request['temp'];
				$hum=$this->_request['hum'];
				$moisture=$this->_request['moisture'];
				switch ($id) {
					case '1':
						if($moisture>=0 && $moisture<=526)
						
							$flag=1;
							
						else
							$flag=0;

						break;
					
					case '2':
						if($moisture>=0 && $moisture<=710)
						
						$flag=1;
						
					else
						$flag=0;
						break;
						default:
						echo "no such node found";

				}
			
				$query="INSERT into sensor_data(id,esp_time,temperature,humidity,moisture,flag)VALUES('$id','$sen','$temp','$hum','$moisture','$flag')";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				//$msg = array('status' => "Success", "msg" => "User updated Successfully.");
				//$this->response($this->json($msg),200);
				$query="SELECT flag,auto_id,moisture,temperature FROM sensor_data WHERE id='$id'order by auto_id DESC LIMIT 4";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

				if($r->num_rows>0) {
					//$this->response($this->json($result), 200);
					$result = array();
					$resFlag=array();
					while($row = $r->fetch_assoc()) //
					{
					    $result[]=$row['auto_id']; // the row 'auto_id' is stored in the array result
						$resFlag[]=$row['flag'];   // the row 'flag' is stored in the array resFlag
					}
				}
					
					$counts=array_count_values($resFlag);
					$c=$counts['1'];
					//echo $c;
					if($c==4)
					 {
					 // the threshold values are different for different moisture sensors.
					 	switch ($id) {
					case '1':
						if($moisture>0 && $moisture<=250)
						{
							
							$msg="ALERT! YOUR PLANT IS TOO DRY!"."                
							      "."MOISTURE PERCENTAGE: 10%-24%"." 
							     "."ADD 500 ml OF WATER TO THE SOIL AS SOON AS POSSIBLE AT TRAY : " .$id."       
							     ". "TEMPERATURE STATUS:" .$temp. "Celcius";
						}
						else if($moisture>250&& $moisture<=350)
						{
							
							$msg="ALERT! YOUR PLANT IS QUITE DRY!"."  
							      "."MOISTURE PERCENTAGE: 24%-50%"." 
							      "."ADD 250 ml(approx) OF WATER TO THE SOIL AS SOON AS POSSIBLE AT TRAY : " .$id."       
							        ". "TEMPERATURE STATUS:" .$temp . "Celcius";

						}
						else if($moisture>350&& $moisture<=526)
						{
							
							$msg="YOUR PLANT REQUIRES LITTLE MORE MOISTURE TO SURVIVE THE HEAT!"."
							      "."MOISTURE PERCENTAGE: 51%-80%"."
							      "."ADD 100 ml(approx) OF WATER TO THE SOIL TO TRAY : " .$id."         
							       ". "TEMPERATURE STATUS:" .$temp ."Celcius";

						}
						
							
                       break;
					
					case '2':
						if($moisture>0 && $moisture<=130)
						{
						
						$msg="ALERT! YOUR PLANT IS TOO DRY!"."                "."MOISTURE PERCENTAGE: 10%-24%"."          "."ADD 500 ml OF WATER TO THE SOIL AS SOON AS POSSIBLE AT TRAY :" .$id."          ". "TEMPERATURE STATUS:" .$temp.  "Celcius";
						}
						else if($moisture>130&& $moisture<=500)
						{
							
							$msg="ALERT! YOUR PLANT IS QUITE DRY!"."         "."MOISTURE PERCENTAGE: 24%-50% "."        "."ADD 250 ml(approx) OF WATER TO THE SOIL AS SOON AS POSSIBLE AT TRAY : " .$id."          ". "TEMPERATURE STATUS:" .$temp ."Celcius";

						}
						else if($moisture>500&& $moisture<=710)
						{
							
						$msg="YOUR PLANT REQUIRES LITTLE MORE MOISTURE TO SURVIVE THE HEAT!"."        "."MOISTURE PERCENTAGE: 51%-80%"."               "."ADD 100 ml(approx) OF WATER TO THE SOIL TO TRAY : " .$id."             ". "TEMPERATURE STATUS:" .$temp."Celcius";

						}
						
						break;
						default:
						echo "no such node found";

				}
				 	 
				 	//To send Alerts using SMS Gateway
				          require 'sendsms.php';  
                         $sendsms = new sendsms();
                           $url = 'http://pay4sms.in';
                          $token = '';
 
                          // for sent sms
                         $credit = '2';
                          $sender = '*********';
                         $sms = $msg;
                        $number = '**************';//include the numbers of registered people.
                         $message_id = $sendsms->sendmessage($url,$token,$credit,$sender,$sms,$number);
                         print_r($message_id);
                         // for check DLR
                        $message_id = 'XXXXXXX';
                        $dlr_status = $sendsms->checkdlr($url,$token,$message_id);
 
              //           // for check Credits
                         $credit = 'XXXX';
              //          $available_credit = $sendsms->availablecredit($url,$token,$credit);
              //            /* Sending the SMS for Username and password ends*/
                        // echo $msg;
                        echo "sms sent";
                         

					//$this->response($this->json($result), 200); // send user details
                     
				
				
			}
