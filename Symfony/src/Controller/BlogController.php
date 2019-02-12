<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{

    /**
     * @Route("/blog", name="blog", methods={"GET"})
     * @Template()
     */
    public function indexAction(Request $request){
        // FIXME pagination
        // only exposes last ten posts

        $postManager = $this->get('bookstash.blog.post_manager');
        return $postManager->frontPosts();
    }


}
