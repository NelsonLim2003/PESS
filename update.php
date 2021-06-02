<!-- Part 1 -->
<?php require_once('nav.php'); ?>
<br><br>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Patrol Car Status</title>
    <link rel="stylesheet" href="pess_style.css">
</head>
<body>
    

<?php 
if (!isset($_POST["btnSearch"])){
?>
    <!-- Create a form to search for patrol car based on ID  -->
    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" name="form1" method="post">
        <table class="ContentStyle">
            <tr></tr>
            <tr>
                <td style="color: white;">Patrol Car ID :</td>
                <td><input type="text" name="patrolCarId" id="patrolCarId"></td>
                <td><input type="submit" id="btnSearch" name="btnSearch" value="Search"></td>
            </tr>
        </table>
    </form>
    <!-- Part 2 -->
    <?php } 

/* if postback via clicking search button */
elseif (isset($_POST["btnSearch"])) {
    require_once('db.php');
    // create database connection
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
    // check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // retrieve patrol car detail
    $sql = "SELECT * FROM patrolcar WHERE patrolcar_id = '".$_POST['patrolCarId']."'";
    $result = $conn->query($sql);
    // if patrol car does not exist , redirect back to update.php
    if ($result->num_rows == 0) {
    ?>
        <script type="text/javascript">window.location="./update.php";</script> 
    <?php }

    // else if the patrol car found
    if ($result->num_rows > 0){
        while ($row = $result->fetch_assoc()) {
            $patrolCarId = $row['patrolcar_id'];
            $patrolCarStatusId = $row['patrolcar_status_id'];
        }
        // retrieve from patrolcar status table for populating the combo box
        $sql = "SELECT * FROM patrolcar_status";
        $result = $conn->query($sql);
        if ($result->num_rows > 0){
            while ($row = $result->fetch_assoc()){
                $patrolCarStatusArray[$row['patrolcar_status_id']] = $row['patrolcar_status_desc'];
            }
        }

        while ($row = $result->fetch_assoc()){
            $patrolCarStatusArray[$row['patrolcar_status_id']] = $row['patrolcar_status_desc'];
            echo "<br>";
                print_r($patrolCarStatusArray[$row['patrolcar_status_id']]);
        }
        $conn->close();
    }
    ?>
        <!-- Display a form for operator to update status of patrol car -->
        <form name="form2" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
            <table>
                <tr></tr>
                <tr>
                    <td style="color: white;">ID :</td>
                    <td style="color: white;"><?php echo $patrolCarId; ?>
                        <input type="hidden" name="patrolCarId" id="patrolCarId" value="<?php echo $patrolCarId; ?>">    
                    </td>
                </tr>
                <tr>
                    <td style="color: white;">Status :</td>
                    <td><select name="patrolCarStatus" id="patrolCarStatus">
                        <?php foreach ($patrolCarStatusArray as $key => $value) { ?>
                        <option value="<?php echo $key; ?>"
                            <?php if ($key == $patrolCarStatusId) { ?> selected="selected" 
                            <?php } ?>
                        >
                            <?php echo $value; ?>
                        </option>
                    <?php } ?>
                    </select></td>
                </tr>
                <tr>
                    <td><input type="reset" name="btnCancel" id="btnCancel" value="Reset"></td>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnUpdate" id="btnUpdate" value="Update"></td>
                </tr>
            </table>
        </form>
    <?php  }?>
    <!-- Part 3 -->
    <?php
    /* if postback via clicking update */
    if (isset($_POST['btnUpdate'])){
        require_once('db.php');
        // create database connection
        $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
        // check connection
        if ($conn->connect_error){
            die("Connection failed: ". $conn->connect_error);
        }
        // update patrol car status
        $sql = "UPDATE patrolcar SET patrolcar_status_id = '".$_POST['patrolCarStatus']."' WHERE patrolcar_id = '".$_POST['patrolCarId']."'";
        if ($conn->query($sql) === FALSE){
            echo "Error: ". $sql . "<br>" . $conn->error;
        }

        /* if patrol car status is Arrived (4) then capture the time of arrival */
        if ($_POST["patrolCarStatus"] == '4'){
            $sql = "UPDATE dispatch SET time_arrived = NOW() WHERE time_arrived is NULL AND patrolcar_id = '".$_POST['patrolCarId']."'";
            if ($conn->query($sql) === FALSE) {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        /* else if status is FREE (3) then capture time of completion */ 
        else if ($_POST['patrolCarStatus'] == '3'){
            /* First, retrieve the incident ID from dispatch table handled by that patrol car */
            $sql = "SELECT incident_id FROM dispatch WHERE time_completed is NULL AND patrolcar_id = '".$_POST['patrolCarId']."'";
            $result = $conn->query($sql);
            if($result->num_rows > 0){
                while ($row = $result->fetch_assoc()){
                    $incidentId = $row['incident_id'];
                }
            }

            // next update dispatch table
            $sql = "UPDATE dispatch SET time_completed = NOW() WHERE time_completed is NULL and patrolcar_id = '".$_POST['patrolCarId']."'";
            if ($conn->query($sql) == FALSE) {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }

            /* update incident to completed (3) all patrol car attended to it are free now */
            $sql = "UPDATE incident SET incident_status_id = '3' WHERE incident_id = '$incidentId' AND NOT EXISTS (SELECT * FROM dispatch WHERE time_completed IS NULL AND incident_id = '$incidentId')";
            if ($conn->query($sql) === FALSE) {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
        $conn->close();
    ?>
    <script type="text/javascript">window.location="logcall.php";</script>    
<?php } ?>


</body>
</html>