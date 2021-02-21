<?php

class Post
{

    public static function createPost($postBody, $loggedInUserId, $profileUserId)
    {

        if (strlen($postBody) < 1 || strlen($postBody) > 257) {
            die('Incorrect length! (createPost)');
        }

        if ($loggedInUserId == $profileUserId) {
            DB::query('INSERT INTO posts VALUES (id, :postbody, NOW(), :userid, 0, NULL)', array(':postbody' => $postBody, ':userid' => $profileUserId));

        } else {

            die('Incorrect user!');
        }

    }

    public static function createImagePost($postBody, $loggedInUserId, $profileUserId)
    {

        if (strlen($postBody) > 257) {
            die('Incorrect length! (createImagePost)');
        }

        if ($loggedInUserId == $profileUserId) {
            DB::query('INSERT INTO posts VALUES (id, :postbody, NOW(), :userid, 0, NULL)', array(':postbody' => $postBody, ':userid' => $profileUserId));

            return DB::query('SELECT id FROM posts WHERE user_id = :userid ORDER BY ID DESC LIMIT 1;', array(':userid' => $loggedInUserId))[0]['id'];

        } else {
            die('Incorrect user!');
        }
    }

    public static function likePost($postId, $likerId)
    {

        if (!DB::query('SELECT user_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $postId, ':userid' => $likerId))) {
            DB::query('UPDATE posts SET likes=likes+1 WHERE id=:postid', array(':postid' => $postId));
            DB::query('INSERT INTO post_likes VALUES (id, :postid, :userid)', array(':postid' => $postId, ':userid' => $likerId));
        } else {
            DB::query('UPDATE posts SET likes=likes-1 WHERE id=:postid', array(':postid' => $postId));
            DB::query('DELETE FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $postId, ':userid' => $likerId));
        }
    }

    public static function displayPosts($userId, $username, $loggedInUserId)
    {

        $dbPosts = DB::query('SELECT * FROM posts WHERE user_id=:userid ORDER BY id DESC', array(':userid' => $userId));
        $posts = "";

        foreach ($dbPosts as $post) {

            if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $post['id'], ':userid' => $loggedInUserId))) {

                $posts .= "<img width = '200px' src = '" . $post['postimg'] . "'>" . htmlspecialchars($post['body']) . "
                <form action=\"profile.php?username=$username&postId=" . $post['id'] . "\" method=\"post\">
                    <input type=\"submit\" name=\"like\" value =\"Like\">
                    <span> " . $post['likes'] . " likes </span>
                </form>
                <hr><br>";
            } else {
                $posts .= "<img src = '" . $post['postimg'] . "'>" . htmlspecialchars($post['body']) . "
                <form action=\"profile.php?username=$username&postId=" . $post['id'] . "\" method=\"post\">
                    <input type=\"submit\" name=\"unlike\" value =\"Unlike\">
                    <span> " . $post['likes'] . " likes </span>
                </form>
                <hr><br>";
            }
        }
        return $posts;
    }

}