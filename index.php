<?php
/**
 * index.php
 *
 * @description Script to fetch and display announcements and the latest topics excluding specified forums from a phpBB forum.
 * @copyright (c) 2024 phpBBModders (http:/phpbbmodders.com)
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './community/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('');

// Include language file for the homepage
$user->add_lang('/website/homepage');

// Ensure the database connection is using the phpBB environment
global $db, $phpbb_container, $config;

// Base URL for constructing links
$base_url = generate_board_url() . '/';

// Fetch announcements from forum ID 2
$announcements_forum_id = 2;
$announcements_limit = 10;

$announcements_ary = [
    'SELECT'    => 'p.*, t.*, u.username, u.user_colour',
    'FROM'      => [POSTS_TABLE => 'p'],
    'LEFT_JOIN' => [
        ['FROM' => [TOPICS_TABLE => 't'], 'ON' => 't.topic_first_post_id = p.post_id'],
        ['FROM' => [USERS_TABLE => 'u'], 'ON' => 'p.poster_id = u.user_id'],
    ],
    'WHERE'     => 't.forum_id = ' . $announcements_forum_id . ' AND t.topic_status <> ' . ITEM_MOVED . ' AND t.topic_visibility = ' . ITEM_APPROVED,
    'ORDER_BY'  => 'p.post_time DESC',
];

$announcements = $db->sql_build_query('SELECT', $announcements_ary);
$announcements_result = $db->sql_query_limit($announcements, $announcements_limit);

while ($row = $db->sql_fetchrow($announcements_result)) {
    $post_text = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], isset($row['bbcode_options']) ? $row['bbcode_options'] : ''); // Check if 'bbcode_options' key exists
    $template->assign_block_vars('announcements', [
        'TOPIC_TITLE'  => htmlspecialchars($row['topic_title']),
        'TOPIC_LINK'   => append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . htmlspecialchars($row['forum_id']) . '&t=' . htmlspecialchars($row['topic_id']) . '&p=' . htmlspecialchars($row['post_id']) . '#p' . htmlspecialchars($row['post_id'])),
        'TOPIC_AUTHOR' => get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour']),
        'TOPIC_DATE'   => $user->format_date($row['post_time']),
        'POST_TEXT'    => $post_text,
        'COMMENTS'     => $phpbb_container->get('content.visibility')->get_count('topic_posts', $row, $row['forum_id']) - 1,
        'U_REPLY'      => append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=reply&f=' . htmlspecialchars($row['forum_id']) . '&t=' . htmlspecialchars($row['topic_id'])),
    ]);
}

$db->sql_freeresult($announcements_result);

// Fetch latest topics excluding specified forums, only first post of each topic
$excluded_forums = [2, 3, 4, 5, 6, 9, 10, 13, 14];
$latest_topics_limit = 10;

$latest_topics_ary = [
    'SELECT'    => 'p.*, t.*, u.username, u.user_colour',
    'FROM'      => [POSTS_TABLE => 'p'],
    'LEFT_JOIN' => [
        ['FROM' => [TOPICS_TABLE => 't'], 'ON' => 't.topic_first_post_id = p.post_id'],
        ['FROM' => [USERS_TABLE => 'u'], 'ON' => 'p.poster_id = u.user_id'],
    ],
    'WHERE'     => $db->sql_in_set('t.forum_id', $excluded_forums, true) . ' AND t.topic_status <> ' . ITEM_MOVED . ' AND t.topic_visibility = ' . ITEM_APPROVED . ' AND p.post_id = t.topic_first_post_id',
    'ORDER_BY'  => 'p.post_time DESC',
];

$latest_topics = $db->sql_build_query('SELECT', $latest_topics_ary);
$latest_topics_result = $db->sql_query_limit($latest_topics, $latest_topics_limit);

while ($row = $db->sql_fetchrow($latest_topics_result)) {
    $template->assign_block_vars('latest_posts', [
        'TOPIC_TITLE'     => htmlspecialchars($row['topic_title']),
        'U_VIEW_TOPIC'    => append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . htmlspecialchars($row['forum_id']) . '&t=' . htmlspecialchars($row['topic_id'])),
        'U_VIEW_POST'     => append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . htmlspecialchars($row['forum_id']) . '&t=' . htmlspecialchars($row['topic_id']) . '&p=' . htmlspecialchars($row['post_id']) . '#p' . htmlspecialchars($row['post_id'])),
        'POST_AUTHOR_FULL'=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour']),
    ]);
}

$db->sql_freeresult($latest_topics_result);

page_header('Home');
$template->assign_var('L_LATEST_POSTS_HOMEPAGE', $user->lang['L_LATEST_POSTS_HOMEPAGE']); // Assign the language constant to the template
$template->assign_vars(array(
    'L_READ_TOPIC' => $user->lang['L_READ_TOPIC'], // Assign the language constant for reading a topic
    'L_COMMENT' => $user->lang['L_COMMENT'], // Assign the language constant for a comment
    'L_COMMENTS' => $user->lang['L_COMMENTS'], // Assign the language constant for comments
    'L_COMMENT_TOPIC' => $user->lang['L_COMMENT_TOPIC'], // Text for commenting on a topic
    'L_ANNOUNCEMENTS' => $user->lang['L_ANNOUNCEMENTS'], // Text for announcements section
));
$template->set_filenames(array(
    'body' => 'website/home_index.html',
));
page_footer();
?>
