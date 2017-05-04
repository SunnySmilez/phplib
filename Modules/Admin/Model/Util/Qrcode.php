<?php
namespace Modules\Admin\Model\Util;


class Qrcode {

    public static function getBase64($data) {
        ob_start();
        \QRcode::png($data);
        $content = ob_get_clean();
        ob_end_clean();
        
        return 'data:image/png;base64,' . base64_encode($content);
    }

}