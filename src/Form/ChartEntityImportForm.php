<?php

namespace Drupal\chart_intigration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\chart_intigration\ParserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\chart_intigration\Entity\ChartEntity;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;

class ChartEntityImportForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityBundleInfo;

  /**
   * The parser service.
   *
   * @var \Drupal\chart_intigration\Parser\ParserInterface
   */
  protected $parser;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * ImporterForm class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_bundle_info
   *   The entity bundle info service.
   * @param \Drupal\chart_intigration\Parser\ParserInterface $parser
   *   The parser service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_bundle_info, ParserInterface $parser, RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityBundleInfo = $entity_bundle_info;
    $this->parser = $parser;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('chart_intigration.parser'),
      $container->get('renderer')
    );
  }

  /**
   * @inheritdoc
   */
  public function getFormId() {
    return 'chart_entity_csv_importer_form';
  }

  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['importer'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'csv-importer',
      ],
    ];

    $form['importer']['entity_type_bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Chart type'),
      '#options' => ['' => '-- Select --', 'property_type_diversification' => 'Property Type Diversification', 'geographic_diversification' => 'Geographic Diversification', 'tenant_industry_diversification' => 'Tenant Industry Diversification'],
      '#required' => TRUE,
      '#weight' => 5,
    ];

    $form['importer']['entity_ref_page'] = [
      '#type' => 'select',
      '#title' => $this->t('Select page for chart update'),
      '#options' => $this->loadTaxonomyByVid(),
      '#weight' => 8,
    ];
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('chart_intigration')->getPath().'/templates';

    $form['importer']['csv'] = [
      '#title' => $this->t('Upload File'),
      '#type' => 'managed_file',
      '#description' => $this->t('Upload CSV file for importing chart with respective chart bundle. Get sample CSV file for <a href ="/'.$module_path.'/property_type_chart_example.csv"><b>property</b></a>, <a href ="/'.$module_path.'/geographic_type_chart_example.csv"><b>geographic</b></a> and <a href ="/'.$module_path.'/tenant_example_data.csv"><b>tenant</b></a> chart.'),
      '#upload_validators' => [ 'file_validate_extensions' => ['csv'],],
      '#required' => TRUE,
      '#autoupload' => TRUE,
      '#weight' => 10,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    ];

    return $form;
  }
   /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity_type_bundle = NULL;
    $csv = current($form_state->getValue('csv'));
    $term_id = $form_state->getValue('entity_ref_page');
    // Convert csv to array.
    $csv_parse = $this->parser->getCsvById($csv, ',');
    if (isset($form_state->getUserInput()['entity_type_bundle'])) {
      $entity_type_bundle = $form_state->getUserInput()['entity_type_bundle'];
    }

    $paragraph_details = $this->related_paragraph_fields($entity_type_bundle);

    // Create chart entity.
    $entity_name = $entity_type_bundle.'_'.rand(10,10000);
    $chart = ChartEntity::create([
      'type'       => $entity_type_bundle,
      'name'       => $entity_name,
    ]);
    $entity_ref_pname = (($entity_type_bundle == 'property_type_diversification') ? 'field_property_type_details' :
      (($entity_type_bundle == 'geographic_diversification') ? 'field_geographic_details' :
        (($entity_type_bundle == 'tenant_industry_diversification') ? 'field_tenant_industry_details' : '')));
    $chart_paragraph = [];

    // Create entity reference paragraph.
    foreach($csv_parse as $key => $row) {
      $paragraph_data = Paragraph::create([
        'type'                        => $paragraph_details['type'],
        $paragraph_details['label']    => $row[0],
        $paragraph_details['value']    => $row[1],
      ]);

      try {
        $paragraph_data->save();
      } catch (Exception $e) {
        \Drupal::logger('chart_intigration')->notice('Cannot save Paragraph: ' . $e->getMessage());
        return('ok');
      }

      // Get paragraph revision id.
      $chart_paragraph[$key] = [
        'target_id'          => $paragraph_data->id(),
        'target_revision_id' => $paragraph_data->getRevisionId(),
      ];
    }
    // Save paragraph in existing products.
    $chart->set($entity_ref_pname, $chart_paragraph);
    $chart->save();

    // Update chart reference node.
    if(!empty($term_id)) {
      $term = Term::load($term_id);
      $nid = $term->get('field_node_id')->value;
      $page_type = $term->get('field_page_type')->value;
      $this->updateChartNode(array('nid' => $nid, 'chart_id' => $chart_id, 'chart_type' => $entity_type_bundle, 'page_type' => $page_type));
    }

    \Drupal::messenger()->addMessage(
      $this->t(
        'Created the @entity_name Chart entity.',
        ['@entity_name' => $entity_name]
      )
    );
    \Drupal::logger('chart_intigration')->notice('Created the dasds Chart entity.');
    $response = new RedirectResponse('/admin/content/chart');
    $response->send();
    return;
  }

  /**
   * @param $bundle_name
   * @return array
   */
  protected function related_paragraph_fields($bundle_name) {
    $paragraph = [];
    switch ($bundle_name) {
      case 'property_type_diversification':
        $paragraph = ['type' => 'property_type_chart',
          'label' => 'field_property_type_selection',
          'value' => 'field_property_type_value'
        ];
        break;
      case 'geographic_diversification':
        $paragraph = ['type' => 'geographic_chart',
          'label' => 'field_geographic_chart_selection',
          'value' => 'field_geographic_value'
        ];
        break;
      case 'tenant_industry_diversification':
        $paragraph = ['type' => 'tenant_industry_chart',
          'label' => 'field_tenant_industry_selection',
          'value' => 'field_tenant_industry_value'
        ];
        break;
    }
    return $paragraph;
  }

  /**
   * Load data by vid.
   * @return array
   */
  protected function loadTaxonomyByVid() {
    $term_data = ['' => '- Select -'];
    $vocab_name = 'chart_entity_pages';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocab_name);
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
    return $term_data;
  }

  /**
   * @param $bundle_name
   * @return array
   */
  protected function node_reference_fields($bundle_name, $page_type) {
    $paragraph = [];
    switch ($bundle_name) {
      case 'property_type_diversification':
        if($page_type == 'portfolio') {
          $paragraph = ['type' => 'portfolio_chart_component',
            'field' => 'field_property_portfolio_chart'
          ];
        } elseif ($page_type == 'cpa') {
          $paragraph = ['type' => 'cpa_portfolio',
            'field' => 'field_chart_items',
            'is_multiple'  => true,
            'reference_field' => 'field_cpa_portfolio_chart'
          ];
        }
        break;
      case 'geographic_diversification':
        if($page_type == 'portfolio') {
          $paragraph = ['type' => 'portfolio_chart_component',
            'field' => 'field_geographic_portfolio_chart'
          ];
        } elseif ($page_type == 'cpa') {
          $paragraph = ['type' => 'cpa_portfolio',
            'field' => 'field_geographic_chart',
            'is_multiple'  => true,
            'reference_field' => 'field_cpa_portfolio_chart'
          ];
        }
        break;
      case 'tenant_industry_diversification':
        if($page_type == 'portfolio') {
          $paragraph = ['type' => 'portfolio_chart_component',
            'field' => 'field_tenant_portfolio_chart'
          ];
        } elseif ($page_type == 'cpa') {
          $paragraph = ['type' => 'cpa_portfolio',
            'field' => 'field_tenant_industry_chart',
            'is_multiple'  => true,
            'reference_field' => 'field_cpa_portfolio_chart'
          ];
        }
        break;
    }
    return $paragraph;
  }

  /**
   * Update chart entity reference node.
   */
  protected function updateChartNode($param) {
    $node = Node::load($param['nid']);
    $paragraph = $node->field_add_components->getValue();
    foreach ( $paragraph as $element ) {
      $p = Paragraph::load( $element['target_id'] );
      $p_type = $p->get('type')->getString();
      $p_details = $this->node_reference_fields($param['chart_type'], $param['page_type']);
      if($p_type == $p_details['type']) {
        if($p_details['is_multiple']) {
          $ref_pid = $p->get($p_details['reference_field'])->getValue()[0]['target_id'];
          $ref_p = Paragraph::load($ref_pid);
          $ref_p->set($p_details['field'],  $param['chart_id']);
          $ref_p->save();
        } else {
          $p->set($p_details['field'],  $param['chart_id']);
          $p->save();
        }
      }
    }
  }
}

