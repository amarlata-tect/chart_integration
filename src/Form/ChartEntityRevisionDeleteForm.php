<?php

namespace Drupal\chart_intigration\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Chart entity revision.
 *
 * @ingroup chart_intigration
 */
class ChartEntityRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Chart entity revision.
   *
   * @var \Drupal\chart_intigration\Entity\ChartEntityInterface
   */
  protected $revision;

  /**
   * The Chart entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $chartEntityStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->chartEntityStorage = $container->get('entity_type.manager')->getStorage('chart_entity');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'chart_entity_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.chart_entity.version_history', ['chart_entity' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $chart_entity_revision = NULL) {
    $this->revision = $this->ChartEntityStorage->loadRevision($chart_entity_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->ChartEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Chart entity: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Chart entity %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.chart_entity.canonical',
       ['chart_entity' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {chart_entity_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.chart_entity.version_history',
         ['chart_entity' => $this->revision->id()]
      );
    }
  }

}
