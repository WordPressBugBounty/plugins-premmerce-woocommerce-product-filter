<?php namespace Premmerce\Filter\Ajax\Strategy;

use Premmerce\Filter\Frontend\Frontend;

class WoocommerceStrategy extends WidgetsStrategy
{
    public function __construct()
    {

        add_action('woocommerce_before_shop_loop', array($this, 'openContainer'), 0);

        add_action('woocommerce_after_shop_loop', array($this, 'closeContainer'), 999);

        add_action('woocommerce_no_products_found', array($this, 'openContainer'), 0);

        add_action('woocommerce_no_products_found', array($this, 'closeContainer'), 999);
    }

    /**
     * Update Response
     *
     * @param  mixed $response
     * @param  mixed $instance
     * @return void
     */
    public function updateResponse(array $response, array $instance = array())
    {
        $instance = Frontend::getInstanceByRequest();

        $response = $this->addArchiveHeader($response);
        $response = $this->addDocumentTitle($response);

        return parent::updateResponse($this->loadContent($response), $instance);
    }

    /**
     * The archive's H1 and description. The container starts after them, at
     * woocommerce_before_shop_loop, but they change with the filter, e.g. on an SEO rule's page.
     * Must run before loadContent(), which turns them off.
     *
     * @param array $response
     *
     * @return array
     */
    public function addArchiveHeader(array $response)
    {
        $response[] = array(
            'selector' => '.woocommerce-products-header__title',
            'callback' => 'html',
            'html'     => woocommerce_page_title(false)
        );

        ob_start();
        do_action('woocommerce_archive_description');
        $description = ob_get_clean();

        $response[] = array(
            'selector' => '.woocommerce-products-header .term-description, .woocommerce-products-header .page-description',
            'callback' => 'remove',
            'html'     => ''
        );

        $response[] = array(
            'selector' => '.woocommerce-products-header',
            'callback' => 'append',
            'html'     => $description
        );

        return $response;
    }

    /**
     * Load Content
     *
     * @param $response
     *
     * @return array
     */
    public function loadContent($response)
    {
        add_filter('woocommerce_show_page_title', '__return_false');
        remove_all_actions('woocommerce_archive_description');

        ob_start();
        echo '<div>';
        woocommerce_content();
        echo '</div>';
        $html = ob_get_clean();

        $response[] = array(
            'selector' => '.premmerce-filter-ajax-container',
            'callback' => 'replacePart',
            'html'     => $html
        );

        return $response;
    }

    /**
     * Open Container
     *
     * @return void
     */
    public function openContainer()
    {
        echo '<div class="premmerce-filter-ajax-container">';
    }

    /**
     * Close Container
     *
     * @return void
     */
    public function closeContainer()
    {
        echo '</div>';
    }
}
