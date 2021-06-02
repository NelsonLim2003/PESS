<?php require_once('nav.php'); ?>
<?php 
// connect to a database
require_once('db.php');
// create connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
// Check connection
if ($conn->connect_error){
    die("Connection failed: ". $conn->connect_error);
}

$sql = "SELECT patrolcar_id, patrolcar_status.patrolcar_status_desc FROM patrolcar 
JOIN patrolcar_status 
ON patrolcar.patrolcar_status_id=patrolcar_status.patrolcar_status_id 
WHERE patrolcar.patrolcar_status_id='2' OR patrolcar.patrolcar_status_id ='3'";

$result = $conn->query($sql);

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $patrolcarArray[$row['patrolcar_id']] = $row['patrolcar_status_desc'];
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Police Emergency Service System</title>
    <link rel="stylesheet" href="pess_style.css">
</head>
<body>
    <form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
    <table>
        <tr>
            <td style="color: white; text-align: center; font-weight: bold; font-size: 30px;" colspan="2">Incident Detail</td>
        </tr>
        <tr>
            <td style="color: white;">Caller's Name :</td>
            <td style="color: white;"><?php echo $_POST['callerName']; ?>
                <input type="hidden" name="callerName" id="callerName" value="<?php echo $_POST['callerName']; ?>">
            </td>
        </tr>
        <tr>
            <td style="color: white;">Contact No :</td>
            <td style="color: white;"><?php echo $_POST['contactNo']; ?>
                <input type="hidden" name="contactNo" id="contactNo" value="<?php echo $_POST['contactNo']; ?>">
            </td>
        </tr>
        <tr>
            <td style="color: white;">Location :</td>
            <td style="color: white;"><?php echo $_POST['location']; ?>
                <input type="hidden" name="location" id="location" value="<?php echo $_POST['location']; ?>">
            </td>
        </tr>
        <tr>
            <td style="color: white;">Incident Type :</td>
            <td style="color: white;"><?php echo $_POST['incidentType']; ?>
                <input type="hidden" name="incidentType" id="incidentType" value="<?php echo $_POST['incidentType']; ?>">
            </td>
        </tr>
        <tr>
            <td style="color: white;">Description :</td>
            <td><textarea name="incidentDesc" id="incidentDesc" cols="30" rows="5" readonly><?php echo $_POST['incidentDesc']; ?>
        </textarea>
            <input type="hidden" name="incidentDesc" id="incidentDesc" value="<?php echo $_POST['incidentDesc'];?>"></td>
        </tr>
    </table>

    <table>
        <tr>
            <td style="color: white; text-align: center; font-weight: bold; font-size: 30px;" colspan="3">Dispatch Patrolcar Panel</td>
        </tr>
        <?php 
            foreach ($patrolcarArray as $key => $value) {
        ?>
        <tr>
            <td><input type="checkbox" name="chkPatrolcar[]" value="<?php echo $key; ?>"></td>
            <td style="color: white;"><?php echo $key; ?></td>
            <?php if ($value == "Free"){ ?>
                <td style="color: LawnGreen; font-weight: bold;"><?php echo $value; ?></td>
            <?php } else { ?>
                <td style="color: orange; font-weight: bold;"><?php echo $value; ?></td>
            <?php } ?>
        </tr>                
        <?php } ?>
        <tr>
            <td><input type="reset" name="btnCancel" id="btnCancel" value="Reset"></td>
            <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnDispatch" id="btnDispatch" value="Dispatch"></td>
        </tr>
    </table>
    </form>
</body>
</html>

<?php 
// if postback via clicking Dispatch button
if (isset($_POST['btnDispatch'])){
    require_once('db.php');

    // create a connection
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
    // check connection
    if($conn->connect_error){
        die("Connection faield: ". $conn->connect_error);
    }

    $patrolcarDispatched = $_POST["chkPatrolcar"];
    $numOfPatrolcarDispatched = count($patrolcarDispatched);

    if ($numOfPatrolcarDispatched > 0){
        $incidentStatus = '2'; // Status set as Dispatched
    } else {
        $incidentStatus = '1'; // Status set as Pending
    }
    $sql = "INSERT INTO incident (caller_name, phone_number, incident_type_id, incident_location, incident_desc, incident_status_id) 
            VALUES('".$_POST['callerName']."','".$_POST['contactNo']."','".$_POST['incidentType']."', '".$_POST['location']."', '".$_POST['incidentDesc']."',
            $incidentStatus)";
    if ($conn->query($sql) === FALSE){
        echo "Error: ". $sql . "<br>" . $conn->error;
    }        

    // retrieve incident_id for newly inserted data
    $incidentId = mysqli_insert_id($conn);

    // update patrolcar status table and add into dispatch table
    for($i=0; $i < $numOfPatrolcarDispatched; $i++){
        // update patrol car status
        $sql = "UPDATE patrolcar SET patrolcar_status_id = '1' WHERE patrolcar_id='".$patrolcarDispatched[$i] . "'";

        if ($conn->query($sql) === FALSE) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        // insert dispatch data
        $sql = "INSERT INTO dispatch (incident_id, patrolcar_id, time_dispatched) VALUES ($incidentId, '".$patrolcarDispatched[$i]."', NOW())";

        if ($conn->query($sql) === FALSE){
            echo "ERROR: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
?>
    <script type="text/javascript">window.location="./logcall.php";</script>
<?php } ?>

<?php // validate if request comes from logcall.php or post back
if (!isset($_POST["btnProcessCall"]) && !isset($_POST["btnDispatch"]))
    header("Location: logcall.php");
?>
