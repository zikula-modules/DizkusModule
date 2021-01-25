<?php

declare(strict_types=1);

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Helper;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;

class SearchHelper implements SearchableInterface
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var ForumManagerService
     */
    private $forumManagerService;

    private $errors = [];

    /**
     * SearchHelper constructor.
     */
    public function __construct(
        PermissionApiInterface $permissionApi,
        SessionInterface $session,
        TranslatorInterface $translator,
        VariableApi $variableApi,
        ForumManager $forumManagerService
    ) {
        $this->name = 'ZikulaDizkusModule';
        $this->permissionApi = $permissionApi;
        $this->session = $session;
        $this->translator = $translator;

        $this->variableApi = $variableApi;
        $this->extendedsearch = $this->variableApi->get($this->name, 'extendedsearch', false);
        $this->showtextinsearchresults = $this->variableApi->get($this->name, 'showtextinsearchresults', false);
        $this->minsearchlength = $this->variableApi->get($this->name, 'minsearchlength', 3);
        $this->maxsearchlength = $this->variableApi->get($this->name, 'maxsearchlength', 30);

        $this->forumManagerService = $forumManagerService;
    }

    /**
     * {@inheritdoc}
     */
    public function amendForm(FormBuilderInterface $form)
    {
        if ($this->permissionApi->hasPermission('ZikulaDizkusModule::', '::', ACCESS_READ)) {
            $managedRoot = $this->forumManagerService->getManager(1);
            $choices = array_flip((['' => '<< ' . $this->translator->__('Select forum') . ' >>'] + $managedRoot->getAllChildren()));

            $form->add('search_forums', ChoiceType::class, [
                'choices'           => $choices,
                'label'             => $this->translator->__('Forums to search'),
                'multiple'          => true,
                'expanded'          => false,
                'choices_as_values' => true
            ]);

            $form->add('search_forums_in', ChoiceType::class, [
                'choices'           => [
                    $this->translator->__('Topic title')   => 'topic',
                    $this->translator->__('Posts')         => 'post',
                    $this->translator->__('Author')        => 'author',
                ],
                'label'             => $this->translator->__('Search '),
                // *this line is important*
                'choices_as_values' => true,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        if (!$this->permissionApi->hasPermission('ZikulaDizkusModule::', '::', ACCESS_READ)) {
            return [];
        }

        $results = [];

        $forums = isset($modVars['search_forums']) && !empty($modVars['search_forums']) ? $modVars['search_forums'] : null;
        $location = $modVars['search_forums_in'] ?? 'post';

        foreach ($words as $word) {
            if (mb_strlen($word) < $this->minsearchlength || mb_strlen($word) > $this->minsearchlength) {
                $this->errors[] = $this->translator->__f('For forum searches, each search term must be between %1s and %2s characters in length.', ['%1s' => $this->minsearchlength, '%2s' => $this->maxsearchlength]);

                return [];
            }
        }

        // @todo - finish search
        // old search
//        if (!is_array($forums) || 0 == count($forums)) {
//            // set default
//            $forums[0] = -1;
//        }
//        $location = (in_array($location, ['post', 'author'])) ? $location : 'post';
//
//        // get all forums the user is allowed to read
//        $allowedForums = ModUtil::apiFunc($this->name, 'forum', 'getForumIdsByPermission');
//        if (!is_array($allowedForums) || 0 == count($allowedForums)) {
//            $this->addError($this->__('You do not have permission to search any of the forums.'));
//
//            return [];
//        }
//
//        $qb = $this->entityManager->createQueryBuilder();
//        $qb->select('t')
//            ->from('Zikula\DizkusModule\Entity\TopicEntity', 't')
//            ->leftJoin('t.posts', 'p');
//
//        switch ($location) {
//            case 'author':
//                $authorIds = [];
//                foreach ($words as $word) {
//                    $authorId = \UserUtil::getIDFromName($word);
//                    if ($authorId > 0) {
//                        $authorIds[] =$authorId;
//                    }
//                }
//                if (count($authorIds) > 0) {
//                    $qb->andWhere($qb->expr()->in('p.poster', ':authorIds'))
//                        ->setParameter('authorIds', $authorIds);
//                } else {
//                    return [];
//                }
//
//                break;
//            case 'post':
//            default:
//                $whereExpr = $this->formatWhere($qb, $words, ['t.title', 'p.post_text'], $searchType);
//                $qb->andWhere($whereExpr);
//        }
//        // check forums (multiple selection is possible!)
//        if (!is_array($forums) && $forums == -1 || $forums[0] == -1) {
//            // search in all forums we are allowed to see
//            $qb->andWhere($qb->expr()->in('t.forum', ':forums'))->setParameter('forums', $allowedForums);
//        } else {
//            // filter out forums we are not allowed to read
//            $forums = array_intersect($allowedForums, (array)$forums);
//            if (0 == count($forums)) {
//                // error or user is not allowed to read any forum at all
//                // return empty result set without even doing a db access
//                $this->addError($this->__('You do not have permission to search the requested forums.'));
//
//                return [];
//            }
//            $qb->andWhere($qb->expr()->in('t.forum', ':forums'))->setParameter('forums', $forums);
//        }
//
//        $topics = $qb->getQuery()->getResult();
//        $sessionId = session_id();
//        $showTextInSearchResults = ModUtil::getVar($this->name, 'showtextinsearchresults', false);
//        // Process the result set and insert into search result table
//        $records = [];
//        foreach ($topics as $topic) {
//            /** @var $topic \Zikula\Module\DizkusModule\Entity\TopicEntity */
//            $records[] = [
//                'title' => $topic->getTitle(),
//                'text' => $showTextInSearchResults ? $topic->getPosts()->first()->getPost_text() : '',
//                'created' => $topic->getTopic_time(),
//                'module' => $this->name,
//                'sesid' => $sessionId,
//                'url' => RouteUrl::createFromRoute('zikuladizkusmodule_user_viewtopic', ['topic' => $topic->getTopic_id()]),
//            ];
//        }

        return $results;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

//  debug
//        dump($words);
//        dump($searchType);
//        dump($modVars);
//        dump($forums);
// users example
//        $users = $this->userRepository->getSearchResults($words);
//        foreach ($users as $user) {
//            if (1 != $user->getUid() && $this->permissionApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_READ)) {
//                $result = new SearchResultEntity();
//                $result->setTitle($user->getUname())
//                    ->setModule('ZikulaUsersModule')
//                    ->setCreated($user->getUser_Regdate())
//                    ->setSesid($this->session->getId());
//                $results[] = $result;
//            }
//        }
