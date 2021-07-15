<?php

defined( 'ABSPATH' ) ?: exit;

class NyxitSeoTests
{
    public static function run()
    {
        echo "<p><b>Testing nyxitSeoHelper class...</b></p>";

        $id = nyxitSeoHelper::get_cur_view_id();
        echo "nyxitSeoHelper::get_cur_view_id() => $id";
    
        echo "<p><b>Testing complete.</b></p>";
    }
}

?>
