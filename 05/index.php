<?php
/*
 * Homework  
 * Jeff Pollack
 * Time logged on assignemt: hours 3
 */
include ('db_connect.php');

if (isset($_POST['zip'])) {
    $zip = $_POST['zip'];
} else {
    $zip = '';
}

/*
 * NOTE:
 * Found subquery method on stackoverflow from user: dash
 * LINK: http://stackoverflow.com/questions/8779303/do-math-in-mysql-from-select
 */
//Building the sql query .................................................
$latitude1 = "SELECT latitude FROM a5_locations WHERE zipcode=$zip";
$longitude1 = "SELECT longitude FROM a5_locations WHERE zipcode=$zip";

$sql = "SELECT l.location_name, "
        . "p.*, "
        . "state, "
        . "zipcode, "
        . "GROUP_CONCAT(subject_label) AS subject_label, "
        . "(SQRT(POW(l.latitude - ($latitude1),2)+POW(l.longitude-($longitude1),2))*69.0) AS dist "
        . "FROM a5_people AS p "
        . "LEFT JOIN a5_locations AS l ON p.locationID=l.locationID "
        . "JOIN a5_people_subject AS ps ON ps.personID=p.personID "
        . "JOIN a5_subject AS s ON s.subjectID=ps.subjectID "
        . "WHERE (SQRT(POW(l.latitude - ($latitude1),2)+POW(l.longitude-($longitude1),2))*69.0)<=25 "
        . "GROUP BY p.personID "
        . "ORDER BY dist ASC";
$result = $pdo->query($sql);

// fetching the users location
$sql_user = "SELECT latitude AS userLat, longitude AS userLong, state, location_name, zipcode "
        . "FROM a5_locations AS loc WHERE zipcode = '$zip'";
$result_user = $pdo->query($sql_user);
?>

<!-- This is where the HTML portion of the program starts -->

<form method="post" action="index.php">
    <label>Enter a zip code:</label>
    <input type='text' name='zip' size='5' maxlength='5' value='<?= $zip ?>'/>
    <input type='submit' name='Filter' value="Search for Teacher"/>
</form>

<?php
if ($zip){
    // Display user input zipcode and city start    
    echo"<table  border ='1'>".
        "<tr>".
            "<th>City</th>".
            "<th>State</th>".
            "<th>Zip Code</th>".
        "</tr>";

            if ($row = $result_user->fetch(PDO::FETCH_ASSOC)) {
                $city = $row['location_name'];
                $state = $row['state'];
                $zip_loc = $row['zipcode'];

                echo "<tr>";
                echo "<td>" . $city . "</td>" .
                "<td>" . $state . "</td>" .
                "<td>" . $zip_loc . "</td>";
                echo "</tr>";
            }
    echo"</table>";
    // Display user input zipcode and city end


    // Display results start
    echo "<table  border ='1'>" .
        "<tr>" .
            "<th>Name</th>" .
            "<th>Provider #</th>" .
            "<th>Subjects</th>" .
            "<th>City</th>" .
            "<th>State</th>" . 
            "<th>Zip Code</th>" .
            "<th>Distance (mi)</th>" .
        "</tr>";

        $count=0;

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $teacher = $row['person_name'];
            $provider_num = $row['provider_number'];
            $subjects = $row['subject_label'];
            $city = $row['location_name'];
            $state = $row['state'];
            $zip_teach = $row['zipcode'];
            $distance = $row['dist'];
            $count++;

            echo "<tr>";
            echo "<td>" . $teacher . "</td>" .
            "<td>" . $provider_num . "</td>" .
            "<td>" . $subjects . "</td>" .
            "<td>" . $city . "</td>" .
            "<td>" . $state . "</td>" .
            "<td>" . $zip_teach . "</td>" .
            "<td>" . number_format($distance, 1) . "</td>";
            echo "</tr>";   
        }

        echo "<hr/>";
        if($count>=0){
            echo "$count"." people were found within 25 miles.";
        }else{
            echo "Sorry, there are no teacher with in 25 miles of that location.";
        }
        echo "<br>";
        echo "</br>";

    echo "</table>";
    // Display results end
}    
?>