<?php

namespace App\Service\Auth;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class NewstashUserProvider extends BaseClass{

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response) {
        $property = $this->getProperty($response);
        $username = $response->getUsername();

        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();

        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';

        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }

        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $responseInt) {
        $username = $responseInt->getUsername();
        $property = $this->getProperty($responseInt);
        $response = $responseInt->getResponse();

        $user = $this->userManager->findUserBy(array($this->getProperty($responseInt) => $username));
        //when the user is registrating
        if (null === $user) {
            $service = $responseInt->getResourceOwner()->getName();
            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_token = $setter.'AccessToken';

            $user = $this->userManager->createUser();
            $user->$setter_id($username);
            $user->$setter_token($responseInt->getAccessToken());

            $user->setUsername($property . ':' . $username);
            $user->setEmail($responseInt->getEmail());

            if (isset($response['first_name'])){ // FB way 
                $user->setFirstName($response['first_name']);
            }
            else if (isset($response['given_name'])){ // Google way
                $user->setFirstName($response['given_name']);
            }
            if (isset($response['last_name'])){ // FB way 
                $user->setLastName($response['last_name']);
            }
            else if (isset($response['family_name'])){ // Google way
                $user->setLastName($response['family_name']);
            }

            if (isset($response['gender'])){
                $user->setGender($response['gender']);
            }
            if (isset($response['locale'])){
                $user->setLocale($response['locale']);
            }

            if ('googleId' == $property){
                if ($response['picture']){
                    $user->setGoogleProfilePic($response['picture']);
                }
            }

            $user->setPassword('*locked*');

            $user->setEnabled(true);
            $this->userManager->updateUser($user);
            return $user;
        }

        //if user exists - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($responseInt);

        // always update first, last, email, profile pic
        if (isset($response['first_name'])){ // FB way 
            $user->setFirstName($response['first_name']);
        }
        else if (isset($response['given_name'])){ // Google way
            $user->setFirstName($response['given_name']);
        }
        if (isset($response['last_name'])){ // FB way 
            $user->setLastName($response['last_name']);
        }
        else if (isset($response['family_name'])){ // Google way
            $user->setLastName($response['family_name']);
        }
        $user->setEmail($responseInt->getEmail());
        if ('googleId' == $property){
            if ($response['picture']){
                $user->setGoogleProfilePic($response['picture']);
            }
        }

        $serviceName = $responseInt->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';

        //update access token
        $user->$setter($responseInt->getAccessToken());

        return $user;
    }
}
