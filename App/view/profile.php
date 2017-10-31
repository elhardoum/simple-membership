<div class="tiny-box">
    <span class="user-err"><?php print_errors($err); ?></span>
    <h1 style="margin-bottom:0">Hello, <?php echo $user->name; ?>!</h1>

    <ul>
        <li><a href="<?php echo App\Ctrl\ProfileEdit::url(); ?>">Edit Profile</a></li>
        <li><a href="<?php echo App\Ctrl\ProfileEditPassword::url(); ?>">Edit Password</a></li>
        <li>Not you? <a href="<?php echo App\Ctrl\Logout::url( '?nonce=' . nonce()->create('logout') ); ?>">Logout</a></li>
    </ul>
</div>