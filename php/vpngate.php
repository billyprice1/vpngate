#!/usr/bin/php
<?php
/**
 * @filename    vpngate.php
 * @description A set of handy tools to handle VPNGate entries
 * @author      Gordon Yeu (Kuo-Cheng Yeu, aka kmd)
 * @webpage     http://mikuru.tw
 * @revision    $Format:%ci$ ($Format:%h$)
 */

require 'vpngate.inc';

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
