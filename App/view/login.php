<?php parse_redirect_data(); ?>

<div class="tiny-box">
    <span class="user-err"><?php print_errors($err, null, null, null, array('email','pass','remember')); ?></span>
    <h1 style="margin-bottom:0">Login</h1>
    <p style="color:#ddd">Login to your account below</p>

    <form method="post">
        <div class="form-group <?php echo $err->hasError('email') ? 'has-errors' : null; ?>">
            <label>
                <input size="35" type="email" name="email" value="<?php echo old('email'); ?>" class="<?php echo $err->hasError('email') ? 'error-src' : null; ?>" placeholder="Email address" spellcheck="false" />
            </label>

            <?php print_field_errors($err, 'email'); ?>
        </div>

        <div class="form-group <?php echo $err->hasError('pass') ? 'has-errors' : null; ?>">
            <label>
                <input size="35" type="password" name="pass" class="<?php echo $err->hasError('pass') ? 'error-src' : null; ?>" placeholder="Password" />
            </label>

            <?php print_field_errors($err, 'pass'); ?>
        </div>

        <div class="form-group">
            <label>
                <input type="submit" value="Login" />
            </label>

            <label>
                <input type="checkbox" name="remember" <?php echo old('remember') ? 'checked="checked"' : null; ?> />
                <span>Remember Me</span>
            </label>
            <input type="hidden" name="nonce" value="<?php echo nonce()->create('login'); ?>" />
        </div>
    </form>

    <p style="color:#ddd">Don't have an account? Click <a href="<?php echo $register; ?>">here</a> to register.</p>
</div>