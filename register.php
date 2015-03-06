<?php 


/* the purpose of this page is to display a form to allow a person to register 
 * the form will be sticky meaning if there is a mistake the data previously  
 * entered will be displayed again. Once a form is submitted (to this same page) 
 * we first sanitize our data by replacing html codes with the html character. 
 * then we check to see if the data is valid. if data is valid enter the data  
 * into the table and we send and dispplay a confirmation email message.  
 *  
 * if the data is incorrect we flag the errors. 
 *  
 * Written By: Robert Erickson robert.erickson@uvm.edu 
 * Last updated on: October 10, 2013 
 *  
 *  
  -- -------------------------------------------------------- 
  -- 
  -- Table structure for table `tblRegister` 
  -- 

  CREATE TABLE IF NOT EXISTS `tblRegister` ( 
  pkRegisterId int(11) NOT NULL AUTO_INCREMENT, 
  fldEmail varchar(65) DEFAULT NULL, 
  fldFirstName varchar (20) DEFAULT NULL,
  fldLastName varchar (20) DEFAULT NULL,
  fldDateJoined timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, 
  fldConfirmed tinyint(1) NOT NULL DEFAULT '0', 
  fldApproved tinyint(4) NOT NULL DEFAULT '0', 
  PRIMARY KEY (`pkRegisterId`) 
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; 

 * I am using a surrogate key for demonstration,  
 * email would make a good primary key as well which would prevent someone 
 * from entering an email address in more than one record. 
 */ 

//----------------------------------------------------------------------------- 
//  
// Initialize variables 
//   

$debug = false; 
if ($debug) print "<p>DEBUG MODE IS ON</p>"; 

$baseURL = "http://www.uvm.edu/"; 
$folderPath = "~dguild/cs148/assignment4.1/"; 
// full URL of this form 
$yourURL = $baseURL . $folderPath . "register.php"; 

require_once("connect.php"); 

//############################################################################# 
// set all form variables to their default value on the form. for testing i set 
// to my email address. you lose 10% on your grade if you forget to change it. 

//$email = "dguild@uvm.edu"; 
$email = "";
//$firstName = "Drew";
$firstName = "";
//$lastName = "Guild";
 $lastName = "";
 $gender = "male";



//############################################################################# 
//  
// flags for errors 

$emailERROR = false;
$firstNameERROR = false;
$lastNameERROR = false;


//############################################################################# 
//   
$mailed = false; 
$messageA = ""; 
$messageB = ""; 
$messageC = ""; 


//----------------------------------------------------------------------------- 
//  
// Checking to see if the form's been submitted. if not we just skip this whole  
// section and display the form 
//  
//############################################################################# 
// minor security check 

if (isset($_POST["btnSubmit"])) { 
    $fromPage = getenv("http_referer"); 

    if ($debug) {
        print "<p>From: " . $fromPage . " should match "; 
        print "<p>Your: " . $yourURL;
    } 

    if ($fromPage != $yourURL) { 
        die("<p>Sorry you cannot access this page. Security breach detected and reported.</p>"); 
    } 


//############################################################################# 
// replace any html or javascript code with html entities 
// 

    $email = htmlentities($_POST["txtEmail"], ENT_QUOTES, "UTF-8");
    $firstName = htmlentities($_POST["txtFirstName"], ENT_QUOTES, "UTF-8");
    $lastName = htmlentities($_POST["txtLastName"], ENT_QUOTES, "UTF-8");


//############################################################################# 
//  
// Check for mistakes using validation functions 
// 
// create array to hold mistakes 
//  

    include ("validation_functions.php"); 

    $errorMsg = array(); 


//############################################################################ 
//  
// Check each of the fields for errors then adding any mistakes to the array. 
// 
    //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^       Check email address 
    if (empty($email)) { 
        $errorMsg[] = "Please enter your Email Address"; 
        $emailERROR = true; 
    } else { 
        $valid = verifyEmail($email); /* test for non-valid  data */ 
        if (!$valid) { 
            $errorMsg[] = "I'm sorry, the email address you entered is not valid."; 
            $emailERROR = true; 
        } 
    } 
    
    if (empty($firstName)) { 
        $errorMsg[] = "Please enter your first name"; 
        $firstNameERROR = true; 
    } else { 
        $valid = verifyAlphaNum($firstName); /* test for non-valid  data */ 
        if (!$valid) { 
            $errorMsg[] = "I'm sorry, the first name you entered is not valid."; 
            $firstNameERROR = true; 
        } 
    }
    
    if (empty($lastName)) { 
        $errorMsg[] = "Please enter your last name"; 
        $lastNameERROR = true; 
    } else { 
        $valid = verifyAlphaNum($lastName); /* test for non-valid  data */ 
        if (!$valid) { 
            $errorMsg[] = "I'm sorry, the last name you entered is not valid."; 
            $lastNameERROR = true; 
        } 
    }


//############################################################################ 
//  
// Processing the Data of the form 
// 

    if (!$errorMsg) { 
        if ($debug) print "<p>Form is valid</p>"; 

//############################################################################ 
// 
// the form is valid so now save the information 
//     
        $primaryKey = ""; 
        $dataEntered = false; 
         
        try { 
            $db->beginTransaction(); 
            
            $sql = 'INSERT INTO tblRegister SET fldEmail="' . $email . '", fldFirstName="' .$firstName . '", fldLastName="' . $lastName . '", fldState="' . $_POST["state"] . '", fldGender="' . $_POST["radGender"] . '", fldVT="' . $_POST["chkVT"] . '", fldBTV="' . $_POST["chkBTV"] . '";'; 
            $stmt = $db->prepare($sql); 
            if ($debug) print "<p>sql ". $sql; 
        
            $stmt->execute(); 
             
            $primaryKey = $db->lastInsertId(); 
            if ($debug) print "<p>pk= " . $primaryKey; 

            // all sql statements are done so lets commit to our changes 
            $dataEntered = $db->commit(); 
            if ($debug) print "<p>transaction complete "; 
        } catch (PDOExecption $e) { 
            $db->rollback(); 
            if ($debug) print "Error!: " . $e->getMessage() . "</br>"; 
            $errorMsg[] = "There was a problem with accpeting your data please contact us directly."; 
        } 


        // If the transaction was successful, give success message 
        if ($dataEntered) { 
            if ($debug) print "<p>data entered now prepare keys "; 
            //################################################################# 
            // create a key value for confirmation 

            $sql = "SELECT fldDateJoined FROM tblRegister WHERE pkRegisterId=" . $primaryKey; 
            $stmt = $db->prepare($sql); 
            $stmt->execute(); 

            $result = $stmt->fetch(PDO::FETCH_ASSOC); 
             
            $dateSubmitted = $result["fldDateJoined"]; 

            $key1 = sha1($dateSubmitted); 
            $key2 = $primaryKey; 

            if ($debug){
		print "<p>key 1: " . $key1; 
              print "<p>key 2: " . $key2; 
	     }


            //################################################################# 
            // 
            //Put forms information into a variable to print on the screen 
            // 

            $messageA = '<h2>Thank you for registering.</h2>'; 

            $messageB = "<p>Click this link to confirm your registration: "; 
            $messageB .= '<a href="' . $baseURL . $folderPath  . 'confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . '">Confirm Registration</a></p>'; 
            $messageB .= "<p>or copy and paste this url into a web browser: "; 
            $messageB .= $baseURL . $folderPath  . 'confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . "</p>"; 

            $messageC .= "<p><b>Email Address:</b><i>   " . $email . "</i></p>"; 

            //############################################################## 
            // 
            // email the form's information 
            // 
             
            $subject = "CS 148 registration that i forgot to change text"; 
            include_once('mailMessage.php'); 
            $mailed = sendMail($email, $subject, $messageA . $messageB . $messageC); 
        } //data entered    
    } // no errors  
}// ends if form was submitted.  

    include ("top.php"); 

    $ext = pathinfo(basename($_SERVER['PHP_SELF'])); 
    $file_name = basename($_SERVER['PHP_SELF'], '.' . $ext['extension']); 

    print '<body id="' . $file_name . '">'; 

    include ("header.php"); 
    include ("nav.php"); 
    ?> 

    <section id="main"> 
        <h1>Register </h1> 

        <? 
//############################################################################ 
// 
//  In this block  display the information that was submitted and do not  
//  display the form. 
// 
        if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) { 
            print "<h2>Your Request has "; 

            if (!$mailed) { 
                echo "not "; 
            } 

            echo "been processed</h2>"; 

            print "<p>A copy of this message has "; 
            if (!$mailed) { 
                echo "not "; 
            } 
            print "been sent to: " . $email . "</p>"; 

            echo $messageA . $messageC; 
        } else { 

        
//############################################################################# 
// 
// Here we display any errors that were on the form 
// 

            print '<div id="errors">'; 

            if ($errorMsg) { 
                echo "<ol>\n"; 
                foreach ($errorMsg as $err) { 
                    echo "<li>" . $err . "</li>\n"; 
                } 
                echo "</ol>\n"; 
            } 

            print '</div>';
            
        }
            ?> 
            <!--   Take out enctype line    --> 
            <form action="<? print $_SERVER['PHP_SELF']; ?>" 
                  enctype="multipart/form-data" 
                  method="post" 
                  id="frmRegister"> 
                <fieldset class="contact"> 
                    <legend>Contact Information</legend> 

                    <label class="required" for="txtEmail">Email: </label> 

                    <input id ="txtEmail" name="txtEmail" class="element text medium<?php if ($emailERROR) echo ' mistake'; ?>" type="text" maxlength="255" value="<?php echo $email; ?>" placeholder="enter your preferred email address" onfocus="this.select();"  tabindex="30"/>
                    <br>
                    <label class="required" for="txtFirstName">First Name: </label>
                    
                    <input id="txtFirstName" name="txtFirstName" class="element text medium<?php if ($firstNameERROR) echo ' mistake'; ?>" type="text" maxlength="255" value="<?php echo $firstName; ?>" onfocus="this.select();" tabindex="40"/>
                    <br>
                    <label class="required" for="txtLastName">Last Name: </label>
                    
                    <input id="txtLastName" name="txtLastName" class="element text medium<?php if ($lastNameERROR) echo ' mistake'; ?>" type="text" maxlength="255" value="<?php echo $lastName; ?>" onfocus="this.select();" tabindex="40"/>
		      <br>
		      <label for="radGender">Gender: </label><br>

		      <input type="radio" id="radGender" name="radGender" <?php if($_POST["radGender"] == "male") echo 'checked="checked"';?> value="male">Male<br>
		      <input type="radio" id="radGender" name="radGender" <?php if($_POST["radGender"] == "female") echo 'checked="checked"';?> value="female">Female<br>

		      <label for="state">Home State:</label>
		      <select id="state" name="state" size="1">
				<option value="VT">Vermont</option>
  				<option value="AL">Alabama</option>
  				<option value="AK">Alaska</option>
 				<option value="AZ">Arizona</option>
  				<option value="AR">Arkansas</option>
  				<option value="CA">California</option>
  				<option value="CO">Colorado</option>
  				<option value="CT">Connecticut</option>
  				<option value="DE">Delaware</option>
  				<option value="DC">Dist of Columbia</option>
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
  				<option value="VA">Virginia</option>
  				<option value="WA">Washington</option>
  				<option value="WV">West Virginia</option>
  				<option value="WI">Wisconsin</option>
  				<option value="WY">Wyoming</option>
			</select>
			</br>
			<label>Select all that apply:</label><br>
			<input type="checkbox" id="chkVT" name="chkVT" <?php if($_POST["chkVT"] == "VT") echo 'checked="checked"'; ?> value="VT">I live in Vermont<br>
			<input type="checkbox" id="chkBTV" name="chkBTV" <?php if($_POST["chkBTV"] == "BTV") echo 'checked="checked"'; ?> value="BTV">I live in Burlington, VT<br>
                </fieldset>  


                <fieldset class="buttons"> 
                    <input type="submit" id="btnSubmit" name="btnSubmit" value="Register" tabindex="991" class="button"> 
                    <input type="reset" id="butReset" name="butReset" value="Reset Form" tabindex="993" class="button" onclick="reSetForm();" > 
                </fieldset>                     

            </form> 
            <?php 
         // end body submit 
        if ($debug){ 
            print "<p>END OF PROCESSING</p>"; 
        }
        ?> 
    </section> 


    <?php 
    include ("footer.php"); 
    ?> 

</body> 
</html>