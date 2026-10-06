<?php

namespace Premmerce\Filter\Admin\Tabs;

use Premmerce\Filter\Seo\SeoModel;
use Premmerce\Filter\Seo\RulesTable;
use Premmerce\Filter\Seo\WPMLHelper;
use Premmerce\Filter\Seo\RulesGenerator;
use Premmerce\SDK\V2\FileManager\FileManager;
use Premmerce\SDK\V2\Notifications\AdminNotifier;
use Premmerce\Filter\Admin\Tabs\Base\BaseSettings;
use Premmerce\Filter\Admin\Tabs\Base\TabInterface;
class SeoRules implements TabInterface {
    /**
     * File Manager
     *
     * @var FileManager
     */
    private $fileManager;

    /**
     * Model
     *
     * @var SeoModel
     */
    private $model;

    /**
     * Admin Notifier
     *
     * @var AdminNotifier
     */
    private $notifier;

    /**
     * Rules Generator
     *
     * @var RulesGenerator
     */
    private $generator;

    const KEY_UPDATE_PATHS = 'premmerce_filter_update_paths';

    /**
     * Capability needed to change SEO rules: the one the Product Filter pages are registered with.
     */
    const CAPABILITY = 'manage_options';

    /**
     * Nonce actions for the rule form, the Generate Rules form and the Update paths link.
     */
    const NONCE_CREATE = 'premmerce_filter_seo_create';

    const NONCE_UPDATE = 'premmerce_filter_seo_update';

    const NONCE_GENERATE = 'premmerce_filter_seo_generate';

    const NONCE_UPDATE_PATHS = 'premmerce_filter_seo_update_paths';

    /**
     * Nonce action of the admin AJAX requests, localized as adminLocOptions.ajax_nonce.
     */
    const NONCE_AJAX = 'filter-ajax-nonce';

    /**
     * SeoRules constructor.
     *
     * @param FileManager   $fileManager
     * @param AdminNotifier $notifier
     */
    public function __construct( FileManager $fileManager, AdminNotifier $notifier ) {
        $this->fileManager = $fileManager;
        $this->model = new SeoModel();
        $this->notifier = $notifier;
    }

    /**
     * Register hooks
     */
    public function init() {
        add_action( 'wp_ajax_get_taxonomy_terms', array($this, 'getTaxonomyTerms') );
    }

    /**
     * Ajax get terms
     */
    public function getTaxonomyTerms() {
        if ( !current_user_can( self::CAPABILITY ) ) {
            wp_die( -1, 403 );
        }
        $terms = get_terms( array(
            'taxonomy'   => ( isset( $_POST['taxonomy'] ) && isset( $_POST['ajax_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ajax_nonce'] ), 'filter-ajax-nonce' ) ? wc_clean( wp_unslash( $_POST['taxonomy'] ) ) : null ),
            'hide_empty' => false,
        ) );
        if ( $terms instanceof \WP_Error ) {
            $terms = array();
        }
        $output = array(
            'results' => array(),
        );
        foreach ( $terms as $term ) {
            list( $id, $text, $slug ) = array_values( (array) $term );
            $output['results'][] = array(
                'id'       => $id,
                'text'     => $text,
                'slug'     => $slug,
                'taxonomy' => $term->taxonomy,
            );
        }
        echo json_encode( $output );
        wp_die();
    }

    /**
     * Render tab content
     */
    public function render() {
        $this->renderList();
    }

    /**
     * Render rules list
     */
    public function renderList() {
        $categoriesDropDownArgs = $this->getCategoryDropdownArgs();
        $attributes = $this->getAttributes();
        $table = new RulesTable($this->fileManager, $this->model);
        $rule = array(
            'id'                => '',
            'term_id'           => '',
            'path'              => '',
            'h1'                => '',
            'title'             => '',
            'meta_description'  => '',
            'description'       => '',
            'enabled'           => 1,
            'discourage_search' => 0,
            'data'              => null,
        );
        $this->fileManager->includeTemplate( 'admin/tabs/seo.php', array(
            'categoriesDropDownArgs' => $categoriesDropDownArgs,
            'attributes'             => $attributes,
            'rulesTable'             => $table,
            'rule'                   => $rule,
            'fm'                     => $this->fileManager,
        ) );
    }

    /**
     * Tab label
     *
     * @return string
     */
    public function getLabel() {
        $text = __( 'SEO Rules', 'premmerce-filter' );
        $seoLabel = BaseSettings::premiumForTabLabel( $text );
        return $seoLabel;
    }

    /**
     * Tab name
     *
     * @return string
     */
    public function getName() {
        return 'seo';
    }

    /**
     * Is tab valid
     *
     * @return bool
     */
    public function valid() {
        return true;
    }

    /**
     * Arguments for category select
     *
     * @return array
     */
    private function getCategoryDropdownArgs() {
        $categoriesDropDownArgs = array(
            'hide_empty'       => 0,
            'hide_if_empty'    => false,
            'taxonomy'         => 'product_cat',
            'name'             => 'term_id',
            'orderby'          => 'name',
            'hierarchical'     => true,
            'show_option_none' => false,
            'echo'             => 0,
        );
        $categoriesDropDownArgs = apply_filters(
            'taxonomy_parent_dropdown_args',
            $categoriesDropDownArgs,
            'product_cat',
            'new'
        );
        return $categoriesDropDownArgs;
    }

    /**
     * Get attributes for term selects
     *
     * @return array
     */
    private function getAttributes() {
        $wcAttributes = wc_get_attribute_taxonomies();
        $attributes = array();
        foreach ( $wcAttributes as $attribute ) {
            $attributes['pa_' . $attribute->attribute_name] = $attribute->attribute_label;
        }
        $brand_taxonomies = apply_filters( 'premmerce_product_filter_brand_taxonomies', array('product_brand') );
        foreach ( $brand_taxonomies as $brand_taxonomy ) {
            if ( taxonomy_exists( $brand_taxonomy ) ) {
                $brandTaxonomy = get_taxonomy( $brand_taxonomy );
                $attributes[$brandTaxonomy->name] = $brandTaxonomy->label;
            }
        }
        return $attributes;
    }

    /**
     * Redirect to previous page
     */
    private function redirectBack() {
        wp_safe_redirect( ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null ) );
        die;
    }

}
