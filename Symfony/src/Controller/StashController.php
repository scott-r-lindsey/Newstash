<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StashController extends AbstractController
{


    /**
     * @Route("/user/displayprefs", name="user_displayprefs_save", methods={"POST"}))
     *
     * Save user display prefs
     */
    public function putUserDisplayPrefs(Request $request) 
    {

        // FIXME

        list($fail, $response, $em, $user, $work) = $this->setup();
        if ($fail){
            return $response;
        }

        $hide    = ($request->request->get('hide'));
        $prefs = $user->getDisplayPrefs();
        $prefs['hide'] = explode(',', $hide);
        if ('' == $prefs['hide'][0]){
            $prefs['hide'] = array();
        }

        $user->setDisplayPrefs($prefs);
        $em->flush();

        $prefs = $user->getDisplayPrefs();

        $response->setData(array(
            'error'     => 0,
            'result'    => 'Data',
            'id'        => $user->getId(),
            'lists'     => [],
            'prefs'     => $prefs
        ));
        return $response;

    }
}
