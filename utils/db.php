<?php

namespace mafiascum\restApi\utils;

class DbUtils {
    public static function array_to_quoted_string($arr) {
        return '\'' . join( '\', \'', $arr ) . '\'';
    }
}