<?php

namespace Drupal\chart_intigration;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\chart_intigration\Entity\ChartEntityInterface;

/**
 * Defines the storage handler class for Chart entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Chart entity entities.
 *
 * @ingroup chart_intigration
 */
interface ChartEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Chart entity revision IDs for a specific Chart entity.
   *
   * @param \Drupal\chart_intigration\Entity\ChartEntityInterface $entity
   *   The Chart entity entity.
   *
   * @return int[]
   *   Chart entity revision IDs (in ascending order).
   */
  public function revisionIds(ChartEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Chart entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Chart entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\chart_intigration\Entity\ChartEntityInterface $entity
   *   The Chart entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ChartEntityInterface $entity);

  /**
   * Unsets the language for all Chart entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
