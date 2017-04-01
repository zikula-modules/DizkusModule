<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Manager;

/**
 * Description of PostsCollectionManager
 *
 * @author Kaik
 */
class PostsCollectionManager
{
    private $posts;


    public function getPosts()
    {
        return $this->posts;
    }

    public function setPosts($posts)
    {
        $this->posts = $posts;
        return $this;
    }

    public function loadPosts()
    {
        $this->posts = $posts;
        return $this;
    }


}
