<?php
echo "<!DOCTYPE html>
<html>
<head>
    <title>College List</title>
    <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css'>
    <style>
        body {
            background: linear-gradient(to right, #007BFF, #00C9FF); /* This is a blue gradient */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: auto; /* Enable scrolling if the content is longer than the screen */
        }
        .container {
            background: white;
            border-radius: 50px;
            padding: 20px;
            width: 60%;
            max-height: 80vh; /* Limit the height to 80% of the viewport height */
            overflow-y: auto; /* Enable vertical scrolling inside the container */
        }
        h2 {
            font-family: 'Times New Roman', sans-serif;
        }
        table {
            width: 100%;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: orange;
            color: white;
        }
        tr:hover {background-color: #f5f5f5;}
    </style>
</head>
<body>
    <div class='container'>
        <h2 class='text-center'>LIST OF COLLEGES</h2>
        <table class='table'>";

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $rank=$_POST['rank'];
    $marks=$_POST['marks'];
    $course=$_POST['course'];
    $cast=$_POST['cast'];
    $city=$_POST['city'];
}
$conn=mysqli_connect('localhost','root','','neet_22_23');
if($conn->connect_error) {
    echo "Not Connected\n";
}

$catg=array(
    'OPEN'=>'OPEN_L',
    'OBC'=>'OBC_L',
    'SC'=>'SC_L',
    'ST'=>'ST_L',
    'VJA'=>'VJA_L',
    'EWS'=>'EWS_L',
    'NTB'=>'NTB_L',
    'NTD'=>'NTD_L',
    'NTC'=>'NTC_L',
    'D1'=>'D1_L',
    'D2'=>'D2_L',
    'D3'=>'D3_L',
    'PH'=>'PH_L',
    'MKB'=>'MKB_L',
    'NRI'=>'NRI_L'
			
);

#Instead of decreasing cuoff by 5% each time ,we can set the  input marks or rank by increasing 5% so it avoids to decrease cuoff each time
$catgCol=$catg[$cast];
$percentageSub=5;

if(!empty($rank))
   {
    $sql="SELECT College, City
            FROM (
                SELECT College, City, 
                       AVG(IFNULL($catgCol, 0)) AS avg_cutoff
                FROM (
                    SELECT College, City, $catgCol
                    FROM `neet_cut_off_22_23_rank`
                    WHERE Course = '$course' AND $catgCol IS NOT NULL AND $catgCol != 0 
                    UNION ALL
                    SELECT College, City, $catgCol
                    FROM `neet_cut_off_21_22_rank`
                    WHERE Course = '$course' AND $catgCol IS NOT NULL AND $catgCol != 0 
                ) AS combined_data
                GROUP BY College, City
            ) AS average_data
            WHERE ($rank <= (avg_cutoff + (avg_cutoff * $percentageSub / 100)))";

    if (!empty($city)) {
        $sql .= " AND City='$city'";
    }

    $sql .= " ORDER BY avg_cutoff ASC";
    }

    /*
    $sql="SELECT College,City FROM `neet_cut_off_22_23_rank` 
    WHERE $rank<=($catgCol + ( $catgCol * $percentageSub/100))  AND Course='$course'";
    if(!empty($city)){
        #$city = trim($city); // This will remove spaces at the beginning and end of the city string
        $sql.=" AND City='$city'";
    } 

    $sql.=" ORDER BY $catgCol ASC";
    */

 else if(!empty($marks)){
    $sql = "SELECT College, City
            FROM (
                SELECT College, City, 
                       AVG(IFNULL($catgCol, 0)) AS avg_cutoff
                FROM (
                    SELECT College, City, $catgCol
                    FROM `neet_cut_off_22_23_marks`
                    WHERE Course = '$course' AND $catgCol IS NOT NULL AND $catgCol != 0 
                    UNION ALL
                    SELECT College, City, $catgCol
                    FROM `neet_cut_off_21_22_marks`
                    WHERE Course = '$course' AND $catgCol IS NOT NULL AND $catgCol != 0 
                ) AS combined_data
                GROUP BY College, City
            ) AS average_data
            WHERE ($marks >= (avg_cutoff - (avg_cutoff * $percentageSub / 100)))";

    if (!empty($city)) {
        $sql .= " AND City='$city'";
    }

    $sql .= " ORDER BY avg_cutoff DESC";
}

    /*
    $sql="SELECT College,City FROM `neet_cut_off_22_23_marks` 
    WHERE $marks>= IFNULL($catgCol,0) - ( IFNULL($catgCol,0) * $percentageSub/100) AND $catgCol IS NOT NULL AND $catgCol!=0  AND Course='$course'";
    if(!empty($city)){
        $sql.=" AND City='$city'";
    } 
    // order college by highest cutoff
    $sql.=" ORDER BY $catgCol DESC";
    */

$result = $conn->query($sql);

// Check if query was successful
if ($result === FALSE) {
    echo "Error: " . $sql . "<br>" . $conn->error;
} else {
    // Check if there are any rows returned
    if ($result->num_rows > 0) {
        // Output data of each row
        echo "<tr><th>College Names</th></tr>\n";
        
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["College"] . ", " . $row["City"] . "</td></tr>\n";
        }
    } else {
        echo "<tr><td>No results found</td></tr>\n";
        

    }
}

// Close connection
$conn->close();
echo "  </table>
    </div>
</body>
</html>";
?>
