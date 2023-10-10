<?php

class app_controller_index extends frame_controller
{
    /**
     * 展示主页
     */
    public function actionIndex()
    {
        lib_log::access("index", json_encode($_SERVER));
        Eason::displayPage('hello_world', app_service_default::default());
    }
}