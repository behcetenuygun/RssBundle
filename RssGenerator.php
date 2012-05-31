<?php

namespace OS\RssBundle;

use Doctrine\ORM\EntityManager,
DOMDocument,
DateTime;

/**
 *
 * @author ouardisoft
 */
class RssGenerator
{

    private $em;
    private $dom;
    private $configs;
    private $router;

    function __construct(EntityManager $em, $configs, $router)
    {
        $this->em = $em;
        $this->configs = unserialize($configs);
        $this->router = $router;
    }

    public function bindChannel($rss)
    {
        $channel = $this->dom->createElement('channel');

        // Channel infos
        $tags = array('title', 'description', 'language', 'webMaster', 'link', 'copyright', 'generator', 'icon');
        foreach ($tags as $tag) {
            $elem = $this->dom->createElement($tag);
            $text = $this->dom->createTextNode($this->configs[$tag]);
            $elem->appendChild($text);

            $channel->appendChild($elem);
        }


        $url = $this->dom->createElement('url');
        $url->appendChild($this->dom->createTextNode($this->configs['image']));

        $image = $this->dom->createElement('image');
        $image->appendChild($url);
        $image->appendChild($this->dom->createElement('description')->appendChild($this->dom->createTextNode('Enugyun.com')));
        $image->appendChild($this->dom->createElement('link')->appendChild($this->dom->createTextNode('http://www.enuygun.com')));

        $channel->appendChild($image);

        $elem = $this->dom->createElement('atom:link');
        $elem->setAttribute('href', $this->configs['link']);
        $elem->setAttribute('rel', 'self');
        $elem->setAttribute('type', 'application/rss+xml');

        $channel->appendChild($elem);

        // Channel items
        $this->bindItems($channel);

        $rss->appendChild($channel);
    }

    public function bindItems($channel)
    {
        $itemConfigs = $this->configs['item'];

        $entities = $this->em->getRepository($itemConfigs['entity'])->listAll(1, 10);

        $itemTags = array('title', 'link', 'description', 'pubDate', 'guid', 'author');
        foreach ($entities as $entity) {
            $item = $this->dom->createElement('item');
            foreach ($itemTags as $tag) {
                $elem = $this->dom->createElement($tag);
                $text = $this->dom->createTextNode($this->getItemTagValue($entity, $tag));

                $elem->appendChild($text);

                $item->appendChild($elem);
            }
            $channel->appendChild($item);
        }
    }

    public function getItemTagValue($entity, $tag, $stripWords = 50)
    {
        $itemConfigs = $this->configs['item'];

        if (!is_array($itemConfigs[$tag])) {
            $value = $entity->{'get' . ucfirst($itemConfigs[$tag])}();

            if ($value instanceof DateTime) {
                $value = $value->format('r');
            } else {
                if($tag == 'description') {
                    $values = preg_split('/[ \t\s]+/', strip_tags($value), $stripWords);
                    unset($values[$stripWords -1]);
                    $value = implode(' ', $values) .'...';
                }
            }

            return $value;
        } else {
            $params = $route = array();
            extract($itemConfigs[$tag]);

            foreach ($params as $key => $param) {
                if (is_array($param)) {
                    $value        = $entity->{'get' . ucfirst($param['field'])}();
                    $object       = new $param['class'];
                    $params[$key] = $object->{$param['method']}($value);
                } else {
                    $value        = $entity->{'get' . ucfirst($param)}();
                    $params[$key] = $value;
                }
            }
            return $this->router->generate($route, $params, true);
        }
    }

    public function getContent()
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;
        $this->dom->substituteEntities = false;

        $rss = $this->dom->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $rss->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
        $this->bindChannel($rss);

        $this->dom->appendChild($rss);

        return $this->dom->saveXML();
    }

}