<?php parse_redirect_data(); ?>

<div class="tiny-box">
    <span class="user-err"><?php print_errors($err, null, null, null, array('email','name','pass','pass_conf')); ?></span>
    <h1 style="margin-bottom:0">Register</h1>
    <p style="color:#ddd">Create a new account</p>

    <form method="post">
        <div class="form-group <?php echo $err->hasError('name') ? 'has-errors' : null; ?>">
            <label>
                <input size="35" type="text" name="name" value="<?php echo old('name'); ?>" class="<?php echo $err->hasError('name') ? 'error-src' : null; ?>" placeholder="Your Name" spellcheck="false" />
            </label>

            <?php print_field_errors($err, 'name'); ?>
        </div>

        <div class="form-group <?php echo $err->hasError('email') ? 'has-errors' : null; ?>">
            <label>
                <input size="35" type="email" name="email" value="<?php echo old('email'); ?>" class="<?php echo $err->hasError('email') ? 'error-src' : null; ?>" placeholder="Email Address" spellcheck="false" />
            </label>

            <?php print_field_errors($err, 'email'); ?>
        </div>

        <div class="form-group <?php echo $err->hasError('pass') ? 'has-errors' : null; ?>">
            <label>
                <input size="35" type="password" name="pass" class="<?php echo $err->hasError('pass') ? 'error-src' : null; ?>" placeholder="Choose a Password" />
            </label>

            <?php print_field_errors($err, 'pass'); ?>
        </div>

        <div class="form-group <?php echo $err->hasError('pass_conf') ? 'has-errors' : null; ?>">
            <label>
                <input size="35" type="password" name="pass_conf" class="<?php echo $err->hasError('pass_conf') ? 'error-src' : null; ?>" placeholder="Confirm your Password" />
            </label>

            <?php print_field_errors($err, 'pass_conf'); ?>
        </div>

        <div class="form-group">
            <label>
                <input type="submit" value="Sign Up" />
            </label>
            <input type="hidden" name="nonce" value="<?php echo nonce()->create('register'); ?>" />
        </div>
    </form>

    <p style="color:#ddd">Have an account? Click <a href="<?php echo $login; ?>">here</a> to login.</p>
</div>