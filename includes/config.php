<?php 

date_default_timezone_set("Europe/Berlin");
session_start();

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception;

// require 'vendor/autoload.php';

// function send_email($from, $to, $subject, $body) {
// 	// Instantiation and passing `true` enables exceptions
// 	$mail = new PHPMailer(true);

// 	try {
// 	  //Server settings
// 	  $mail->SMTPDebug = 0; // SMTP::DEBUG_SERVER;                      // Enable verbose debug output
// 	  $mail->isSMTP();                                            // Send using SMTP
// 	  $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
// 	  $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
// 	  $mail->Username   = 'geheimclubbooking@gmail.com';                     // SMTP username
// 	  $mail->Password   = "t9=dy/z'xGndIY|^!YlH";                               // SMTP password
// 	  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
// 	  $mail->Port       = 587;                                    // TCP port to connect to

// 	  //Recipients
// 	  $mail->setFrom($from);
// 	  // $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
// 	  $mail->addAddress($to);               // Name is optional
// 	  // $mail->addReplyTo('info@example.com', 'Information');
// 	  // $mail->addCC('cc@example.com');
// 	  // $mail->addBCC('bcc@example.com');

// 	  // Attachments
// 	  // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
// 	  // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

// 	  // Content
// 	  $mail->isHTML(true);                                  // Set email format to HTML
// 	  $mail->Subject = $subject;
// 	  $mail->Body    = $body;
// 	  // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

// 	  $mail->send();
// 	  return true;
// 	} catch (Exception $e) {
// 	  die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
// 	}
// }



if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
  $link = "https://$_SERVER[HTTP_HOST]/"; 
else
  $link = "http://$_SERVER[HTTP_HOST]/"; 

define('URL', $link);


/* DATABASE SETTINGS */
define('SERVERNAME', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'evisionsmd');
//define('DB_PORT', 3306);
/* ../ DATABASE SETTINGS */

if (isset($_GET['logout'])) {
	session_unset();
	session_destroy();

	header("location: " . URL);
	exit();
}

function clean_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function vd($variable, $exit = true) {
	echo '<pre>';
	var_dump($variable);
	echo '</pre>';
	if ($exit) {
		exit;
	}
}

function getTextDay($day_number) {
  switch($day_number) {
    case 0:
      return 'Sunday';
      break;
    
    case 1:
      return 'Monday';
      break;

    case 2:
      return 'Tuesday';
      break;

    case 3:
      return 'Wednesday';
      break;

    case 4:
      return 'Thursday';
      break;

    case 5:
      return 'Friday';
      break;

    case 6:
      return 'Saturday';
      break;
  }
}

function proper_date($date) {
	$date = date_create($date);
	return date_format($date,"d-m-Y");
}

function dotted_date($date) {
	$date = date_create($date);
	return date_format($date,"d.m.Y");
}

function display_currency($amount) {
	return '€ ' . number_format($amount, 2);
}

function dotted_string($number) {
  $string = (string) $number;
  $str_ln = strlen($string);
  if ($str_ln > 3) {
    $rev_s = strrev($string);
    $new_s = '';
    for ($i = 0; $i < $str_ln; $i++) {
      if ($i % 3 == 0) {
        if ($i != $str_ln) {
          $new_s .= (substr($rev_s, $i, 3) . '.');
        }
      }
    }
    $new_s = rtrim($new_s, '.');
    return strrev($new_s);
  } else {
    return $string;
  }
}

function eu_currency($string) {
	setlocale(LC_MONETARY, 'it_IT');
	$temp = money_format('%.2n', $string);
	$temp = str_replace("EUR", "€", $temp);
	return  $temp;
}

class DB {
	public $conn;
	
	function __construct() {
		$this->conn = mysqli_connect(SERVERNAME, DB_USER, DB_PASSWORD, DB_NAME)
			or die("<h1>Database connection failed</h1>");
	}

	function query($conn, $sql) {
		return $results = mysqli_query($conn, $sql);
	}

	function single_row($sql) {
		if (mysqli_query($this->conn, $sql)) {
		  $result = mysqli_query($this->conn, $sql);
		  if (mysqli_num_rows($result) > 0) {
			  return mysqli_fetch_assoc($result);
			} else {
			  return [];
			}
		} else {
		  echo "Error: " . $sql . "<br>" . mysqli_error($this->conn);
		}
	}

	function multiple_row($sql) {
		$array = Array();
		$results = mysqli_query($this->conn, $sql);
		while($row = mysqli_fetch_assoc($results)) {
			array_push($array, $row);
		}
		return $array;
	}
	// table , column name, value
	function insert($table, $array) {
		// $sql
		$q1 = "insert into $table ";
		$i =0;
		$col = '';
		$val= "'";
		foreach($array as $k=>$v){
			$col .= $k;
			$val .= $v;

			if($i< count($array)-1){
				$col .=', ';
				$val .= "', ";
			}
			$val .= "'";
			$i++;
		}

		$sql  = $q1."(".$col.") values (".$val.")";
		if (mysqli_query($this->conn, $sql)) {
			return true;
		} else {
			echo "Error: " . $sql . "<br>" . $this->conn->error;
		}
	}

	function update ($table, $array, $conditions) { // give value as ["id" => 3, "name" => "qaisar"] array format
		$sql = "UPDATE $table SET";
		$array_length = count($array);
		if (count($array) > 0) {
      foreach ($array as $key => $value) {
        $value = "'$value'";
        $updates[] = "$key = $value";
      }
    }
    $implode_updates_Array = implode(', ', $updates);
    if (count($conditions) > 0) {
    	foreach ($conditions as $key => $value) {
    		$value = "'$value'";
    		$conditions_array[] = "$key = $value";
    	}
    }
    $implode_conditions_Array = implode(' AND ', $conditions_array);
    $sql = "UPDATE $table SET $implode_updates_Array WHERE $implode_conditions_Array";
    if (mysqli_query($this->conn, $sql)) {
		  return true;
		} else {
		  echo "Error updating record: " . mysqli_error($this->conn);
		}
	}

	function delete($table, $array) {
		if (count($array) > 0) {
      foreach ($array as $key => $value) {
        $value = "'$value'";
        $conditions[] = "$key = $value";
      }
    }
    $imploded_array = implode(' AND ', $conditions);
		$sql = "DELETE FROM $table WHERE $imploded_array";
    if (mysqli_query($this->conn, $sql)) {
    	return true;
    } else {
    	return "Error deleting record: " . mysqli_error($this->conn);
    }
	}
}

$db = new DB;