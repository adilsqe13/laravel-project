<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Article;

class ScrapeBeyondChatsBlogs extends Command
{
    protected $signature = 'scrape:beyondchats';
    protected $description = 'Automatically scrape last 5 oldest BeyondChats blog articles from last page';

    public function handle()
    {
        $client = HttpClient::create();

        /* -------------------------------------------------
           STEP 1: Detect last page automatically
        --------------------------------------------------*/
        $firstResponse = $client->request('GET', 'https://beyondchats.com/blogs/');
        $firstHtml = $firstResponse->getContent();
        $firstCrawler = new Crawler($firstHtml);

        $lastPage = 1;

        $firstCrawler->filter('.page-numbers')->each(function ($node) use (&$lastPage) {
            if (is_numeric(trim($node->text()))) {
                $lastPage = max($lastPage, (int) trim($node->text()));
            }
        });

        $this->info("Detected last page: {$lastPage}");

        /* -------------------------------------------------
           STEP 2: Fetch last page
        --------------------------------------------------*/
        $response = $client->request(
            'GET',
            "https://beyondchats.com/blogs/page/{$lastPage}/"
        );

        $html = $response->getContent();
        $crawler = new Crawler($html);

        /* -------------------------------------------------
           STEP 3: Get 5 oldest articles
        --------------------------------------------------*/
        $posts = $crawler->filter('article')->slice(-5);

        if ($posts->count() === 0) {
            $this->error('No articles found on last page');
            return;
        }

        /* -------------------------------------------------
           STEP 4: Clear old database records
        --------------------------------------------------*/
        Article::truncate();
        $this->info('Old articles cleared from database');

        /* -------------------------------------------------
           STEP 5: Scrape article detail pages + store
        --------------------------------------------------*/
        $posts->each(function ($node) use ($client) {

            $title = $node->filter('h2')->count()
                ? trim($node->filter('h2')->text())
                : 'No title';

            $url = $node->filter('a')->count()
                ? $node->filter('a')->attr('href')
                : null;

            if (!$url) {
                return;
            }

            /* -------- Fetch article content -------- */
            try {
                $articleResponse = $client->request('GET', $url);
                $articleHtml = $articleResponse->getContent();
                $articleCrawler = new Crawler($articleHtml);

                $content = $articleCrawler->filter('.entry-content')->count()
                    ? trim($articleCrawler->filter('.entry-content')->text())
                    : 'Content not found';

            } catch (\Exception $e) {
                $content = 'Failed to fetch content';
            }

            /* -------- Store in database -------- */
            Article::create([
                'title'   => $title,
                'url'     => $url,
                'content' => $content,
            ]);
        });

        $this->info("5 oldest articles scraped successfully from page {$lastPage}");
    }
}
