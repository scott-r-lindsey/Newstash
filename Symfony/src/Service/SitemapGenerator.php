<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\WorkRepository;
use App\Service\Mongo;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\RouterInterface;

class SitemapGenerator
{
    private $em;
    private $logger;
    private $mongo;
    private $projectDir;
    private $twig;
    private $router;
    private $filesystem;
    private $dir;

    private $output;

    const SITE_URL      = 'https://booksto.love/';
    const HOSTNAME      = 'booksto.love';

    const URLS_PER_FILE = 25000;

    const STATIC_XML =
'<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>http://booksto.love/</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>http://booksto.love/about</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>http://booksto.love/privacy</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>http://booksto.love/blog</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
</urlset>
';

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        Mongo $mongo,
        string $projectDir,
        EngineInterface $twig,
        RouterInterface $router,
        Filesystem $filesystem
    )
    {
        $this->em                   = $em;
        $this->logger               = $logger;
        $this->mongo                = $mongo;
        $this->projectDir           = $projectDir;
        $this->twig                 = $twig;
        $this->router               = $router;
        $this->filesystem           = $filesystem;
        $this->dir                  = $this->projectDir . '/var/sitemap';
    }

    public function generateSitemap(): array
    {
        $mongodb            = $this->mongo->getDb();
        $workCollection     = $mongodb->works;

        $this->output->writeln('clearing old content...');
        $this->clearOld();

        // sitemap-static.xml -------------------------------------------------

        $this->output->writeln('writing sitemap-static.xml');
        $this->filesystem->dumpFile(
            $this->dir . '/sitemap-static.xml', self::STATIC_XML);


        // sitemap-urls-$i.xml ------------------------------------------------

        $cursor     = $workCollection->find([]);
        $works      = [];
        $i = $ii    = 0;

        $this->output->writeln('fetching from works');
        foreach ($cursor as $work) {
            $ii++;

            $urls[] = [
                'loc'           => $this->router->generate('work', [
                        'work_id'   => $work['work_id'],
                        'slug'      => $work['slug'],
                    ]),
                'changefreq'    => 'weekly',
                'priority'      => '1.0'
            ];

            if (self::URLS_PER_FILE == $ii){
                $i++;
                $this->output->writeln("Writing file $i");
                $this->writeSitemapFile($i, $urls);

                $ii         = 0;
                $urls       = [];
            }
        }

        $i++;
        $this->output->writeln("Writing file $i");
        $this->writeSitemapFile($i, $urls);

        // sitemap.xml -------------------------------------------------------

        $rendered = $this->twig->render(
            'default/sitemap-map.xml.twig',
            [
                'hostname'      => self::HOSTNAME,
                'files'         => range(1, $i)
            ]
        );

        $this->output->writeln("Writing sitemap.xml");
        $this->filesystem->dumpFile(
            $this->dir . '/sitemap.xml', $rendered);

        // gzip each file

        $this->output->writeln("gzipping files");
        $this->gzipFiles();

        // return array -------------------------------------------------------

        $finder     = new Finder();
        $files      = [];

        $finder->files()->in($this->dir)->name('*xml*');
        foreach ($finder as $file) {
            $files[] = $file->getRealPath();
        }

        return $files;
    }

    public function setOutput($output): void
    {
        $this->output = $output;
    }

    private function clearOld(): void
    {
        $finder = new Finder();
        $finder->files()->in($this->dir)->name('*xml*');
        $this->filesystem->remove($finder);
    }

    private function gzipFiles(): void
    {
        $finder = new Finder();
        $finder->files()->in($this->dir)->name('*xml*');

        $cmd = ['gzip'];

        foreach ($finder as $file) {
            $cmd[] = $file->getRealPath();
        }

        $process = new Process($cmd);
        $process->run();
    }

    private function writeSitemapFile(
        int $i,
        array $urls
    ): void
    {

        $rendered = $this->twig->render(
                'default/sitemap.xml.twig',
                [
                    'urls'          => $urls,
                    'hostname'      => self::HOSTNAME
                ]
            );

        $this->filesystem->dumpFile($this->dir . "/sitemap-urls-$i.xml", $rendered);
    }

    private function writeln(string $message): void
    {
        if ($this->output) {
            $this->output->writeln($message);
        }
    }
}
