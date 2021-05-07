<?php

namespace Drupal\chart_intigration\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Chart entity type entity.
 *
 * @ConfigEntityType(
 *   id = "chart_entity_type",
 *   label = @Translation("Chart entity type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\chart_intigration\ChartEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\chart_intigration\Form\ChartEntityTypeForm",
 *       "edit" = "Drupal\chart_intigration\Form\ChartEntityTypeForm",
 *       "delete" = "Drupal\chart_intigration\Form\ChartEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\chart_intigration\ChartEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "chart_entity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "chart_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/chart_entity_type/{chart_entity_type}",
 *     "add-form" = "/admin/structure/chart_entity_type/add",
 *     "edit-form" = "/admin/structure/chart_entity_type/{chart_entity_type}/edit",
 *     "delete-form" = "/admin/structure/chart_entity_type/{chart_entity_type}/delete",
 *     "collection" = "/admin/structure/chart_entity_type"
 *   }
 * )
 */
class ChartEntityType extends ConfigEntityBundleBase implements ChartEntityTypeInterface {

  /**
   * The Chart entity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Chart entity type label.
   *
   * @var string
   */
  protected $label;

}
