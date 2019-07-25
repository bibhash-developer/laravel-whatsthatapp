<?php

/*
 * Developer: Ramayan prasad
 * Date: 29-Oct-2018
 * Description: Notifications helper functions
 */
function getNotification() {
   $userId = session('user_data')['id'];
   $userType = session('user_data')['user_type_id'];
   $data = DB::table('notifications')->where(['user_id' => $userId, 'user_type' => $userType, 'is_read' => 0])->get();
   //pr($data);die;
   return $data;
}