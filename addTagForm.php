
<h1>Add a New Tag</h1>
<main class="general">
    <form class="addfb-form" method="post">
        <h2>New Tag</h2>
        <p>An asterisk (<label><em>*</em></label>) indicates a required field.</p>
        <fieldset>
            <legend>Add New Tag</legend>
            <label for="tag"><em>* </em>Input Tag:</label>
            <input type="text" id="tag" name="tag" required placeholder="Enter new tag">
        </fieldset>
        
        <!-- if tag already exists in dbTags, don't add it -->
        <?php
            if(isset($_POST['tag'])){
                foreach ($_POST['tag'] as $selected) {
                    $sq="SELECT dbTags WHERE tagText=$selected";
                    if(!$sq){
                        echo "adding new tag";
                        mysqli_query($con, "INSERT INTO dbTags(tagText) VALUES ('$selected')");
                    }else{
                        echo "this tag already exists";
                    }
                }
            }
        ?>

        <p>Press submit to add tag.</p>
        <input type="submit" name="addtag-form" value="Submit">
    </form>
        <a class="button cancel" href="index.php" style="margin-top: .5rem">Cancel</a>
    
</main>