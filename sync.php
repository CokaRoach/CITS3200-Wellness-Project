<?php

include "config.php";
include "layout.php";
include "connect.php";

$tbl_name = "student";

if (isset($_GET["username"]) && isset($_GET["password"])) {
	$username = escape_data($_GET['username']);
	$password = escape_data($_GET['password']);

	if (is_int_val($username)) {
		$sql="SELECT * FROM $tbl_name, classmap WHERE student.id='$username' AND student.password='$password' AND classmap.id=student_id";
		$result = mysql_query($sql);

		if (mysql_num_rows($result) != 0) {
			sync($username, $password);
		} else {
			echo "NO";
		}
	} else {
		echo "NO";
	}
} else {
	echo "NO";
}

function sync($username, $password) {
	$result = mysql_fetch_array(mysql_query($sql));
	$lower = new DateTime($result["start"]);
	$upper = new DateTime($result["finish"]);
	$today = new DateTime(date("Y-m-d"));
	$window = $result["window"];

	if ($today <= $upper && $ $today >= $lower) {
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		echo "<!DOCTYPE plist PUBLIC \"-//Apple//DTD PLIST 1.0//EN\" \"http://www.apple.com/DTDs/PropertyList-1.0.dtd\">\n";
		echo "<plist version=\"1.0\">\n";
		echo "<array>\n";

		$month= date("m");
		$date= date("d");
		$year= date("Y");

		for ($i = 0; $i < $window; $i++) { //day 1 will be today, 2 will be yesterday etc...
			$hr = "Enter Data";
			$sleepHours = "Enter Data";
			$health = "Enter Data";
			$ratings;

			echo "<array>\n";

			$nextDate = date("d-m-y", mktime(0, 0, 0, $m, ($de-$i), $y));

			$sql = "SELECT * FROM training_records1 WHERE id='$username' AND daydate=$nextDate";
			$fitnessData = mysql_query($sql); //has fitness data

			$sql = "SELECT * FROM training_records2 WHERE id='$username' AND dayDate=$nextDate";
			$ratingData = mysql_query($sql); //has heart rate, sleep hours, health and ratings. Ratings are in form 2,4,5,3,1 etc... where 2 corresponds to the value for the first rating for this group etc...

			$sql = "SELECT rating_item.description FROM rating_item, rating_item_map, classmap WHERE classmap.student_id=\"$username\" AND rating_item_map.groupname=classmap.class_name AND rating_item.id=rating_item_map.id";
			$availableRatings = mysql_query($sql); //use this to get the names of the ratings

			$sql = "SELECT compcodes.description, training_records1.duration FROM training_records1, compcodes WHERE training_records1.id=\"$username\" AND AND daydate=$nextDate AND compcode.compcode=training_records1.compcode";
			$activityData = mysql_query($sql); //get the activity data for this day

			if (mysql_row_count($ratingData) != 0) { //we have an entry for this day
				$temp = mysql_fetch_array($ratingData);

				if ($temp["heart_rate"] != "NULL") { //if no data then all 3 fields will be null
					$hr = $temp["heart_rate"];
					$sleep = $temp["sleep"];
					$health = $temp["health"];
				}
				
				if ($temp["ratings"] != "") { //then user must have entered rating data
					$ratings = explode(",", $temp["ratings"]);
				}
			}
								
			echo "<dict>\n";
			echo "<key>title</key>\n";
			echo "<string>Wellness Data</string>\n";
			echo "<key>completed</key>\n";
		
			if ($hr != "Enter Data") {
				echo "<string>YES</string>\n";
			} else {
				echo "<string>NO</string>\n";
			}

			echo "<key>data</key>\n";
			echo "<array>\n";			
			echo "<dict>\n";
			echo "<key>name</key>\n";
			echo "<string>Resting HR</string>\n";
			echo "<key>rating</key>\n";
			echo "<string>$hr</string>\n";
			echo "<key>picker</key>\n";
			echo "<true/>\n";
			echo "<key> edited </key>\n";
			echo "<string>NO</string>\n";
			echo "</dict>\n";
			echo "<dict>\n";
			echo "<key>name</key>\n";
			echo "<string>Sleep hours</string>\n";
			echo "<key>rating</key>\n";
			echo "<string>$sleep</string>\n";
			echo "<key>picker</key>\n";
			echo "<true/>\n";
			echo "<key> edited </key>\n";
			echo "<string>NO</string>\n";
			echo "</dict>\n";
			echo "<dict>\n";
			echo "<key>name</key>\n";
			echo "<string>Health</string>\n";
			echo "<key>rating</key>\n";
			echo "<string>$health</string>\n";
			echo "<key>picker</key>\n";
			echo "<false/>\n";
			echo "<key> edited </key>\n";
			echo "<string>NO</string>\n";
			echo "</dict>\n";
			echo "</array>\n";
			echo "</dict>\n";
			
			echo "<dict>\n";
			echo "<key>title</key>\n";
			echo "<string>Exercise Data</string>\n";
			echo "<key>completed</key>\n";

			if (isset($ratings)) {
				echo "<string>YES</string>\n";
				echo "<key>data</key>\n";
				echo "<array>\n";

				$count = 0;
				while (($r = mysql_fetch_array($availableRatings) != null) {
					echo "<dict>\n";
					echo "<key>name</key>\n";
					echo "<string>" . $r[0] . "</string>\n";
					echo "<key>rating</key>\n";
					echo "<string>" . $ratings[$count] . "</string>\n";
					echo "<key>picker</key>\n";
					echo "<false/>\n";
					echo "<key> edited </key>\n";
					echo "<string>NO</string>\n";
					echo "</dict>\n";

					$count++;	
				}					
			} else {
				echo "<string>NO</string>\n";
				echo "<key>data</key>\n";
				echo "<array>\n";

			while (($r = mysql_fetch_array($availableRatings) != null) {
					echo "<dict>\n";
					echo "<key>name</key>\n";
					echo "<string>" . $r[0] . "</string>\n";
					echo "<key>rating</key>\n";
					echo "<string>Enter Data</string>\n";
					echo "<key>picker</key>\n";
					echo "<false/>\n";
					echo "<key> edited </key>\n";
					echo "<string>NO</string>\n";
					echo "</dict>\n";
				}					
			}

			echo "</array>\n";
			echo "</dict>\n";

			echo "<dict>\n";
					
			echo "<key>title</key>\n";
			echo "<string>Rating Items</string>\n";
			echo "<key>completed</key>\n";

			if (mysql_row_count($activityData) != 0) {
				echo "<string>YES</string>\n";
				echo "<key>data</key>\n";
				echo "<array>\n";
				
				while (($activities = mysql_fetch_array($activityData) != null) {
					echo "<dict>\n";
					echo "<key>name</key>\n";
					echo "<string>" . $activities[0] . "</string>\n"; //activity name
					echo "<key>rating</key>\n";
					echo "<string>" . $activities[1] . "</string>\n"; //activity duration
					echo "<key>picker</key>\n";
					echo "<false/>\n";
					echo "<key>edited</key>\n";
					echo "<string>NO</string>\n";
					echo "</dict>\n";
				}	
			} else {
				echo "<string>NO</string>\n";
				echo "<key>data</key>\n";
				echo "<array>\n";
				echo "<dict>\n";
				echo "<key>name</key>\n";
				echo "<string>Activity</string>\n";
				echo "<key>rating</key>\n";
				echo "<string>Enter Data</string>\n";
				echo "<key>picker</key>\n";
				echo "<false/>\n";
				echo "<key>edited</key>\n";
				echo "<string>NO</string>\n";
				echo "</dict>\n";
			}

			echo "</array>\n";
			echo "</dict>\n";
			echo "<string>$nextDate</string>\n";
			echo "</array>\n";		
		}
		echo "</array>\n";
		echo "</plist>\n";	
	} else {
		echo "outside of window";
	}
}
?>