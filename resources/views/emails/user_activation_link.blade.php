<?php
    //pr($user);
?>
Please <a href="{{ url('/') }}/api/v1/user/activation/{{ $user['activation_code'] }}">Click Here</a> to verify your email.