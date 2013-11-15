<?php
//This file simply returns the configuration array to pass to Logger::configure()
return array(
	//Root logger
	'rootLogger' => array(
		'appenders' => array(
			'logFileAppender',
			'htmlFileAppender',
		),
		'level' => 'INFO'
	),
	//Other loggers
	'loggers' => array(
		'Autoloader' => array(
			'appenders' => array(
				'logFileAppender',
				'htmlFileAppender',
			),
			'level' => 'INFO'
		),
		
		'DtoManager' => array(
			'appenders' => array(
				'logFileAppender',
				'htmlFileAppender',
			),
			'level' => 'TRACE'
		),
		
		'ValidationManager' => array(
			'appenders' => array(
				'logFileAppender',
				'htmlFileAppender',
			),
			'level' => 'TRACE'
		),
		
		'JsonManager' => array(
			'appenders' => array(
				'logFileAppender',
				'htmlFileAppender',
			),
			'level' => 'TRACE'
		),
		
		'ActionDispatcher' => array(
			'appenders' => array(
				'logFileAppender',
				'htmlFileAppender',
			),
			'level' => 'TRACE'
		),
	),
	
	//Appenders
	'appenders' => array(
		'logFileAppender' => array(
			'class' => 'LoggerAppenderRollingFile',
			'layout' => array(
				'class' => 'LoggerLayoutPattern',
				'params' => array(
					'conversionPattern' => '%date{Y-m-d H:i:s} [%5p] [%20logger] [%.-20F:%4L] %message%newline'
				)
			),
			'params' => array(
				'file' => DIR_PATH . '/../logs/erasmus.log',
				'append' => true
			)
		),
		
		'htmlFileAppender' => array(
			'class' => 'LoggerAppenderRollingFile',
			'layout' => array(
					'class' => 'LoggerLayoutHtml',
					'params' => array(
						'locationinfo' => 'true',
						'title' => 'RSMS Log Messages'
					)
			),
			'params' => array(
					'file' => DIR_PATH . '/../logs/erasmus-log.html',
					'append' => false
			)
		),
	)
);
?>