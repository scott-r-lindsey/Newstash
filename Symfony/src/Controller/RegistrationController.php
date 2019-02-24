<?php
declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class RegistrationController extends AbstractController
{

    /**
     * @Route("/xregister/confirmed", name="over_fos_user_registration_confirmed", methods={"GET"})
     * @Template()
     */
    public function confirmed(
        UserInterface $user,
        Request $request
    ): array
    {

        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $session = $this->get('session');
        $redirect = '/';

        if ($session->has('registrationRedirect')){
            $redirect = $session->get('registrationRedirect');
        }

        return [
            'user' => $user,
            'redirect'  => $redirect
        ];
    }
}
