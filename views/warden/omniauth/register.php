<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Warden :: OmniAuth Registration</title>
        <style>
            body {
                background: #f5f5f5;
                font-family: "Helvetica Neue", "HelveticaNeue", Helvetica, Arial, "Lucida Grande", sans-serif;
                font-size: 13px;
                line-height: 18px;
                color: #555;
                position: relative;
                -webkit-font-smoothing: antialiased;
            }
            h1 {
                background: #181818;
                color: #FFF;
                line-height: 1.25;
                font-size: 35px;
                font-size: 3.5rem;
                font-weight: bold;
                margin: 0 0 12px;
                text-shadow: 0 2px 0 #B90B0B;
            }
            p { line-height: 24px; margin: 0 0 18px; }
            a { color: #2a85e8; text-decoration: none; outline: 0; line-height: inherit; }
            a:hover { color: #11639d; }
            small { font-size: 60%; line-height: inherit; }
            div.alert {
                position: relative;
                padding: 7px 15px;
                margin-bottom: 18px;
                color: #404040;
                background-color: #EEDC94;
                background-repeat: repeat-x;
                background-image: -khtml-gradient(linear, left top, left bottom, from(#fceec1), to(#eedc94));
                background-image: -moz-linear-gradient(top, #fceec1, #eedc94);
                background-image: -ms-linear-gradient(top, #fceec1, #eedc94);
                background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #fceec1), color-stop(100%, #eedc94));
                background-image: -webkit-linear-gradient(top, #fceec1, #eedc94);
                background-image: -o-linear-gradient(top, #fceec1, #eedc94);
                background-image: linear-gradient(top, #fceec1, #eedc94);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fceec1', endColorstr='#eedc94', GradientType=0);
                text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
                border-color: #EEDC94 #EEDC94 #E4C652;
                border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
                text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
                border-width: 1px;
                border-style: solid;
                -webkit-border-radius: 4px;
                -moz-border-radius: 4px;
                border-radius: 4px;
                -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25);
                -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25);
                text-align: center;
            }
            .alert>span {
                 display: block;
                 padding: 5px 10px 6px;
             }
            #wrapper {margin: 0 auto; width: 850px;}

            form {
                background: #fff;
                padding: 30px;
                margin: 20px auto;
                width: 400px;
                border-radius: 6px;
                -moz-border-radius: 6px;
                -webkit-border-radius: 6px;
                -webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
                -moz-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
            }
            form label { display: block; font-size: 13px; line-height: 18px; cursor: pointer; margin-bottom: 9px; }
            form .input-text {
                background: #fff;
                border: solid 1px #bbb; border-radius: 2px; -webkit-border-radius: 2px; -moz-border-radius: 2px;
                font-size: 13px; padding: 6px 3px 4px; outline: none !important;
                width: 334px;
            }
            form .input-text:focus { background-color: #f9f9f9; }
            div.error {
                background: #e91c21;
                border: solid 0px #e91c21;
                border-width: 0px 1px 1px 1px;
                color: #fff;
                font-size: 12px;
                font-weight: bold;
                margin: 0 0 15px;
                padding: 6px 4px;
                width: 334px;
                border-bottom-left-radius: 2px;
                border-bottom-right-radius: 2px;
                -webkit-border-bottom-left-radius: 2px;
                webkit-border-bottom-right-radius: 2px;
                -moz-border-radius-bottomleft: 2px;
                -moz-border-radius-bottomright: 2px;
            }
        </style>
    </head>
    <body>
        <div id="wrapper">
            <div class="alert">
                <span>This is an example View file for <strong>Warden\Controller_OmniAuth</strong>.</span>
                <span>It is recommended that you copy this file to your <strong>APPPATH</strong> and modify it to suit your needs.</span>
            </div>
            <?php echo Form::open(\Config::get('warden.omniauthable.urls.registration')); ?>

            <?php if (Session::get_flash('warden.omniauthable.error')): ?>
                <div class="error"><?php echo Session::get_flash('warden.omniauthable.error'); ?></div>
            <?php endif; ?>

            <?php if (Config::get('warden.profilable') === true): ?>
                <p>
                    <label for="full_name">Full Name</label>
                    <?php echo Form::input('full_name', $user->full_name, array('class' => 'input-text')); ?>
                </p>
            <?php endif; ?>

            <p>
                <label for="username">Username</label>
                <?php echo Form::input('username', $user->username, array('class' => 'input-text')); ?>
            </p>

            <p>
                <label for="email">Email</label>
                <?php echo Form::input('email', $user->email, array('class' => 'input-text')); ?>
            </p>

            <p>
                <label for="password">Password</label>
                <?php echo Form::password('password', null, array('class' => 'input-text')); ?>
            </p>

            <?php echo Form::submit('submit'); ?>

            <?php echo Form::close(); ?>
        </div>
    </body>
</html>