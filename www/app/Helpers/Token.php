<?php

/**
 * @package KN
 * @subpackage KN Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Token {

    public static function create($id) {

        $token = [
            'timeout'       => strtotime('+1 hour'),
            'header'        => Base::getHeader(),
            'ip'            => Base::getIp(),
            'id'            => (int)$id
        ];

        return Base::encryptKey(json_encode($token));

    }

    public static function verify($token) {

        $return = 0;
        $token = @json_decode(Base::decryptKey($token), true);
        if (is_array($token)) {

            if (
                (isset($token['timeout']) !== false AND $token['timeout'] >= time()) AND
                (isset($token['header']) !== false AND $token['header'] == Base::getHeader()) AND
                (isset($token['ip']) !== false AND $token['ip'] == Base::getIp()) AND
                (isset($token['id']) !== false AND $token['id'])
            ) {
                $return = $token['id'];
            }

        }

        return $return;
    }

}