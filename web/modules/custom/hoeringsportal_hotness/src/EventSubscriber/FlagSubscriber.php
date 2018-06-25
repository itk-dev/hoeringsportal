<?php

/**
 * @file
 * Contains \Drupal\hoeringsportal_hotness\EventSubscriber\FlagSubscriber.
 */

namespace Drupal\hoeringsportal_hotness\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\flag\Event\FlagEvents;
use Drupal\flag\Event\FlaggingEvent;
use Drupal\flag\Event\UnflaggingEvent;
use Drupal\node\Entity\Node;

class FlagSubscriber implements EventSubscriberInterface {

  public function onFlag(FlaggingEvent $event) {
    $indicators = \Drupal::getContainer()->get('hoeringsportal_hotness.settings')->getAll();
    $flagging = $event->getFlagging();
    $entity_nid = $flagging->getFlaggable()->id();
    $node = Node::load($entity_nid);
    $flag_counts = \Drupal::service('flag.count')->getEntityFlagCounts($node);

    // Check for promote flag, else it's the first flag being set, and we just set the count manually.
    if(isset($flag_counts['promote'])) {
      $counts = $flag_counts['promote'] + 1; // +1  due to the flag about to be set.
    }
    else {
      $counts = 1;
    }

    // Set hotness based on nodes likes and indicator configuration.
    if (isset($indicators)) {
      foreach ($indicators as $key => $indicator) {
        if ($counts >= $indicator) {
          $node->field_hotness->value = $key;
        }
      }
    }

    $node->save();
  }

  public function onUnflag(UnflaggingEvent $event) {
    $indicators = \Drupal::getContainer()->get('hoeringsportal_hotness.settings')->getAll();
    $flagging = $event->getFlaggings();
    $flagging = reset($flagging);
    $entity_nid = $flagging->getFlaggable()->id();
    $node = Node::load($entity_nid);
    $flag_counts = \Drupal::service('flag.count')->getEntityFlagCounts($node);

    // Check for promote flag, else it's the first flag being set, and we just set the count manually.
    if(isset($flag_counts['promote'])) {
      $counts = $flag_counts['promote'] - 1; // -1  due to the flag about to be removed.
    }
    else {
      $counts = 0;

    }

    // Set hotness based on nodes likes and indicator configuration.
    if (isset($indicators)) {
      if ($counts > 0) {
        foreach ($indicators as $key => $indicator) {
          if ($counts >= $indicator) {
            $node->field_hotness->value = $key;
          }
        }
      }
      else {
        $node->field_hotness->value = 1;
      }
    }

    $node->save();
  }

  public static function getSubscribedEvents() {
    $events = [];
    $events[FlagEvents::ENTITY_FLAGGED][] = ['onFlag'];
    $events[FlagEvents::ENTITY_UNFLAGGED][] = ['onUnflag'];
    return $events;
  }
}
