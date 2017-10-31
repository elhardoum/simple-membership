<?php parse_redirect_data(); ?>

<div class="tiny-box">
    <span class="user-err"><?php print_errors($err); ?></span>
    <h1 style="margin-bottom:0">Hello, <?php echo $user->name; ?>!</h1>

    <ul>
        <li><a href="<?php echo url('/profile/edit'); ?>" dclass="button">Edit Profile</a></li>
        <li><a href="<?php echo url('/profile/edit/password'); ?>" dclass="button">Edit Password</a></li>
        <li>Not you? <a dclass="button" href="<?php echo "{$logout}?nonce=" . nonce()->create('logout'); ?>">Logout</a></li>
    </ul>

</div>