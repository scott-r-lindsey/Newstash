<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScoreRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="sessions"
 * )
 */

class Session{

    /**
     * @ORM\Id
    /* @ORM\Column(type="string", length=128, nullable=false, name="sess_id")
     */
    private $id;

    /** @ORM\Column(type="blob", name="sess_data") */
    private $data;

    /** @ORM\Column(type="integer", name="sess_time", options={"unsigned"=true}) */
    private $time;

    /** @ORM\Column(type="integer", name="sess_lifetime", options={"unsigned"=true}) */
    private $lifetime;

}
