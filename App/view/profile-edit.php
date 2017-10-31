<div class="tiny-box">
    <span class="user-err"><?php print_errors($err, null, null, null, array('name','email')); ?></span>
    <h1 style="margin-bottom:0">Edit Profile</h1>
    <p style="color:#ddd">Update your profile info below</p>

    <form method="post">
        <div class="form-group <?php echo $err->hasError('name') ? 'has-errors' : null; ?>">
            <label>
                <input size="35" type="text" name="name" value="<?php echo old('name'); ?>" class="<?php echo $err->hasError('name') ? 'error-src' : null; ?>" placeholder="Your Name" spellcheck="false" />
                <small style="display:block">Your name.</small>
            </label>

            <?php print_field_errors($err, 'name'); ?>
        </div>

        <div class="form-group <?php echo $err->hasError('email') ? 'has-errors' : null; ?>">
            <label>
                <input size="35" type="email" name="email" value="<?php echo old('email'); ?>" class="<?php echo $err->hasError('email') ? 'error-src' : null; ?>" placeholder="Email address" spellcheck="false" />
                <small style="display:block">Your email address.</small>
            </label>

            <?php print_field_errors($err, 'email'); ?>
        </div>

        <div class="form-group">
            <label>
                <input type="submit" value="Update Profile" />
            </label>
            <label>
                <a href="<?php echo App\Ctrl\Profile::url(); ?>" class="button secondary">cancel</a>
            </label>
            <input type="hidden" name="nonce" value="<?php echo nonce()->create('edit-profile'); ?>" />
        </div>
    </form>
</div>