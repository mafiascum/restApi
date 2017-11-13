<?php

namespace mafiascum\restApi\controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class topicsV1
{
    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\request\request */
    protected $request;

    /* @var \phpbb\db\driver\driver */
	protected $db;

    /* @var \phpbb\user */
    protected $user;

    /* @var \phpbb\user_loader */
    protected $user_loader;

    /* @var \phpbb\auth\auth */
    protected $auth;

    /* phpbb\language\language */
    protected $language;

    public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db,  \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\auth\auth $auth, $table_prefix)
    {
        $this->helper = $helper;
        $this->template = $template;
        $this->request = $request;
        $this->db = $db;
        $this->user = $user;
        $this->user_loader = $user_loader;
        $this->language = $language;
        $this->auth = $auth;
        $this->table_prefix = $table_prefix;
    }

    public function posts($topic_id)
    {
        $sql = "SELECT topic_id, forum_id FROM " . $this->table_prefix . "topics
                WHERE topic_id = " . $topic_id;
        $result = $this->db->sql_query($sql);
        while ($row = $this->db->sql_fetchrow($result)) {
            $forum_id = $row['forum_id'];
        }

        if ($forum_id == null || !$this->auth->acl_get('f_read', $forum_id)) {
            return  new JsonResponse(array("reason" => "Topic Id " . $topic_id . " does not exist."), Response::HTTP_NOT_FOUND);
        }

        $author_id = $this->request->variable('author_id', 0);
        $author = $this->db->sql_escape($this->request->variable('author', ''));
        $limit = $this->request->variable('limit', 50);
        $offset = $this->request->variable('offset', 0);
        
        $sql = "SELECT post_id, topic_id, poster_id, forum_id, post_text,
                bbcode_uid, bbcode_bitfield
                FROM " . $this->table_prefix . "posts
                WHERE topic_id = " . $topic_id;

        if ($author_id > 0) {
            $sql = $sql . " AND poster_id = " . $author_id;
        }
        if ($author) {
            $sql = $sql . " AND post_username LIKE '%" . $author . "%'"; 
        }
        $sql = $sql . " ORDER BY post_time LIMIT " . $limit . " OFFSET " . $offset;
        
        $result = $this->db->sql_query($sql);

        $response = array();
        while ($row = $this->db->sql_fetchrow($result)) {
            $parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
            $postObj = array(
                'post_id' => $row['post_id'],
                'topic_id' => $row['topic_id'],
                'poster_id' => $row['poster_id'],
                'forum_id' => $row['forum_id'],
                //'post_text' => generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, true),
                'post_text' => $row['post_text'],
            );
            $response[] = $postObj;
        }

        return new JsonResponse($response);
    }
}


                
