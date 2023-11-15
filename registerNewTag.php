<?php
    // Author: Sarah Harrington
    // Description: Registration page for new tags 
    session_cache_expire(30);
    session_start();
    
    require_once('include/input-validation.php');

    $loggedIn = false;
    if (isset($_SESSION['change-password'])) {
        header('Location: changePassword.php');
        die();
    }
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
    }

    // if (isset($_SESSION['_id'])) {
    //     header('Location: index.php');
    // } else {
    //     $_SESSION['logged_in'] = 1;
    //     $_SESSION['access_level'] = 0;
    //     $_SESSION['venue'] = "";
    //     $_SESSION['type'] = "";
    //     $_SESSION['_id'] = "guest";
    //     header('Location: personEdit.php?id=new');
    // }
?>

<!DOCTYPE html>
<html>


<head>
    <?php require_once('universal.inc'); ?>
    <title>Hunger Actions Coalition | Add New Tag <?php if ($loggedIn) echo ' New Tag' ?></title>
</head>

<body>
    <?php
        require_once('header.php');
        require_once('domain/Tag.php');
        require_once('database/dbTags.php');
        require_once('include/input-validation.php');
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // make every submitted field SQL-safe except for password
            $ignoreList = array('password');
            $args = sanitize($_POST, $ignoreList);

            // echo "<p>The form was submitted:</p>";
            // foreach ($args as $key => $value) {
            //     echo "<p>$key: $value</p>";
            // }

            $required = array(
                'tag' 
            );

            $errors = false;
            if (!wereRequiredFieldsSubmitted($args, $required)) {
                //TODO put back error check, need to fix required fields
            }

            //set it to next autoincrement value

            $tag = $args['tag'];

            $newTag = new Tag($tag);

            $result = add_tag($newTag);

            if (!$result) {
                echo '<div class="error-toast"><p>Failed to add tag.</p></div>';
            } else {
                echo '<div class="happy-toast"<p>New tag added successfully!</p></div>';
                Header("refresh:3;url=index.php");
                
            }   
        } else {
            $select .= '
                <option value="' . $values[$i] . '">' . $times[$i] . '</option>';
        }
    }
    $select .= '</select>';
    return $select;
}
?>

<h1>New Volunteer Registration</h1>
<main class="signup-form">
    <form class="signup-form" method="post">
        <h2>Registration Form</h2>
        <p>Please fill out each section of the following form if you would like to volunteer for the organization.</p>
        <p>An asterisk (<label><em>*</em></label>) indicates a required field.</p>
        <fieldset>
            <legend>Personal Information</legend>
            <p>The following information will help us identify you within our system.</p>
            <label for="first-name"><em>* </em>First Name</label>
            <input type="text" id="first-name" name="first-name" required placeholder="Enter your first name">

            <label for="last-name"><em>* </em>Last Name</label>
            <input type="text" id="last-name" name="last-name" required placeholder="Enter your last name">

            <label for="gender"><em>* </em>Gender</label>
            <select id="gender" name="gender" required>
                <option value="">Choose an option</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>

            <label for="birthdate"><em>* </em>Date of Birth</label>
            <input type="date" id="birthdate" name="birthdate" required placeholder="Choose your birthday" max="<?php echo date('Y-m-d'); ?>">


            <label for="address"><em>* </em>Street Address</label>
            <input type="text" id="address" name="address" required placeholder="Enter your street address">

            <label for="city"><em>* </em>City</label>
            <input type="text" id="city" name="city" required placeholder="Enter your city">

            <label for="state"><em>* </em>State</label>
            
            <select id="state" name="state" required>
                <option value="AL">Alabama</option>
                <option value="AK">Alaska</option>
                <option value="AZ">Arizona</option>
                <option value="AR">Arkansas</option>
                <option value="CA">California</option>
                <option value="CO">Colorado</option>
                <option value="CT">Connecticut</option>
                <option value="DE">Delaware</option>
                <option value="DC">District Of Columbia</option>
                <option value="FL">Florida</option>
                <option value="GA">Georgia</option>
                <option value="HI">Hawaii</option>
                <option value="ID">Idaho</option>
                <option value="IL">Illinois</option>
                <option value="IN">Indiana</option>
                <option value="IA">Iowa</option>
                <option value="KS">Kansas</option>
                <option value="KY">Kentucky</option>
                <option value="LA">Louisiana</option>
                <option value="ME">Maine</option>
                <option value="MD">Maryland</option>
                <option value="MA">Massachusetts</option>
                <option value="MI">Michigan</option>
                <option value="MN">Minnesota</option>
                <option value="MS">Mississippi</option>
                <option value="MO">Missouri</option>
                <option value="MT">Montana</option>
                <option value="NE">Nebraska</option>
                <option value="NV">Nevada</option>
                <option value="NH">New Hampshire</option>
                <option value="NJ">New Jersey</option>
                <option value="NM">New Mexico</option>
                <option value="NY">New York</option>
                <option value="NC">North Carolina</option>
                <option value="ND">North Dakota</option>
                <option value="OH">Ohio</option>
                <option value="OK">Oklahoma</option>
                <option value="OR">Oregon</option>
                <option value="PA">Pennsylvania</option>
                <option value="RI">Rhode Island</option>
                <option value="SC">South Carolina</option>
                <option value="SD">South Dakota</option>
                <option value="TN">Tennessee</option>
                <option value="TX">Texas</option>
                <option value="UT">Utah</option>
                <option value="VT">Vermont</option>
                <option value="VA" selected>Virginia</option>
                <option value="WA">Washington</option>
                <option value="WV">West Virginia</option>
                <option value="WI">Wisconsin</option>
                <option value="WY">Wyoming</option>
            </select>

            <label for="zip"><em>* </em>Zip Code</label>
            <input type="text" id="zip" name="zip" pattern="[0-9]{5}" title="5-digit zip code" required placeholder="Enter your 5-digit zip code">
        </fieldset>
        <fieldset>
            <legend>Contact Information</legend>
            <p>The following information will help us determine the best way to contact you regarding event coordination.</p>
            <label for="email"><em>* </em>E-mail</label>
            <p>This will also serve as your username when logging in.</p>
            <input type="email" id="email" name="email" required placeholder="Enter your e-mail address">

            <label for="phone"><em>* </em>Phone Number</label>
            <input type="tel" id="phone" name="phone" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required placeholder="Ex. (555) 555-5555">

            <label><em>* </em>Phone Type</label>
            <div class="radio-group">
                <input type="radio" id="phone-type-cellphone" name="phone-type" value="cellphone" required><label for="phone-type-cellphone">Cell</label>
                <input type="radio" id="phone-type-home" name="phone-type" value="home" required><label for="phone-type-home">Home</label>
                <input type="radio" id="phone-type-work" name="phone-type" value="work" required><label for="phone-type-work">Work</label>
            </div>

            <label for="contact-when" required><em>* </em>Best Time to Reach You</label>
            <input type="text" id="contact-when" name="contact-when" required placeholder="Ex. Evenings, Days">

            <label><em>* </em>Preferred Contact Method</label>
            <div class="radio-group">
                <input type="radio" id="method-phone" name="contact-method" value="phone" required><label for="method-phone">Phone call</label>
                <input type="radio" id="method-text" name="contact-method" value="text" required><label for="method-text">Text</label>
                <input type="radio" id="method-email" name="contact-method" value="email" required><label for="method-email">E-mail</label>
            </div>
        </fieldset>
        <fieldset>
            <legend>Emergency Contact</legend>
            <p>Please provide us with someone to contact on your behalf in case of an emergency.</p>
            <label for="econtact-name" required><em>* </em>Contact Name</label>
            <input type="text" id="econtact-name" name="econtact-name" required placeholder="Enter emergency contact name">

            <label for="econtact-phone"><em>* </em>Contact Phone Number</label>
            <input type="tel" id="econtact-phone" name="econtact-phone" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required placeholder="Enter emergency contact phone number. Ex. (555) 555-5555">

            <label for="econtact-name"><em>* </em>Contact Relation to You</label>
            <input type="text" id="econtact-relation" name="econtact-relation" required placeholder="Ex. Spouse, Mother, Father, Sister, Brother, Friend">
        </fieldset>
        <fieldset>
            <legend>Volunteer Information</legend>
            <p>The following information will be used to help us determine your availability and skillset.</p>
            <label for="start-date"><em>* </em>I will be available to volunteer starting</label>
            <input type="date" id="start-date" name="start-date" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>">
            <label><em>* </em>Availability</label>
            <p>Enter the days and times you will be available to volunteer each week, starting from the date above.</p>
            <div class="availability-container">
                <div class="availability-day">
                    <p class="availability-day-header">
                        <input id="available-sundays" name="available-sundays" type="checkbox" required>
                        <label for="available-sundays">Sundays</label>
                    </p>
                    <p><em class="hidden">* </em>From</p>
                    <!-- <input type="text" id="sundays-start" name="sundays-start" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" disabled> -->
                    <?php echo buildSelect('sundays-start', true) ?>
                    <p><em class="hidden">* </em>to</p>
                    <?php echo buildSelect('sundays-end', true) ?>
                    <!-- <input type="text" id="sundays-end" name="sundays-end" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" disabled> -->
                    <p id="sundays-range-error" class="hidden error">Start time must come before end time.</p>
                </div>
                <div class="availability-day">
                    <p class="availability-day-header">
                        <input id="available-mondays" name="available-mondays" type="checkbox" required>
                        <label for="available-mondays">Mondays</label>
                    </p>
                    <p><em class="hidden">* </em>From</p>
                    <?php echo buildSelect('mondays-start', true) ?>
                    <!-- <input type="text" id="mondays-start" name="mondays-start" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" disabled> -->
                    <p><em class="hidden">* </em>to</p>
                    <?php echo buildSelect('mondays-end', true) ?>
                    <!-- <input type="text" id="mondays-end" name="mondays-end" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" disabled> -->
                    <p id="mondays-range-error" class="hidden error">Start time must come before end time.</p>
                </div>
                <div class="availability-day">
                    <p class="availability-day-header">
                        <input id="available-tuesdays" name="available-tuesdays" type="checkbox" required>
                        <label for="available-tuesdays">Tuesdays</label>
                    </p>
                    <p><em class="hidden">* </em>From</p>
                    <?php echo buildSelect('tuesdays-start', true) ?>
                    <!-- <input type="text" id="tuesdays-start" name="tuesdays-start" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" disabled> -->
                    <p><em class="hidden">* </em>to</p>
                    <?php echo buildSelect('tuesdays-end', true) ?>
                    <!-- <input type="text" id="tuesdays-end" name="tuesdays-end" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" disabled> -->
                    <p id="tuesdays-range-error" class="hidden error">Start time must come before end time.</p>
                </div>
                <div class="availability-day">
                    <p class="availability-day-header">
                        <input id="available-wednesdays" name="available-wednesdays" type="checkbox" required>
                        <label for="available-wednesdays">Wednesdays</label>
                    </p>
                    <p><em class="hidden">* </em>From</p>
                    <?php echo buildSelect('wednesdays-start', true) ?>
                    <!-- <input type="text" id="wednesdays-start" name="wednesdays-start" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" disabled> -->
                    <p><em class="hidden">* </em>to</p>
                    <?php echo buildSelect('wednesdays-end', true) ?>
                    <!-- <input type="text" id="wednesdays-end" name="wednesdays-end" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" disabled> -->
                    <p id="wednesdays-range-error" class="hidden error">Start time must come before end time.</p>
                </div>
                <div class="availability-day">
                    <p class="availability-day-header">
                        <input id="available-thursdays" name="available-thursdays" type="checkbox" required>
                        <label for="available-thursdays">Thursdays</label>
                    </p>
                    <p>From</p>
                    <?php echo buildSelect('thursdays-start', true) ?>
                    <!-- <input type="text" id="thursdays-start" name="thursdays-start" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" disabled> -->
                    <p>to</p>
                    <?php echo buildSelect('thursdays-end', true) ?>
                    <!-- <input type="text" id="thursdays-end" name="thursdays-end" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" disabled> -->
                    <p id="thursdays-range-error" class="hidden error">Start time must come before end time.</p>
                </div>
                <div class="availability-day">
                    <p class="availability-day-header">
                        <input id="available-fridays" name="available-fridays" type="checkbox" required>
                        <label for="available-fridays">Fridays</label>
                    </p>
                    <p><em class="hidden">* </em>From</p>
                    <?php echo buildSelect('fridays-start', true) ?>
                    <!-- <input type="text" id="fridays-start" name="fridays-start" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" disabled> -->
                    <p><em class="hidden">* </em>to</p>
                    <?php echo buildSelect('fridays-end', true) ?>
                    <!-- <input type="text" id="fridays-end" name="fridays-end" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" disabled> -->
                    <p id="fridays-range-error" class="hidden error">Start time must come before end time.</p>
                </div>
                <div class="availability-day">
                    <p class="availability-day-header">
                        <input id="available-saturdays" name="available-saturdays" type="checkbox" required>
                        <label for="available-saturdays">Saturdays</label>
                    </p>
                    <p><em class="hidden">* </em>From</p>
                    <?php echo buildSelect('saturdays-start', true) ?>
                    <!-- <input type="text" id="saturdays-start" name="saturdays-start" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 8:00AM" disabled> -->
                    <p><em class="hidden">* </em>to</p>
                    <?php echo buildSelect('saturdays-end', true) ?>
                    <!-- <input type="text" id="saturdays-end" name="saturdays-end" pattern="([1-9]|10|11|12):[0-5][0-9]([aApP][mM])" placeholder="Ex. 4:00PM" disabled> -->
                    <p id="saturdays-range-error" class="hidden error">Start time must come before end time.</p>
                </div>
            </div>

            <label for="skills">Skills</label>
            <textarea id="skills" name="skills" placeholder="Please let us know about any special skills you may have, including proficiencies in non-English languages."></textarea>

            <label>Additional Information</label>
            <div class="checkbox-grouping">
                <div class="checkbox-pair">
                    <input id="has-computer" name="has-computer" type="checkbox">
                    <label for="has-computer">I own a computer</label>
                </div>
                <div class="checkbox-pair">
                    <input id="has-camera" name="has-camera" type="checkbox">
                    <label for="has-camera">I own a camera</label>
                </div>
                <div class="checkbox-pair">
                    <input id="has-transportation" name="has-transportation" type="checkbox">
                    <label for="has-transportation">I have access to reliable transportation</label>
                </div>
            </div>

            <label for="shirt-size"><em>* </em>T-shirt Size</label>
            <select id="shirt-size" name="shirt-size" required>
                <option value="S">Small</option>
                <option value="M">Medium</option>
                <option value="L">Large</option>
                <option value="XL">Extra Large</option>
                <option value="XXL">2X Large</option>
            </select>
        </fieldset>
        <fieldset>
            <legend>Login Credentials</legend>
            <p>You will use the following information to log in to the VMS.</p>

            <label for="email-relisting">E-mail Address</label>
            <span id="email-dupe" class="pseudo-input">Enter your e-mail address above</span>

            <label for="password"><em>* </em>Password</label>
            <input type="password" id="password" name="password" placeholder="Enter a strong password" required>

            <label for="password-reenter"><em>* </em>Re-enter Password</label>
            <input type="password" id="password-reenter" name="password-reenter" placeholder="Re-enter password" required>
            <p id="password-match-error" class="error hidden">Passwords do not match!</p>
        </fieldset>
        <p>By pressing Submit below, you are agreeing to volunteer for the organization.</p>
        <input type="submit" name="registration-form" value="Submit">
    </form>
    <?php if ($loggedIn): ?>
        <a class="button cancel" href="index.php" style="margin-top: .5rem">Cancel</a>
    <?php endif ?>
</main>