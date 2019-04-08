<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Work;
use App\Entity\Edition;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use TheCodingMachine\GraphQLite\Annotations\Query;

class GraphQLController extends AbstractController
{

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }


    /**
     * @Query()
     */
    public function hello(string $name): string
    {
        return 'Hello ' . $name;
    }

    /**
     * @Query()
     */
    public function work(
        int $id
    ): Work
    {
        return $this->em->getRepository(Work::class)->findOneById($id);
    }

    /**
     * @Query()
     */
    public function edition(
        string $asin
    ): Edition
    {
        return $this->em->getRepository(Edition::class)->findOneByAsin($asin);
    }


}
