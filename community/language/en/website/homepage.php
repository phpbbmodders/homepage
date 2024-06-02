<?php
/**
 * Language file for the phpBBModders homepage
 * @copyright (c) 2024 phpBBModders (http:/phpbbmodders.com)
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = [];
}

// Define language variables
$lang = array_merge($lang, array(
    'L_LATEST_POSTS_HOMEPAGE' => 'Latest Posts',
    'L_READ_TOPIC' => 'Read Topic',
    'L_COMMENT' => 'Comment',
    'L_COMMENTS' => 'Comments',
    'L_COMMENT_TOPIC' => 'Comment on Topic',
    'L_ANNOUNCEMENTS' => 'Announcements',
));

?>
