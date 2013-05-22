<?php
//This file simply returns the configuration array to pass to Logger::configure()
return array(
	//Root logger
	'rootLogger' => array(
		'appenders' => array(
			'fileAppender',
		),
		'level' => 'DEBUG'
	),
	//Other loggers
	'loggers' => array(
		'Test' => array(
			'appenders' => array(
				'fileAppender'
			),
			'level' => 'DEBUG'
		),
	),
	
	//Appenders
	'appenders' => array(
		'fileAppender' => array(
			'class' => 'LoggerAppenderRollingFile',
			'layout' => array(
				'class' => 'LoggerLayoutPattern',
				'params' => array(
					'conversionPattern' => '%date{Y-m-d G:i:s} [%5p] [%15logger] [%.-20F:%4L] %message%newline'
				)
			),
			'params' => array(
				'file' => '/logs/erasmus.log',
				'append' => true
			)
		),
	)
);
?>