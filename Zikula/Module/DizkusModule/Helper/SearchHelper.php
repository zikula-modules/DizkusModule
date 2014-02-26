<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Helper;

use Zikula\Module\SearchModule\AbstractSearchable;
use SecurityUtil;
use ModUtil;
use Zikula\Core\ModUrl;

class SearchHelper extends AbstractSearchable
{
    /**
     * get the UI options for search form
     *
     * @param $args
     * @return string
     */
    public function getOptions($args)
    {
        if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            $this->view->assign('active', isset($args['active']) && isset($args['active'][$this->name]) || !isset($args['active']));
            $this->view->assign('forums', ModUtil::apiFunc($this->name, 'Forum', 'getParents', array('includeRoot' => false)));

            return $this->view->fetch('search/options.tpl');
        }

        return '';
    }

    /**
     * Get the search results
     *
     * @param array $args The arguments array.
     * q             string the text to search
     * searchtype    string 'AND', 'OR' or 'EXACT'
     * searchorder   string 'newest', 'oldest' or 'alphabetical'
     * numlimit      int    limit for search, defaults to 10
     * page          int    number of page to show
     * startnum      int    the first item to show
     * from Dizkus:
     * searchwhere   string 'posts' or 'author'
     * forums        array of forum IDs to search
     *     
     * @param $args
     * @return array
     */
    public function getResults($args)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            return array();
        }
        // only use the first term in the array
        $searchedTerm = $args['q'][0];

        $request = $this->getContainer()->get("request");
        $forums = $request->get('Dizkus_forum', null);
        $searchWhere = $request->get('Dizkus_searchwhere', 'post');

        $minLength = ModUtil::getVar($this->name, 'minsearchlength', 3);
        $maxLength = ModUtil::getVar($this->name, 'maxsearchlength', 30);
        if (strlen($searchedTerm) < $minLength || strlen($searchedTerm) > $maxLength) {
            $request->getSession()->getFlashBag()->add('status', $this->__f('Error! For forum searches, the search string must be between %1$s and %2$s characters in length.', array($minLength, $maxLength)));
            return array();
        }
        if (!is_array($forums) || count($forums) == 0) {
            // set default
            $forums[0] = -1;
        }
        $searchWhere = (in_array($searchWhere, array('post', 'author'))) ? $searchWhere : 'post';

        // get all forums the user is allowed to read
        $allowedForums = ModUtil::apiFunc($this->name, 'forum', 'getForumIdsByPermission');
        if (!is_array($allowedForums) || count($allowedForums) == 0) {
            $request->getSession()->getFlashBag()->add('danger', $this->__('Error: You do not have permission to search any of the forums.'));
            return array();
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t')
            ->from('Zikula\Module\DizkusModule\Entity\TopicEntity', 't')
            ->leftJoin('t.posts', 'p');

        switch ($searchWhere) {
            case 'author':
                $authorId = \UserUtil::getIDFromName($searchedTerm);
                if ($authorId > 0) {
                    $qb->andWhere('p.poster = :authorId')
                        ->setParameter('authorId', $authorId);
                } else {
                    return array();
                }
                break;
            case 'post':
            default:
                $whereExpr = $this->formatWhere($qb, $args, array('t.title', 'p.post_text'));
                $qb->andWhere($whereExpr);
        }
        // check forums (multiple selection is possible!)
        if (!is_array($forums) && $forums == -1 || $forums[0] == -1) {
            // search in all forums we are allowed to see
            $qb->andWhere($qb->expr()->in('t.forum', $allowedForums));
        } else {
            // filter out forums we are not allowed to read
            $forums = array_intersect($allowedForums, (array)$forums);
            if (count($forums) == 0) {
                // error or user is not allowed to read any forum at all
                // return empty result set without even doing a db access
                $request->getSession()->getFlashBag()->add('danger', $this->__('Error: You do not have permission to search the requested forums.'));
                return array();
            }
            $qb->andWhere($qb->expr()->in('t.forum', $forums));
        }

        $topics = $qb->getQuery()->getResult();
        $sessionId = session_id();
        $showTextInSearchResults = ModUtil::getVar($this->name, 'showtextinsearchresults', 'no');
        // Process the result set and insert into search result table
        $records = array();
        foreach ($topics as $topic) {
            /** @var $topic \Zikula\Module\DizkusModule\Entity\TopicEntity */
            $records[] = array(
                'title' => $topic->getTitle(),
                'text' => $showTextInSearchResults == 'yes' ? $topic->getPosts()->first()->getPost_text() : '',
                'created' => $topic->getTopic_time(),
                'module' => $this->name,
                'sesid' => $sessionId,
                'url' => new ModUrl($this->name, 'user', 'viewtopic', \ZLanguage::getLanguageCode(), array('topic' => $topic->getTopic_id()))
            );
        }

        return $records;
    }

}