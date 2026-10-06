<?php namespace Premmerce\Filter\Ajax\Strategy;

use Premmerce\Filter\Widget\ActiveFilterWidget;
use Premmerce\Filter\Widget\FilterWidget;

class WidgetsStrategy implements ThemeStrategyInterface
{
    public function updateResponse(array $response, array $instance)
    {
        $response = $this->addFilter($response, $instance);
        $response = $this->addActiveFilter($response);

        return $response;
    }

    public function addFilter($response, $instance)
    {
        ob_start();

        the_widget(FilterWidget::class, $instance);

        $response[] = array(
            'selector' => '[data-premmerce-filter]',
            'callback' => 'replacePart',
            'html'     => ob_get_clean()
        );

        return $response;
    }

    public function addActiveFilter($response)
    {
        ob_start();

        the_widget(ActiveFilterWidget::class);

        $response[] = array(
            'selector' => '.premmerce-active-filters-widget-wrapper',
            'callback' => 'replaceWith',
            'html'     => ob_get_clean()
        );

        return $response;
    }

    /**
     * The document title of the page the filter went to, e.g. an SEO rule's title. Only for
     * themes that let WordPress print the title tag, as wp_get_document_title() is their title.
     *
     * @param array $response
     *
     * @return array
     */
    public function addDocumentTitle(array $response)
    {
        if (current_theme_supports('title-tag')) {
            $response[] = array(
                'selector' => 'head > title',
                'callback' => 'html',
                'html'     => wp_get_document_title()
            );
        }

        return $response;
    }
}
