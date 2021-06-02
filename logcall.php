<?php require_once('nav.php'); ?>
<?php require_once('db.php');

// Create Connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
// Check Connection
if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}

$sql = "SELECT * FROM incident_type";

$result = $conn->query($sql);
if ($result->num_rows > 0){
    while ($row = $result->fetch_assoc()) {
        $incidentType[$row['incident_type_id']] = $row['incident_type_desc'];
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
    <form name="frmLogCall" method="post" onsubmit="return validateForm()" action="dispatch.php">
        <table>
            <tr>
                <td style="color: white; text-align:center; font-size:30px; font-weight: bold" colspan="2">Log Call Panel</td>
            </tr>
            
            <tr>
                <td style="color: white;">Caller's Name :</td>
                <td><input type="text" name="callerName" id="callerName"></td>
            </tr>

            <tr>
                <td style="color: white;">Contact No :</td>
                <td><input type="text" name="contactNo" id="contactNo"></td>
            </tr>

            <tr>
                <td style="color: white;" >Location :</td>
                <td><input type="text" name="location" id="location"></td>
            </tr>

            <tr>
                <td style="color: white;">Incident Type :</td>
                <td>
                    <select name="incidentType" id="incidentType">
                        <?php //populate combo box with $incidentType 
                            foreach ($incidentType as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>">
                                <?php echo $value; ?>
                            </option>                        
                        <?php
                            }
                        ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td style="color: white;">Description :</td>
                <td><textarea name="incidentDesc" id="incidentDesc" cols="30" rows="5"></textarea></td>
            </tr>
            
            <tr>
                <td><input type="reset" value="Reset" name="btnCancel" id="btnCancel"></td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="Process Call..." name="btnProcessCall" id="btnProcessCall"></td>
            </tr>
        </table>
    </form>
</body>
</html>

<script type="text/javascript">
    function validateForm(){
        var x = document.forms["frmLogCall"]["callerName"].value;
        if (x == null || x == "")
        {
            alert("Caller Name is required.");
            return false;
        }

        var a = document.forms["frmLogCall"]["contactNo"].value;
        if (a == null || a == "")
        {
            alert("Contact Number is required.");
            return false;
        }

        var b = document.forms["frmLogCall"]["location"].value;
        if (b == null || b == "")
        {
            alert("Location is required.");
            return false;
        }

        var c = document.forms["frmLogCall"]["incidentType"].value;
        if (c == null || c == "")
        {
            alert("Incident Type is required.");
            return false;
        }

        var d = document.forms["frmLogCall"]["incidentDesc"].value;
        if (d == null || d == "")
        {
            alert("Incident Description is required.");
            return false;
        }
    }
</script>
