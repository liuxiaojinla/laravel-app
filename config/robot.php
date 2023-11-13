<?php
// +----------------------------------------------------------------------
// | 机器人设置
// +----------------------------------------------------------------------

return [
	// 定义相关默认配置
	'defaults' => [
		'robot' => env('robot.driver', 'default'),
	],

	// 定义机器人的相关配置
	'robots' => [
		'default' => [
			'driver' => 'wework',
			'key' => env('ROBOT.WEWORK_KEY', ''),
		],

		//
		'danger' => [
			'driver' => 'dingtalk',
			'key' => env('ROBOT.DINGTALK_KEY', ''),
			'secret' => env('ROBOT.DINGTALK_SECRET', ''),
			'title' => env('ROBOT.DINGTALK_TITLE', '提醒助手'),
		],
	],
];
