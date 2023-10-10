<?php

class app_service_default extends frame_controller
{
    static function default():array {
        return [
            'word' => '你好世界',
            'time' => time(),
        ];
    }
}