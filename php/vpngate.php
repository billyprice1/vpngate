#!/usr/bin/php
<?php
define('DEBUG', TRUE);
define('CSV_FILE', 'vpngate.csv');

if (DEBUG)
{
    define('CSV_URL', 'http://127.0.0.1/~kmd/vpngate.csv');
}
else
{
    // Original URL got GFW'd, use mirror site.
    //define('CSV_URL', 'http://www.vpngate.net/api/iphone/');
    define('CSV_URL', 'http://mikuru.tw/~kmd/vpngate/vpngate.csv');
}

// DO NOT change it, explicit assign value by key for better readability
$key_list = array(
    0   => 'HostName',
    1   => 'IP',
    2   => 'Score',
    3   => 'Ping',
    4   => 'Speed',
    5   => 'CountryLong',
    6   => 'CountryShort',
    7   => 'NumVpnSessions',
    8   => 'Uptime',
    9   => 'TotalUsers',
    10  => 'TotalTraffic',
    11  => 'LogType',
    12  => 'Operator',
    13  => 'Message',
    14  => 'OpenVPN_ConfigData_Base64',
);

// Item to be shown
$option_list = array(1, 6, 7);


function get_file(&$raw)
{
    echo "Getting VPN list...\n";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, CSV_URL);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $raw = curl_exec($ch);
    curl_close($ch);
}

function generate_list(&$list, &$raw)
{
    $lines = explode("\n", $raw);

    array_shift($lines);
    array_shift($lines);

    foreach ($lines as $key => $line)
    {
        $list[$key] = explode(',', $line);
    }
}

function show_list(&$list, &$key_list, &$option_list)
{
    foreach ($option_list as $option)
    {
        echo "\t\t" . $key_list[$option];
    }
    echo "\n";

    foreach ($list as $index => $site)
    {
        if (count($site) != 15) continue;

        echo $index;
        foreach ($option_list as $key)
        {
            echo "\t\t" . $site[$key];
        }
        echo "\n";
    }
    echo "\n";
}

function generate_config($filename, $content)
{
    if ( ! mkdir($filename . '.tblk'))
    {
        echo "Cannot create directory\n";
        return false;
    }

    if (file_put_contents($filename . '.tblk/' . $filename . '.ovpn', $content) === FALSE)
    {
        echo "Cannot create file\n"; 
        return false;
    }
}

function usage()
{
    echo "Usage:\n";
    echo "\t" . basename(__FILE__) . " [-a | -l]\n";
    echo "\t" . basename(__FILE__) . " [-i index]\n";
    echo "\n";
    echo "Options:\n";
    echo "\t-a\t" . "Generate all VPN config files\n";
    echo "\t-l\t" . "List all VPN entries\n";
    echo "\t-i\t" . "Generate VPN config file by index\n";
}

// Starts here
if ($argc < 2)
{
    usage();
    exit;
}

$raw;
get_file($raw);

$list;
generate_list($list, $raw);

switch($argv[1])
{
    case '-a':
        foreach ($list as $index => $site)
        {
            echo "Generating VPN config $index...\n";
            if (count($site) != 15)
            {
                continue;
            }
            $filename = $site[1] . '_' . $site[6];
            $output =  base64_decode($site[14]);

            generate_config($filename, $output);
        }
        break;
    case '-i':
        if (isset($argv[2]))
        {
            $index = intval($argv[2]);
            echo "Generating VPN config $index...\n";

            $filename = $list[$index][1] . '_' . $list[$index][6];
            $content =  base64_decode($list[$index][14]);

            if (generate_config($filename, $content) !== FALSE)
            {
                echo "Done\n";
            }
        }
        break;
    case '-l':
        show_list($list, $key_list, $option_list);
        break;
    default:
        usage();
        break;
}
?>
