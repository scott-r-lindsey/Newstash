<?php
declare(strict_types=1); 

namespace App\Command;
  
use App\Command\BaseCommand;
use App\Entity\BrowseNode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\AwsProductApi;
use Doctrine\ORM\EntityManagerInterface;

class LoadBrowseNodesCommand extends BaseCommand
{

    private $api;
    private $pulled = [];
    private $input;
    private $output;

    const NODE_ROOT     = 283155;
    const NODE_KINDLE   = 154606011;

    /**
     *
     */
    public function __construct(
        AwsProductApi $api,
        EntityManagerInterface $em
    )
    {
        $this->api  = $api;
        $this->em   = $em;

        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('newstash:aws-load-browsenodes')         
            ->setDescription('Fetches browse nodes from amazon')   
            ->setHelp('This is the help')            
        ;
    }

    /**
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output   = $output;
        $this->input    = $input;

        #----------------------------------------------------------------------
        $books_root_node = $this->upfetch(283155);
        $books_root_node->setName('Books');
        $books_root_node->setRoot(true);

        $this->em->flush();

        $this->loadChildren($books_root_node);
        #----------------------------------------------------------------------
    }

    /**
     *
     */
    private function loadChildren(BrowseNode $parent): void
    {
        $children       = [];
        $description    = $parent->getDescription();

        if (isset($this->pulled[$parent->getId()])) {
            return;
        }
        $this->pulled[$parent->getId()] = 1;

        $sxe = $this->api->browseNodeLookup($parent->getId());

        // dupe 171225 could lead to inconstent node naming
        $name = (string)$sxe->BrowseNodes->BrowseNode->Name;

        if ($name != $parent->getName()){
            $parent->setName($name);
        }

        $children = [];

        if ($sxe->BrowseNodes->BrowseNode->Children) {
            $seen = [];

            foreach ($sxe->BrowseNodes->BrowseNode->Children->BrowseNode as $b){

                if (isset($seen[(string)$b->BrowseNodeId])) {
                    continue;
                }

                $child = $this->upfetch((int)$b->BrowseNodeId);

                $this->updateChild($b, $child, $parent);

                $children[] = $child;
            }
        }

        $this->em->flush();

        foreach ($children as $child) {
            $this->loadChildren($child);
        }
    }

    /**
     *
     */
    private function upfetch(int $nodeId): BrowseNode
    {
        $repo = $this->em->getRepository(BrowseNode::class);

        if (!$node = $repo->findOneById($nodeId)) {
            $node = new BrowseNode();
            $node->setId($nodeId);
            $this->em->persist($node);
        }

        return $node;
    }

    /**
     *
     */
    private function updateChild(
        \SimpleXmlElement $node, 
        BrowseNode $child,
        BrowseNode $parent
    ): void {

        $child->setDescription((string)$node->Name[0]);

        $slug_strings = array();
        $pathData = $parent->getPathdata();

        if ($pathData){
            foreach ($pathData as $p){
                $slug_strings[] = $p['name'];
            }
        }

        $slug_strings[] = (string)$node->Name;
        $slug = $this->slugify($slug_strings);

        $ar = [
            'id'    => (string)$node->BrowseNodeId,
            'name'  => (string)$node->Name,
            'slug'  => $slug
        ];

        $pathData[] = $ar;

        $child->setPathdata($pathData)
            ->setName((string)$node->Name[0])
            ->setSlug($slug);
    }

    /**
     *
     */
    private function slugify(array $strings): string{
        $slug = implode(' ', $strings);
        $slug = preg_replace('/[^a-zA-Z0-9 ]/', '', $slug);
        $slug = strtolower($slug);
        $slug = str_replace(' ', '-', $slug);
        return $slug;
    }
}
