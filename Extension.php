<?php

namespace Bolt\Extension\HeidiLabs\SpeakerDeck;

use Bolt\Application;
use Bolt\BaseExtension;

class Extension extends BaseExtension
{

    public function getName()
    {
        return "speakerdeck";
    }

    /**
     * Initialize SpeakerDeck. Called during bootstrap phase.
     */
    function initialize()
    {
        // Initialize the Twig function
        $this->addTwigFunction('speakerdeck', 'twigSpeakerdeck');

    }

    /**
     * Twig function {{ speakerdeck('url') }} in SpeakerDeck extension.
     */
    function twigSpeakerdeck($deckUrl = "")
    {
        if(!filter_var($deckUrl, FILTER_VALIDATE_URL)) {

            return 0;
        }

        $deck = $this->fetchOembed($deckUrl);

        $html = "";

        if ($deck) {
            $content = json_decode($deck, 1);
            $html = $content['html'];
        } else {
            $html = '<a href="' . $deckUrl . '">' . $deckUrl . '</a>';
        }

        return new \Twig_Markup($html, 'UTF-8');

    }

    private function fetchOembed($url)
    {
        $handle = preg_replace('/[^A-Za-z0-9_-]+/', '', $url);
        $handle = str_replace('https', '', $handle);
        $handle = str_replace('speakerdeckcom', '', $handle);

        if ($this->app['cache']->contains($handle)) {
            return $this->app['cache']->fetch($handle);
        }

        $url = "http://speakerdeck.com/oembed.json?url=$url";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $data = curl_exec($ch);
        curl_close($ch);

        $this->app['cache']->save($handle, $data);

        return $data;
    }

    public function isSafe()
    {
        return true;
    }
}






