<?php
namespace App\GraphQL\Resolver;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\DataLoader\DataLoader;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class UserResolver implements ResolverInterface {

    private $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em           = $em;
    }

    public function setUserLoader(DataLoader $userLoader)
    {
        $this->userLoader = $userLoader;
    }

    // ------------------------------------------------------------------------

    public function __invoke(ResolveInfo $info, $value, Argument $args)
    {
        $method = $info->fieldName;
        return $this->$method($value, $args);
    }

    public function user(int $id)
    {
        return $this->userLoader->load($id);
    }

    // ------------------------------------------------------------------------
    // getters

    public function id(User $user): int
    {
        return $user->getId();
    }

    // ------------------------------------------------------------------------
    // getters

    public function facebookId(User $user): ?string
    {
        return $user->getFacebookId();
    }
    public function googleId(User $user): ?string
    {
        return $user->getGoogleId();
    }
    public function avatar_url(User $user): ?string
    {
        return $user->getAvatarUrl();
    }
    public function first_name(User $user): ?string
    {
        return $user->getFirstName();
    }
    public function last_name(User $user): ?string
    {
        return $user->getLastName();
    }
    public function gender(User $user): ?string
    {
        return $user->getGender();
    }
    public function locale(User $user): ?string
    {
        return $user->getLocale();
    }
    public function rating_count(User $user): int
    {
        return $user->getRatingCount() || 0;
    }
    public function review_count(User $user): int
    {
        return $user->getReviewCount() || 0;
    }
    public function comment_count(User $user): int
    {
        return $user->getCommentCount() || 0;
    }
}
