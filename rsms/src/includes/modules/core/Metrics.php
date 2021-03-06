<?php

class Metrics {
    private static $_METRICS = array();
    public static function start(string $desc){
        $start = microtime(true);
        self::$_METRICS[$desc] = $start;

        return $desc;
    }

    public static function stop($desc){
        $start = self::$_METRICS[$desc];
        $t = round(microtime(true) - $start, 4);

        // reformat time to 4 decimals
        $t = sprintf("%0.4f", $t);

        // Log the details, padding the formatted number
        self::log($desc, str_pad($t, 9, ' ', STR_PAD_LEFT) . 's');
    }

    public static function log($desc, $metric){
        Logger::getLogger('Metrics')->info("[$metric] $desc");
    }
}
?>