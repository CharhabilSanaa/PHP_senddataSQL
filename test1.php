<?php

//parameters

$dbServerName = "192.168.0.171:4045";
$dbUsername = "user_wallet";
$dbPassword = "wallet!22@";
$dbName = "apep_wallet_mobile";



// create connection
$conn = new mysqli($dbServerName, $dbUsername, $dbPassword, $dbName);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";


//browse all csv file in the directory
    $files = glob("data/*.csv");           

    foreach($files as $file) {

        //the name of the file will be the same as the table
        $table = $file;
        //$table = preg_replace('/[0-9\@\.\;\" "]+/', '_', $table);
        //$table = substr(preg_replace ('/[^0-9a-z]/', '_', $table), 0, 20);
        $table = substr($table, 5);
        $table = substr($table, 0, -13);

        // get structure from csv 
        ini_set('auto_detect_line_endings',TRUE);
        $handle = fopen($file,'r');

        //check if there is an error in csv file
        if ( ($data = fgetcsv($handle, 4096, ",") ) === FALSE ) {
            echo "Cannot read from csv $file";die();
        }
        
        
        //a new variable for fields of column
        $fields = array();  
        $field_count = 0;
        for($i=0;$i<count($data); $i++) {

            $f = strtolower(trim($data[$i]));

            if ($f) {
    
                // normalize the field name, strip to 20 chars if too long
    
                //$f = substr(preg_replace ('/[^0-9a-z]/', '_', $f), 0, 20);          //remove special caracters
                $f = preg_replace('/[0-9\@\.\;\" "]+/', ' ', $f);
                //$f= trim($f, '/!.');
    
                $field_count++;
    
                $fields[] = $f.' VARCHAR(50)';

            }
        }


        //create table with exception :
        $sql = "CREATE TABLE $table (" . implode(', ', $fields) . ')';
        
        //check the query
        if ($conn->query($sql) === TRUE) {
            echo "Table created successfully";
          } else {
            echo "Error creating table: " . $conn->error;
          }



          //read the data and run the insert query
        while ( ($data = fgetcsv($handle, 4096, ",") ) !== FALSE ) {

            $fields = array();

            for($i=0;$i<$field_count; $i++) {
                $fields[] = '\''.addslashes($data[$i]).'\'';

            }

            $sql = "Insert into $table values(" . implode(', ', $fields) . ')';

            if ($conn->query($sql) === TRUE) {
                echo "Data upload successfully";
            } else {
                echo "Error uploading data: " . $conn->error;
            }
            
            
        }
        fclose($handle);
        ini_set('auto_detect_line_endings',FALSE);

        

    }

?>