<?php

namespace Drupal\chart_intigration\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\chart_intigration\Entity\ChartEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ChartEntityController.
 *
 *  Returns responses for Chart entity routes.
 */
class ChartEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Chart entity revision.
   *
   * @param int $chart_entity_revision
   *   The Chart entity revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($chart_entity_revision) {
    $chart_entity = $this->entityTypeManager()->getStorage('chart_entity')
      ->loadRevision($chart_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('chart_entity');

    return $view_builder->view($chart_entity);
  }

  /**
   * Page title callback for a Chart entity revision.
   *
   * @param int $chart_entity_revision
   *   The Chart entity revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($chart_entity_revision) {
    $chart_entity = $this->entityTypeManager()->getStorage('chart_entity')
      ->loadRevision($chart_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $chart_entity->label(),
      '%date' => $this->dateFormatter->format($chart_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Chart entity.
   *
   * @param \Drupal\chart_intigration\Entity\ChartEntityInterface $chart_entity
   *   A Chart entity object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ChartEntityInterface $chart_entity) {
    $account = $this->currentUser();
    $chart_entity_storage = $this->entityTypeManager()->getStorage('chart_entity');

    $langcode = $chart_entity->language()->getId();
    $langname = $chart_entity->language()->getName();
    $languages = $chart_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $chart_entity->label()]) : $this->t('Revisions for %title', ['%title' => $chart_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all chart entity revisions") || $account->hasPermission('administer chart entity entities')));
    $delete_permission = (($account->hasPermission("delete all chart entity revisions") || $account->hasPermission('administer chart entity entities')));

    $rows = [];

    $vids = $chart_entity_storage->revisionIds($chart_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\chart_intigration\ChartEntityInterface $revision */
      $revision = $chart_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $chart_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.chart_entity.revision', [
            'chart_entity' => $chart_entity->id(),
            'chart_entity_revision' => $vid,
          ]));
        }
        else {
          $link = $chart_entity->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.chart_entity.translation_revert', [
                'chart_entity' => $chart_entity->id(),
                'chart_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.chart_entity.revision_revert', [
                'chart_entity' => $chart_entity->id(),
                'chart_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.chart_entity.revision_delete', [
                'chart_entity' => $chart_entity->id(),
                'chart_entity_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['chart_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
