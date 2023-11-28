<!-- Add check for logged in and privleges -->
<?php
include_once('database/dbPersons.php');
require_once('include/output.php');
require_once('domain/Person.php');
require_once('include/input-validation.php');
require_once('universal.inc');
require_once('header.php');

session_cache_expire(30);
session_start();

ini_set("display_errors", 1);
error_reporting(E_ALL);

$loggedIn = false;
$accessLevel = 0;
$userID = null;
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
    $accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}
// Require admin privileges
if ($accessLevel < 2) {
    header('Location: login.php');
    echo '<div class="error-toast"><p> Improper access level </p> </div>';
    die();
}

$foodbank = NULL;
if (isset($_GET["id"])) {
    $id = $_GET["id"];
    $foodbank = retrieve_person($id);
    if ($foodbank == false) {
        echo '<div class = "error-toast"><p>Incorrect food bank given</p></div>';
    }
} else {
    //this will be changed to error checking
    echo '<div class= "error-toast"><p> No food bank given </p> </div>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // make every submitted field SQL-safe except for password
    $ignoreList = array('password','tag');
    $args = sanitize($_POST, $ignoreList);

    // echo "<p>The form was submitted:</p>";
    // foreach ($args as $key => $value) {
    //     echo "<p>$key: $value</p>";
    // }

    $required = array(
        'fb-name', 'phone', 'website', 'address', 'address2', 'city', 'county', 'state', 'zip', 'opnotes', 'adtl-services', 'tag',
        'available-sundays', 'available-mondays', 'available-tuesdays', 'available-wednesday', 'available-thursdays', 'available-fridays',
        'available-saturdays'
    );

    $errors = false;
    if (!wereRequiredFieldsSubmitted($args, $required)) {
        //TODO put back error check, need to fix required fields
    }

    $id = $args['id'];
    $fbName = $args['fb-name'];


    $phone = validateAndFilterPhoneNumber($args['phone']);
    if (!$phone) {
        $errors = true;
        echo 'bad phone';
    }

    $website = $args['website'];

    $address = $args['address'];

    if ($args['address2'] == "" || $args['address2'] == null) {
        $address2 = null;
    } else {
        $address2 = $args['address2'];
    }

    $city = $args['city'];

    $county = $args['county'];

    $state = $args['state'];
    if (!valueConstrainedTo($state, array(
        'AK', 'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DC', 'DE', 'FL', 'GA',
        'HI', 'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MD', 'ME',
        'MI', 'MN', 'MO', 'MS', 'MT', 'NC', 'ND', 'NE', 'NH', 'NJ', 'NM',
        'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX',
        'UT', 'VA', 'VT', 'WA', 'WI', 'WV', 'WY'
    ))) {
        $errors = true;
    }
    $zipcode = $args['zip'];
    if (!validateZipcode($zipcode)) {
        $errors = true;
        echo 'bad zip';
    }

    //$notes = $args['opnotes'];

    if ($args['opnotes'] == "" || $args['opnotes'] == null) {
        $notes = null;
    } else {
        $notes = $args['opnotes'];
    }

    //$altServices = $args['adtl-services'];

    if ($args['adtl-services'] == "" || $args['adtl-services'] == null) {
        $altServices = null;
    } else {
        $altServices = $args['adtl-services'];
    }

    // $tag = $args['tag'];
    // if (!valueConstrainedTo($tag, ['Male', 'Female', 'Other'])) {
    //     $errors = true;
    //     echo 'bad tag';
    // }
    

    $startDate = $args['frequency'];

    $days = array('sundays', 'mondays', 'tuesdays', 'wednesdays', 'thursdays', 'fridays', 'saturdays');
    $availability = array();
    $availabilityCount = 0;
    foreach ($days as $day) {
        if (isset($args['available-' . $day])) {
            $startKey = $day . '-start';
            $endKey = $day . '-end';
            if (!isset($args[$startKey]) || !isset($args[$endKey])) {
                $errors = true;
            }
            $start = $args[$startKey];
            $end = $args[$endKey];
            // $range24h = validate12hTimeRangeAndConvertTo24h($start, $end);
            $range24h = null;
            if (validate24hTimeRange($start, $end)) {
                $range24h = [$start, $end];
            }
            if (!$range24h) {
                $errors = true;
                echo "bad $day availability";
            }
            $availability[$day] = $range24h;
            $availabilityCount++;
        } else {
            $availability[$day] = null;
        }
    }
    if ($availabilityCount == 0) {
        $errors = true;
        echo '<div class="error-toast">bad availability - none chosen</div>';
    }
    $sundaysStart = '';
    $sundaysEnd = '';
    if ($availability['sundays']) {
        $sundaysStart = $availability['sundays'][0];
        $sundaysEnd = $availability['sundays'][1];
    }
    $mondaysStart = '';
    $mondaysEnd = '';
    if ($availability['mondays']) {
        $mondaysStart = $availability['mondays'][0];
        $mondaysEnd = $availability['mondays'][1];
    }
    $tuesdaysStart = '';
    $tuesdaysEnd = '';
    if ($availability['tuesdays']) {
        $tuesdaysStart = $availability['tuesdays'][0];
        $tuesdaysEnd = $availability['tuesdays'][1];
    }
    $wednesdaysStart = '';
    $wednesdaysEnd = '';
    if ($availability['wednesdays']) {
        $wednesdaysStart = $availability['wednesdays'][0];
        $wednesdaysEnd = $availability['wednesdays'][1];
    }
    $thursdaysStart = '';
    $thursdaysEnd = '';
    if ($availability['thursdays']) {
        $thursdaysStart = $availability['thursdays'][0];
        $thursdaysEnd = $availability['thursdays'][1];
    }
    $fridaysStart = '';
    $fridaysEnd = '';
    if ($availability['fridays']) {
        $fridaysStart = $availability['fridays'][0];
        $fridaysEnd = $availability['fridays'][1];
    }
    $saturdaysStart = '';
    $saturdaysEnd = '';
    if ($availability['saturdays']) {
        $saturdaysStart = $availability['saturdays'][0];
        $saturdaysEnd = $availability['saturdays'][1];
    }

    if ($errors) {
        echo '<div class="error-toast"><p>Your form submission contained unexpected input.</p> </div>';
        die();
    }


    // $result = update_food_bank($id,$fbName,$address,$city,$state,$zipcode,$phone,$startDate,$notes,$sundaysStart,$sundaysEnd,$mondaysStart,$mondaysEnd,$tuesdaysStart,$tuesdaysEnd,$wednesdaysStart,$wednesdaysEnd,$thursdaysStart,$thursdaysEnd,$fridaysStart,$fridaysEnd,$saturdaysStart,$saturdaysEnd,null,$address2,$county,$website,$altServices);
    $result = update_person_profile($id, $fbName,NULL, '', $address, $city, $state, $zipcode, $id, $phone, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $sundaysStart, $sundaysEnd, $mondaysStart, $mondaysEnd, $tuesdaysStart, $tuesdaysEnd, $wednesdaysStart, $wednesdaysEnd, $thursdaysStart, $thursdaysEnd, $fridaysStart, $fridaysEnd, $saturdaysStart, $saturdaysEnd, null, $address2, $county, $website, $altServices, $startDate, $notes);
    if (!$result) {
        echo '<div class="error-toast"><p>Failed to  update food bank.</p></div>';
    } else {
        echo '<div class="happy-toast"<p>Food bank updated successfully!</p></div>';
        Header("refresh:2;url=viewfoodbank.php?id=" . $id);
    }
}
?>




<!DOCTYPE html>
<html>

<head>
    <title>FUMC FB VMS | Edit Food Bank</title>
</head>

<body>
    <h1>Edit a Food Bank</h1>
    <?php if ($foodbank) : ?>
        <?php
        $times = [
            '12:00 AM', '1:00 AM', '2:00 AM', '3:00 AM', '4:00 AM', '5:00 AM',
            '6:00 AM', '7:00 AM', '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM',
            '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM',
            '6:00 PM', '7:00 PM', '8:00 PM', '9:00 PM', '10:00 PM', '11:00 PM',
            '11:59 PM'
        ];
        $values = [
            "00:00", "01:00", "02:00", "03:00", "04:00", "05:00",
            "06:00", "07:00", "08:00", "09:00", "10:00", "11:00",
            "12:00", "13:00", "14:00", "15:00", "16:00", "17:00",
            "18:00", "19:00", "20:00", "21:00", "22:00", "23:00",
            "23:59"
        ];

        function buildSelect($name, $disabled = false, $selected = null)
        {
            global $times;
            global $values;
            if ($disabled) {
                $select = '
            <select id="' . $name . '" name="' . $name . '" disabled>';
            } else {
                $select = '
            <select id="' . $name . '" name="' . $name . '">';
            }
            if (!$selected) {
                $select .= '<option disabled selected value>Select a time</option>';
            }
            $n = count($times);
            for ($i = 0; $i < $n; $i++) {
                $value = $values[$i];
                if ($selected == $value) {
                    $select .= '
                <option value="' . $values[$i] . '" selected>' . $times[$i] . '</option>';
                } else {
                    $select .= '
                <option value="' . $values[$i] . '">' . $times[$i] . '</option>';
                }
            }
            $select .= '</select>';
            return $select;
        }
        ?>

        <main class="general">
            <form class="editfb-form" method="post">
                <h2>Update Food Bank Form</h2>
                <p>Please fill out each section of the following form to edit a food bank.</p>
                <p>An asterisk (<label><em>*</em></label>) indicates a required field.</p>
                <fieldset>
                    <legend>Food Bank Information</legend>
                    <input type="hidden" name="id" value="<?php echo $id ?>">
                    <label for="fb-name"><em>* </em>Food Bank Name</label>
                    <input type="text" id="fb-name" name="fb-name" value="<?php echo $foodbank->get_first_name(); ?> " required placeholder="Enter the food bank name">

                    <label for="phone"><em>* </em>Phone Number</label>
                    <input type="tel" id="phone" name="phone" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required placeholder="Ex. (555) 555-5555" value="<?php echo '(' . substr($foodbank->get_phone1(), 0, 3) . ') ' . substr($foodbank->get_phone1(), 3, 3) . '-' . substr($foodbank->get_phone1(), 6); ?>">

                    <label for="website"><em>* </em>Website Link</label>
                    <input type="text" id="website" name="website" required placeholder="Enter the food bank website link" value="<?php echo $foodbank->get_website(); ?>">

                    <label for="address"><em>* </em>Address</label>
                    <input type="text" id="address" name="address" required placeholder="Enter the food bank address" value=<?php echo $foodbank->get_address(); ?>>

                    <label for="address2">Address 2</label>
                    <?php if ($foodbank->get_address()) : ?>
                        <?php echo '<input type="text" id="address2" name="address2" placeholder="Enter secondary or additional address" value="' . $foodbank->get_address2() . '">' ?>

                    <?php else : ?>
                        <?php echo '<input type="text" id="address2" name="address2" placeholder="Enter secondary or additional address">'; ?>
                    <?php endif; ?>
                    <label for="city"><em>* </em>City</label>
                    <input type="text" id="city" name="city" required placeholder="Enter the city" value="<?php echo $foodbank->get_city(); ?>">

                    <label for="county"><em>* </em>County</label>
                    <input type="text" id="county" name="county" required placeholder="Enter the county" value=" <?php echo $foodbank->get_county(); ?> ">

                    <label for="state"><em>* </em>State</label>
                    <select id="state" name="state" required>
                        <?php
                        $state = $foodbank->get_state();
                        $states = array(
                            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District Of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
                        );
                        $abbrevs = array(
                            'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
                        );
                        $length = count($states);
                        for ($i = 0; $i < $length; $i++) {
                            if ($abbrevs[$i] == $state) {
                                echo '<option value="' . $abbrevs[$i] . '" selected>' . $states[$i] . '</option>';
                            } else {
                                echo '<option value="' . $abbrevs[$i] . '">' . $states[$i] . '</option>';
                            }
                        }
                        ?>
                    </select>

                    <label for="zip"><em>* </em>Zip Code</label>
                    <input type="text" id="zip" name="zip" pattern="[0-9]{5}" title="5-digit zip code" required placeholder="Enter your 5-digit zip code" value="<?php echo $foodbank->get_zip(); ?>">


                    <label for="opnotes">Operation Notes</label>
                    <?php if ($foodbank->get_notes()) : ?>
                        <?php echo '<textarea wrap="soft" id="opnotes" name="opnotes" placeholder="Input operation notes">' . $foodbank->get_notes() . '</textarea>'; ?>
                    <?php else : ?>
                        <?php echo '<textarea wrap="soft" id="opnotes" name="opnotes" placeholder="Input operation notes"></textarea>'; ?>
                    <?php endif; ?>

                    <label for="adtl-services">Additional Services Offered</label>
                    <?php if ($foodbank->get_altServices()) : ?>
                        <?php echo '<input type="text" id="adtl-services" name="adtl-services" value="' . $foodbank->get_altServices() . '">'; ?>
                    <?php else : ?>
                        <?php echo '<input type="text" id="adtl-services" name="adtl-services">'; ?>
                    <?php endif; ?>
                    <!-- <label for="tag"><em>* </em>Tag</label>
                    <select id="tag" name="tag" required>
                        <option value="">Choose an option</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select> -->

                    <!-- Show list of Tags-->
                    <label for="tag"><em>* </em>Tags</label>
                    <?php
                        $con=connect();

                        $resulting = mysqli_query($con, "SELECT tagID, tagText FROM dbTags");
                        
                        while ($row = mysqli_fetch_array($resulting)) {
                            echo "<input name='tag[]' type='checkbox' value='" .$row['tagID']."'/> ".$row['tagText'];
                        }
                        //after submission, check if checkboxes checked, if so, add to dbFBTags
                        //post only gets all checked 
                        if(isset($_POST['tag'])){
                            foreach ($_POST['tag'] as $selected) {
                                // echo "<br>".$selected. "was checked.<br>";
                                // if there isn't a tag in dbFBTags with that id already, add it
                                $sq="SELECT dbFBTags WHERE id=$selected";
                                if(!$sq){
                                    // echo "tag added";
                                    mysqli_query($con, "INSERT INTO dbFBTags(id, userID) VALUES ('$selected','$id')");
                                } else{
                                    // echo "tag already exists";
                                }
                            }
                        }
                    ?>

                </fieldset>


                <fieldset>
                    <legend>Food Bank Schedule</legend>

                    <br>

                    <p>Please enter the days, times, and frequency of the food bank.</p>


                    <label><em>* </em>Availability</label>
                    <p>Enter the days and times the food bank is available each week.</p>

                    <div class="availability-container">
                        <?php
                        $start = $foodbank->get_sunday_availability_start();
                        $end = $foodbank->get_sunday_availability_end();
                        $day = $start && $end;
                        ?>
                        <div class="availability-day">
                            <p class="availability-day-header">
                                <input id="available-sundays" name="available-sundays" type="checkbox" <?php if ($day) echo 'checked'; ?>>
                                <label for="available-sundays">Sundays</label>
                            </p>
                            <p><em class="hidden">* </em>From</p>
                            <?php echo buildSelect('sundays-start', !$day, $start) ?>
                            <!-- <input type="text" id="sundays-start" name="sundays-start" value="<?php echo time24hTo12h($start); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                    else echo 'required'; ?>> -->
                            <p><em class="hidden">* </em>to</p>
                            <?php echo buildSelect('sundays-end', !$day, $end) ?>
                            <!-- <input type="text" id="sundays-end" name="sundays-end" value="<?php echo time24hTo12h($end); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                else echo 'required'; ?>> -->
                            <p id="sundays-range-error" class="hidden error">Start time must come before end time.</p>
                        </div>
                        <?php
                        $start = $foodbank->get_monday_availability_start();
                        $end = $foodbank->get_monday_availability_end();
                        $day = $start && $end;
                        ?>
                        <div class="availability-day">
                            <p class="availability-day-header">
                                <input id="available-mondays" name="available-mondays" type="checkbox" <?php if ($day) echo 'checked'; ?>>
                                <label for="available-mondays">Mondays</label>
                            </p>
                            <p><em class="hidden">* </em>From</p>
                            <?php echo buildSelect('mondays-start', !$day, $start) ?>
                            <!-- <input type="text" id="mondays-start" name="mondays-start" value="<?php echo time24hTo12h($start); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                    else echo 'required'; ?>> -->
                            <p><em class="hidden">* </em>to</p>
                            <?php echo buildSelect('mondays-end', !$day, $end) ?>
                            <!-- <input type="text" id="mondays-end" name="mondays-end" value="<?php echo time24hTo12h($end); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                else echo 'required'; ?>> -->
                            <p id="mondays-range-error" class="hidden error">Start time must come before end time.</p>
                        </div>
                        <?php
                        $start = $foodbank->get_tuesday_availability_start();
                        $end = $foodbank->get_tuesday_availability_end();
                        $day = $start && $end;
                        ?>
                        <div class="availability-day">
                            <p class="availability-day-header">
                                <input id="available-tuesdays" name="available-tuesdays" type="checkbox" <?php if ($day) echo 'checked'; ?>>
                                <label for="available-tuesdays">Tuesdays</label>
                            </p>
                            <p><em class="hidden">* </em>From</p>
                            <?php echo buildSelect('tuesdays-start', !$day, $start) ?>
                            <!-- <input type="text" id="tuesdays-start" name="tuesdays-start" value="<?php echo time24hTo12h($start); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                        else echo 'required'; ?>> -->
                            <p><em class="hidden">* </em>to</p>
                            <?php echo buildSelect('tuesdays-end', !$day, $end) ?>
                            <!-- <input type="text" id="tuesdays-end" name="tuesdays-end" value="<?php echo time24hTo12h($end); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                else echo 'required'; ?>> -->
                            <p id="tuesdays-range-error" class="hidden error">Start time must come before end time.</p>
                        </div>
                        <?php
                        $start = $foodbank->get_wednesday_availability_start();
                        $end = $foodbank->get_wednesday_availability_end();
                        $day = $start && $end;
                        ?>
                        <div class="availability-day">
                            <p class="availability-day-header">
                                <input id="available-wednesdays" name="available-wednesdays" type="checkbox" <?php if ($day) echo 'checked'; ?>>
                                <label for="available-wednesdays">Wednesdays</label>
                            </p>
                            <p><em class="hidden">* </em>From</p>
                            <?php echo buildSelect('wednesdays-start', !$day, $start) ?>
                            <!-- <input type="text" id="wednesdays-start" name="wednesdays-start" value="<?php echo time24hTo12h($start); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                            else echo 'required'; ?>> -->
                            <p><em class="hidden">* </em>to</p>
                            <?php echo buildSelect('wednesdays-end', !$day, $end) ?>
                            <!-- <input type="text" id="wednesdays-end" name="wednesdays-end" value="<?php echo time24hTo12h($end); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                    else echo 'required'; ?>> -->
                            <p id="wednesdays-range-error" class="hidden error">Start time must come before end time.</p>
                        </div>
                        <?php
                        $start = $foodbank->get_thursday_availability_start();
                        $end = $foodbank->get_thursday_availability_end();
                        $day = $start && $end;
                        ?>
                        <div class="availability-day">
                            <p class="availability-day-header">
                                <input id="available-thursdays" name="available-thursdays" type="checkbox" <?php if ($day) echo 'checked'; ?>>
                                <label for="available-thursdays">Thursdays</label>
                            </p>
                            <p><em class="hidden">* </em>From</p>
                            <?php echo buildSelect('thursdays-start', !$day, $start) ?>
                            <!-- <input type="text" id="thursdays-start" name="thursdays-start" value="<?php echo time24hTo12h($start); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                        else echo 'required'; ?>> -->
                            <p><em class="hidden">* </em>to</p>
                            <?php echo buildSelect('thursdays-end', !$day, $end) ?>
                            <!-- <input type="text" id="thursdays-end" name="thursdays-end" value="<?php echo time24hTo12h($end); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                    else echo 'required'; ?>> -->
                            <p id="thursdays-range-error" class="hidden error">Start time must come before end time.</p>
                        </div>
                        <?php
                        $start = $foodbank->get_friday_availability_start();
                        $end = $foodbank->get_friday_availability_end();
                        $day = $start && $end;
                        ?>
                        <div class="availability-day">
                            <p class="availability-day-header">
                                <input id="available-fridays" name="available-fridays" type="checkbox" <?php if ($day) echo 'checked'; ?>>
                                <label for="available-fridays">Fridays</label>
                            </p>
                            <p><em class="hidden">* </em>From</p>
                            <?php echo buildSelect('fridays-start', !$day, $start) ?>
                            <!-- <input type="text" id="fridays-start" name="fridays-start" value="<?php echo time24hTo12h($start); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                    else echo 'required'; ?>> -->
                            <p><em class="hidden">* </em>to</p>
                            <?php echo buildSelect('fridays-end', !$day, $end) ?>
                            <!-- <input type="text" id="fridays-end" name="fridays-end" value="<?php echo time24hTo12h($end); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                else echo 'required'; ?>> -->
                            <p id="fridays-range-error" class="hidden error">Start time must come before end time.</p>
                        </div>
                        <?php
                        $start = $foodbank->get_saturday_availability_start();
                        $end = $foodbank->get_saturday_availability_end();
                        $day = $start && $end;
                        ?>
                        <div class="availability-day">
                            <p class="availability-day-header">
                                <input id="available-saturdays" name="available-saturdays" type="checkbox" <?php if ($day) echo 'checked'; ?>>
                                <label for="available-saturdays">Saturdays</label>
                            </p>
                            <p><em class="hidden">* </em>From</p>
                            <?php echo buildSelect('saturdays-start', !$day, $start) ?>
                            <!-- <input type="text" id="saturdays-start" name="saturdays-start" value="<?php echo time24hTo12h($start); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                        else echo 'required'; ?>> -->
                            <p><em class="hidden">* </em>to</p>
                            <?php echo buildSelect('saturdays-end', !$day, $end) ?>
                            <!-- <input type="text" id="saturdays-end" name="saturdays-end" value="<?php echo time24hTo12h($end); ?>" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" <?php if (!$day) echo 'disabled';
                                                                                                                                                                                                                    else echo 'required'; ?>> -->
                            <p id="saturdays-range-error" class="hidden error">Start time must come before end time.</p>
                        </div>
                    </div>

                    <label for="frequency"><em>*</em>Frequency</label>
                    <input type="text" id="frequency" name="frequency" value="<?php echo $foodbank->get_start_date(); ?>">
                    </div>

                </fieldset>


                <p>By pressing Submit below, the food bank and assciated information you have input will be added to the system.</p>
                <input type="submit" name="editfb-form" value="Submit">
            </form>
            <a class="button cancel" href="viewfoodbank.php?id=<?php echo $id ?>" style="margin-top: .5rem">Cancel</a>

        </main>
    <?php endif; ?>
</body>

</html>