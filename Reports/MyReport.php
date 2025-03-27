<?php
namespace App\Reports;
require_once "/srv/www/htdocs/core/autoload.php";
class MyReport extends \koolreport\KoolReport
{
    use \koolreport\laravel\Friendship;
    // By adding above statement, you have claim the friendship between two frameworks
    // As a result, this report will be able to accessed all databases of Laravel
    // There are no need to define the settings() function anymore
    // while you can do so if you have other datasources rather than those
    // defined in Laravel.
    

    function setup()
    {
        // Let say, you have "sale_database" is defined in Laravel's database settings.
        // Now you can use that database without any futher setitngs.
        $this->src("spot_hrm")
        ->query("SELECT * FROM agent_cards_sales_mid_point")
        ->pipe($this->dataStore("agent_cards_sales_mid_point"));        
    }
}