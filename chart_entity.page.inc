<?php

/**
 * @file
 * Contains chart_entity.page.inc.
 *
 * Page callback for Chart entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Chart entity templates.
 *
 * Default template: chart_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_chart_entity(array &$variables) {
  // Fetch ChartEntity Entity Object.
  $chart_entity = $variables['elements']['#chart_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
