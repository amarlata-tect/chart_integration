<?php

namespace Drupal\chart_intigration;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class ChartEntityStorage extends SqlContentEntityStorage implements ChartEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ChartEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {chart_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {chart_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ChartEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {chart_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('chart_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
