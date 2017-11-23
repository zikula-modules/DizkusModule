<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Helper;

use Zikula\Module\SearchModule\AbstractSearchable;
use SecurityUtil;
use ModUtil;
use Zikula\Core\RouteUrl;

/**
 * @todo Use new search api
 */
class SearchHelper extends AbstractSearchable
{
    /**
     * get the UI options for search form
     *
     * @param boolean $active
     * @param array|null $modVars
     * @return string
     */
    public function getOptions($active, $modVars = null)
    {
        if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            $this->view->assign('active', $active);
            $this->view->assign('forums', ModUtil::apiFunc($this->name, 'Forum', 'getParents', ['includeRoot' => false]));

            return $this->view->fetch('Search/options.tpl');
        }

        return '';
    }

    /**
     * Get the search results
     *
     * @param array $words array of words to search for
     * @param string $searchType AND|OR|EXACT
     * @param array|null $modVars module form vars passed though
     * @return array
     */
    public function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            return [];
        }

        $forums = isset($modVars['forum']) ? $modVars['forum'] : null;
        $location = isset($modVars['location']) ? $modVars['location'] : 'post';

        $minLength = ModUtil::getVar($this->name, 'minsearchlength', 3);
        $maxLength = ModUtil::getVar($this->name, 'maxsearchlength', 30);
        foreach ($words as $word) {
            if (strlen($word) < $minLength || strlen($word) > $maxLength) {
                $this->addError($this->__f('For forum searches, each search term must be between %1$s and %2$s characters in length.', [$minLength, $maxLength]));

                return [];
            }
        }
        if (!is_array($forums) || 0 == count($forums)) {
            // set default
            $forums[0] = -1;
        }
        $location = (in_array($location, ['post', 'author'])) ? $location : 'post';

        // get all forums the user is allowed to read
        $allowedForums = ModUtil::apiFunc($this->name, 'forum', 'getForumIdsByPermission');
        if (!is_array($allowedForums) || 0 == count($allowedForums)) {
            $this->addError($this->__('You do not have permission to search any of the forums.'));

            return [];
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t')
            ->from('Zikula\DizkusModule\Entity\TopicEntity', 't')
            ->leftJoin('t.posts', 'p');

        switch ($location) {
            case 'author':
                $authorIds = [];
                foreach ($words as $word) {
                    $authorId = \UserUtil::getIDFromName($word);
                    if ($authorId > 0) {
                        $authorIds[] =$authorId;
                    }
                }
                if (count($authorIds) > 0) {
                    $qb->andWhere($qb->expr()->in('p.poster', ':authorIds'))
                        ->setParameter('authorIds', $authorIds);
                } else {
                    return [];
                }

                break;
            case 'post':
            default:
                $whereExpr = $this->formatWhere($qb, $words, ['t.title', 'p.post_text'], $searchType);
                $qb->andWhere($whereExpr);
        }
        // check forums (multiple selection is possible!)
        if (!is_array($forums) && $forums == -1 || $forums[0] == -1) {
            // search in all forums we are allowed to see
            $qb->andWhere($qb->expr()->in('t.forum', ':forums'))->setParameter('forums', $allowedForums);
        } else {
            // filter out forums we are not allowed to read
            $forums = array_intersect($allowedForums, (array)$forums);
            if (0 == count($forums)) {
                // error or user is not allowed to read any forum at all
                // return empty result set without even doing a db access
                $this->addError($this->__('You do not have permission to search the requested forums.'));

                return [];
            }
            $qb->andWhere($qb->expr()->in('t.forum', ':forums'))->setParameter('forums', $forums);
        }

        $topics = $qb->getQuery()->getResult();
        $sessionId = session_id();
        $showTextInSearchResults = ModUtil::getVar($this->name, 'showtextinsearchresults', false);
        // Process the result set and insert into search result table
        $records = [];
        foreach ($topics as $topic) {
            /** @var $topic \Zikula\Module\DizkusModule\Entity\TopicEntity */
            $records[] = [
                'title' => $topic->getTitle(),
                'text' => $showTextInSearchResults ? $topic->getPosts()->first()->getPost_text() : '',
                'created' => $topic->getTopic_time(),
                'module' => $this->name,
                'sesid' => $sessionId,
                'url' => RouteUrl::createFromRoute('zikuladizkusmodule_user_viewtopic', ['topic' => $topic->getTopic_id()]),
            ];
        }

        return $records;
    }
}
