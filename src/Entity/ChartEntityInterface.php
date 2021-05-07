<?php

namespace Drupal\chart_intigration\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Chart entity entities.
 *
 * @ingroup chart_intigration
 */
interface ChartEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Chart entity name.
   *
   * @return string
   *   Name of the Chart entity.
   */
  public function getName();

  /**
   * Sets the Chart entity name.
   *
   * @param string $name
   *   The Chart entity name.
   *
   * @return \Drupal\chart_intigration\Entity\ChartEntityInterface
   *   The called Chart entity entity.
   */
  public function setName($name);

  /**
   * Gets the Chart entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Chart entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Chart entity creation timestamp.
   *
   * @param int $timestamp
   *   The Chart entity creation timestamp.
   *
   * @return \Drupal\chart_intigration\Entity\ChartEntityInterface
   *   The called Chart entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Chart entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Chart entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\chart_intigration\Entity\ChartEntityInterface
   *   The called Chart entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Chart entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Chart entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\chart_intigration\Entity\ChartEntityInterface
   *   The called Chart entity entity.
   */
  public function setRevisionUserId($uid);

}
