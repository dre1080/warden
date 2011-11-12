<p style="font-size: 18px;">Hi <?php echo $username;?>!</p>

<p>You can confirm your account through the link below:</p>

<p>
    <a href="<?php echo rtrim(\Config::get('warden.confirmable.url'), '/').'/'.$confirmation_token ?>">
        Confirm my account
    </a>
</p>