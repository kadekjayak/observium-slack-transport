#!/usr/bin/env php
<?php
/**
 * Slack External Program transport for Observium
 * Some code on this file is taken from the original Source
 */

/**
 * CONFIGURATION
 * 
 * Slack Endpoit
 * Change it as you want
 */
$endpoint = [
    'username' => 'Observium',
    'channel' => '#site_monitor',
    'url' => 'https://hooks.slack.com/services/change',
];



// Title
$title = getenv('OBSERVIUM_TITLE');

// Environment Variables
$env_keys = [
    'ALERT_STATE',
    'ALERT_URL',
    'DEVICE_HOSTNAME',
    'ENTITY_TYPE',
    'DEVICE_OS',
    'ENTITY_NAME',
    'ENTITY_DESCRIPTION',
    'DURATION',
    'ALERT_MESSAGE',
    'METRICS'
];
$message_tags = [];
foreach( $env_keys as $key ) {
    $message_tags[ $key ] = getenv( 'OBSERVIUM_' . $key ); // Get from Environment variables
}

// Data Payload
$data = [
    "username" => $endpoint['username'], 
    "channel" => $endpoint['channel']
];

// Color
$color = ( $message_tags['ALERT_STATE'] == "RECOVER" ? "good" : "danger" );

// Fields
$fields = [
    [
        'title' => 'Device', 
        'value' => $message_tags['DEVICE_HOSTNAME'] . " (".$message_tags['DEVICE_OS'].")", 
        'short' => TRUE
    ],
    [
        'title' => 'Entity', 
        'value' => $message_tags['ENTITY_TYPE'] . " / " . $message_tags['ENTITY_NAME'] . ( isset( $message_tags['ENTITY_DESCRIPTION'] ) ? ' '.$message_tags['ENTITY_DESCRIPTION'] : '' ), 
        'short' => TRUE
    ],
    [
        'title' => 'Alert Message/Duration', 
        'value'  => $message_tags['ALERT_MESSAGE'] . PHP_EOL . $message_tags['DURATION'], 
        'short' => TRUE
    ],
    [
        'title' => 'Metrics',  
        'value' => str_replace("             ", "", $message_tags['METRICS']), 
        'short' => TRUE
    ]
];

// Attachment
$data['attachments'][] = [
                        'fallback' => $title,
                        'title' => $title,
                        'title_link' => $message_tags['ALERT_URL'],
                        'color' => $color,
                        'fields' => $fields,
                    ];

/**
 * Post to Slack
 */
$ch = curl_init( $endpoint['url'] );
$payload = json_encode( $data );

curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json') );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

$result = curl_exec($ch);
echo $result;
curl_close($ch);
