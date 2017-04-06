<?php
/**
 * Dizkus.
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\PlainResponse;

class UsersController extends AbstractController
{
    /**
     * @Route("/users/online", options={"expose"=true})
     * @Method("GET")
     *
     * Performs a user search based on the user name fragment entered so far.
     *
     * @param Request $request
     *                         fragment A partial user name entered by the user
     *
     * @throws AccessDeniedException
     *
     * @return string plainResponse with json_encoded object of users matching the criteria
     */
    public function getOnlineUsersAction(Request $request)
    {
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $settings = $this->getVars();
        $secinactivemins = $this->get('zikula_extensions_module.api.variable')->getSystemVar('secinactivemins');
        $online = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumUserEntity')->getOnlineUsers($secinactivemins, $settings['onlineusers_moderatorcheck']);
        if(count($online['users']) > 0 && $settings['onlineusers_moderatorcheck']){
            foreach($online['users'] as $uid => $user){
                if($user['isModerator'] == false){
                   $online['users'][$uid]['isModerator'] = $this->hasPermission('ZikulaDizkusModule::', '::', ACCESS_MODERATE);
                }
            }
        }

        return $this->render('@ZikulaDizkusModule/User/users.online.html.twig', [
            'online' => $online,
            'secinactivemins' => $secinactivemins,
            'anonymoussessions'=> $this->get('zikula_extensions_module.api.variable')->getSystemVar('anonymoussessions'),
            'settings' => $settings,
        ]);
    }

    /**
     * @Route("/users", options={"expose"=true})
     * @Method("GET")
     *
     * Performs a user search based on the user name fragment entered so far.
     *
     * @param Request $request
     *                         fragment A partial user name entered by the user
     *
     * @throws AccessDeniedException
     *
     * @return string plainResponse with json_encoded object of users matching the criteria
     */
    public function getUsersByFragmentsAction(Request $request)
    {
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }
        $fragment = $request->query->get('fragment', null);
        $users = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumUserEntity')->getUsersByFragments(['fragments' => [$fragment]]);

        $reply = [];
        $reply['query'] = $fragment;
        $reply['suggestions'] = [];
        /** @var $user \Zikula\UsersModule\Entity\UserEntity */
        foreach ($users as $user) {
            $reply['suggestions'][] = [
                'value' => htmlentities(stripslashes($user->getUname())),
                'data' => $user->getUid(),];
        }

        return new PlainResponse(json_encode($reply));
    }

}
