<?php
// Helper function to configure a logger without having to duplicate so much content...
// See LOG4PHP-221: https://issues.apache.org/jira/browse/LOG4PHP-221
//   Unless you provide appenders on a logger, they will not override the rootLogger config
function configLogger($level, $appenders = array('logFileAppender')){
	return array(
		'level' => $level,
		'appenders' => $appenders,
		'additivity' => false
	);
}

//This file simply returns the configuration array to pass to Logger::configure()
return array(
	//Root logger
	'rootLogger' => configLogger('INFO'),

	//Other loggers
	'loggers' => array(
        'ActionManager' => configLogger('TRACE'),
	),

	//Appenders
	'appenders' => array(
		'logFileAppender' => array(
			'class' => 'LoggerAppenderRollingFile',
			'layout' => array(
				'class' => 'LoggerLayoutPattern',
				'params' => array(
					'conversionPattern' => '%date{Y-m-d H:i:s} [%s{UNIQUE_ID}] [%5p] [%20logger] [%.-20F:%4L] %message%newline'
				)
			),
			'params' => array(
				'file' => constant('RSMS_LOGS') . '/tests.log',
				'append' => false
			)
		)
	)
);
?>