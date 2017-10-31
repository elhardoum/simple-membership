<div class="tiny-box">
    <span class="user-err"><?php print_errors($err); ?></span>
    <h1 style="margin-bottom:0">Hello There!</h1>

    <?php if ( $loggedIn ) : ?>
        <p style="color:#ddd">Welcome! Click <a href="<?php echo App\Ctrl\Profile::url(); ?>">here</a> to view your profile.</p>
    <?php else : ?>
        <p style="color:#ddd">Welcome! You can <a href="<?php echo App\Ctrl\Login::url(); ?>">login</a> or <a href="<?php echo App\Ctrl\Register::url(); ?>">register</a>.</p>
    <?php endif; ?>
</div>