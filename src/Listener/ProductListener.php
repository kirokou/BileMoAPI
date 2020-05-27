<?php

namespace App\Listener;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\StaticPropertyMetadata;

class ProductListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'format' => 'json',
                'class' => 'App\Entity\Product',
                'method' => 'onPostSerialize',
            ]
        ];
    }

    public static function onPostSerialize(ObjectEvent $event)
    {
        // Possibilité de récupérer l'objet qui a été sérialisé
        $object = $event->getObject();

        $date = new \Datetime();
        // Possibilité de modifier le tableau après sérialisation
        $event->getVisitor()->visitProperty(new StaticPropertyMetadata ('', 'delivered_at', null), $date->format('l jS \of F Y h:i:s A'));
    }
}