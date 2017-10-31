<?php parse_redirect_data(); ?>

<div class="tiny-box">
    <span class="user-err"><?php print_errors($err, null, null, null, array('old_pass','pass','pass_conf')); ?></span>
    <h1 style="margin-bottom:0">Edit your Password</h1>
    <p style="color:#ddd">Update your password below</p>

    <form method="post">
        <div class="form-group <?php echo $err->hasError('old_pass') ? 'has-errors' : null; ?>">
            <label>
                <input size="35" type="password" name="old_pass" class="<?php echo $err->hasError('old_pass') ? 'error-src' : null; ?>" placeholder="Your Old Password" />
            </label>

            <?php print_field_errors($err, 'old_pass'); ?>
        </div>

        <div class="form-group <?php echo $err->hasError('pass') ? 'has-errors' : null; ?>">
            <label>
                <input size="35" type="password" name="pass" class="<?php echo $err->hasError('pass') ? 'error-src' : null; ?>" placeholder="Enter a New Password" />
            </label>

            <?php print_field_errors($err, 'pass'); ?>
        </div>

        <div class="form-group <?php echo $err->hasError('pass_conf') ? 'has-errors' : null; ?>">
            <label>
                <input size="35" type="password" name="pass_conf" class="<?php echo $err->hasError('pass_conf') ? 'error-src' : null; ?>" placeholder="Confirm New Password" />
            </label>

            <?php print_field_errors($err, 'pass_conf'); ?>
        </div>

        <div class="form-group">
            <label>
                <input type="submit" value="Update Password" />
            </label>
            <label>
                <a href="<?php echo url('/profile'); ?>" class="button secondary">cancel</a>
            </label>
            <input type="hidden" name="nonce" value="<?php echo nonce()->create('edit-password'); ?>" />
        </div>
    </form>
</div>